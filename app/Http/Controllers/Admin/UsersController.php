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

    public function importToMailchimp(Request $request)
    {
        $email  = strtolower(trim($request->input('email', '')));
        $userId = $request->input('user_id');
        $tags   = (array) $request->input('tags', []);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email tidak valid.'], 422);
        }

        $user    = $userId ? User::find($userId) : User::where('email', $email)->first();
        $company = $user ? CompanyModel::where('users_id', $user->id)->first()
            : CompanyModel::where('company_email', $email)->first();
        $profile = $user ? ProfileModel::where('users_id', $user->id)->first()
            : ProfileModel::where('email', $email)->first();

        $flagExploreCci = optional($company)->explore ?? optional($company)->cci;
        if (empty($flagExploreCci)) {
            return response()->json(['success' => false, 'message' => 'Data explore/cci tidak tersedia.'], 422);
        }

        // === Build merge_fields yang aman ===
        $merge = [];

        // FNAME
        if (!empty($user->name)) $merge['FNAME'] = $user->name;

        // ADDRESS (MERGE3) bertipe Address → kirim object, skip kalau kosong semua
        $addr = [
            'addr1'   => (string) ($company->company_address ?? ''),
            'addr2'   => '',
            'city'    => (string) ($company->city ?? ''),
            'state'   => '',
            'zip'     => (string) ($company->portal_code ?? ''),
            'country' => (string) ($company->country ?? ''),
        ];
        $hasAddr = collect($addr)->filter(fn($v) => trim((string)$v) !== '')->isNotEmpty();
        if ($hasAddr) $merge['MERGE3'] = $addr;

        // PHONE (MERGE4) → harus format internasional (+62...) kalau tidak, skip
        $phone = $profile->phone ?? $profile->fullphone ?? null;
        $phone = is_string($phone) ? trim($phone) : $phone;
        if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', $phone)) {
            $merge['MERGE4'] = $phone;
        }
        // COMPANY
        if (!empty($company->company_name)) $merge['MMERGE5'] = $company->company_name;

        // CATEGORY company
        if (!empty($company->company_category)) {
            $merge['MMERGE6'] = $company->company_category === 'other'
                ? ($company->company_other ?? 'other')
                : $company->company_category;
        }

        // JOB TITLE
        if (!empty($profile->job_title)) $merge['MMERGE8'] = $profile->job_title;

        // DATE REGISTER (di audience kamu tipe "Text" → bebas)
        $merge['MMERGE10'] = now()->toDateTimeString();

        // OFFICE NUMBER
        if (!empty($company->office_number)) $merge['MMERGE11'] = $company->office_number;

        // EXPLORE (Text)
        if (!empty($flagExploreCci)) $merge['MMERGE12'] = $flagExploreCci;

        // WEBSITE
        if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;

        // Buang nilai kosong/null kecuali address (sudah ditangani)
        $merge = collect($merge)->reject(fn($v, $k) => $k !== 'MERGE3' && (is_null($v) || trim((string)$v) === ''))->all();

        // === Konfigurasi Mailchimp ===
        $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
        $server = config('newsletter.server') ?: (function ($k) {
            $p = explode('-', $k);
            return $p[1] ?? null;
        })($apiKey);
        $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

        if (!$apiKey || !$server || !$listId) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi Mailchimp belum lengkap.'], 500);
        }

        try {
            // === Manual PUT untuk dapat error detail ===
            $subscriberHash = md5(strtolower($email));
            $url = "https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}";

            $payload = [
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'status'        => 'subscribed',
                'merge_fields'  => $merge,
            ];

            $resp = \Illuminate\Support\Facades\Http::withBasicAuth('anystring', $apiKey)
                ->timeout(20)
                ->put($url, $payload);

            if (!$resp->successful()) {
                // Ambil error detail dari Mailchimp
                $json = $resp->json();
                $detail = $json['detail'] ?? 'Gagal impor.';
                // Kadang ada field problems
                if (!empty($json['errors'])) {
                    // contoh: [{"field":"merge_fields.PHONE","message":"..."}]
                    $problems = collect($json['errors'])->map(function ($e) {
                        return ($e['field'] ?? 'field') . ': ' . ($e['message'] ?? '');
                    })->implode(' | ');
                    $detail .= ' — ' . $problems;
                }
                return response()->json(['success' => false, 'message' => $resp->status() . ': ' . $detail], 400);
            }

            // === Tambah tags ===
            if (empty($tags)) {
                $tags = ['Register of Membership ' . now()->format('d M Y')];
            }
            \Illuminate\Support\Facades\Http::withBasicAuth('anystring', $apiKey)
                ->post("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}/tags", [
                    'tags' => collect($tags)->filter()->values()->map(fn($t) => ['name' => $t, 'status' => 'active'])->all()
                ]);

            return response()->json(['success' => true, 'message' => 'Imported + tagged.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
