<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyDraw\LuckyDrawEntry;
use Illuminate\Http\Request;

/**
 * Riwayat undian Lucky Draw — siapa saja yang ikut & hadiah yang didapat.
 * Read-only: data penerima (opsional) diisi sendiri oleh pengunjung lewat
 * halaman publik /lucky-draw.
 */
class LuckyDrawEntryController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $result = $request->get('result', 'all');
        $search = trim((string) $request->get('search'));

        $query = LuckyDrawEntry::with('item')->orderBy('created_at', 'desc');

        if ($result === 'won') {
            $query->won();
        } elseif ($result === 'lost') {
            $query->lost();
        } elseif ($result === 'pending') {
            $query->pending();
        } else {
            $result = 'all';
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $list = $query->paginate(50)->withQueryString();

        $countTotal   = LuckyDrawEntry::count();
        $countWon     = LuckyDrawEntry::won()->count();
        $countLost    = LuckyDrawEntry::lost()->count();
        $countPending = LuckyDrawEntry::pending()->count();

        return view('admin.lucky_draw.entries.index', compact(
            'list',
            'result',
            'search',
            'countTotal',
            'countWon',
            'countLost',
            'countPending'
        ));
    }
}
