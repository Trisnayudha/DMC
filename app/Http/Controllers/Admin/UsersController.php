<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\UserEditLog;
use App\Support\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }
    public function index(Request $request)
    {
        $filter       = $request->filter;
        $dateFrom     = $request->date_from;
        $dateTo       = $request->date_to;
        $month        = $request->month;
        $year         = $request->year;
        $statusMember = $request->status_member; // 'active' | 'pending' | ''

        if ($filter == 'unregist') {
            $query = MemberModel::whereNull('register_as');

            if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);
            if ($month)    $query->whereMonth('created_at', $month);
            if ($year)     $query->whereYear('created_at', $year);

            if (!$dateFrom && !$dateTo && !$month && !$year) {
                $query->where('created_at', '>=', Carbon::now()->startOfYear());
            }

            $list = $query->orderBy('id', 'desc')->get();
        } else {
            $query = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
                ->leftJoin('company', 'company.id', 'profiles.company_id')
                ->whereNotNull('users.isStatus');
            if ($filter == 'this_month') {
                $query->whereBetween('users.created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
            }

            if ($filter == 'self_edited') {
                $selfEditedIds = DB::table('user_edit_logs')
                    ->whereNull('admin_id')
                    ->pluck('user_id')
                    ->unique()
                    ->values()
                    ->all();
                $query->whereIn('users.id', $selfEditedIds);
            }

            if ($filter == 'password_null') {
                $query->whereNull('users.password');
            }

            if ($statusMember === 'active') {
                $query->where('users.status_member', 'active');
            } elseif ($statusMember === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('users.status_member')
                        ->orWhere('users.status_member', 'pending');
                });
            } elseif ($filter === 'declined') {
                $query->where('users.status_member', 'declined');
            } else {
                // Default "All": exclude declined
                $query->where(function ($q) {
                    $q->whereNull('users.status_member')
                        ->orWhere('users.status_member', '!=', 'declined');
                });
            }

            if ($dateFrom) $query->whereDate('users.created_at', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('users.created_at', '<=', $dateTo);
            if ($month)    $query->whereMonth('users.created_at', $month);
            if ($year)     $query->whereYear('users.created_at', $year);

            $list = $query->orderBy('users.id', 'desc')
                ->select(
                    'users.*',
                    'users.created_at as user_created_at',
                    'profiles.*',
                    'company.*',
                    'users.id as user_id'
                )
                ->get();

            // Mapping manual: jika ada company lain dengan nama sama yang sudah verified,
            // tandai row ini agar tombol verify bisa langsung biru.
            $verifiedCompanyNameMap = CompanyModel::query()
                ->where('is_verified', true)
                ->whereNotNull('company_name')
                ->whereRaw("TRIM(company_name) <> ''")
                ->selectRaw('LOWER(TRIM(company_name)) as normalized_name')
                ->distinct()
                ->pluck('normalized_name')
                ->flip();

            $list = $list->map(function ($row) use ($verifiedCompanyNameMap) {
                $normalizedName = Str::lower(trim((string) ($row->company_name ?? '')));
                $row->has_verified_company_name = $normalizedName !== '' && $verifiedCompanyNameMap->has($normalizedName);
                return $row;
            });
        }

        // Self-edit map: user_id => latest self-edit timestamp (single query)
        $selfEditMap = DB::table('user_edit_logs')
            ->whereNull('admin_id')
            ->select('user_id', DB::raw('MAX(created_at) as last_self_edit'))
            ->groupBy('user_id')
            ->pluck('last_self_edit', 'user_id')
            ->all();

        // Stats
        $countActiveMember = User::whereNotNull('isStatus')
            ->where('status_member', 'active')
            ->count();

        $countPendingMember = User::whereNotNull('isStatus')
            ->where(function ($q) {
                $q->whereNull('status_member')
                    ->orWhere('status_member', 'pending');
            })
            ->count();

        $countDeclined = User::whereNotNull('isStatus')
            ->where('status_member', 'declined')
            ->count();

        $countNewThisMonth = User::whereNotNull('isStatus')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $countUnRegistered = MemberModel::where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNull('register_as')
            ->count();

        $countVerifyEmail = User::whereNotNull('isStatus')
            ->whereNotNull('verify_email')->whereNull('verify_phone')->count();

        $countVerifyPhone = User::whereNotNull('isStatus')
            ->whereNotNull('verify_phone')->whereNull('verify_email')->count();

        $countDoubleVerify = User::whereNotNull('isStatus')
            ->whereNotNull('verify_phone')->whereNotNull('verify_email')->count();

        $countSelfEdited = DB::table('user_edit_logs')
            ->whereNull('admin_id')
            ->distinct('user_id')
            ->count('user_id');

        $countActiveWithoutPassword = User::whereNotNull('isStatus')
            ->where('status_member', 'active')
            ->whereNull('password')
            ->count();

        return view('admin.users.index', [
            'list'               => $list,
            'countActiveMember'  => $countActiveMember,
            'countPendingMember' => $countPendingMember,
            'countDeclined'      => $countDeclined,
            'countNewThisMonth'  => $countNewThisMonth,
            'countUnRegistered'  => $countUnRegistered,
            'countVerifyEmail'   => $countVerifyEmail,
            'countVerifyPhone'   => $countVerifyPhone,
            'countDoubleVerify'  => $countDoubleVerify,
            'countSelfEdited'    => $countSelfEdited,
            'countActiveWithoutPassword' => $countActiveWithoutPassword,
            'selfEditMap'        => $selfEditMap,
        ]);
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

    // App\Http\Controllers\Admin\UsersController.php (tambahin method ini)
    public function updateTier(Request $request, $id)
    {
        $request->validate([
            'tier' => 'required|in:reguler,black',
        ]);

        $user = User::findOrFail($id);
        $user->tier = $request->tier;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tier updated.',
            'tier'    => $user->tier,
        ]);
    }

    public function verifyMember(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->filled('name') || $request->filled('email') || $request->filled('job_title') || $request->filled('phone')) {
            if ($request->filled('name')) {
                $user->name = trim($request->input('name'));
            }
            if ($request->filled('email')) {
                $user->email = trim($request->input('email'));
            }
            if ($request->filled('job_title') || $request->filled('phone')) {
                $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
                if ($request->filled('job_title')) {
                    $profile->job_title = trim($request->input('job_title'));
                }
                if ($request->filled('phone')) {
                    $profile->phone = trim($request->input('phone'));
                }
                $profile->save();
            }
        }

        $verifiedAt = now();

        $user->status_member = 'active';
        $user->uname = $this->generateVerificationMemberId($user, $verifiedAt);

        try {
            $qrImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($user->uname);

            $fileName = 'img-verify-' . $user->id . '-' . $verifiedAt->timestamp . '.png';
            $outputFile = '/public/uploads/qr-code/' . $fileName;
            $dbPath = '/storage/uploads/qr-code/' . $fileName;

            Storage::disk('local')->put($outputFile, $qrImage);
            $user->qrcode = $dbPath;
        } catch (\Throwable $e) {
            Log::warning('verifyMember: QR regeneration failed for user ' . $id . ': ' . $e->getMessage());
        }

        $user->save();

        // Auto-import to Mailchimp after verify
        $company = CompanyModel::where('users_id', $user->id)->first();
        $profile = ProfileModel::where('users_id', $user->id)->first();

        $email = strtolower(trim($user->email ?? ''));

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
                $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
                $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

                if ($apiKey && $server && $listId) {
                    $merge = [];
                    if (!empty($user->name))               $merge['FNAME']    = $user->name;
                    if (!empty($company->company_name))    $merge['MMERGE5']  = $company->company_name;
                    if (!empty($profile->job_title))       $merge['MMERGE7']  = $profile->job_title;
                    if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;
                    $merge['MMERGE11'] = now()->format('m/d/Y');

                    $phone = $profile->fullphone ?? $profile->phone ?? null;
                    if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', trim((string) $phone))) {
                        $merge['MERGE4'] = trim((string) $phone);
                    }

                    $subscriberHash = md5($email);
                    Http::withBasicAuth('anystring', $apiKey)
                        ->timeout(20)
                        ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}", [
                            'email_address' => $email,
                            'status_if_new' => 'subscribed',
                            'status'        => 'subscribed',
                            'merge_fields'  => $merge,
                        ]);
                }
            } catch (\Throwable $e) {
                Log::warning('verifyMember: Mailchimp import failed for user ' . $id . ': ' . $e->getMessage());
            }

            try {
                $setPasswordUrl = null;
                if (empty($user->password)) {
                    $token = Password::broker()->createToken($user);
                    $setPasswordUrl = route('password.reset', [
                        'token' => $token,
                        'email' => $email,
                    ]);
                }

                $memberId = $user->uname;
                $linkExpiryMinutes = (int) config('auth.passwords.users.expire', 60);
                $linkExpiryHours = (int) max(1, ceil($linkExpiryMinutes / 60));
                $loginUrl = (string) config('dmc.post_reset_password_redirect_url', 'https://www.djakarta-miningclub.com?modalloginopen=true');

                $send = new EmailSender();
                $send->subject = 'Djakarta Mining Club – Membership Approval Confirmation (ID: ' . $memberId . ')';
                $send->template = 'email.membership-approved';
                $send->data = [
                    'users_name' => $user->name ?? 'Member',
                    'member_id' => $memberId,
                    'registered_email' => $email,
                    'set_password_url' => $setPasswordUrl,
                    'link_expiry_hours' => $linkExpiryHours,
                    'login_url' => $loginUrl,
                ];
                $send->name = $user->name ?? 'Member';
                $send->from = env('EMAIL_SENDER');
                $send->name_sender = env('EMAIL_NAME');
                $send->to = $email;
                $send->sendEmail();
            } catch (\Throwable $e) {
                Log::warning('verifyMember: approval email failed for user ' . $id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Member verified dan data telah diimport ke Mailchimp.',
        ]);
    }

    public function declineMember(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->status_member = 'declined';
        $user->save();

        $email = strtolower(trim($user->email ?? ''));

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $send = new EmailSender();
                $send->subject = 'Update on Your Djakarta Mining Club Membership Application';
                $send->template = 'email.membership-declined';
                $send->data = [
                    'users_name' => $user->name ?? 'Applicant',
                ];
                $send->name         = $user->name ?? 'Applicant';
                $send->from         = env('EMAIL_SENDER');
                $send->name_sender  = env('EMAIL_NAME');
                $send->to           = $email;
                $send->sendEmail();
            } catch (\Throwable $e) {
                Log::warning('declineMember: decline email failed for user ' . $id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Membership application declined dan email notifikasi telah dikirim.',
        ]);
    }

    private function generateVerificationMemberId(User $user, ?Carbon $verifiedAt = null): string
    {
        $verifiedAt = $verifiedAt ? $verifiedAt->copy() : now();
        $datePart = $verifiedAt->format('Ymd');
        $monthPrefix = $verifiedAt->format('Ym');

        $maxSequence = 0;

        User::where('uname', 'like', $monthPrefix . '%')
            ->pluck('uname')
            ->each(function ($uname) use ($monthPrefix, &$maxSequence) {
                if (!is_string($uname)) {
                    return;
                }

                if (preg_match('/^' . preg_quote($monthPrefix, '/') . '\d{2}(\d{4})[A-Z0-9]*$/', $uname, $matches)) {
                    $sequence = (int) $matches[1];
                    if ($sequence > $maxSequence) {
                        $maxSequence = $sequence;
                    }
                }
            });

        $nextSequence = max(1, $maxSequence + 1);

        for ($sequence = $nextSequence; $sequence <= 9999; $sequence++) {
            $memberId = $datePart . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
        }

        $sequence = max(10000, $nextSequence);
        while (true) {
            $memberId = $datePart . (string) $sequence;
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
            $sequence++;
        }
    }

    private function memberIdExistsForOtherUser(string $memberId, User $user): bool
    {
        return User::where('uname', $memberId)
            ->where('id', '!=', $user->id)
            ->exists();
    }

    private function generateMemberIdFromUser(User $user): string
    {
        $datePart = Carbon::parse($user->created_at ?? now())->format('Ymd');
        $idPart = str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
        $memberId = $datePart . $idPart;

        $isTakenByOtherUser = User::where('uname', $memberId)
            ->where('id', '!=', $user->id)
            ->exists();

        if (!$isTakenByOtherUser) {
            return $memberId;
        }

        return $memberId . strtoupper(Str::random(2));
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'job_title'     => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'prefix'        => 'nullable|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'company_website' => 'nullable|string|max:255',
            'company_category' => 'nullable|string|max:255',
            'company_other' => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'portal_code'   => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:255',
            'prefix_office_number' => 'nullable|string|max:255',
            'office_number' => 'nullable|string|max:255',
            'full_office_number' => 'nullable|string|max:255',
            'status_member' => 'nullable|in:active,pending',
            'tier'          => 'nullable|in:reguler,black',
        ]);

        $user    = User::findOrFail($id);
        $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
        $company = null;

        if (!empty($profile->company_id)) {
            $company = CompanyModel::find($profile->company_id);
        }

        if (!$company) {
            $company = CompanyModel::where('users_id', $user->id)->first();
        }

        if (!$company) {
            $company = new CompanyModel();
            $company->users_id = $user->id;
        }

        $normalizeForCompare = static function ($value): string {
            if (is_null($value)) {
                return '';
            }

            return is_string($value) ? trim($value) : (string) $value;
        };

        $nullableString = static function ($value): ?string {
            if (is_null($value)) {
                return null;
            }

            $value = is_string($value) ? trim($value) : (string) $value;
            return $value === '' ? null : $value;
        };

        $nextStatusMember = $request->filled('status_member')
            ? (string) $request->input('status_member')
            : (string) ($user->status_member ?? '');
        $nextTier = $request->filled('tier')
            ? (string) $request->input('tier')
            : (string) ($user->tier ?? '');

        $watchUser    = ['name', 'email', 'status_member', 'tier'];
        $watchProfile = ['job_title', 'phone'];
        $watchCompany = [
            'prefix',
            'company_name',
            'company_website',
            'company_category',
            'company_other',
            'address',
            'city',
            'portal_code',
            'country',
            'prefix_office_number',
            'office_number',
            'full_office_number',
        ];

        $changes = [];
        $hasCompanyChanges = false;
        $shouldAutoVerifyCompany = false;
        $isFilled = static function ($value): bool {
            return !is_null($value) && (!is_string($value) || trim($value) !== '');
        };

        foreach ($watchUser as $field) {
            $old = $normalizeForCompare($user->$field ?? '');
            if ($field === 'status_member') {
                $new = $normalizeForCompare($nextStatusMember);
            } elseif ($field === 'tier') {
                $new = $normalizeForCompare($nextTier);
            } else {
                $new = $normalizeForCompare($request->input($field, ''));
            }
            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        foreach ($watchProfile as $field) {
            $old = $normalizeForCompare($profile->$field ?? '');
            $new = $normalizeForCompare($request->input($field, ''));
            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        foreach ($watchCompany as $field) {
            if (!$request->has($field)) {
                continue;
            }
            $old = $normalizeForCompare($company->$field ?? '');
            $new = $normalizeForCompare($request->input($field, ''));
            if ($old !== $new) {
                $hasCompanyChanges = true;
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        $candidateCompanyData = [];
        foreach ($watchCompany as $field) {
            if ($request->has($field)) {
                $candidateCompanyData[$field] = $nullableString($request->input($field));
            } else {
                $candidateCompanyData[$field] = $nullableString($company->{$field} ?? null);
            }
        }

        $requiredCompanyFields = [
            'prefix',
            'company_name',
            'company_website',
            'company_category',
            'address',
            'city',
            'portal_code',
            'prefix_office_number',
            'office_number',
            'full_office_number',
            'country',
        ];

        $isCompanyComplete = true;
        foreach ($requiredCompanyFields as $field) {
            if (!$isFilled($candidateCompanyData[$field] ?? null)) {
                $isCompanyComplete = false;
                break;
            }
        }

        if (($candidateCompanyData['company_category'] ?? null) === 'other' && !$isFilled($candidateCompanyData['company_other'] ?? null)) {
            $isCompanyComplete = false;
        }

        if (!(bool) ($company->is_verified ?? false) && $isCompanyComplete) {
            // Auto-verify hanya jika field company lengkap.
            $shouldAutoVerifyCompany = true;
        }

        if ($shouldAutoVerifyCompany) {
            $changes['is_verified'] = ['old' => (bool) ($company->is_verified ?? false) ? '1' : '0', 'new' => '1'];
        }

        if (empty($changes)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $user->name          = trim((string) $request->name);
        $user->email         = trim((string) $request->email);
        $user->status_member = $nextStatusMember;
        $user->tier          = $nextTier;
        $user->save();

        $shouldSaveCompany = $hasCompanyChanges || $shouldAutoVerifyCompany;
        if ($shouldSaveCompany && $company->exists) {
            $belongsToOtherUser = !empty($company->users_id) && (int) $company->users_id !== (int) $user->id;
            $sharedWithOtherUsers = ProfileModel::where('company_id', $company->id)
                ->where('users_id', '!=', $user->id)
                ->exists();

            if ($belongsToOtherUser || $sharedWithOtherUsers) {
                $company = $company->replicate();
                $company->users_id = $user->id;
            }
        }

        if ($shouldSaveCompany) {
            foreach ($watchCompany as $field) {
                if (!$request->has($field)) {
                    continue;
                }
                $company->{$field} = $nullableString($request->input($field));
            }

            if ($shouldAutoVerifyCompany) {
                $company->is_verified = true;
                $company->verified_at = now();
            }

            $company->users_id = $user->id;
            $company->save();
        }

        $phone = $nullableString($request->phone);
        $profile->job_title = $nullableString($request->job_title);
        $profile->phone     = $phone;
        $profile->fullphone = $phone;
        if (($hasCompanyChanges || $company->exists) && (int) ($company->id ?? 0) > 0) {
            $profile->company_id = $company->id;
        }
        $profile->users_id  = $user->id;
        $profile->save();

        $adminUser = auth()->user();
        DB::table('user_edit_logs')->insert([
            'user_id'    => $user->id,
            'admin_id'   => auth()->id(),
            'admin_name' => $adminUser ? $adminUser->name : null,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Admin edited user', [
            'admin'   => $adminUser ? $adminUser->name : null,
            'user_id' => $user->id,
            'email'   => $user->email,
            'changes' => $changes,
        ]);

        return response()->json(['success' => true, 'message' => 'Data user berhasil diperbarui.', 'changes' => $changes]);
    }

    public function editLogs(Request $request)
    {
        $criticalFields = ['company_name', 'company_category', 'company_other', 'prefix'];

        $query = UserEditLog::with('user')->orderByDesc('created_at');

        if ($request->source === 'self') {
            $query->whereNull('admin_id');
        } elseif ($request->source === 'admin') {
            $query->whereNotNull('admin_id');
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->critical === '1') {
            $query->where(function ($q) use ($criticalFields) {
                foreach ($criticalFields as $field) {
                    $q->orWhere('changes', 'like', '%"' . $field . '"%');
                }
            });
        }

        $logs = $query->paginate(50);

        $countSelfEdit    = UserEditLog::whereNull('admin_id')->count();
        $countAdminEdit   = UserEditLog::whereNotNull('admin_id')->count();
        $countUniqueUsers = UserEditLog::distinct('user_id')->count('user_id');
        $countCritical    = UserEditLog::whereNull('admin_id')
            ->where(function ($q) use ($criticalFields) {
                foreach ($criticalFields as $field) {
                    $q->orWhere('changes', 'like', '%"' . $field . '"%');
                }
            })->count();

        return view('admin.users.edit_logs', compact(
            'logs',
            'countSelfEdit',
            'countAdminEdit',
            'countUniqueUsers',
            'countCritical'
        ));
    }

    public function userLogs(Request $request, $id)
    {
        $logs = DB::table('user_edit_logs')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                $log->changes = json_decode($log->changes, true);
                return $log;
            });

        return response()->json($logs);
    }

    public function import(Request $request)
    {
        $request->validate([
            'uploaded_file' => 'required|file|mimes:xls,xlsx|max:20480', // 20MB
        ]);

        $file = $request->file('uploaded_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();

            $lastRow    = (int) $sheet->getHighestDataRow();
            $startRow   = 2; // header di row 1
            $success    = 0;
            $skipped    = 0;
            $errors     = 0;
            $errorRows  = [];

            // Helper kecil
            $clean = fn($v) => is_string($v) ? trim($v) : (is_null($v) ? null : $v);
            $validEmail = fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL);
            $normalizeHeader = function ($header) {
                $header = strtolower(trim((string) $header));
                if ($header === '') {
                    return '';
                }
                // Samakan format header: "Email Address" -> "email address"
                $header = preg_replace('/[^a-z0-9]+/i', ' ', $header);
                return trim(preg_replace('/\s+/', ' ', $header));
            };
            $nullIfPlaceholder = function ($value) use ($clean) {
                $value = $clean($value);
                if (!is_string($value)) {
                    return $value;
                }

                $normalized = strtolower(trim($value));
                if (in_array($normalized, ['-', '--', 'n/a', 'na', 'null', 'none'], true)) {
                    return null;
                }

                return $value;
            };
            $normPhone = function ($p) {
                if (!$p) return null;
                $p = preg_replace('/[^0-9+]/', '', (string)$p);
                // contoh normalisasi sederhana: leading 0 -> +62
                if (Str::startsWith($p, '0')) $p = '+62' . ltrim($p, '0');
                return $p;
            };
            $normUrl = function ($u) use ($clean) {
                $u = $clean($u);
                if (!$u) return null;
                // tambah https kalau user isi tanpa schema
                if (!Str::startsWith($u, ['http://', 'https://'])) {
                    $u = 'https://' . $u;
                }
                return $u;
            };

            // Build mapping header -> column letter (row 1)
            $highestColumn = $sheet->getHighestDataColumn();
            $headerRow     = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];
            $headerToCol   = [];
            foreach ($headerRow as $columnLetter => $headerValue) {
                $normalizedHeader = $normalizeHeader($headerValue);
                if ($normalizedHeader !== '') {
                    $headerToCol[$normalizedHeader] = $columnLetter;
                }
            }

            $getCellValue = function ($columnLetter, $row) use ($sheet, $clean) {
                $cell = $sheet->getCell($columnLetter . $row);
                $rawValue = $cell->getValue();

                // Untuk numeric (terutama nomor telepon), gunakan formatted value agar tidak scientific notation
                if (is_numeric($rawValue)) {
                    return $clean($cell->getFormattedValue());
                }

                return $clean($rawValue);
            };

            $valueFromAliases = function (int $row, array $aliases, ?string $fallbackColumn = null) use ($headerToCol, $normalizeHeader, $getCellValue) {
                foreach ($aliases as $alias) {
                    $normalizedAlias = $normalizeHeader($alias);
                    if (isset($headerToCol[$normalizedAlias])) {
                        return $getCellValue($headerToCol[$normalizedAlias], $row);
                    }
                }

                return $fallbackColumn ? $getCellValue($fallbackColumn, $row) : null;
            };

            DB::beginTransaction();

            for ($row = $startRow; $row <= $lastRow; $row++) {
                try {
                    // Mapping berdasarkan header template, fallback ke posisi lama agar tetap backward-compatible
                    $name          = $valueFromAliases($row, ['Name'], 'B');
                    $jobTitle      = $valueFromAliases($row, ['Job title', 'Job Title'], 'C');
                    $companyName   = $valueFromAliases($row, ['Company name', 'Company Name'], 'A');
                    $email         = strtolower((string) $valueFromAliases($row, ['Email Address', 'Email'], 'E'));
                    $phoneRaw      = $valueFromAliases($row, ['Mobile Phone', 'Phone', 'Phone Number'], 'D');
                    $officeNumber  = $valueFromAliases($row, ['Office Number', 'Full Office Number'], 'L');
                    $address       = $valueFromAliases($row, ['Office Address', 'Address'], 'I');
                    $companyWeb    = $normUrl($valueFromAliases($row, ['Website', 'Company Website'], 'F'));
                    $companyCat    = $valueFromAliases($row, ['Category', 'Company Category'], 'G');
                    $companyOther  = $valueFromAliases($row, ['Company Other', 'Other Category'], 'H');
                    $city          = $valueFromAliases($row, ['City'], 'J');
                    $portalCode    = $valueFromAliases($row, ['Portal Code', 'Postal Code', 'Zip Code'], 'K');
                    $registerAs    = $nullIfPlaceholder($valueFromAliases($row, ['Register As', 'Source'], 'M')); // optional

                    // skip baris kosong total
                    if (!$email && !$name && !$companyName) {
                        $skipped++;
                        continue;
                    }

                    // wajib email valid
                    if (!$email || !$validEmail($email)) {
                        $errors++;
                        $errorRows[] = "Row {$row}: email invalid/empty ({$email})";
                        continue;
                    }

                    // upsert User
                    /** @var \App\Models\User $user */
                    $user = \App\Models\User::firstOrNew(['email' => $email]);
                    $user->name     = $name ?: $user->name ?: '(no name)';
                    $user->isStatus = 'Active';
                    if ($registerAs) {
                        $user->source = $registerAs;
                    }
                    $user->save();

                    // upsert Company (by users_id)
                    /** @var \App\Models\CompanyModel $company */
                    $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
                    if ($companyName)  $company->company_name = $companyName;
                    if ($companyWeb)   $company->company_website = $companyWeb;
                    if ($companyCat)   $company->company_category = $companyCat;
                    if ($companyOther) $company->company_other = $companyOther;
                    if ($address)      $company->address = $address;
                    if ($city)         $company->city = $city;
                    if ($portalCode)   $company->portal_code = $portalCode;
                    if ($officeNumber) {
                        $company->office_number = $officeNumber;
                        $company->full_office_number = $officeNumber;
                    }
                    $company->save();

                    // upsert Profile (by users_id)
                    /** @var \App\Models\ProfileModel $profile */
                    $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
                    $phoneNormalized = $normPhone($phoneRaw);
                    if ($phoneNormalized) {
                        $profile->fullphone  = $phoneNormalized;
                        $profile->phone      = $phoneNormalized;
                    }
                    if ($jobTitle) {
                        $profile->job_title  = $jobTitle;
                    }
                    $profile->users_id   = $user->id;
                    $profile->company_id = $company->id;
                    $profile->save();

                    $success++;
                } catch (\Throwable $rowEx) {
                    $errors++;
                    $errorRows[] = "Row {$row}: " . $rowEx->getMessage();
                    // lanjut baris berikutnya
                }
            }

            DB::commit();

            // log detail error ke laravel.log biar ga numpuk di flash message
            if (!empty($errorRows)) {
                Log::warning('Import XLS - partial errors', ['errors' => $errorRows]);
            }

            return back()->with('success', "Import selesai: {$success} berhasil, {$skipped} dilewati (kosong), {$errors} error. Cek log untuk detail error.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import XLS gagal total', ['exception' => $e]);
            return back()->withErrors('Gagal mengimpor data. Pesan: ' . $e->getMessage());
        }
    }



    public function mailchimpContactCount()
    {
        $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
        $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');
        $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);

        if (!$apiKey || !$listId || !$server) {
            return response()->json(['success' => false, 'count' => null, 'message' => 'Mailchimp belum dikonfigurasi.']);
        }

        try {
            $resp = Http::withBasicAuth('anystring', $apiKey)
                ->timeout(10)
                ->get("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}", [
                    'fields' => 'stats.member_count,stats.unsubscribe_count,stats.cleaned_count',
                ]);

            if (!$resp->successful()) {
                return response()->json(['success' => false, 'count' => null, 'message' => 'Gagal mengambil data Mailchimp.']);
            }

            $stats = $resp->json('stats');
            return response()->json([
                'success'      => true,
                'count'        => $stats['member_count'] ?? 0,
                'unsubscribed' => $stats['unsubscribe_count'] ?? 0,
                'cleaned'      => $stats['cleaned_count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('mailchimpContactCount failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'count' => null, 'message' => $e->getMessage()]);
        }
    }

    public function member()
    {

        $list = MemberModel::orderBy('created_at', 'desc')->get();
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

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email tidak valid.'], 422);
        }

        $user    = $userId ? User::find($userId) : User::where('email', $email)->first();
        $company = $user ? CompanyModel::where('users_id', $user->id)->first()
            : CompanyModel::where('company_email', $email)->first();
        $profile = $user ? ProfileModel::where('users_id', $user->id)->first()
            : ProfileModel::where('email', $email)->first();

        $merge = [];
        if (!empty($user->name))               $merge['FNAME']    = $user->name;
        if (!empty($company->company_name))    $merge['MMERGE5']  = $company->company_name;
        if (!empty($profile->job_title))       $merge['MMERGE7']  = $profile->job_title;
        if (!empty($company->explore ?? $company->cci)) $merge['MMERGE12'] = $company->explore ?? $company->cci;
        if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;
        $merge['MMERGE11'] = now()->format('m/d/Y');

        $phone = is_string($profile->fullphone ?? $profile->phone ?? null) ? trim($profile->fullphone ?? $profile->phone) : null;
        if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', $phone)) {
            $merge['MERGE4'] = $phone;
        }

        $apiKey = config('newsletter.apiKey');
        $listId = config('newsletter.lists.subscribers.id');
        $server = explode('-', $apiKey)[1] ?? null;

        if (!$apiKey || !$server || !$listId) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi Mailchimp belum lengkap.'], 500);
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth('anystring', $apiKey)
                ->timeout(20)
                ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/" . md5($email), [
                    'email_address' => $email,
                    'status_if_new' => 'subscribed',
                    'status'        => 'subscribed',
                    'merge_fields'  => $merge,
                ]);

            if (!$resp->successful()) {
                $json   = $resp->json();
                $detail = $json['detail'] ?? 'Gagal impor.';
                if (!empty($json['errors'])) {
                    $detail .= ' — ' . collect($json['errors'])->map(fn($e) => ($e['field'] ?? '') . ': ' . ($e['message'] ?? ''))->implode(' | ');
                }
                return response()->json(['success' => false, 'message' => $resp->status() . ': ' . $detail], 400);
            }

            return response()->json(['success' => true, 'message' => 'Berhasil diimpor ke Mailchimp.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    public function export(Request $request, $id)
    {
        $m = MemberModel::findOrFail($id); // <- hanya baca dari MemberModel

        // validasi minimal yang memang ada di MemberModel
        $email       = strtolower(trim((string) $m->email));
        $name        = trim((string)($m->name ?? ''));
        $companyName = trim((string)($m->company_name ?? ''));
        if ($email === '')   return back()->with('error', 'Export gagal: email kosong.');
        if ($companyName === '') return back()->with('error', 'Export gagal: company_name kosong.');

        DB::beginTransaction();
        try {
            // ===== 1) USERS (key: email) =====
            $existingUser = User::where('email', $email)->first();
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'         => $name ?: $email,
                    'password'     => $existingUser ? $existingUser->password : null,
                    'verify_email' => $existingUser ? ($existingUser->verify_email ?? 'verified') : 'verified',
                    'verify_phone' => $existingUser ? ($existingUser->verify_phone ?? null) : null,
                    'otp'          => null,
                    'isStatus'     => $existingUser ? ($existingUser->isStatus ?? 'Active') : 'Active',
                    'qrcode'       => $existingUser ? $existingUser->qrcode : null,
                ]
            );

            if (empty($user->uname)) {
                $user->uname = $this->generateMemberIdFromUser($user);
                $user->save();
            }

            // ===== 2) COMPANY (key: company_name + optional company_website) =====
            $companyWebsite = trim((string)($m->company_website ?? ''));
            $companyQuery = CompanyModel::where('company_name', $companyName);
            if ($companyWebsite !== '') $companyQuery->where('company_website', $companyWebsite);
            $existingCompany = $companyQuery->first();

            $companyPayload = [
                'prefix'               => $m->prefix ?: null,
                'company_name'         => $companyName,
                'company_website'      => $companyWebsite ?: null,
                'company_category'     => $m->company_category ?: null,
                'company_other'        => $m->company_other ?: null,
                'address'              => $m->address ?: null,
                'city'                 => $m->city ?: null,
                'portal_code'          => $m->portal_code ?: null,
                'prefix_office_number' => $m->prefix_office_number ?: null,
                'office_number'        => $m->office_number ?: null,
                'full_office_number'   => $m->full_office_number ?: null,
                'country'              => $m->country ?: null,
                'cci'                  => (int)($m->cci ?? 0),
                'explore'              => (int)($m->explore ?? 0),
                'users_id'             => $user->id, // owner
            ];

            if ($existingCompany) {
                $existingCompany->update($companyPayload);
                $company = $existingCompany;
            } else {
                $company = CompanyModel::create($companyPayload);
            }

            // ===== 3) PROFILE (key: users_id) =====
            $existingProfile = ProfileModel::where('users_id', $user->id)->first();
            $profilePayload = [
                'prefix_phone' => $m->prefix_phone ?: ($existingProfile ? $existingProfile->prefix_phone : null),
                'phone'        => $m->phone ?: ($existingProfile ? $existingProfile->phone : null),
                'fullphone'    => $m->fullphone ?: ($existingProfile ? $existingProfile->fullphone : null),
                'image'        => $existingProfile ? $existingProfile->image : null,
                'job_title'    => $m->job_title ?: ($existingProfile ? $existingProfile->job_title : null),
                'company_id'   => $company->id,
                'users_id'     => $user->id,
            ];

            if ($existingProfile) {
                $existingProfile->update($profilePayload);
                $profile = $existingProfile;
            } else {
                $profile = ProfileModel::create($profilePayload);
            }

            // // ===== 4) MAILCHIMP INTEGRATION =====
            // NewsletterFacade::subscribeOrUpdate($email, [
            //     'FNAME'    => $user->name,
            //     'MERGE3'   => $company->address,
            //     'PHONE'    => $profile->phone,
            //     'MMERGE5'  => $company->company_name,
            //     'MMERGE6'  => $company->company_category,
            //     'MMERGE8'  => $profile->job_title,
            //     'MMERGE10' => now(),
            //     'MMERGE11' => $company->office_number,
            // ]);

            // // Tambah tag penanda sumber registrasi
            // $this->mcAddTags($user->email, [
            //     'Backend Membership'
            // ]);
            $m->update(['exported_at' => now()]);

            DB::commit();
            return back()->with('success', "Export OK → user:{$user->id}, company:{$company->id}, profile:{$profile->id}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Export gagal: ' . $e->getMessage());
        }
    }
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
}
