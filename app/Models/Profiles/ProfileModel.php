<?php

namespace App\Models\Profiles;

use App\Models\Company\CompanyModel;
use App\Models\User; // penting: import model User
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileModel extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = [
        'prefix_phone',
        'phone',
        'fullphone',
        'image',
        'job_title',
        'company_id',
        'users_id',
    ];

    /**
     * Relasi ke tabel users
     * Setiap profile dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    /**
     * (Opsional) Relasi ke tabel company jika kamu sering perlu ambil data perusahaan.
     */
    public function company()
    {
        return $this->belongsTo(CompanyModel::class, 'company_id', 'id');
    }
}
