<?php

namespace App\Services\Payment;

use App\Models\Payments\Payment;

class PaymentService extends Payment
{
    public static function listPaymentRegister($events_id)
    {
        return Payment::join('users', 'users.id', '=', 'payment.member_id')
            ->leftJoin('company', 'company.users_id', '=', 'users.id')
            ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
            ->where('payment.events_id', $events_id)
            ->select('users.*', 'payment.*', 'company.*', 'profiles.*', 'payment.id as payment_id', 'payment.created_at as register')
            ->orderBy('payment.created_at', 'desc')
            ->get();
    }

    public static function countRegister($params, $events_id)
    {
        $packages = ['nonmember', 'member', 'onsite', 'table', 'free', 'sponsor'];

        return Payment::where('events_id', $events_id)
            ->when(!is_null($params), function ($query) use ($params, $packages) {
                if (is_array($params)) {
                    return $query->whereIn('package', $params);
                } elseif ($params === 'free' || $params === 'sponsor') {
                    return $query->where('package', $params);
                }
            })
            ->count();
    }
}
