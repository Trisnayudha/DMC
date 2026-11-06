<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MailchimpUnsubscribeController;
use App\Services\Mailchimp\UnsubscribeSnapshotService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class MailchimpUnsubscribeSnapshotTest extends TestCase
{
    private $service;
    private $config = ['listId' => 'audience', 'apiKey' => 'key', 'server' => 'us1'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        require_once database_path('migrations/2026_11_06_120000_create_mailchimp_unsubscribe_snapshots_table.php');
        (new \CreateMailchimpUnsubscribeSnapshotsTable())->up();
        $this->service = app(UnsubscribeSnapshotService::class);
    }

    public function test_failed_sync_preserves_last_successful_snapshot()
    {
        $controller = Mockery::mock(MailchimpUnsubscribeController::class);
        $this->app->instance(MailchimpUnsubscribeController::class, $controller);
        $member = ['email_address' => 'a@example.com', 'last_changed' => '2026-01-01'];
        $controller->shouldReceive('fetchUnsubscribedMembersFromMailchimp')->once()->andReturn([$member]);
        $controller->shouldReceive('fetchActivitiesForMembersFromMailchimp')->once()->andReturn(['a@example.com' => [['action' => 'unsub']]]);
        $controller->shouldReceive('fetchListStatsFromMailchimp')->once()->andReturn(['member_count' => 2]);
        $this->assertTrue($this->service->syncIfDue($this->config, true));

        $controller->shouldReceive('fetchUnsubscribedMembersFromMailchimp')->once()->andThrow(new \RuntimeException('offline'));
        $this->assertFalse($this->service->syncIfDue($this->config, true));
        $snapshot = $this->service->get('audience');
        $this->assertSame([$member], json_decode($snapshot->members, true));
        $this->assertSame('offline', $snapshot->last_error);
        $this->assertNotNull($snapshot->last_synced_at);
    }

    public function test_unchanged_activity_is_reused_and_resubscribe_removes_contact()
    {
        $controller = Mockery::mock(MailchimpUnsubscribeController::class);
        $this->app->instance(MailchimpUnsubscribeController::class, $controller);
        $member = ['email_address' => 'a@example.com', 'last_changed' => '2026-01-01'];
        $controller->shouldReceive('fetchUnsubscribedMembersFromMailchimp')->twice()->andReturn([$member]);
        $controller->shouldReceive('fetchActivitiesForMembersFromMailchimp')->once()->with($this->config, [$member])->andReturn(['a@example.com' => [['action' => 'unsub']]]);
        $controller->shouldReceive('fetchActivitiesForMembersFromMailchimp')->once()->with($this->config, [])->andReturn([]);
        $controller->shouldReceive('fetchListStatsFromMailchimp')->twice()->andReturn([]);
        $this->service->syncIfDue($this->config, true);
        $this->service->syncIfDue($this->config, true);
        $this->assertCount(1, json_decode($this->service->get('audience')->activities, true));
        $this->service->removeResubscribed('audience', 'a@example.com');
        $this->assertSame([], json_decode($this->service->get('audience')->members, true));
        $this->assertNotNull($this->service->get('audience')->requested_at);
    }
    public function test_failed_activity_refresh_keeps_old_activity_and_retries()
    {
        $controller = Mockery::mock(MailchimpUnsubscribeController::class);
        $this->app->instance(MailchimpUnsubscribeController::class, $controller);
        $old = ['email_address' => 'a@example.com', 'last_changed' => '2026-01-01'];
        $changed = ['email_address' => 'a@example.com', 'last_changed' => '2026-02-01'];
        $controller->shouldReceive('fetchUnsubscribedMembersFromMailchimp')->times(3)->andReturn([$old], [$changed], [$changed]);
        $controller->shouldReceive('fetchActivitiesForMembersFromMailchimp')->once()->with($this->config, [$old])->andReturn(['a@example.com' => [['action' => 'unsub']]]);
        $controller->shouldReceive('fetchActivitiesForMembersFromMailchimp')->twice()->with($this->config, [$changed])->andReturn([], ['a@example.com' => [['action' => 'new']]]);
        $controller->shouldReceive('fetchListStatsFromMailchimp')->times(3)->andReturn([]);
        $this->service->syncIfDue($this->config, true);
        $this->service->syncIfDue($this->config, true);
        $this->assertSame('unsub', json_decode($this->service->get('audience')->activities, true)['a@example.com'][0]['action']);
        $this->assertSame(['a@example.com'], json_decode($this->service->get('audience')->activity_retry, true));
        $this->service->syncIfDue($this->config, true);
        $this->assertSame('new', json_decode($this->service->get('audience')->activities, true)['a@example.com'][0]['action']);
    }

}
