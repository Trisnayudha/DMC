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
        'alternative_email',
        'password',
        'verify_email',
        'verify_phone',
        'otp',
        'isStatus',
        'uname',
        'qrcode',
        'source',
        'hear',
        'status_member',
        'tier',
        'verified_at',
        'two_step_verified',
        'two_step_verified_at',
        'two_step_verified_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'verified_at'          => 'datetime',
        'two_step_verified'    => 'boolean',
        'two_step_verified_at' => 'datetime',
    ];

    /**
     * Relasi ke ProfileModel (one to one)
     * Setiap user hanya memiliki satu profile.
     */
    /**
     * Apakah kolom users.member_registered_at sudah ada (migration belum tentu
     * sudah dijalankan di DB ini — jangan 500 kalau belum).
     */
    public static function hasMemberRegisteredAtColumn(): bool
    {
        static $has = null;

        if ($has === null) {
            try {
                $has = \Illuminate\Support\Facades\Schema::hasColumn('users', 'member_registered_at');
            } catch (\Throwable $e) {
                $has = false;
            }
        }

        return $has;
    }

    /**
     * Catat saat orang ini mendaftar jadi member (form dikirim, status jadi
     * pending) — dipakai semua pintu pendaftaran. Penting untuk akun yang sudah
     * ada sebelumnya karena daftar event: users.created_at tetap tanggal daftar
     * event, jadi tanggal daftar member disimpan terpisah. Update langsung via
     * query supaya updated_at tidak ikut berubah.
     */
    public function stampMemberRegistered(): void
    {
        if (!self::hasMemberRegisteredAtColumn() || !$this->exists) {
            return;
        }

        try {
            $now = now();
            \Illuminate\Support\Facades\DB::table('users')->where('id', $this->id)->update(['member_registered_at' => $now]);
            $this->setAttribute('member_registered_at', $now);
            $this->syncOriginalAttribute('member_registered_at');
        } catch (\Throwable $e) {
            // Pencatatan tanggal tidak boleh menggagalkan pendaftaran member.
            \Illuminate\Support\Facades\Log::warning('stampMemberRegistered failed for user ' . $this->id . ': ' . $e->getMessage());
        }
    }

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
