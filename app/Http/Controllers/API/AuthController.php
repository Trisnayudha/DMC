<?php

namespace App\Http\Controllers\API;

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
            $response['status'] = false;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();

            return response()->json($response, 422);
        } else {
            $response['status'] = true;
            $response['message'] = 'Next';
            $response['payload'] = null;
            return response()->json($response, 200);
        }
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
        $phone = $request->phone;
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
        $office_number = $request->office_number;
        $portal_code = $request->portal_code;
        $cci = $request->cci;
        $explore = $request->explore;
        if ($company_category == 'other') {
            $company_category = $company_other;
        }
        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();

            return response()->json($response, 422);
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
            $role = $this->user->checkrole($user->id);
            $response['status'] = true;
            $response['message'] = 'Register Successfully';
            $response['payload'] = [
                'id' => $user->id,
                'email' => $email,
                'phone' => $phone,
            ];
            return response()->json($response, 200);
        }
    }
    public function requestOtp(Request $request)
    {
        $otp = rand(1000, 9999);
        Log::info("otp = " . $otp);
        $email = $request->email;
        $phone = $request->phone;
        if (!empty($email)) {
            $user = User::where('email', '=', $email)->update(['otp' => $otp]);
            dd('email:' . $user);
        } else {
            $user = ProfileModel::where('phone', '=', $phone)->join('users', 'users.id', '=', 'profiles.users_id')->first();
            $stat = ProfileModel::where('phone', '=', $phone)->join('users', 'users.id', '=', 'profiles.users_id')->update(['users.otp' => $otp]);
            if (!empty($user)) {
                $send = new WhatsappApi();
                $send->phone = $phone;
                $send->message = 'Dear ' . $user->name . '
                Your OTP is: ' . $otp;
                $send->WhatsappMessage();
                $response['status'] = true;
                $response['message'] = 'Successfully send OTP to Whatsapp';
                $response['payload'] = json_decode($send->res);
                return response()->json($response, 422);
            } else {
                $response['status'] = false;
                $response['message'] = 'Phone Number Wrong';
                $response['payload'] = null;
                return response()->json($response, 422);
            }
        }
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
