<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'verify_email',
        'verify_phone',
        'otp',
        'isStatus',
        'uname',
        'qrcode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relasi ke ProfileModel (one to one)
     * Setiap user hanya memiliki satu profile.
     */
    public function profile()
    {
        return $this->hasOne(\App\Models\Profiles\ProfileModel::class, 'users_id', 'id');
    }

    /**
     * (Opsional) Relasi ke CompanyModel jika dibutuhkan
     * Setiap user bisa punya satu company (dari profile/company_id).
     */
    public function company()
    {
        return $this->hasOne(\App\Models\Company\CompanyModel::class, 'users_id', 'id');
    }
}
