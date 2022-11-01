<?php

namespace App\Http\Controllers\Callback;

use App\Helpers\EmailSender;
use App\Helpers\XenditInvoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Payments\Payment;
use App\Repositories\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;


class XenditCallbackController extends Controller
{

    public function invoice()
    {
        try {
            //        contoh respon callback invoice
            //        "id": "579c8d61f23fa4ca35e52da4",
            //        "external_id": "invoice_123124123",
            //        "user_id": "5781d19b2e2385880609791c",
            //        "is_high": true,
            //        "payment_method": "BANK_TRANSFER",
            //        "status": "PAID",
            //        "merchant_name": "Xendit",
            //        "amount": 50000,
            //        "paid_amount": 50000,
            //        "bank_code": "PERMATA",
            //        "paid_at": "2016-10-12T08:15:03.404Z",
            //        "payer_email": "wildan@xendit.co",
            //        "description": "This is a description",
            //        "adjusted_received_amount": 47500,
            //        "fees_paid_amount": 0,
            //        "updated": "2016-10-10T08:15:03.404Z",
            //        "created": "2016-10-10T08:15:03.404Z",
            //        "currency": "IDR",
            //        "payment_channel": "PERMATA",
            //        "payment_destination": "888888888888"

            $id = request('id');
            $external_id = request('external_id');
            $user_id = request('user_id');
            $is_high = request('is_high');
            $payment_method = request('payment_method');
            $status = request('status');
            $merchant_name = request('merchant_name');
            $amount = request('amount');
            $paid_amount = request('paid_amount');
            $bank_code = request('bank_code');
            $paid_at = request('paid_at');
            $payer_email = request('payer_email');
            $description = request('description');
            $adjusted_received_amount = request('adjusted_received_amount');
            $fees_paid_amount = request('fees_paid_amount');
            $updated = request('updated');
            $created = request('created');
            $currency = request('currency');
            $payment_channel = request('payment_channel');
            $payment_destination = request('payment_destination');

            $check = Payment::where('code_payment', '=', $external_id)->first();
            if (!empty($check)) {
                if (in_array($status, ['PAID'])) {
                    $check->status = "Paid Off";
                    $check->payment_method = $payment_method;
                    $check->link = null;

                    $findUser = Payment::where('code_payment', $external_id)
                        ->join('xtwp_users_dmc as a', 'a.id', 'payment.member_id')
                        ->first();

                    $data = [
                        'users_name' => $findUser->name,
                        'users_email' => $findUser->email,
                        'phone' => $findUser->phone,
                        'company_name' => $findUser->company_name,
                        'company_address' => $findUser->address,
                        'status' => 'Paid Off',
                        'events_name' => 'Djakarta Mining Club and Coal Club Indonesia x McCloskey by OPIS',
                        'code_payment' => $findUser->code_payment,
                        'created_date' => date('d, M Y H:i'),
                        'package_name' => $findUser->package,
                        'price' => number_format($findUser->price, 0, ',', '.'),
                        'total_price' => number_format($findUser->price, 0, ',', '.'),
                        'voucher_price' => number_format(0, 0, ',', '.'),
                    ];
                    $pdf = Pdf::loadView('email.invoice-new', $data);
                    Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($findUser->email);
                        $message->subject('Thank You For Payment - Indonesia Miner ');
                        $message->attachData($pdf->output(), 'E-Receipt_' . $findUser->code_payment . '.pdf');
                    });
                    $res['api_status'] = 1;
                    $res['api_message'] = 'Payment status is updated';
                } else {
                    $check->status = "Expired";
                    $check->link = null;
                    $res['api_status'] = 0;
                    $res['api_message'] = 'Payment is Expired';
                }
                $check->save();
            } else {
                $res['api_status'] = 0;
                $res['api_message'] = 'Payment is not Found';
            }
            return response()->json($res, 200);
        } catch (\Exception $msg) {
            $res['api_status'] = 0;
            $res['api_message'] = $msg->getMessage();
            return response()->json($res, 500);
        }
    }
}
