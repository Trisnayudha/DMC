<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
use App\Http\Controllers\Controller;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentUsersVA;
use App\Models\User;
use Illuminate\Http\Request;
use Xendit\PaymentChannels;
use Xendit\Xendit;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Xendit\VirtualAccounts;
use Illuminate\Support\Carbon;
use Xendit\Invoice;

class PaymentController extends Controller
{
    public function listbank()
    {
        // init xendit
        $isProd = env('XENDIT_ISPROD');
        if ($isProd) {
            $secretKey = env('XENDIT_SECRET_KEY_PROD');
        } else {
            $secretKey = env('XENDIT_SECRET_KEY_TEST');
        }
        // params invoice
        Xendit::setApiKey($secretKey);
        $getPaymentChannels = PaymentChannels::list();

        $available_payment = array_filter($getPaymentChannels, function ($key) {
            // dd($key['is_activated']);
            return $key['is_enabled'] == true && ($key['channel_category'] == 'VIRTUAL_ACCOUNT' || $key['channel_category'] == 'CREDIT_CARD');
        });

        $response['status'] = 200;
        $response['message'] = 'List Bank';
        $response['payload'] = array_values($available_payment);

        return response()->json($response);
    }

    public function payment(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $type = $request->type;
        $events_id = $request->events_id;
        $tickets_id = $request->tickets_id;
        $payment_method = $request->payment_method;
        $createVA = null;
        $codePayment = strtoupper(Str::random(7));
        $package = $type == 'paid' ? 'Premium' : 'free';
        $payment_method = $payment_method == 'other' ? 'Free-pass Apps' : $payment_method;
        // $payment_method = $payment ? $payment : 'free pas';
        $Serv = env('APP_NAME');

        if ($Serv == 'Server') {
            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($codePayment);
        }
        $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
        if ($Serv == 'Server') {
            Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        }
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $events_id)->first();
        $findUsers = User::where('id', '=', $id)->first();
        $findTicket = EventsTicket::where('id', '=', $tickets_id)->first();
        $save = new Payment();
        if ($findPayment == null) {
            $save->member_id = $id;
            $save->package = $package;
            $save->code_payment = $codePayment;
            $save->payment_method = $payment_method;
            $save->tickets_id = $tickets_id;
            $save->events_id = $events_id;
            $save->status_registration = ($type == 'free' ? 'Waiting' : 'Waiting');
            $save->qr_code = $db;
            $save->save();
            if ($type == 'free') {
                $saveUser = new UserRegister();
                $saveUser->events_id = $events_id;
                $saveUser->users_id = $id;
                $saveUser->payment_id = $save->id;
                // $saveUser->save();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Register Success, please waiting approval';
                $notif->NotifApp();
            } else {
                // init xendit
                $isProd = env('XENDIT_ISPROD');
                if ($isProd) {
                    $secretKey = env('XENDIT_SECRET_KEY_PROD');
                } else {
                    $secretKey = env('XENDIT_SECRET_KEY_TEST');
                }
                // params invoice
                Xendit::setApiKey($secretKey);

                $params = [
                    'external_id' => $codePayment,
                    'bank_code' => $payment_method,
                    'name' => $findUsers->name,
                    'expected_amount' => $findTicket->price_rupiah,
                    'is_closed' => true,
                    "expiration_date" => Carbon::now()->addDays(1)->toISOString(),
                    'is_single_use' => true,
                ];

                $createVA = VirtualAccounts::create($params);
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $save->id;
                $save_va->is_closed = $createVA['is_closed'];
                $save_va->status = $createVA['status'];
                $save_va->currency = $createVA['currency'];
                // $save_va->country = $createVA['country'];
                $save_va->owner_id = $createVA['owner_id'];
                $save_va->bank_code = $createVA['bank_code'];
                $save_va->merchant_code = $createVA['merchant_code'];
                $save_va->account_number = $createVA['account_number'];
                $save_va->expected_amount = $createVA['expected_amount'];
                $save_va->expiration_date = $createVA['expiration_date'];
                $save_va->is_single_use = $createVA['is_single_use'];
                $save_va->save();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Your wait is over! Your Virtual Account is now up and running, ready for smooth transactions.';
                $notif->NotifApp();
            }
            $response['status'] = 200;
            $response['message'] = 'success';
            $response['payload'] = $createVA ? $createVA : null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'You have another payment, please contact admin for information';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function creditCard(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $type = $request->type;
        $events_id = $request->events_id;
        $tickets_id = $request->tickets_id;
        $payment_method = $request->payment_method;
        $createVA = null;
        $codePayment = strtoupper(Str::random(7));
        $package = $type == 'paid' ? 'Premium' : 'free';
        $payment_method = $payment_method == 'other' ? 'Free-pass Apps' : $payment_method;

        $date = date('d-m-Y H:i:s');
        $linkPay = null;
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $events_id)->first();
        $findUsers = User::where('id', '=', $id)->first();
        $findTicket = EventsTicket::where('id', '=', $tickets_id)->first();

        $Serv = env('APP_NAME');
        if ($Serv == 'Server') {
            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($codePayment);
        }
        $save = new Payment();
        if (empty($findPayment)) {
            if ($payment_method == 'CREDIT_CARD') {
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                if ($Serv == 'Server') {
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                }
                $save->member_id = $id;
                $save->package = $package;
                $save->code_payment = $codePayment;
                $save->payment_method = $payment_method;
                $save->tickets_id = $tickets_id;
                $save->events_id = $events_id;
                $save->status_registration = ($type == 'free' ? 'Waiting' : 'Waiting');
                $save->qr_code = $db;

                // init xendit
                $isProd = env('XENDIT_ISPROD');
                if ($isProd) {
                    $secretKey = env('XENDIT_SECRET_KEY_PROD');
                } else {
                    $secretKey = env('XENDIT_SECRET_KEY_TEST');
                }
                // params invoice
                Xendit::setApiKey($secretKey);
                $params = [
                    'external_id' => $codePayment,
                    'payer_email' => $findUsers->email,
                    'description' => 'Payment Credit Card',
                    'amount' => $findTicket->price_rupiah,
                    'success_redirect_url' => 'https://api.djakarta-miningclub.com/payment-success',
                    'failure_redirect_url' => url('/'),
                ];
                $createInvoice = Invoice::create($params);

                $linkPay = $createInvoice['invoice_url'];
                $save->link = $linkPay;
                $save->save();
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $save->id;
                $save_va->is_closed = 0;
                $save_va->status = "PENDING";
                // $save_va->currency = $createInvoice['currency'];
                $save_va->country = 'IDR';
                $save_va->owner_id = $createInvoice['user_id'];
                $save_va->bank_code = 'CREDIT_CARD';
                // $save_va->merchant_code = $createInvoice['merchant_code'];
                // $save_va->account_number = $createInvoice['account_number'];
                $save_va->expected_amount = $createInvoice['amount'];
                $save_va->expiration_date = $createInvoice['expiry_date'];
                $save_va->is_single_use = 0;
                $save_va->save();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Invoice ' . $codePayment . ' created succesfully';
                $notif->NotifApp();
                $response['status'] = 200;
                $response['message'] = 'success';
                $response['payload'] = $createInvoice ? $createInvoice : null;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Payment Method not same with url';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 404;
            $response['message'] = 'You have another payment, please contact admin for information';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
