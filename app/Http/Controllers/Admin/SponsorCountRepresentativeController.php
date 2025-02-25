<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payments\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SponsorCountRepresentativeController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun dari request atau default ke tahun sekarang
        $year = $request->get('year', now()->year);

        // Ambil daftar top 5 company berdasarkan jumlah attend untuk tahun yang dipilih
        $topCompanies = Payment::selectRaw('company.company_name as company, COUNT(DISTINCT payment.member_id) as count_attend')
            ->join('profiles', 'payment.member_id', '=', 'profiles.users_id')
            ->join('company', 'profiles.company_id', '=', 'company.id')
            ->whereYear('payment.created_at', $year)
            ->groupBy('company.company_name')
            ->orderBy('count_attend', 'desc')
            ->limit(5)
            ->pluck('company')
            ->toArray();

        // Ambil detail representative dari company top 5 beserta data event attendance untuk tahun yang dipilih
        $representatives = Payment::select(
            'users.name as representative_name',
            'company.company_name as company',
            'profiles.created_at as registration_time',
            'payment.created_at as attend_time',
            'events.name as event_name',
            'users_event.present as present'
        )
            ->join('profiles', 'payment.member_id', '=', 'profiles.users_id')
            ->join('users', 'payment.member_id', '=', 'users.id')
            ->join('company', 'profiles.company_id', '=', 'company.id')
            ->join('events', 'payment.events_id', '=', 'events.id')
            ->join('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'users.id');
            })
            ->whereYear('payment.created_at', $year)
            ->whereIn('company.company_name', $topCompanies)
            ->orderBy('company.company_name')
            ->orderBy('payment.created_at', 'desc')
            ->get();

        return view('admin.sponsor.representative.index', compact('representatives', 'year'));
    }
}
