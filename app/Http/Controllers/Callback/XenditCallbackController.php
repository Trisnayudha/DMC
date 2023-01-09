<?php

namespace App\Http\Controllers\Callback;

use App\Helpers\EmailSender;
use App\Helpers\Notification;
use App\Helpers\WhatsappApi;
use App\Helpers\XenditInvoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentUsersVA;
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
                $findUser = Payment::where('code_payment', $external_id)
                    ->join('users as a', 'a.id', 'payment.member_id')
                    ->first();
                $findTicket = EventsTicket::where('id', '=', $findUser->tickets_id)->first();
                $data = [
                    'users_name' => $findUser->name,
                    'users_email' => $findUser->email,
                    'phone' => $findUser->phone,
                    'company_name' => $findUser->company_name,
                    'company_address' => $findUser->address,
                    'status' => 'Paid Off',
                    'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
                    'code_payment' => $findUser->code_payment,
                    'create_date' => date('d, M Y H:i'),
                    'package_name' => $findUser->package,
                    'price' => number_format($findTicket->price_rupiah, 0, ',', '.'),
                    'total_price' => number_format($findTicket->price_rupiah, 0, ',', '.'),
                    'voucher_price' => number_format(0, 0, ',', '.'),
                ];

                if ($payment_method == 'CREDIT_CARD') {
                    $check->status_registration = "Paid Off";
                    $check->payment_method = $payment_method;
                    $check->link = null;
                    $UserEvent = new UserRegister();
                    $UserEvent->users_id = $check->member_id;
                    $UserEvent->events_id = $check->events_id;
                    $UserEvent->payment_id = $check->id;
                    $UserEvent->save();

                    $pdf = Pdf::loadView('email.invoice-new', $data);
                    Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($findUser->email);
                        $message->subject('Thank you for payment - The 53rd Djakarta Mining Club Networking Event');
                        $message->attachData($pdf->output(), 'E-Receipt_' . $findUser->code_payment . '.pdf');
                    });

                    $send = new WhatsappApi();
                    $send->phone = '083829314436';
                    $send->message = 'Succes Fully Paymen Apps';
                    $send->WhatsappMessage();
                    $res['api_status'] = 1;
                    $res['api_message'] = 'Payment status is updated';
                } else {

                    if (in_array($status, ['PAID'])) {
                        $check->status_registration = "Paid Off";
                        $check->payment_method = $payment_method;
                        $check->link = null;

                        $pdf = Pdf::loadView('email.invoice-new', $data);
                        Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findUser->email);
                            $message->subject('Thank you for payment - The 53rd Djakarta Mining Club Networking Event');
                            $message->attachData($pdf->output(), 'E-Receipt_' . $findUser->code_payment . '.pdf');
                        });
                        $notif = new Notification();
                        $notif->id = $check->member_id;
                        $notif->message = 'Payment successfully Web';
                        $notif->NotifApp();
                        $res['api_status'] = 1;
                        $res['api_message'] = 'Payment status is updated';
                    } elseif (in_array($status, ['ACTIVE'])) {
                        // $check->status_registration = "Expired";
                        // $check->link = null;
                        $res['api_status'] = 1;
                        $res['api_message'] = 'FVA ACTIVE';
                    } else {
                        $check->status_registration = "Expired";
                        $check->payment_method = $payment_method;
                        $check->link = null;
                        $res['api_status'] = 1;
                        $res['api_message'] = 'Expired';
                    }
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
    public function fva(Request $request)
    {
        // "updated": "2017-02-15T11:01:52.896Z",
        // "created": "2017-02-15T11:01:52.896Z",
        // "payment_id": "1487156512722",
        // "callback_virtual_account_id": "58a434ba39cc9e4a230d5a2b",
        // "owner_id": "5824128aa6f9f9b648be9d76",
        // "external_id": "fixed-va-1487156410",
        // "account_number": "1001470126",
        // "bank_code": "MANDIRI",
        // "amount": 80000,
        // "transaction_timestamp": "2017-02-15T11:01:52.722Z",
        // "merchant_code": "88464",
        // "id": "58a435201b6ce2a355f46070"
        try {
            //     $owner_id = $request->owner_id;
            $external_id = request('external_id');

            $findPayment = Payment::where('code_payment', '=', $external_id)->first();

            if (!empty($findPayment)) {
                $findUsersVA = PaymentUsersVA::where('payment_id', '=', $findPayment->id)->first();
                $findPayment->status_registration = 'Paid Off';
                $findPayment->save();
                $findUsersVA->status = 'Paid Off';
                $findUsersVA->save();

                $UserEvent = new UserRegister();
                $UserEvent->users_id = $findPayment->member_id;
                $UserEvent->events_id = $findPayment->events_id;
                $UserEvent->payment_id = $findPayment->id;
                $UserEvent->save();
                $send = new WhatsappApi();
                $send->phone = '083829314436';
                $send->message = 'Succes Fully Payment';
                $send->WhatsappMessage();
                $notif = new Notification();
                $notif->id = $findPayment->member_id;
                $notif->message = 'Payment successfully';
                $notif->NotifApp();
                $res['api_status'] = 1;
                $res['api_message'] = $external_id;
            } else {
                $res['api_status'] = 0;
                $res['api_message'] = 'Payment Not Found';
            }
            return response()->json($res, 200);
        } catch (\Exception $msg) {
            $res['api_status'] = 0;
            $res['api_message'] = $msg->getMessage();
            return response()->json($res, 500);
        }
    }
}
