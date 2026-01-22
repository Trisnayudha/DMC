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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Support\QrCode;
use Illuminate\Support\Str;
use Spatie\Newsletter\NewsletterFacade;
use Svg\Tag\Rect;

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

    public function verify_signin_phone(Request $request)
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
            $otp = $request->otp;
            $findUser = ProfileModel::where([['fullphone', '=', $phone], ['users.otp', '=', $otp]])->join('users', 'users.id', 'profiles.users_id')->first();
            if (!empty($findUser)) {
                User::where('id', '=', $findUser->users_id)->update(['otp' => null]);
                $user = User::where('id', '=', $findUser->id)->first();
                $role = $this->user->checkrole($findUser->id);
                $data = [
                    'id' => $findUser->id,
                    'name' => $findUser->name,
                    'email' => $findUser->email,
                    'role' => $role[0]->name ?? 'guest',
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
                'role' => isset($role[0]->name) ? $role[0]->name : 'guest',
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

    public function signin_qr(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'uname' => ['required', 'exists:users,uname'],
            ],
            [
                'uname.required' => 'QR wajib diisi',
                'uname.exists' => 'QR Not Found'
            ]
        );

        if ($validate->fails()) {
            $data = [
                'uname' => $validate->errors()->first('uname')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        }
        $uname = $request->uname;
        $findUser = User::where('uname', '=', $uname)->first();
        if (!empty($findUser)) {
            $role = $this->user->checkrole($findUser->id);
            $data = [
                'id' => $findUser->id,
                'name' => $findUser->name,
                'email' => $findUser->email,
                'role' => $role[0]->name,
                'token' => $findUser->createToken('token-name')->plainTextToken,
                'verify_email' => $findUser->verify_email,
                'verify_phone' => $findUser->verify_phone
            ];
            $response['status'] = 200;
            $response['message'] = 'Successfully Login';
            $response['payload'] = $data;
        } else {
            $data = [
                'uname' => 'Uname not found'
            ];
            $response['status'] = 422;
            $response['message'] = 'Uname not found';
            $response['payload'] = $data;
        }
        return response()->json($response);
    }

    public function check(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'string', 'email', 'max:255'],
                'fullphone' => ['required']
            ],
            [
                'fullphone.required' => 'Harap Masukan Nomor Handphone',
                'email.required' => 'Email Harap diisi',
            ]
        );

        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email'),
                'fullphone' => $validate->errors()->first('fullphone')
            ];
            return response()->json([
                'status' => 422,
                'message' => 'Checking Email & Fullphone was wrong',
                'payload' => $data
            ]);
        }

        $email = $request->email;
        $phone = $request->fullphone;

        // Cek apakah user dengan email ini sudah ada
        $user = User::where('email', $email)->first();
        $profile = ProfileModel::where('fullphone', $phone)->first();

        if ($user) {
            // Jika user ada tapi verify_email masih null -> boleh lanjut
            if (is_null($user->verify_email)) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Next (email belum terverifikasi)',
                    'payload' => null
                ]);
            }

            // Jika sudah terverifikasi
            return response()->json([
                'status' => 422,
                'message' => 'Email sudah digunakan',
                'payload' => ['email' => 'Email sudah digunakan']
            ]);
        }

        if ($profile) {
            // Jika profile ada tapi user terkait belum verifikasi phone
            $relatedUser = $profile->user ?? null;
            if ($relatedUser && is_null($relatedUser->verify_phone)) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Next (phone belum terverifikasi)',
                    'payload' => null
                ]);
            }

            return response()->json([
                'status' => 422,
                'message' => 'Nomor Handphone sudah terdaftar',
                'payload' => ['fullphone' => 'Nomor Handphone sudah terdaftar']
            ]);
        }

        // Jika lolos semua validasi
        return response()->json([
            'status' => 200,
            'message' => 'Next',
            'payload' => null
        ]);
    }

    public function signup(Request $request)
    {
        // 1) Validasi format (tanpa unique keras)
        $validate = Validator::make(
            $request->all(),
            [
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:30'],
                'email'    => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:5'],
            ],
            [
                'phone.required'    => 'Harap Masukan Nomor Handphone',
                'password.required' => 'Password Harap diisi',
                'email.required'    => 'Email Harap diisi',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status'  => 401,
                'message' => $validate->errors()->first(),
                'payload' => null,
            ]);
        }

        // 2) Ambil input
        $name                  = $request->name;
        $country_phone         = $request->country_phone;
        $phone                 = $request->phone;
        $fullphone             = ($country_phone ?? '') . ($phone ?? '');
        $email                 = $request->email;
        $password              = $request->password;
        $prefix                = $request->prefix;
        $company_name          = $request->company_name;
        $job_title             = $request->job_title;
        $address               = $request->address;
        $country               = $request->country;
        $company_category      = $request->company_category;
        $company_other         = $request->company_other;
        $company_website       = $request->company_website;
        $city                  = $request->city;
        $country_phone_office  = $request->country_phone_office;
        $office_number         = $request->office_number;
        $portal_code           = $request->portal_code;
        $cci                   = $request->cci;
        $explore               = $request->explore;

        // 3) Cek user existing (auto-create dari event biasanya sudah ada di users)
        $userByEmail = User::where('email', $email)->first();
        $profileByPhone = ProfileModel::where(function ($q) use ($phone, $fullphone) {
            $q->where('phone', $phone)
                ->orWhere('fullphone', $fullphone);
        })->first();

        // 4) If conflict verified → tolak
        if ($userByEmail && !$this->isProvisionalUser($userByEmail)) {
            return response()->json([
                'status'  => 422,
                'message' => 'This email is already registered. Please log in or use the "Forgot Password" option.',
                'payload' => ['field' => 'email', 'action' => 'LOGIN_OR_FORGOT'],
            ]);
        }

        if ($profileByPhone && !is_null(optional($profileByPhone->user)->verify_phone)) {
            return response()->json([
                'status'  => 422,
                'message' => 'This phone number is already registered. Please log in or use the "Forgot Password" option.',
                'payload' => ['field' => 'phone', 'action' => 'LOGIN_OR_FORGOT'],
            ]);
        }


        DB::beginTransaction();
        try {
            // 5) CLAIM MODE: userByEmail ada & provisional → update (klaim akun)
            if ($userByEmail && $this->isProvisionalUser($userByEmail)) {
                // Pastikan ada MemberModel draft untuk OTP flow (kalau belum, buat/merge)
                $member = MemberModel::firstOrNew(['email' => $email]);
                $member->prefix               = $prefix;
                $member->company_name         = $company_name;
                $member->prefix_phone         = $country_phone;
                $member->phone                = $phone;
                $member->fullphone            = $fullphone;
                $member->email                = $email;
                $member->name                 = $name;
                $member->job_title            = $job_title;
                $member->company_website      = $company_website;
                $member->country              = $country;
                $member->address              = $address;
                $member->city                 = $city;
                $member->prefix_office_number = $country_phone_office;
                $member->office_number        = $office_number;
                $member->full_office_number   = ($country_phone_office ?? '') . ($office_number ?? '');
                $member->portal_code          = $portal_code;
                $member->company_category     = $company_category;
                $member->company_other        = $company_other;
                $member->explore              = $explore;
                $member->cci                  = $cci;
                $member->password             = Hash::make($password); // simpan sementara; akan dipindah di verifyOtp
                $member->save();

                // (Opsional) Kirim OTP di step berikutnya (requestOtp)
                DB::commit();
                return response()->json([
                    'status'  => 200,
                    'message' => 'Akun ditemukan dari pendaftaran event. Silakan verifikasi (OTP) untuk mengklaim akun.',
                    'payload' => [
                        'mode'  => 'CLAIM',
                        'email' => $email,
                        'phone' => $fullphone,
                    ],
                ]);
            }

            // 6) CREATE MODE: tidak ada userByEmail (baru)
            $member = MemberModel::firstOrNew(['email' => $email]);
            $member->prefix               = $prefix;
            $member->company_name         = $company_name;
            $member->prefix_phone         = $country_phone;
            $member->phone                = $phone;
            $member->fullphone            = $fullphone;
            $member->email                = $email;
            $member->name                 = $name;
            $member->job_title            = $job_title;
            $member->company_website      = $company_website;
            $member->country              = $country;
            $member->address              = $address;
            $member->portal_code          = $portal_code;
            $member->city                 = $city;
            $member->prefix_office_number = $country_phone_office;
            $member->office_number        = $office_number;
            $member->full_office_number   = ($country_phone_office ?? '') . ($office_number ?? '');
            $member->company_category     = $company_category;
            $member->company_other        = $company_other;
            $member->explore              = $explore;
            $member->cci                  = $cci;
            $member->password             = Hash::make($password);
            $member->save();

            DB::commit();
            return response()->json([
                'status'  => 200,
                'message' => 'Save data Successfully',
                'payload' => [
                    'email' => $email,
                    'phone' => $fullphone,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal menyimpan data. ' . $e->getMessage(),
                'payload' => null,
            ]);
        }
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
        $codePayment = strtoupper(Str::random(7));
        if ($validate->fails()) {
            $response['status'] = 401;
            $response['message'] = 'Something was wrong';
            $response['payload'] = $validate->errors()->first();
        } else {
            if (!empty($email)) {
                $findUser  = MemberModel::where([['email', '=', $email], ['otp', '=', $request->otp]])->first();
                if (!empty($findUser)) {
                    $image = QrCode::format('png')
                        ->size(300)->errorCorrection('H')
                        ->generate($codePayment);
                    $output_file = '/public/uploads/qr-code/img-' . time() . '.png';
                    $db = '/storage/uploads/qr-code/img-' . time() . '.png';
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $user = User::create([
                        'name' => $findUser->name,
                        'email' =>  $findUser->email,
                        'password' => $findUser->password,
                        'verify_email' => 'verified',
                        'isStatus' => 'Active',
                        'qrcode' => $db,
                        'uname' => $codePayment,
                    ]);
                    $user->assignRole('guest');
                    $company = CompanyModel::create([
                        'prefix' => $findUser->prefix,
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
                        'explore' => $findUser->explore,
                        'users_id' => $user->id
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
                        'verify_phone' => $user->verify_phone,
                        'status_member' => 'member'
                    ];
                    $shouldSubscribe = $this->isTruthy($findUser->explore) || $this->isTruthy($findUser->cci);

                    // if ($shouldSubscribe) {
                    NewsletterFacade::subscribeOrUpdate($findUser->email, [
                        'FNAME'    => $user->name,
                        'MERGE3'   => $findUser->address,      // jika field Address tipe "Address", boleh diganti object address
                        'PHONE'    => $phone,
                        'MMERGE5'  => $findUser->company_name,
                        'MMERGE6'  => $findUser->company_category,
                        'MMERGE8'  => $findUser->job_title,
                        'MMERGE10' => Carbon::now(),           // di audience kamu ini Text → aman
                        'MMERGE11' => $findUser->office_number,
                        'MMERGE12' => $findUser->explore ?? $findUser->cci,
                    ]);

                    // Tambah tag penanda sumber registrasi
                    $this->mcAddTags($findUser->email, [
                        'Register of Membership ' . now()->format('d M Y'),
                    ]);
                    // } else {
                    //     Log::info('Skip Mailchimp subscribe: explore/cci tidak truthy', [
                    //         'email' => $findUser->email,
                    //         'explore' => $findUser->explore,
                    //         'cci' => $findUser->cci
                    //     ]);
                    // }

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
                    $image = QrCode::format('png')
                        ->size(300)->errorCorrection('H')
                        ->generate($codePayment);
                    $output_file = '/public/uploads/qr-code/img-' . time() . '.png';
                    $db = '/storage/uploads/qr-code/img-' . time() . '.png';
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $user = User::create([
                        'name' => $findUser->name,
                        'email' =>  $findUser->email,
                        'password' => $findUser->password,
                        'verify_phone' => 'verified',
                        'isStatus' => 'Active',
                        'qrcode' => $db,
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
                        'explore' => $findUser->explore,
                        'users_id' => $user->id
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

                    $shouldSubscribe = $this->isTruthy($findUser->explore) || $this->isTruthy($findUser->cci);

                    if ($shouldSubscribe) {
                        NewsletterFacade::subscribeOrUpdate($findUser->email, [
                            'FNAME'    => $user->name,
                            'MERGE3'   => $findUser->address,      // jika field Address tipe "Address", boleh diganti object address
                            'PHONE'    => $phone,
                            'MMERGE5'  => $findUser->company_name,
                            'MMERGE6'  => $findUser->company_category,
                            'MMERGE8'  => $findUser->job_title,
                            'MMERGE10' => Carbon::now(),           // di audience kamu ini Text → aman
                            'MMERGE11' => $findUser->office_number,
                            'MMERGE12' => $findUser->explore ?? $findUser->cci,
                        ]);

                        // Tambah tag penanda sumber registrasi
                        $this->mcAddTags($findUser->email, [
                            'Register of Membership ' . now()->format('d M Y'),
                        ]);
                    } else {
                        Log::info('Skip Mailchimp subscribe: explore/cci tidak truthy', [
                            'email' => $findUser->email,
                            'explore' => $findUser->explore,
                            'cci' => $findUser->cci
                        ]);
                    }

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

    protected function isTruthy($val): bool
    {
        if (is_bool($val)) return $val;
        if (is_numeric($val)) return (int)$val !== 0;
        $s = strtolower(trim((string)$val));
        return !in_array($s, ['', '0', 'false', 'no', 'off', 'null', 'undefined'], true);
    }

    /**
     * Tambah tags ke Mailchimp (sama seperti sebelumnya).
     */
    protected function mcAddTags(string $email, array $tags): void
    {
        try {
            $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
            $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');
            if (!$apiKey || !$listId) return;

            $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
            if (!$server) return;

            $subscriberHash = md5(strtolower($email));
            Http::withBasicAuth('anystring', $apiKey)->post(
                "https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}/tags",
                ['tags' => collect($tags)->filter()->values()->map(fn($t) => ['name' => $t, 'status' => 'active'])->all()]
            );
        } catch (\Throwable $e) {
            Log::error('Mailchimp tagging failed: ' . $e->getMessage());
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
            User::where('email', '=', $request->email)->update(['otp' => $otp,  'verify_email' => 'verified',]);
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
    protected function isProvisionalUser(\App\Models\User $u): bool
    {
        // Anggap provisional bila BELUM punya password ATAU belum ada verifikasi apapun
        return (empty($u->password) || is_null($u->verify_email) || is_null($u->verify_phone));
    }
}
