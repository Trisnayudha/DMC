<?php

namespace App\Http\Controllers\Callback;

use App\Helpers\EmailSender;
use App\Helpers\Notification;
use App\Helpers\WhatsappApi;
use App\Helpers\XenditInvoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BookingContact\BookingContact;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentUsersVA;
use App\Repositories\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Ladumor\OneSignal\OneSignal;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($external_id);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $findUser = Payment::where('code_payment', $external_id)
                    ->join('users as a', 'a.id', 'payment.member_id')
                    ->join('profiles as b', 'a.id', 'b.users_id')
                    ->join('company as c', 'c.id', 'b.company_id')
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
                    'price' => number_format($paid_amount, 0, ',', '.'),
                    'total_price' => number_format($paid_amount, 0, ',', '.'),
                    'voucher_price' => number_format(0, 0, ',', '.'),
                    'image' => $db,
                    'job_title' => $findUser->job_title
                ];
                if ($payment_method == 'CREDIT_CARD') {
                    $check->status_registration = "Paid Off";
                    $check->payment_method = $payment_method;
                    $check->link = null;

                    $check->qr_code = $db;
                    $UserEvent = new UserRegister();
                    $UserEvent->users_id = $check->member_id;
                    $UserEvent->events_id = $check->events_id;
                    $UserEvent->payment_id = $check->id;
                    $UserEvent->save();

                    $pdf = Pdf::loadView('email.invoice-new', $data);
                    Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($findUser->email);
                        $message->subject('Thank you for payment - The 54th Djakarta Mining Club Networking Event');
                        $message->attachData($pdf->output(), 'E-Receipt_' . $findUser->code_payment . '.pdf');
                    });

                    $pdf = Pdf::loadView('email.ticket', $data);
                    Mail::send('email.approval-event', $data, function ($message) use ($pdf, $findUser) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($findUser->email);
                        $message->subject($findUser->code_payment . ' - Your registration is approved for Mineral Trends 2023 ');
                        $message->attachData($pdf->output(), $findUser->code_payment . '-' . time() . '.pdf');
                    });

                    $send = new WhatsappApi();
                    $send->phone = '08111937300';
                    $send->message = '
Hai Team,

Success payment dari ' . $findUser->name . '
Detail Informasinya:
Nama : ' . $findUser->name . '
Email: ' . $findUser->email . '
Phone Number: ' . $findUser->phone . '
Company : ' . $findUser->company_name . '

Thank you
Best Regards Bot DMC
';
                    $send->WhatsappMessage();
                    $notif = new Notification();
                    $notif->id = $check->member_id;
                    $notif->message = 'Payment successfully Web';
                    $notif->NotifApp();
                    $res['api_status'] = 1;
                    $res['api_message'] = 'Payment status is updated';
                } else {

                    if (in_array($status, ['PAID'])) {
                        $findUser = Payment::where('code_payment', $external_id)
                            ->join('users as a', 'a.id', 'payment.member_id')
                            ->join('profiles as b', 'a.id', 'b.users_id')
                            ->join('company as c', 'c.id', 'b.company_id')
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
                            'price' => number_format($paid_amount, 0, ',', '.'),
                            'total_price' => number_format($paid_amount, 0, ',', '.'),
                            'voucher_price' => number_format(0, 0, ',', '.'),
                            'image' => $db,
                            'job_title' => $findUser->job_title,
                        ];
                        $check->status_registration = "Paid Off";
                        $check->payment_method = $payment_method;
                        $check->link = null;
                        $check->qr_code = $db;
                        $pdf = Pdf::loadView('email.invoice-new', $data);
                        Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findUser->email);
                            $message->subject('Thank you for payment - The 54th Djakarta Mining Club Event ');
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

                        $send = new WhatsappApi();
                        $send->phone = '08111937300';
                        $send->message = '

EXPIRED ALERT ! ! !

Nama : ' . $findUser->name . '
Email: ' . $findUser->email . '
Phone Number: ' . $findUser->phone . '
Company : ' . $findUser->company_name . '

Tolong di kontak kembali , takutnya ada kesulitan payment

