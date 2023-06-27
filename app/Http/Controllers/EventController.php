<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Models\BookingContact\BookingContact;
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
use Carbon\Carbon;
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
                ->leftjoin('company', 'company.users_id', 'users.id')
                ->leftjoin('profiles', 'profiles.users_id', 'users.id')
                ->where('payment.events_id', $checkEvent->id)
                ->select('users.*', 'payment.*', 'company.*', 'profiles.*', 'payment.id as payment_id', 'payment.created_at as register', 'users.id as id')
                ->orderby('payment.created_at', 'desc')
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


    public function request(Request $request)
    {
        $id = $request->id;
        $val = $request->val;
        $db = null;
        $update = Payment::where('id', $id)->first();
        $findEvent = Events::where('id', $update->events_id)->first();
        // dd($findEvent);
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
                        'events_name' => $findEvent->name,
                        'start_date' => $findEvent->start_date,
                        'end_date' => $findEvent->end_date,
                        'start_time' => $findEvent->start_time,
                        'end_time' => $findEvent->end_time,
                        'image' => $db
                    ];
                    $email = $findUsers->email;
                    // dd("sukses");
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
            } else {
                return redirect()->route('events-details', ['slug' => $slug])->with('error', 'Peserta sudah ada mendaftarkan diri');
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
        //TODO masih hardcode
        $check = Payment::where('events_id', '=', '5')->where('member_id', '=', $user->id)->first();
        $findEvent = Events::where('id', '5')->first();
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
            'events_name' => 'The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia',
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
                $payment->events_id = 5;
            } else {
                $payment->package = $paymentMethod;
                $payment->payment_method = 'Credit Card';
                $payment->status_registration = 'Waiting';
                $payment->link = $linkPay;
                $payment->code_payment = $codePayment;
                $payment->events_id = 5;
                if ($paymentMethod == 'member') {
                    $payment->tickets_id = 1;
                } else if ($paymentMethod == 'nonmember') {
                    $payment->tickets_id = 2;
                } else {
                    $payment->tickets_id = 6;
                }
            }
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
                    'events_name' => 'The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia',
                    'image' => $db,
                    'start_date' => $findEvent->start_date,
                    'end_date' => $findEvent->end_date,
                    'start_time' => $findEvent->start_time,
                    'end_time' => $findEvent->end_time,
                ];
                // dd("sukses");
                ini_set('max_execution_time', 300);
                //TODO bakal ada bug
                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($codePayment . ' - Your registration is approved for The 10th Anniversary Djakarta Mining Club and Coal Club Indonesia');
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
                return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('alert', 'Register Successfully');
            } else {
                // $pdf = Pdf::loadView('email.invoice-new', $data);
                Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject('Invoice - Waiting for Payment');
                    // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                });
                return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('alert', 'Check your email for payment Invoice !!!');
            }
        } else {
            return redirect()->route('events-details', ['slug' => $findEvent->slug])->with('error', 'Email Already Register, please check your inbox for information event or create new email for registering')->withInput();
        }
    }

    public function detail_participant($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        $findParticipant = Payment::join('events', 'events.id', 'payment.events_id')
            ->join('users', 'users.id', 'payment.member_id')
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftjoin('company', 'company.users_id', 'users.id')
            ->leftJoin('users_event', function ($join) use ($findEvent) {
                $join->on('users_event.users_id', '=', 'payment.member_id')
                    ->where('users_event.events_id', '=', $findEvent->id);
            })
            ->where('payment.events_id', $findEvent->id)
            ->where('payment.status_registration', 'Paid Off')
            ->select(
                'users.id as users_id',
                'users.name',
                'users.email',
                'payment.code_payment',
                'payment.package',
                'profiles.job_title',
                'profiles.phone',
                'company.country',
                'company.address',
                'company.company_name',
                'company.prefix',
                'users_event.present',
                'users_event.created_at as created',
                'users_event.updated_at as updated',
                'events.id as events_id',
                'payment.created_at as payment_updated',
                'payment.id as payment_id',
                'events.start_date',
                'events.end_date',
                'company.company_category',
                'company.company_other'
            )
            ->orderBy('payment.id', 'desc')
            ->get();


        $data = [
            'list' => $findParticipant,
        ];
        return view('admin.events.event-detail-participant', $data);
    }

    public function sendParticipant(Request $request)
    {
        // dd($request->all());
        $users_id = $request->users_id;
        $events_id = $request->events_id;
        $method = $request->method;
        $payment_id = $request->payment_id;
        $check = Payment::where('id', $payment_id)->first();
        $findUsers = User::where('id', $check->member_id)->first();
        $findProfile = ProfileModel::where('users_id', $check->member_id)->first();
        $findCompany = CompanyModel::where('users_id', $check->member_id)->first();
        $findEvent = Events::where('id', $events_id)->first();
        $email = $findUsers->email;
        $codePayment = $check->code_payment;
        if ($method == 'confirmation') {
            $save = UserRegister::where('users_id', $users_id)->where('events_id', $events_id)->first();
            if ($save == null) {
                $save = new UserRegister();
            }
            $save->users_id = $users_id;
            $save->events_id = $events_id;
            $save->payment_id = $payment_id;
            $save->created_at = Carbon::now();
            $save->updated_at = null;
            $save->save();

            $image = QrCode::format('png')
                ->size(200)->errorCorrection('H')
                ->generate($check->code_payment);
            $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
            $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
            Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
            $findEvent = Events::where('id', $events_id)->first();
            $data = [
                'code_payment' => $check->code_payment,
                'create_date' => date('d, M Y H:i'),
                'users_name' => $findUsers->name,
                'users_email' => $findUsers->email,
                'phone' => $findProfile->phone,
                'company_name' => $findCompany->company_name,
                'company_address' => $findCompany->address,
                'job_title' => $findProfile->job_title,
                'events_name' => $findEvent->name,
                'start_date' => $findEvent->start_date,
                'end_date' => $findEvent->end_date,
                'start_time' => $findEvent->start_time,
                'end_time' => $findEvent->end_time,
                'image' => $db
            ];
            // dd("sukses");
            // ini_set('max_execution_time', 120);

            $pdf = Pdf::loadView('email.ticket', $data);
            Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject($codePayment . ' - Confirmation Reminder for ' . $findEvent->name);
                $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
            });
            return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('success', 'Successfully Send Confirmation');
        } else {
            $save = UserRegister::where('users_id', $users_id)->where('events_id', $events_id)->first();
            if ($save == null) {
                $save = new UserRegister();
            }
            $save->users_id = $users_id;
            $save->events_id = $events_id;
            $save->payment_id = $payment_id;
            $save->present = 1;
            $save->save();

            return redirect()->route('events-details-participant', ['slug' => $findEvent->slug])->with('success', 'Successfully Present');
        }
    }
}
