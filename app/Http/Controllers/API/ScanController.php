<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Users\UsersConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScanController extends Controller
{
    public function users(Request $request)
    {
        $uname = $request->uname;
        $validate = Validator::make(
            $request->all(),
            [
                'uname' => ['required'],
            ],
            [
                'uname.required' => 'Data Qr code tidak ditemukan',
            ]
        );

        if ($validate->fails()) {
            $data = [
                'error' => $validate->errors()->first('uname')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {

            $check = User::where('uname', '=', $uname)->first();
            if (!empty($check)) {
                $detail = User::leftjoin('profiles', 'profiles.users_id', 'users.id')
                    ->leftjoin('company', 'company.id', 'profiles.company_id')
                    ->select('users.id', 'users.name', 'profiles.image', 'users.verify_email', 'users.verify_phone', 'company.prefix', 'company.company_name')
                    ->where('users.uname', '=', $uname)->first();
                $response['status'] = 200;
                $response['message'] = 'User Found';
                $response['payload'] = $detail;
            } else {
                $response['status'] = 401;
                $response['message'] = 'User Not Found';
                $response['payload'] = null;
            }
        }
        return response()->json($response, 200);
    }

    public function postRequest(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $users_id = $request->users_id;
        $validate = Validator::make(
            $request->all(),
            [
                'users_id' => ['required'],
            ],
            [
                'users_id.required' => 'Id tidak boleh kosong',
            ]
        );

        if ($validate->fails()) {
            $data = [
                'error' => $validate->errors()->first('users_id')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else {
            if ($users_id != $id) {
                $post = UsersConnection::firstOrNew([
                    'users_id' => $id,
                    'users_id_target' => $users_id
                ]);

                $post->users_id = $id;
                $post->users_id_target = $users_id;
                $post->save();
                $response['status'] = 200;
                $response['message'] = 'User Connected';
                $response['payload'] = null;
            } else {
                $response['status'] = 401;
                $response['message'] = 'Anda tidak dapat melakukan scan menggunakan qr sendiri';
                $response['payload'] = null;
            }
        }
        return response()->json($response, 200);
    }

    public function listConnected($limit = 10)
    {
        $id =  auth('sanctum')->user()->id;

        $check = UsersConnection::where('users_id', '=', $id)->first();
        if (!empty($check)) {
            $detail = User::leftjoin('profiles', 'profiles.users_id', 'users.id')
                ->leftjoin('company', 'company.id', 'profiles.company_id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'profiles.fullphone',
                    'company.full_office_number',
                    'company.company_website',
                    'profiles.image',
                    'users.verify_email',
                    'users.verify_phone',
                    'company.prefix',
                    'company.company_name'
                )
                ->where('users.id', '=', $id)->paginate($limit);
            $response['status'] = 200;
            $response['message'] = 'User Found';
            $response['payload'] = $detail;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Data kosong';
            $response['payload'] = null;
        }

        return response()->json($response, 200);
    }
}
