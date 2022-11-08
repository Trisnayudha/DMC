<?php

namespace App\Http\Controllers;

use App\Models\Payments\Payment;
use Illuminate\Http\Request;

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
}
