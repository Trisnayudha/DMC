<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\MasterDatabaseController;
use PHPUnit\Framework\TestCase;

class MasterDatabaseCategoryTest extends TestCase
{
    public function test_categories_prioritize_membership_over_event_history(): void
    {
        $row = (object) ['source' => 'users', 'member_status' => 'active', 'user_source' => 'DMC Event', 'member_registered_at' => null, 'has_event' => 1];
        $this->assertSame('active', MasterDatabaseController::categoryFor($row));
        $row->member_status = 'pending';
        $this->assertSame('pending', MasterDatabaseController::categoryFor($row));
        $row->member_status = 'declined';
        $this->assertSame('declined', MasterDatabaseController::categoryFor($row));
        $row->member_status = 'deactivated';
        $this->assertSame('deactivated', MasterDatabaseController::categoryFor($row));
        $row->member_status = null;
        $this->assertSame('event', MasterDatabaseController::categoryFor($row));
        $row->member_registered_at = '2026-01-01';
        $this->assertSame('other', MasterDatabaseController::categoryFor($row));
        $row->user_source = 'Website';
        $row->has_event = 0;
        $this->assertSame('other', MasterDatabaseController::categoryFor($row));
        $row->source = 'xtwp';
        $this->assertSame('intake', MasterDatabaseController::categoryFor($row));
    }
}
