<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** Sponsor Log: semua perubahan data sponsor & submenu-nya (tabel sponsor_logs). */
class SponsorLogController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $filters = [
            'sponsor_id' => $request->get('sponsor_id'),
            'subject'    => $request->get('subject'),
            'action'     => $request->get('action'),
            'user'       => $request->get('user'),
            'date_from'  => $request->get('date_from'),
            'date_to'    => $request->get('date_to'),
        ];

        // Sebelum migration sponsor_logs dijalankan, halaman tetap terbuka (kosong).
        $ready = Schema::hasTable('sponsor_logs');

        return view('admin.sponsor.logs.index', [
            'logs'     => $ready ? $this->logQuery($filters)->paginate(50)->withQueryString() : null,
            'filters'  => $filters,
            'ready'    => $ready,
            'sponsors' => Sponsor::orderBy('name')->get(['id', 'name', 'branding_name']),
            'subjects' => $ready ? SponsorLog::distinct()->orderBy('subject')->pluck('subject') : collect(),
            'users'    => $ready ? SponsorLog::whereNotNull('cms_user_name')->distinct()->orderBy('cms_user_name')->pluck('cms_user_name') : collect(),
        ]);
    }

    private function logQuery(array $filters)
    {
        $query = SponsorLog::with('sponsor:id,name,branding_name')->orderByDesc('id');
        foreach (['sponsor_id', 'subject', 'action'] as $field) {
            if ($filters[$field]) {
                $query->where($field, $filters[$field]);
            }
        }
        if ($filters['user']) {
            $query->where('cms_user_name', $filters['user']);
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
