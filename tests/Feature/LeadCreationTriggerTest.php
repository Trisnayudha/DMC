<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventsDetailController;
use App\Http\Controllers\Admin\MemberLeadFollowUpController;
use App\Http\Controllers\Admin\UsersController;
use App\Models\Company\CompanyModel;
use App\Models\MemberLeadFollowUp;
use App\Models\User;
use App\Services\Membership\MemberVerificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadCreationTriggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'lead_trigger_test', 'database.connections.lead_trigger_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Carbon::setTestNow('2026-10-28 10:00:00');
        Http::fake();
        Storage::fake('local');
        $this->mock(MemberVerificationService::class, function ($mock) {
            $mock->shouldReceive('changeMailchimpEmail')->andReturn(MemberVerificationService::MAILCHIMP_EMAIL_SKIPPED);
        });

        $tables = [
            'users' => ['name', 'email', 'alternative_email', 'status_member', 'uname', 'verified_at', 'qrcode', 'tier'],
            'profiles' => ['users_id', 'company_id', 'job_title', 'phone', 'fullphone'],
            'company' => ['users_id', 'explore', 'prefix', 'company_name', 'company_website', 'company_category', 'company_other', 'address', 'city', 'portal_code', 'prefix_office_number', 'office_number', 'full_office_number', 'country', 'is_verified', 'verified_at'],
            'payment' => ['member_id', 'code_payment', 'package', 'tickets_id'],
            'member_company_follow_ups' => ['user_id', 'status'],
            'user_edit_logs' => ['user_id', 'admin_id', 'admin_name', 'changes'],
            'member_lead_follow_up_notes' => ['lead_id', 'step', 'note', 'created_by_id', 'created_by_name'],
        ];
        foreach ($tables as $name => $fields) {
            Schema::create($name, function (Blueprint $table) use ($fields) {
                $table->id();
                foreach ($fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
            });
        }
        Schema::create('member_lead_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('result')->default('pending');
            $table->boolean('do_not_send')->default(false);
            $table->integer('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            foreach (['deadline_at', 'sponsorkit_sent_at', 'first_follow_up_at', 'second_follow_up_at'] as $field) {
                $table->timestamp($field)->nullable();
            }
            $table->timestamps();
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Test Member', 'email' => 'member@example.test', 'status_member' => 'active']);
        DB::table('company')->insert(['id' => 1, 'users_id' => 1, 'company_name' => 'Test Mining', 'explore' => 'agree']);
        DB::table('profiles')->insert(['users_id' => 1, 'company_id' => 1, 'job_title' => 'Director']);
        DB::table('payment')->insert(['member_id' => 1, 'code_payment' => 'TEST-EVENT']);
    }

    public function test_member_data_edits_do_not_create_a_lead(): void
    {
        $response = (new UsersController)->updateUser(Request::create('/admin/users/1', 'POST', [
            'name' => 'Updated Member', 'email' => 'member@example.test', 'company_name' => 'Updated Mining',
            'job_title' => 'General Manager', 'phone' => '628123456789',
        ]), 1);

        $this->assertTrue($response->getData()->success);
        $this->assertSame('Updated Mining', CompanyModel::find(1)->company_name);
        $this->assertSame('Updated Member', User::find(1)->name);
        $this->assertSame(0, MemberLeadFollowUp::count());
    }

    public function test_event_participant_edits_do_not_create_or_reopen_a_lead(): void
    {
        foreach ([null, 'win', 'loss'] as $result) {
            MemberLeadFollowUp::query()->delete();
            if ($result) {
                MemberLeadFollowUp::create(['user_id' => 1, 'result' => $result]);
            }
            (new EventsDetailController)->editPeserta(Request::create('/admin/events/participant', 'POST', [
                'code_payment_edit' => 'TEST-EVENT', 'package_edit' => 'member',
                'name_edit' => 'Edited Attendee', 'email_edit' => 'member@example.test',
                'company_name_edit' => 'Event Mining', 'phone_edit' => '628123456789', 'job_title_edit' => 'Manager',
            ]));
            $this->assertSame('Edited Attendee', User::find(1)->name);
            $this->assertSame('Event Mining', CompanyModel::find(1)->company_name);
            $this->assertSame($result ? 1 : 0, MemberLeadFollowUp::count());
            $this->assertSame(0, MemberLeadFollowUp::where('result', 'pending')->count());
        }
    }

    public function test_company_creation_and_consent_edits_are_not_approval_events(): void
    {
        CompanyModel::find(1)->update(['explore' => '0']);
        CompanyModel::find(1)->update(['explore' => 'agree']);
        CompanyModel::create(['users_id' => 1, 'company_name' => 'Another Company', 'explore' => 'agree']);
        User::find(1)->update(['name' => 'Another Name']);
        $this->assertSame(0, MemberLeadFollowUp::count());
    }

    private function approve(): void
    {
        $response = (new UsersController)->verifyMember(Request::create('/admin/users/1/verify', 'POST', [
            'send_notification' => false,
        ]), 1);
        $this->assertTrue($response->getData()->success);
    }

    public function test_explicit_member_approval_creates_one_lead_and_retry_preserves_it(): void
    {
        DB::table('users')->where('id', 1)->update(['status_member' => 'pending']);
        $this->approve();
        $this->assertSame('active', User::find(1)->status_member);
        $this->assertSame(1, MemberLeadFollowUp::count());
        $lead = MemberLeadFollowUp::first();
        $deadline = $lead->deadline_at->toDateTimeString();
        Carbon::setTestNow(now()->addDay());
        $this->approve();
        $this->assertSame(1, MemberLeadFollowUp::count());
        $this->assertSame($deadline, $lead->fresh()->deadline_at->toDateTimeString());
    }

    public function test_verifying_an_active_member_does_not_create_or_reopen_a_lead(): void
    {
        $this->approve();
        $this->assertSame(0, MemberLeadFollowUp::count());
        MemberLeadFollowUp::create(['user_id' => 1, 'result' => 'win']);
        $this->approve();
        $this->assertSame(1, MemberLeadFollowUp::count());
        $this->assertSame('win', MemberLeadFollowUp::first()->result);
    }

    public function test_automatic_approval_lead_still_requires_explore_consent(): void
    {
        DB::table('users')->where('id', 1)->update(['status_member' => 'pending']);
        DB::table('company')->where('id', 1)->update(['explore' => '0']);
        $this->approve();
        $this->assertSame(0, MemberLeadFollowUp::count());
    }

    public function test_manual_button_can_add_an_active_member_and_rejects_duplicates_or_unapproved_members(): void
    {
        DB::table('company')->where('id', 1)->update(['explore' => '0']);
        $request = Request::create('/admin/leads', 'POST', ['user_id' => 1, 'notes' => 'Added manually']);
        $controller = new MemberLeadFollowUpController;
        $this->assertTrue($controller->store($request)->getData()->success);
        $this->assertSame(1, MemberLeadFollowUp::count());
        $this->assertSame('Added manually', DB::table('member_lead_follow_up_notes')->value('note'));
        $this->assertSame(422, $controller->store($request)->getStatusCode());
        DB::table('users')->where('id', 1)->update(['status_member' => 'pending']);
        $this->assertSame(422, $controller->store($request)->getStatusCode());
        $this->assertSame(1, MemberLeadFollowUp::count());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('lead_trigger_test');
        parent::tearDown();
    }
}
