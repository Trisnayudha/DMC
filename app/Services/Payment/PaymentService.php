<?php

namespace App\Services\Payment;

use App\Models\Payments\Payment;

class PaymentService extends Payment
{
    public static function listPaymentRegister($events_id, $params)
    {
        $packages = ['nonmember', 'member', 'onsite', 'table', 'free', 'sponsor'];
        $query = Payment::join('users as member_users', 'member_users.id', '=', 'payment.member_id')
            ->leftJoin('company', 'company.users_id', '=', 'member_users.id')
            ->leftJoin('profiles', 'profiles.users_id', '=', 'member_users.id')
            ->leftJoin('users as pic_users', 'pic_users.id', '=', 'payment.pic_id')
            ->where('payment.events_id', $events_id)
            ->select('member_users.*', 'payment.*', 'company.*', 'profiles.*', 'payment.id as payment_id', 'payment.created_at as register', 'pic_users.name as pic_name')
            ->orderBy('payment.created_at', 'desc');

        if ($params === 'paid') {
            $query->whereIn('payment.package', ['onsite', 'nonmember', 'member', 'table']);
        } elseif ($params === 'sponsor') {
            $query->whereIn('payment.package', ['sponsor']);
        } elseif ($params === 'free') {
            $query->whereIn('payment.package', ['free']);
        }

        $payments = $query->get();
        return $payments;
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

    public static function countRegisterApprove($params, $events_id)
    {
        $packages = ['nonmember', 'member', 'onsite', 'table', 'free', 'sponsor'];

        return Payment::where('events_id', $events_id)
            ->when(!is_null($params), function ($query) use ($params, $packages) {
                if (is_array($params)) {
                    return $query->whereIn('package', $params);
                } elseif ($params === 'free' || $params === 'sponsor') {
                    return $query->where('package', $params);
                } else {
                    return $query;
                }
            })
            ->where('status_registration', 'Paid Off')
            ->count();
    }
}
