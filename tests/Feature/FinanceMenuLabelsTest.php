<?php

namespace Tests\Feature;

use App\Models\CmsUser;
use App\Models\CmsRole;
use App\Support\CmsAccess;
use Tests\TestCase;

class FinanceMenuLabelsTest extends TestCase
{
    public function test_finance_labels_are_specific_to_the_finance_role(): void
    {
        $role = new CmsRole;
        $role->permissions = ['payments', 'sponsor_annual_report'];
        $finance = new CmsUser(['role' => 'finance_team', 'is_active' => true]);
        $finance->setRelation('cmsRole', $role);
        $other = new CmsUser(['role' => 'event_team', 'is_active' => true]);
        $other->setRelation('cmsRole', $role);

        $financeNav = CmsAccess::navigation($finance);
        $otherNav = CmsAccess::navigation($other);
        $this->assertSame('Event Payments', $financeNav['Finance']['payments']['label']);
        $this->assertSame('Sponsorship Financial Reports', $financeNav['Finance']['sponsors']['label']);
        $this->assertSame('Expenses Report', $otherNav['Finance']['payments']['label']);
        $this->assertSame('Renewal Status', $otherNav['Finance']['sponsors']['label']);
    }
}
