<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Newsletter;

class UserController extends Controller
{

    public function index()
    {
        $id =  auth('sanctum')->user()->id;
        if (!empty($id)) {
            $check = User::where('id', $id)->first();
            if (!empty($check)) {
                // dd(substr()uniqid());
                $mailchimp = Newsletter::isSubscribed($check->email); //returns a boolean
                if ($mailchimp == true) {
                    $mailchimp = 'subscribe';
                } else {
                    $mailchimp = 'unsubscribe';
                }
                $findUser = User::join('profiles', 'profiles.users_id', 'users.id')
                    ->join('company', 'company.id', 'profiles.company_id')
                    ->where('users.id', $id)
                    ->first();
                $data = [
                    'name' => $findUser->name,
                    'email' => $findUser->email,
                    'prefix_phone' => $findUser->prefix_phone,
                    'phone' => $findUser->phone,
                    'fullphone' => $findUser->fullphone,
                    'uuid' => $findUser->uname,
                    'qrcode' => $findUser->qrcode,
                    'date_register' => date('m/y', strtotime($findUser->created_at)),
                    'image' => $findUser->image,
                    'prefix_company_name' => $findUser->prefix,
                    'company_name' => $findUser->company_name,
                    'job_title' => $findUser->job_title,
                    'prefix_office_number' => $findUser->prefix_office_number,
                    'office_number' => $findUser->office_number,
                    'full_office_number' => $findUser->full_office_number,
                    'company_website' => $findUser->company_website,
                    'address' => $findUser->address,
                    'country' => $findUser->country,
                    'city' => $findUser->city,
                    'postal_code' => $findUser->portal_code,
                    'company_category' => $findUser->company_category,
                    'company_other' => $findUser->company_other,
                    'cci' => $findUser->cci,
                    'explore' => $findUser->explore,
                    'subscribe' => $mailchimp,
                    'verify_email' => $findUser->verify_email,
                    'verify_phone' => $findUser->verify_phone

                ];
                $response['status'] = 200;
                $response['message'] = 'User Found';
                $response['payload'] = $data;
            } else {
                $response['status'] = 401;
                $response['message'] = 'User Not Found';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 404;
            $response['message'] = 'Token Not Found';
            $response['payload'] = null;
        }

        return response()->json($response, 200);
    }

    public function update_profile(Request $request)
    {
        $id = auth('sanctum')->user()->id;

        $check = User::where('id', $id)->first();
        $profile = ProfileModel::where('users_id', $check->id)->first();
        if (!empty($check)) {

            $file = $request->image;
            if (!empty($file)) {
                $imageName = time() . '.' . $request->image->extension();
                $db = '/storage/profile/' . $imageName;
                $save_folder = $request->image->storeAs('public/profile', $imageName);
                $profile->image = $db;
                $profile->save();
            }
            $check->name = $request->name;
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

    public function update_company(Request $request)
    {
        $id = auth('sanctum')->user()->id;
        $check = User::where('users.id', $id)->join('profiles', 'profiles.users_id', 'users.id')
            ->join('company', 'company.id', 'profiles.company_id')
            ->select('users.id as users_id', 'profiles.id as profile_id', 'company.id as company_id')
            ->first();
        // dd($check);
        if (!empty($check)) {
            $updateCompany = CompanyModel::where('id', $check->company_id)->first();
            $updateProfile = ProfileModel::where('id', $check->profile_id)->first();
            $updateProfile->job_title = $request->job_title;
            $updateProfile->save();
            $updateCompany->prefix = $request->prefix;
            $updateCompany->company_name = $request->company_name;
            $updateCompany->prefix_office_number = $request->prefix_office_number;
            $updateCompany->office_number = $request->office_number;
            $updateCompany->company_website = $request->company_website;
            $updateCompany->address = $request->address;
            $updateCompany->country = $request->country;
            $updateCompany->city = $request->city;
            $updateCompany->portal_code = $request->postal_code;
            $updateCompany->cci = $request->cci;
            $updateCompany->explore = $request->explore;
            $updateCompany->company_category = $request->company_category;
            $updateCompany->company_other = $request->company_other;
            $updateCompany->save();
            $response['status'] = 200;
            $response['message'] = 'Company Update Successfully';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }

    public function changePassword(Request $request)
    {
        $id = auth('sanctum')->user()->id;
        $validate = Validator::make($request->all(), [
            'current_password' => 'required',
        ], [
            'current_password.required' => 'Password saat tidak boleh kosong',
        ]);
        if ($validate->fails()) {
            $data = [
                'current_password' => $validate->errors()->first('current_password'),
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {

            $user = User::where('id', $id)->first();
            if (Hash::check($request->current_password, $user->password)) {
                $validate = Validator::make($request->all(), [
                    'new_password' => 'required|different:current_password|string|min:6',
                ], [
                    'new_password.required' => 'Silahkan isi password baru anda',
                    'new_password.different' => 'Password harus berbeda',
                    'new_password.min' => 'Password minimal 6 karakter',

                ]);
                if ($validate->fails()) {
                    $data = [
                        'new_password' => $validate->errors()->first('new_password')
                    ];
                    $response['status'] = 422;
                    $response['message'] = 'Invalid data';
                    $response['payload'] = $data;
                } else {
                    $user->password = Hash::make($request->new_password);
                    $user->save();
                    $response['status'] = 200;
                    $response['message'] = 'Successfully Update new password';
                    $response['payload'] = null;
                }
            } else {
                $data = [
                    'current_password' => 'Password was wrong',
                ];
                $response['status'] = 422;
                $response['message'] = 'Invalid data';
                $response['payload'] = $data;
            }
        }
        return response()->json($response, 200);
    }

    public function subscribe()
    {
        $id = auth('sanctum')->user()->id;

        $findUsers = User::where('id', $id)->first();

        if (!empty($findUsers)) {
            Newsletter::subscribeOrUpdate($findUsers->email);

            $response['status'] = 200;
            $response['message'] = 'Successfully Subscribe Email';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }

    public function unsubscribe()
    {
        $id = auth('sanctum')->user()->id;

        $findUsers = User::where('id', $id)->first();

        if (!empty($findUsers)) {
            Newsletter::unsubscribe($findUsers->email);
            $response['status'] = 200;
            $response['message'] = 'Successfully UnSubscribe Email';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }

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

    public function verifyOtp(Request $request)
    {
        // Validasi input dasar
        $validator = Validator::make(
            $request->all(),
            [
                'params' => 'required',
                'new_email' => 'exists:users,email',
            ],
            [
                'new_email.unique' => 'Email sudah digunakan'
            ]
        );

        // Jika validasi gagal, kembalikan response error
        if ($validator->fails()) {
            return response()->json([
                'status'  => 401,
                'message' => 'Something was wrong',
                'payload' => [
                    'params' => $validator->errors()->first('params')
                ]
            ]);
        }

        // Ambil semua data request yang dibutuhkan
        $otp         = $request->otp;
        $params      = $request->params; // "change" atau "verify"
        $email       = $request->email;
        $newEmail    = $request->new_email;
        $prefixPhone = $request->prefix_phone;
        $oldPhone    = $request->old_phone;
        $newPhone    = $request->new_phone;
        $phone       = $request->phone;

        // Jika ada request->phone, maka oldPhone di-override
        if (!empty($phone)) {
            $oldPhone = $phone;
        }

        // Persiapkan struktur response default
        $response = [
            'status'  => 401,
            'message' => 'Something was wrong',
            'payload' => null
        ];

        /**
         * ----------------------------------------------------------------
         * LOGIKA UNTUK NOMOR TELEPON
         * ----------------------------------------------------------------
         */
        if (!empty($oldPhone)) {
            // Coba temukan user berdasarkan fullphone dan otp
            $findUser = ProfileModel::join('users', 'users.id', '=', 'profiles.users_id')
                ->where([
                    ['profiles.fullphone', $oldPhone],
                    ['users.otp', $otp]
                ])->first();

            // Jika user tidak ditemukan, kembalikan error
            if (empty($findUser)) {
                return response()->json($response);
            }

            // Jika user ditemukan, cek params
            if ($params === 'change') {
                // Pastikan data untuk mengganti nomor telepon ada
                if (empty($prefixPhone) || empty($newPhone)) {
                    $response['payload'] = ['phone' => 'Prefix/nomor baru tidak boleh kosong'];
                    return response()->json($response);
                }

                // Ubah nomor telepon di table profiles
                $fullPhone = $prefixPhone . $newPhone;
                $change    = ProfileModel::where('fullphone', $findUser->fullphone)->first();
                $change->fullphone   = $fullPhone;
                $change->prefix_phone = $prefixPhone;
                $change->phone       = $newPhone;
                $change->save();

                // Update data user
                $update = User::where('id', $change->users_id)->first();
                $update->otp           = null;
                $update->verify_phone  = 'verified';
                $update->save();

                // Set response sukses
                $response['status']  = 200;
                $response['message'] = 'Success Change Phone Number';
                $response['payload'] = null;
                return response()->json($response);
            } elseif ($params === 'verify') {
                // Verifikasi nomor telepon
                $verify = ProfileModel::where('fullphone', $oldPhone)->first();
                if ($verify) {
                    $update = User::where('id', $verify->users_id)->first();
                    $update->otp          = null;
                    $update->verify_phone = 'verified';
                    $update->save();

                    $response['status']  = 200;
                    $response['message'] = 'Success Verify Phone Number';
                    $response['payload'] = null;
                    return response()->json($response);
                }

                // Jika data profile tidak ditemukan
                return response()->json($response);
            }

            // Jika params bukan "change" atau "verify"
            $response['payload'] = [
                'params' => 'Please Choose params ( verify / change )'
            ];
            return response()->json($response);
        }

        /**
         * ----------------------------------------------------------------
         * LOGIKA UNTUK EMAIL
         * ----------------------------------------------------------------
         */
        if (!empty($email)) {
            // Coba temukan user berdasarkan email dan otp
            $findUser = User::where([
                ['email', $email],
                ['otp', $otp]
            ])->first();

            // Jika user tidak ditemukan, kembalikan error
            if (empty($findUser)) {
                return response()->json($response);
            }

            // Jika user ditemukan, cek params
            if ($params === 'change') {
                // Ganti email user
                $findUser->email        = $newEmail;
                $findUser->verify_email = 'verified';
                $findUser->otp          = null;
                $findUser->save();

                $response['status']  = 200;
                $response['message'] = 'Success Change Email';
                $response['payload'] = null;
                return response()->json($response);
            } elseif ($params === 'verify') {
                // Verifikasi email user
                $findUser->verify_email = 'verified';
                $findUser->otp          = null;
                $findUser->save();

                $response['status']  = 200;
                $response['message'] = 'Success Verify Email';
                $response['payload'] = null;
                return response()->json($response);
            }

            // Jika params bukan "change" atau "verify"
            $response['payload'] = [
                'params' => 'Please Choose params ( verify / change )'
            ];
            return response()->json($response);
        }

        /**
         * ----------------------------------------------------------------
         * JIKA TELEPON ATAU EMAIL SAMA SEKALI TIDAK DIISI
         * ----------------------------------------------------------------
         */
        $response['payload'] = [
            'phone'     => 'Silakan isi `phone` (untuk verify/change)',
            'email'     => 'Silakan isi `email` (untuk verify)',
            'new_email' => 'Wajib diisi jika ingin mengubah email'
        ];
        return response()->json($response);
    }


    public function check(Request $request)
    {
        $phone = $request->phone;
        $email = $request->email;

        if (!empty($phone)) {
            //
            $check = ProfileModel::where('fullphone', $phone)->first();
            if (empty($check)) {
                $response['status'] = 200;
                $response['message'] = 'Next';
                $response['payload'] = null;
            } else {
                $data = [
                    'phone' => 'Phone number already used',
                ];
                $response['status'] = 401;
                $response['message'] = 'Something was wrong';
                $response['payload'] = $data;
            }
        } else {
            $check = User::where('email', $email)->first();
            if (empty($check)) {
                $response['status'] = 200;
                $response['message'] = 'Next';
                $response['payload'] = null;
            } else {
                $data = [
                    'email' => 'Email address already used',
                ];
                $response['status'] = 401;
                $response['message'] = 'Something was wrong';
                $response['payload'] = $data;
            }
        }

        return response()->json($response);
    }

    public function deleteAccount()
    {
        $id = auth('sanctum')->user()->id;

        $deleteTableUsers = User::where('id', $id)->delete();
        $deleteTableProfile = ProfileModel::where('users_id', $id)->delete();
        $deleteTableCompany = CompanyModel::where('users_id', $id)->delete();
        $response['status'] = 200;
        $response['message'] = 'Success Delete Account';
        $response['payload'] = null;

        return response()->json($response);
    }
}
