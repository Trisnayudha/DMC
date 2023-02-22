<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsappApi;
use App\Models\BookingContact\BookingContact;
use App\Models\Company\CompanyModel;
use App\Models\Events\UserRegister;
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

        //        contoh respon callback invoice
        //        "id": "579c8d61f23fa4ca35e52da4",
        //        "external_id": "invoice_123124123",
        //        "user_id": "5781d19b2e2385880609791c",
        //        "is_high": true,
        //        "payment_method": "BANK_TRANSFER",
        //        "status": "PAID",
        //        "merchant_name": "Xendit",
        //        "amount": 50000,
        //        "paid_amount": 50000,
        //        "bank_code": "PERMATA",
        //        "paid_at": "2016-10-12T08:15:03.404Z",
        //        "payer_email": "wildan@xendit.co",
        //        "description": "This is a description",
        //        "adjusted_received_amount": 47500,
        //        "fees_paid_amount": 0,
        //        "updated": "2016-10-10T08:15:03.404Z",
        //        "created": "2016-10-10T08:15:03.404Z",
        //        "currency": "IDR",
        //        "payment_channel": "PERMATA",
        //        "payment_destination": "888888888888"

        $id = request('id');
        $external_id = request('external_id');
        $user_id = request('user_id');
        $is_high = request('is_high');
        $payment_method = request('payment_method');
        $status = request('status');
        $merchant_name = request('merchant_name');
        $amount = request('amount');
        $paid_amount = request('paid_amount');
        $bank_code = request('bank_code');
        $paid_at = request('paid_at');
        $payer_email = request('payer_email');
        $description = request('description');
        $adjusted_received_amount = request('adjusted_received_amount');
        $fees_paid_amount = request('fees_paid_amount');
        $updated = request('updated');
        $created = request('created');
        $currency = request('currency');
        $payment_channel = request('payment_channel');
        $payment_destination = request('payment_destination');
        $status = 'PAID';
        $payment_method = 'Credit_card';
        $paid_amount  = 1000000;
        try {
            $check = Payment::where('code_payment', '=', 'HMGPZV7')->first();

            if (!empty($check)) {
                if ($status == 'PAID') {
                    // $image = QrCode::format('png')
                    //     ->size(200)->errorCorrection('H')
                    //     ->generate('SOXVGUK');
                    $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                    $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                    // Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $findUser = Payment::where('code_payment', 'HMGPZV7')
                        ->join('users as a', 'a.id', 'payment.member_id')
                        ->join('profiles as b', 'a.id', 'b.users_id')
                        ->join('company as c', 'c.id', 'b.company_id')
                        ->first();
                    if ($check->booking_contact_id != null) {
                        $findContact = BookingContact::where('id', $check->booking_contact_id)->first();

                        $loopPayment = Payment::where('booking_contact_id', $findContact->id)
                            ->join('users as a', 'a.id', 'payment.member_id')
                            ->join('profiles as b', 'a.id', 'b.users_id')
                            ->join('company as c', 'c.id', 'b.company_id')
                            ->join('events_tickets as d', 'payment.tickets_id', 'd.id')
                            ->get();
                        $detailWa = [];
                        $item_details = [];
                        foreach ($loopPayment as $data) {
                            $update = Payment::where('member_id', $data->member_id)->where('events_id', '4')->first();
                            $item_details[] = [
                                'name' => $data->name,
                                'job_title' => $data->email,
                                'price' => number_format($data->price_rupiah, 0, ',', '.'),
                                'paidoff' => false
                            ];
                            $update->status_registration = "Paid Off";
                            $update->payment_method = $payment_method;
                            $update->save();
                            $UserEvent = UserRegister::where('payment_id', $update->id)->first();
                            if (empty($UserEvent)) {
                                $UserEvent = new UserRegister();
                            }
                            $UserEvent->users_id = $update->member_id;
                            $UserEvent->events_id = $update->events_id;
                            $UserEvent->payment_id = $update->id;
                            $UserEvent->save();
                            $detailWa[] = '
Nama : ' . $data->name . '
Email: ' . $data->email . '
Phone Number: ' . $data->phone . '
Company : ' . $data->company_name . '
';
                        }
                        $link = null;
                        $data = [
                            'users_name' => $findContact->name_contact,
                            'users_email' => $findContact->email_contact,
                            'phone' => $findContact->phone_contact,
                            'company_name' => $findContact->company_name,
                            'company_address' => $findContact->address,
                            'status' => 'Paid Off',
                            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
                            'code_payment' => $findUser->code_payment,
                            'create_date' => date('d, M Y H:i'),
                            'package_name' => $findUser->package,
                            'price' => number_format($paid_amount, 0, ',', '.'),
                            'total_price' => number_format($paid_amount, 0, ',', '.'),
                            'voucher_price' => number_format(0, 0, ',', '.'),
                            'image' => $db,
                            'item' => $item_details,
                            'job_title' => $findContact->job_title_contact,
                            'link' => $link
                        ];
                        $pdf = Pdf::loadView('email.invoice-new-multiple', $data);
                        Mail::send('email.success-register-event', $data, function ($message) use ($findContact, $pdf) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findContact->email_contact);
                            $message->subject('Thank you for payment - Technological Advances Driving Innovation in Indonesia`s Mining
                            Industry ');
                            $message->attachData($pdf->output(), 'E-Receipt.pdf');
                        });
                        $send = new WhatsappApi();
                        $send->phone = '083829314436';
                        $send->message = '
Hai Team,

Success payment dari ' . $findContact->name . '
Detail Informasinya:
' . implode(" ", $detailWa) . '

Thank you
Best Regards Bot DMC
                                                ';
                        $send->WhatsappMessage();
                        $res['api_status'] = 1;
                        $res['api_message'] = 'Payment status is updated';
                        $res['payload'] = $loopPayment;
                    }
                }
            } else {
                $res['api_status'] = 0;
                $res['api_message'] = 'Payment is not Found';
            }
            return response()->json($res, 200);
        } catch (\Exception $msg) {
            $res['api_status'] = 0;
            $res['api_message'] = $msg->getMessage();
            return response()->json($res, 500);
        }
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
}
