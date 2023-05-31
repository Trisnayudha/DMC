<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Models\SpecialEvent\SpecialEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecialEventController extends Controller
{

    public function free()
    {
        return view('special_event.free');
    }

    public function index()
    {
        $this->middleware('auth');
        $list = SpecialEvent::orderby('id', 'desc')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.special-event.index', $data);
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $company_name = $request->company;
        $phone = $request->phone;
        $job_title = $request->job_title;


        $findUser = SpecialEvent::where('email', $email)->first();

        if (empty($findUser)) {
            $codePayment = strtoupper(Str::random(7));
            $save = new SpecialEvent();
            $save->name = $name;
            $save->email = $email;
            $save->company_name = $company_name;
            $save->phone = $phone;
            $save->job_title = $job_title;
            $save->status = 'Waiting';
            $save->code_booking = $codePayment;
            $save->save();
            $date = date('d-m-Y H:i:s');
            $data = [
                'users_name' => $name,
                'events_name' => 'the Leadership Luncheon - The Future of Sustainable Mining in Indonesia',
            ];
            $send = new EmailSender();
            $send->to = $email;
            $send->from = env('EMAIL_SENDER');
            $send->data = $data;
            $send->subject = 'Thank you for registering Leadership Luncheon - The Future of Sustainable Mining in Indonesia ';
            $send->template = 'email.special_event.waiting-approval';
            $send->sendEmail();

            $wa = new WhatsappApi();
            $wa->phone = '081288761410';
            $wa->message = '
Hai Mas Damun,

Ada pendaftar DS baru nih..

Detail
Name :' . $name . '
Job Title :' . $job_title . '
Email :' . $email . '
Company :' . $company_name . '

Cemiwiw
';
            $wa->WhatsappMessage();
            return redirect()->back()->with('success', 'Successfully register event');
        } else {
            return redirect()->back()->with('error', 'Email already register, please check your inbox email. Thank you');
        }
    }

    public function request(Request $request)
    {
        // dd($request->all());
        $id = $request->id;
        $status = $request->val;
        $findUser = SpecialEvent::where('id', $id)->first();

        if (!empty($findUser)) {
            $name = $findUser->name;
            $email = $findUser->email;
            $code_booking = $findUser->code_booking;
            $data = [
                'users_name' => $name,
                'code_payment' => $code_booking
            ];
            if ($status == 'approve') {
                $findUser->status = 'Approve';
                $findUser->save();
                $send = new EmailSender();
                $send->from = env('EMAIL_SENDER');
                $send->to = $email;
                $send->data = $data;
                $send->subject = 'Your registration for Leadership Luncheon - The Future of Sustainable Mining in Indonesia has now been approved';
                $send->name = $name;
                $send->template = 'email.special_event.approval-event';
                $send->sendEmail();
                return redirect()->back()->with('success', 'Successfully Reject Register');
            } else {
                $findUser->status = 'Reject';
                $findUser->save();
                $send = new EmailSender();
                $send->from = env('EMAIL_SENDER');
                $send->to = $email;
                $send->data = $data;
                $send->subject = '[FULLY BOOKED] Leadership Luncheon - The Future of Sustainable Mining in Indonesia';
                $send->name = $name;
                $send->template = 'email.special_event.reject-event';
                $send->sendEmail();
                return redirect()->back()->with('success', 'Successfully Reject Register');
            }
        }
    }
}
