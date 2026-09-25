<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\PartnershipEvent\PartnershipEventVisitor;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\VisitModel;
use App\Services\GiveawayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FormMemberController extends Controller
{
    public function index()
    {
        return view('FormMember.index');
    }
    public function test()
    {
        // dd('test');
        return view('email.membership');
        // $send = new EmailSender();
        // $send->subject = "Membership";
        // $send->template = "email.test";
        // $send->data = [
        //     "name" => 'Nama',
        //     'email' => 'test@gmail.com',

        // ];
        // $send->from = env('EMAIL_SENDER');
        // $send->name_sender = env('EMAIL_NAME');
        // $send->to = 'yudha@indonesiaminer.com';
        // $send->sendEmail();
        // dd($send);
    }
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'name'         => 'required',
            'phone'        => 'required',
            'email'        => 'required|email',
            'job_title'    => 'required',
            'city'         => 'required',
            'country'      => 'required',
            'newsletter'   => 'required',
        ]);

        $email        = strtolower(trim($request->email));
        $countryPhone = $request->country_phone ?? '62';
        $phone        = $request->phone;
        $fullphone    = $countryPhone . ltrim($phone, '0');

        $countryPhoneOffice = $request->country_phone_office ?? '62';
        $officeNumber       = $request->office_number;
        $fullOfficeNumber   = $officeNumber ? $countryPhoneOffice . ltrim($officeNumber, '0') : null;

        $companyCategory = $request->company_category;
        if (in_array($companyCategory, ['other', 'Other'])) {
            $companyCategory = $request->company_other ?: $companyCategory;
        }

        $companyName = $request->prefix
            ? $request->company_name . ', ' . $request->prefix
            : $request->company_name;

        // Duplicate checks against User + Profile (same as API registerWeb)
        $userByEmail    = User::where('email', $email)->first();
        $profileByPhone = ProfileModel::where(function ($q) use ($phone, $fullphone) {
            $q->where('phone', $phone)->orWhere('fullphone', $fullphone);
        })->first();

        if ($userByEmail && !$this->isProvisionalUser($userByEmail)) {
            return redirect()->back()
                ->withErrors(['email' => 'This email is already registered. Please log in.'])
                ->withInput();
        }

        if ($profileByPhone && !is_null(optional($profileByPhone->user)->verify_phone)) {
            return redirect()->back()
                ->withErrors(['phone' => 'This phone number is already registered.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'          => $request->name,
                'isStatus'      => 'Active',
                'status_member' => 'pending',
                'source'        => 'Website',
                'hear'          => $request->source ?? optional($userByEmail)->hear,
            ];

            if ($userByEmail && $this->isProvisionalUser($userByEmail)) {
                $user = $userByEmail;
                $user->update($userData);
            } else {
                $user = User::create(array_merge($userData, ['email' => $email, 'password' => null]));
                $user->assignRole('guest');
            }

            $company = CompanyModel::updateOrCreate(
                ['users_id' => $user->id],
                [
                    'company_name'         => $companyName,
                    'company_website'      => $request->company_website,
                    'company_category'     => $companyCategory,
                    'company_other'        => $request->company_other,
                    'city'                 => $request->city,
                    'country'              => $request->country,
                    'portal_code'          => $request->portal_code,
                    'prefix_office_number' => $countryPhoneOffice,
                    'office_number'        => $officeNumber,
                    'full_office_number'   => $fullOfficeNumber,
                    'explore'              => $request->explore ?? '',
                ]
            );

            ProfileModel::updateOrCreate(
                ['users_id' => $user->id],
                [
                    'prefix_phone' => $countryPhone,
                    'phone'        => $phone,
                    'fullphone'    => $fullphone,
                    'job_title'    => $request->job_title,
                    'newsletter'   => $request->newsletter ?? '',
                    'wa_updates'   => $request->wa_updates ?? '',
                    'company_id'   => $company->id,
                ]
            );


            $send = new EmailSender();
            $send->subject     = 'Thank You for Registering – Your Membership Application Is Under Review';
            $send->template    = 'email.waiting-approval';
            $send->data        = [
                'users_name'  => $request->name,
                'events_name' => 'Djakarta Mining Club Membership',
            ];
            $send->name        = $request->name;
            $send->from        = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to          = $email;
            $send->sendEmail();

            DB::commit();
        } catch (\Exception $registrationError) {
            DB::rollBack();
            unset($registrationError);
            return redirect()->back()
                ->with('error', 'Registration failed. Please try again.')
                ->withInput();
        }

        return redirect()->back()->with('alert', 'Registration successful. We will notify you by email after verification.');
    }

    protected function isProvisionalUser(User $user): bool
    {
        return empty($user->password) || is_null($user->verify_email) || is_null($user->verify_phone);
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

    public function visit(Request $request, $slug = null)
    {
        $event = $this->resolveActivePartnershipEvent($slug, $request);
        $giveawayEnabled = $this->isGiveawayEnabled($event, $request);
        return view('FormMember.visit', compact('event', 'giveawayEnabled'));
    }

    public function visitStore(Request $request, $slug = null)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'title'       => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:30',
        ]);

        $event = $this->resolveActivePartnershipEvent($slug, $request);
        $eventId = $event ? $event->id : 70;

        $visitor = PartnershipEventVisitor::create([
            'events_id'      => $eventId,
            'name'           => $request->name,
            'company_name'   => PartnershipEventVisitor::normalizeCompanyName($request->institution),
            'job_title'      => $request->title,
            'business_email' => $request->email,
            'mobile_number'  => $request->phone,
        ]);

        // Keep legacy VisitModel recorded for backwards compatibility
        try {
            VisitModel::create([
                'name'        => $request->name,
                'institution' => $request->institution,
                'job_title'   => $request->title,
                'email'       => $request->email,
                'phone'       => $request->phone,
            ]);
        } catch (\Exception $e) {
            // Silently continue if legacy table is optional
        }

        $giveawayEnabled = $this->isGiveawayEnabled($event, $request);
        $gift = null;

        if ($giveawayEnabled) {
            $gift = GiveawayService::draw($visitor->id);
            if ($gift) {
                $visitor->update(['merchandise' => $gift->name]);
            }
        }

        return redirect()->back()->with([
            'success'          => 'Thank you for visiting our booth!',
            'giveaway_enabled' => $giveawayEnabled,
            'gift'             => $gift ? $gift->name : null,
        ]);
    }

    /**
     * Check if giveaway feature is enabled:
     * 1. Query parameter or form input (?giveaway=1 / 0)
     * 2. Per-event setting ($event->has_giveaway)
     * 3. Global config/env (config('dmc.booth_giveaway_enabled'))
     */
    protected function isGiveawayEnabled(?Events $event = null, ?Request $request = null): bool
    {
        if ($request && $request->has('giveaway')) {
            return filter_var($request->input('giveaway'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($event && isset($event->has_giveaway)) {
            return (bool) $event->has_giveaway;
        }

        return (bool) config('dmc.booth_giveaway_enabled', env('BOOTH_GIVEAWAY_ENABLED', true));
    }

    /**
     * Resolve active partnership event with dynamic fallback:
     * 1. Explicit slug or ID from route parameter ($slug) or request input (event_slug/event_id/event)
     * 2. Config / Environment variable (ACTIVE_BOOTH_EVENT_SLUG / ACTIVE_BOOTH_EVENT_ID)
     * 3. Ongoing partnership event based on current date (start_date <= today <= end_date)
     * 4. Hardcoded default fallback: International Critical Minerals & Metals Summit: Indonesia 2026 (id: 70)
     */
    protected function resolveActivePartnershipEvent(?string $slug = null, ?Request $request = null): ?Events
    {
        $identifier = $slug;

        if (!$identifier && $request) {
            $identifier = $request->input('event_slug')
                ?: $request->input('event_id')
                ?: $request->query('event')
                ?: $request->query('event_id');
        }

        if ($identifier) {
            $event = Events::where('slug', $identifier)
                ->orWhere('id', is_numeric($identifier) ? (int) $identifier : 0)
                ->first();

            if ($event) {
                return $event;
            }
        }

        // Config / .env setting
        $configuredSlug = config('dmc.active_booth_event_slug', env('ACTIVE_BOOTH_EVENT_SLUG'));
        if (!empty($configuredSlug)) {
            $event = Events::where('slug', $configuredSlug)
                ->orWhere('id', is_numeric($configuredSlug) ? (int) $configuredSlug : 0)
                ->first();

            if ($event) {
                return $event;
            }
        }

        $configuredId = config('dmc.active_booth_event_id', env('ACTIVE_BOOTH_EVENT_ID'));
        if (!empty($configuredId)) {
            $event = Events::find($configuredId);
            if ($event) {
                return $event;
            }
        }

        // Smart auto-detect: Partnership Event happening today
        $today = Carbon::today()->toDateString();
        $runningEvent = Events::whereIn('event_type', ['Partnership Event'])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->first();

        if ($runningEvent) {
            return $runningEvent;
        }

        // Default fallback: International Critical Minerals & Metals Summit: Indonesia 2026
        return Events::where('slug', 'international-critical-minerals-metals-summit-indonesia-2026')
            ->orWhere('id', 70)
            ->first();
    }
}
