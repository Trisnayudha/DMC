<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use Illuminate\Http\Request;

class SponsorContactDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $package = $request->get('package', '');

        $query = Sponsor::with(['pics', 'representatives', 'members'])
            ->where('status', 'publish')
            ->orderBy('name');

        if ($package) {
            $query->where('package', $package);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $sponsors = $query->get();

        $totalPics  = $sponsors->sum(function ($s) { return $s->pics->count(); });
        $totalReps  = $sponsors->sum(function ($s) { return $s->representatives->count(); });
        $totalMembers = $sponsors->sum(function ($s) { return $s->members->count(); });

        return view('admin.sponsor.contact_directory', compact(
            'sponsors', 'search', 'package',
            'totalPics', 'totalReps', 'totalMembers'
        ));
    }
}
