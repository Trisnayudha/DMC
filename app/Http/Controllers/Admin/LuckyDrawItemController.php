<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyDraw\LuckyDrawItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

/**
 * Kelola katalog hadiah Lucky Draw beserta chance % masing-masing.
 * Total chance_percent semua item is_active tidak boleh melebihi 100% —
 * sisanya otomatis jadi peluang "zonk" di LuckyDrawService::draw().
 */
class LuckyDrawItemController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index()
    {
        $items      = LuckyDrawItem::orderBy('sort_order')->orderBy('id')->get();
        $usedChance = round((float) LuckyDrawItem::active()->sum('chance_percent'), 2);

        return view('admin.lucky_draw.items.index', compact('items', 'usedChance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'image'          => 'nullable|image|max:5120',
            'chance_percent' => 'required|numeric|min:0|max:100',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $this->assertChanceBudget($request);

        LuckyDrawItem::create([
            'name'           => $request->name,
            'image'          => $this->storeImage($request),
            'chance_percent' => $request->chance_percent,
            'is_active'      => $request->boolean('is_active'),
            'sort_order'     => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Prize added successfully.');
    }

    public function update(Request $request, $id)
    {
        $item = LuckyDrawItem::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'image'          => 'nullable|image|max:5120',
            'chance_percent' => 'required|numeric|min:0|max:100',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $this->assertChanceBudget($request, $item->id);

        $item->name           = $request->name;
        $item->chance_percent = $request->chance_percent;
        $item->is_active      = $request->boolean('is_active');
        $item->sort_order     = $request->sort_order ?? 0;

        $newImage = $this->storeImage($request);
        if ($newImage) {
            $item->image = $newImage;
        }

        $item->save();

        return redirect()->back()->with('success', 'Prize updated successfully.');
    }

    public function destroy($id)
    {
        $item = LuckyDrawItem::findOrFail($id);

        if ($item->entries()->exists()) {
            return redirect()->back()->with(
                'error',
                "This prize has already been awarded at least once, so it can't be deleted. Deactivate it instead to keep the winner history intact."
            );
        }

        $item->delete();

        return redirect()->back()->with('success', 'Prize deleted successfully.');
    }

    /**
     * Update chance_percent dan/atau is_active via AJAX untuk kontrol cepat di mobile/desktop.
     */
    public function quickUpdate(Request $request, $id)
    {
        $item = LuckyDrawItem::findOrFail($id);

        $request->validate([
            'chance_percent' => 'nullable|numeric|min:0|max:100',
            'is_active'      => 'nullable|boolean',
        ]);

        $newActive = $request->has('is_active') ? $request->boolean('is_active') : $item->is_active;
        $newChance = $request->has('chance_percent') ? round((float) $request->chance_percent, 2) : $item->chance_percent;

        // Validasi budget jika item aktif
        if ($newActive) {
            $usedByOthers = (float) LuckyDrawItem::active()
                ->where('id', '!=', $item->id)
                ->sum('chance_percent');

            $remaining = round(100 - $usedByOthers, 2);

            if ($newChance > $remaining) {
                return response()->json([
                    'success'   => false,
                    'message'   => "Total peluang aktif melebihi 100%. Sisa kuota tersedia: {$remaining}%.",
                    'remaining' => $remaining,
                    'used'      => round($usedByOthers + (float) $item->chance_percent, 2),
                ], 422);
            }
        }

        if ($request->has('is_active')) {
            $item->is_active = $newActive;
        }
        if ($request->has('chance_percent')) {
            $item->chance_percent = $newChance;
        }
        $item->save();

        $totalActive = round((float) LuckyDrawItem::active()->sum('chance_percent'), 2);
        $remaining = round(max(0, 100 - $totalActive), 2);

        return response()->json([
            'success'   => true,
            'message'   => "{$item->name} berhasil diperbarui.",
            'item'      => [
                'id'             => $item->id,
                'name'           => $item->name,
                'chance_percent' => $item->chance_percent,
                'is_active'      => $item->is_active,
            ],
            'used'      => $totalActive,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Aksi batch cepat:
     * 1. 'force_win': Menjadikan item terpilih 100% dan meng-nolkan item lainnya.
     * 2. 'balance': Membagi rata 100% ke seluruh item aktif.
     */
    public function quickBatch(Request $request)
    {
        $request->validate([
            'action'  => 'required|in:force_win,balance',
            'item_id' => 'nullable|exists:lucky_draw_items,id',
        ]);

        if ($request->action === 'force_win') {
            $targetId = $request->item_id;
            if (!$targetId) {
                return response()->json(['success' => false, 'message' => 'Item target tidak ditemukan.'], 422);
            }

            $target = LuckyDrawItem::findOrFail($targetId);
            $target->update([
                'is_active'      => true,
                'chance_percent' => 100,
            ]);

            // Set semua item lainnya chance_percent = 0
            LuckyDrawItem::where('id', '!=', $targetId)->update([
                'chance_percent' => 0,
            ]);

            $totalActive = 100.0;
            $remaining = 0.0;

            return response()->json([
                'success'   => true,
                'message'   => "Mode 'Pasti Keluar' aktif: {$target->name} disetel 100%. Hadiah lain dinolkan.",
                'used'      => $totalActive,
                'remaining' => $remaining,
                'items'     => LuckyDrawItem::orderBy('sort_order')->orderBy('id')->get(['id', 'chance_percent', 'is_active']),
            ]);
        }

        if ($request->action === 'balance') {
            $activeItems = LuckyDrawItem::active()->get();
            $count = $activeItems->count();

            if ($count === 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada hadiah yang aktif untuk dibagi rata.'], 422);
            }

            $equalChance = round(100 / $count, 2);
            $remainder = round(100 - ($equalChance * $count), 2);

            foreach ($activeItems as $idx => $actItem) {
                $chance = $equalChance + ($idx === 0 ? $remainder : 0);
                $actItem->update(['chance_percent' => $chance]);
            }

            $totalActive = 100.0;
            $remaining = 0.0;

            return response()->json([
                'success'   => true,
                'message'   => "Peluang 100% berhasil dibagi rata ke {$count} hadiah aktif.",
                'used'      => $totalActive,
                'remaining' => $remaining,
                'items'     => LuckyDrawItem::orderBy('sort_order')->orderBy('id')->get(['id', 'chance_percent', 'is_active']),
            ]);
        }
    }

    /**
     * Total chance_percent semua item is_active (di luar item yang sedang
     * diedit) ditambah nilai baru tidak boleh melebihi 100%.
     */
    private function assertChanceBudget(Request $request, ?int $excludeId = null): void
    {
        if (!$request->boolean('is_active')) {
            return;
        }

        $usedByOthers = (float) LuckyDrawItem::active()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('chance_percent');

        $remaining = round(100 - $usedByOthers, 2);

        if ((float) $request->chance_percent > $remaining) {
            throw ValidationException::withMessages([
                'chance_percent' => "Total chance across all active prizes can't exceed 100%. Remaining quota: {$remaining}%.",
            ]);
        }
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/lucky-draw/items', $imageName);

        Image::make($image)
            ->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save(storage_path('app/public/lucky-draw/items/' . $imageName));

        return '/storage/lucky-draw/items/' . $imageName;
    }
}
