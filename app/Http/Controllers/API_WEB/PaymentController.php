<?php

namespace App\Http\Controllers\API_WEB;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentUsersVA;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Xendit\Invoice;
use Xendit\VirtualAccounts;
use Xendit\Xendit;

class PaymentController extends Controller
{
    public function historyPayment(Request $request)
    {
        $limit = $request->limit ?? 5;
        $user = auth('sanctum')->user();
        $id = $user->id;

        // Ambil parameter type sebagai filter status_registration.
        // Jika tidak dikirim, default akan mencakup semua status berikut:
        $statuses = $request->input('type', ['Waiting', 'Paid Off', 'Cancel', 'Expired']);
        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $findPayment = Payment::where('member_id', $id)
            ->join('events_tickets', 'events_tickets.id', '=', 'payment.tickets_id')
            ->select('payment.*', 'events_tickets.price_rupiah')
            ->whereIn('status_registration', $statuses)
            ->orderBy('id', 'desc')
            ->paginate($limit);

        return response()->json([
            'status'  => 200,
            'message' => 'Success',
            'payload' => $findPayment
        ]);
    }


    public function cancel(Request $request)
    {
        $code_payment = $request->code_payment;

        $findPayment = Payment::where('code_payment', $code_payment)->where('status_registration', 'Waiting')->first();
        if ($findPayment) {
            $findPayment->status_registration = 'Cancel';
            $findPayment->link = null;
            $findPayment->save();
            $response['status'] = 200;
            $response['message'] = 'Success Cancel payment';
            $response['payload'] = null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Payment Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function refresh(Request $request)
    {
        $code_payment = $request->code_payment;
        $findPayment = Payment::join('events', 'events.id', 'payment.events_id')->join('events_tickets', 'events_tickets.id', 'payment.tickets_id')->where('code_payment', $code_payment)->first();
        if ($findPayment->status_registration == 'Waiting') {
            $response['status'] = 404;
        } else {
            $response['status'] = 200;
        }
        $response['message'] = 'Success Refresh payment';
        $response['payload'] = $findPayment;
        return response()->json($response);
    }

    public function detail(Request $request)
    {
        $code_payment = $request->code_payment;

        // Cari data payment beserta data events terkait
        $findPayment = Payment::join('events', 'events.id', '=', 'payment.events_id')
            ->where('code_payment', $code_payment)
            ->select('payment.id as payment_id', 'payment.*', 'events.*', 'payment.link')
            ->first();

        // Jika payment tidak ditemukan, kembalikan error
        if (!$findPayment) {
            return response()->json([
                'status'  => 404,
                'message' => 'Payment not found',
                'payload' => null
            ], 404);
        }

        // Ambil exchange rate (nilai 1$ ke rupiah) dari helper
        $exchangeRate = \App\Helpers\ScrapeHelper::scrapeExchangeRate();

        // Pastikan field discount di Payment dalam bentuk integer
        $findPayment->discount = (int) $findPayment->discount;
        // Hitung discount_dollar: discount (rupiah) / exchange rate, dibulatkan dan di-cast ke int
        $findPayment->discount_dollar = (int) round($findPayment->discount / $exchangeRate);

        // Cari data ticket berdasarkan ticket id dari payment
        $findTicket = EventsTicket::where('id', $findPayment->tickets_id)->first();
        if ($findTicket) {
            // Misal, jika field harga tiket (price_rupiah) ada, konversi ke integer
            $findTicket->price_rupiah = (int) $findTicket->price_rupiah;
        }

        // Ambil detail payment
        $findDetailPayment = PaymentUsersVA::where('payment_id', $findPayment->payment_id)->first();

        // Jika detail payment ditemukan, tambahkan properti 'link'
        if ($findDetailPayment) {
            $findDetailPayment->link = $findPayment->link;
            // Pastikan field numerik lainnya di-cast ke int, misalnya expected_amount
            if (isset($findDetailPayment->expected_amount)) {
                $findDetailPayment->expected_amount = (int) $findDetailPayment->expected_amount;
            }
        }

        $data = [
            'detail'  => $findPayment,
            'ticket'  => $findTicket,
            'payment' => $findDetailPayment
        ];

        return response()->json([
            'status'  => 200,
            'message' => 'Success Refresh payment',
            'payload' => $data
        ]);
    }



    public function PaymentAnonymous(Request $request)
    {
        // Get data from request
        $emails = $request->email;
        $names = $request->name;
        $phones = $request->phone;
        $companies = $request->company;
        $job_titles = $request->job_title;
        $address = $request->address;
        $events_id = $request->events_id;
        $payment_method = $request->payment_method;
        $type = $request->type;
        $price = $request->price;
        $price_dollar = $request->price_dollar;
        $totalPrice = array_sum($price); // Sum the prices for total amount
        $package = null;
        // Validation Request
        $user_ids = []; // Array to store user IDs
        $codePayment = []; // Array to store code payment
        $paymentId = []; // Array to store payment ID
        // Xendit configuration
        $isProd = env('XENDIT_ISPROD');
        $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
        $findEvent = Events::where('id', $events_id)->first();
        foreach ($emails as $index => $email) {
            // Check and create User
            $user = User::firstOrNew(['email' => $email]);
            if (!$user->exists) {
                $user->name = $names[$index];
                $user->save();
            }
            $user_ids[] = $user->id; // Store user ID

            // Check and create Company
            $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
            if (!$company->exists) {
                $company->users_id = $user->id;
                $company->company_name = $companies[$index];
                $company->address = $address[$index];
                $company->save();
            }

            // Check and create Profile
            $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
            if (!$profile->exists) {
                $profile->users_id = $user->id;
                $profile->company_id = $company->id;
                $profile->phone = $phones[$index];
                $profile->job_title = $job_titles[$index];
                $profile->save();
            }


            $payment = Payment::firstOrNew(['member_id' => $user->id, 'events_id' => $events_id]);
            $payment->member_id = $user->id;
            if ($price[$index] == 1000000) {
                $ticket_id = 11;
                $package = 'nonmember';
            } elseif ($price[$index] == 900000) {
                $ticket_id = 12;
                $package = 'member';
            } else {
                $ticket_id = 26;
                $package = 'free';
            }
            $payment->package = $package;
            $payment->code_payment = strtoupper(Str::random(7));
            $codePayment[] = $payment->code_payment;
            $payment->payment_method = $payment_method;
            $payment->events_id = $events_id;
            $payment->status_registration = 'Waiting';
            // Only set groupby_users_id in the first loop if there are multiple users
            $payment->groupby_users_id = count($emails) == 1 ? null : $user_ids[0];
            $payment->tickets_id = $ticket_id;

            $payment->save();
            $paymentId[] = $payment->id;
            $detailWa[] = '
Name: ' . $names[$index] . '
Email: ' . $emails[$index] . '
Phone Number: ' . $phones[$index] . '
Company:' . $companies[$index] . '
Job Title:' . $job_titles[$index] . '
    ';
            $dataEmail[] = [
                'name' => $names[$index],
                'email' => $emails[$index],
                'phone' => $phones[$index],
                'company' => $companies[$index],
                'job_title' => $job_titles[$index],
                'code_payment' => $payment->code_payment
            ];
        }
        if ($type == 'paid') {
            // END LOOPING EACH USER IN THE GROUP
            Xendit::setApiKey($secretKey);

            if ($payment_method != 'CREDIT_CARD') {
                $params = [
                    'external_id' => $codePayment[0],
                    'bank_code' => $payment_method,
                    'name' => $names[0],
                    'expected_amount' => $totalPrice,
                    'is_closed' => true,
                    "expiration_date" => Carbon::now()->addDays(1)->toISOString(),
                    'is_single_use' => true,
                ];
                $createVA = VirtualAccounts::create($params);
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $paymentId[0];
                $save_va->is_closed = $createVA['is_closed'];
                $save_va->status = $createVA['status'];
                $save_va->currency = $createVA['currency'];
                // $save_va->country = $createVA['country'];
                $save_va->owner_id = $createVA['owner_id'];
                $save_va->bank_code = $createVA['bank_code'];
                $save_va->merchant_code = $createVA['merchant_code'];
                $save_va->account_number = $createVA['account_number'];
                $save_va->expected_amount = $createVA['expected_amount'];
                $save_va->expiration_date = $createVA['expiration_date'];
                $save_va->is_single_use = $createVA['is_single_use'];
                $save_va->save();
                $response['status'] = 200;
                $response['message'] = 'success';
                $response['payload'] = $createVA ? $createVA : null;
            } else {
                $params = [
                    'external_id' => $codePayment[0],
                    'payer_email' => $emails[0],
                    'description' => 'Invoice Event DMC',
                    'amount' => $totalPrice,
                    'success_redirect_url' => 'https://djakarta-miningclub.com',
                    'failure_redirect_url' => url('/'),
                ];
                $createInvoice = Invoice::create($params);
                $linkPay = $createInvoice['invoice_url'];
                $payment = Payment::where('id', $paymentId[0])->first();
                $payment->link = $linkPay;
                $payment->save();
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $paymentId[0];
                $save_va->is_closed = 0;
                $save_va->status = "PENDING";
                // $save_va->currency = $createInvoice['currency'];
                $save_va->country = 'IDR';
                $save_va->owner_id = $createInvoice['user_id'];
                $save_va->bank_code = 'CREDIT_CARD';
                // $save_va->merchant_code = $createInvoice['merchant_code'];
                // $save_va->account_number = $createInvoice['account_number'];
                $save_va->expected_amount = $createInvoice['amount'];
                $save_va->expiration_date = $createInvoice['expiry_date'];
                $save_va->is_single_use = 0;
                $save_va->save();
                $response['status'] = 200;
                $response['message'] = 'success';
                $response['payload'] = $createInvoice ? $createInvoice : null;
                //Payment CC
            }
            $date = date('d, M Y H:i');
            $dueDate = date('d, M Y H:i', strtotime($date . ' +1 day'));

            $data = [
                'code_payment' => $codePayment[0],
                'create_date' => $date,
                'due_date' => $dueDate,
                'users_name' => $names[0],
                'users_email' => $emails[0],
                'phone' => $phones[0],
                'company_name' => $companies[0],
                'company_address' => null,
                'status' => 'WAITING',
                'events_name' => $findEvent->name,
                'price' => number_format($totalPrice, 0, ',', '.'),
                'voucher_price' => 0,
                'total_price' => number_format($totalPrice, 0, ',', '.'),
                'link' => $linkPay ?? null
            ];
            Mail::send('email.confirm_payment', $data, function ($message) use ($email, $findEvent) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('Invoice - Waiting for Payment: ' . $findEvent->name);
                // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
            });
        } else {
            $data = [
                'code_payment' => $codePayment[0],
                'status' => 'WAITING'
            ];
            foreach ($dataEmail as $key) {
                $dataEmail = [
                    'users_name' => $key['name'],
                    'events_name' => $findEvent->name,
                ];

                $send = new EmailSender();
                $send->to = $key['email'];
                $send->from = env('EMAIL_SENDER');
                $send->data = $dataEmail;
                $send->subject = 'Thank you for registering ' . $findEvent->name;
                // $send->subject = 'Terima kasih atas registrasi anda untuk ' . $findEvent->name;
                $send->template = 'email.waiting-approval';
                $send->sendEmail();
            }
            //Code whatsapp send notif
            $send = new WhatsappApi();
            $send->phone = '081332178421';
            $send->message = '
Registration Notification,

Hai ada pendaftaran GRATIS
Detail Informasinya:
' . implode(" ", $detailWa) . '

Thank you
Best Regards Bot DMC Website
';
            $send->WhatsappMessage();
            $response['status'] = 200;
            $response['message'] = 'success';
            $response['payload'] = $data;
        }
        return response()->json($response);
    }
}
