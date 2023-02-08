<?php

namespace App\Http\Controllers;

use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PrintController extends Controller
{

    public function request(Request $request)
    {
        $check = Payment::where('code_payment', $request->input_text)->first();
        if (!empty($check)) {
            $findUsers = User::where('users.id', $check->member_id)->join('company', 'company.users_id', 'users.id')->first();
            $data = [
                'name' => $findUsers->name,
                'company_name' => $findUsers->company_name
            ];

            $save = UserRegister::where('payment_id', $check->id)->first();
            if (empty($save)) {
                $save = new UserRegister();
            }
            $save->users_id = $check->member_id;
            $save->events_id = $check->events_id;
            $save->payment_id = $check->id;
            $save->present = 1;
            $save->save();
            $response['status'] = 1;
            $response['message'] = 'Success Scan QR Code';
            $response['data'] = $data;
        } else {
            $response['status'] = 0;
            $response['message'] = 'Qr Code tidak terdaftar di sistem';
            $response['data'] = null;
        }
        return response()->json($response);
    }
    public function scan()
    {
        return view('scan.scan');
    }

    public function index(Request $request)
    {
        $data = [
            'name' => $request->name,
            'company' => $request->company,
        ];
        return view('print.print', $data);
    }
}
