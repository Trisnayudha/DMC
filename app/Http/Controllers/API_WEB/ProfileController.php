<?php

namespace App\Http\Controllers\API_WEB;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function requestOtp(Request $request)
    {
        $id = auth('sanctum')->user()->id;
        $otp = rand(10000, 99999);
        Log::info("otp = " . $otp);
        $validate = Validator::make(
            $request->all(),
            [
                'params' => 'required'
            ],
        );
        if ($validate->fails()) {
            $data = [
                'params' => $validate->errors()->first('params')
            ];
            $response['status'] = 401;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $data;
        } else {
            $email = $request->email;
            $phone = $request->phone;
            $params = $request->params;
            if (!empty($email)) {
                $user = User::where('id', '=', $id)->first();
                $stat = User::where('id', '=', $id)->update(['otp' => $otp]);
                $send = new EmailSender();
                $send->subject = "OTP Register";
                if ($params == 'change') {
                    $wording = 'We received a request to change your account. To change, please use this
                    code:';
                } elseif ($params == 'verify') {
                    $wording = 'We received a request to verify your account. To verify, please use this
                    code:';
                } else {
                    $data = [
                        'params' => 'Please Choose params ( verify / change )'
                    ];
                    $response['status'] = 401;
                    $response['message'] = 'Something was wrong';
                    $response['payload'] = $data;
                    return response()->json($response);
                }

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
                $user = ProfileModel::where('users_id', '=', $id)->first();
                $stat = ProfileModel::where('users_id', '=', $id)->join('users', 'users.id', 'profiles.users_id')->update(['users.otp' => $otp]);
                $send = new WhatsappApi();
                $send->phone = $phone;
                if ($params == 'change') {
                    $send->message = 'Dear ' . $user->name . '
Your change phone number code (OTP) ' . $otp;
                } elseif ($params == 'verify') {
                    $send->message = 'Dear ' . $user->name . '
Your verification code (OTP) ' . $otp;
                } else {
                    $data = [
                        'params' => 'Please Choose params ( verify / change )'
                    ];
                    $response['status'] = 401;
                    $response['message'] = 'Something was wrong';
                    $response['payload'] = $data;
                    return response()->json($response);
                }
                $send->WhatsappMessage();
                $data = [
                    'phone' => $phone,
                    'whatsapp' => json_decode($send->res),
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully send OTP to Whatsapp';
                $response['payload'] = $data;
            }
        }
        return response()->json($response);
    }

    public function update_profile(Request $request)
    {
        $id = auth('sanctum')->user()->id;

        $check = User::where('id', $id)->first();
        $profile = ProfileModel::where('users_id', $check->id)->first();
        $company = CompanyModel::where('users_id', $check->id)->first();
        if (!empty($check)) {

            $file = $request->image;
            if (!empty($file)) {
                $imageName = time() . '.' . $request->image->extension();
                $db = '/storage/profile/' . $imageName;
                $save_folder = $request->image->storeAs('public/profile', $imageName);
                $profile->image = $db;
                $profile->save();
            }
            $company->company_name = $request->company_name;
            $company->address = $request->address;
            $company->office_number = $request->office_number;
            $company->company_website = $request->company_web;
            $check->name = $request->name;
            $company->save();
            $check->save();

            $response['status'] = 200;
            $response['message'] = 'Successfully update data';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }
}
