<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profiles\Profile;
use App\Models\Profiles\ProfileApi;
use App\Models\Profiles\ProfileService;
use App\Models\ProfileUsahas\ProfileUsaha;
use App\Models\User;
use App\Models\Users\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            $response['error'] = $validate->errors();

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
