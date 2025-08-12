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
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Newsletter\NewsletterFacade;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        if ($request->filter == 'unregist') {
            $list = MemberModel::whereNull('register_as')
                ->where('created_at', '>=', Carbon::now()->startOfYear())
                ->orderby('id', 'desc')
                ->get();
        } else {
            $list = User::leftjoin('profiles', 'profiles.users_id', 'users.id')
                ->leftjoin('company', 'company.id', 'profiles.company_id')
                ->whereNotNull('users.isStatus')
                ->orderBy('users.id', 'desc')
                ->select('*', 'users.id as id')
                ->get();
        }

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

        $countDoubleVerify = User::whereNotNull('users.isStatus')
            ->where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNotNull('verify_phone')
            ->whereNotNull('verify_email')
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
            'countDoubleVerify' => $countDoubleVerify
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
                $user->isStatus = 'Active';
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

    public function importToMailchimp(Request $request, User $user)
    {
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email tidak valid.'], 422);
        }

        $company = CompanyModel::where('users_id', $user->id)->first();
        $profile = ProfileModel::where('users_id', $user->id)->first();

        $flagExploreCci = $company->explore ?? $company->cci ?? null;
        if (empty($flagExploreCci)) {
            return response()->json(['success' => false, 'message' => 'Data explore/cci tidak tersedia.'], 422);
        }

        $mergeFields = [
            'FNAME'    => $user->name,
            'MERGE3'   => optional($company)->address,
            'PHONE'    => optional($profile)->phone,
            'MMERGE5'  => optional($company)->company_name,
            'MMERGE6'  => optional($company)->company_category == 'other'
                ? (optional($company)->company_other ?? 'other')
                : optional($company)->company_category,
            'MMERGE8'  => optional($profile)->job_title,
            'MMERGE10' => Carbon::now()->toDateTimeString(),
            'MMERGE11' => optional($company)->office_number,
            'MMERGE12' => $flagExploreCci,
        ];

        $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
        $server = config('newsletter.server');
        if (!$server && $apiKey) {
            $parts = explode('-', $apiKey);
            $server = $parts[1] ?? null; // derive "us16"
        }
        $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

        if (!$apiKey || !$server || !$listId) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi Mailchimp belum lengkap (apiKey/server/listId).'
            ], 500);
        }

        try {
            // 1) upsert member
            NewsletterFacade::subscribeOrUpdate($user->email, $mergeFields);
            if (!NewsletterFacade::lastActionSucceeded()) {
                $err = NewsletterFacade::getLastError() ?: 'Gagal impor ke Mailchimp.';
                return response()->json(['success' => false, 'message' => $err], 500);
            }

            // 2) tambah tag (default: Register of Membership + tanggal)
            $defaultTag = 'Register of Membership ' . Carbon::now()->format('d M Y');
            $tags = (array) ($request->input('tags') ?: [$defaultTag]);

            $subscriberHash = md5(strtolower($user->email));
            Http::withBasicAuth('anystring', $apiKey)->post(
                "https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}/tags",
                ['tags' => collect($tags)->filter()->values()->map(fn($t) => ['name' => $t, 'status' => 'active'])->all()]
            );

            return response()->json(['success' => true, 'message' => 'Berhasil diimport ke Mailchimp beserta tag.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
