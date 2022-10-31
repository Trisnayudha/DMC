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
        $explore = $request->explore;
        $cci = $request->cci;
        // dd($request->all());

        $paymentMethod = $request->paymentMethod;
        if ($paymentMethod == 'member') {
            $total_price = 900000;
        } else {
            $total_price = 1000000;
        }
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
        $convert = strtotime($date);

        $params = [
            'external_id' => 'DMC-' . $convert,
            'payer_email' => $email,
            'description' => 'Invoice Event DMC',
            'amount' => $total_price,
            'success_redirect_url' => url('/'),
            'failure_redirect_url' => url('/'),
        ];
        // $createInvoice = Invoice::create($params);
        $data = [
            'code_payment' => 'DMC-' . time(),
            'create_date' => date('d, M Y H:i'),
            'due_date' => date('d, M Y H:i', strtotime($date . ' +1 day')),
            'users_name' => $name,
            'users_email' => $email,
            'phone' => $phone,
            'company_name' => $company_name,
            'address_company' => $address,
            'status' => 'WAITING',
            'events_name' => 'DMC',
            'event_price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => null
        ];
        $pdf = Pdf::loadView('email.invoice-new', $data);
        // $content = $pdf->download()->getOriginalContent();
        // $output_file = '/public/ticket/ticket-' . time() . '.pdf';
        // Storage::disk('local')->put($output_file, $content); //storage/app/public/img/qr-code/img-1557309130.png
        Mail::send('email.test', $data, function ($message) use ($pdf) {
            $message->from(env('EMAIL_SENDER'));
            $message->to('yudha@indonesiaminer.com');
            $message->subject('test invoice');
            $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
        });
        return view('email.invoice-new', $data);
        // $content = $pdf->download()->getOriginalContent();
        // $output_file = '/uploads/ticket/ticket-' . time() . '.pdf';
        // dd($createInvoice);
        // payment log invoice
        // XenditInvoice::saveInvoice($save->id, $users_id, $createInvoice);
        // $linkPay = $createInvoice['invoice_url'];
    }

    public function register_free(Request $request)
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

        $payment = Payment::firstOrNew(['member_id' => $user->id]);
        if ($paymentMethod == 'free') {
            $payment->package = $paymentMethod;
            $payment->price = 0;
            $payment->status = 'Approve';
            // $payment->link = null;
        } else {
            $payment->status = 'Waiting';
            // $payment->link = null;
        }
        $payment->save();

        if ($paymentMethod == 'free') {
            $user_event = UserRegister::firstOrNew(
                ['users_id' =>  $user->id],
                ['payment_id' => $payment->id]
            );
            $user_event->save();
        }

        if ($paymentMethod == 'member') {
            $total_price = 900000;
        } else if ($paymentMethod == 'nonmember') {
            $total_price = 1000000;
        } else {
            $total_price  = 0;
        }
        // // init xendit
        // $isProd = env('XENDIT_ISPROD');
        // if ($isProd) {
        //     $secretKey = env('XENDIT_SECRET_KEY_PROD');
        // } else {
        //     $secretKey = env('XENDIT_SECRET_KEY_TEST');
        // }
        // // params invoice
        // Xendit::setApiKey($secretKey);
        $date = date('d-m-Y H:i:s');
        // $convert = strtotime($date);

        // $params = [
        //     'external_id' => 'DMC-' . $convert,
        //     'payer_email' => $email,
        //     'description' => 'Invoice Event DMC',
        //     'amount' => $total_price,
        //     'success_redirect_url' => url('/'),
        //     'failure_redirect_url' => url('/'),
        // ];
        // $createInvoice = Invoice::create($params);
        $data = [
            'code_payment' => 'DMC-' . time(),
            'create_date' => date('d, M Y H:i'),
            'due_date' => date('d, M Y H:i', strtotime($date . ' +1 day')),
            'users_name' => $name,
            'users_email' => $email,
            'phone' => $phone,
            'company_name' => $company_name,
            'address_company' => $address,
            'status' => 'WAITING',
            'events_name' => 'DMC',
            'event_price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => null
        ];

        if ($paymentMethod == 'free') {
            $send = new EmailSender();
            $send->to = $email;
            $send->from = env('EMAIL_SENDER');
            $send->data = $data;
            $send->subject = 'Registration successfully to The 53rd Djakarta Mining Club Networking Event';
            $send->template = 'email.success-register-event';
            $send->sendEmail();
        } else {
            $pdf = Pdf::loadView('email.invoice-new', $data);
            Mail::send('email.test', $data, function ($message) use ($pdf) {
                $message->from(env('EMAIL_SENDER'));
                $message->to('yudha@indonesiaminer.com');
                $message->subject('test invoice');
                $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
            });
        }
        return redirect()->back()->with('alert', 'Register Successfully');
    }
}
