<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\LuckyDraw\LuckyDrawEntry;
use App\Models\LuckyDraw\LuckyDrawItem;
use App\Services\LuckyDrawService;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class LuckyDrawController extends Controller
{
    public function index()
    {
        // Tampilkan semua hadiah yang aktif di roda putar (termasuk yang chance 0%).
        // Item berpeluang 0% tetap dipajang di roda, namun algoritma pengundian
        // (LuckyDrawService::draw()) secara ketat hanya memilih item dengan chance > 0.
        $prizes = LuckyDrawItem::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'chance_percent', 'image']);

        return view('lucky-draw.index', compact('prizes'));
    }

    /**
     * Foto kartu nama diambil langsung dari kamera lalu auto-upload begitu
     * dipilih (lihat lucky-draw/index.blade.php) — sebelum tombol "Undi
     * Sekarang" diklik. Bikin entry duluan (lucky_draw_item_id & drawn_at
     * masih kosong) supaya id-nya bisa dititipkan balik ke frontend, lalu
     * disertakan sebagai `entry_id` saat benar-benar undi lewat store().
     */
    public function uploadBusinessCard(Request $request)
    {
        $request->validate([
            'business_card' => 'required|image|max:5120',
        ]);

        $entry = LuckyDrawEntry::create([
            'business_card_path' => $this->storeBusinessCard($request),
        ]);

        return response()->json([
            'success'           => true,
            'entry_id'          => $entry->id,
            'business_card_url' => asset($entry->business_card_path),
        ]);
    }

    public function store(Request $request)
    {
        $spinMode = $request->input('spin_mode', 'anonymous');

        if ($spinMode === 'required') {
            if ($request->input('capture_mode') === 'card') {
                $request->validate([
                    'entry_id' => 'required|integer|exists:lucky_draw_entries,id',
                ], [
                    'entry_id.required' => 'A business card photo is required before drawing.',
                ]);
            } else {
                $request->validate([
                    'name'  => 'required|string|max:255',
                    'phone' => 'required|string|max:30',
                ], [
                    'name.required'  => 'Participant full name is required.',
                    'phone.required' => 'Phone number is required.',
                ]);
            }
        }

        $request->validate([
            'entry_id'      => 'nullable|integer|exists:lucky_draw_entries,id',
            'name'          => 'nullable|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'job_title'     => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:255',
            'business_card' => 'nullable|image|max:5120',
        ]);

        $prize = LuckyDrawService::draw();

        // entry_id ada = kartu nama sudah keupload duluan lewat
        // uploadBusinessCard(); tinggal dilengkapi hasil undinya, bukan bikin
        // baris baru.
        $entry = $request->filled('entry_id')
            ? LuckyDrawEntry::findOrFail($request->entry_id)
            : new LuckyDrawEntry();

        $participantName = $request->filled('name')
            ? $request->name
            : ($spinMode === 'anonymous' ? 'Anonymous' : null);

        $entry->lucky_draw_item_id = $prize ? $prize->id : null;
        $entry->name               = $participantName;
        $entry->company_name       = $request->company_name;
        $entry->job_title          = $request->job_title;
        $entry->phone              = $request->phone;
        $entry->email              = $request->email;
        $entry->drawn_at           = now();

        // Fallback kalau JS gagal jalan: form disubmit normal dengan file
        // business_card langsung, tanpa lewat uploadBusinessCard() dulu.
        if (!$entry->business_card_path) {
            $entry->business_card_path = $this->storeBusinessCard($request);
        }

        $entry->save();

        $result = [
            'success'     => 'Thanks for joining the Lucky Draw!',
            'prize'       => $prize ? $prize->name : null,
            'prize_id'    => $prize ? $prize->id : null,
            'prize_image' => $prize && $prize->image ? asset($prize->image) : null,
        ];

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result);
    }

    /**
     * Lightweight ping to verify network connectivity from client.
     */
    public function ping()
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Synchronize a lucky draw entry drawn/stored offline at the venue.
     */
    public function syncOffline(Request $request)
    {
        $request->validate([
            'name'                => 'nullable|string|max:255',
            'company_name'        => 'nullable|string|max:255',
            'job_title'           => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:30',
            'email'               => 'nullable|email|max:255',
            'lucky_draw_item_id'  => 'nullable|integer|exists:lucky_draw_items,id',
            'prize_name'          => 'nullable|string|max:255',
            'drawn_at'            => 'nullable|string',
            'business_card'       => 'nullable|image|max:10240',
        ]);

        $itemId = $request->input('lucky_draw_item_id');
        if (!$itemId && $request->filled('prize_name')) {
            $matchedItem = LuckyDrawItem::where('name', $request->prize_name)->first();
            if ($matchedItem) {
                $itemId = $matchedItem->id;
            }
        }

        $drawnAt = now();
        if ($request->filled('drawn_at')) {
            try {
                $drawnAt = \Carbon\Carbon::parse($request->drawn_at);
            } catch (\Exception $e) {
                $drawnAt = now();
            }
        }

        $participantName = $request->filled('name')
            ? $request->name
            : ($request->input('spin_mode') === 'anonymous' ? 'Anonymous' : null);

        $entry = new LuckyDrawEntry();
        $entry->lucky_draw_item_id = $itemId;
        $entry->name               = $participantName;
        $entry->company_name       = $request->company_name;
        $entry->job_title          = $request->job_title;
        $entry->phone              = $request->phone;
        $entry->email              = $request->email;
        $entry->drawn_at           = $drawnAt;
        $entry->business_card_path = $this->storeBusinessCard($request);
        $entry->save();

        return response()->json([
            'success'   => true,
            'entry_id'  => $entry->id,
            'message'   => 'Offline entry successfully synced.',
        ]);
    }

    private function storeBusinessCard(Request $request): ?string
    {
        if (!$request->hasFile('business_card')) {
            return null;
        }

        $file = $request->file('business_card');
        $imageName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/lucky-draw/business-cards', $imageName);

        Image::make($file)
            ->resize(1600, 1600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save(storage_path('app/public/lucky-draw/business-cards/' . $imageName));

        return '/storage/lucky-draw/business-cards/' . $imageName;
    }

    /**
     * Nonaktifkan item hadiah dari roda undian setelah dimenangkan.
     * Data tetap tersimpan di database untuk menjaga riwayat pemenang,
     * hanya kolom is_active diset false agar tidak lagi muncul di roda.
     */
    public function deactivateItem($id)
    {
        $item = LuckyDrawItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Hadiah tidak ditemukan.',
            ], 404);
        }

        $item->is_active = false;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => "Hadiah {$item->name} telah dinonaktifkan dari roda.",
            'item_id' => $item->id,
            'name'    => $item->name,
        ]);
    }
}

