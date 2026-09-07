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
        // Only the names are needed — the wheel's slice labels are the only
        // place prizes are shown now (no separate gallery/photos on this page).
        $prizes = LuckyDrawItem::active()
            ->where('chance_percent', '>', 0)
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

        $entry->lucky_draw_item_id = $prize ? $prize->id : null;
        $entry->name               = $request->name;
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
            'prize_image' => $prize && $prize->image ? asset($prize->image) : null,
        ];

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result);
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
}
