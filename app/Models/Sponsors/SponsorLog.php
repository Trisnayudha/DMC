<?php

namespace App\Models\Sponsors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SponsorLog extends Model
{
    protected $table = 'sponsor_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'sponsor_id',
        'subject',
        'subject_id',
        'action',
        'description',
        'changes',
        'cms_user_id',
        'cms_user_name',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /** Disimpan per request supaya Schema::hasTable tidak dipanggil berulang. */
    private static $tableReady = null;

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    /**
     * Catat satu aktivitas. Tidak pernah melempar error: kalau tabel belum
     * di-migrate atau insert gagal, aksi utama tetap jalan dan error masuk log.
     */
    public static function record(?int $sponsorId, string $subject, ?int $subjectId, string $action, ?array $changes = null, ?string $description = null): void
    {
        try {
            if (self::$tableReady === null) {
                self::$tableReady = Schema::hasTable('sponsor_logs');
            }
            if (!self::$tableReady) {
                return;
            }
            $user = Auth::user();
            static::create([
                'sponsor_id'    => $sponsorId,
                'subject'       => $subject,
                'subject_id'    => $subjectId,
                'action'        => $action,
                'description'   => $description,
                'changes'       => $changes ?: null,
                'cms_user_id'   => $user ? $user->id : null,
                'cms_user_name' => $user ? $user->name : 'System',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sponsor log gagal disimpan: ' . $e->getMessage(), ['subject' => $subject, 'subject_id' => $subjectId]);
        }
    }
}
