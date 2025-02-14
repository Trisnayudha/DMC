<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Helpers\Notification;
use App\Helpers\WhatsappApi;
use App\Models\BookingContact\BookingContact;
use App\Models\BusinessCard\BusinessCard;
use App\Models\Company\CompanyModel;
use App\Models\Contact;
use App\Models\Events\UserRegister;
use App\Models\Exhibitor;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller
{

    public function testEmail()
    {
        // $send = Mail::send('email.test', [], function ($message) {
        //     $message->from(env('EMAIL_SENDER'));
        //     $message->to('yudha@indonesiaminer.com');
        //     $message->subject('IT DMC TEST SEND MESSAGE');
        // });
        $send = new  EmailSender();
        $send->template = 'email.test';
        $send->name_sender = 'Secretariat';
        $send->from = 'secretariat@djakarta-miningclub.com';
        // $send->to = 'ray.ratumbanua@mammothequip.co.id';
        // $send->to = 'yudha@indonesiaminer.com';
        $send->to = 'erina@djakarta-miningclub.com';
        $send->subject = 'IT DMC TEST SEND MESSAGE';
        $send->sendEmail();
        dd($send);
    }


    public function storeBusinessCard(Request $request)
    {
        // Validasi input, hanya email yang required
        $validatedData = $request->validate([
            'company' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'required|email|unique:business_card,email',
            'mobile' => 'nullable|string|max:15',
        ]);

        // Simpan data ke database
        $businessCard = new BusinessCard();
        $businessCard->company = $validatedData['company'] ?? null;
        $businessCard->name = $validatedData['name'] ?? null;
        $businessCard->job_title = $validatedData['job_title'] ?? null;
        $businessCard->email = $validatedData['email'];
        $businessCard->mobile = $validatedData['mobile'] ?? null;
        $businessCard->save();

        // Response sukses
        return response()->json([
            'message' => 'Business card successfully saved!',
            'data' => $businessCard
        ], 201);
    }

    public function collectAndStoreExhibitorData(array $ids)
    {
        // Allow the script to run for up to 10 minutes (600 seconds)
        set_time_limit(600);
        foreach ($ids as $id) {
            // Make the GET request to the exhibitor API
            $response = Http::get("https://vexpo.iee-series.com/iee/pc/exhibitor/{$id}");

            if ($response->successful()) {
                $data = $response->json()['data'];

                // Store the exhibitor data in the database
                Exhibitor::updateOrCreate(
                    ['id' => $id], // Ensure that the same exhibitor is not duplicated
                    [
                        'name' => $data['name'] ?? 'N/A',
                        'country' => $data['country'] ?? 'N/A',
                        'desc' => $data['desc'] ?? null,
                        'website' => $data['website'] ?? null,
                        'contact' => $data['contact'] ?? null,
                        'contact_email' => $data['contactEmail'] ?? null,
                        'display_email' => $data['displayEmail'] ?? null,
                        'venue_hall' => $data['venueHall'] ?? null,
                        'event_name' => $data['eventName'] ?? null,
                        'exhibitor_logo' => $data['exhibitorLogo'] ?? null,
                        'booth_number' => $data['boothNumber'] ?? null,
                        'category1' => $data['category1'] ?? null,
                        'category2' => $data['category2'] ?? null,
                    ]
                );
            } else {
                // Handle API request failure
                return response()->json(['error' => "Failed to fetch data for exhibitor ID: {$id}"], 500);
            }
        }

        return response()->json(['message' => 'Exhibitor data collected and stored successfully']);
    }

    public function fetchAndStoreContactData(Request $request)
    {
        // Mendapatkan IDs dari parameter 'ids' di query string
        $idsParam = $request->query('ids');

        if (!$idsParam) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

        $ids = explode(',', $idsParam);

        // Validasi bahwa $ids adalah array dan tidak kosong
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'Invalid or missing IDs provided'], 400);
        }

        // Mengatur batas waktu eksekusi
        set_time_limit(600);

        // Nilai cookie session Anda
        $sessionCookieValue = 'eyJpdiI6ImhZZ1JLcTczTk5aSTN3RFp2Q0NQdVE9PSIsInZhbHVlIjoibUVzbFIwZzFha0l0R3RVRkNHRWxzczVCaTczdU81Q0Y0VDA2S0w4emF0OFJnY2dxUmw3cHZqVjI0L3JTbGNiZHNSdU1hVzIzWlZLaS9CTEh3bWhxMitWdWZNMEJJUWUwMGZDQVBCVlFDMnhORTBpZlF0VGl2WE1QTnphUUlwUHMiLCJtYWMiOiIwZGY4ZWNlZWJkYjEwMWMzZDNiOTIyODJiYTNkNzczM2QwMmMxMWZiMDJjZGMyMzc3OGFjOTg0NGM0NmZlNThiIiwidGFnIjoiIn0%3D';

        foreach ($ids as $id) {
            $id = trim($id); // Menghilangkan spasi di awal/akhir ID

            // Melakukan permintaan GET ke API dengan menyertakan cookie session
            $response = Http::withCookies([
                'eventware_session' => $sessionCookieValue,
            ], '.v4.eventnetworking.com') // Menentukan domain cookie
                ->get("https://beacon.v4.eventnetworking.com/imarc-2024/contacts/{$id}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['contact'])) {
                    $contact = $data['contact'];

                    // Menyimpan data kontak ke database
                    Contact::updateOrCreate(
                        ['contact_id' => $contact['id']], // Memastikan kontak tidak duplikat
                        [
                            'display_name' => $contact['display_name'] ?? 'N/A',
                            'avatar_url' => $contact['avatar_url'] ?? null,
                            'bio' => $contact['bio'] ?? null,
                            'country_name' => $contact['country_name'] ?? null,
                            'flourish_text' => $contact['flourish_text'] ?? null,
                            'job_title' => $contact['job_title'] ?? null,
                            'company_display_name' => $contact['company']['display_name'] ?? null,
                        ]
                    );
                } else {
                    return response()->json(['error' => "Contact data not found for ID: {$id}"], 500);
                }
            } else {
                return response()->json(['error' => "Failed to fetch data for contact ID: {$id}"], 500);
            }
        }

        return response()->json(['message' => 'Contact data collected and stored successfully']);
    }

    public function test()
    {
        return view('test');

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
                // $image = QrCode::format('png')
                //     ->size(200)->errorCorrection('H')
                //     ->generate('SOXVGUK');
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                // Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $findUser = Payment::where('code_payment', 'HMGPZV7')
                    ->leftjoin('users as a', 'a.id', 'payment.member_id')
                    ->leftjoin('profiles as b', 'a.id', 'b.users_id')
                    ->leftjoin('company as c', 'c.id', 'b.company_id')
                    ->first();
                if ($status == 'PAID') {
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
                            $update->qr_code =  $db;
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
                            $message->subject('Thank you for payment - The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia ');
                            $message->attachData($pdf->output(), 'E-Receipt.pdf');
                        });
                        $send = new WhatsappApi();
                        $send->phone = '081288761410';
                        $send->message = '
