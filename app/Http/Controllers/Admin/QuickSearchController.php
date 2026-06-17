<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        $category = trim($request->input('category', ''));

        if (strlen($q) < 2 && $category === '') {
            return response()->json(['results' => []]);
        }

        $users = User::query()
            ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
            ->leftJoin('company', 'company.users_id', '=', 'users.id')
            ->when(strlen($q) >= 2, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('users.name', 'LIKE', "%{$q}%")
                        ->orWhere('users.email', 'LIKE', "%{$q}%")
                        ->orWhere('company.company_name', 'LIKE', "%{$q}%");
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('company.company_category', $category);
            })
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.status_member',
                'profiles.phone',
                'profiles.fullphone',
                'profiles.job_title',
                'company.company_name',
                'company.is_verified',
            ])
            ->limit($request->input('limit', 10))
            ->offset($request->input('offset', 0))
            ->get();

        $results = $users->map(function ($user) {
            $payments = DB::table('payment')
                ->join('events', 'events.id', '=', 'payment.events_id')
                ->leftJoin('events_tickets', 'events_tickets.id', '=', 'payment.tickets_id')
                ->where('payment.member_id', $user->id)
                ->orderBy('events.start_date', 'desc')
                ->select([
                    'events.name as event_name',
                    'events.start_date',
                    'events_tickets.title as ticket_title',
                    'payment.status_registration',
                    'payment.package',
                    'payment.created_at',
                ])
                ->get();

            $history = $payments->map(function ($p) {
                return [
                    'event_name'   => $p->event_name,
                    'ticket_title' => $p->ticket_title ?? $p->package ?? '-',
                    'status'       => $p->status_registration,
                    'event_date'   => $p->start_date ? date('d M Y', strtotime($p->start_date)) : '-',
                ];
            });

            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->fullphone ?: $user->phone,
                'job_title'    => $user->job_title,
                'company_name' => $user->company_name,
                'status_member'    => $user->status_member ?? 'pending',
                'company_verified' => (bool) $user->is_verified,
                'history'      => $history,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
