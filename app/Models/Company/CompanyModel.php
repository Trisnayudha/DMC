<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Profiles\ProfileModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyModel extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'prefix',
        'company_name',
        'is_verified',
        'verified_at',
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
        'cci',
        'explore',
        'users_id',
    ];

    /**
     * Relasi ke User
     * Satu company dimiliki oleh satu user (users_id)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    /**
     * Relasi ke Profiles
     * Satu company bisa punya banyak profile (employee/delegate)
     */
    public function profiles()
    {
        return $this->hasMany(ProfileModel::class, 'company_id', 'id');
    }

    /**
     * Subcategory yang dipilih company ini (many-to-many via pivot).
     * company_category (string) tetap sebagai kategori utama — ini tambahan multi-value.
     */
    public function subcategories()
    {
        return $this->belongsToMany(
            \App\Models\Company\CompanySubcategory::class,
            'company_subcategory_company',
            'company_id',
            'company_subcategory_id'
        );
    }

    /**
     * Nama company (lowercase, trimmed) yang punya minimal satu member
     * berstatus deactivated/declined. Dipakai untuk menyembunyikan company
     * "tainted" dari Company Database & dari hitungan verified company,
     * supaya company dengan member bermasalah tidak ikut ditampilkan/dihitung.
     */
    public static function taintedCompanyNames(): array
    {
        return static::query()
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

    /**
     * Jumlah company terverifikasi, dedup per nama company (bukan per row).
     * Satu company bisa punya banyak row di tabel `company` (satu per member/karyawan),
     * jadi hitungan mentah where('is_verified', true)->count() akan overcount.
     * Definisi ini disamakan dengan Company Database page: exclude company "hantu"
     * (users_id tidak valid) dan company "tainted" (lihat taintedCompanyNames()).
     */
    public static function countVerifiedCompanies(): int
    {
        $tainted = static::taintedCompanyNames();

        $query = static::query()
            ->where('is_verified', true)
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'company.users_id');
            });

        if (!empty($tainted)) {
            $placeholders = implode(',', array_fill(0, count($tainted), '?'));
            $query->whereRaw("LOWER(TRIM(company_name)) NOT IN ($placeholders)", $tainted);
        }

        return $query
            ->selectRaw('LOWER(TRIM(company_name)) as normalized_name')
            ->distinct()
            ->get()
            ->count();
    }

    /**
     * Sync semua record di tabel company yang punya company_name sama.
     * Cari record paling lengkap (paling banyak field terisi),
     * lalu update semua record lain dengan data tersebut.
     */
    public static function syncByName(string $companyName, bool $overwriteFilled = false): array
    {
        $result = [
            'total_records' => 0,
            'updated_records' => 0,
            'best_record_id' => null,
        ];

        if (empty(trim($companyName))) {
            return $result;
        }

        $fields = [
            'prefix', 'company_website', 'company_category', 'company_other',
            'address', 'city', 'portal_code', 'prefix_office_number',
            'office_number', 'full_office_number', 'country',
        ];

        // Ambil semua record dengan company_name yang sama (case-insensitive)
        $companies = self::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($companyName))])->get();

        $result['total_records'] = $companies->count();
        if ($companies->count() <= 1) {
            return $result; // Tidak ada record lain yang perlu di-sync
        }

        $isFilled = static function ($value): bool {
            if (is_null($value)) {
                return false;
            }

            if (is_string($value)) {
                return trim($value) !== '';
            }

            return true;
        };

        // Pilih record paling lengkap berdasarkan jumlah field non-empty
        $best = $companies->sortByDesc(function ($row) use ($fields) {
            return collect($fields)->filter(fn($f) => !is_null($row->$f) && (!is_string($row->$f) || trim($row->$f) !== ''))->count();
        })->first();
        $result['best_record_id'] = $best ? $best->id : null;

        // Kumpulkan data dari record terlengkap
        $syncData = [];
        foreach ($fields as $field) {
            if ($isFilled($best->$field)) {
                $syncData[$field] = $best->$field;
            }
        }

        if (empty($syncData)) {
            return $result;
        }

        // Update semua record lain (selain yang sudah paling lengkap)
        // hanya mengisi field yang masih kosong
        foreach ($companies as $company) {
            if ($company->id === $best->id) {
                continue;
            }

            $toUpdate = [];
            foreach ($syncData as $field => $value) {
                if ($overwriteFilled || !$isFilled($company->$field)) {
                    $toUpdate[$field] = $value;
                }
            }

            if (!empty($toUpdate)) {
                self::where('id', $company->id)->update($toUpdate);
                $result['updated_records']++;
            }
        }

        return $result;
    }

    /**
     * "Explore Marketing" on the company record is a messy free-text column
     * in practice (NULL/''/'0'/'agree'/'aggree' [typo]/'true'/'1') — treat any of
     * these as truthy.
     */
    public function isLeadExplore(): bool
    {
        $normalized = strtolower(trim((string) $this->explore));
        return in_array($normalized, ['agree', 'aggree', 'true', '1'], true);
    }

    protected static function booted()
    {
        static::saved(function ($company) {
            if ($company->users_id && $company->isLeadExplore() && \Illuminate\Support\Facades\Schema::hasTable('member_lead_follow_ups')) {
                try {
                    $leadAdmin = auth()->user();
                    \App\Models\MemberLeadFollowUp::firstOrCreate(
                        ['user_id' => $company->users_id, 'result' => \App\Models\MemberLeadFollowUp::RESULT_PENDING],
                        [
                            'deadline_at'     => now()->addHours(48),
                            'created_by_id'   => auth()->id(),
                            'created_by_name' => $leadAdmin ? $leadAdmin->name : 'System',
                        ]
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('CompanyModel saved event: lead auto-create failed for user ' . $company->users_id . ': ' . $e->getMessage());
                }
            }
        });
    }
}
