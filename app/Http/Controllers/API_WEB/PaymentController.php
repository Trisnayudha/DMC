<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Payments\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function historyPayment(Request $request)
    {
        $limit = $request->limit ?? 5;
        $id =  auth('sanctum')->user()->id;
        $findPayment = Payment::where('member_id', '=', $id)
            ->join('events_tickets', 'events_tickets.id', 'payment.tickets_id')
            ->select('payment.*', 'events_tickets.price_rupiah')
            ->whereIn('package', ['nonmember', 'onsite', 'member', 'Premium'])
            ->orderby('id', 'desc')->paginate($limit);
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $findPayment;
        return response()->json($response);
    }

    public function cancel(Request $request)
    {
        $code_payment = $request->code_payment;

        $findPayment = Payment::where('code_payment', $code_payment)->where('status_registration', 'Waiting')->first();
        if ($findPayment) {
            $findPayment->status_registration = 'Cancel';
            $findPayment->link = null;
            $findPayment->save();
            $response['status'] = 200;
            $response['message'] = 'Success Cancel payment';
            $response['payload'] = null;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Payment Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function refresh(Request $request)
    {
        $code_payment = $request->code_payment;
        $findPayment = Payment::join('events', 'events.id', 'payment.events_id')->join('events_tickets', 'events_tickets.id', 'payment.tickets_id')->where('code_payment', $code_payment)->first();
        $response['status'] = 200;
        $response['message'] = 'Success Refresh payment';
        $response['payload'] = $findPayment;
        return response()->json($response);
    }
}
