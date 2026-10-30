<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** Inquiry Database: pesan Contact Us dari web & apps (tabel contact_inquiry). */
class ContactInquiryController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $filters = [
            'source'    => $request->get('source'),
            'date_from' => $request->get('date_from'),
            'date_to'   => $request->get('date_to'),
        ];

        // Sebelum migration contact_inquiry dijalankan, halaman tetap terbuka (kosong).
        $ready = Schema::hasTable('contact_inquiry');
        $list = $ready ? $this->inquiryQuery($filters)->get() : collect();

        return view('admin.inquiries.index', [
            'list'    => $list,
            'filters' => $filters,
            'ready'   => $ready,
            'stats'   => [
                'total'   => $list->count(),
                'web'     => $list->where('source', ContactInquiry::SOURCE_WEB)->count(),
                'apps'    => $list->where('source', ContactInquiry::SOURCE_APPS)->count(),
                'members' => $list->filter(function ($i) {
                    return optional($i->user)->status_member === 'active';
                })->count(),
            ],
        ]);
    }

    private function inquiryQuery(array $filters)
    {
        // Form Contact Us tidak menanyakan company/job title; diambil dari profil user yang tertaut.
        $query = ContactInquiry::with('user.profile.company')->orderBy('created_at', 'desc');
        if (in_array($filters['source'], [ContactInquiry::SOURCE_WEB, ContactInquiry::SOURCE_APPS], true)) {
            $query->where('source', $filters['source']);
        }
        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        return $query;
    }
}
