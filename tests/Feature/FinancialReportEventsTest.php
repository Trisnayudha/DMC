<?php

namespace Tests\Feature;

use App\Models\CmsRole;
use App\Models\CmsUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinancialReportEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'financial_report_test', 'database.connections.financial_report_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        (require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php'))->up();
        (require database_path('migrations/2026_10_28_110000_add_member_team_cms_role.php'))->up();
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->nullable(); $table->date('start_date')->nullable(); $table->timestamps();
        });
        Schema::create('events_tickets', function (Blueprint $table) {
            $table->id(); $table->integer('events_id')->nullable(); $table->string('title')->nullable(); $table->string('type')->nullable();
            $table->decimal('price_rupiah')->nullable(); $table->decimal('price_dollar')->nullable();
        });
        Schema::create('payment', function (Blueprint $table) {
            $table->id(); $table->integer('events_id')->nullable(); $table->integer('tickets_id')->nullable();
            $table->string('status_registration')->nullable(); $table->string('payment_method')->nullable();
            $table->integer('member_id')->nullable(); $table->string('code_payment')->nullable(); $table->decimal('discount')->nullable();
            $table->string('package')->nullable(); $table->integer('groupby_users_id')->nullable(); $table->integer('booking_contact_id')->nullable(); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {$table->id(); $table->string('name')->nullable(); $table->string('email')->nullable();});
        Schema::create('profiles', function (Blueprint $table) {$table->id(); $table->integer('users_id')->nullable(); $table->integer('company_id')->nullable(); $table->string('job_title')->nullable(); $table->string('phone')->nullable();});
        Schema::create('company', function (Blueprint $table) {$table->id(); $table->string('company_name')->nullable(); $table->string('company_category')->nullable();});
        DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', function (...$values) { return max($values); });
        $finance = CmsUser::create(['name' => 'Finance', 'email' => 'finance@example.test', 'password' => Hash::make('secret123'), 'role' => 'finance_team', 'is_active' => true]);
        $this->actingAs($finance, 'cms');
    }

    private function event(int $id, string $name, ?string $slug): void
    {
        DB::table('events')->insert(['id' => $id, 'name' => $name, 'slug' => $slug, 'start_date' => '2026-10-28']);
    }

    public function test_finance_team_can_browse_priced_events_and_open_the_existing_report(): void
    {
        $this->event(1, 'Paid Event Without Sales', 'paid-no-sales');
        $this->event(2, 'Free Event', 'free-event');
        $this->event(3, 'Dollar Ticket Event', 'dollar-ticket');
        $this->event(4, 'Historical Paid Event', 'historical-paid');
        $this->event(5, 'Missing Slug', null);
        DB::table('events_tickets')->insert([
            ['id' => 1, 'events_id' => 1, 'price_rupiah' => 100000, 'price_dollar' => 0],
            ['id' => 2, 'events_id' => 2, 'price_rupiah' => 0, 'price_dollar' => 0],
            ['id' => 3, 'events_id' => 3, 'price_rupiah' => 0, 'price_dollar' => 20],
            ['id' => 4, 'events_id' => 5, 'price_rupiah' => 100, 'price_dollar' => 0],
        ]);
        // A historic transaction may point to a ticket attached to a different event.
        DB::table('payment')->insert(['events_id' => 4, 'tickets_id' => 1, 'status_registration' => 'Paid Off', 'payment_method' => 'bank']);
        $this->get('/admin/financial-reports')->assertOk()
            ->assertSee('Paid Event Without Sales')->assertSee('Dollar Ticket Event')->assertSee('Historical Paid Event')
            ->assertDontSee('Free Event')->assertDontSee('Missing Slug')
            ->assertSee(route('events-financial-report', ['slug' => 'paid-no-sales']), false);
        $this->get('/admin/events/paid-no-sales/financial-report')->assertOk()->assertSee('Paid Event Without Sales')
            ->assertSee(route('admin.financial_reports.index'), false)
            ->assertDontSee(route('events-details', ['slug' => 'paid-no-sales']), false);
        $this->get('/admin/events')->assertForbidden();
        $this->get('/admin/financial-reports?search=Dollar')->assertOk()->assertSee('Dollar Ticket Event')
            ->assertDontSee('Paid Event Without Sales')->assertDontSee('Historical Paid Event');
        $this->get('/admin/financial-reports?search=nonexistent')->assertOk()->assertSee('Tidak ada event berbayar yang cocok');
    }

    public function test_events_are_paginated_without_duplicate_rows_for_multiple_tickets(): void
    {
        for ($i = 1; $i <= 17; $i++) {
            $this->event($i, 'Paid ' . $i, 'paid-' . $i);
            DB::table('events_tickets')->insert(['events_id' => $i, 'price_rupiah' => 1, 'price_dollar' => 0]);
        }
        DB::table('events_tickets')->insert(['events_id' => 17, 'price_rupiah' => 5, 'price_dollar' => 0]);
        $this->get('/admin/financial-reports')->assertOk()->assertSee('1–15 dari 17 event')
            ->assertSee('Paid 17')->assertDontSee('Paid 1</strong>', false);
        $this->get('/admin/financial-reports?page=2')->assertOk()->assertSee('16–17 dari 17 event')->assertSee('Paid 1');
    }

    public function test_finance_menu_requires_financial_permission(): void
    {
        CmsRole::find('finance_team')->update(['permissions' => []]);
        $finance = CmsUser::where('role', 'finance_team')->first();
        $this->actingAs($finance->fresh(), 'cms')->get('/admin/financial-reports')->assertForbidden();
        $this->get('/admin/events/paid/financial-report')->assertForbidden();
        $this->get('/admin/events/paid/financial-report/excel')->assertForbidden();
    }

    protected function tearDown(): void
    {
        DB::purge('financial_report_test');
        parent::tearDown();
    }
}
