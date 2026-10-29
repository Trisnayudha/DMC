<?php

namespace Tests\Unit;

use App\Models\MemberLeadFollowUp;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class MemberLeadDeadlineTest extends TestCase
{
    public function test_sponsor_kit_deadline_counts_calendar_hours()
    {
        $start = Carbon::parse('2026-10-30 10:00:00'); // Friday
        $this->assertSame('2026-11-01 10:00:00', MemberLeadFollowUp::deadlineFor('sponsorkit', $start)->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-30 10:00:00', $start->format('Y-m-d H:i:s'));
    }

    public function test_follow_up_deadlines_skip_weekends()
    {
        $friday = Carbon::parse('2026-10-30 10:00:00');
        $this->assertSame('2026-11-04 10:00:00', MemberLeadFollowUp::deadlineFor('follow_up_1', $friday)->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-06 10:00:00', MemberLeadFollowUp::deadlineFor('follow_up_2', $friday)->format('Y-m-d H:i:s'));
        $this->assertNull(MemberLeadFollowUp::deadlineFor(null, $friday));
    }
}
