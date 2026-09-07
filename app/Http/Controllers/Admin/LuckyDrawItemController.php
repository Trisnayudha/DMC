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
