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
        $sponsorList = Sponsor::orderBy('name')->get();

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
            ->whereYear('payment.created_at', $year)
            // Hanya menampilkan data yang benar-benar attend
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
        $nonAttendSponsors = Sponsor::select('sponsors.name as company')
            ->leftJoin('payment', function ($join) use ($year) {
                $join->on('sponsors.id', '=', 'payment.sponsor_id')
                    ->whereYear('payment.created_at', $year);
            })
            ->leftJoin('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'payment.member_id');
            })
            ->groupBy('sponsors.id', 'sponsors.name')
            // Pastikan tidak ada data dengan present != NULL
            ->havingRaw('COALESCE(SUM(CASE WHEN users_event.present IS NOT NULL THEN 1 ELSE 0 END), 0) = 0')
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
