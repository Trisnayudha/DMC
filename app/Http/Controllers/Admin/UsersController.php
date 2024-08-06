<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = User::leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftjoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus')
            ->orderBy('users.id', 'desc')
            ->select('*', 'users.id as id')
            ->get();

        $countMember = User::whereNotNull('users.isStatus')
            ->where('created_at', '>=', Carbon::now()->startOfYear())
            ->count();

        $countVerifyEmail = User::whereNotNull('users.isStatus')
            ->where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNotNull('verify_email')
            ->whereNull('verify_phone')
            ->count();


        $countVerifyPhone = User::whereNotNull('users.isStatus')
            ->where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNotNull('verify_phone')
            ->whereNull('verify_email')
            ->count();

        $countUnRegistered = MemberModel::where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNull('register_as')
            ->count();

        $data = [
            'list' => $list,
            'countMember' => $countMember,
            'countVerifyEmail' => $countVerifyEmail,
            'countVerifyPhone' => $countVerifyPhone,
            'countUnRegistered' => $countUnRegistered,
        ];

        return view('admin.users.index', $data);
    }

    public function store(Request $request)
    {
        try {
            $user = User::firstOrNew(['email' => $request->email]);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
            $company->prefix = $request->prefix;
            $company->company_name = $request->company_name;
            $company->company_website = $request->company_website;
            $company->company_category = $request->company_category;
            $company->company_other = $request->company_other;
            $company->address = $request->address;
            $company->city = $request->city;
            $company->portal_code = $request->portal_code;
            $company->office_number = $request->office_number;
            $company->country = $request->country;
            $company->users_id = $user->id;
            $company->save();

            $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
            $profile->phone = $request->phone;
            $profile->job_title = $request->job_title;
            $profile->users_id = $user->id;
            $profile->company_id = $company->id;
            $profile->save();

            return redirect()->route('users')->with('success', 'Successfully added user');
        } catch (\Exception $e) {
            // Handle the exception
            return back()->withErrors('Failed to add user. Error: ' . $e->getMessage());
        }
    }


    public function import(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);

        $the_file = $request->file('uploaded_file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range = range(2, $row_limit);
            $column_range = range('M', $column_limit);

            $startcount = 0;

            foreach ($row_range as $row) {
                $email = $sheet->getCell('E' . $row)->getValue();
                $user = User::firstOrNew(['email' => $email]);
                $user->name = $sheet->getCell('B' . $row)->getValue();
                $user->email = $email;
                $user->save();

                $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
                $company->company_name = $sheet->getCell('A' . $row)->getValue();
                $company->company_website = $sheet->getCell('F' . $row)->getValue();
                $company->company_category = $sheet->getCell('G' . $row)->getValue();
                $company->company_other = $sheet->getCell('H' . $row)->getValue();
                $company->address = $sheet->getCell('I' . $row)->getValue();
                $company->city = $sheet->getCell('J' . $row)->getValue();
                $company->portal_code = $sheet->getCell('K' . $row)->getValue();
                $company->full_office_number = $sheet->getCell('L' . $row)->getValue();
                $company->save();

                $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
                $profile->fullphone = $sheet->getCell('D' . $row)->getValue();
                $profile->job_title = $sheet->getCell('C' . $row)->getValue();
                $profile->users_id = $user->id;
                $profile->company_id = $company->id;
                $profile->save();

                $startcount++;
            }

            return back()->with('success', 'Successfully imported ' . $startcount . ' data');
        } catch (Exception $e) {
            $error_code = $e;
            return back()->withErrors('There was a problem uploading the data! Error Code: ' . $error_code);
        }
    }



    public function member()
    {

        $list = MemberModel::where('register_as', '=', 'Member')->orderBy('created_at', 'desc')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.member.index', $data);
    }

    public function editUserEvent($id)
    {
        $data = User::join('payment', 'payment.member_id', 'users.id')
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftjoin('company', 'company.id', 'profiles.company_id')
            ->where('payment.id', $id)
            ->first();
        if (!empty($data)) {

            return response()->json([
                'status' => 1,
                'payload' => $data
            ]);
        } else {
            $data = User::join('profiles', 'profiles.users_id', 'users.id')
                ->join('company', 'company.id', 'profiles.company_id')
                ->where('payment.id', $id)
                ->first();
            return response()->json([
                'status' => 1,
                'payload' => $data
            ]);
        }
    }

    public function checkMember($email)
    {
        $check = MemberModel::where('email', $email)->first();
        if (!empty($check)) {
            return response()->json([
                'status' => 1,
                'message' => 'Members'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Non-Members'
            ]);
        }
    }
}
