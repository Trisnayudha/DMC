<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
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
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = 'Email tidak ditemukan';
            $response['payload'] = $validate->errors();

            return response()->json($response, 422);
        } else if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $role = $this->user->checkrole($user->id);
            $success['id'] =  $user->id;
            $success['token'] = $user->createToken('token-name')->plainTextToken;
            $success['email'] =  $user->email;
            $success['role'] = $role[0]->name;
            return response()->json([
                'status' => 'Login Successfully!',
                'data' => $success
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Password salah'
            ], 400);
        }
    }

    public function check(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'unique:profiles']
            ],
            [
                'phone.required' => 'Harap Masukan Nomor Handphone',
                'phone.unique' => 'Nomor Handphone sudah terdaftar',
                'email.required' => 'Email Harap diisi',
                'email.unique' => 'Email sudah digunakan'
            ]
        );
        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email'),
                'phone' => $validate->errors()->first('phone')
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
        $phone = $country_phone . $request->phone;
        $email = $request->email;
        $password = $request->password;
        $prefix = $request->prefix;
        $company_name = $request->company_name . ", " . $prefix;
        $job_title = $request->job_title;
        $address = $request->address;
        $country = $request->country;
        $company_category = $request->company_category;
        $company_other = $request->company_other;
        $company_website = $request->company_website;
        $city = $request->city;
        $country_phone_office = $request->country_phone_office;
        $office_number = $country_phone_office . $request->office_number;
        $portal_code = $request->portal_code;
        $cci = $request->cci;
        $explore = $request->explore;
        if ($company_category == 'other') {
            $company_category = $company_other;
        }
        if ($validate->fails()) {
            $response['status'] = 401;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();
        } else {
            $user = User::create([
                'name' => $name,
                'email' =>  $email,
                'password' => Hash::make($password),
            ]);
            $company = CompanyModel::create([
                'company_name' => $company_name,
                'company_website' => $company_website,
                'company_category' => $company_category,
                'address' => $address,
                'city' => $city,
                'portal_code' => $portal_code,
                'office_number' => $office_number,
                'country' => $country,
                'cci' => $cci,
                'explore' => $explore
            ]);
            $profile = ProfileModel::create([
                'phone' => $phone,
                'job_title' => $job_title,
                'users_id' => $user->id,
                'company_id' => $company->id
            ]);
            $user->assignRole('guest');
            $response['status'] = 200;
            $response['message'] = 'Register Successfully';
            $response['payload'] = [
                'id' => $user->id,
                'email' => $email,
                'phone' => $phone,
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
        $iscond = $request->iscond;
        if (!empty($email)) {
            $user = User::where('email', '=', $email)->first();
            $stat = User::where('email', '=', $email)->update(['otp' => $otp]);
            if (!empty($user)) {
                $send = new EmailSender();
                if ($iscond == 'signup') {
                    $send->subject = "OTP Register";
                    $wording = 'We received a request to register your account. To register, please use this
                    code:';
                } elseif ($iscond == 'forgot') {
                    $send->subject = "OTP Forgot Password";
                    $wording = 'We received a request to reset the password for your account. To reset the password, please use this
                    code:';
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
                $response['status'] = 401;
                $response['message'] = 'Email was Wrong';
                $response['payload'] = null;
            }
        } else {
            $user = ProfileModel::where('phone', '=', $phone)->join('users', 'users.id', '=', 'profiles.users_id')->first();
            $stat = ProfileModel::where('phone', '=', $phone)->join('users', 'users.id', '=', 'profiles.users_id')->update(['users.otp' => $otp]);
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
        $email = $request->email;
        $phone = $request->phone;
        if (!empty($email)) {
            $user  = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
            if (!empty($user)) {
                User::where('email', '=', $request->email)->update([
                    'otp' => null,
                    'verify_email' => 'verified'
                ]);
                $check = User::where('id', '=', $user->id)->first();
                auth()->login($user, true);
                $role = $this->user->checkrole($user->id);
                $accessToken = auth()->user()->createToken('token-name')->plainTextToken;
                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role[0]->name,
                    'token' => $accessToken,
                    'verify_email' => $check->verify_email,
                    'verify_phone' => $check->verify_phone
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully OTP';
                $response['payload'] = $data;
            } else {
                $response['status'] = 401;
                $response['message'] = 'OTP Invalid';
                $response['payload'] = null;
            }
        } else {
            $user = ProfileModel::where([['profiles.phone', '=', $phone], ['users.otp', '=', $request->otp]])->join('users', 'users.id', '=', 'profiles.users_id')->first();
            $userlogin  = User::where('email', '=', $user->email)->first();
            // dd($user);
            if (!empty($user)) {
                User::where('email', '=', $user->email)->update([
                    'otp' => null,
                    'verify_phone' => 'verified'
                ]);
                $check = User::where('id', '=', $user->id)->first();
                auth()->login($userlogin, true);
                // dd($user);
                $role = $this->user->checkrole($user->id);
                $accessToken = auth()->user()->createToken('token-name')->plainTextToken;
                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role[0]->name,
                    'token' => $accessToken,
                    'verify_email' => $check->verify_email,
                    'verify_phone' => $check->verify_phone
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully OTP';
                $response['payload'] = $data;
            } else {
                $response['status'] = 401;
                $response['message'] = 'OTP Invalid';
                $response['payload'] = null;
            }
        }
        return response()->json($response);
    }
    public function register(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'name' => ['string'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:5'],
                'phone' => ['required', 'unique:profiles'],
            ],
            [
                'phone.required' => 'Harap Masukan Nomor Handphone',
                'phone.unique' => 'Nomor Handphone sudah terdaftar',
                'password.required' => 'Password Harap diisi',
                'email.required' => 'Email Harap diisi',
                'email.unique' => 'Email sudah digunakan'
            ]
        );

        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = $validate->errors()->first();

            return response()->json($response, 422);
        } else {

            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'verif' => '0',
                'restpass' => '0',
                'password' => Hash::make($request['password']),
            ]);
            $user->assignRole('guest');
            $user->user_id_profile()->create([
                'phone' => $request['phone'],
            ])->id;
            $user->user_id_profileusaha()->create()->id;
            $role = $this->user->checkrole($user->id);
            $response['status'] = true;
            $response['message'] = 'Berhasil registrasi donkksss';
            $response['id'] = $user->id;
            $response['email'] = $request['email'];
            $response['phone'] = $request['phone'];
            $response['token'] = $user->createToken('token-name')->plainTextToken;
            $response['role'] = $role[0]->name;
            return response()->json($response, 200);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'message'   => 'Berhasil LogOut'
        ], 200);
    }

    public function resetpassword(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'nik' => ['required']
            ],
            [
                'nik.required' => 'NIK Harap diisi',
                'name.required' => 'Name Harap diisi',
                'name.string' => 'Harap memasukan Nama berupa String',
            ]
        );
        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = $validate->errors()->first();

            return response()->json($response, 422);
        } else {
            try {

                $user = User::where('name', $request['name'])->firstOrFail();
                try {
                    $profile = Profile::where('nik', $request['nik'])->firstOrFail();
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => false,
                        'message' => 'NIK tidak ditemukan'
                    ], 422);
                }
                //throw $th;
                $proses = User::where('id', $user->id)->update(['restpass' => '1']);
                $success['Information'] = 'Data Request Password sedang dikonfirmasi';
                $success['id'] =  $user->id;
                $success['NIK'] =  $profile->nik;
                $success['Name'] =  $user->name;
                activity()->log('Melakukan Request Reset Password');
                return response()->json([
                    'status' => true,
                    'message' => $success
                ]);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nama tidak ditemukan'
                ], 422);
            }
        }
    }
}
