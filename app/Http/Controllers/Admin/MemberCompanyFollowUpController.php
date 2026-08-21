<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use App\Models\Company\CompanyModel;
use App\Models\MemberCompanyFollowUp;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\VerificationLog;
use App\Services\Membership\MemberVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MemberCompanyFollowUpController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $status = $request->get('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);
        $q      = trim((string) $request->get('q', ''));
        $picId  = $request->get('pic_id');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = MemberCompanyFollowUp::with(['user.profile.company'])->orderBy('created_at', 'desc');

        if ($status === MemberCompanyFollowUp::STATUS_VERIFIED) {
            $query->where('status', MemberCompanyFollowUp::STATUS_VERIFIED);
        } elseif ($status !== 'all') {
            $status = MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP;
            $query->where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);
        }

        if ($q !== '') {
            $query->where(function ($outer) use ($q) {
                $outer->where('previous_company_name', 'like', "%{$q}%")
                    ->orWhere('new_company_name', 'like', "%{$q}%")
                    ->orWhere('previous_job_title', 'like', "%{$q}%")
                    ->orWhere('new_job_title', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        if ($picId) {
            $query->where('flagged_by_id', $picId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $list = $query->get();

        $countNeedsFollowUp = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)->count();
        $countVerified      = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_VERIFIED)->count();

        // Datalist suggestions for both the Flag and Edit & Verify modals — only
        // already-verified companies, so admins are steered toward matching an
        // existing verified record (and getting its details autofilled) rather
        // than towards an unverified/duplicate name. Typing a brand-new company
        // that isn't verified yet still works fine, it's just not suggested.
        $companyNames = CompanyModel::where('is_verified', true)
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');

        $pics = CmsUser::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.member_follow_ups.index', compact(
            'list',
            'status',
            'q',
            'picId',
            'dateFrom',
            'dateTo',
            'countNeedsFollowUp',
            'countVerified',
            'companyNames',
            'pics'
        ));
    }

    /**
     * Look up one already-verified company's full details by name — lets the
     * Edit & Verify modal auto-fill the rest of the company fields when the
     * typed name matches one, instead of the admin re-typing data that's
     * already on file. On-demand (not bundled into index()) so a page with
     * ~1,500 verified companies doesn't ship that whole dataset on every load.
     *
     * Where more than one verified company shares a name (rare, but real —
     * e.g. same-named companies that are genuinely separate entities), the
     * most recently verified one wins; it's a convenience autofill the admin
     * can still override, not an authority.
     */
    public function lookupVerifiedCompany(Request $request)
    {
        $name = strtolower(trim((string) $request->get('name')));

        if ($name === '') {
            return response()->json(['found' => false]);
        }

        $company = CompanyModel::where('is_verified', true)
            ->whereRaw('LOWER(TRIM(company_name)) = ?', [$name])
            ->orderBy('verified_at', 'desc')
            ->first([
                'prefix', 'company_website', 'company_category',
                'company_other', 'address', 'city', 'portal_code', 'country',
                'prefix_office_number', 'office_number', 'full_office_number',
            ]);

        if (!$company) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true, 'data' => $company]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'new_company_name' => 'required|string|max:255',
            'new_job_title'    => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $user = User::with('profile.company')->findOrFail($request->user_id);
        $previousCompanyName = optional(optional($user->profile)->company)->company_name;
        $previousJobTitle    = optional($user->profile)->job_title;

        $followUp = MemberCompanyFollowUp::firstOrNew([
            'user_id' => $user->id,
            'status'  => MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP,
        ]);

        $admin = auth()->user();
        $followUp->previous_company_name = $previousCompanyName;
        $followUp->new_company_name      = $request->new_company_name;
        // Guarded: these two columns ship in this same change, but the
        // migration adding them isn't necessarily run yet (never auto-run
        // against this DB) — skip setting them rather than erroring on an
        // unknown column, same defensive pattern used elsewhere this session.
        if (Schema::hasColumn('member_company_follow_ups', 'previous_job_title')) {
            $followUp->previous_job_title = $previousJobTitle;
            $followUp->new_job_title      = $request->new_job_title;
        }
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
     * Edit a follow-up member's data in place — same fields and field-diff/
     * user_edit_logs pattern as UsersController::updateUser() (Members DMC),
     * so both edit paths behave identically instead of maintaining two
     * separate implementations. No duplicate account is created: the existing
     * User/Profile/Company records are updated directly.
     *
     * Completing this edit is treated as the manual "2-Step Verified"
     * confirmation (no separate click needed), and closes out the follow-up.
     */
    public function update(Request $request, $id)
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
        ]);

        $followUp = MemberCompanyFollowUp::with('user.profile')->findOrFail($id);
        $user     = $followUp->user;

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 422);
        }

        $newEmail = strtolower(trim($request->email));
        if ($newEmail !== strtolower(trim($user->email ?? ''))) {
            $emailExists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'Email sudah digunakan oleh akun lain.'], 422);
            }
        }

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

        $isFilled = static function ($value): bool {
            return !is_null($value) && (!is_string($value) || trim($value) !== '');
        };

        $watchUser    = ['name', 'email'];
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

        foreach ($watchUser as $field) {
            $old = $normalizeForCompare($user->$field ?? '');
            $new = $field === 'email' ? $normalizeForCompare($newEmail) : $normalizeForCompare($request->input($field, ''));
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

        // Same auto-verify-company-if-complete rule as updateUser(), for parity.
        $candidateCompanyData = [];
        foreach ($watchCompany as $field) {
            $candidateCompanyData[$field] = $request->has($field)
                ? $nullableString($request->input($field))
                : $nullableString($company->{$field} ?? null);
        }

        $requiredCompanyFields = [
            'prefix', 'company_name', 'company_website', 'company_category',
            'address', 'city', 'portal_code',
            'prefix_office_number', 'office_number', 'full_office_number', 'country',
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

        $shouldAutoVerifyCompany = !(bool) ($company->is_verified ?? false) && $isCompanyComplete;
        if ($shouldAutoVerifyCompany) {
            $changes['is_verified'] = ['old' => (bool) ($company->is_verified ?? false) ? '1' : '0', 'new' => '1'];
        }

        if (empty($changes)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $oldEmail      = (string) ($user->email ?? '');
        $currentStatus = strtolower((string) ($user->status_member ?? ''));
        $wasActive     = $currentStatus === 'active';
        // Only auto-approve a status that was never decided on (pending/blank).
        // 'deactivated'/'declined' are deliberate admin decisions elsewhere —
        // a follow-up data edit here must never silently reverse those.
        $shouldAutoApprove = !$wasActive && !in_array($currentStatus, ['deactivated', 'declined'], true);

        $user->name  = trim((string) $request->name);
        $user->email = $newEmail;

        // Completing an edit here IS the manual 2-Step confirmation — admin has
        // just reviewed and corrected the member's real info, no separate click
        // needed on Members DMC.
        $admin = auth()->user();
        $user->two_step_verified    = true;
        $user->two_step_verified_at = now();
        $user->two_step_verified_by = $admin ? $admin->name : 'Staff';

        if (!$shouldAutoApprove) {
            $user->save();
        } else {
            // Not an approved member yet (e.g. flagged for follow-up before ever
            // being verified) — completing this edit also admits them, using the
            // same status_member/uname/QR core the manual Verify flow uses.
            // Otherwise they'd end up "2-Step" but stuck showing Pending, since
            // the 2-Step badge on Members DMC only renders once status_member is
            // active. activate() saves $user itself (incl. the fields above).
            // No approval email / lead auto-create here — those belong to
            // first-time admission, not a data-correction pass.
            $user->verified_at = now();
            app(MemberVerificationService::class)->activate($user);

            // Close out any open SLA review timer too, same as the manual Verify
            // flow — mirrors UsersController::finishVerificationLog(), duplicated
            // here since that one is private to that controller.
            if (Schema::hasTable('verification_logs')) {
                try {
                    $log = VerificationLog::open()->where('user_id', $user->id)->latest('started_at')->first();
                    if (!$log) {
                        $log = new VerificationLog([
                            'user_id'         => $user->id,
                            'started_at'      => $user->verified_at,
                            'started_by_id'   => auth()->id(),
                            'started_by_name' => $admin ? $admin->name : 'Staff',
                        ]);
                    }
                    $log->finished_at      = $user->verified_at;
                    $log->finished_by_id   = auth()->id();
                    $log->finished_by_name = $admin ? $admin->name : 'Staff';
                    $log->result           = VerificationLog::RESULT_ACTIVE;
                    $log->save();
                } catch (\Throwable $e) {
                    Log::warning('MemberCompanyFollowUp update: verification log close failed for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        }

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
        $profile->users_id = $user->id;
        $profile->save();

        DB::table('user_edit_logs')->insert([
            'user_id'    => $user->id,
            'admin_id'   => auth()->id(),
            'admin_name' => $admin ? $admin->name : null,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Close out the follow-up.
        $followUp->status           = MemberCompanyFollowUp::STATUS_VERIFIED;
        $followUp->verified_by_id   = auth()->id();
        $followUp->verified_by_name = $admin ? $admin->name : null;
        $followUp->verified_at      = now();
        $followUp->save();

        $message = $user->name . ' berhasil diperbarui & ditandai Verified.'
            . ($shouldAutoApprove ? ' Status member otomatis di-approve jadi Active.' : '');

        if (isset($changes['email'])) {
            $mailchimpResult = app(MemberVerificationService::class)->changeMailchimpEmail($user, $oldEmail);

            if ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_RENAMED) {
                $message .= ' Email di Mailchimp ikut dipindahkan.';
            } elseif ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_SWAPPED) {
                $message .= ' Mailchimp: email lama di-archive, email baru di-subscribe.';
            } elseif ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_FAILED) {
                $message .= ' (Mailchimp: gagal memindahkan email — perlu dicek manual.)';
            }
        }

        return response()->json(['success' => true, 'message' => $message, 'changes' => $changes]);
    }
}
