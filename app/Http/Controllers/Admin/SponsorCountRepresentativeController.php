<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use App\Services\Sponsors\SponsorContactRowBuilder;
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
            'payment.id as payment_id',
            'payment.code_payment',
            'payment.qr_code',
            'users.name as representative_name',
            'users.email as representative_email',
            'profiles.phone as representative_phone',
            'profiles.job_title as representative_job_title',
            'company.company_name as representative_company',
            'company.address as representative_address',
            'sponsors.name as company',
            'payment.created_at as attend_time',
            'events.name as event_name',
            'events.start_date',
            'events.end_date',
            'events.start_time',
            'events.end_time',
            'users_event.present as present'
        )
            ->join('users', 'payment.member_id', '=', 'users.id')
            ->join('sponsors', 'payment.sponsor_id', '=', 'sponsors.id')
            ->join('events', 'payment.events_id', '=', 'events.id')
            ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
            ->leftJoin('company', 'company.users_id', '=', 'users.id')
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
            ->orderBy('events.start_date', 'desc')
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

        // All publish sponsors with their contacts (member + PIC + rep + billing,
        // dedup by email, data user diutamakan) for the modal selector
        $allSponsorsWithMembers = Sponsor::with(['members', 'pics', 'representatives', 'billings'])
            ->where('status', 'publish')
            ->orderBy('name')
            ->get();

        $builder = new SponsorContactRowBuilder();
        $sponsorContactsMap = [];
        foreach ($allSponsorsWithMembers as $s) {
            $contacts = $builder->build($s)
                // user yang sudah punya akun tampil duluan di dropdown
                ->sortByDesc(function ($row) {
                    return $row['user_id'] !== null;
                })
                ->map(function ($row) {
                    return [
                        'user_id' => $row['user_id'],
                        'name'    => $row['name'],
                        'email'   => $row['email'],
                        'phone'   => $row['phone'],
                        'title'   => $row['title'],
                        'role'    => $row['role'],
                    ];
                })
                ->values();

            // Sertakan info company/sponsor untuk prefill form New Contact
            $sponsorContactsMap[$s->id] = [
                'contacts'         => $contacts,
                'company_name'     => $s->branding_name ?: $s->name,
                'company_website'  => $s->website ?? '',
                'company_category' => $s->company_category ?? '',
                'address'          => $s->address ?? '',
                'office_number'    => $s->office_number ?? '',
                'country'          => $s->country ?? 'Indonesia',
                'prefix'           => $s->prefix ?? 'PT',
            ];
        }

        return view('admin.sponsor.representative.index', [
            'representatives'       => $representatives,
            'sponsorList'           => $sponsorList,
            'year'                  => $year,
            'filterSponsor'         => $filterSponsor,
            'nonAttendSponsors'     => $nonAttendSponsors,
            'events'                => $events,
            'allSponsorsWithMembers' => $allSponsorsWithMembers,
            'sponsorContactsMap'    => $sponsorContactsMap,
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
                    $message->subject('[Ticket #' . $codePayment . '] Entry Confirmation – ' . $findEvent->subject_name);
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
            }

            return response()->json(['success' => true, 'message' => 'Member successfully registered to ' . $findEvent->name . '.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Resend e-ticket via email dan/atau WA dengan pesan yang bisa dikustomisasi.
     * Data asli (payment, user) tidak diubah.
     */
    public function resendTicket(Request $request, int $paymentId)
    {
        $request->validate([
            'send_email'    => 'nullable',
            'send_wa'       => 'nullable',
            'email_subject' => 'nullable|string|max:255',
            'email_body'    => 'nullable|string',
            'wa_message'    => 'nullable|string',
        ]);

        $payment = Payment::join('users', 'payment.member_id', '=', 'users.id')
            ->join('events', 'payment.events_id', '=', 'events.id')
            ->leftJoin('profiles', 'profiles.users_id', '=', 'users.id')
            ->leftJoin('company', 'company.users_id', '=', 'users.id')
            ->where('payment.id', $paymentId)
            ->select(
                'payment.id',
                'payment.code_payment',
                'payment.qr_code',
                'users.name',
                'users.email',
                'profiles.phone',
                'profiles.job_title',
                'company.company_name',
                'company.address',
                'events.name as event_name',
                'events.start_date',
                'events.end_date',
                'events.start_time',
                'events.end_time'
            )
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $sent = [];

        if ($request->send_email) {
            try {
                $data = [
                    'code_payment'    => $payment->code_payment,
                    'create_date'     => date('d, M Y H:i'),
                    'users_name'      => $payment->name,
                    'users_email'     => $payment->email,
                    'phone'           => $payment->phone,
                    'job_title'       => $payment->job_title,
                    'company_name'    => $payment->company_name,
                    'company_address' => $payment->address,
                    'events_name'     => $payment->event_name,
                    'start_date'      => $payment->start_date,
                    'end_date'        => $payment->end_date,
                    'start_time'      => $payment->start_time,
                    'end_time'        => $payment->end_time,
                    'image'           => $payment->qr_code,
                    'custom_body'     => $request->email_body ?: null,
                ];

                ini_set('max_execution_time', 300);
                $pdf     = Pdf::setOptions(['isRemoteEnabled' => true])->loadView('email.ticket', $data);
                $email   = $payment->email;
                $subject = $request->email_subject ?: ('[Ticket #' . $payment->code_payment . '] Entry Confirmation – ' . preg_replace('/^The\s+/i', '', $payment->event_name));

                \Illuminate\Support\Facades\Mail::send(
                    'email.approval-event',
                    $data,
                    function ($message) use ($email, $pdf, $subject, $payment) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($email);
                        $message->subject($subject);
                        $message->attachData($pdf->output(), $payment->code_payment . '-ticket.pdf');
                    }
                );
                $sent[] = 'email';
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Email failed: ' . $e->getMessage()], 500);
            }
        }

        if ($request->send_wa) {
            $phone = preg_replace('/[^0-9]/', '', $payment->phone ?? '');
            if (empty($phone)) {
                return response()->json(['success' => false, 'message' => 'No phone number on record for this contact.'], 422);
            }

            try {
                $wa          = new WhatsappApi();
                $wa->phone   = $phone;
                $wa->message = $request->wa_message ?: $this->defaultWaMessage($payment);
                $result      = $wa->WhatsappMessage();

                if ($result !== 'valid') {
                    return response()->json(['success' => false, 'message' => 'WA failed: ' . $result], 500);
                }
                $sent[] = 'WhatsApp';
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'WA failed: ' . $e->getMessage()], 500);
            }
        }

        if (empty($sent)) {
            return response()->json(['success' => false, 'message' => 'Please select at least one channel (Email or WA).'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Ticket resent via ' . implode(' & ', $sent) . '.']);
    }

    private function defaultWaMessage($payment): string
    {
        $date = $payment->start_date ? \Carbon\Carbon::parse($payment->start_date)->format('d M Y') : '-';
        return "Hi {$payment->name},\n\nYour e-ticket for *{$payment->event_name}* ({$date}) is ready.\n\nTicket Code: *{$payment->code_payment}*\n\nPlease show this code at the registration desk.\n\nThank you,\nDMC Team";
    }

    /**
     * Daftarkan orang yang belum ada di table users: buat/cari user by email,
     * upsert company + profile, lalu register ke event dengan payment.sponsor_id
     * terisi sehingga otomatis ter-tagging sebagai member sponsor tersebut.
     */
    public function addNewPersonToEvent(Request $request)
    {
        $request->validate([
            'sponsor_id'        => 'required|exists:sponsors,id',
            'event_id'          => 'required|exists:events,id',
            'name'              => 'required|string|max:255',
            'email'             => 'required|email',
            'register_as_member' => 'nullable',
        ]);

        try {
            $findEvent = Events::find($request->event_id);
            $user      = $this->upsertPerson($request);

            $existing = Payment::where('member_id', $user->id)
                ->where('events_id', $findEvent->id)
                ->first();
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'This email is already registered for this event.'], 409);
            }

            $ticket = $request->input('ticket', 'sponsor');

            if (in_array($ticket, ['free', 'sponsor'])) {
                $this->registerPaidOff($request, $user, $findEvent, $ticket);
                $message = $user->name . ' successfully registered to ' . $findEvent->name . '.';
            } else {
                $this->registerWithInvoice($request, $user, $findEvent, $ticket);
                $message = $user->name . ' registered to ' . $findEvent->name . '. Payment invoice created.';
            }

            // Daftarkan sebagai member pending + kirim WA ke Mba Dira
            if (!empty($request->register_as_member)) {
                $user->status_member = 'pending';
                $user->save();

                $sponsor = Sponsor::find($request->sponsor_id);
                $sponsorName = $sponsor ? ($sponsor->branding_name ?: $sponsor->name) : '-';

                try {
                    $wa          = new WhatsappApi();
                    $wa->phone   = '6281385080008';
                    $wa->message =
                        "📋 *[Member Verification Request]*\n\n" .
                        "Halo Mbak Dira, ada permintaan verifikasi member baru dari sistem DMC.\n\n" .
                        "*Nama:* {$user->name}\n" .
                        "*Email:* {$user->email}\n" .
                        "*No. HP:* " . ($request->phone ?: '-') . "\n" .
                        "*Job Title:* " . ($request->job_title ?: '-') . "\n" .
                        "*Perusahaan:* {$sponsorName} *(Sponsor)*\n\n" .
                        "Kontak ini baru saja didaftarkan sebagai peserta event *{$findEvent->name}* " .
                        "dan belum pernah terdaftar sebagai member DMC sebelumnya.\n\n" .
                        "Mohon dilakukan verifikasi & approval pada laporan mingguan member. " .
                        "Status saat ini: *PENDING*\n\n" .
                        "Terima kasih! 🙏";
                    $wa->WhatsappMessage();
                } catch (\Exception $e) {
                    // WA gagal tidak boleh block proses registrasi
                }

                $message .= ' Member registration submitted (pending approval).';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Buat/cari user by email lalu lengkapi company + profile (pola add_invitation)
    private function upsertPerson(Request $request): User
    {
        $user = User::firstOrNew(['email' => $request->email]);
        $user->name   = $request->name;
        $user->email  = $request->email;
        $user->source = $user->exists ? $user->source : 'Event';
        $user->save();

        $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
        $company->prefix           = $request->prefix;
        $company->company_name     = $request->company_name;
        $company->company_website  = $request->company_website;
        $company->company_category = $request->company_category;
        $company->company_other    = $request->company_other;
        $company->address          = $request->address;
        $company->office_number    = $request->office_number;
        $company->country          = $request->country;
        $company->users_id         = $user->id;
        $company->save();

        if (!empty($request->company_name)) {
            CompanyModel::syncByName($request->company_name);
        }

        $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
        $profile->phone      = $request->phone;
        $profile->job_title  = $request->job_title;
        $profile->users_id   = $user->id;
        $profile->company_id = $company->id;
        $profile->save();

        return $user;
    }

    // Tiket free/sponsor: langsung Paid Off + QR + users_event (pola addMemberToEvent)
    private function registerPaidOff(Request $request, User $user, Events $findEvent, string $ticket): void
    {
        $codePayment = strtoupper(Str::random(7));
        $image       = QrCode::format('png')->size(200)->errorCorrection('H')->generate($codePayment);
        $outputFile  = '/public/uploads/payment/qr-code/img-' . time() . '.png';
        $dbPath      = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
        Storage::disk('local')->put($outputFile, $image);

        $payment                      = new Payment();
        $payment->member_id           = $user->id;
        $payment->events_id           = $findEvent->id;
        $payment->sponsor_id          = $request->sponsor_id;
        $payment->package             = $ticket;
        $payment->tickets_id          = 6;
        $payment->code_payment        = $codePayment;
        $payment->qr_code             = $dbPath;
        $payment->status_registration = 'Paid Off';
        $payment->pic_id              = Auth::id();
        $payment->save();

        $register             = new UserRegister();
        $register->users_id   = $user->id;
        $register->events_id  = $findEvent->id;
        $register->payment_id = $payment->id;
        $register->save();

        if (!empty($request->send_email)) {
            $data = [
                'code_payment'    => $codePayment,
                'create_date'     => date('d, M Y H:i'),
                'users_name'      => $user->name,
                'users_email'     => $user->email,
                'phone'           => $request->phone,
                'job_title'       => $request->job_title,
                'company_name'    => $request->company_name,
                'company_address' => $request->address,
                'events_name'     => $findEvent->name,
                'start_date'      => $findEvent->start_date,
                'end_date'        => $findEvent->end_date,
                'start_time'      => $findEvent->start_time,
                'end_time'        => $findEvent->end_time,
                'image'           => $dbPath,
            ];

            ini_set('max_execution_time', 300);
            $pdf   = Pdf::setOptions(['isRemoteEnabled' => true])->loadView('email.ticket', $data);
            $email = $user->email;
            Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('[Ticket #' . $codePayment . '] Entry Confirmation – ' . $findEvent->subject_name);
                $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
            });
        }
    }

    // Tiket berbayar: invoice Xendit + status Waiting (pola add_invitation)
    private function registerWithInvoice(Request $request, User $user, Events $findEvent, string $ticket): void
    {
        $prices    = ['member' => 900000, 'nonmember' => 1000000, 'onsite' => 1250000];
        $ticketIds = ['member' => 1, 'nonmember' => 2, 'onsite' => 9];

        $totalPrice  = $prices[$ticket] ?? 0;
        $codePayment = strtoupper(Str::random(7));

        $secretKey = env('XENDIT_ISPROD') ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
        \Xendit\Xendit::setApiKey($secretKey);
        $createInvoice = \Xendit\Invoice::create([
            'external_id'          => $codePayment,
            'payer_email'          => $user->email,
            'description'          => 'Invoice Event DMC',
            'amount'               => $totalPrice,
            'success_redirect_url' => 'https://djakarta-miningclub.com',
            'failure_redirect_url' => url('/'),
        ]);
        $linkPay = $createInvoice['invoice_url'];

        $payment                      = new Payment();
        $payment->member_id           = $user->id;
        $payment->events_id           = $findEvent->id;
        $payment->sponsor_id          = $request->sponsor_id;
        $payment->package             = $ticket;
        $payment->tickets_id          = $ticketIds[$ticket] ?? 6;
        $payment->code_payment        = $codePayment;
        $payment->payment_method      = 'Credit Card';
        $payment->status_registration = 'Waiting';
        $payment->link                = $linkPay;
        $payment->pic_id              = Auth::id();
        $payment->save();

        if (!empty($request->send_email)) {
            $date = date('d-m-Y H:i:s');
            $data = [
                'code_payment'    => $codePayment,
                'create_date'     => date('d, M Y H:i'),
                'due_date'        => date('d, M Y H:i', strtotime($date . ' +1 day')),
                'users_name'      => $user->name,
                'users_email'     => $user->email,
                'phone'           => $request->phone,
                'job_title'       => $request->job_title,
                'company_name'    => $request->company_name,
                'company_address' => $request->address,
                'status'          => 'WAITING',
                'events_name'     => $findEvent->name,
                'price'           => number_format($totalPrice, 0, ',', '.'),
                'voucher_price'   => 0,
                'total_price'     => number_format($totalPrice, 0, ',', '.'),
                'link'            => $linkPay,
            ];

            $email = $user->email;
            Mail::send('email.confirm_payment', $data, function ($message) use ($email, $findEvent, $codePayment) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('[Ticket #' . $codePayment . '] Waiting for Payment – ' . $findEvent->subject_name);
            });
        }
    }
}
