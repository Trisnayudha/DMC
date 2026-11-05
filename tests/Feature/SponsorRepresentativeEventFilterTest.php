<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SponsorCountRepresentativeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SponsorRepresentativeEventFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'representative_filter_test', 'database.connections.representative_filter_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('sponsors', function ($table) {
            $table->id(); $table->string('name'); $table->string('branding_name')->nullable(); $table->string('status');
        });
        Schema::create('company_categories', function ($table) {
            $table->id(); $table->string('name'); $table->integer('sort_order')->default(0);
        });
        Schema::create('events', function ($table) {
            $table->id(); $table->string('name'); $table->string('slug'); $table->string('event_type');
            $table->date('start_date'); $table->date('end_date')->nullable(); $table->string('start_time')->nullable(); $table->string('end_time')->nullable();
        });
        require_once database_path('migrations/2026_11_04_020000_create_sponsors_cc_contacts.php');
        (new \CreateSponsorsCcContacts)->up();
        Schema::create('users', function ($table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('status_member')->nullable();
        });
        Schema::create('profiles', function ($table) {
            $table->id(); $table->unsignedBigInteger('users_id'); $table->string('phone')->nullable();
            $table->string('fullphone')->nullable(); $table->string('job_title')->nullable();
        });
        Schema::create('company', function ($table) {
            $table->id(); $table->unsignedBigInteger('users_id'); $table->string('company_name')->nullable(); $table->string('address')->nullable();
        });
        Schema::create('payment', function ($table) {
            $table->id(); $table->unsignedBigInteger('sponsor_id')->nullable(); $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('events_id'); $table->string('code_payment')->nullable(); $table->string('qr_code')->nullable(); $table->timestamps();
        });
        Schema::create('users_event', function ($table) {
            $table->id(); $table->unsignedBigInteger('payment_id'); $table->unsignedBigInteger('users_id'); $table->timestamp('present')->nullable();
        });
        foreach (['sponsors_pic', 'sponsors_billing', 'sponsors_representative'] as $tableName) {
            Schema::create($tableName, function ($table) { $table->id(); $table->unsignedBigInteger('sponsor_id'); });
        }
        DB::table('sponsors')->insert([
            ['id' => 1, 'name' => 'Registered', 'status' => 'publish'],
            ['id' => 2, 'name' => 'Other Event', 'status' => 'publish'],
            ['id' => 3, 'name' => 'No Registration', 'status' => 'publish'],
        ]);
        DB::table('events')->insert([
            ['id' => 1, 'name' => 'Selected', 'slug' => 'selected', 'event_type' => 'DMC Event', 'start_date' => '2026-04-01'],
            ['id' => 2, 'name' => 'Other', 'slug' => 'other', 'event_type' => 'DMC Event', 'start_date' => '2026-05-01'],
            ['id' => 3, 'name' => 'No registrations', 'slug' => 'empty', 'event_type' => 'DMC Event', 'start_date' => '2026-06-01'],
            ['id' => 4, 'name' => 'Partnership', 'slug' => 'partnership', 'event_type' => 'DMC Partnership Event', 'start_date' => '2026-07-01'],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Person One', 'email' => 'one@example.test'],
            ['id' => 2, 'name' => 'Person Two', 'email' => 'two@example.test'],
        ]);
        DB::table('payment')->insert([
            ['id' => 1, 'sponsor_id' => 1, 'member_id' => 1, 'events_id' => 1, 'created_at' => '2025-12-01 10:00:00'],
            ['id' => 2, 'sponsor_id' => 2, 'member_id' => 2, 'events_id' => 2, 'created_at' => '2026-01-01 10:00:00'],
        ]);
    }

    private function directory(array $params): array
    {
        return (new SponsorCountRepresentativeController)->index(Request::create('/admin/sponsors-representative-count', 'GET', $params))->getData();
    }

    public function test_event_filter_excludes_any_registration_without_requiring_checkin_or_registration_year(): void
    {
        $data = $this->directory(['year' => 2026, 'event' => 1]);
        $this->assertSame(['No Registration', 'Other Event'], $data['nonAttendSponsors']->pluck('name')->all());
        $this->assertSame([1], $data['representatives']->pluck('payment_id')->all());
        $this->assertCount(1, $data['eventSummary']['registered']);
        $this->assertCount(2, $data['eventSummary']['not_registered']);
        $this->assertCount(0, $data['eventSummary']['attended']);
        $this->assertCount(3, $data['eventSummary']['not_attended']);
        $this->assertSame('Person One', $data['eventSummary']['registered'][0]['people']->first()->representative_name);
        DB::table('users_event')->insert(['payment_id' => 1, 'users_id' => 1, 'present' => '2026-04-01 10:00:00']);
        $checkedIn = $this->directory(['year' => 2026, 'event' => 1])['eventSummary'];
        $this->assertCount(1, $checkedIn['attended']);
        $this->assertCount(2, $checkedIn['not_attended']);
        $this->assertSame('Person One', $checkedIn['attended'][0]['present']->first()->representative_name);
        $this->assertContains(3, $data['eventFilterOptions']->pluck('id')->all());
        $this->assertNotContains(4, $data['eventFilterOptions']->pluck('id')->all());
        $this->assertNotContains(4, $this->directory(['year' => 2026, 'event' => 4])['eventFilterOptions']->pluck('id')->all());
        $this->assertCount(3, $this->directory(['year' => 2026, 'event' => 3])['nonAttendSponsors']);
        $this->assertSame(['Other Event'], $this->directory(['year' => 2026, 'event' => 1, 'company' => 'Other Event'])['nonAttendSponsors']->pluck('name')->all());
        $this->assertCount(0, $this->directory(['year' => 2026, 'event' => 1, 'company' => 'Registered'])['nonAttendSponsors']);
    }

    public function test_event_overview_renders_compact_cards_and_readable_details(): void
    {
        $data = $this->directory(['year' => 2026, 'event' => 1]);
        $html = view('admin.sponsor.representative._event_summary', $data)->render();
        $this->assertSame(4, substr_count($html, '>View sponsors '));
        $this->assertStringContainsString('Person One', $html);
        $this->assertStringContainsString('No check-in, including unregistered sponsors', $html);
        $this->assertStringContainsString('Selected &middot; Active sponsors', $html);
    }

    public function test_unfiltered_view_keeps_annual_checkin_behavior(): void
    {
        DB::table('users_event')->insert(['payment_id' => 2, 'users_id' => 2, 'present' => '2026-05-01 10:00:00']);
        $this->assertSame(['No Registration', 'Registered'], $this->directory(['year' => 2026])['nonAttendSponsors']->pluck('name')->all());
    }

    public function test_sponsor_selectors_use_branding_name_and_fall_back_to_official_name(): void
    {
        DB::table('sponsors')->where('id', 1)->update(['branding_name' => 'Alpha Brand']);
        DB::table('sponsors')->where('id', 2)->update(['branding_name' => '']);

        $data = $this->directory(['year' => 2026, 'company' => 'Registered']);
        $this->assertSame([1, 3, 2], $data['sponsorList']->pluck('id')->all());
        $this->assertSame([1, 3, 2], $data['allSponsorsWithMembers']->pluck('id')->all());
        $html = view('admin.sponsor.representative._add_to_event_modal', $data)->render();
        $this->assertStringContainsString('data-name="Alpha Brand"', $html);
        $this->assertStringContainsString('data-name="Other Event"', $html);
    }

    public function test_attendance_list_displays_branding_name_with_official_name_fallback(): void
    {
        DB::table('sponsors')->where('id', 1)->update(['branding_name' => 'Alpha Brand']);
        DB::table('sponsors')->where('id', 2)->update(['branding_name' => '']);

        $branded = $this->directory(['year' => 2026, 'event' => 1, 'company' => 'Registered']);
        $fallback = $this->directory(['year' => 2026, 'event' => 2]);

        $this->assertSame('Alpha Brand', $branded['representatives']->first()->company);
        $this->assertSame('Other Event', $fallback['representatives']->first()->company);
    }

    protected function tearDown(): void
    {
        DB::purge('representative_filter_test');
        parent::tearDown();
    }
}
