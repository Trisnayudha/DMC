<?php

namespace App\Exports;

use App\Models\Sponsors\SponsorRenewal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SponsorRenewalsExport implements FromCollection
{
    protected $year;
    protected $state;

    public function __construct($year = null, $state = null)
    {
        $this->year = $year;
        $this->state = $state;
    }

    public function collection()
    {
        $query = SponsorRenewal::with('sponsor');

        if ($this->year) {
            $query->where('renewal_year', $this->year);
        }

        if ($this->state === 'renewed') {
            $query->where('renewal_status', 'renewed');
        }

        return $query->get()->map(function ($item) {
            return [
                'Sponsor Name'    => $item->sponsor->name ?? '-',
                'Year'            => $item->renewal_year,
                'Contract Start'  => $item->contract_start,
                'Contract End'    => $item->contract_end,
                'Package'         => $item->package,
                'Renewal Status'  => $item->renewal_status,
                'Current'         => $item->is_current ? 'Yes' : 'No',
            ];
        });
    }
}
