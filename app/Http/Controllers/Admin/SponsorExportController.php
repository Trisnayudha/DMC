<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SponsorExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SponsorExportController extends Controller
{
    public function export()
    {
        // Format nama file: "Update Sponsor Detail_Tanggal_Waktu.xlsx"
        $filename = "Update Sponsor Detail_" . Carbon::now()->format('Y-m-d_H-i-s') . ".xlsx";
        return Excel::download(new SponsorExport, $filename);
    }
}
