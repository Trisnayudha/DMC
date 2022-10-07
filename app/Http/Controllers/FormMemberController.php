<?php

namespace App\Http\Controllers;

use App\Models\MemberModel;
use Illuminate\Http\Request;

class FormMemberController extends Controller
{
    public function index()
    {
        return view('FormMember.index');
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
        $company_category = $request->company_category;
        $company_other = $request->company_other;

        if ($company_category == 'other') {
            $company_category = $company_other;
        }
        $explore = $request->explore;
        $cci = $request->cci;
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
            $findUsers->company_category = $company_category;
            $findUsers->explore = $explore;
            $findUsers->cci = $cci;

            $findUsers->save();
            return redirect()->back()->with('alert', 'Updated data!');
        } else {
            $validated = $request->validate([
                'company_name' => 'required',
                'phone' => 'required|unique:xtwp_users_dmc',
                'email' => 'required|unique:xtwp_users_dmc',
                'name' => 'required',
                'job_title' => 'required',
                'company_website' => 'required',
                'address' => 'required',
                'country' => 'required',
                'company_category' => 'required',

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
            $save->company_category = $company_category;
            $save->explore = $explore;
            $save->cci = $cci;
            $save->save();

            return redirect()->back()->with('alert', 'New Membership DMC!');
        }
    }
}
