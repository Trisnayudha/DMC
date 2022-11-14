<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payment';
    protected $fillable = [
        'member_id',
        'package',
        'payment_method',
        'price',
        'status',
        'link',
        'code_payment',
        'qr_code'
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
