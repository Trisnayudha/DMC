<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberCompanyFollowUp;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Support\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class MemberCompanyFollowUpController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $status = $request->get('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);

        $query = MemberCompanyFollowUp::with(['user.profile'])->orderBy('created_at', 'desc');

        if ($status === MemberCompanyFollowUp::STATUS_VERIFIED) {
            $query->where('status', MemberCompanyFollowUp::STATUS_VERIFIED);
        } elseif ($status !== 'all') {
            $status = MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP;
            $query->where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);
        }

        $list = $query->get();

        $countNeedsFollowUp = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)->count();
        $countVerified      = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_VERIFIED)->count();

        $companyNames = CompanyModel::whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');

        return view('admin.member_follow_ups.index', compact(
            'list',
            'status',
            'countNeedsFollowUp',
            'countVerified',
            'companyNames'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'new_company_name' => 'required|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $user = User::with('profile.company')->findOrFail($request->user_id);
        $previousCompanyName = optional(optional($user->profile)->company)->company_name;

        $followUp = MemberCompanyFollowUp::firstOrNew([
            'user_id' => $user->id,
            'status'  => MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP,
        ]);

        $admin = auth()->user();
        $followUp->previous_company_name = $previousCompanyName;
        $followUp->new_company_name      = $request->new_company_name;
        $followUp->notes                 = $request->notes;
        $followUp->status                = MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP;
        $followUp->flagged_by_id         = auth()->id();
        $followUp->flagged_by_name       = $admin ? $admin->name : null;
        $followUp->save();

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil ditandai perlu follow up.',
        ]);
    }

    /**
     * Verify a company follow-up:
     *  1. Deactivate old user account & null out their phone (unique constraint)
     *  2. Create new User with new email (or old email) + new company/job_title
     *  3. Copy profile & company data from old account
     *  4. Run the full verify flow (uname, QR code, Mailchimp, approval email)
     *  5. Mark the follow-up record as verified
     */
    public function markVerified(Request $request, $id)
    {
        $request->validate([
            'new_company_name' => 'required|string|max:255',
            'new_job_title'    => 'nullable|string|max:255',
            'new_email'        => 'nullable|email|max:255',
        ]);

        $followUp = MemberCompanyFollowUp::with('user.profile')->findOrFail($id);
        $oldUser  = $followUp->user;

        if (!$oldUser) {
            return response()->json(['success' => false, 'message' => 'User lama tidak ditemukan.'], 422);
        }

        $newCompanyName = trim($request->new_company_name);
        $newJobTitle    = $request->filled('new_job_title') ? trim($request->new_job_title) : null;
        $newEmail       = $request->filled('new_email')     ? strtolower(trim($request->new_email)) : strtolower(trim($oldUser->email ?? ''));

        // Check email uniqueness (allow same as old user)
        if ($newEmail !== strtolower(trim($oldUser->email ?? ''))) {
            $emailExists = User::where('email', $newEmail)
                ->where('id', '!=', $oldUser->id)
                ->exists();
            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'Email baru sudah digunakan oleh akun lain.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            // ── 1. Load old profile & company ────────────────────────────
            $oldProfile = ProfileModel::where('users_id', $oldUser->id)->first();
            $oldCompany = CompanyModel::where('users_id', $oldUser->id)->first();

            // ── 2. Capture phone BEFORE null-out (to copy to new profile) ─
            $oldPhone     = $oldProfile?->phone;
            $oldFullPhone = $oldProfile?->fullphone;
            $oldPrefixPhone = $oldProfile?->prefix_phone;

            // ── 2. Null-out phone on old profile FIRST (unique constraint) ─
            if ($oldProfile) {
                $oldProfile->phone     = null;
                $oldProfile->fullphone = null;
                $oldProfile->save();
            }

            // ── 3. Deactivate old user ────────────────────────────────────
            $oldUser->status_member = 'deactivated';
            $oldUser->save();

            // ── 4. Create new User ────────────────────────────────────────
            $newUser = new User();
            $newUser->name          = $oldUser->name;
            $newUser->email         = $newEmail;
            $newUser->verify_email  = $oldUser->verify_email;
            $newUser->verify_phone  = $oldUser->verify_phone;
            $newUser->isStatus      = $oldUser->isStatus;
            $newUser->source        = $oldUser->source;
            $newUser->status_member = 'pending'; // will be set to active below
            $newUser->tier          = $oldUser->tier;
            // password intentionally NOT copied — reset via email
            $newUser->save();

            // ── 5. Run verify flow on new user ────────────────────────────
            $verifiedAt = now();
            $newUser->status_member = 'active';
            $newUser->verified_at   = $verifiedAt;
            $newUser->uname         = $this->generateMemberId($newUser, $verifiedAt);

            try {
                $qrImage  = QrCode::format('png')->size(300)->errorCorrection('H')->generate($newUser->uname);
                $fileName = 'img-verify-' . $newUser->id . '-' . $verifiedAt->timestamp . '.png';
                Storage::disk('local')->put('/public/uploads/qr-code/' . $fileName, $qrImage);
                $newUser->qrcode = '/storage/uploads/qr-code/' . $fileName;
            } catch (\Throwable $e) {
                Log::warning('markVerified: QR generation failed for new user: ' . $e->getMessage());
            }

            $newUser->save();

            // ── 6. Create new Profile (with original phone transferred) ────
            $newProfile = new ProfileModel();
            $newProfile->users_id     = $newUser->id;
            $newProfile->prefix_phone = $oldPrefixPhone;
            $newProfile->phone        = $oldPhone;          // transferred from old
            $newProfile->fullphone    = $oldFullPhone;      // transferred from old
            $newProfile->image        = $oldProfile?->image;
            $newProfile->job_title    = $newJobTitle ?? $oldProfile?->job_title;
            $newProfile->newsletter   = $oldProfile?->newsletter;
            $newProfile->wa_updates   = $oldProfile?->wa_updates;
            // company_id will be set after new company is created
            $newProfile->save();

            // ── 7. Create new Company ─────────────────────────────────────
            $newCompany = new CompanyModel();
            $newCompany->users_id             = $newUser->id;
            $newCompany->company_name         = $newCompanyName;
            $newCompany->prefix               = $oldCompany?->prefix;
            $newCompany->company_website      = $oldCompany?->company_website;
            $newCompany->company_category     = $oldCompany?->company_category;
            $newCompany->company_other        = $oldCompany?->company_other;
            $newCompany->address              = $oldCompany?->address;
            $newCompany->city                 = $oldCompany?->city;
            $newCompany->portal_code          = $oldCompany?->portal_code;
            $newCompany->prefix_office_number = $oldCompany?->prefix_office_number;
            $newCompany->office_number        = $oldCompany?->office_number;
            $newCompany->full_office_number   = $oldCompany?->full_office_number;
            $newCompany->country              = $oldCompany?->country;
            $newCompany->is_verified          = true;
            $newCompany->verified_at          = $verifiedAt;
            $newCompany->explore              = $oldCompany?->explore;
            $newCompany->save();

            // Link profile to new company
            $newProfile->company_id = $newCompany->id;
            $newProfile->save();

            // ── 8. Mark follow-up as verified ─────────────────────────────
            $admin = auth()->user();
            $followUp->status          = MemberCompanyFollowUp::STATUS_VERIFIED;
            $followUp->verified_by_id  = auth()->id();
            $followUp->verified_by_name = $admin ? $admin->name : null;
            $followUp->verified_at     = $verifiedAt;
            $followUp->save();

            DB::commit();

            // ── 9. Post-commit: Mailchimp + approval email ─────────────────
            $this->syncToMailchimp($newUser, $newProfile, $newCompany);
            $this->sendApprovalEmail($newUser, $newEmail);

            return response()->json([
                'success' => true,
                'message' => $oldUser->name . ' — akun lama dinonaktifkan, akun baru dengan company "' . $newCompanyName . '" berhasil dibuat & diverifikasi.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('markVerified follow-up failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────────

    private function generateMemberId(User $user, Carbon $verifiedAt): string
    {
        $datePart    = $verifiedAt->format('Ymd');
        $monthPrefix = $verifiedAt->format('Ym');
        $maxSequence = 0;

        User::where('uname', 'like', $monthPrefix . '%')
            ->pluck('uname')
            ->each(function ($uname) use ($monthPrefix, &$maxSequence) {
                if (!is_string($uname)) return;
                if (preg_match('/^' . preg_quote($monthPrefix, '/') . '\d{2}(\d{4})[A-Z0-9]*$/', $uname, $m)) {
                    $seq = (int) $m[1];
                    if ($seq > $maxSequence) $maxSequence = $seq;
                }
            });

        $next = max(1, $maxSequence + 1);
        for ($seq = $next; $seq <= 9999; $seq++) {
            $memberId = $datePart . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            if (!User::where('uname', $memberId)->where('id', '!=', $user->id)->exists()) {
                return $memberId;
            }
        }

        $seq = max(10000, $next);
        while (true) {
            $memberId = $datePart . (string) $seq;
            if (!User::where('uname', $memberId)->where('id', '!=', $user->id)->exists()) {
                return $memberId;
            }
            $seq++;
        }
    }

    private function syncToMailchimp(User $user, ?ProfileModel $profile, ?CompanyModel $company): void
    {
        $email = strtolower(trim($user->email ?? ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;

        try {
            $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
            $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
            $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

            if (!$apiKey || !$server || !$listId) return;

            $merge = [];
            if (!empty($user->name))              $merge['FNAME']    = $user->name;
            if (!empty($company?->company_name))  $merge['MMERGE5']  = $company->company_name;
            if (!empty($profile?->job_title))     $merge['MMERGE7']  = $profile->job_title;
            if (!empty($company?->company_website)) $merge['MMERGE13'] = $company->company_website;
            $merge['MMERGE11'] = now()->format('m/d/Y');

            $phone = $profile?->fullphone ?? $profile?->phone ?? null;
            if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', trim((string) $phone))) {
                $merge['MERGE4'] = trim((string) $phone);
            }

            Http::withBasicAuth('anystring', $apiKey)
                ->timeout(20)
                ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/" . md5($email), [
                    'email_address' => $email,
                    'status_if_new' => 'subscribed',
                    'status'        => 'subscribed',
                    'merge_fields'  => $merge,
                ]);
        } catch (\Throwable $e) {
            Log::warning('markVerified: Mailchimp sync failed: ' . $e->getMessage());
        }
    }

    private function sendApprovalEmail(User $user, string $email): void
    {
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;

        try {
            $setPasswordUrl = null;
            if (empty($user->password)) {
                $token = Password::broker()->createToken($user);
                $setPasswordUrl = route('password.reset', ['token' => $token, 'email' => $email]);
            }

            $linkExpiryHours = (int) max(1, ceil((int) config('auth.passwords.users.expire', 60) / 60));
            $loginUrl = (string) config('dmc.post_reset_password_redirect_url', 'https://www.djakarta-miningclub.com?modalloginopen=true');

            $send = new EmailSender();
            $send->subject     = 'Djakarta Mining Club – Membership Approval Confirmation (ID: ' . $user->uname . ')';
            $send->template    = 'email.membership-approved';
            $send->data        = [
                'users_name'       => $user->name ?? 'Member',
                'member_id'        => $user->uname,
                'registered_email' => $email,
                'set_password_url' => $setPasswordUrl,
                'link_expiry_hours' => $linkExpiryHours,
                'login_url'        => $loginUrl,
            ];
            $send->name        = $user->name ?? 'Member';
            $send->from        = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to          = $email;
            $send->sendEmail();
        } catch (\Throwable $e) {
            Log::warning('markVerified: approval email failed: ' . $e->getMessage());
        }
    }
}
