<?php

namespace Tests\Unit;

use App\Services\Mailchimp\UnsubscribeCampaignStatistics;
use PHPUnit\Framework\TestCase;

class UnsubscribeCampaignStatisticsTest extends TestCase
{
    public function test_it_ranks_campaigns_and_keeps_unknown_and_admin_contacts_in_the_denominator()
    {
        $contacts = collect([
            (object) ['is_admin_unsub' => false, 'unsub_campaign_id' => 'one', 'unsub_campaign_title' => 'Newsletter'],
            (object) ['is_admin_unsub' => false, 'unsub_campaign_id' => 'one', 'unsub_campaign_title' => 'Newsletter'],
            (object) ['is_admin_unsub' => false, 'unsub_campaign_id' => 'two', 'unsub_campaign_title' => 'Newsletter'],
            (object) ['is_admin_unsub' => false, 'unsub_campaign_id' => null, 'unsub_campaign_title' => null],
            (object) ['is_admin_unsub' => true, 'unsub_campaign_id' => null, 'unsub_campaign_title' => null],
        ]);

        $stats = (new UnsubscribeCampaignStatistics())->calculate($contacts);

        $this->assertSame(5, $stats['total']);
        $this->assertSame(3, $stats['attributed']);
        $this->assertSame(1, $stats['unknown']);
        $this->assertSame(1, $stats['admin']);
        $this->assertCount(2, $stats['campaigns']);
        $this->assertSame('one', $stats['campaigns'][0]['id']);
        $this->assertSame(40.0, $stats['campaigns'][0]['percentage']);
        $this->assertSame(20.0, $stats['campaigns'][1]['percentage']);
    }

    public function test_empty_contacts_have_no_campaigns_or_division_error()
    {
        $stats = (new UnsubscribeCampaignStatistics())->calculate(collect());
        $this->assertSame(0, $stats['total']);
        $this->assertCount(0, $stats['campaigns']);
    }
}
