<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentUsersVA extends Model
{
    use HasFactory;
    protected $table = 'payment_users_va';
    protected $casts = [
        'merchant_code' => 'int',
        'account_number' => 'int',
        'expected_amount' => 'int'
    ];
    protected $fillable = [
        'payment_id',
        "is_closed",
        "status",
        "currency",
        "country",
        "owner_id",
        "external_id",
        "bank_code",
        "merchant_code",
        "name",
        "account_number",
        "expected_amount",
        "expiration_date",
        "is_single_use"
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
