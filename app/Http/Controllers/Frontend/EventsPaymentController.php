<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\BookingContact\BookingContact;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Xendit\Invoice;
use Xendit\Xendit;

class EventsPaymentController extends Controller
{

    public function payment_personal(Request $request)
    {
        try {
            $inputData = $request->only([
                'prefix',
                'company_name',
                'phone',
                'email',
                'name',
                'job_title',
                'company_website',
                'country',
                'address',
                'city',
                'office_number',
                'portal_code',
                'company_category',
                'company_other',
                'paymentMethod',
                'slug',
                'typeSponsor'
            ]);

            $user = User::firstOrNew(['email' => $inputData['email']]);
            $user->name = $inputData['name'];
            $user->save();

            $companyData = [
                'prefix',
                'company_name',
                'company_website',
                'company_category',
                'company_other',
                'address',
                'city',
                'portal_code',
                'office_number',
                'country'
            ];
            $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
            $company->fill(array_intersect_key($inputData, array_flip($companyData)));
            $company->users_id = $user->id;
            $company->save();

            $profileData = ['phone', 'job_title'];
            $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);

            // Memeriksa apakah data yang diinputkan berbeda dengan data di database
            if ($profile->phone !== $inputData['phone']) {
                $profile->phone = $inputData['phone'];
            }

            // Mengisi data job_title dari input
            $profile->job_title = $inputData['job_title'];

            $profile->company_id = $company->id;
            $profile->save();
            $typeSponsor = null;
            if (!empty($inputData['typeSponsor'])) {
                $typeSponsor = $inputData['typeSponsor'];
            }
            $findEvent = Events::where('slug', $inputData['slug'])->first();

            $total_price = 0;
            if ($inputData['paymentMethod'] == 'member') {
                $total_price = 900000;
            } else if ($inputData['paymentMethod'] == 'nonmember') {
                $total_price = 1000000;
            } else if ($inputData['paymentMethod'] == 'onsite') {
                $total_price = 1250000;
            }

            $codePayment = strtoupper(Str::random(7));
            $date = date('d-m-Y H:i:s');
            $linkPay = null;

            if ($inputData['paymentMethod'] != 'free') {
                $isProd = env('XENDIT_ISPROD');
                $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
                Xendit::setApiKey($secretKey);

                $params = [
                    'external_id' => $codePayment,
                    'payer_email' => $inputData['email'],
                    'description' => 'Invoice Event DMC',
                    'amount' => $total_price,
                    'success_redirect_url' => 'https://djakarta-miningclub.com',
                    'failure_redirect_url' => url('/'),
                ];

                $createInvoice = Invoice::create($params);
                $linkPay = $createInvoice['invoice_url'];
            }

            $check = Payment::where('events_id', '=', $findEvent->id)->where('member_id', '=', $user->id)->first();

            $data = [
                'code_payment' => $codePayment,
                'create_date' => date('d, M Y H:i'),
                'due_date' => date('d, M Y H:i', strtotime($date . ' +1 day')),
                'users_name' => $inputData['name'],
                'users_email' => $inputData['email'],
                'phone' => $inputData['phone'],
                'company_name' => $inputData['company_name'],
                'company_address' => $inputData['address'],
                'status' => 'WAITING',
                'events_name' => $findEvent->name,
                'price' => number_format($total_price, 0, ',', '.'),
                'voucher_price' => 0,
                'total_price' => number_format($total_price, 0, ',', '.'),
                'link' => $linkPay
            ];

            if (empty($check)) {
                $payment = Payment::firstOrNew(['member_id' => $user->id, 'events_id' => $findEvent->id]);

                if ($inputData['paymentMethod'] == 'free') {
                    $payment->package = $inputData['paymentMethod'];
                    $payment->status_registration = 'Waiting';
                    $payment->code_payment = $codePayment;
                    $payment->events_id = $findEvent->id;
                    $payment->sponsor_code = $typeSponsor;
                } else {
                    $payment->package = $inputData['paymentMethod'];
                    $payment->payment_method = 'Credit Card';
                    $payment->status_registration = 'Waiting';
                    $payment->link = $linkPay;
                    $payment->code_payment = $codePayment;
                    $payment->events_id = $findEvent->id;
                    $payment->sponsor_code = $typeSponsor;

                    if ($inputData['paymentMethod'] == 'member') {
                        $payment->tickets_id = 1;
                    } else if ($inputData['paymentMethod'] == 'nonmember') {
                        $payment->tickets_id = 2;
                    }
                }

                $payment->save();

                if ($inputData['paymentMethod'] == 'free') {
                    $send = new EmailSender();
                    $send->to = $inputData['email'];
                    $send->from = env('EMAIL_SENDER');
                    $send->data = $data;
                    $send->subject = 'Thank you for registering ' . $findEvent->name;
                    // $send->subject = 'Terima kasih atas registrasi anda untuk ' . $findEvent->name;
                    $send->template = 'email.waiting-approval';
                    $send->sendEmail();

                    $send = new WhatsappApi();
                    $send->phone = '081332178421';
                    $send->message = '
Registration Notification,

Hai ada pendaftaran GRATIS dari ' . $inputData['name'] . '
Detail Informasinya:
Nama: ' . $inputData['name'] . '
Company: ' . $inputData['company_name'] . '
Email: ' . $inputData['email'] . '
Phone: ' . $inputData['phone'] . '
Category Company: ' . ($inputData['company_category'] == 'other' ? $inputData['company_other'] : $inputData['company_category']) . '

Thank you
Best Regards Bot DMC Website
';
                    $send->WhatsappMessage();
                    $send = new EmailSender();
                    $send->to = $inputData['email'];
                    $send->from = env('EMAIL_SENDER');
                    $send->data = $data;
                    $send->subject = 'Thank you for registering ' . $findEvent->name;
                    // $send->subject = 'Terima kasih atas registrasi anda untuk ' . $findEvent->name;
                    $send->template = 'email.waiting-approval';
                    $send->sendEmail();
                    return redirect()->back()->with('alert', 'Register Successfully, you`ll be notified by email when your registration has been approved.');
                    // return redirect()->back()->with('alert', 'Pendaftaran Berhasil, Anda akan diberitahu melalui email ketika pendaftaran Anda disetujui.');
                } else {
                    $pdf = Pdf::loadView('email.invoice-new', $data);
                    Mail::send('email.confirm_payment', $data, function ($message) use ($inputData, $pdf) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($inputData['email']);
                        $message->subject('Invoice - Waiting for Payment');
                        $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                    });

                    return redirect()->back()->with('alert', 'Check your email for payment Invoice !!!');
                }
            } else {
                if ($inputData['paymentMethod'] == 'free') {
                    $payment = Payment::firstOrNew([
                        'member_id' => $user->id,
                        'events_id' => $findEvent->id
                    ]);

                    if ($inputData['paymentMethod'] == 'free') {
                        if ($check->status_registration == 'Paid Off') {
                            return redirect()->back()->with('error', 'Sorry, you have already registered for this event and cannot register again. Please check your email for arrival ticket information.')->withInput();
                        }

                        $payment->package = $inputData['paymentMethod'];
                        $payment->status_registration = 'Waiting';
                        $payment->code_payment = $codePayment;
                        $payment->events_id = $findEvent->id;
                        $payment->sponsor_code = $typeSponsor;
                        $payment->save();

                        $send = new EmailSender();
                        $send->to = $inputData['email'];
                        $send->from = env('EMAIL_SENDER');
                        $send->data = $data;
                        $send->subject = 'Thank you for registering ' . $findEvent->name;
                        // $send->subject = 'Terima kasih atas registrasi anda untuk ' . $findEvent->name;
                        $send->template = 'email.waiting-approval';
                        $send->sendEmail();
                        dd($send);
                        return redirect()->back()->with('alert', 'Registration successful! You`ll be notified via email upon approval.');
                        // return redirect()->back()->with('alert', 'Pendaftaran Berhasil, Anda akan diberitahu melalui email ketika pendaftaran Anda disetujui.');
                    }
                }

                return redirect()->back()->with('error', 'Email Already Register, please check your inbox for information event or create a new email for registering')->withInput();
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }





