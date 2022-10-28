<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xendit\PaymentChannels;
use Xendit\Xendit;

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

        $response['status'] = 200;
        $response['message'] = 'List Bank';
        $response['payload'] = $getPaymentChannels;

        return response()->json($response);
    }
}
