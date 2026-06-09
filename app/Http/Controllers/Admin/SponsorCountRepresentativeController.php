<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payments\Payment;
use App\Models\Sponsors\Sponsor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SponsorCountRepresentativeController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun dari request atau default ke tahun sekarang
        $year = $request->get('year', now()->year);

        // Ambil sponsor yang dipilih untuk filter representative yang attend
        $filterSponsor = $request->get('company', '');

        // Dapatkan daftar sponsor untuk dropdown filter
        $sponsorList = Sponsor::where('status', 'publish')->orderBy('name')->get();

        /*
         |---------------------------------------------------------------------------------
         | Ambil Data Representative yang Attend (present != NULL)
         |---------------------------------------------------------------------------------
         */
        $representativesQuery = Payment::select(
            'users.name as representative_name',
            'sponsors.name as company',
            'payment.created_at as attend_time',
            'events.name as event_name',
            'users_event.present as present'
        )
            ->join('users', 'payment.member_id', '=', 'users.id')
            ->join('sponsors', 'payment.sponsor_id', '=', 'sponsors.id')
            ->join('events', 'payment.events_id', '=', 'events.id')
            ->join('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'users.id');
            })
            ->where('sponsors.status', 'publish')
            ->whereYear('payment.created_at', $year)
            ->whereNotNull('users_event.present');

        // Jika filter sponsor dipilih, tambahkan kondisi
        if (!empty($filterSponsor)) {
            $representativesQuery->where('sponsors.name', $filterSponsor);
        }

        $representatives = $representativesQuery
            ->orderBy('sponsors.name')
            ->orderBy('payment.created_at', 'desc')
            ->get();

        /*
         |---------------------------------------------------------------------------------
         | Ambil Data Sponsor yang Representativenya Tidak Pernah Attend
         |---------------------------------------------------------------------------------
         */
        // Sponsor IDs yang sudah ada attend di tahun ini
        $attendedSponsorIds = \Illuminate\Support\Facades\DB::table('payment')
            ->join('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'payment.member_id');
            })
            ->whereYear('payment.created_at', $year)
            ->whereNotNull('users_event.present')
            ->whereNotNull('payment.sponsor_id')
            ->pluck('payment.sponsor_id')
            ->unique();

        $nonAttendSponsors = Sponsor::with(['pics', 'representatives', 'members'])
            ->where('status', 'publish')
            ->whereNotIn('id', $attendedSponsorIds)
            ->orderBy('name')
            ->get();

        return view('admin.sponsor.representative.index', [
            'representatives' => $representatives,
            'sponsorList'     => $sponsorList,
            'year'            => $year,
            'filterSponsor'   => $filterSponsor,
            'nonAttendSponsors' => $nonAttendSponsors,
        ]);
    }
}
