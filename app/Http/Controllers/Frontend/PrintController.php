<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrintController extends Controller
{

    public function request(Request $request)
    {
        $check = Payment::where('code_payment', $request->input_text)->first();
        $nosave = $request->noscan;
        if (!empty($check)) {
            $findUsers = User::where('users.id', $check->member_id)->join('company', 'company.users_id', 'users.id')->first();
            $data = [
                'name' => $findUsers->name,
                'company_name' => $findUsers->company_name,
                'package' => $check->package,
            ];
            if (empty($nosave)) {
                $save = UserRegister::where('payment_id', $check->id)->first();
                if (empty($save)) {
                    $save = new UserRegister();
                }
                $save->users_id = $check->member_id;
                $save->events_id = $check->events_id;
                $save->payment_id = $check->id;
                $save->present = Carbon::now();
                $save->save();
            }

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
            'package' => $request->package,
        ];
        return view('print.print', $data);
    }
}
