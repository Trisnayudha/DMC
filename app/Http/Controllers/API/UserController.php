<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                $mailchimp = Newsletter::hasMember($check->email); //returns a boolean
                $findUser = User::join('profiles', 'profiles.users_id', 'users.id')->join('company', 'company.id', 'profiles.company_id')->first();
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
                $filename = $request->image->getClientOriginalName();
                $output_file = 'public/uploads/image-profile/img-' . time() . '.png';
                $output_db = 'storage/uploads/image-profile/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $filename); //storage/app/public/img/qr-code/img-1557309130.png
                $profile->image = $output_db;
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
        $country_phone_office = $request->country_phone_office;
        $office_number = $country_phone_office . $request->office_number;
        if (!empty($check)) {
            $updateCompany = CompanyModel::where('id', $check->company_id)->first();
            $updateProfile = ProfileModel::where('id', $check->profile_id)->first();
            $updateProfile->job_title = $request->job_title;
            $updateProfile->save();
            $updateCompany->company_name = $request->company_name;
            $updateCompany->office_number = $office_number;
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
}
