<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
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
use App\Models\Vouchers\Voucher;
use Illuminate\Http\Request;
use Xendit\PaymentChannels;
use Xendit\Xendit;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Xendit\VirtualAccounts;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Xendit\Invoice;

class PaymentController extends Controller
{

    public function listbank()
    {
        // init xendit
        $isProd = env('XENDIT_ISPROD');
        if ($isProd) {
            $secretKey = env('XENDIT_SECRET_KEY_PROD');
        } else {
            $secretKey = env('XENDIT_SECRET_KEY_TEST');
        }
        // params invoice
        Xendit::setApiKey($secretKey);
        $getPaymentChannels = PaymentChannels::list();
        $available_payment = array_filter($getPaymentChannels, function ($key) {
            return $key['is_enabled'] == true &&
                ($key['channel_category'] == 'VIRTUAL_ACCOUNT' || $key['channel_category'] == 'CREDIT_CARD');
        });

        // Ubah kembali index array
        $available_payment = array_values($available_payment);

        // Cek apakah sudah ada opsi CREDIT_CARD, jika belum tambahkan manual
        $hasCreditCard = false;
        foreach ($available_payment as $payment) {
            if ($payment['channel_category'] == 'CREDIT_CARD') {
                $hasCreditCard = true;
                break;
            }
        }

        if (!$hasCreditCard) {
            $manual_credit_card = [
                'business_id'      => Str::random(24), // business_id random
                'is_livemode'      => true,
                'channel_code'     => 'VISA',
                'name'             => 'VISA',
                'currency'         => 'IDR',
                'channel_category' => 'CREDIT_CARD',
                'is_enabled'       => true,
            ];
            $available_payment[] = $manual_credit_card;
        }

        $response['status'] = 200;
        $response['message'] = 'List Bank';
        $response['payload'] = $available_payment;

        return response()->json($response);
    }

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
            ->join('events', 'events.id', '=', 'payment.events_id')
            ->select('payment.*', 'events_tickets.*', 'events.*', 'payment.link')
            ->whereIn('status_registration', $statuses);

        // Tambahkan filter pencarian berdasarkan events.name jika parameter search tersedia
        if ($request->has('search')) {
            $search = $request->input('search');
            $findPayment->where('events.name', 'LIKE', '%' . $search . '%');
        }

        $findPayment = $findPayment->orderBy('payment.id', 'desc')
            ->paginate($limit);

