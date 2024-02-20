<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventsDetailParticipantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function detail_participant($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        $findParticipant = Payment::join('events', 'events.id', 'payment.events_id')
            ->join('users', 'users.id', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.users_id', 'users.id')
            ->leftJoin('users_event', function ($join) use ($findEvent) {
                $join->on('users_event.users_id', '=', 'payment.member_id')
                    ->where('users_event.events_id', '=', $findEvent->id);
            })
            ->leftJoin('users as users_present', 'users_present.id', '=', 'users_event.pic_id_present')
            ->leftJoin('users as users_reminder', 'users_reminder.id', '=', 'users_event.pic_id_reminder')
            ->leftJoin('users as users_reminder_wa', 'users_reminder_wa.id', '=', 'users_event.pic_id_reminder_wa')
            ->where([
                ['payment.events_id', $findEvent->id],
                ['payment.status_registration', 'Paid Off']
            ])
            ->select(
                'users.id as users_id',
                'users.name',
                'users.email',
                'payment.code_payment',
                'payment.package',
                'profiles.job_title',
                'profiles.phone',
                'profiles.prefix_phone',
                'profiles.fullphone',
                'company.country',
                'company.address',
                'company.company_name',
                'company.prefix',
                'users_event.present',
                'users_present.name as name_present',
                'users_event.pic_id_present',
                'users_event.reminder',
                'users_event.reminder_wa',
                'users_reminder.name as name_reminder',
                'users_reminder.name as name_reminder_wa',
                'users_event.pic_id_reminder',
                'users_event.created_at as created',
                'users_event.updated_at as updated',
                'events.id as events_id',
                'payment.created_at as payment_updated',
                'payment.id as payment_id',
                'events.start_date',
                'events.end_date',
                'company.company_category',
                'company.company_other'
            )
            ->orderBy('payment.id', 'desc')
            ->get();
        $checkin = UserRegister::where('events_id', $findEvent->id)->whereNotNull('present')->count();
        $absent = Payment::leftJoin('users_event', 'users_event.payment_id', '=', 'payment.id')
            ->where('payment.events_id', $findEvent->id)
            ->where('payment.status_registration', 'Paid Off')
            ->whereNull('users_event.present')
            ->count();

        $data = [
            'list' => $findParticipant,
            'date' => $findEvent->start_date,
            'checkin' => $checkin,
            'absent' => $absent,
        ];
        return view('admin.events.event-detail-participant', $data);
    }

    public function sendParticipant(Request $request)
    {
        try {
            // dd($request->all());
            $users_id = $request->users_id;
            $events_id = $request->events_id;
            $method = $request->method;
            $payment_id = $request->payment_id;
            $check = Payment::where('id', $payment_id)->first();
            $findUsers = User::where('id', $check->member_id)->first();
            $findProfile = ProfileModel::where('users_id', $check->member_id)->first();
            $findCompany = CompanyModel::where('users_id', $check->member_id)->first();
            $findEvent = Events::where('id', $events_id)->first();
            $email = $findUsers->email;
            $codePayment = $check->code_payment;
            $pic = Auth::id();
            if ($method == 'confirmation') {
                $save = UserRegister::where('users_id', $users_id)->where('events_id', $events_id)->first();
                if ($save == null) {
                    $save = new UserRegister();
                }
                $save->users_id = $users_id;
                $save->events_id = $events_id;
                $save->payment_id = $payment_id;
                $save->pic_id_reminder = $pic;
                $save->reminder = Carbon::now();
                $save->save();

                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($check->code_payment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $findEvent = Events::where('id', $events_id)->first();
                $data = [
                    'code_payment' => $check->code_payment,
                    'create_date' => date('d, M Y H:i'),
                    'users_name' => $findUsers->name,
                    'users_email' => $findUsers->email,
                    'phone' => $findProfile->phone,
                    'company_name' => $findCompany->company_name,
                    'company_address' => $findCompany->address,
                    'job_title' => $findProfile->job_title,
                    'events_name' => $findEvent->name,
                    'start_date' => $findEvent->start_date,
                    'end_date' => $findEvent->end_date,
                    'start_time' => $findEvent->start_time,
                    'end_time' => $findEvent->end_time,
                    'image' => $db
                ];
                // dd("sukses");
                // ini_set('max_execution_time', 120);

                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($codePayment . ' - Confirmation Reminder for ' . $findEvent->name);
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
                return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('success', 'Successfully Send Confirmation');
            } elseif ($method == 'confirmation_wa') {
                // dd($method);
                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($check->code_payment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $findEvent = Events::where('id', $events_id)->first();
                $data = [
                    'code_payment' => $check->code_payment,
                    'create_date' => date('d, M Y H:i'),
                    'users_name' => $findUsers->name,
                    'users_email' => $findUsers->email,
                    'phone' => $findProfile->phone,
                    'company_name' => $findCompany->company_name,
                    'company_address' => $findCompany->address,
                    'job_title' => $findProfile->job_title,
                    'events_name' => $findEvent->name,
                    'start_date' => $findEvent->start_date,
                    'end_date' => $findEvent->end_date,
                    'start_time' => $findEvent->start_time,
                    'end_time' => $findEvent->end_time,
                    'image' => $db
                ];
                // dd("sukses");
                // ini_set('max_execution_time', 120);

                $pdf = Pdf::loadView('email.ticket', $data);
                $filename = 'ticket_' . $findUsers->name . '_' . time() . '.pdf';

                // Store the PDF in the desired directory within the storage folder
                $pdfPath = 'public/ticket/' . $filename;
                $db = '/storage/ticket/' . $filename;
                Storage::put($pdfPath, $pdf->output());
                $send = new WhatsappApi();
                $send->phone = $findProfile->phone;
                $send->message = '📌"REMINDER to attend ' . $findEvent->name . '"

Hi ' . $findUsers->name . ',

This is a confirmation that you are registered to attend our event ' . $findEvent->name . ' on Thursday February 22, 2024 at ' . $findEvent->location . ', starting at ' . date('h.i a', strtotime($findEvent->start_time)) . ' - ' . date('h.i a', strtotime($findEvent->end_time)) . ' (WIB)

If you are confirmed to attend this event, please REPLY YES to this message.

We`re looking forward to seeing you there, share insightful sessions and network!

Your presence will be greatly appreciated. Thank you 😊🙏🏻

Regards,
*Secretariat Djakarta Mining Club
';
                $send->document = asset($db);
                // $send->WhatsappMessageWithDocument();
                $send->WhatsappMessage();
                if ($send->res == 'invalid') {
                    return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('error', 'Whatsapp tidak ditemukan');
                }
                $save = UserRegister::where('users_id', $users_id)->where('events_id', $events_id)->first();
                if ($save == null) {
                    $save = new UserRegister();
                }
                $save->users_id = $users_id;
                $save->events_id = $events_id;
                $save->payment_id = $payment_id;
                $save->pic_id_reminder_wa = $pic;
                $save->reminder_wa = Carbon::now();
                $save->save();
                return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('success', 'Success send wa');
            } else {
                $save = UserRegister::where('users_id', $users_id)->where('events_id', $events_id)->first();
                if ($save == null) {
                    $save = new UserRegister();
                }
                $save->users_id = $users_id;
                $save->events_id = $events_id;
                $save->payment_id = $payment_id;
                $save->pic_id_present = $pic;
                $save->present = Carbon::now();
                $save->save();

                return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('success', 'Successfully Present');
            }
        } catch (\Exception $e) {
            // Handle the exception
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
