<?php

namespace App\Http\Controllers;

use App\Helpers\EmailSender;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\MemberModel;
use App\Models\Payments\Payment;
use App\Models\Sponsors\Sponsor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Xendit\Invoice;
use Xendit\Xendit;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        return view('admin.events.create');
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

        $user = MemberModel::firstOrNew(
            ['email' =>  $email],
        );
        $user->prefix = $prefix;
        $user->phone = $phone;
        $user->company_name = $company_name;
        $user->job_title = $job_title;
        $user->name = $name;
        $user->company_website = $company_website;
        $user->company_other = $company_other;
        $user->company_category = $company_category;
        $user->address = $address;
        $user->country = $country;
        $user->city = $city;
        $user->office_number = $office_number;
        $user->portal_code = $portal_code;
        $user->register_as = 'Events';
        $user->save();

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
        $check = MemberModel::where('email', $email)->join('payment', 'payment.member_id', 'xtwp_users_dmc.id')->first();

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
            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia x McCloskey by OPIS',
            'price' => number_format($total_price, 0, ',', '.'),
            'voucher_price' => 0,
            'total_price' => number_format($total_price, 0, ',', '.'),
            'link' => $linkPay
        ];

        if (empty($check)) {
            $payment = Payment::firstOrNew(['member_id' => $user->id]);
            if ($paymentMethod == 'free') {
                $payment->package = $paymentMethod;
                $payment->price = $total_price;
                $payment->status = 'Waiting';
                $payment->code_payment = $codePayment;
                // $payment->link = null;
            } else {
                $payment->package = $paymentMethod;
                $payment->price = $total_price;
                $payment->status = 'Waiting';
                $payment->link = $linkPay;
                $payment->code_payment = $codePayment;
            }
            $payment->save();
            if ($paymentMethod == 'free') {
                $send = new EmailSender();
                $send->to = $email;
                $send->from = env('EMAIL_SENDER');
                $send->data = $data;
                $send->subject = 'Thank you for registering Energy Market Briefing 2022 ';
                $send->template = 'email.waiting-approval';
                $send->sendEmail();
                return redirect()->back()->with('alert', 'Register Successfully');
            } else {
                // $pdf = Pdf::loadView('email.invoice-new', $data);
                Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject('Invoice Events - Payment');
                    // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
                });
                return redirect()->back()->with('alert', 'Check your email for payment Invoice !!!');
            }
        } else {
            return redirect()->back()->with('error', 'Email Already Register, please check your inbox for information event or create new email for registering')->withInput();
        }


        // if ($paymentMethod == 'free') {
        //     $user_event = UserRegister::firstOrNew(
        //         ['users_id' =>  $user->id],
        //         ['payment_id' => $payment->id]
        //     );
        //     $user_event->save();
        // }
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
            $create = MemberModel::create([
                'sponsor_id' => $request->company,
                'company_name' => $findSponsor->name,
                'address' => $findSponsor->address,
                'office_number' => $findSponsor->office_number,
                'company_website' => $findSponsor->company_website,
                'name' => $request->name[$key],
                'phone' => $request->phone[$key],
                'job_title' => $request->job_title[$key],
                'email' => $request->email[$key],
                'register_as' => 'Event-Sponsor'
            ]);
            $codePayment = strtoupper(Str::random(7));
            Payment::create([
                'member_id' => $create->id,
                'package' => 'free',
                'price' => 0,
                'code_payment' => $codePayment,
                'status' => 'Waiting'
            ]);
        }
        return redirect()->back()->with('alert', 'Successfully Registering as Sponsor');
    }

    public function request(Request $request)
    {
        // dd($request->all());
        $id = $request->id;
        $val = $request->val;
        $update = Payment::where('id', $id)->first();
        if (!empty($update)) {
            $check = DB::table('payment')
                ->join('xtwp_users_dmc', 'xtwp_users_dmc.id', 'payment.member_id')
                ->select('payment.*', 'xtwp_users_dmc.*', 'payment.id as payment_id')
                ->where('payment.id', '=', $id)
                ->first();
            if ($val == 'approve') {
                $update->status = "Approve";
                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($check->code_payment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png
                $update->qr_code = $db;
            } else {
                $update->status = "Reject";
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
                'events_name' => 'Djakarta Mining Club and Coal Club Indonesia x McCloskey by OPIS',
                'image' => $db
            ];
            $email = $check->email;
            $code_payment = $check->code_payment;
            $pdf = Pdf::loadView('email.ticket', $data);
            Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $code_payment) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject($code_payment . ' - Your registration is approved for Energy Market Briefing 2022');
                $message->attachData($pdf->output(), $code_payment . '-' . time() . '.pdf');
            });
            // $pdf = Pdf::loadView('email.ticket', $data);
            // return $pdf->stream();
            return redirect()->back()->with('alert', 'Successfully Approval');
        } else {
            dd("Payment not found");
        }
    }
}
