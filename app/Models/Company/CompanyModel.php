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
    public static function syncByName(string $companyName): void
    {
        if (empty(trim($companyName))) {
            return;
        }

        $fields = [
            'prefix', 'company_website', 'company_category', 'company_other',
            'address', 'city', 'portal_code', 'prefix_office_number',
            'office_number', 'full_office_number', 'country', 'cci', 'explore',
        ];

        // Ambil semua record dengan company_name yang sama (case-insensitive)
        $companies = self::whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($companyName))])->get();

        if ($companies->count() <= 1) {
            return; // Tidak ada record lain yang perlu di-sync
        }

        // Pilih record paling lengkap berdasarkan jumlah field non-empty
        $best = $companies->sortByDesc(function ($row) use ($fields) {
            return collect($fields)->filter(fn($f) => !empty($row->$f))->count();
        })->first();

        // Kumpulkan data dari record terlengkap
        $syncData = [];
        foreach ($fields as $field) {
            if (!empty($best->$field)) {
                $syncData[$field] = $best->$field;
            }
        }

        if (empty($syncData)) {
            return;
        }

        // Update semua record lain (selain yang sudah paling lengkap)
        // hanya mengisi field yang masih kosong
        foreach ($companies as $company) {
            if ($company->id === $best->id) {
                continue;
            }

            $toUpdate = [];
            foreach ($syncData as $field => $value) {
                if (empty($company->$field)) {
                    $toUpdate[$field] = $value;
                }
            }

            if (!empty($toUpdate)) {
                self::where('id', $company->id)->update($toUpdate);
            }
        }
    }
}