Hai Team,

Success payment dari ' . $findContact->name . '
Detail Informasinya:
' . implode(" ", $detailWa) . '

Thank you
Best Regards Bot DMC
                                                ';
                        $send->WhatsappMessage();

                        $notif = new Notification();
                        $notif->id = $check->member_id;
                        $notif->message = 'Payment successfully Web';
                        $notif->NotifApp();
                    } else {
                        // dd("masuk sini");
                        $check->status_registration = "Paid Off";
                        $check->payment_method = $payment_method;
                        $check->link = null;
                        $check->qr_code = $db;
                        $check->save();

                        $UserEvent = UserRegister::where('payment_id', $check->id)->first();
                        if (empty($UserEvent)) {
                            $UserEvent = new UserRegister();
                        }
                        $UserEvent->users_id = $check->member_id;
                        $UserEvent->events_id = $check->events_id;
                        $UserEvent->payment_id = $check->id;
                        $UserEvent->save();
                        $data = [
                            'users_name' => $findUser->name,
                            'users_email' => $findUser->email,
                            'phone' => $findUser->phone,
                            'company_name' => $findUser->company_name,
                            'company_address' => $findUser->address,
                            'status' => 'Paid Off',
                            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
                            'code_payment' => $findUser->code_payment,
                            'create_date' => date('d, M Y H:i'),
                            'package_name' => $findUser->package,
                            'price' => number_format($paid_amount, 0, ',', '.'),
                            'total_price' => number_format($paid_amount, 0, ',', '.'),
                            'voucher_price' => number_format(0, 0, ',', '.'),
                            'image' => $db,
                            'job_title' => $findUser->job_title
                        ];
                        $notif = new Notification();
                        $notif->id = $check->member_id;
                        $notif->message = 'Payment ' . $external_id . ' Successfully';
                        $notif->NotifApp();
                        $pdf = Pdf::loadView('email.invoice-new', $data);
                        Mail::send('email.success-register-event', $data, function ($message) use ($findUser, $pdf) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findUser->email);
                            $message->subject('Thank you for payment - The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia');
                            $message->attachData($pdf->output(), 'E-Receipt_' . $findUser->code_payment . '.pdf');
                        });

                        $pdf = Pdf::loadView('email.ticket', $data);
                        Mail::send('email.approval-event', $data, function ($message) use ($pdf, $findUser) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($findUser->email);
                            $message->subject($findUser->code_payment . ' - Your registration is approved for The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia ');
                            $message->attachData($pdf->output(), $findUser->code_payment . '-' . time() . '.pdf');
                        });
                        $send = new WhatsappApi();
                        $send->phone = '081288761410';
                        $send->message = '
