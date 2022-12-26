<?php

namespace App\Http\Controllers\API;

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
        $package = $type == 'paid' ? 'Premium' : 'Silver';
        $payment_method = $payment_method == 'other' ? 'Free-pass' : $payment_method;
        // $payment_method = $payment ? $payment : 'free pas';
        $image = QrCode::format('png')
            ->size(200)->errorCorrection('H')
            ->generate($codePayment);
        $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
        Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
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
            if ($type == 'free') {

                $save->status = 'Free';
            } else {
                $save->status = 'Waiting';
            }
            $save->qr_code = $db;
            $save->save();
            if ($type == 'free') {
                $saveUser = new UserRegister();
                $saveUser->events_id = $events_id;
                $saveUser->users_id = $id;
                $saveUser->payment_id = $save->id;
                $saveUser->save();
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
            }
            $response['status'] = 200;
            $response['message'] = 'success';
            $response['payload'] = $createVA ? $createVA : null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Error, please contact call center';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
