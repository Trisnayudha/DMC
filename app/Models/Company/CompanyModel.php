<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Profiles\ProfileModel;

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
}