Hai Team,

Success payment dari ' . $findUser->name . '
Detail Informasinya:
Nama : ' . $findUser->name . '
Email: ' . $findUser->email . '
Phone Number: ' . $findUser->phone . '
Company : ' . $findUser->company_name . '

Thank you
Best Regards Bot DMC
';
                        $send->WhatsappMessage();
                    }

                    $res['api_status'] = 1;
                    $res['api_message'] = 'Payment status is updated';
                } elseif ($status == 'EXPIRED') {
                    $check->status_registration = "Expired";
                    $check->payment_method = $payment_method;
                    $check->link = null;
                    $check->save();
                    $send = new WhatsappApi();
                    $send->phone = '081288761410';
                    $send->message = '

EXPIRED ALERT ! ! !

Nama : ' . $findUser->name . '
Email: ' . $findUser->email . '
Phone Number: ' . $findUser->phone . '
Company : ' . $findUser->company_name . '

Tolong di kontak kembali , takutnya ada kesulitan payment

Thank you
Best Regards Bot DMC
';
                    $send->WhatsappMessage();
                    $res['api_status'] = 1;
                    $res['api_message'] = 'Expired';
                } else {
                    $send = new WhatsappApi();
                    $send->phone = '081288761410';
                    $send->message = 'Error tidak diketahui, Check segera xendit';
                    $send->WhatsappMessage();
                    $res['api_status'] = 1;
                    $res['api_message'] = 'Error Tidak diketahui';
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

    public function saveInvoice(Request $request)
    {
        $db = null;
        $data = [
            'users_name' => 'Yudha',
            'users_email' => 'yudha@indonesiaminer.com',
            'phone' => '083829314436',
            'company_name' => 'Indonesia Miner',
            'company_address' => 'Gg Samsi',
            'status' => 'Paid Off',
            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
            'code_payment' => 'QZKdS8',
            'create_date' => date('d, M Y H:i'),
            'package_name' => 'Premium',
            'price' => number_format('1000000', 0, ',', '.'),
            'total_price' => number_format('1000000', 0, ',', '.'),
            'voucher_price' => number_format(0, 0, ',', '.'),
            'image' => $db,
            'job_title' => 'IT OFFICER'
        ];
        ini_set('max_execution_time', 300);
        $pdf = Pdf::loadView('email.invoice-new', $data);
        // Generate a unique filename for the PDF
        $filename = 'invoice_' . time() . '.pdf';

        // Store the PDF in the desired directory within the storage folder
        $pdfPath = 'public/invoice/' . $filename;
        $db = '/storage/invoice/' . $filename;
        Storage::put($pdfPath, $pdf->output());

        $send = new WhatsappApi();
        $send->phone = '083829314436';
        $send->document = asset($db);
        $send->WhatsappMessageWithDocument();
        dd($send);
    }
}
