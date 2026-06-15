<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SponsorContactDirectoryExport;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Services\Sponsors\SponsorContactRowBuilder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SponsorContactDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $package = $request->get('package', '');

        $sponsors = $this->getSponsorsWithContactRows($search, $package);

        $totalPics     = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'pic')->count(); });
        $totalBillings = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'billing')->count(); });
        $totalReps     = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'representative')->count(); });

        return view('admin.sponsor.contact_directory', compact(
            'sponsors', 'search', 'package',
            'totalPics', 'totalBillings', 'totalReps'
        ));
    }

    public function export(Request $request)
    {
        $role    = $request->get('role', 'all');
        $search  = $request->get('search', '');
        $package = $request->get('package', '');

        $sponsors = $this->getSponsorsWithContactRows($search, $package);

        $suffix   = $role === 'all' ? 'contacts' : $role;
        $filename = 'sponsor-contact-directory-' . $suffix . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SponsorContactDirectoryExport($sponsors, $role), $filename);
    }

    private function getSponsorsWithContactRows($search, $package)
    {
        $query = Sponsor::with(['pics', 'billings', 'representatives', 'members'])
            ->where('status', 'publish')
            ->orderBy('name');

        if ($package) {
            $query->where('package', $package);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $sponsors = $query->get();
        $builder  = new SponsorContactRowBuilder();

        foreach ($sponsors as $sponsor) {
            $sponsor->contactRows = $builder->build($sponsor);
        }

        return $sponsors;
    }
}
