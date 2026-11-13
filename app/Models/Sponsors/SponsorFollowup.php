<?php

namespace App\Models\Sponsors;

use App\Models\CmsUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SponsorFollowup extends Model
{
    use \App\Traits\LogsSponsorActivity;

    /** Secretariat follow-up until the sponsor confirms renewal. */
    const TYPE_RENEWAL = 'renewal';

    /** Finance follow-up after the invoice is issued, until the sponsor pays. */
    const TYPE_INVOICE = 'invoice';

    protected $table = 'sponsor_followups';

    protected $fillable = [
        'sponsor_id',
        'type',
        'renewal_year',
        'followed_up_at',
        'channel',
        'notes',
        'confirmed_renewal',
        'confirmed_payment',
        'kmk_rate',
        'proof_path',
        'created_by',
    ];

    protected $casts = [
        'followed_up_at'    => 'date',
        'confirmed_renewal' => 'boolean',
        'confirmed_payment' => 'boolean',
    ];

    /** @var bool|null cached Schema check, see hasTypeColumn() */
    private static $typeColumnExists;

    /**
     * The type / confirmed_payment columns come from migration 2026_11_13_100000.
     * Until it runs, every row is treated as a renewal follow-up.
     */
    public static function hasTypeColumn(): bool
    {
        if (self::$typeColumnExists === null) {
            self::$typeColumnExists = Schema::hasColumn('sponsor_followups', 'type');
        }
        return self::$typeColumnExists;
    }

    public function scopeOfType($query, string $type)
    {
        if (!self::hasTypeColumn()) {
            // Before the migration only renewal follow-ups exist.
            return $type === self::TYPE_RENEWAL ? $query : $query->whereRaw('1 = 0');
        }
        return $query->where('type', $type);
    }

    /**
     * Renewal follow-ups belong to the Secretariat (Sponsors module); invoice
     * follow-ups belong to Finance. Superadmin can record both.
     */
    public static function userCanRecord(string $type, $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->isSuperadmin()) {
            return true;
        }
        return $type === self::TYPE_INVOICE
            ? $user->role === 'finance_team'
            : $user->canAccessModule('sponsors');
    }

    public function isInvoice(): bool
    {
        return $this->type === self::TYPE_INVOICE;
    }

    public function sponsorLogSubject(): string
    {
        return $this->isInvoice() ? 'Invoice Follow-up' : 'Renewal Follow-up';
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function creator()
    {
        // created_by diisi auth()->id() dari guard cms → id di cms_users, bukan users
        return $this->belongsTo(CmsUser::class, 'created_by');
    }
}
