<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
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
                'source'        => $request->source ?? 'web',
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

    public function visit()
    {
        return view('FormMember.visit');
    }

    public function visitStore(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'title'       => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:30',
        ]);

        $visit = VisitModel::create([
            'name' => $request->name,
            'institution' => $request->institution,
            'job_title' => $request->title,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $gift = GiveawayService::draw($visit->id);

        return redirect()->back()->with([
            'success' => 'Thank you for visiting our booth!',
            'gift' => $gift ? $gift->name : null
        ]);
    }
}