    public function payment_multiple(Request $request)
    {
        try {
            // Retrieve data from the request
            $bookingContact = $request->booking_contact;
            $company_name = $bookingContact['company_name'];
            $address = $bookingContact['address'];
            $city = $bookingContact['city'];
            $company_category = $bookingContact['company_category'];
            $company_website = $bookingContact['company_website'];
            $country = $bookingContact['country'];
            $email_contact = $bookingContact['email_contact'];
            $job_title_contact = $bookingContact['job_title_contact'];
            $name_contact = $bookingContact['name_contact'];
            $office_number = $bookingContact['office_number'];
            $phone_contact = $bookingContact['phone_contact'];
            $portal_code = $bookingContact['portal_code'];
            $prefix = $bookingContact['prefix_contact'];
            $company_other = $bookingContact['company_other'];
            $slug = $bookingContact['slug'];

            // Save booking contact data
            $saveBooking = new BookingContact();
            $saveBooking->fill($bookingContact);
            $saveBooking->save();

            // Retrieve table data
            $tables = $request->tables;
            $countPrice = 0;
            $item_details = [];
            $findEvent = Events::where('slug', $slug)->first();
            $detailWa = [];

            foreach ($tables as $table) {
                // Check if user exists
                $checkUsers = User::firstOrNew(['email' => $table['email']]);
                $isNewUser = !$checkUsers->exists;

                // Save user data
                if ($isNewUser) {
                    $checkUsers->name = $table['name'];
                    $checkUsers->save();
                }

                // Save company data
                $company = CompanyModel::firstOrNew(['users_id' => $checkUsers->id]);
                $company->fill([
                    'prefix' => $table['prefix'],
                    'company_name' => $table['company'],
                    'company_website' => $company_website,
                    'company_category' => $company_category,
                    'company_other' => $company_other,
                    'address' => $address,
                    'city' => $city,
                    'portal_code' => $portal_code,
                    'office_number' => $office_number,
                    'country' => $country,
                    'users_id' => $checkUsers->id
                ]);
                $company->save();

                // Save profile data
                $profile = ProfileModel::firstOrNew(['users_id' => $checkUsers->id]);
                $profile->fill([
                    'phone' => $table['phone'],
                    'job_title' => $table['job_title'],
                    'users_id' => $checkUsers->id,
                    'company_id' => $company->id
                ]);
                $profile->save();

                // Determine ticket ID and total price based on the table price
                $ticket_id = null;
                $total_price = 0;

                switch ($table['price']) {
                    case 'member':
                        $ticket_id = 12;
                        $total_price = 900000;
                        // $ticket_id = 18;
                        // $total_price = 500000;
                        break;
                    case 'nonmember':
                        $ticket_id = 11;
                        $total_price = 1000000;
                        // $ticket_id = 19;
                        // $total_price = 600000;
                        break;
                    case 'onsite':
                        $ticket_id = 9;
                        $total_price = 1250000;
                        // $ticket_id = 20;
                        // $total_price = 750000;
                        break;
                    case 'table':
                        $ticket_id = 13;
                        $total_price = 4000000;
                        break;
                }

                // Check if payment exists
                $checkPayment = Payment::where('member_id', $checkUsers->id)->where('events_id', $table['events_id'])->first();
                $codePayment = strtoupper(Str::random(7));
                $paidoff = false;

                if (empty($checkPayment)) {
                    // Payment is empty
                    $checkPayment = new Payment();
                    $checkPayment->fill([
                        'package' => $table['price'],
                        'status_registration' => 'Waiting',
                        'code_payment' => $codePayment,
                        'member_id' => $checkUsers->id,
                        'events_id' => $findEvent->id,
                        'tickets_id' => $ticket_id,
                        'booking_contact_id' => $saveBooking->id,
                        'groupby_users_id' => (count($tables) > 1) ? $saveBooking->id : null // Jika count($table) > 1, set $saveBooking->id, jika tidak, set null.
                    ]);
                    $checkPayment->save();
                } elseif ($checkPayment->status_registration == 'Waiting' || $checkPayment->status_registration == 'Expired') {
                    // Payment exists but is waiting
                    $checkPayment->package = $table['price'];
                    $checkPayment->code_payment = $codePayment;
                    $checkPayment->member_id = $checkUsers->id;
                    $checkPayment->tickets_id = $ticket_id;
                    $checkPayment->events_id = $findEvent->id;
                    $checkPayment->booking_contact_id = (count($tables) > 1) ? $saveBooking->id : null; // Jika count($table) > 1, set $saveBooking->id, jika tidak, set null.
                    $checkPayment->save();
                } else {
                    // Payment has already been paid off
                    $total_price = 0;
                    $paidoff = true;
                }

                $countPrice += $total_price;

                $item_details[] = [
                    'name' => $table['name'],
                    'job_title' => $table['email'],
                    'price' => number_format($total_price, 0, ',', '.'),
                    'paidoff' => $paidoff
                ];

                $detailWa[] = "
Nama : {$table['name']}
Email: {$table['email']}
Phone Number: {$table['phone']}
Company: {$table['company']}
Job Title: {$table['job_title']}
";
            }

            $send = new WhatsappApi();
            // $send->phone = '083829314436';
            $send->phone = '081332178421';
            $send->message = "
Registration Notification,

Hai ada pendaftaran multiple dari {$name_contact}
Detail Informasinya:
" . implode(" ", $detailWa) . "

Thank you
Best Regards Bot DMC Website
";
            $send->WhatsappMessage();
            $linkPay = null;
            $isProd = env('XENDIT_ISPROD');
            $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');

            Xendit::setApiKey($secretKey);

            $params = [
                'external_id' => $checkPayment->code_payment,
                'payer_email' => $email_contact,
                'description' => 'Invoice Event DMC',
                'amount' => $countPrice,
                'success_redirect_url' => 'https://djakarta-miningclub.com',
                'failure_redirect_url' => url('/')
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
                'link' => $linkPay,
                'events_name' => $findEvent->name,
            ];

            $saveBooking->link = $linkPay;
            $saveBooking->status = 'Waiting';
            // $saveBooking->save();

            $email = $saveBooking->email_contact;

            ini_set('max_execution_time', 300);
            $pdf = Pdf::loadView('email.invoice-new-multiple', $payload);
            // $saveBooking = new BookingContact();
            // Generate a unique filename for the PDF
            $filename = 'invoice_' . time() . '.pdf';

            // Store the PDF in the desired directory within the storage folder
            $pdfPath = 'public/invoice/' . $filename;
            $db = '/storage/uploads/invoice/' . $filename;
            Storage::put($pdfPath, $pdf->output());
            $saveBooking->invoice = $db;
            $saveBooking->save();

            Mail::send('email.invoice-new-multiple', $payload, function ($message) use ($email, $pdf) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('Invoice waiting Payment DMC');
                $message->attachData($pdf->output(), 'Invoice-' . time() . '.pdf');
            });

            $response['status'] = 1;
            $response['message'] = 'Thank you for registering. The invoice has been successfully emailed to you.';
        } catch (Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
