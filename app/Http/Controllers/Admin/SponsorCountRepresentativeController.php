<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SponsorCountRepresentativeController extends Controller
{
    public function index(Request $request)
    {
        $year         = $request->get('year', now()->year);
        $filterSponsor = $request->get('company', '');

        $sponsorList = Sponsor::where('status', 'publish')->orderBy('name')->get();

        /*
         |---------------------------------------------------------------------------------
         | Representative attendance — all registered sponsor members (present or not)
         |---------------------------------------------------------------------------------
         */
        $representativesQuery = Payment::select(
            'users.name as representative_name',
            'sponsors.name as company',
            'payment.created_at as attend_time',
            'events.name as event_name',
            'users_event.present as present'
        )
            ->join('users', 'payment.member_id', '=', 'users.id')
            ->join('sponsors', 'payment.sponsor_id', '=', 'sponsors.id')
            ->join('events', 'payment.events_id', '=', 'events.id')
            ->leftJoin('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'users.id');
            })
            ->where('sponsors.status', 'publish')
            ->whereNotNull('payment.sponsor_id')
            ->whereYear('payment.created_at', $year);

        if (!empty($filterSponsor)) {
            $representativesQuery->where('sponsors.name', $filterSponsor);
        }

        $representatives = $representativesQuery
            ->orderBy('sponsors.name')
            ->orderBy('payment.created_at', 'desc')
            ->get();

        /*
         |---------------------------------------------------------------------------------
         | Sponsors with no representative attendance this year
         |---------------------------------------------------------------------------------
         */
        $attendedSponsorIds = \Illuminate\Support\Facades\DB::table('payment')
            ->join('users_event', function ($join) {
                $join->on('users_event.payment_id', '=', 'payment.id')
                    ->on('users_event.users_id', '=', 'payment.member_id');
            })
            ->whereYear('payment.created_at', $year)
            ->whereNotNull('users_event.present')
            ->whereNotNull('payment.sponsor_id')
            ->pluck('payment.sponsor_id')
            ->unique();

        $nonAttendSponsors = Sponsor::with(['pics', 'representatives', 'members'])
            ->where('status', 'publish')
            ->whereNotIn('id', $attendedSponsorIds)
            ->orderBy('name')
            ->get();

        // Events for the "Add to Event" modal
        $events = Events::orderBy('start_date', 'desc')
            ->whereYear('start_date', '>=', now()->year - 2)
            ->get(['id', 'name', 'slug', 'start_date']);

        // All publish sponsors with their members for the modal selector
        $allSponsorsWithMembers = Sponsor::with('members')
            ->where('status', 'publish')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sponsorMembersMap = [];
        foreach ($allSponsorsWithMembers as $s) {
            $members = [];
            foreach ($s->members as $m) {
                $members[] = ['id' => $m->id, 'name' => $m->name];
            }
            $sponsorMembersMap[$s->id] = $members;
        }

        return view('admin.sponsor.representative.index', [
            'representatives'       => $representatives,
            'sponsorList'           => $sponsorList,
            'year'                  => $year,
            'filterSponsor'         => $filterSponsor,
            'nonAttendSponsors'     => $nonAttendSponsors,
            'events'                => $events,
            'allSponsorsWithMembers' => $allSponsorsWithMembers,
            'sponsorMembersMap'     => $sponsorMembersMap,
        ]);
    }

    public function addMemberToEvent(Request $request)
    {
        try {
            $userId     = $request->user_id;
            $eventId    = $request->event_id;
            $sponsorId  = $request->sponsor_id;
            $sendEmail  = $request->send_email;

            $findEvent = Events::find($eventId);
            if (!$findEvent) {
                return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
            }

            $existing = Payment::where('member_id', $userId)
                ->where('events_id', $eventId)
                ->first();
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Member is already registered for this event.'], 409);
            }

            $findUser = User::where('users.id', $userId)
                ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
                ->leftJoin('company', 'company.users_id', '=', 'users.id')
                ->select('users.id as users_id', 'users.*', 'company.*', 'profiles.*')
                ->first();

            if (!$findUser) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            $codePayment = strtoupper(Str::random(7));
            $image       = QrCode::format('png')->size(200)->errorCorrection('H')->generate($codePayment);
            $outputFile  = '/public/uploads/payment/qr-code/img-' . time() . '.png';
            $dbPath      = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
            Storage::disk('local')->put($outputFile, $image);

            $payment                      = new Payment();
            $payment->member_id           = $userId;
            $payment->events_id           = $eventId;
            $payment->sponsor_id          = $sponsorId;
            $payment->package             = 'sponsor';
            $payment->tickets_id          = 6;
            $payment->code_payment        = $codePayment;
            $payment->qr_code             = $dbPath;
            $payment->status_registration = 'Paid Off';
            $payment->pic_id              = Auth::id();
            $payment->save();

            $register             = new UserRegister();
            $register->users_id   = $userId;
            $register->events_id  = $eventId;
            $register->payment_id = $payment->id;
            $register->save();

            if (!empty($sendEmail)) {
                $data = [
                    'code_payment'    => $codePayment,
                    'create_date'     => date('d, M Y H:i'),
                    'users_name'      => $findUser->name,
                    'users_email'     => $findUser->email,
                    'phone'           => $findUser->phone,
                    'job_title'       => $findUser->job_title,
                    'company_name'    => $findUser->company_name,
                    'company_address' => $findUser->address,
                    'events_name'     => $findEvent->name,
                    'start_date'      => $findEvent->start_date,
                    'end_date'        => $findEvent->end_date,
                    'start_time'      => $findEvent->start_time,
                    'end_time'        => $findEvent->end_time,
                    'image'           => $dbPath,
                ];

                ini_set('max_execution_time', 300);
                $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
                    ->loadView('email.ticket', $data);

                $email = $findUser->email;
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($codePayment . ' - Your registration is approved for ' . $findEvent->name);
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
            }

            return response()->json(['success' => true, 'message' => 'Member successfully registered to ' . $findEvent->name . '.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
