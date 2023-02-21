<?php

namespace App\Http\Controllers;

use App\Models\BookingContact\BookingContact;
use App\Models\Company\CompanyModel;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Xendit\Invoice;
use Xendit\Xendit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

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
        // dd($request->all());
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

        $saveBooking = new BookingContact();
        $saveBooking->name_contact = $name_contact;
        $saveBooking->email_contact = $email_contact;
        $saveBooking->phone_contact = $phone_contact;
        $saveBooking->job_title_contact = $job_title_contact;
        $saveBooking->prefix = $prefix;
        $saveBooking->company_name = $company_name;
        $saveBooking->address = $address;
        $saveBooking->city = $city;
        $saveBooking->company_website = $company_website;
        $saveBooking->country = $country;
        $saveBooking->portal_code = $portal_code;
        $saveBooking->company_category = $company_category;
        $saveBooking->company_other = $company_other;
        $saveBooking->office_number = $office_number;
        $saveBooking->save();

        $tables = $request->tables;
        $countPrice = 0;
        $item_details = [];
        foreach ($tables as $table) {
            $checkUsers = User::where('email', $table['email'])->first();

            if (empty($checkUsers)) {
                $checkUsers = new User();
                $checkUsers->email = $table['email'];
                $checkUsers->name = $table['name'];
                $checkUsers->save();
                $company = CompanyModel::firstOrNew([
                    'users_id' => $checkUsers->id
                ]);
                $company->prefix = $prefix;
                $company->company_name = $company_name;
                $company->company_website = $company_website;
                $company->company_category = $company_category;
                $company->company_other = $company_other;
                $company->address = $address;
                $company->city = $city;
                $company->portal_code = $portal_code;
                $company->office_number = $office_number;
                $company->country = $country;
                $company->users_id = $checkUsers->id;
                $company->save();
                $profile = ProfileModel::where('users_id', $checkUsers->id)->first();
                if (empty($profile)) {
                    $profile = new ProfileModel();
                }
                $profile->phone = $table['phone'];
                $profile->job_title = $table['job_title'];
                $profile->users_id = $checkUsers->id;
                $profile->company_id = $company->id;
                $profile->save();
            }
            if ($table['price'] == 'member') {
                $total_price = 900000;
            } else if ($table['price'] == 'nonmember') {
                $total_price = 1000000;
            } else if ($table['price'] == 'onsite') {
                $total_price = 1250000;
            } else {
                $total_price  = 0;
            }
            $checkPayment = Payment::where('member_id', $checkUsers->id)->first();
            $codePayment = strtoupper(Str::random(7));


            //Conditional Payment
            if (empty($checkPayment)) {
                //Payment Kosong
                $checkPayment = new Payment();
                $checkPayment->package = $table['price'];
                $checkPayment->status_registration = 'Waiting';
                $checkPayment->code_payment = $codePayment;
                $checkPayment->member_id = $checkUsers->id;
                $checkPayment->events_id = 4;
                $checkPayment->booking_contact_id = $saveBooking->id;
                // $checkPayment->link = $linkPay;
                $checkPayment->save();
            } elseif ($checkPayment->status_registration == 'Waiting' || $checkPayment->status_registration == 'Expired') {
                // Payment ada tapi waiting
                $checkPayment = Payment::where('member_id', $checkUsers->id)->first();
                $checkPayment->package =  $table['price'];
                $checkPayment->status_registration = 'Waiting';
                $checkPayment->code_payment = $codePayment;
                $checkPayment->member_id = $checkUsers->id;
                $checkPayment->events_id = 4;
                $checkPayment->booking_contact_id = $saveBooking->id;
                // $checkPayment->link = $linkPay;
                $checkPayment->save();
            } else {
                //Paymentnya sudah Paid Off
            }
            $countPrice += $total_price;
            $item_details[] = [
                'name' => $table['name'],
                'job_title' => $table['email'],
                'price' => number_format($total_price, 0, ',', '.'),
            ];
        }
        $date = date('d-m-Y H:i:s');
        $linkPay = null;
        // init xendit
        $isProd = env('XENDIT_ISPROD');
        if ($isProd) {
            $secretKey = env('XENDIT_SECRET_KEY_PROD');
        } else {
            $secretKey = env('XENDIT_SECRET_KEY_TEST');
        }
        // params invoice
        Xendit::setApiKey($secretKey);
        $params = [
            'external_id' => $checkPayment->code_payment,
            'payer_email' => $email_contact,
            'description' => 'Invoice Event DMC',
            'amount' => $countPrice,
            'success_redirect_url' => 'https://djakarta-miningclub.com',
            'failure_redirect_url' => url('/'),
        ];
        $createInvoice = Invoice::create($params);
        $linkPay = $createInvoice['invoice_url'];
        $payload = [
            'code_payment' => $checkPayment->code_payment,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $saveBooking->name_contact,
            'users_email' => $saveBooking->email_contact,
            'phone' => $saveBooking->phone_contact,
            'company_name' => $saveBooking->company_name,
            'company_address' => $saveBooking->address,
            'status' => 'Waiting',
            'item' => $item_details,
            'voucher_price' => 0,
            'total_price' => number_format($countPrice, 0, ',', '.'),
            'link' => $linkPay
        ];
        $email = $saveBooking->email_contact;
        $pdf = Pdf::loadView('email.invoice-new-multiple', $payload);
        Mail::send('email.invoice-new-multiple', $payload, function ($message) use ($email, $pdf) {
            $message->from(env('EMAIL_SENDER'));
            $message->to($email);
            $message->subject('Invoice waiting Payment DMC');
            $message->attachData($pdf->output(), 'Invoice-' . time() . '.pdf');
        });
        $data = [
            'link' => $linkPay,
            'tables' => $tables,
            'count' => $countPrice
        ];
        $response['status'] = 200;
        $response['message'] = 'Data has been saved';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
// $company = CompanyModel::firstOrNew([
            //     'users_id' => $user->id
            // ]);
            // $company->prefix = $prefix;
            // $company->company_name = $company_name;
            // $company->company_website = $company_website;
            // $company->company_category = $company_category;
            // $company->company_other = $company_other;
            // $company->address = $address;
            // $company->city = $city;
            // $company->portal_code = $portal_code;
            // $company->office_number = $office_number;
            // $company->country = $country;
            // $company->users_id = $user->id;
            // $company->save();
            // $profile = ProfileModel::where('users_id', $user->id)->first();
            // if (empty($profile)) {
            //     $profile = new ProfileModel();
            // }
            // $profile->phone = $phone;
            // $profile->job_title = $job_title;
            // $profile->users_id = $user->id;
            // $profile->company_id = $company->id;
            // $profile->save();
