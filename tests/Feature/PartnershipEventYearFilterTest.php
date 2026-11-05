<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PartnershipEventVisitorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PartnershipEventYearFilterTest extends TestCase
{
    public function test_year_and_search_filter_partnership_events_together(): void
    {
        config(['database.default' => 'partnership_year_test', 'database.connections.partnership_year_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('events', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('event_type');
            $table->date('start_date')->nullable();
        });
        Schema::create('partnership_event_visitors', function ($table) {
            $table->id();
            $table->integer('events_id');
        });
        DB::table('events')->insert([
            ['name' => 'Mining 2026', 'event_type' => 'Partnership Event', 'start_date' => '2026-09-01'],
            ['name' => 'Mining 2025', 'event_type' => 'Partnership Event', 'start_date' => '2025-09-01'],
            ['name' => 'Other 2026', 'event_type' => 'Partnership Event', 'start_date' => '2026-06-01'],
            ['name' => 'Mining DMC', 'event_type' => 'DMC Partnership Event', 'start_date' => '2024-01-01'],
        ]);

        $controller = new PartnershipEventVisitorController();
        $filtered = $controller->index(Request::create('/', 'GET', ['search' => 'Mining', 'year' => '2026']))->getData();
        $this->assertSame(['Mining 2026'], $filtered['events']->pluck('name')->all());
        $this->assertSame([2026, 2025], $filtered['years']->all());

        $reset = $controller->index(Request::create('/', 'GET'))->getData();
        $this->assertCount(3, $reset['events']);
    }
}
