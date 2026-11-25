<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CmsUser extends Authenticatable
{
    protected $table = 'cms_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'role',
        'otp_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'login_otp_hash',
        'login_otp_sent_at',
        'login_otp_attempts',
    ];
    const ROLES = [
        'member_team' => 'Member Team',
        'event_team' => 'Event Team',
        'marketing_team' => 'Marketing Team',
        'secretariat_team' => 'Secretariat Team',
        'finance_team' => 'Financial Team',
        'superadmin' => 'Superadmin',
    ];

    protected $casts = ['is_active' => 'boolean', 'otp_enabled' => 'boolean', 'login_otp_sent_at' => 'datetime'];

    protected static function booted()
    {
        static::updating(function (self $user) {
            if ($user->isDirty(['otp_enabled', 'email', 'password', 'is_active'])) {
                $user->login_otp_hash = null;
                $user->login_otp_sent_at = null;
                if ($user->getRememberToken()) {
                    $user->setRememberToken(null);
                }
            }
        });
    }

    public function cmsRole()
    {
        return $this->belongsTo(CmsRole::class, 'role', 'key');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function canAccessModule(string $module): bool
    {
        return \App\Support\CmsAccess::allows($this, $module);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? 'Level belum ditetapkan';
    }
}
