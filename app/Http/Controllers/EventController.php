<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Models\Events\UserRegister;
use App\Models\MemberModel;
use App\Models\Payments\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Xendit\Invoice;
use Xendit\Xendit;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function view()
    {
        return view('register_event.index');
    }

    public function view2()
    {
        return view('register_event.index2');
    }

    public function payment_personal(Request $request)
    {
        $prefix = $request->prefix;
        $company_name = $request->company_name . ", " . $prefix;
        $phone = $request->phone;
        $email = $request->email;
        $name = $request->name;
        $job_title = $request->job_title;
        $company_website = $request->company_website;
        $country = $request->country;
        $address = $request->address;
        $city = $request->city;
        $office_number = $request->office_number;
        $portal_code = $request->portal_code;
        $company_category = $request->company_category;
        $company_other = $request->company_other;

        if ($company_category == 'other') {
            $company_category = $company_other;
        }
        $paymentMethod = $request->paymentMethod;

        $user = MemberModel::firstOrNew(
            ['email' =>  $email],
            ['phone' => $phone]
        );
        $user->phone = $phone;
        $user->company_name = $company_name;
        $user->job_title = $job_title;
        $user->name = $name;
        $user->company_website = $company_website;
        $user->company_category = $company_category;
        $user->address = $address;
        $user->country = $country;
        $user->city = $city;
        $user->office_number = $office_number;
        $user->portal_code = $portal_code;
        $user->save();

        if ($paymentMethod == 'member') {
            $total_price = 900000;
        } else if ($paymentMethod == 'nonmember') {
            $total_price = 1000000;
        } else if ($paymentMethod == 'onsite') {
            $total_price = 1200000;
        } else {
            $total_price  = 0;
        }
        $codePayment = strtoupper(Str::random(7));
        if ($paymentMethod != 'free') {

            // init xendit
            $isProd = env('XENDIT_ISPROD');
            if ($isProd) {
                $secretKey = env('XENDIT_SECRET_KEY_PROD');
            } else {
                $secretKey = env('XENDIT_SECRET_KEY_TEST');
            }
            // params invoice
            Xendit::setApiKey($secretKey);
            $date = date('d-m-Y H:i:s');

            $params = [
                'external_id' => $codePayment,
                'payer_email' => $email,
                'description' => 'Invoice Event DMC',
                'amount' => $total_price,
                'success_redirect_url' => 'https://djakarta-miningclub.com',
                'failure_redirect_url' => url('/'),
            ];
            $createInvoice = Invoice::create($params);
            $linkPay = $createInvoice['invoice_url'];
        }


        $payment = Payment::firstOrNew(['member_id' => $user->id]);
        if ($paymentMethod == 'free') {
            $payment->package = $paymentMethod;
            $payment->price = $total_price;
            $payment->status = 'Approve';
            // $payment->link = null;
        } else {
            $payment->package = $paymentMethod;
            $payment->price = $total_price;
            $payment->status = 'Waiting';
            $payment->link = $linkPay;
            $payment->code_payment = $codePayment;
        }
        $payment->save();

        if ($paymentMethod == 'free') {
            $user_event = UserRegister::firstOrNew(
                ['users_id' =>  $user->id],
                ['payment_id' => $payment->id]
            );
            $user_event->save();
        }



        $data = [
            'code_payment' => 'DMC-' . time(),
            'create_date' => date('d, M Y H:i'),
            'due_date' => date('d, M Y H:i', strtotime($date . ' +1 day')),
            'users_name' => $name,
            'users_email' => $email,
            'phone' => $phone,
            'company_name' => $company_name,
            'company_address' => $address,
            'status' => 'WAITING',
            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia x McCloskey by OPIS',
            'price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => $linkPay
        ];

        if ($paymentMethod == 'free') {
            $send = new EmailSender();
            $send->to = $email;
            $send->from = env('EMAIL_SENDER');
            $send->data = $data;
            $send->subject = 'Registration successfully to The 53rd Djakarta Mining Club Networking Event';
            $send->template = 'email.success-register-event';
            $send->sendEmail();
            return redirect()->back()->with('alert', 'Register Successfully');
        } else {
            // $pdf = Pdf::loadView('email.invoice-new', $data);
            Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('Invoice Events - Payment');
                // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
            });
            return redirect()->back()->with('alert', 'Check your email for payment Invoice !!!');
        }
    }
}
