<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\EventsCategory;
use App\Models\Events\EventsCategoryList;
use App\Models\Events\UserRegister;
use App\Models\MemberModel;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Xendit\Invoice;
use Xendit\Xendit;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpKernel\Profiler\Profile;

class EventController extends Controller
{

    public function index()
    {
        $list = Events::orderBy('id', 'desc')->get();

        $data = [
            'list' => $list
        ];
        return view('admin.events.event', $data);
    }

    public function free()
    {
        return view('register_event.free');
    }

    public function create()
    {
        $categories = EventsCategory::orderBy('id', 'desc')->get();

        $data = [
            'categories' => $categories
        ];
        return view('admin.events.create', $data);
    }

    public function edit($id)
    {
        $findEvent = Events::where('id', $id)->first();
        $data = [
            'data' => $findEvent
        ];
        return view('admin.events.edit', $data);
    }

    public function update(Request $request)
    {
        $findEvent = Events::where('id', $request->id)->first();
        $findEvent->name = $request->name;
        $findEvent->location = $request->location;
        $findEvent->description = $request->description;
        $findEvent->type = $request->type;
        $findEvent->location = $request->location;
        $findEvent->start_date = $request->start_date;
        $findEvent->end_date = $request->end_date;
        $findEvent->start_time = $request->start_time;
        $findEvent->end_time = $request->end_time;
        $findEvent->status = $request->status;
        $findEvent->slug = Str::slug($request->name);
        $file = $request->image;
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/events/' . $imageName;
            $findEvent_folder = $request->image->storeAs('public/events', $imageName);
            $findEvent->image = $db;
        }
        $findEvent->save();
        return redirect()->route('events')->with('success', 'Successfully Update event');
    }

    public function detail($slug)
    {
        $this->middleware('auth');
        $checkEvent = Events::where('slug', $slug)->first();
        if (!empty($checkEvent)) {

            $list = Payment::join('users', 'users.id', 'payment.member_id')
                ->join('company', 'company.users_id', 'users.id')
                ->join('profiles', 'profiles.users_id', 'users.id')
                ->where('payment.events_id', $checkEvent->id)
                ->select('users.*', 'payment.*', 'company.*', 'profiles.*', 'payment.id as payment_id')
                ->get();
            // dd($list);
            $users = User::orderBy('id', 'desc')->get();
            $data = [
                'payment' => $list,
                'users' => $users,
                'slug' => $slug
            ];
            return view('admin.events.event-detail', $data);
        } else {
            return 'Event Not Found';
        }
    }

    public function store(Request $request)
    {
        $save = new Events();
        $save->name = $request->name;
        $save->location = $request->location;
        $save->description = $request->description;
        $save->type = $request->type;
        $save->start_date = $request->start_date;
        $save->end_date = $request->end_date;
        $save->start_time = $request->start_time;
        $save->end_time = $request->end_time;
        $save->status = $request->status;
        $save->slug = Str::slug($request->name);
        $file = $request->image;
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/events/' . $imageName;
            $save_folder = $request->image->storeAs('public/events', $imageName);
            $save->image = $db;
        }
        $save->save();

        foreach ($request->category_id as $key => $value) {
            $category = EventsCategoryList::create([
                'events_id' => $save->id,
                'events_category_id' => $request->category_id[$key]
            ]);
        }
        return redirect()->route('events')->with('success', 'Successfully create new event');
    }

    public function sementara()
    {
        $this->middleware('auth');
        $list = DB::table('payment')
            ->join('xtwp_users_dmc', 'xtwp_users_dmc.id', 'payment.member_id')
            ->select('payment.*', 'xtwp_users_dmc.*', 'payment.id as payment_id')
            ->get();
        $data = [
            'payment' => $list
        ];
        return view('admin.events.sementara', $data);
    }

    public function view()
    {
        return view('register_event.index');
    }

    public function view2()
    {
        return view('register_event.index2');
    }

    public function payment_personal(Request $request)
    {
        $prefix = $request->prefix;
        $company_name = $request->company_name;
        $phone = $request->phone;
        $email = $request->email;
        $name = $request->name;
        $job_title = $request->job_title;
        $company_website = $request->company_website;
        $country = $request->country;
        $address = $request->address;
        $city = $request->city;
        $office_number = $request->office_number;
        $portal_code = $request->portal_code;
        $company_category = $request->company_category;
        $company_other = $request->company_other;
        $paymentMethod = $request->paymentMethod;

        $user = User::firstOrNew(
            ['email' =>  $email],
        );
        $user->name = $name;
        $user->email = $email;
        $user->save();

        $company = CompanyModel::firstOrNew([
            'users_id' => $user->id
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
            $total_price  = 0;
        }
        $codePayment = strtoupper(Str::random(7));
        $date = date('d-m-Y H:i:s');
        $linkPay = null;
        if ($paymentMethod != 'free') {

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
        $check = Payment::where('events_id', '=', '1')->where('member_id', '=', $user->id)->first();

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
            'events_name' => 'Mineral Trends 2023',
            'price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => $linkPay
        ];

        if (empty($check)) {
            $payment = Payment::firstOrNew(['member_id' => $user->id]);
            if ($paymentMethod == 'free') {
                $payment->package = $paymentMethod;
                // $payment->price = $total_price;
                $payment->status_registration = 'Waiting';
                $payment->code_payment = $codePayment;
                // $payment->link = null;
                $payment->events_id = 1;
            } else {
                $payment->package = $paymentMethod;
                $payment->payment_method = 'Credit Card';
                $payment->status_registration = 'Waiting';
                $payment->link = $linkPay;
                $payment->code_payment = $codePayment;
                $payment->events_id = 1;
                if ($paymentMethod == 'member') {
                    $payment->tickets_id = 1;
                } else if ($paymentMethod == 'nonmember') {
                    $payment->tickets_id = 2;
                }
            }
            $payment->save();
            if ($paymentMethod == 'free') {
                $send = new EmailSender();
                $send->to = $email;
                $send->from = env('EMAIL_SENDER');
                $send->data = $data;
                $send->subject = 'Thank you for registering Mineral Trends 2023 ';
                $send->template = 'email.waiting-approval';
                $send->sendEmail();
                return redirect()->back()->with('alert', 'Register Successfully, you`ll be notified by email when your registration has
                been approved');
            } else {
                // $pdf = Pdf::loadView('email.invoice-new', $data);
                Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject('Invoice - Waiting for Payment');
                    // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                });
                return redirect()->back()->with('alert', 'Check your email for payment Invoice !!!');
            }
        } else {
            if ($paymentMethod == 'free') {
                $payment = Payment::firstOrNew([
                    'member_id' => $user->id,
                    'events_id' => '1'
                ]);
                if ($paymentMethod == 'free') {
                    if ($check->status_registration == 'Paid Off') {
                        return redirect()->back()->with('error', 'Sorry, you have already registered for this event and cannot register again. Please check your email for arrival ticket information.')->withInput();
                    }
                    $payment->package = $paymentMethod;
                    $payment->status_registration = 'Waiting';
                    $payment->code_payment = $codePayment;
                    $payment->events_id = 1;
                    $payment->save();
                    $send = new EmailSender();
                    $send->to = $email;
                    $send->from = env('EMAIL_SENDER');
                    $send->data = $data;
                    $send->subject = 'Thank you for registering Mineral Trends 2023 ';
                    $send->template = 'email.waiting-approval';
                    $send->sendEmail();
                    return redirect()->back()->with('alert', 'Register Successfully, you`ll be notified by email when your registration has been approved');
                }
            }
            return redirect()->back()->with('error', 'Email Already Register, please check your inbox for information event or create new email for registering')->withInput();
        }
    }

    public function sponsor()
    {
        $company = Sponsor::get();
        $data = [
            'company' => $company
        ];
        return view('register_event.sponsor', $data);
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
            $findCompany->office_number = $findSponsor->office_number;
            $findCompany->address = $findSponsor->address;
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
            $findPayment = Payment::where('member_id', $findUser->id)->where('events_id', '1')->first();
            if (empty($findPayment)) {
                $findPayment = new Payment();
            }


            $findPayment->member_id = $findUser->id;
            $findPayment->package = 'Sponsors';
            $findPayment->code_payment = $codePayment;
            $findPayment->link = null;
            $findPayment->events_id = 1;
            $findPayment->tickets_id = 1;
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
                'events_name' => 'Mineral Trends 2023',
                'image' => $db
            ];
            $email =  $request->email[$key];

            $pdf = Pdf::loadView('email.ticket', $data);
            Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject($codePayment . ' - Your registration is approved for Mineral Trends 2023');
                $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
            });
        }
        return redirect()->back()->with('alert', 'Successfully Registering Sponsor');
    }

    public function register_multiple(Request $request)
    {
        // dd($request->all());
        $startcount = 0;
        $ticket = 0;
        $count_ticket = 0;
        $response = [];
        $ticketfinal = 0;
        $id = [];
        foreach ($request->name as $key => $value) {
            $uname = strtoupper(Str::random(7));
            $code_payment = strtoupper(Str::random(7));
            $checkUser = User::where('email', $request->email[$key])->first();
            if (!empty($checkUser)) {
                $count_ticket++;
                $ticket = 900000;
                $ticketfinal += 900000;
                $status = 1;
                $bool = 'Member';
            } else {
                $status = 2;
                $ticket = 1000000;
                $ticketfinal += 1000000;
                $bool = 'Non-Member';
            }
            $response[] = [
                'name' => $request->name[$key],
                'email' => $request->email[$key],
                'status' => $status,
                'ticket' => $ticket,
                'bool' => $bool,
                'job_title' => $request->job_title[$key]
            ];

            $findUser = User::firstOrNew(array('email' => $request->email[$key]));
            $findUser->name = $request->name[$key];
            $findUser->email = $request->email[$key];
            $findUser->password = Hash::make('DMC2023');
            $findUser->uname = $uname;
            $findUser->save();

            $id[] = [
                'id' => $findUser->id,
                'code_payment' => $code_payment
            ];
            $id_final = $id[0]['id'];
            $code_payment_final = $id[0]['code_payment'];
            $string_office = $request->office_number;
            $office_number = preg_replace('/[^0-9]/', '', $string_office);
            $firstTwoDigits_office = substr($string_office, 1, 3);
            $phone_office = substr($office_number, 2);

            $findCompany = CompanyModel::where('users_id', $findUser->id)->first();
            if (empty($findCompany)) {
                $findCompany = new CompanyModel();
            }
            $findCompany->prefix = $request->prefix;
            $findCompany->company_name = $request->company_name;
            $findCompany->office_number = $phone_office;
            $findCompany->prefix_office_number = $firstTwoDigits_office;
            $findCompany->full_office_number = $office_number;
            if ($request->company_other == 'Other') {
                $findCompany->company_other = $request->company_other;
            }
            $findCompany->company_category = $request->company_category;
            $findCompany->address = $request->address;
            $findCompany->city = $request->city;
            $findCompany->country = $request->country;
            $findCompany->portal_code = $request->portal_code;
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

            $findPayment = Payment::where('member_id', $findUser->id)->where('events_id', '1')->first();
            if (empty($findPayment)) {
                $findPayment = new Payment();
            }


            $findPayment->member_id = $findUser->id;
            $findPayment->package = $bool;
            $findPayment->code_payment = $code_payment;
            $findPayment->payment_method = 'CREDIT_CARD';
            $findPayment->link = null;
            $findPayment->events_id = 1;
            $findPayment->tickets_id = 1;
            $findPayment->status_registration = 'Waiting';
            $findPayment->groupby_users_id = $id_final;
            $findPayment->save();

            // code untuk company dan profile
            $startcount++;
        }
        // init xendit
        $isProd = env('XENDIT_ISPROD');
        if ($isProd) {
            $secretKey = env('XENDIT_SECRET_KEY_PROD');
        } else {
            $secretKey = env('XENDIT_SECRET_KEY_TEST');
        }
        // params invoice
        Xendit::setApiKey($secretKey);

        $item_details = [];
        foreach ($response as $key => $data) {
            $item_details[] = [
                'id' => 'item' . ($key + 1),
                'name' => $data['name'],
                'price' => number_format($data['ticket'], 0, ',', '.'),
                'quantity' => 1,
                'job_title' => $data['job_title']
            ];
        }
        $params = [
            'external_id' => $code_payment_final,
            'payer_email' => $findUser->email,
            'description' => 'Invoice Event DMC',
            'amount' => $ticketfinal,
            'success_redirect_url' => 'https://djakarta-miningclub.com',
            'failure_redirect_url' => url('/'),
            'callback_url' => 'http://127.0.0.1:8000/balakutakdicabean',
            'item_details' => $item_details
        ];
        $createInvoice = Invoice::create($params);
        $linkPay = $createInvoice['invoice_url'];
        $response['link'] = $linkPay;
        $firstUsers = User::where('users.id', $id_final)
            ->join('profiles', 'profiles.users_id', 'users.id')
            ->join('company', 'company.users_id', 'users.id')
            ->first();
        $payload = [
            'code_payment' => $code_payment_final,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $findUser->name,
            'users_email' => $findUser->email,
            'phone' => $firstUsers->fullphone,
            'company_name' => $firstUsers->company_name,
            'company_address' => $firstUsers->company_address,
            'status' => 'Waiting',
            'item' => $item_details,
            'price' => '3424242',
            'voucher_price' => 0,
            'total_price' => number_format($ticketfinal, 0, ',', '.'),
            'link' => $linkPay
        ];

        $saveLink = Payment::where('code_payment', $code_payment_final)->first();
        $saveLink->link = $linkPay;
        $saveLink->save();
        // return view('email.invoice-new-multiple', $payload);
        $send = new EmailSender();
        $send->to = $firstUsers->email;
        $send->from = env('EMAIL_SENDER');
        $send->data = $payload;
        $send->subject = 'Invoice - Waiting for Payment';
        $send->template = 'email.invoice-new-multiple';
        $send->sendEmail();
        return redirect()->back()->with('alert', 'The invoice will be sent to the email address ' . $firstUsers->email);
    }

    public function request(Request $request)
    {
        $id = $request->id;
        $val = $request->val;
        $db = null;
        $update = Payment::where('id', $id)->first();
        if (!empty($update)) {
            $check = DB::table('payment')
                ->join('users', 'users.id', 'payment.member_id')
                ->join('company', 'company.users_id', 'users.id')
                ->join('profiles', 'profiles.users_id', 'users.id')
                ->select('payment.*', 'users.*', 'payment.id as payment_id', 'profiles.*', 'company.*')
                ->where('payment.id', '=', $id)
                ->first();
            if ($val == 'approve') {
                $update->status_registration = "Paid Off";
                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($check->code_payment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $update->qr_code = $db;
            } else {
                $update->status_registration = "Reject";
            }
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
                'events_name' => 'Djakarta Mining Club Mineral Trends 2023',
                'image' => $db
            ];
            $email = $check->email;
            $code_payment = $check->code_payment;
            if ($val == 'approve') {
                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $code_payment) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($code_payment . ' - Your registration is approved for Mineral Trends 2023');
                    $message->attachData($pdf->output(), $code_payment . '-' . time() . '.pdf');
                });
                return redirect()->back()->with('success', 'Successfully Approval');
            } else {
                $send = new EmailSender();
                $send->from = env('EMAIL_SENDER');
                $send->to = $email;
                $send->data = $data;
                $send->subject = '[FULLY BOOKED] Mineral Trends 2023';
                $send->name = $check->name;
                $send->template = 'email.reject-event';
                $send->sendEmail();
                return redirect()->back()->with('success', 'Successfully Reject Register');
            }
            // $pdf = Pdf::loadView('email.ticket', $data);
            // return $pdf->stream();

        } else {
            dd("Payment not found");
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);
        $the_file = $request->file('uploaded_file');
        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range    = range(2, $row_limit);
            $column_range = range('M', $column_limit);
            $startcount = 1;
            $data = array();
            foreach ($row_range as $row) {
                $user = MemberModel::firstOrNew(array('email' => $sheet->getCell('E' . $row)->getValue()));
                $user->company_name = $sheet->getCell('A' . $row)->getValue();
                $user->name = $sheet->getCell('B' . $row)->getValue();
                $user->job_title = $sheet->getCell('C' . $row)->getValue();
                $user->phone = $sheet->getCell('D' . $row)->getValue();
                $user->email = $sheet->getCell('E' . $row)->getValue();
                $user->company_website = $sheet->getCell('F' . $row)->getValue();
                $user->company_category = $sheet->getCell('G' . $row)->getValue();
                $user->company_other = $sheet->getCell('H' . $row)->getValue();
                $user->address = $sheet->getCell('I' . $row)->getValue();
                $user->city = $sheet->getCell('J' . $row)->getValue();
                $user->portal_code = $sheet->getCell('K' . $row)->getValue();
                $user->office_number = $sheet->getCell('L' . $row)->getValue();
                $user->register_as = $sheet->getCell('M' . $row)->getValue();
                $user->save();
                $codePayment = strtoupper(Str::random(7));
                $payment = Payment::firstOrNew(array('member_id' => $user->id));
                $payment->member_id = $user->id;
                $payment->package = 'free';
                $payment->code_payment = $codePayment;
                $payment->price = 0;
                $payment->status = 'Waiting';
                $payment->save();
                $startcount++;
            }
            return back()->with('success', 'Success Import ' . $startcount . ' data');
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }

    public function dataCheck(Request $request)
    {
        $id = $request->nama;
        $ticket = $request->ticket;
        $pilihan = $request->pilihan;
        $slug  = $request->event;
        $code_payment = strtoupper(Str::random(7));
        $findUsers = User::where('users.id', $id)
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftjoin('company', 'company.users_id', 'users.id')
            ->select('users.id as users_id', 'users.*', 'company.*', 'profiles.*')
            ->first();
        $findEvent = Events::where('slug', $slug)->first();
        $findPayment = Payment::where('member_id', $id)->where('events_id', $findEvent->id)->first();
        if ($findUsers) {
            if (empty($findPayment)) {

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
                $save->tickets_id = 6; // perlu di dinamisin
                $save->status_registration = 'Paid Off';
                $save->qr_code = $db;
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
                        'events_name' => 'Mineral Trends 2023',
                        'image' => $db
                    ];
                    $email = $findUsers->email;
                    // dd("sukses");
                    ini_set('max_execution_time', 300);

                    $pdf = Pdf::loadView('email.ticket', $data);
                    Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $code_payment) {
                        $message->from(env('EMAIL_SENDER'));
                        $message->to($email);
                        $message->subject($code_payment . ' - Your registration is approved for Mineral Trends 2023');
                        $message->attachData($pdf->output(), $code_payment . '-' . time() . '.pdf');
                    });
                }
                return back()->with('success', 'Success add peserta');
            } else {
                return back()->with('error', 'Peserta sudah ada mendaftarkan diri');
            }
        }
    }

    public function regisInvitation(Request $request)
    {
        // dd($request->all());
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

        $user = User::firstOrNew(
            ['email' =>  $email],
        );
        $user->name = $name;
        $user->email = $email;
        $user->save();

        $company = CompanyModel::firstOrNew([
            'users_id' => $user->id
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
            $total_price  = 0;
        }
        $codePayment = strtoupper(Str::random(7));
        $date = date('d-m-Y H:i:s');
        $linkPay = null;
        if ($paymentMethod != 'free') {
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
        $check = Payment::where('events_id', '=', '1')->where('member_id', '=', $user->id)->first();

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
            'events_name' => 'Mineral Trends 2023',
            'price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => $linkPay
        ];

        if (empty($check)) {
            $payment = Payment::firstOrNew(['member_id' => $user->id]);
            if ($paymentMethod == 'free') {
                $payment->package = $paymentMethod;
                // $payment->price = $total_price;
                $payment->status_registration = 'Paid Off';
                $payment->code_payment = $codePayment;
                $payment->events_id = 1;
            } else {
                $payment->package = $paymentMethod;
                $payment->payment_method = 'Credit Card';
                $payment->status_registration = 'Waiting';
                $payment->link = $linkPay;
                $payment->code_payment = $codePayment;
                $payment->events_id = 1;
                if ($paymentMethod == 'member') {
                    $payment->tickets_id = 1;
                } else if ($paymentMethod == 'nonmember') {
                    $payment->tickets_id = 2;
                } else {
                    $payment->tickets_id = 6;
                }
            }
            $payment->save();
            if ($paymentMethod == 'free') {
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
                    'events_name' => 'Mineral Trends 2023',
                    'image' => $db
                ];
                // dd("sukses");
                ini_set('max_execution_time', 300);

                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($codePayment . ' - Your registration is approved for Mineral Trends 2023');
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
                return redirect()->back()->with('alert', 'Register Successfully');
            } else {
                // $pdf = Pdf::loadView('email.invoice-new', $data);
                Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject('Invoice - Waiting for Payment');
                    // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                });
                return redirect()->back()->with('alert', 'Check your email for payment Invoice !!!');
            }
        } else {
            return redirect()->back()->with('error', 'Email Already Register, please check your inbox for information event or create new email for registering')->withInput();
        }
    }
}
