<?php

namespace App\Models\Profiles;

use App\Models\Company\CompanyModel;
use App\Models\User; // penting: import model User
use App\Support\JobTierClassifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        'job_tier',
        'job_tier_manual',
        'newsletter',
        'wa_updates',
        'company_id',
        'users_id',
    ];

    /**
     * job_tier HANYA diisi untuk member active (users.status_member = 'active');
     * selain itu selalu NULL. Di setiap save (registrasi app/web, import, edit
     * admin, dst) tier dihitung dari job_title, kecuali admin sudah override
     * manual. Perubahan status member ditangani User::booted() → syncJobTierForUser().
     * Tidak melakukan apa-apa sebelum migration job_tier dijalankan.
     */
    protected static function booted()
    {
        static::saving(function (ProfileModel $profile) {
            if (!self::hasJobTierColumn()) {
                return;
            }

            $isActive = User::where('id', $profile->users_id)->value('status_member') === 'active';
            if (!$isActive) {
                $profile->job_tier        = null;
                $profile->job_tier_manual = false;
                return;
            }

            if (!$profile->job_tier_manual && ($profile->isDirty('job_title') || $profile->job_tier === null)) {
                $profile->job_tier = JobTierClassifier::classify($profile->job_title);
            }
        });
    }

    /**
     * Tier yang ditampilkan (tabel admin, export): NULL kalau bukan member
     * active; nilai tersimpan kalau ada; kalau belum ada (sebelum backfill)
     * dihitung langsung dari job_title.
     */
    public static function jobTierFor(?string $jobTitle, ?string $statusMember, $storedTier = null): ?int
    {
        if ($statusMember !== 'active') {
            return null;
        }

        return $storedTier !== null ? (int) $storedTier : JobTierClassifier::classify($jobTitle);
    }

    /**
     * Dipanggil saat status_member user berubah: jadi active → hitung tier
     * (kecuali override manual); bukan active → kosongkan. Lewat query langsung
     * supaya updated_at profile tidak ikut berubah.
     */
    public static function syncJobTierForUser(int $userId, ?string $statusMember): void
    {
        if (!self::hasJobTierColumn()) {
            return;
        }

        $profiles = DB::table('profiles')->where('users_id', $userId);

        if ($statusMember !== 'active') {
            $profiles->update(['job_tier' => null, 'job_tier_manual' => false]);
            return;
        }

        foreach ($profiles->where('job_tier_manual', false)->get(['id', 'job_title']) as $row) {
            DB::table('profiles')->where('id', $row->id)
                ->update(['job_tier' => JobTierClassifier::classify($row->job_title)]);
        }
    }

    public static function hasJobTierColumn(): bool
    {
        static $has = null;

        if ($has === null) {
            try {
                $has = Schema::hasColumn('profiles', 'job_tier');
            } catch (\Throwable $e) {
                $has = false;
            }
        }

        return $has;
    }

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
