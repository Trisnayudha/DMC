<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Profiles\ProfileModel;
use Illuminate\Support\Facades\DB;

class CompanyModel extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'prefix',
        'company_name',
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
}
