<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\MemberModel;
use App\Models\User;
use App\Models\VisitModel;
use App\Services\GiveawayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Newsletter;

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

        $prefix = $request->prefix;
        $company_name = $request->company_name . ", " . $prefix;
        $phone = $request->phone;
        $email = $request->email;
        $name = $request->name;
        $job_title = $request->job_title;
        $company_website = $request->company_website;
        $country = $request->country;
        $address = $request->address;
        $city = $request->city;
        $office_number = $request->office_number;
        $portal_code = $request->portal_code;
        $company_category = $request->company_category;
        $company_other = $request->company_other;

        if ($company_category == 'other') {
            $company_category = $company_other;
        }
        $explore = $request->explore;
        $cci = $request->cci;
        $test = Newsletter::subscribeOrUpdate($email, [
            'FNAME' => $name,
            'MERGE3' => $address,
            'PHONE' => $phone,
            'MMERGE5' => $company_name,
            'MMERGE6' => $company_category,
            'MMERGE8' => $job_title,
            'MMERGE10' => Carbon::now(),
            'MMERGE11' => $office_number,
            'MMERGE12' => $explore
        ]);
        $findUsers = MemberModel::where('phone', $phone)->orWhere('email', $email)->first();
        if (!empty($findUsers)) {
            $findUsers->company_name = $company_name;
            $findUsers->phone = $phone;
            $findUsers->email = $email;
            $findUsers->name = $name;
            $findUsers->job_title = $job_title;
            $findUsers->company_website = $company_website;
            $findUsers->country = $country;
            $findUsers->address = $address;
            $findUsers->city = $city;
            $findUsers->office_number = $office_number;
            $findUsers->portal_code = $portal_code;
            $findUsers->company_category = $company_category;
            $findUsers->explore = $explore;
            $findUsers->cci = $cci;
            $findUsers->register_as = 'Member';
            $findUsers->save();
            return redirect()->back()->with('alert', 'Updated data!');
        } else {
            $validated = $request->validate([
                'company_name' => 'required',
                'phone' => 'required|unique:xtwp_users_dmc',
                'email' => 'required|unique:xtwp_users_dmc',
                'name' => 'required',
                'job_title' => 'required',
                // 'company_website' => 'required',
                // 'address' => 'required',
                // 'country' => 'required',
                // 'company_category' => 'required',

            ]);
            $save = new MemberModel();
            $save->company_name = $company_name;
            $save->phone = $phone;
            $save->email = $email;
            $save->name = $name;
            $save->job_title = $job_title;
            $save->company_website = $company_website;
            $save->country = $country;
            $save->address = $address;
            $save->portal_code = $portal_code;
            $save->city = $city;
            $save->office_number = $office_number;
            $save->company_category = $company_category;
            $save->explore = $explore;
            $save->cci = $cci;
            $save->register_as = 'Member';
            $save->save();


            $send = new EmailSender();
            $send->subject = "Your Membership is Activated!";
            $send->template = "email.membership";
            $send->data = [
                "name" => $name,
                'email' => $email,

            ];
            $send->name = $name;
            $send->from = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to = $email;
            $send->sendEmail();
            return redirect()->back()->with('alert', 'New Membership DMC!');
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
