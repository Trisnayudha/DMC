<?php

namespace Tests\Feature;

use Tests\TestCase;

class SponsorRenewalFormTotalsTest extends TestCase
{
    public function test_no_vat_totals_show_idr_before_usd(): void
    {
        $sponsor = new \App\Models\Sponsors\Sponsor(['name' => 'Example', 'package' => 'gold']);
        $form = new \App\Models\Sponsors\SponsorRenewalForm(['amount_usd' => 3500, 'amount_idr' => 62520500, 'vat_percent' => 0]);
        $html = view('admin.sponsor.renewal-form', [
            'sponsor' => $sponsor, 'renewalForm' => $form, 'renewal' => null,
            'contactOverride' => (object) ['name' => 'Contact', 'title' => null, 'phone' => null, 'email' => null],
            'kursRate' => 17863, 'packageBenefits' => [],
        ])->render();
        $this->assertTrue(strpos($html, 'Total In IDR') < strpos($html, 'Total In USD'));
        $this->assertStringContainsString('>62.520.500<', $html);
        $this->assertStringContainsString('>3.500<', $html);
        $this->assertStringNotContainsString('Grand Total IDR', $html);
    }

    public function test_usd_vat_totals_follow_the_requested_order(): void
    {
        $sponsor = new \App\Models\Sponsors\Sponsor(['name' => 'Example', 'package' => 'gold']);
        $form = new \App\Models\Sponsors\SponsorRenewalForm(['amount_usd' => 7500, 'amount_idr' => 133972500, 'vat_percent' => 11]);
        $html = view('admin.sponsor.renewal-form', [
            'sponsor' => $sponsor, 'renewalForm' => $form, 'renewal' => null,
            'contactOverride' => (object) ['name' => 'Contact', 'title' => null, 'phone' => null, 'email' => null],
            'kursRate' => 17863, 'packageBenefits' => [],
        ])->render();
        $this->assertTrue(strpos($html, 'Total In USD') < strpos($html, 'VAT 11%'));
        $this->assertTrue(strpos($html, 'VAT 11%') < strpos($html, 'Grand Total USD'));
        $this->assertTrue(strpos($html, 'Grand Total USD') < strpos($html, 'Grand Total IDR'));
        $this->assertStringContainsString('>825<', $html);
        $this->assertStringContainsString('>8.325<', $html);
        $this->assertStringContainsString('>148.709.475<', $html);
    }
}
