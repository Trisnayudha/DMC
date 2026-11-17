<?php

namespace App\Services\Payment;

use App\Models\Payments\Payment;

class PaymentService extends Payment
{
    /** ?status= filter key on the event registration list => payment.status_registration value. */
    const REGISTRATION_STATUS_FILTERS = [
        'waiting'  => 'Waiting',
        'rejected' => 'Reject',
    ];

    /**
     * Join the company the profile points to (profiles.company_id), the same record the
     * Members DMC page shows. A user can own several company rows; joining on
     * company.users_id picked an arbitrary one (often an empty duplicate) and could
     * repeat the participant row. Falls back to the user's latest company row.
     */
    public static function companyJoinCondition(string $usersAlias): \Illuminate\Database\Query\Expression
    {
        return \Illuminate\Support\Facades\DB::raw('COALESCE(profiles.company_id, (SELECT MAX(c2.id) FROM company c2 WHERE c2.users_id = ' . $usersAlias . '.id))');
    }

    public static function listPaymentRegister($events_id, $params, $sponsorId = null, $status = null)
    {
        $packages = ['nonmember', 'member', 'onsite', 'table', 'free', 'sponsor'];
        $query = Payment::join('users as member_users', 'member_users.id', '=', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.users_id', '=', 'member_users.id')
            ->leftJoin('company', 'company.id', '=', self::companyJoinCondition('member_users'))
            ->leftJoin('cms_users as pic_users', 'pic_users.id', '=', 'payment.pic_id')
            ->leftJoin('event_sponsors', function ($join) {
                $join->on('event_sponsors.code_access', '=', 'payment.sponsor_code')
                    ->on('event_sponsors.events_id', '=', 'payment.events_id');
            })
            ->leftJoin('sponsors', 'sponsors.id', '=', \Illuminate\Support\Facades\DB::raw('COALESCE(payment.sponsor_id, event_sponsors.sponsors_id)'))
            ->where('payment.events_id', $events_id)
            ->select(
                'member_users.*',
                'payment.*',
                'company.*',
                'profiles.*',
                'payment.id as payment_id',
                'payment.created_at as register',
                'pic_users.name as pic_name',
                'sponsors.name as sponsor_name',
                'sponsors.id as sponsor_company_id',
                'company.id as company_id_ref', // dipakai export untuk lookup subcategory
                'member_users.status_member as status_member' // keanggotaan ASLI (bukan cara daftar)
            )
            ->orderBy('payment.created_at', 'desc');

        if ($sponsorId !== null && $sponsorId !== '') {
            $query->where('sponsors.id', $sponsorId)->where('sponsors.status', 'publish');
        }

        if ($params === 'paid') {
            $query->whereIn('payment.package', ['onsite', 'nonmember', 'member', 'table']);
        } elseif ($params === 'sponsor') {
            $query->whereIn('payment.package', ['sponsor']);
        } elseif ($params === 'free') {
            $query->whereIn('payment.package', ['free']);
        } elseif ($params === 'potential_member') {
            $query->where('payment.is_membership_prospect', 1);
        }

        if (isset(self::REGISTRATION_STATUS_FILTERS[$status])) {
            $query->where('payment.status_registration', self::REGISTRATION_STATUS_FILTERS[$status]);
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

    public static function findPaymmentUsers($id, $events_id = null)
    {
        return Payment::where('payment.groupby_users_id', $id)
            ->when($events_id, function ($query) use ($events_id) {
                $query->where('payment.events_id', $events_id);
            })
            ->leftJoin('users', 'users.id', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('events_tickets', 'payment.tickets_id', 'events_tickets.id')
            ->select('*', 'payment.events_id as events_id')
            ->get();
    }

    public static function findPaymmentUser($id)
    {
        return Payment::where('payment.id', $id)
            ->leftjoin('users', 'users.id', 'payment.member_id')
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', '=', self::companyJoinCondition('users'))
            ->leftjoin('events_tickets', 'payment.tickets_id', 'events_tickets.id')
            ->select('*', 'payment.events_id as events_id')
            ->first();
    }
}
