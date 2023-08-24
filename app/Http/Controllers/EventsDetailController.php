<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Models\BookingContact\BookingContact;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Services\Events\EventsService;
use App\Services\Payment\PaymentService;
use App\Services\Users\UsersService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Xendit\Invoice;
use Xendit\Xendit;
use Illuminate\Support\Facades\Response;

class EventsDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function detail($slug, Request $request)
    {
        try {
            $params = $request->params;
            $event = Events::where('slug', $slug)->first();
            if ($event) {
                $list = PaymentService::listPaymentRegister($event->id, $params);
                $countAll = PaymentService::countRegister(null, $event->id);
                $countAllApprove = PaymentService::countRegisterApprove(null, $event->id);
                $countSponsor = PaymentService::countRegister('sponsor', $event->id);
                $countPaid = PaymentService::countRegister(['nonmember', 'member', 'onsite', 'table'], $event->id);
                $countFree = PaymentService::countRegister('free', $event->id);
                $usersCategory = UsersService::showChartCategory($event->id);
                $usersJobTitle = UsersService::showChartJobTitle($event->id);
                $users = User::orderBy('id', 'desc')->get();
                // dd($event->end_date);
                $data = [
                    'payment' => $list,
                    'users' => $users,
                    'slug' => $slug,
                    'all' => $countAll,
                    'allApprove' => $countAllApprove,
                    'sponsor' => $countSponsor,
                    'free' => $countFree,
                    'paid' => $countPaid,
                    'date' => $event->end_date,
                    'chartCategoryData' => $usersCategory,
                    'chartJobTitle' => $usersJobTitle
                ];
                return view('admin.events.event-detail', $data);
            } else {
                return 'Event Not Found';
            }
        } catch (\Exception $e) {
            // Handle the exception here
            return $e->getMessage();
        }
    }


    public function add_user(Request $request)
    {
        try {
            $id = $request->nama;
            $ticket = $request->ticket;
            $pilihan = $request->pilihan;
            $slug = $request->event;
            $code_payment = strtoupper(Str::random(7));
            $pic = Auth::id();

            $findUsers = User::where('users.id', $id)
                ->leftJoin('profiles', 'profiles.users_id', 'users.id')
                ->leftJoin('company', 'company.users_id', 'users.id')
                ->select('users.id as users_id', 'users.*', 'company.*', 'profiles.*')
                ->first();

            $findEvent = Events::where('slug', $slug)->first();

            $findPayment = Payment::where('member_id', $id)->where('events_id', $findEvent->id)->first();

            if (!$findUsers) {
                throw new Exception('User not found');
            }

            if (!empty($findPayment)) {
                return redirect()->route('events-details', ['slug' => $slug])->with('error', 'Peserta sudah ada mendaftarkan diri');
            }

            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($code_payment);
            $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
            $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
            Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png

            $save = new Payment();
            $save->member_id = $id;
            $save->package = $ticket;
            $save->code_payment = $code_payment;
            $save->events_id = $findEvent->id;
            if ($ticket == 'member') {
                $save->tickets_id = 1;
            } else if ($ticket == 'nonmember') {
                $save->tickets_id = 2;
            } else if ($ticket == 'free') {
                $save->tickets_id = 3;
            } else if ($ticket == 'onsite') {
                $save->tickets_id = 9;
            } else {
                $save->tickets_id = 6;
            }
            $save->status_registration = 'Paid Off';
            $save->qr_code = $db;
            $save->pic_id = $pic;
            $save->save();

            if (!empty($pilihan)) {
                $data = [
                    'code_payment' => $code_payment,
                    'create_date' => date('d, M Y H:i'),
                    'users_name' => $findUsers->name,
                    'users_email' => $findUsers->email,
                    'phone' => $findUsers->phone,
                    'job_title' => $findUsers->job_title,
                    'company_name' => $findUsers->company_name,
                    'company_address' => $findUsers->address,
                    'events_name' => $findEvent->name,
                    'start_date' => $findEvent->start_date,
                    'end_date' => $findEvent->end_date,
                    'start_time' => $findEvent->start_time,
                    'end_time' => $findEvent->end_time,
                    'image' => $db
                ];
                $email = $findUsers->email;

                ini_set('max_execution_time', 300);
                $pdf = Pdf::loadView('email.ticket', $data);

                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $code_payment, $findEvent) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($code_payment . ' - Your registration is approved for ' . $findEvent->name);
                    $message->attachData($pdf->output(), $code_payment . '-' . time() . '.pdf');
                });
            }

            return redirect()->route('events-details', ['slug' => $slug])->with('success', 'Success add peserta');
        } catch (Exception $e) {
            return redirect()->route('events-details', ['slug' => $slug])->with('error', $e->getMessage());
        }
    }

    public function add_invitation(Request $request)
    {
        try {
            $pic = Auth::id();
            $prefix = $request->prefix;
            $name = $request->name;
            $company_website = $request->company_website;
            $job_title = $request->job_title;
            $company_category = $request->company_category;
            $company_name = $request->company_name;
            $email = $request->email;
            $phone = $request->phone;
            $country = $request->country;
            $address = $request->address;
            $office_number = $request->office_number;
            $portal_code = $request->portal_code;
            $city = $request->city;
            $company_other = $request->company_other;
            $paymentMethod = $request->ticket;
            $slug = $request->event;
            $user = User::firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->email = $email;
            $user->save();

            $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
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
            $company->users_id = $user->id;
            $company->save();

            $profile = ProfileModel::where('users_id', $user->id)->first();
            if (empty($profile)) {
                $profile = new ProfileModel();
            }
            $profile->phone = $phone;
            $profile->job_title = $job_title;
            $profile->users_id = $user->id;
            $profile->company_id = $company->id;
            $profile->save();

            if ($paymentMethod == 'member') {
                $total_price = 900000;
            } else if ($paymentMethod == 'nonmember') {
                $total_price = 1000000;
            } else if ($paymentMethod == 'onsite') {
                $total_price = 1250000;
            } else {
                $total_price = 0;
            }
            $codePayment = strtoupper(Str::random(7));
            $date = date('d-m-Y H:i:s');
            $linkPay = null;
            if ($paymentMethod != 'free' && $paymentMethod != 'sponsor') {
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
                    'payer_email' => $email,
                    'description' => 'Invoice Event DMC',
                    'amount' => $total_price,
                    'success_redirect_url' => 'https://djakarta-miningclub.com',
                    'failure_redirect_url' => url('/'),
                ];
                $createInvoice = Invoice::create($params);
                $linkPay = $createInvoice['invoice_url'];
            }
            // TODO masih hardcode
            $findEvent = Events::where('slug', $slug)->first();
            $check = Payment::where('events_id', '=', $findEvent->id)->where('member_id', '=', $user->id)->first();
            $data = [
                'code_payment' => $codePayment,
                'create_date' => date('d, M Y H:i'),
                'due_date' => date('d, M Y H:i', strtotime($date . ' +1 day')),
                'users_name' => $name,
                'users_email' => $email,
                'phone' => $phone,
                'company_name' => $company_name,
                'company_address' => $address,
                'status' => 'WAITING',
                'events_name' => $findEvent->name,
                'price' => number_format($total_price, 0, ',', '.'),
                'voucher_price' => 0,
                'total_price' => number_format($total_price, 0, ',', '.'),
                'link' => $linkPay
            ];

            if (empty($check)) {
                $payment = Payment::firstOrNew(['member_id' => $user->id]);
                if ($paymentMethod == 'free' || $paymentMethod == 'sponsor') {
                    $payment->package = $paymentMethod;
                    // $payment->price = $total_price;
                    $payment->status_registration = 'Paid Off';
                    $payment->code_payment = $codePayment;
                    $payment->events_id = $findEvent->id;
                } else {
                    $payment->package = $paymentMethod;
                    $payment->payment_method = 'Credit Card';
                    $payment->status_registration = 'Waiting';
                    $payment->link = $linkPay;
                    $payment->code_payment = $codePayment;
                    $payment->events_id = $findEvent->id;
                    if ($paymentMethod == 'member') {
                        $payment->tickets_id = 1;
                    } else if ($paymentMethod == 'nonmember') {
                        $payment->tickets_id = 2;
                    } else if ($paymentMethod == 'free') {
                        $payment->tickets_id = 3;
                    } else if ($paymentMethod == 'onsite') {
                        $payment->tickets_id = 9;
                    } else {
                        $payment->tickets_id = 6;
                    }
                }
                $payment->pic_id = $pic;
                $payment->save();
                if ($paymentMethod == 'free' || $paymentMethod == 'sponsor') {
                    $image = QrCode::format('png')
                        ->size(200)->errorCorrection('H')
                        ->generate($codePayment);
                    $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                    $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                    Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                    $data = [
                        'code_payment' => $codePayment,
                        'create_date' => date('d, M Y H:i'),
                        'users_name' => $name,
                        'users_email' => $email,
                        'phone' => $phone,
                        'company_name' => $company_name,
                        'company_address' => $address,
                        'job_title' => $job_title,
                        'events_name' => $findEvent->name,
                        'image' => $db,
                        'start_date' => $findEvent->start_date,
                        'end_date' => $findEvent->end_date,
                        'start_time' => $findEvent->start_time,
                        'end_time' => $findEvent->end_time,
                    ];
                    // dd("sukses");
                    ini_set('max_execution_time', 300);
                    // TODO bakal ada bug
                    $pdf = Pdf::loadView('email.ticket', $data);
                    try {
                        Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($email);
                            $message->subject($codePayment . ' - Your registration is approved for ' . $findEvent->name);
                            $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                        });
                    } catch (\Exception $e) {
                        return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('error', 'An error occurred while sending email. Please try again later.')->withInput();
                    }
                    return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('alert', 'Register Successfully');
                } else {
                    // $pdf = Pdf::loadView('email.invoice-new', $data);
                    try {
                        Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                            $message->from(env('EMAIL_SENDER'));
                            $message->to($email);
                            $message->subject('Invoice - Waiting for Payment');
                            // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                        });
                    } catch (\Exception $e) {
                        return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('error', 'An error occurred while sending email. Please try again later.')->withInput();
                    }
                    return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('alert', 'Check your email for payment Invoice !!!');
                }
            } else {
                return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('error', 'Email Already Register, please check your inbox for information event or create new email for registering')->withInput();
            }
        } catch (Exception $e) {
            return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('error', $e->getMessage());
        }
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $val = $request->val;
        $db = null;
        $update = Payment::where('id', $id)->first();
        $findEvent = Events::where('id', $update->events_id)->first();
        $pic = Auth::id();
        if (!empty($update)) {
            $check = DB::table('payment')
                ->leftJoin('users', 'users.id', 'payment.member_id')
                ->leftJoin('company', 'company.users_id', 'users.id')
                ->leftJoin('profiles', 'profiles.users_id', 'users.id')
                ->select('payment.*', 'users.*', 'payment.id as payment_id', 'profiles.*', 'company.*')
                ->where('payment.id', '=', $id)
                ->first();
            if ($val == 'approve') {
                $update->status_registration = "Paid Off";
                $update->payment_method = 'Approve Manual';
                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($check->code_payment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $update->qr_code = $db;
                $saveUser = UserRegister::where('users_id', $check->users_id)->first();
                if (empty($saveUser)) {
                    $saveUser = new UserRegister();
                }
                $saveUser->events_id = $update->events_id;
                $saveUser->users_id = $check->users_id;
                $saveUser->payment_id = $check->payment_id;
                $saveUser->save();
            } else {
                $update->status_registration = "Reject";
                $saveUser = UserRegister::where('users_id', $check->users_id)->first();
                if (!empty($saveUser)) {
                    $saveUser->delete();
                }
            }
            $update->pic_id = $pic;
            $update->save();

            // dd($check);
            $data = [
                'code_payment' => $check->code_payment,
                'create_date' => date('d, M Y H:i'),
                'users_name' => $check->name,
                'users_email' => $check->email,
                'phone' => $check->phone,
                'job_title' => $check->job_title,
                'company_name' => $check->company_name,
                'company_address' => $check->address,
                'events_name' => $findEvent->name,
                'start_date' => $findEvent->start_date,
                'end_date' => $findEvent->end_date,
                'start_time' => $findEvent->start_time,
                'end_time' => $findEvent->end_time,
                'image' => $db
            ];
            $email = $check->email;
            $code_payment = $check->code_payment;
            if ($val == 'approve') {
                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $code_payment, $findEvent) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($code_payment . ' - Your registration is approved for ' . $findEvent->name);
                    $message->attachData($pdf->output(), $code_payment . '-' . time() . '.pdf');
                });
                return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('success', 'Successfully Approval');
            } else {
                $send = new EmailSender();
                $send->from = env('EMAIL_SENDER');
                $send->to = $email;
                $send->data = $data;
                $send->subject = '[FULLY BOOKED] ' . $findEvent->name;
                $send->name = $check->name;
                $send->template = 'email.reject-event';
                $send->sendEmail();
                return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('success', 'Successfully Reject Register');
            }
            // $pdf = Pdf::loadView('email.ticket', $data);
            // return $pdf->stream();

        } else {
            dd("Payment not found");
        }
    }
    public function removeParticipant(Request $request)
    {
        $id = $request->id;
        Payment::where('id', $id)->delete();
        UserRegister::where('payment_id', $id)->delete();

        return redirect()->back()->with('success', 'Successfully Remove Participant');
    }

    public function editPeserta(Request $request)
    {

        $findPayment = Payment::where('code_payment', $request->code_payment_edit)->first();

        $findPayment->package = $request->package_edit ?? null;
        if ($request->package_edit == 'member') {
            $findPayment->tickets_id = 2;
        } else if ($request->package_edit == 'nonmember') {
            $findPayment->tickets_id = 1;
        } else if ($request->package_edit == 'free') {
            $findPayment->tickets_id = 3;
        } else if ($request->package_edit == 'onsite') {
            $findPayment->tickets_id = 9;
        } else {
            $findPayment->tickets_id = 6;
        }
        // dd($findPayment->tickets_id);
        $findPayment->save();
        // dd($request->email_edit);

        $findUsers = User::where('id', $findPayment->member_id)->first();
        $findUsers->name = $request->name_edit;
        $findUsers->email = $request->email_edit;
        $findUsers->save();

        $company = CompanyModel::where('users_id', $findUsers->id)->first();
        if (empty($company)) {
            $company = new CompanyModel();
        }
        $company->prefix = $request->prefix_edit;
        $company->company_name = $request->company_name_edit;
        $company->company_website = $request->company_website_edit;
        $company->company_category = $request->company_category_edit;
        $company->company_other = $request->company_other_edit;
        $company->address = $request->address_edit;
        $company->city = $request->city_edit;
        $company->portal_code = $request->portal_code_edit;
        $company->office_number = $request->office_number_edit;
        $company->country = $request->country_edit;
        $company->users_id = $findUsers->id;
        $company->save();

        $profile = ProfileModel::where('users_id', $findUsers->id)->first();
        if (empty($profile)) {
            $profile = new ProfileModel();
        }
        $profile->phone = $request->phone_edit;
        $profile->job_title = $request->job_title_edit;
        $profile->users_id = $findUsers->id;
        $profile->company_id = $company->id;
        $profile->save();
        return redirect()->back()->with('success', 'Successfully Update data');
    }

    public function invoice(Request $request)
    {
        $payment_id = $request->id;
        $findPayment = PaymentService::findPaymmentUser($payment_id);
        //check booking_contact_id
        $findEvent = EventsService::showDetail($findPayment->events_id);

        if ($findPayment->booking_contact_id != null) {
            $findPayments = PaymentService::findPaymmentUsers($findPayment->booking_contact_id);
            $countPrice = null;
            foreach ($findPayments as $table) {
                $item_details[] = [
                    'name' => $table['name'],
                    'job_title' => $table['email'],
                    'price' => number_format($table['price_rupiah'], 0, ',', '.'),
                    'paidoff' => false
                ];
                $countPrice += $table['price_rupiah'];
            }
            $findBooking = BookingContact::where('id', $table['booking_contact_id'])->first();
            $payload = [
                'code_payment' => $findPayment->code_payment,
                'create_date' => date('d, M Y H:i'),
                'users_name' => $findBooking->name_contact,
                'users_email' => $findBooking->email_contact,
                'phone' => $findBooking->phone_contact,
                'company_name' => $findBooking->company_name,
                'company_address' => $findBooking->address,
                'status' => 'Paid Off',
                'voucher_price' => 0,
                'item' => $item_details,
                'price' => number_format($table['price_rupiah'], 0, ',', '.'),
                'total_price' => number_format($countPrice, 0, ',', '.'),
                'events_name' => $findEvent->name,
                'link' => null
            ];
            ini_set('max_execution_time', 120);
            $pdf = Pdf::loadView('email.invoice-new-multiple', $payload);
            $filename = 'invoice_' . $findPayment->code_payment . '.pdf';
            // Download the PDF with the specified filename
            return $pdf->download($filename);
        }
        $payload = [
            'code_payment' => $findPayment->code_payment,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $findPayment->name,
            'users_email' => $findPayment->email,
            'phone' => $findPayment->phone,
            'company_name' => $findPayment->company_name,
            'company_address' => $findPayment->address,
            'status' => 'Paid Off',
            'voucher_price' => 0,
            'price' => number_format($findPayment->price_rupiah, 0, ',', '.'),
            'total_price' => number_format($findPayment->price_rupiah, 0, ',', '.'),
            'events_name' => $findEvent->name,
        ];
        ini_set('max_execution_time', 120); // Set the maximum execution time to 120 seconds
        $pdf = PDF::loadView('email.invoice-new', $payload);
        // Set the desired filename for the downloaded PDF
        $filename = 'invoice_' . $findPayment->code_payment . '.pdf';
        // Download the PDF with the specified filename
        return $pdf->download($filename);
    }

    public function ticket(Request $request)
    {
        $payment_id = $request->id;
        $findUsers = PaymentService::findPaymmentUser($payment_id);
        $findEvent = EventsService::showDetail($findUsers->events_id);

        $data = [
            'code_payment' => $findUsers->code_payment,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $findUsers->name,
            'users_email' => $findUsers->email,
            'phone' => $findUsers->phone,
            'job_title' => $findUsers->job_title,
            'company_name' => $findUsers->company_name,
            'company_address' => $findUsers->address,
            'events_name' => $findEvent->name,
            'start_date' => $findEvent->start_date,
            'end_date' => $findEvent->end_date,
            'start_time' => $findEvent->start_time,
            'end_time' => $findEvent->end_time,
            'image' => $findUsers->qr_code
        ];
        $email = $findUsers->email;

        ini_set('max_execution_time', 300);
        $pdf = Pdf::loadView('email.ticket', $data);
        $filename = 'e_ticket-' . $findUsers->code_payment . '.pdf';
        // Download the PDF with the specified filename
        return $pdf->download($filename);
    }
}
