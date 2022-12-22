<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
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
        $getPaymentChannels = VirtualAccounts::getVABanks();

        $response['status'] = 200;
        $response['message'] = 'List Bank';
        $response['payload'] = $getPaymentChannels;

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
        // $image = QrCode::format('png')
        //     ->size(200)->errorCorrection('H')
        //     ->generate($codePayment);
        $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
        // Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id')->first();
        $findUsers = User::where('id', '=', $id)->first();
        $findTicket = EventsTicket::where('id', '=', $tickets_id)->first();
        $save = new Payment();
        $save->member_id = $id;
        $save->package = $package;
        $save->code_payment = $codePayment;
        $save->payment_method = $payment_method;
        $save->tickets_id = $tickets_id;
        $save->events_id = $events_id;
        $save->status = $type == 'free' ? 'free' : 'Waiting';
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
        }
        $response['status'] = 200;
        $response['message'] = 'success';
        $response['payload'] = $createVA ? $createVA : null;

        return response()->json($response);
    }
}
