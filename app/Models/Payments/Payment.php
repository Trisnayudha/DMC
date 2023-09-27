<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payment';
    protected $casts = [
        'id' => 'int',
        'member_id' => 'int',
        'events_id' => 'int',
        'tickets_id' => 'int'
    ];
    protected $fillable = [
        'member_id',
        'package',
        'payment_method',
        'events_id',
        'tickets_id',
        'status_registration',
        'link',
        'code_payment',
        'qr_code',
        'pic_id',
        'booking_contact_id',
        'groupby_users_id',
        'sponsor_code'
    ];

    public static function arrayCode()
    {
        return DB::table('payment')
            ->select('payment.code_payment')
            ->inRandomOrder()
            ->limit(2000)
            ->pluck('payment.code_payment')
            ->toArray();
    }
}
