<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MemberLeadFollowUpController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'lead_preview_test', 'database.connections.lead_preview_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status_member');
        });
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('users_id');
            $table->string('phone')->nullable();
            $table->string('fullphone')->nullable();
        });
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->integer('users_id');
            $table->string('company_name');
        });
        Schema::create('cms_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('member_lead_follow_up_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id');
            $table->timestamps();
        });
        Schema::create('member_lead_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('pic_id')->nullable();
            $table->string('result')->default('pending');
            $table->boolean('do_not_send')->default(false);
            $table->string('temperature')->default('cold');
            $table->string('channel')->nullable();
            foreach (['deadline_at', 'sponsorkit_sent_at', 'first_follow_up_at', 'second_follow_up_at'] as $field) {
                $table->timestamp($field)->nullable();
            }
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Sample Lead', 'email' => 'sample@example.test', 'status_member' => 'active'],
            ['id' => 2, 'name' => 'Unapproved', 'email' => 'inactive@example.test', 'status_member' => 'pending'],
        ]);
        DB::table('profiles')->insert(['users_id' => 1, 'phone' => '08123456789', 'fullphone' => '628123456789']);
        foreach ([
            ['id' => 1, 'temperature' => 'hot', 'channel' => 'email'],
            ['id' => 2, 'do_not_send' => true, 'temperature' => 'hot', 'channel' => 'email'],
            ['id' => 3, 'result' => 'win', 'temperature' => 'warm', 'channel' => 'whatsapp'],
            ['id' => 4, 'result' => 'loss'],
            ['id' => 5, 'user_id' => 2],
        ] as $lead) {
            DB::table('member_lead_follow_ups')->insert(array_merge(['user_id' => 1, 'created_at' => now()], $lead));
        }
    }

    private function data(array $query = []): array
    {
        return (new MemberLeadFollowUpController)->index(Request::create('/admin/leads', 'GET', $query))->getData();
    }

    public function test_overview_counts_excluded_leads_separately_and_defaults_to_all(): void
    {
        $data = $this->data();
        $this->assertCount(4, $data['list']);
        $this->assertSame(4, $data['countTotalLeads']);
        $this->assertSame(3, $data['countEligible']);
        $this->assertSame(1, $data['countDoNotSend']);
        $this->assertSame(1, $data['countPending']);
        $this->assertSame(1, $data['countWin']);
        $this->assertSame(1, $data['countLoss']);
        $this->assertEquals(['hot' => 1, 'warm' => 1, 'cold' => 1], $data['temperatureCounts']->all());
    }

    public function test_combined_filters_match_the_visible_result_and_temperature(): void
    {
        $this->assertSame([1], $this->data(['result' => 'pending', 'eligibility' => 'eligible', 'channel' => 'email', 'temperature' => 'hot'])['list']->pluck('id')->all());
        $this->assertSame([2], $this->data(['eligibility' => 'do_not_send'])['list']->pluck('id')->all());
        $this->assertSame([2], $this->data(['result' => 'do_not_send'])['list']->pluck('id')->all());
        $this->assertCount(0, $this->data(['channel' => 'whatsapp', 'result' => 'pending'])['list']);
        $this->assertSame(4, $this->data(['channel' => 'email'])['countTotalLeads']);
    }

    public function test_search_matches_phone_and_unknown_filters_are_ignored(): void
    {
        $this->assertCount(4, $this->data(['search' => '628123456789'])['list']);
        $this->assertCount(4, $this->data(['search' => '08123456789'])['list']);
        $this->assertCount(0, $this->data(['search' => 'no-match'])['list']);
        $this->assertCount(4, $this->data(['channel' => 'invalid', 'eligibility' => 'invalid'])['list']);
    }

    public function test_pagination_is_stable_and_preserves_filters(): void
    {
        for ($id = 6; $id <= 28; $id++) {
            DB::table('member_lead_follow_ups')->insert([
                'id' => $id, 'user_id' => 1, 'channel' => 'email', 'created_at' => '2030-01-01 10:00:00',
            ]);
        }
        $first = $this->data(['channel' => 'email'])['list'];
        $second = $this->data(['channel' => 'email', 'page' => 2])['list'];
        $this->assertSame(25, $first->total());
        $this->assertSame(10, $first->perPage());
        $this->assertSame(range(28, 19), $first->pluck('id')->all());
        $this->assertSame(range(18, 9), $second->pluck('id')->all());
        $this->assertSame(11, $second->firstItem());
        $this->assertSame(20, $second->lastItem());
        $this->assertStringContainsString('channel=email', $second->nextPageUrl());
        $this->assertStringContainsString('page=3', $second->nextPageUrl());
        $this->assertSame(27, $this->data()['countTotalLeads']);
        $large = $this->data(['per_page' => 25, 'page' => 2])['list'];
        $this->assertCount(2, $large);
        $this->assertSame(26, $large->firstItem());
        $this->assertSame(27, $large->lastItem());
        $this->assertStringContainsString('per_page=25', $large->previousPageUrl());

        // Export covers all matching leads, regardless of the current page.
        \Maatwebsite\Excel\Facades\Excel::fake();
        (new MemberLeadFollowUpController)->export(Request::create('/admin/leads/export', 'GET', [
            'channel' => 'email', 'page' => 2, 'per_page' => 10,
        ]));
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('lead-follow-up-all-' . date('Y-m-d') . '.xlsx', function ($export) {
            return $export->collection()->count() === 25;
        });
    }

    public function test_page_size_is_bounded_and_empty_results_have_no_pagination_links(): void
    {
        foreach ([0, -1, 10000, 'all', [10]] as $invalidSize) {
            $this->assertSame(10, $this->data(['per_page' => $invalidSize])['list']->perPage());
        }
        $this->assertSame(50, $this->data(['per_page' => '50'])['list']->perPage());
        $this->assertSame(1, $this->data(['page' => -1])['list']->currentPage());
        $empty = $this->data(['search' => 'no-match'])['list'];
        $this->assertSame(0, $empty->total());
        $this->assertNull($empty->firstItem());
        $this->assertFalse($empty->hasPages());
    }

    protected function tearDown(): void
    {
        DB::purge('lead_preview_test');
        parent::tearDown();
    }
}
