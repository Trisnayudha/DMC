<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MasterDatabaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDatabaseSourcesTest extends TestCase
{
    public function test_sources_overlap_without_double_counting_people(): void
    {
        config(['database.default' => 'master_sources', 'database.connections.master_sources' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function (...$parts) {
            return in_array(null, $parts, true) ? null : implode('', $parts);
        });
        Schema::create('users', function ($t) { $t->id(); $t->string('name'); $t->string('email')->nullable(); $t->string('status_member')->nullable(); $t->string('source')->nullable(); $t->timestamps(); });
        Schema::create('profiles', function ($t) { $t->id(); $t->integer('users_id'); $t->integer('company_id')->nullable(); $t->string('job_title')->nullable(); $t->string('fullphone')->nullable(); $t->string('phone')->nullable(); });
        Schema::create('company', function ($t) { $t->id(); foreach (['company_name', 'full_office_number', 'office_number', 'address', 'company_website', 'company_category', 'cci', 'explore'] as $column) $t->string($column)->nullable(); });
        Schema::create('payment', function ($t) { $t->id(); $t->integer('member_id'); $t->integer('events_id')->nullable(); });
        Schema::create('xtwp_users_dmc', function ($t) { $t->id(); foreach (['name', 'job_title', 'company_name', 'email', 'fullphone', 'phone', 'full_office_number', 'office_number', 'address', 'company_website', 'company_category', 'cci', 'explore'] as $column) $t->string($column)->nullable(); $t->timestamps(); });
        Schema::create('partnership_event_visitors', function ($t) { $t->id(); foreach (['name', 'job_title', 'company_name', 'business_email', 'mobile_number', 'office_number', 'address', 'website'] as $column) $t->string($column)->nullable(); $t->timestamps(); });
        DB::table('users')->insert(['id' => 1, 'name' => 'Member', 'email' => 'person@example.test', 'status_member' => 'active']);
        DB::table('payment')->insert([['member_id' => 1, 'events_id' => 1], ['member_id' => 1, 'events_id' => 2]]);
        DB::table('xtwp_users_dmc')->insert([['name' => 'member', 'email' => ' PERSON@example.test '], ['name' => 'No email', 'email' => null]]);
        DB::table('partnership_event_visitors')->insert([['name' => 'Member', 'business_email' => 'person@example.test'], ['name' => 'Other visitor', 'business_email' => 'other@example.test']]);
        $data = (new MasterDatabaseController())->index(Request::create('/admin/master-database'))->getData();
        $this->assertSame(3, (int) $data['totalDeduped']);
        $this->assertSame(5, (int) $data['totalRaw']);
        $this->assertSame(['members' => 1, 'events' => 1, 'partnership' => 2, 'intake' => 2], $data['sourceCounts']);
        $this->assertSame(['events' => 1, 'intake' => 1], $data['sourceMatched']);
        $this->assertSame(0, $data['eventOnlyCount']);
        $data = (new MasterDatabaseController())->index(Request::create('/admin/master-database?mode=raw'))->getData();
        $this->assertSame(5, count($data['list']));
        $this->assertSame(1, $data['sourceCounts']['members']);
        DB::table('xtwp_users_dmc')->insert(['name' => 'Different Person', 'email' => 'person@example.test']);
        $data = (new MasterDatabaseController())->index(Request::create('/admin/master-database'))->getData();
        // Matching is by email only: a different name on the same email is still the same contact.
        $this->assertSame(3, (int) $data['totalDeduped']);
        // Non-member cards only exclude Members: "No email" (intake) and "Other visitor" (partnership).
        $this->assertSame(['events' => 0, 'partnership' => 1, 'intake' => 1], $data['nonMemberCounts']);
        $review = (new MasterDatabaseController())->index(Request::create('/admin/master-database?mode=duplicates'))->getData();
        $this->assertCount(4, $review['list']);
        DB::table('users')->insert(['id' => 2, 'name' => 'Event Only', 'email' => 'event@example.test', 'status_member' => null]);
        DB::table('payment')->insert(['member_id' => 2, 'events_id' => 1]);
        $data = (new MasterDatabaseController())->index(Request::create('/admin/master-database'))->getData();
        $this->assertSame(1, $data['eventOnlyCount']);

        $this->assertSame(['members' => 1, 'events' => 1, 'partnership' => 1, 'intake' => 1, 'other' => 0], $data['groupCounts']);
        $this->assertSame((int) $data['totalDeduped'], array_sum($data['groupCounts']));
        // Repeated source rows do not remove a contact from its contribution group.
        DB::table('xtwp_users_dmc')->insert(['name' => 'Other visitor', 'email' => 'other@example.test']);
        DB::table('users')->insert(['id' => 3, 'name' => 'Member', 'email' => 'person@example.test', 'status_member' => null, 'created_at' => '2030-01-01']);
        $deduped = (new MasterDatabaseController())->index(Request::create('/admin/master-database'))->getData();
        $this->assertSame($data['groupCounts'], $deduped['groupCounts']);
        $this->assertSame(1, $deduped['breakdown']['active']);
        foreach (['raw', 'duplicates'] as $mode) {
            $view = (new MasterDatabaseController())->index(Request::create('/admin/master-database?mode=' . $mode))->getData();
            $this->assertSame($deduped['groupCounts'], $view['groupCounts']);
            foreach ($view['list'] as $row) {
                if ($row->in_members) {
                    $this->assertSame('members', $row->contact_group);
                    $this->assertSame('active', $row->identity_member_status);
                }
            }
        }
        // Unclassified contacts must stay visible and cannot be silently assigned a source.
        DB::table('users')->insert(['id' => 4, 'name' => 'Unclassified', 'email' => 'unknown@example.test']);
        $data = (new MasterDatabaseController())->index(Request::create('/admin/master-database'))->getData();
        $this->assertSame(1, $data['groupCounts']['other']);
        $this->assertSame((int) $data['totalDeduped'], array_sum($data['groupCounts']));
    }
}