Thank you
Best Regards Bot DMC
';
                        $send->WhatsappMessage();
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
    public function invoice_v2()
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
                if ($status == 'PAID') {
                    // $image = QrCode::format('png')
                    //     ->size(200)->errorCorrection('H')
                    //     ->generate('SOXVGUK');
                    $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                    $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                    // Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $findUser = Payment::where('code_payment', 'HMGPZV7')
                        ->join('users as a', 'a.id', 'payment.member_id')
                        ->join('profiles as b', 'a.id', 'b.users_id')
                        ->join('company as c', 'c.id', 'b.company_id')
                        ->first();
                    if ($check->booking_contact_id != null) {
                        $findContact = BookingContact::where('id', $check->booking_contact_id)->first();

                        $loopPayment = Payment::where('booking_contact_id', $findContact->id)
                            ->join('users as a', 'a.id', 'payment.member_id')
                            ->join('profiles as b', 'a.id', 'b.users_id')
                            ->join('company as c', 'c.id', 'b.company_id')
                            ->join('events_tickets as d', 'payment.tickets_id', 'd.id')
                            ->get();
                        $detailWa = [];
                        $item_details = [];
                        foreach ($loopPayment as $data) {
                            $update = Payment::where('member_id', $data->member_id)->where('events_id', '4')->first();
                            $item_details[] = [
                                'name' => $data->name,
                                'job_title' => $data->email,
                                'price' => number_format($data->price_rupiah, 0, ',', '.'),
                                'paidoff' => $data->status_registration == 'Paid Off' ? true : false
                            ];
                            $update->status_registration = "Paid Off";
                            $update->payment_method = $payment_method;
                            $update->save();
                            $UserEvent = UserRegister::where('payment_id', $update->id)->first();
                            if (empty($UserEvent)) {
                                $UserEvent = new UserRegister();
                            }
                            $UserEvent->users_id = $update->member_id;
                            $UserEvent->events_id = $update->events_id;
                            $UserEvent->payment_id = $update->id;
                            $UserEvent->save();
                            $detailWa[] = '
Nama : ' . $data->name . '
Email: ' . $data->email . '
Phone Number: ' . $data->phone . '
Company : ' . $data->company_name . '
';
                        }
                        $link = null;
                        $data = [
                            'users_name' => $findContact->name_contact,
                            'users_email' => $findContact->email_contact,
                            'phone' => $findContact->phone_contact,
                            'company_name' => $findContact->company_name,
                            'company_address' => $findContact->address,
                            'status' => 'Paid Off',
                            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
                            'code_payment' => $findUser->code_payment,
                            'create_date' => date('d, M Y H:i'),
                            'package_name' => $findUser->package,
                            'price' => number_format($paid_amount, 0, ',', '.'),
                            'total_price' => number_format($paid_amount, 0, ',', '.'),
                            'voucher_price' => number_format(0, 0, ',', '.'),
                            'image' => $db,
                            'item' => $item_details,
                            'job_title' => $findContact->job_title_contact,
                            'link' => $link
                        ];
                        return view('email.invoice-new-multiple', $data);
                        $pdf = Pdf::loadView('email.invoice-new-multiple', $data);
                        Mail::send('email.success-register-event', $data, function ($message) use ($findContact, $pdf) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findContact->email_contact);
                            $message->subject('Thank you for payment - Technological Advances Driving Innovation in Indonesia`s Mining
                            Industry ');
                            $message->attachData($pdf->output(), 'E-Receipt.pdf');
                        });
                        $send = new WhatsappApi();
                        $send->phone = '083829314436';
                        $send->message = '
Hai Team,

Success payment dari ' . $findContact->name . '
Detail Informasinya:
' . implode(" ", $detailWa) . '

Thank you
Best Regards Bot DMC
                                                ';
                        $send->WhatsappMessage();
                        $res['api_status'] = 1;
                        $res['api_message'] = 'Payment status is updated';
                        $res['payload'] = $loopPayment;
                    }
                }
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
    public function fva_paid(Request $request)
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
                $UserEvent = UserRegister::where('payment_id', $findPayment->id)->first();
                if (empty($UserEvent)) {
                    $UserEvent = new UserRegister();
                }
                $UserEvent->users_id = $findPayment->member_id;
                $UserEvent->events_id = $findPayment->events_id;
                $UserEvent->payment_id = $findPayment->id;
                $UserEvent->save();
                $send = new WhatsappApi();
                $send->phone = '083829314436';
                $send->message = 'Succes Fully Payment FVA';
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

    public function fva_create()
    { {
            // "id": "63db6e78c5f24b5e0540db9d",
            // "name": "Dandi",
            // "status": "ACTIVE",
            // "country": "ID",
            // "created": "2023-02-02T08:04:08.767Z",
            // "updated": "2023-02-02T08:04:08.806Z",
            // "currency": "IDR",
            // "owner_id": "627a17539917bb3ad4f7cf88",
            // "bank_code": "BRI",
            // "is_closed": true,
            // "external_id": "HMECFEQ",
            // "is_single_use": true,
            // "merchant_code": "13282",
            // "account_number": "13282472664527646",
            // "expected_amount": 10000,
            // "expiration_date": "2023-02-03T08:04:07.137Z"
            $external_id = request('external_id');
            $check = Payment::where('code_payment', $external_id)->first();
            $fields['include_external_user_ids'] = ['external_user_id_' . $check->member_id];
            $message = 'hey!! this is test push.!';

            $d = OneSignal::sendPush($fields, $message);

            return response()->json($d);
        }
    }
}
