<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function test()
    {
        return view('test');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $codePayment = strtoupper(Str::random(7));
        $image = QrCode::format('png')
            ->size(300)->errorCorrection('H')
            ->generate($codePayment);
        $output_file = '/public/uploads/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/qr-code/img-' . time() . '.png';
        Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png



        // storage/app/images/file.png
        dd($db);
    }

    public function payment(Request $request)
    {
        //buat ngambil data booking contact
        $company_name = $request->booking_contact['company_name'];
        $address = $request->booking_contact['address'];
        $city = $request->booking_contact['city'];
        $company_category = $request->booking_contact['company_category'];
        $company_website = $request->booking_contact['company_website'];
        $country = $request->booking_contact['country'];
        $email_contact = $request->booking_contact['email_contact'];
        $job_title_contact = $request->booking_contact['job_title_contact'];
        $name_contact = $request->booking_contact['name_contact'];
        $office_number = $request->booking_contact['office_number'];
        $phone_contact = $request->booking_contact['phone_contact'];
        $portal_code = $request->booking_contact['portal_code'];
        $prefix = $request->booking_contact['prefix'];
        $company_other = $request->booking_contact['company_other'];
        // mengambil data array
        foreach ($request->tables as $data) {
            $email = $data['email'];
            $name = $data['name'];
            $job_title = $data['job_title'];
            $phone = $data['phone'];
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $request->all();
        return response()->json($response);
    }
}
