<?php

namespace App\Http\Controllers\Callback;

use App\Helpers\EmailSender;
use App\Helpers\XenditInvoice;
use App\Repositories\CompanyRegPay;
use App\Repositories\CompanyRegSponsors;
use App\Repositories\Payment;
use App\Repositories\UsersDelegate;
use App\Repositories\UsersCompany;
use App\Repositories\EventsCompany;
use App\Traits\Payment as TraitPayment;
use App\Traits\PaymentRegisterSponsors as TraitPaymentRegisterSponsors;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Company;
use PDF;

class XenditCallbackController extends Controller
{

    public function postInvoice(Request $request)
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


            $res['api_status'] = 0;
            $res['api_message'] = 'Payment is not found';

            return response()->json($res, 200);
        } catch (\Exception $msg) {
            $res['api_status'] = 0;
            $res['api_message'] = $msg->getMessage();
            return response()->json($res, 500);
        }
    }
}
