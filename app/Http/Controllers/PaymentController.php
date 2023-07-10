<?php

namespace App\Http\Controllers;

use App\Models\Company\CompanyModel;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Xendit\Invoice;
use Xendit\Xendit;

class PaymentController extends Controller
{
    public function index()
    {
        $payment = Payment::join('xtwp_users_dmc', 'xtwp_users_dmc.id', 'payment.member_id')->get();
        // dd($payment);
        $data = [
            'payment' => $payment
        ];
        return view('admin.payment.payment', $data);
    }

    public function renewal(Request $request)
    {
        try {
            $check = Payment::findOrFail($request->id);
            $findUsers = User::findOrFail($check->member_id);
            $findTicket = EventsTicket::findOrFail($check->tickets_id);
            $findProfile = ProfileModel::where('users_id', $check->member_id)->first();
            $findCompany = CompanyModel::where('users_id', $check->member_id)->first();

            $isProd = env('XENDIT_ISPROD');
            $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
            Xendit::setApiKey($secretKey);

            $params = [
                'external_id' => $check->code_payment,
                'payer_email' => $findUsers->email,
                'description' => 'Invoice Event DMC',
                'amount' => $findTicket->price_rupiah,
                'success_redirect_url' => 'https://djakarta-miningclub.com',
                'failure_redirect_url' => url(''),
            ];
            $createInvoice = Invoice::create($params);
            $linkPay = $createInvoice['invoice_url'];

            $date = date('d, M Y H:i');
            $dueDate = date('d, M Y H:i', strtotime($date . ' +1 day'));

            $data = [
                'code_payment' => $check->code_payment,
                'create_date' => $date,
                'due_date' => $dueDate,
                'users_name' => $findUsers->name,
                'users_email' => $findUsers->email,
                'phone' => $findProfile->phone,
                'company_name' => $findCompany->company_name,
                'company_address' => $findCompany->address,
                'status' => 'WAITING',
                'events_name' => 'Mineral Trends 2023',
                'price' => number_format($findTicket->price_rupiah, 0, ',', '.'),
                'voucher_price' => 0,
                'total_price' => number_format($findTicket->price_rupiah, 0, ',', '.'),
                'link' => $linkPay
            ];

            $email = $findUsers->email;

            $check->status_registration = 'Waiting';
            $check->link = $linkPay;
            $check->save();

            // Generate PDF outside of the request handling
            // $pdf = Pdf::loadView('email.invoice-new', $data);

            Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('Invoice - Waiting for Payment');
                // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
            });

            return redirect()->back()->with('success', 'Check your email for payment Invoice !!!');
        } catch (\Exception $e) {
            // Handle the exception
            return redirect()->back()->with('error', 'An error occurred. Please try again later.')->withErrors([$e->getMessage()]);
        }
    }
}
