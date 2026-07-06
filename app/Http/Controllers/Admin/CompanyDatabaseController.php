<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CompanyDatabaseExport;
use App\Exports\CompanyImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\CompanyDatabaseImport;
use App\Models\Company\CompanyModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class CompanyDatabaseController extends Controller
{
    /**
     * @var string[]
     */
    private array $syncFields = [
        'company_name',
        'is_verified',
        'prefix',
        'company_website',
        'company_category',
        'company_other',
        'address',
        'city',
        'portal_code',
        'prefix_office_number',
        'office_number',
        'full_office_number',
        'country',
    ];
    /**
     * company_other tidak dihitung sebagai indikator kelengkapan
     * karena hanya relevan kalau category = other.
     *
     * @var string[]
     */
    private array $completenessFields = [
        'company_name',
        'prefix',
        'company_website',
        'company_category',
        'address',
        'city',
        'portal_code',
        'full_office_number',
        'country',
    ];

    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function export(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $scope  = $this->normalizeScope((string) $request->query('scope', 'all'));

        $groups = $this->applyScope($this->buildCompanyGroups($search), $scope);

        $filename = 'company-database-' . $scope . '-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new CompanyDatabaseExport($groups), $filename);
    }

    public function downloadTemplate()
    {
        return Excel::download(new CompanyImportTemplate(), 'template-import-company.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new CompanyDatabaseImport();
        Excel::import($import, $request->file('file'));

        $message = "Import selesai. {$import->getUpdated()} record diupdate.";
        if ($import->getSkipped() > 0) {
            $message .= " {$import->getSkipped()} row dilewati.";
        }

        activity('company_database')
            ->causedBy(auth()->user())
            ->withProperties([
                'updated_records' => $import->getUpdated(),
                'skipped'         => $import->getSkipped(),
                'errors'          => $import->getErrors(),
            ])
            ->log('import');

        if (!empty($import->getErrors())) {
            return back()->with('success', $message)->with('import_errors', $import->getErrors());
        }

        return back()->with('success', $message);
    }

    public function logs(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $action = trim((string) $request->query('action', ''));

        $query = Activity::with('causer')
            ->where('log_name', 'company_database')
            ->latest();

        if ($action !== '') {
            $query->where('description', $action);
        }

        if ($search !== '') {
            $query->where('properties->company_name', 'like', '%' . $search . '%');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.company_database.logs', compact('logs', 'search', 'action'));
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $scope = $this->normalizeScope((string) $request->query('scope', 'need_sync'));

        $groups = $this->buildCompanyGroups($search);
        $list   = $this->applyScope($groups, $scope);

        $totalCompanies = $groups->count();
        $totalVerified  = $this->applyScope($groups, 'verified')->count();
        $verifiedPct    = $totalCompanies > 0 ? round($totalVerified / $totalCompanies * 100, 1) : 0;

        // Completeness distribution — all groups regardless of active filter/search
        $allGroups = $this->buildCompanyGroups('');
        $buckets = ['≤3' => 0, '4–6' => 0, '7–9' => 0, '10' => 0, '11' => 0];
        foreach ($allGroups as $g) {
            $s = $g->best_score;
            if ($s <= 3)      $buckets['≤3']++;
            elseif ($s <= 6)  $buckets['4–6']++;
            elseif ($s <= 9)  $buckets['7–9']++;
            elseif ($s === 10) $buckets['10']++;
            else              $buckets['11']++;
        }

        return view('admin.company_database.index', [
            'list'                     => $list,
            'search'                   => $search,
            'scope'                    => $scope,
            'totalCompanies'           => $totalCompanies,
            'totalNeedSync'            => $this->applyScope($groups, 'need_sync')->count(),
            'totalDuplicates'          => $this->applyScope($groups, 'duplicates')->count(),
            'totalVerified'            => $totalVerified,
            'totalUnverified'          => $this->applyScope($groups, 'unverified')->count(),
            'verifiedPct'              => $verifiedPct,
            'completenessDistribution' => $buckets,
        ]);
    }

    public function verifiedCompanies(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $query = CompanyModel::query()
            ->select(['id', 'company_name', 'prefix', 'company_website', 'company_category',
                'company_other', 'address', 'city', 'portal_code', 'prefix_office_number',
                'office_number', 'full_office_number', 'country'])
            ->where('is_verified', true)
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''");

        if ($search !== '') {
            $query->where('company_name', 'like', '%' . $search . '%');
        }

        $results = $query->orderBy('company_name')->limit(10)->get();

        // Return unique by normalized name
        $seen = [];
        $companies = [];
        foreach ($results as $row) {
            $key = strtolower(trim((string) $row->company_name));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $companies[] = [
                'company_name' => $row->company_name,
                'prefix' => $row->prefix,
                'company_website' => $row->company_website,
                'company_category' => $row->company_category,
                'company_other' => $row->company_other,
                'address' => $row->address,
                'city' => $row->city,
                'portal_code' => $row->portal_code,
                'prefix_office_number' => $row->prefix_office_number,
                'office_number' => $row->office_number,
                'full_office_number' => $row->full_office_number,
                'country' => $row->country,
            ];
        }

        return response()->json($companies);
    }

    public function chartData(Request $request)
    {
        $period = $request->query('period', 'daily');
        $weeks  = (int) $request->query('weeks', 8);
        $weeks  = max(1, min(52, $weeks));

        if ($period === 'weekly') {
            $rows = DB::select("
                SELECT
                    YEARWEEK(verified_at, 1) AS period_key,
                    MIN(DATE(verified_at))   AS period_start,
                    COUNT(DISTINCT LOWER(TRIM(company_name))) AS total
                FROM company
                WHERE is_verified = 1
                  AND verified_at >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
                GROUP BY YEARWEEK(verified_at, 1)
                ORDER BY period_key ASC
            ", [$weeks]);

            // Map DB results by week key
            $map = [];
            foreach ($rows as $r) {
                $map[$r->period_key] = (int) $r->total;
            }

            // Fill all weeks in range
            $labels = [];
            $data   = [];
            for ($i = $weeks - 1; $i >= 0; $i--) {
                $monday = \Carbon\Carbon::now()->startOfWeek()->subWeeks($i);
                $key    = (int) $monday->format('oW'); // ISO year+week
                $labels[] = $monday->format('d M');
                $data[]   = $map[$key] ?? 0;
            }
        } else {
            $days = max(7, min(90, (int) $request->query('days', 30)));
            $rows = DB::select("
                SELECT
                    DATE(verified_at) AS period_key,
                    COUNT(DISTINCT LOWER(TRIM(company_name))) AS total
                FROM company
                WHERE is_verified = 1
                  AND verified_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(verified_at)
                ORDER BY period_key ASC
            ", [$days]);

            // Map DB results by date string
            $map = [];
            foreach ($rows as $r) {
                $map[$r->period_key] = (int) $r->total;
            }

            // Fill every day in range
            $labels = [];
            $data   = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date     = \Carbon\Carbon::today()->subDays($i);
                $key      = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $data[]   = $map[$key] ?? 0;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
            'total'  => array_sum($data),
        ]);
    }

    public function companyUsers(Request $request)
    {
        $normalizedName = strtolower(trim((string) $request->query('normalized_name', '')));
        if ($normalizedName === '') {
            return response()->json([]);
        }

        $users = DB::table('company as c')
            ->join('users as u', 'u.id', '=', 'c.users_id')
            ->leftJoin('profiles as p', 'p.users_id', '=', 'u.id')
            ->whereRaw('LOWER(TRIM(c.company_name)) = ?', [$normalizedName])
            ->select([
                'u.id as user_id',
                'u.name',
                'u.email',
                'u.tier',
                'u.status_member',
                'p.job_title',
                'p.fullphone',
                'c.company_name',
                'c.is_verified',
            ])
            ->orderBy('u.name')
            ->get();

        return response()->json($users);
    }

    public function incompleteDetail(Request $request)
    {
        $normalizedName = strtolower(trim((string) $request->query('normalized_name', '')));
        if ($normalizedName === '') {
            return response()->json([]);
        }

        $rows = DB::table('company as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.users_id')
            ->whereRaw('LOWER(TRIM(c.company_name)) = ?', [$normalizedName])
            ->where(function ($q) {
                $q->whereNull('u.status_member')
                    ->orWhereNotIn('u.status_member', ['deactivated', 'declined']);
            })
            ->select(array_merge(
                ['c.id', 'u.name as user_name', 'u.email as user_email'],
                array_map(function ($f) { return 'c.' . $f; }, $this->completenessFields)
            ))
            ->orderBy('c.id')
            ->get();

        $fields = $this->completenessFields;

        $records = $rows->map(function ($row) use ($fields) {
            $fieldStatus = [];
            $filled = 0;
            foreach ($fields as $f) {
                $val = $row->{$f};
                $ok = !is_null($val) && (is_string($val) ? trim($val) !== '' : true);
                $fieldStatus[$f] = $ok;
                if ($ok) $filled++;
            }
            return [
                'id'           => $row->id,
                'user_name'    => $row->user_name,
                'user_email'   => $row->user_email,
                'fields'       => $fieldStatus,
                'filled'       => $filled,
                'total'        => count($fields),
            ];
        });

        return response()->json([
            'fields'  => $fields,
            'records' => $records,
        ]);
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'normalized_name' => 'required|string',
        ]);

        $sample = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($data['normalized_name']))])->first();
        if (!$sample) {
            return back()->with('error', 'Company tidak ditemukan.');
        }

        $result = CompanyModel::syncByName((string) $sample->company_name, true);

        activity('company_database')
            ->causedBy(auth()->user())
            ->withProperties([
                'company_name'    => $sample->company_name,
                'updated_records' => $result['updated_records'],
                'total_records'   => $result['total_records'],
            ])
            ->log('sync');

        return back()->with(
            'success',
            "Sync berhasil untuk {$sample->company_name}. Record diperbarui: {$result['updated_records']} dari {$result['total_records']} record."
        );
    }

    public function syncAll(Request $request)
    {
        $scope = $this->normalizeScope((string) $request->input('scope', 'need_sync'));
        $search = trim((string) $request->input('search', ''));

        $groups = $this->applyScope($this->buildCompanyGroups($search), $scope);

        $syncedCompanies = 0;
        $updatedRows = 0;

        foreach ($groups as $group) {
            $result = CompanyModel::syncByName((string) $group->company_name, true);
            $updatedRows += (int) $result['updated_records'];

            if ((int) $result['total_records'] > 1) {
                $syncedCompanies++;
            }
        }

        activity('company_database')
            ->causedBy(auth()->user())
            ->withProperties([
                'scope'            => $scope,
                'search'           => $search ?: null,
                'synced_companies' => $syncedCompanies,
                'updated_rows'     => $updatedRows,
            ])
            ->log('sync_all');

        return back()->with(
            'success',
            "Sync semua selesai. {$syncedCompanies} company diproses, {$updatedRows} record company diperbarui."
        );
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'normalized_name' => 'required|string',
            'company_name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:255',
            'company_website' => 'nullable|string|max:255',
            'company_category' => 'nullable|string|max:255',
            'company_other' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'portal_code' => 'nullable|string|max:255',
            'prefix_office_number' => 'nullable|string|max:255',
            'office_number' => 'nullable|string|max:255',
            'full_office_number' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'subcategory_ids' => 'nullable|array',
            'subcategory_ids.*' => 'integer|exists:company_subcategories,id',
        ]);

        $normalizedName = strtolower(trim((string) $data['normalized_name']));
        $sample = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [$normalizedName])->first();
        if (!$sample) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Company tidak ditemukan.'], 422);
            }
            return back()->with('error', 'Company tidak ditemukan.');
        }

        $payload = [];
        foreach ($this->syncFields as $field) {
            if ($field === 'is_verified') {
                continue;
            }
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = $this->normalizeNullableString($data[$field]);
        }

        // Subcategory hanya diproses jika modal memang mengirim field ini (hidden marker),
        // supaya edit dari sumber lain tidak sengaja menghapus pivot yang sudah ada.
        $hasSubcategoryInput = $request->has('subcategory_ids_present');
        $subcategoryIds = collect($data['subcategory_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if (empty($payload) && !$hasSubcategoryInput) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang diubah.'], 422);
            }
            return back()->with('error', 'Tidak ada data yang diubah.');
        }

        $payload['is_verified'] = true;
        $payload['verified_at'] = now();

        $before = collect($payload)->keys()
            ->filter(fn($k) => $k !== 'is_verified' && $k !== 'verified_at')
            ->mapWithKeys(fn($k) => [$k => $sample->{$k}])
            ->toArray();

        $companyIds = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [$normalizedName])->pluck('id');

        $updatedRows = CompanyModel::whereRaw('LOWER(TRIM(company_name)) = ?', [$normalizedName])->update($payload);

        if ($hasSubcategoryInput) {
            $this->syncSubcategories($companyIds, $subcategoryIds);
        }

        $changes = [];
        foreach ($before as $field => $oldValue) {
            $newValue = $payload[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = ['before' => $oldValue, 'after' => $newValue];
            }
        }

        activity('company_database')
            ->causedBy(auth()->user())
            ->withProperties([
                'company_name'    => $sample->company_name,
                'updated_records' => $updatedRows,
                'changes'         => $changes,
                'subcategory_count' => $hasSubcategoryInput ? $subcategoryIds->count() : null,
            ])
            ->log('update');

        $message = "Update & sync berhasil untuk {$sample->company_name}. {$updatedRows} record company diperbarui.";

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    /**
     * Set subcategory (pivot) untuk semua record company dengan nama sama.
     * Hapus pivot lama lalu insert sesuai pilihan — konsisten dengan update bulk-by-name.
     */
    private function syncSubcategories($companyIds, $subcategoryIds): void
    {
        $companyIds = collect($companyIds)->filter()->unique()->values();
        if ($companyIds->isEmpty()) {
            return;
        }

        $now = now();
        $pivotRows = [];
        foreach ($companyIds as $companyId) {
            foreach ($subcategoryIds as $subcategoryId) {
                $pivotRows[] = [
                    'company_id'             => $companyId,
                    'company_subcategory_id' => $subcategoryId,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ];
            }
        }

        DB::transaction(function () use ($companyIds, $pivotRows) {
            DB::table('company_subcategory_company')
                ->whereIn('company_id', $companyIds->all())
                ->delete();

            if (!empty($pivotRows)) {
                DB::table('company_subcategory_company')->insert($pivotRows);
            }
        });
    }

    /**
     * Nama company (ter-normalisasi) yang punya minimal satu member dengan status
     * deactivated/declined. Seluruh company dengan nama ini di-hide dari database —
     * sekali ada member yang di-deactivate/decline, company-nya tidak ditampilkan
     * lagi walau ada member lain yang masih aktif atau baru daftar/verify.
     *
     * @return string[]
     */
    private function taintedCompanyNames(): array
    {
        return CompanyModel::query()
            ->leftJoin('users', 'users.id', '=', 'company.users_id')
            ->whereIn('users.status_member', ['deactivated', 'declined'])
            ->whereNotNull('company.company_name')
            ->whereRaw("TRIM(company.company_name) <> ''")
            ->pluck('company.company_name')
            ->map(function ($n) {
                return Str::lower(trim((string) $n));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function buildCompanyGroups(string $search = ''): Collection
    {
        $taintedNames = $this->taintedCompanyNames();

        $query = CompanyModel::query()
            ->select(['id', 'users_id', 'is_verified', 'company_name', 'updated_at', ...$this->syncFields])
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            // Hanya company yang user-nya benar-benar ada. Kalau users_id menunjuk ke
            // user yang sudah terhapus (atau null), company-nya tidak ditampilkan —
            // mencegah company "hantu" tanpa user muncul di list.
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'company.users_id');
            });

        // Company yang punya minimal satu member deactivated/declined di-hide SELURUHNYA,
        // meskipun ada member lain yang masih aktif atau baru daftar/verify.
        if (!empty($taintedNames)) {
            $placeholders = implode(',', array_fill(0, count($taintedNames), '?'));
            $query->whereRaw("LOWER(TRIM(company_name)) NOT IN ($placeholders)", $taintedNames);
        }

        if ($search !== '') {
            $query->where('company_name', 'like', '%' . $search . '%');
        }

        /** @var EloquentCollection<int, CompanyModel> $rows */
        $rows = $query->orderByDesc('updated_at')->get();

        // Preload subcategory pivot untuk semua company id sekaligus (hindari N+1).
        $subcatByCompany = DB::table('company_subcategory_company')
            ->whereIn('company_id', $rows->pluck('id')->all())
            ->get()
            ->groupBy('company_id');

        return $rows
            ->groupBy(function ($row) {
                return Str::lower(trim((string) $row->company_name));
            })
            ->map(function (Collection $companies, string $normalizedName) use ($subcatByCompany) {
                $scored = $companies->sortByDesc(function ($row) {
                    return $this->completenessScore($row);
                })->values();

                $best = $scored->first();
                $maxScore = count($this->completenessFields);

                $isVerified = $companies->contains(fn($row) => (bool) $row->is_verified);

                // Gabungan subcategory dari semua record company senama (pivot per company_id).
                $subcategoryIds = collect();
                foreach ($companies as $row) {
                    if ($subcatByCompany->has($row->id)) {
                        $subcategoryIds = $subcategoryIds->merge(
                            $subcatByCompany->get($row->id)->pluck('company_subcategory_id')
                        );
                    }
                }
                $subcategoryIds = $subcategoryIds->map(fn($id) => (int) $id)->unique()->values()->all();

                return (object) [
                    'normalized_name' => $normalizedName,
                    'company_name' => trim((string) ($best->company_name ?? '')),
                    'is_verified' => $isVerified,
                    'total_records' => $companies->count(),
                    'incomplete_records' => $companies->filter(function ($row) use ($maxScore) {
                        return $this->completenessScore($row) < $maxScore;
                    })->count(),
                    'best_record_id' => $best ? $best->id : null,
                    'best_score' => $best ? $this->completenessScore($best) : 0,
                    'max_score' => $maxScore,
                    'best_values' => [
                        'company_name' => $best ? $best->company_name : null,
                        'prefix' => $best ? $best->prefix : null,
                        'company_website' => $best ? $best->company_website : null,
                        'company_category' => $best ? $best->company_category : null,
                        'company_other' => $best ? $best->company_other : null,
                        'address' => $best ? $best->address : null,
                        'city' => $best ? $best->city : null,
                        'portal_code' => $best ? $best->portal_code : null,
                        'prefix_office_number' => $best ? $best->prefix_office_number : null,
                        'office_number' => $best ? $best->office_number : null,
                        'full_office_number' => $best ? $best->full_office_number : null,
                        'country' => $best ? $best->country : null,
                        'subcategory_ids' => $subcategoryIds,
                    ],
                    'user_ids' => $companies->pluck('users_id')->filter()->unique()->take(5)->implode(', '),
                    'updated_at' => $best ? $best->updated_at : null,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a->incomplete_records !== $b->incomplete_records) {
                    return $b->incomplete_records <=> $a->incomplete_records;
                }

                if ($a->total_records !== $b->total_records) {
                    return $b->total_records <=> $a->total_records;
                }

                return strcmp($a->company_name, $b->company_name);
            })
            ->values();
    }

    private function applyScope(Collection $groups, string $scope): Collection
    {
        if ($scope === 'duplicates') {
            return $groups->filter(fn($g) => $g->total_records > 1)->values();
        }

        if ($scope === 'verified') {
            return $groups->filter(fn($g) => $g->is_verified)->values();
        }

        if ($scope === 'unverified') {
            return $groups->filter(fn($g) => !$g->is_verified)->values();
        }

        if ($scope === 'all') {
            return $groups;
        }

        // need_sync: duplikat yang belum semua verified atau masih ada incomplete
        return $groups
            ->filter(fn($g) => !$g->is_verified && ($g->total_records > 1 || $g->incomplete_records > 0))
            ->values();
    }

    private function normalizeScope(string $scope): string
    {
        if (!in_array($scope, ['need_sync', 'duplicates', 'verified', 'unverified', 'all'], true)) {
            return 'need_sync';
        }

        return $scope;
    }

    private function completenessScore(object $company): int
    {
        return collect($this->completenessFields)
            ->filter(function ($field) use ($company) {
                return $this->isFilled($company->{$field} ?? null);
            })
            ->count();
    }

    private function isFilled($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function normalizeNullableString($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }
}
