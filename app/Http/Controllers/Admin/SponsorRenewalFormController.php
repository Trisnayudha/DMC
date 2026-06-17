<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ScrapeHelper;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use Barryvdh\DomPDF\Facade\Pdf;

class SponsorRenewalFormController extends Controller
{
    private function buildData(int $sponsorId): array
    {
        $sponsor = Sponsor::with(['firstPic', 'currentRenewal'])->findOrFail($sponsorId);

        $packageBenefits = PackageBenefit::with('benefit')
            ->where('package_name', $sponsor->package)
            ->get()
            ->groupBy(function ($pb) {
                return $pb->benefit->category ?? 'Other';
            });

        $kursRate = null;
        try {
            $kursRate = ScrapeHelper::scrapeExchangeRate();
        } catch (\Exception $e) {
            // silently fallback to null
        }

        return [
            'sponsor'         => $sponsor,
            'renewal'         => $sponsor->currentRenewal,
            'packageBenefits' => $packageBenefits,
            'kursRate'        => $kursRate,
            'logoPath'        => public_path('image/logo-dmc-cci3.png'),
        ];
    }

    public function preview(int $sponsorId)
    {
        return view('admin.sponsor.renewal-form', $this->buildData($sponsorId));
    }

    public function generate(int $sponsorId)
    {
        $data = $this->buildData($sponsorId);

        $pdf = Pdf::setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ])
            ->loadView('admin.sponsor.renewal-form', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'renewal-form-' . str_replace(' ', '-', strtolower($data['sponsor']->name)) . '.pdf';

        return $pdf->download($filename);
    }
}
