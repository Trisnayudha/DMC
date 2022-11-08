<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberModel;
use App\Models\Profiles\Profile;
use App\Models\Profiles\ProfileApi;
use App\Models\Profiles\ProfileModel;
use App\Models\Profiles\ProfileService;
use App\Models\ProfileUsahas\ProfileUsaha;
use App\Models\User;
use App\Models\Users\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }
    public function signin_phone(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'phone' => ['required', 'exists:profiles,phone'],
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
            $findUser = ProfileModel::where([['phone', '=', $phone], ['users.verify_phone', '=', 'verified']])->join('users', 'users.id', 'profiles.users_id')->first();
            if (!empty($findUser)) {
                $otp = rand(1000, 9999);
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

    public function verify_signin_phone(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'phone' => ['required', 'exists:profiles,phone'],
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
            $otp = $request->otp;
            $findUser = ProfileModel::where([['phone', '=', $phone], ['users.otp', '=', $otp]])->join('users', 'users.id', 'profiles.users_id')->first();
            if (!empty($findUser)) {
                User::where('id', '=', $findUser->users_id)->update(['otp' => null]);
                $user = User::where('id', '=', $findUser->id)->first();
                $role = $this->user->checkrole($findUser->id);
                $data = [
                    'id' => $findUser->id,
                    'name' => $findUser->name,
                    'email' => $findUser->email,
                    'role' => $role[0]->name,
                    'token' => $user->createToken('token-name')->plainTextToken,
                    'verify_email' => $findUser->verify_email,
                    'verify_phone' => $findUser->verify_phone
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully Login';
                $response['payload'] = $data;
            } else {
                $data = [
                    'otp' => 'Invalid OTP'
                ];
                $response['status'] = 422;
                $response['message'] = 'OTP was Wrong';
                $response['payload'] = $data;
            }
        }
        return response()->json($response);
    }

    public function signin_email(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'exists:users,email'],
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.exists' => 'Email Not Found'
            ]
        );

        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $role = $this->user->checkrole($user->id);
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role[0]->name,
                'token' => $user->createToken('token-name')->plainTextToken,
                'verify_email' => $user->verify_email,
                'verify_phone' => $user->verify_phone
            ];
            $response['status'] = 200;
            $response['message'] = 'Successfully Login';
            $response['payload'] = $data;
        } else {
            $data = [
                'password' => 'Password was wrong'
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        }
        return response()->json($response);
    }

    public function check(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'fullphone' => ['required', 'unique:profiles']
            ],
            [
                'fullphone.required' => 'Harap Masukan Nomor Handphone',
                'fullphone.unique' => 'Nomor Handphone sudah terdaftar',
                'email.required' => 'Email Harap diisi',
                'email.unique' => 'Email sudah digunakan'
            ]
        );
        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email'),
                'fullphone' => $validate->errors()->first('fullphone')
            ];
            $response['status'] = 422;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $data;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Next';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function signup(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'name' => ['string', 'required'],
                'phone' => ['required', 'unique:profiles'],
                'email' => ['required', 'unique:users'],
                'password' => ['required', 'string', 'min:5'],
                'prefix' => ['required'],
                'company_name' => ['required'],
                'job_title' => ['required'],
                'address' => ['required'],
                'country' => ['required'],
                'company_category' => ['required'],
                'city' => ['required'],
                'office_number' => ['required'],
                'portal_code' => ['required'],
            ],
            [
                'phone.required' => 'Harap Masukan Nomor Handphone',
                'phone.unique' => 'Nomor Handphone sudah terdaftar',
                'password.required' => 'Password Harap diisi',
                'email.required' => 'Email Harap diisi',
                'email.unique' => 'Email sudah digunakan'
            ]
        );
        $name = $request->name;
        $country_phone = $request->country_phone;
        $phone = $request->phone;
        $fullphone = $request->country_phone . $request->phone;
        $email = $request->email;
        $password = $request->password;
        $prefix = $request->prefix;
        $company_name = $request->company_name;
        $job_title = $request->job_title;
        $address = $request->address;
        $country = $request->country;
        $company_category = $request->company_category;
        $company_other = $request->company_other;
        $company_website = $request->company_website;
        $city = $request->city;
        $country_phone_office = $request->country_phone_office;
        $office_number = $request->office_number;
        $portal_code = $request->portal_code;
        $cci = $request->cci;
        $explore = $request->explore;
        if ($validate->fails()) {
            $response['status'] = 401;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();
        } else {
            $findUsers = MemberModel::where('phone', $phone)->orWhere('email', $email)->first();
            if (!empty($findUsers)) {
                $findUsers->prefix = $prefix;
                $findUsers->company_name = $company_name;
                $findUsers->prefix_phone = $country_phone;
                $findUsers->phone = $phone;
                $findUsers->fullphone = $fullphone;
                $findUsers->email = $email;
                $findUsers->name = $name;
                $findUsers->job_title = $job_title;
                $findUsers->company_website = $company_website;
                $findUsers->country = $country;
                $findUsers->address = $address;
                $findUsers->city = $city;
                $findUsers->prefix_office_number = $country_phone_office;
                $findUsers->office_number = $office_number;
                $findUsers->full_office_number = $country_phone_office . $office_number;
                $findUsers->portal_code = $portal_code;
                $findUsers->company_category = $company_category;
                $findUsers->company_other = $company_other;
                $findUsers->explore = $explore;
                $findUsers->password = Hash::make($password);
                $findUsers->cci = $cci;
                $findUsers->save();
            } else {
                $save = new MemberModel();
                $save->prefix = $prefix;
                $save->company_name = $company_name;
                $save->prefix_phone = $country_phone;
                $save->phone = $phone;
                $save->fullphone = $fullphone;
                $save->email = $email;
                $save->name = $name;
                $save->job_title = $job_title;
                $save->company_website = $company_website;
                $save->country = $country;
                $save->address = $address;
                $save->portal_code = $portal_code;
                $save->city = $city;
                $save->prefix_office_number = $country_phone_office;
                $save->office_number = $office_number;
                $save->full_office_number = $country_phone_office . $office_number;
                $save->company_category = $company_category;
                $save->company_other = $company_other;
                $save->explore = $explore;
                $save->cci = $cci;
                $save->password = Hash::make($password);
                $save->save();
            }
            $response['status'] = 200;
            $response['message'] = 'Save data Successfully';
            $response['payload'] = [
                'email' => $email,
                'phone' => $fullphone,
            ];
        }
        return response()->json($response);
    }
    public function requestOtp(Request $request)
    {
        $otp = rand(1000, 9999);
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


    public function verifyOtp(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['unique:users'],
                'phone' => ['unique:profiles'],
            ],
            [
                'phone.unique' => 'Nomor Handphone sudah terdaftar',
                'email.unique' => 'Email sudah digunakan'
            ]
        );
        $email = $request->email;
        $phone = $request->phone;
        if ($validate->fails()) {
            $response['status'] = 401;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();
        } else {
            $codePayment = strtoupper(Str::random(7));
            if (!empty($email)) {
                $findUser  = MemberModel::where([['email', '=', $email], ['otp', '=', $request->otp]])->first();
                if (!empty($findUser)) {
                    $image = QrCode::size(200)->generate($codePayment);
                    $output_file = 'public/uploads/qr-code/img-' . time() . '.png';
                    $output_db = 'storage/uploads/qr-code/img-' . time() . '.png';
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $user = User::create([
                        'name' => $findUser->name,
                        'email' =>  $findUser->email,
                        'password' => $findUser->password,
                        'verify_email' => 'verified',
                        'isStatus' => 'Active',
                        'qrcode' => $output_db,
                        'uname' => $codePayment,
                    ]);
                    $user->assignRole('guest');
                    $company = CompanyModel::create([
                        'company_name' => $findUser->company_name,
                        'company_website' => $findUser->company_website,
                        'company_category' => $findUser->company_category,
                        'company_other' => $findUser->company_other,
                        'address' => $findUser->address,
                        'city' => $findUser->city,
                        'portal_code' => $findUser->portal_code,
                        'prefix_office_number' => $findUser->prefix_office_number,
                        'office_number' => $findUser->office_number,
                        'full_office_number' => $findUser->full_office_number,
                        'country' => $findUser->country,
                        'cci' => $findUser->cci,
                        'explore' => $findUser->explore
                    ]);
                    $profile = ProfileModel::create([
                        'prefix_phone' => $findUser->prefix_phone,
                        'phone' => $findUser->phone,
                        'fullphone' => $findUser->fullphone,
                        'job_title' => $findUser->job_title,
                        'users_id' => $user->id,
                        'company_id' => $company->id
                    ]);
                    $user = User::where('id', '=', $user->id)->first();

                    auth()->login($user, true);

                    $role = $this->user->checkrole($user->id);
                    $accessToken = auth()->user()->createToken('token-name')->plainTextToken;
                    $data = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role[0]->name,
                        'token' => $accessToken,
                        'verify_email' => $user->verify_email,
                        'verify_phone' => $user->verify_phone
                    ];
                    MemberModel::where('id', '=', $findUser->id)->delete($findUser->id);
                    $response['status'] = 200;
                    $response['message'] = 'Successfully Register';
                    $response['payload'] = $data;
                } else {
                    $response['status'] = 401;
                    $response['message'] = 'OTP Invalid';
                    $response['payload'] = null;
                }
            } else {
                $findUser = MemberModel::where([['fullphone', '=', $phone], ['otp', '=', $request->otp]])->first();
                if (!empty($findUser)) {
                    $image = QrCode::size(200)->generate($codePayment);
                    $output_file = 'public/uploads/qr-code/img-' . time() . '.png';
                    $output_db = 'storage/uploads/qr-code/img-' . time() . '.png';
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $user = User::create([
                        'name' => $findUser->name,
                        'email' =>  $findUser->email,
                        'password' => $findUser->password,
                        'verify_phone' => 'verified',
                        'isStatus' => 'Active',
                        'qrcode' => $output_db,
                        'uname' => $codePayment
                    ]);
                    $user->assignRole('guest');
                    $company = CompanyModel::create([
                        'prefix' => $findUser->prefix,
                        'company_name' => $findUser->company_name,
                        'company_website' => $findUser->company_website,
                        'address' => $findUser->address,
                        'company_category' => $findUser->company_category,
                        'company_other' => $findUser->company_other,
                        'city' => $findUser->city,
                        'portal_code' => $findUser->portal_code,
                        'prefix_office_number' => $findUser->prefix_office_number,
                        'office_number' => $findUser->office_number,
                        'full_office_number' => $findUser->full_office_number,
                        'country' => $findUser->country,
                        'cci' => $findUser->cci,
                        'explore' => $findUser->explore
                    ]);
                    $profile = ProfileModel::create([
                        'prefix_phone' => $findUser->prefix_phone,
                        'phone' => $findUser->phone,
                        'fullphone' => $findUser->fullphone,
                        'job_title' => $findUser->job_title,
                        'users_id' => $user->id,
                        'company_id' => $company->id
                    ]);
                    $user = User::where('id', '=', $user->id)->first();

                    auth()->login($user, true);

                    $role = $this->user->checkrole($user->id);
                    $accessToken = auth()->user()->createToken('token-name')->plainTextToken;
                    $data = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role[0]->name,
                        'token' => $accessToken,
                        'verify_email' => $user->verify_email,
                        'verify_phone' => $user->verify_phone
                    ];
                    MemberModel::where('id', '=', $findUser->id)->delete($findUser->id);
                    $response['status'] = 200;
                    $response['message'] = 'Successfully Register';
                    $response['payload'] = $data;
                } else {
                    $response['status'] = 401;
                    $response['message'] = 'OTP Invalid';
                    $response['payload'] = null;
                }
            }
        }
        return response()->json($response);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'message'   => 'Berhasil LogOut'
        ], 200);
    }

    public function forgot(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'exists:users,email'],
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.exists' => 'Email Not Found'
            ]
        );

        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {
            $otp = rand(10000, 99999);
            Log::info("otp = " . $otp);
            User::where('email', '=', $request->email)->update(['otp' => $otp]);
            $send = new EmailSender();
            $send->subject = "OTP Forgot Password";
            $wording = 'We received a request to reset the password for your account. To reset the password, please use this
            code:';
            $send->template = "email.tokenverify";
            $send->data = [
                'name' => 'Mr/Mrs',
                'wording' => $wording,
                'otp' => $otp
            ];
            $send->from = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to = $request->email;
            $send->sendEmail();
            $response['status'] = 200;
            $response['message'] = 'Email Found';
            $response['payload'] = $send;
        }
        return response()->json($response);
    }

    public function verify_forgot(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'exists:users,email'],
                'otp' => ['required']
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.exists' => 'Email Not Found'
            ]
        );
        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {
            //
            $email = $request->email;
            $otp = $request->otp;
            $user = User::where([['email', '=', $email], ['otp', '=', $otp]])->first();
            if (!empty($user)) {
                $response['status'] = 200;
                $response['message'] = 'Berhasil Verify OTP';
                $response['payload'] = null;
            } else {
                $data = [
                    'otp' => 'OTP not sync'
                ];
                $response['status'] = 422;
                $response['message'] = 'Something was Wrong';
                $response['payload'] = $data;
            }
        }
        return response()->json($response);
    }

    public function resetpassword(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'exists:users,email'],
                'password' => ['required']
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.exists' => 'Email Not Found'
            ]
        );

        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {
            $email = $request->email;
            $password = Hash::make($request->password);
            $otp = $request->otp;
            $user = User::where([['email', '=', $email], ['otp', '=', $otp]])->first();
            if (!empty($user)) {
                User::where('id', '=', $user->id)->update(['otp' => null]);
                User::where('email', '=', $email)->update(['password' => $password]);
                $response['status'] = 200;
                $response['message'] = 'Successfully change password';
                $response['payload'] = null;
            } else {
                $data = [
                    'otp' => 'OTP not sync'
                ];
                $response['status'] = 422;
                $response['message'] = 'Something was wrong';
                $response['payload'] = $data;
            }
        }
        return response()->json($response);
    }
}