        return response()->json([
            'status'  => 200,
            'message' => 'Success',
            'payload' => $findPayment
        ]);
    }

    public function payment(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $type = $request->type;
        $events_id = $request->events_id;
        $tickets_id = $request->tickets_id;
        $payment_method = $request->payment_method;
        $createVA = null;
        $codePayment = strtoupper(Str::random(7));
        $package = $type == 'paid' ? 'member' : 'free';
        $payment_method = $payment_method == 'other' ? 'Free-pass Apps' : $payment_method;
        // $payment_method = $payment ? $payment : 'free pas';
        $Serv = env('APP_NAME');

        if ($Serv == 'Server') {
            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($codePayment);
        }
        $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
        if ($Serv == 'Server') {
            Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
        }
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $events_id)->first();
        $findUsers = User::where('id', '=', $id)->first();
        $profileModel = ProfileModel::where('users_id', $findUsers->id)->first();
        $companyModel = CompanyModel::where('users_id', $findUsers->id)->first();
        $findTicket = EventsTicket::where('id', '=', $tickets_id)->first();
        $save = new Payment();
        if ($findPayment == null || $findPayment->status_registration == 'Cancel') {
            $save->member_id = $id;
            $save->package = $package;
            $save->code_payment = $codePayment;
            $save->payment_method = $payment_method;
            $save->tickets_id = $tickets_id;
            $save->events_id = $events_id;
            $save->status_registration = ($type == 'free' ? 'Waiting' : 'Waiting');
            $save->qr_code = $db;
            $save->save();
            if ($type == 'free') {
                $saveUser = new UserRegister();
                $saveUser->events_id = $events_id;
                $saveUser->users_id = $id;
                $saveUser->payment_id = $save->id;
                // $saveUser->save();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Register Success, please waiting approval';
                $notif->NotifApp();
            } else {
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
                    'external_id' => $codePayment,
                    'bank_code' => $payment_method,
                    'name' => $findUsers->name,
                    'expected_amount' => $findTicket->price_rupiah,
                    'is_closed' => true,
                    "expiration_date" => Carbon::now()->addDays(1)->toISOString(),
                    'is_single_use' => true,
                ];

                $createVA = VirtualAccounts::create($params);
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $save->id;
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
                //notif wa
                $send = new WhatsappApi();
                $send->phone = '081332178421';  // Nomor admin
                // $send->phone = '083829314436';  // Nomor admin
                $send->message = "
Paid Registration Notification,

Ada pendaftaran baru (PAID) dengan metode pembayaran: $payment_method
Detail Informasi:
Name: $findUsers->name
Email: $findUsers->email
Phone: $profileModel->phone
Company: $companyModel->company_name
Job Title: $profileModel->job_title

Code Payment: $codePayment
Total Bayar: Rp. " . number_format($createVA['expected_amount'], 0, ',', '.') . "

Terima kasih.
";
                $send->WhatsappMessage();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Your wait is over! Your Virtual Account is now up and running, ready for smooth transactions.';
                $notif->NotifApp();

                //notif email


            }
            $free = [
                'code_payment' => $codePayment,
                'status' => 'WAITING'
            ];
            $response['status'] = 200;
            $response['message'] = 'success';
            $response['payload'] = $createVA ? $createVA : $free;
        } else {
            $response['status'] = 404;
            $response['message'] = 'You have another payment, please contact admin for information';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function creditCard(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $type = $request->type;
        $events_id = $request->events_id;
        $tickets_id = $request->tickets_id;
        $payment_method = $request->payment_method;
        $createVA = null;
        $codePayment = strtoupper(Str::random(7));
        $package = $type == 'paid' ? 'Premium' : 'free';
        $payment_method = $payment_method == 'other' ? 'Free-pass Apps' : $payment_method;

        $date = date('d-m-Y H:i:s');
        $linkPay = null;
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $events_id)->first();
        $findUsers = User::where('id', '=', $id)->first();
        $profileModel = ProfileModel::where('users_id', $findUsers->id)->first();
        $companyModel = CompanyModel::where('users_id', $findUsers->id)->first();
        $findTicket = EventsTicket::where('id', '=', $tickets_id)->first();

        $Serv = env('APP_NAME');
        if ($Serv == 'Server') {
            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($codePayment);
        }
        $save = new Payment();
        if (empty($findPayment) || $findPayment->status_registration == 'Cancel') {
            if ($payment_method == 'CREDIT_CARD') {
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                if ($Serv == 'Server') {
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                }
                $save->member_id = $id;
                $save->package = $package;
                $save->code_payment = $codePayment;
                $save->payment_method = $payment_method;
                $save->tickets_id = $tickets_id;
                $save->events_id = $events_id;
                $save->status_registration = ($type == 'free' ? 'Waiting' : 'Waiting');
                $save->qr_code = $db;

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
                    'external_id' => $codePayment,
                    'payer_email' => $findUsers->email,
                    'description' => 'Payment Credit Card',
                    'amount' => $findTicket->price_rupiah,
                    'success_redirect_url' => 'https://api.djakarta-miningclub.com/payment-success',
                    'failure_redirect_url' => url('/'),
                ];
                $createInvoice = Invoice::create($params);

                $linkPay = $createInvoice['invoice_url'];
                $save->link = $linkPay;
                $save->save();
                $save_va = new PaymentUsersVA();
                $save_va->payment_id = $save->id;
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
                //notif wa
                $send = new WhatsappApi();
                $send->phone = '081332178421';  // Nomor admin
                // $send->phone = '083829314436';  // Nomor admin
                $send->message = "
Paid Registration Notification,

Ada pendaftaran baru (PAID) dengan metode pembayaran: $payment_method
Detail Informasi:
Name: $findUsers->name
Email: $findUsers->email
Phone: $profileModel->phone
Company: $companyModel->company_name
Job Title: $profileModel->job_title

Code Payment: $codePayment
Total Bayar: Rp. " . number_format($findTicket->price_rupiah, 0, ',', '.') . "

Terima kasih.
";
                $send->WhatsappMessage();
                $notif = new Notification();
                $notif->id = $id;
                $notif->message = 'Invoice ' . $codePayment . ' created succesfully';
                $notif->NotifApp();

                //notif email


                $response['status'] = 200;
                $response['message'] = 'success';
                $response['payload'] = $createInvoice ? $createInvoice : null;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Payment Method not same with url';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 404;
            $response['message'] = 'You have another payment, please contact admin for information';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function payment_v2(Request $request)
    {
        DB::beginTransaction();
        try {
            // Cek apakah user sedang login (via Sanctum)
            $user = auth('sanctum')->user();

            // Ambil data dari request (single registration)
            $email          = $request->email;
            $name           = $request->name;
            $phone          = $request->phone;
            $company        = $request->company;
            $job_title      = $request->job_title;
            $address        = $request->address;       // jika perlu
            $events_id      = $request->events_id;
            $payment_method = $request->payment_method; // misal: BCA, MANDIRI, CREDIT_CARD, dsb
            $type           = $request->type;          // 'paid' atau 'free'
            $ticket_id      = $request->tickets_id;    // ID ticket
            $voucher_code   = $request->voucher_code;  // kode voucher
            $codePayment    = strtoupper(Str::random(7));

            // Variabel penampung link invoice (untuk CC)
            $linkPay = null;

            // --- Validasi data utama (Event & Tiket) ---
            $findEvent  = Events::where('id', $events_id)->first();
            $findTicket = EventsTicket::where('id', $ticket_id)->first();
            $package        = $findTicket->title == 'Member' ? 'member' : 'nonmember';
            $price          = $findTicket->price_rupiah;   // harga normal
            if (!$findEvent || !$findTicket) {
                // Batalkan transaksinya, lalu return response
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Event atau Tiket tidak ditemukan',
                    'payload' => null
                ]);
            }

            // --- Proses user (Login vs Anonymous) ---
            if (!$user) {
                // Anonymous, cari/buat user by email
                $user = User::firstOrNew(['email' => $email]);
                if (!$user->exists) {
                    $user->name     = $name;
                    // misal generate password random
                    $user->password = bcrypt(Str::random(12));
                    $user->save();
                }

                // Buat/Update data company
                $companyModel = CompanyModel::firstOrNew(['users_id' => $user->id]);
                if (!$companyModel->exists) {
                    $companyModel->users_id     = $user->id;
                    $companyModel->company_name = $company;
                    $companyModel->address      = $address;
                    $companyModel->save();
                }

                // Buat/Update data profile
                $profileModel = ProfileModel::firstOrNew(['users_id' => $user->id]);
                if (!$profileModel->exists) {
                    $profileModel->users_id   = $user->id;
                    $profileModel->company_id = $companyModel->id;
                    $profileModel->phone      = $phone;
                    $profileModel->job_title  = $job_title;
                    $profileModel->save();
                }
            }
            // Jika user login, kita sudah punya $user
            $user_id = $user->id;

            // --- Cek apakah user sudah pernah daftar (dan belum dibatalkan) untuk event ini ---
            $findPayment = Payment::where('member_id', $user_id)
                ->where('events_id', $events_id)
                ->whereNotIn('status_registration', ['Cancel', 'Expired'])
                ->first();
            if ($findPayment) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Anda sudah memiliki payment untuk event ini. Silakan hubungi admin.',
                    'payload' => null
                ]);
            }

            // --- Proses voucher diskon ---
            $discount = 0;
            if (!empty($voucher_code)) {
                // Cek di table vouchers
                $voucherData = Voucher::where('voucher_code', $voucher_code)
                    ->where('status', 'active')
                    ->first();
                if ($voucherData) {
                    if ($voucherData->type == 'fixed') {
                        // Diskon berupa nominal tetap
                        $discount = $voucherData->nominal;
                        // Pastikan discount tidak melebihi harga
                        if ($discount > $price) {
                            $discount = $price;
                        }
                    } elseif ($voucherData->type == 'percentage') {
                        // Diskon berupa persentase
                        // Misal nominal voucher 10 berarti 10% diskon
                        $discount = ($price * $voucherData->nominal) / 100;
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 400,
                        'message' => 'Voucher tidak valid atau sudah tidak aktif',
                        'payload' => null
                    ]);
                }
            }
            $finalPrice = $price - $discount;


            // --- Buat Payment baru ---
            $payment = new Payment();
            $payment->member_id           = $user_id;
            $payment->package             = $package;
            $payment->code_payment        = $codePayment;
            $payment->payment_method      = $payment_method;
            $payment->tickets_id          = $ticket_id;
            $payment->events_id           = $events_id;
            $payment->status_registration = 'Waiting';
            $payment->discount            = $discount;
            $payment->voucher_code        = $voucher_code;
            $payment->save();

            // --- Integrasi Xendit ---
            $isProd    = env('XENDIT_ISPROD');
            $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
            Xendit::setApiKey($secretKey);

            $responsePayload = null;

            // Jika type = paid, buat invoice/VA
            if ($type == 'paid') {
                if ($payment_method == 'CREDIT_CARD') {
                    // Buat Invoice untuk Credit Card
                    $paramsInvoice = [
                        'external_id'          => $codePayment,
                        'payer_email'          => $user->email,
                        'description'          => 'Invoice Event ' . $findEvent->name,
                        'amount'               => $finalPrice,
                        'success_redirect_url' => 'https://djakarta-miningclub.com',
                        'failure_redirect_url' => url('/'),
                    ];

                    $createInvoice = Invoice::create($paramsInvoice);
                    $linkPay       = $createInvoice['invoice_url'];

                    // Update payment dengan link invoice
                    $payment->link = $linkPay;
                    $payment->save();

                    // Simpan ke PaymentUsersVA
                    $save_va = new PaymentUsersVA();
                    $save_va->payment_id      = $payment->id;
                    $save_va->is_closed       = 0;
                    $save_va->status          = "PENDING";
                    $save_va->country         = 'IDR';
                    $save_va->owner_id        = $createInvoice['user_id'];
                    $save_va->bank_code       = 'CREDIT_CARD';
                    $save_va->expected_amount = $createInvoice['amount'];
                    $save_va->expiration_date = $createInvoice['expiry_date'];
                    $save_va->is_single_use   = 0;
                    $save_va->save();

                    $responsePayload = $createInvoice;
                } else {
                    // Buat VA (contoh: BCA, MANDIRI, dll)
                    $paramsVA = [
                        'external_id'     => $codePayment,
                        'bank_code'       => $payment_method,
                        'name'            => $user->name,
                        'expected_amount' => $finalPrice,
                        'is_closed'       => true,
                        'is_single_use'   => true,
                        'expiration_date' => Carbon::now()->addDay(1)->toISOString(),
                    ];
                    $createVA = VirtualAccounts::create($paramsVA);

                    // Simpan ke PaymentUsersVA
                    $save_va = new PaymentUsersVA();
                    $save_va->payment_id      = $payment->id;
                    $save_va->is_closed       = $createVA['is_closed'];
                    $save_va->status          = $createVA['status'];
                    $save_va->currency        = $createVA['currency'];
                    $save_va->owner_id        = $createVA['owner_id'];
                    $save_va->bank_code       = $createVA['bank_code'];
                    $save_va->merchant_code   = $createVA['merchant_code'];
                    $save_va->account_number  = $createVA['account_number'];
                    $save_va->expected_amount = $createVA['expected_amount'];
                    $save_va->expiration_date = $createVA['expiration_date'];
                    $save_va->is_single_use   = $createVA['is_single_use'];
                    $save_va->save();

                    $responsePayload = $createVA;
                }

                // Kirim Email Invoice / Konfirmasi
                try {
                    $dataEmail = [
                        'code_payment' => $codePayment,
                        'create_date' => Carbon::now()->format('d M Y H:i'),
                        'due_date' => Carbon::now()->addDay(1)->format('d M Y H:i'),
                        'users_name' => $user->name,
                        'users_email' => $user->email,
                        'phone' => $profileModel->phone,
                        'company_name' => $companyModel->company_name,
                        'company_address' => null,
                        'status' => 'WAITING',
                        'events_name' => $findEvent->name,
                        'price' => number_format($price, 0, ',', '.'),
                        'voucher_price' => number_format($discount, 0, ',', '.'),
                        'total_price' => number_format($finalPrice, 0, ',', '.'),
                        'link' => $linkPay ?? null,
                        'fva' => $save_va->account_number ?? null
                    ];
                    Mail::send('email.confirm_payment', $dataEmail, function ($message) use ($email, $findEvent) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($email);
                        $message->subject('Invoice - Waiting for Payment: ' . $findEvent->name);
                    });
                } catch (\Exception $e) {
                    // Kalau email gagal, kita log tapi tidak kita rollback transaksinya,
                    // Karena Payment & VA sudah sukses tercipta.
                    Log::error('Mail error: ' . $e->getMessage());
                }

                // Kirim notifikasi WhatsApp (opsional), misal ke admin
                try {
                    $send = new WhatsappApi();
                    $send->phone = '081332178421';  // Nomor admin
                    // $send->phone = '083829314436';  // Nomor admin
                    $send->message = "
    Paid Registration Notification,

    Ada pendaftaran baru (PAID) dengan metode pembayaran: $payment_method
    Detail Informasi:
    Name: $name
    Email: $email
    Phone: $phone
    Company: $company
    Job Title: $job_title

    Code Payment: $codePayment
    Total Bayar: Rp. " . number_format($finalPrice, 0, ',', '.') . "

    Terima kasih.
    ";
                    $send->WhatsappMessage();
                } catch (\Exception $e) {
                    Log::error('Whatsapp send error (paid): ' . $e->getMessage());
                }
            } else {
                // Kalau type = free
                // Kirim email notifikasi "waiting approval" (opsional)
                try {
                    $dataEmail = [
                        'users_name'   => $user->name,
                        'events_name'  => $findEvent->name,
                    ];
                    Mail::send('email.waiting-approval', $dataEmail, function ($message) use ($email, $findEvent) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($email);
                        $message->subject('Thank you for registering ' . $findEvent->name);
                    });
                } catch (\Exception $e) {
                    Log::error('Mail error: ' . $e->getMessage());
                }

                // Kirim notifikasi WhatsApp (opsional), misal ke admin
                try {
                    $send = new WhatsappApi();
                    // $send->phone = '081332178421'; // Nomor admin
                    $send->phone = '083829314436'; // Nomor admin
                    $send->message = "
    Registration Notification,

    Hai ada pendaftaran GRATIS
    Detail Informasinya:
    Name: $name
    Email: $email
    Phone Number: $phone
    Company: $company
    Job Title: $job_title

    Thank you
    Best Regards Bot DMC Website
    ";
                    $send->WhatsappMessage();
                } catch (\Exception $e) {
                    Log::error('Whatsapp send error (free): ' . $e->getMessage());
                }
            }

            // Jika semua langkah sukses, commit transaction
            DB::commit();

            // Siapkan response akhir
            $response = [
                'status'  => 200,
                'message' => 'success',
                'payload' => $responsePayload
                    ? $responsePayload
                    : [
                        'code_payment' => $codePayment,
                        'status'       => 'WAITING'
                    ]
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            // Jika ada error, rollback seluruh perubahannya
            DB::rollBack();

            // Log error (opsional)
            Log::error('payment_v2 error: ' . $e->getMessage());

            // Return error ke client
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan. Transaksi dibatalkan: ' . $e->getMessage(),
                'payload' => null
            ]);
        }
    }
}
