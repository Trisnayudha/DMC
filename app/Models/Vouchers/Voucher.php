<?php

namespace App\Models\Vouchers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'voucher_code',
        'status',
        'type',
        'nominal',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
    ];

    // Jika Anda butuh casting/konversi otomatis
    protected $casts = [
        'valid_from'   => 'datetime',
        'valid_until'  => 'datetime',
    ];

    /**
     * Contoh fungsi helper untuk cek apakah voucher masih berlaku
     * (sesuai valid_from, valid_until, status, dsb).
     */
    public function isValid(): bool
    {
        // Cek status
        if ($this->status !== 'active') {
            return false;
        }

        // Jika ada limit pemakaian, cek apakah used_count < max_uses
        if (!is_null($this->max_uses) && $this->used_count >= $this->max_uses) {
            return false;
        }

        // Jika ada waktu valid_from & valid_until
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
