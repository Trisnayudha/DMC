<?php

namespace App\Http\Controllers;

use App\Models\Company\CompanyModel;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
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
            ->leftjoin('company', 'company.id', 'profiles.company_id')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.users.index', $data);
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);
        $the_file = $request->file('uploaded_file');
        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range    = range(2, $row_limit);
            $column_range = range('M', $column_limit);
            $startcount = 1;
            $data = array();
            foreach ($row_range as $row) {
                $user = User::firstOrNew(array('email' => $sheet->getCell('E' . $row)->getValue()));
                $user->name = $sheet->getCell('B' . $row)->getValue();
                $user->email = $sheet->getCell('E' . $row)->getValue();
                // $user->verify_phone = 'verified';
                // $user->password = Hash::make('DMCAPPS');
                $user->save();
                $company = CompanyModel::firstOrNew([
                    'users_id' => $user->id
                ]);
                $company->company_name = $sheet->getCell('A' . $row)->getValue();
                $company->company_website = $sheet->getCell('F' . $row)->getValue();
                $company->company_category = $sheet->getCell('G' . $row)->getValue();
                $company->company_other = $sheet->getCell('H' . $row)->getValue();
                $company->address = $sheet->getCell('I' . $row)->getValue();
                $company->city = $sheet->getCell('J' . $row)->getValue();
                $company->portal_code = $sheet->getCell('K' . $row)->getValue();
                $company->full_office_number = $sheet->getCell('L' . $row)->getValue();
                $company->save();

                $profile = ProfileModel::firstOrNew([
                    'users_id' => $user->id
                ]);
                $profile->fullphone = $sheet->getCell('D' . $row)->getValue();
                $profile->job_title = $sheet->getCell('C' . $row)->getValue();
                $profile->users_id = $user->id;
                $profile->company_id = $company->id;
                $profile->save();
                // $user->register_as = $sheet->getCell('M' . $row)->getValue();
                // $codePayment = strtoupper(Str::random(7));
                // $payment = Payment::firstOrNew(array('member_id' => $user->id));
                // $payment->member_id = $user->id;
                // $payment->package = 'free';
                // $payment->code_payment = $codePayment;
                // $payment->price = 0;
                // $payment->status = 'Waiting';
                // $payment->save();


                $startcount++;
            }
            return back()->with('success', 'Success Import ' . $startcount . ' data');
        } catch (Exception $e) {
            // dd($e);
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }

    public function check_email(Request $request)
    {
        $email = $request->email;

        $check = User::where('email', '=', $email)->first();
        if ($check) {
            $res['status'] = 1;
        } else {
            $res['status'] = 0;
        }
        return response()->json($res);
    }

    public function member()
    {

        $list = MemberModel::where('register_as', '=', 'Member')->orderBy('created_at', 'desc')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.member.index', $data);
    }
}
