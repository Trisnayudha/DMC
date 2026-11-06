<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Kelola link pendaftaran member per channel (member_sources). Link publiknya
 * {frontend}/member/{CATEGORY}/{CODE}; member yang daftar lewat link itu
 * tersimpan dengan users.source = 'CATEGORY/CODE' (lihat
 * AuthController::resolveWebSource). Karena itu category/code yang sudah
 * dipakai pendaftar tidak boleh diubah atau dihapus — breakdown source di
 * admin/users akan kehilangan nama event-nya.
 */
class MemberSourceController extends Controller
{
    const FORM_TYPES = [
        'individual' => 'Individual',
        'corporate'  => 'Corporate',
    ];

    /**
     * Category yang dikenali UsersController::normalizeSourceKey(). Category
     * lain tetap valid, tapi tidak masuk bucket mana pun di chart "Members by Source".
     */
    const KNOWN_CATEGORIES = [
        'EP'  => 'Partnership Event',
        'E'   => 'DMC Event',
        'REF' => 'Referral',
    ];

    public function index()
    {
        $sources = MemberSource::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('admin.member_source.index', [
            'sources'         => $sources,
            'registrations'   => $this->registrationCounts($sources),
            'frontendUrl'     => rtrim(config('app.frontend_url'), '/'),
            'formTypes'       => self::FORM_TYPES,
            'knownCategories' => self::KNOWN_CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        MemberSource::create($data);

        return back()->with('success', 'Source "' . $data['category'] . '/' . $data['code'] . '" was added successfully.');
    }

    public function update(Request $request, $id)
    {
        $source = MemberSource::findOrFail($id);
        $data   = $this->validated($request, $source);

        $keyChanged = $data['category'] !== strtoupper($source->category)
            || $data['code'] !== strtoupper($source->code);

        if ($keyChanged && $this->registrationCount($source) > 0) {
            throw ValidationException::withMessages([
                'code' => 'Category/Code cannot be changed because members have registered through this link. Create a new source instead.',
            ]);
        }

        $source->update($data);

        return back()->with('success', 'Source "' . $source->category . '/' . $source->code . '" was updated successfully.');
    }

    public function toggleActive($id)
    {
        $source = MemberSource::findOrFail($id);
        $source->is_active = !$source->is_active;
        $source->save();

        $status = $source->is_active ? 'activated' : 'deactivated';

        return back()->with('success', 'Source "' . $source->category . '/' . $source->code . '" was ' . $status . ' successfully.');
    }

    public function destroy($id)
    {
        $source = MemberSource::findOrFail($id);

        if ($this->registrationCount($source) > 0) {
            return back()->with(
                'error',
                'Source "' . $source->category . '/' . $source->code . '" cannot be deleted because members have registered through it. Deactivate it to disable the link while preserving its history.'
            );
        }

        $source->delete();

        return back()->with('success', 'Source deleted successfully.');
    }

    /**
     * Category & code di-uppercase supaya konsisten dengan data lama (EP/MI2026)
     * dan formatnya harus lolos regex di AuthController::resolveWebSource(),
     * kalau tidak link-nya valid di FE tapi source-nya jatuh ke 'Website'.
     */
    private function validated(Request $request, ?MemberSource $source = null): array
    {
        $request->merge([
            'category' => strtoupper(trim((string) $request->category)),
            'code'     => strtoupper(trim((string) $request->code)),
        ]);

        $request->validate([
            'category'  => ['required', 'max:20', 'regex:/^[A-Z]+$/'],
            'code'      => [
                'required',
                'max:50',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('member_sources', 'code')
                    ->where('category', $request->category)
                    ->ignore($source ? $source->id : null),
            ],
            'name'      => 'required|string|max:255',
            'form_type' => ['required', Rule::in(array_keys(self::FORM_TYPES))],
        ], [
            'category.regex' => 'Category may contain only letters (A–Z), without spaces or numbers.',
            'code.regex'     => 'Code may contain only letters and numbers (A–Z, 0–9), without spaces or symbols.',
            'code.unique'    => 'This Category/Code combination already exists.',
        ]);

        return [
            'category'  => $request->category,
            'code'      => $request->code,
            'name'      => $request->name,
            'form_type' => $request->form_type,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function registrationCount(MemberSource $source): int
    {
        return User::whereRaw('LOWER(source) = ?', [strtolower($source->category . '/' . $source->code)])->count();
    }

    /**
     * Jumlah pendaftar per source dalam satu query, di-key 'category/code'
     * lowercase — users.source bisa beda kapitalisasi (ep/mi2026 vs EP/MI2026).
     */
    private function registrationCounts($sources): array
    {
        if ($sources->isEmpty()) {
            return [];
        }

        $keys = $sources->map(function ($source) {
            return strtolower($source->category . '/' . $source->code);
        })->all();

        $counts = [];
        $rows = User::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(source)'), $keys)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->get();

        foreach ($rows as $row) {
            $key = strtolower($row->source);
            $counts[$key] = ($counts[$key] ?? 0) + (int) $row->total;
        }

        return $counts;
    }
}
