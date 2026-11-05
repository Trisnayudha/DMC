<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\NewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewsMonthlyStatsTest extends TestCase
{
    public function test_monthly_stats_exclude_other_years_and_months(): void
    {
        config(['database.default' => 'news_stats_test', 'database.connections.news_stats_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('news', function ($table) {
            $table->id();
            $table->string('type');
            $table->integer('views');
            $table->timestamp('created_at');
        });
        DB::table('news')->insert([
            ['type' => 'default', 'views' => 10, 'created_at' => '2026-11-01 00:00:00'],
            ['type' => 'default', 'views' => 20, 'created_at' => '2026-11-30 23:59:59'],
            ['type' => 'default', 'views' => 40, 'created_at' => '2025-11-05 00:00:00'],
            ['type' => 'default', 'views' => 80, 'created_at' => '2026-10-31 23:59:59'],
            ['type' => 'default', 'views' => 160, 'created_at' => '2026-12-01 00:00:00'],
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-05 10:30:00'));
        try {
            $data = (new NewsController())->index(Request::create('/'))->getData();
            $this->assertSame(2, $data['totalView']);
            $this->assertEquals(30, $data['countView']);
        } finally {
            Carbon::setTestNow();
        }
    }
}
