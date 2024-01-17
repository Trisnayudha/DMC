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
}
