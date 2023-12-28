<?php

namespace App\Http\Controllers\API_WEB;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signin_phone(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'phone' => ['required', 'exists:profiles,fullphone'],
            ],
            [
                'phone.required' => 'Phone wajib di isi',
                'phone.exists' => 'Phone Number tidak ditemukan'
            ]
        );

        if ($validate->fails()) {
            $data = [
                'phone' => $validate->errors()->first('phone')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {
            $phone = $request->phone;
            $findUser = ProfileModel::where([['fullphone', '=', $phone], ['users.verify_phone', '=', 'verified']])->join('users', 'users.id', 'profiles.users_id')->first();
            if (!empty($findUser)) {
                $otp = rand(10000, 99999);
                Log::info("otp = " . $otp);
                User::where('id', '=', $findUser->users_id)->update(['otp' => $otp]);
                $send = new WhatsappApi();
                $send->phone = $phone;
                $send->message = 'Dear ' . $findUser->name . '
Your verification code (OTP) ' . $otp;
                $send->WhatsappMessage();
                $data = [
                    'phone' => $phone,
                    'whatsapp' => json_decode($send->res),
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully send OTP to Whatsapp';
                $response['payload'] = $data;
            } else {
                $data = [
                    'phone' => 'Phone number not verified'
                ];
                $response['status'] = 422;
                $response['message'] = 'Invalid phone number';
                $response['payload'] = $data;
            }
        }
        return response()->json($response);
    }

    public function requestOtp(Request $request)
    {
        $otp = rand(10000, 99999);
        Log::info("otp = " . $otp);
        $email = $request->email;
        $phone = $request->phone;
        if (!empty($email)) {
            $user = MemberModel::where('email', '=', $email)->first();
            $stat = MemberModel::where('email', '=', $email)->update(['otp' => $otp]);
            if (!empty($user)) {
                $send = new EmailSender();
                $send->subject = "OTP Register";
                $wording = 'We received a request to register your account. To register, please use this
                    code:';
                $send->template = "email.tokenverify";
                $send->data = [
                    'name' => $user->name,
                    'wording' => $wording,
                    'otp' => $otp
                ];
                $send->from = env('EMAIL_SENDER');
                $send->name_sender = env('EMAIL_NAME');
                $send->to = $email;
                $send->sendEmail();
                $response['status'] = 200;
                $response['message'] = 'Successfully send OTP to Email';
                $response['payload'] = $send;
            } else {
                $response['status'] = 401;
                $response['message'] = 'Email was Wrong';
                $response['payload'] = null;
            }
        } else {
            $user = MemberModel::where('fullphone', '=', $phone)->first();
            $stat = MemberModel::where('fullphone', '=', $phone)->update(['otp' => $otp]);
            if (!empty($user)) {
                $send = new WhatsappApi();
                $send->phone = $phone;
                $send->message = 'Dear ' . $user->name . '
Your verification code (OTP) ' . $otp;
                $send->WhatsappMessage();
                $data = [
                    'phone' => $phone,
                    'whatsapp' => json_decode($send->res),
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully send OTP to Whatsapp';
                $response['payload'] = $data;
            } else {
                $response['status'] = 401;
                $response['message'] = 'Phone Number was Wrong';
                $response['payload'] = null;
            }
        }
        return response()->json($response);
    }
}
