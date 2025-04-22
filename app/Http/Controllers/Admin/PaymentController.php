<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Xendit\Invoice;
use Xendit\Xendit;

class PaymentController extends Controller
{
    public function index()
    {
        $payment = Payment::join('xtwp_users_dmc', 'xtwp_users_dmc.id', 'payment.member_id')->get();
        // dd($payment);
        $data = [
            'payment' => $payment
        ];
        return view('admin.payment.payment', $data);
    }

    public function renewal(Request $request)
    {
        try {
            $check = Payment::findOrFail($request->id);
            $findEvent = Events::where('id', $check->events_id)->first();
            $findUsers = User::findOrFail($check->member_id);
            $findProfile = ProfileModel::where('users_id', $check->member_id)->first();
            $findCompany = CompanyModel::where('users_id', $check->member_id)->first();
            $isProd = env('XENDIT_ISPROD');
            $secretKey = $isProd ? env('XENDIT_SECRET_KEY_PROD') : env('XENDIT_SECRET_KEY_TEST');
            Xendit::setApiKey($secretKey);
            $date = date('d, M Y H:i');
            $dueDate = date('d, M Y H:i', strtotime($date . ' +1 day'));
            if (!empty($check->booking_contact_id)) {
                $loop = Payment::where('booking_contact_id', $check->booking_contact_id)
                    ->where('payment.events_id', $check->events_id)
                    ->join('events_tickets', 'events_tickets.id', '=', 'payment.tickets_id')
                    ->select('payment.*', 'events_tickets.price_rupiah')
                    ->get();
                $totalPrice = 0;
                foreach ($loop as $index => $data) {
                    $subtotal = $data->price_rupiah;
                    if ($data->discount > 0) {
                        $subtotal -= $data->discount;
                    }
                    $totalPrice += $subtotal;
                    $updateStatus = Payment::where('id', $data->id)->first();
                    $updateStatus->status_registration = 'Waiting';
                    $updateStatus->save();
                }
                // Debug output, replace with Log::info or remove after testing
                $params = [
                    'external_id' => $check->code_payment,
                    'payer_email' => $findUsers->email,
                    'description' => 'Invoice Event DMC',
                    'amount' => $totalPrice,
                    'success_redirect_url' => 'https://djakarta-miningclub.com',
                    'failure_redirect_url' => url('/'),
                ];
                $createInvoice = Invoice::create($params);
                $linkPay = $createInvoice['invoice_url'];
                $data = [
                    'code_payment' => $check->code_payment,
                    'create_date' => $date,
                    'due_date' => $dueDate,
                    'users_name' => $findUsers->name,
                    'users_email' => $findUsers->email,
                    'phone' => $findProfile->phone,
                    'company_name' => $findCompany->company_name,
                    'company_address' => $findCompany->address,
                    'status' => 'WAITING',
                    'events_name' => $findEvent->name,
                    'price' => number_format($totalPrice, 0, ',', '.'),
                    'voucher_price' => 0,
                    'total_price' => number_format($totalPrice, 0, ',', '.'),
                    'link' => $linkPay,
                    'fva' => null,
                ];
            } else {
                $findTicket = EventsTicket::findOrFail($check->tickets_id);
                $subtotal = $findTicket->price_rupiah;
                if ($check->discount > 0) {
                    $subtotal -= $check->discount;
                }
                $total = $subtotal;
                $params = [
                    'external_id' => $check->code_payment,
                    'payer_email' => $findUsers->email,
                    'description' => 'Invoice Event DMC',
                    'amount' => $total,
                    'success_redirect_url' => 'https://djakarta-miningclub.com',
                    'failure_redirect_url' => url(''),
                ];
                $createInvoice = Invoice::create($params);
                $linkPay = $createInvoice['invoice_url'];


                $data = [
                    'code_payment' => $check->code_payment,
                    'create_date' => $date,
                    'due_date' => $dueDate,
                    'users_name' => $findUsers->name,
                    'users_email' => $findUsers->email,
                    'phone' => $findProfile->phone,
                    'company_name' => $findCompany->company_name,
                    'company_address' => $findCompany->address,
                    'status' => 'WAITING',
                    'events_name' => $findEvent->name,
                    'price' => number_format($total, 0, ',', '.'),
                    'voucher_price' => 0,
                    'total_price' => number_format($total, 0, ',', '.'),
                    'link' => $linkPay,
                    'fva' => null,
                ];
            }

            $email = $findUsers->email;

            $check->status_registration = 'Waiting';
            $check->link = $linkPay;
            $check->save();

            // Generate PDF outside of the request handling
            // $pdf = Pdf::loadView('email.invoice-new', $data);

            Mail::send('email.confirm_payment', $data, function ($message) use ($email) {
                $message->from(env('EMAIL_SENDER'));
                $message->to($email);
                $message->subject('Invoice - Waiting for Payment');
                // $message->attachData($pdf->output(), 'DMC-' . time() . '.pdf');
            });
            $send = new WhatsappApi();
            $send->phone = '081332178421';
            $send->message = 'Nih bro link renewalnya : ' . $linkPay;
            $send->WhatsappMessage();
            return redirect()->back()->with('success', 'Check your email for payment Invoice !!!');
        } catch (\Exception $e) {
            // Handle the exception
            return redirect()->back()->with('error', 'An error occurred. Please try again later.')->withErrors([$e->getMessage()]);
        }
    }
}
