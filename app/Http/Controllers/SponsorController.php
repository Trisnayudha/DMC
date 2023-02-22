<?php

namespace App\Http\Controllers;

use App\Models\Company\CompanyModel;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SponsorController extends Controller
{
    public function sponsor()
    {
        $company = Sponsor::get();
        $data = [
            'company' => $company
        ];
        return view('register_event.sponsor', $data);
    }

    public function show_sponsor($id)
    {
        $detail = Sponsor::findOrFail($id);

        return response()->json($detail);
    }

    public function register_sponsor(Request $request)
    {
        $findSponsor = Sponsor::find($request->company);
        foreach ($request->name as $key => $value) {
            $uname = strtoupper(Str::random(7));
            $codePayment = strtoupper(Str::random(7));

            $findUser = User::firstOrNew(array('email' => $request->email[$key]));
            $findUser->name = $request->name[$key];
            $findUser->email = $request->email[$key];
            $findUser->password = Hash::make('DMC2023');
            $findUser->uname = $uname;
            $findUser->save();

            $id[] = [
                'id' => $findUser->id,
                'code_payment' => $codePayment
            ];
            $id_final = $id[0]['id'];
            // $code_payment_final = $id[0]['code_payment'];
            $string_office = $request->office_number;
            $office_number = preg_replace('/[^0-9]/', '', $string_office);
            $firstTwoDigits_office = substr($string_office, 1, 3);
            $phone_office = substr($office_number, 2);

            $findCompany = CompanyModel::where('users_id', $findUser->id)->first();
            if (empty($findCompany)) {
                $findCompany = new CompanyModel();
            }
            $findCompany->company_name = $findSponsor->name;
            $findCompany->office_number = $request->office_number;
            $findCompany->address = $request->address;
            $findCompany->company_website = $request->company_website;
            $findCompany->users_id = $findUser->id;
            $findCompany->save();


            $string = $request->phone[$key];
            $number = preg_replace('/[^0-9]/', '', $string);
            $firstTwoDigits = substr($string, 1, 3);
            $phone = substr($number, 2);

            $findProfile = ProfileModel::where('users_id', $findUser->id)->first();
            if (empty($findProfile)) {
                $findProfile = new ProfileModel();
            }
            $findProfile->users_id = $findUser->id;
            $findProfile->fullphone = $number;
            $findProfile->job_title = $request->job_title[$key];
            $findProfile->phone = $phone;
            $findProfile->prefix_phone = $firstTwoDigits;
            $findProfile->company_id = $findCompany->id;
            $findProfile->save();
            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($codePayment);
            $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
            $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
            Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
            $findPayment = Payment::where('member_id', $findUser->id)->where('events_id', '4')->first();
            if (empty($findPayment)) {
                $findPayment = new Payment();
            }


            $findPayment->member_id = $findUser->id;
            $findPayment->package = 'Sponsors';
            $findPayment->code_payment = $codePayment;
            $findPayment->link = null;
            $findPayment->events_id = 4;
            $findPayment->tickets_id = 3;
            $findPayment->status_registration = 'Paid Off';
            $findPayment->groupby_users_id = $id_final;
            $findPayment->save();

            $data = [
                'code_payment' => $codePayment,
                'create_date' => date('d, M Y H:i'),
                'users_name' => $request->name[$key],
                'users_email' => $request->email[$key],
                'phone' => $request->phone[$key],
                'job_title' => $request->job_title[$key],
                'company_name' => $findCompany->company_name,
                'company_address' => $findCompany->address,
                'events_name' => 'Technological Advances Driving Innovation in Indonesia`s Mining Industry',
                'image' => $db
            ];
            $email =  $request->email[$key];

            $pdf = Pdf::loadView('email.ticket', $data);
            Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject($codePayment . ' - Your registration is approved for Technological Advances Driving Innovation in Indonesia`s Mining Industry 2023');
                $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
            });
        }
        return redirect()->back()->with('alert', 'Successfully Registering Sponsor');
    }
}
