<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    public function index(Request $reqeuest)
    {
        $id =  auth('sanctum')->user()->id;
        $category = $reqeuest->category;
        $message = $reqeuest->message;
        $findUser = User::where('id', $id)->first();
        if (!empty($findUser)) {
            $data = [
                'name' => $findUser->name,
                'email' => $findUser->email,
                'category' => $category,
                'question' => $message,
            ];
            $this->saveInquiry([
                'source'     => ContactInquiry::SOURCE_APPS,
                'users_id'   => $findUser->id,
                'first_name' => $findUser->name,
                'email'      => $findUser->email,
                'phone'      => optional($findUser->profile)->fullphone,
                'category'   => $category,
                'message'    => $message,
            ]);
            $send = new EmailSender();
            $send->to = 'yudha@indonesiaminer.com';
            $send->from = env('EMAIL_SENDER');
            $send->data = $data;
            $send->subject = 'Contact Us DMC APPS';
            $send->template = 'email.contact-us';
            $send->sendEmail();
            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $send;
        } else {
            $response['status'] = 404;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function indexV2(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'comment' => 'nullable|string|max:1000',
        ], [
            'firstName.required' => 'First Name is required',
            'lastName.required' => 'Last Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'phone.required' => 'Phone is required',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['errors'] = $validator->errors();
            return response()->json($response);
        }

        // Jika validasi berhasil, lanjutkan dengan mengirim email
        $data = $request->only(['firstName', 'lastName', 'email', 'phone', 'comment']);

        $this->saveInquiry([
            'source'     => ContactInquiry::SOURCE_WEB,
            'users_id'   => optional(User::where('email', $data['email'])->first())->id,
            'first_name' => $data['firstName'],
            'last_name'  => $data['lastName'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'message'    => $data['comment'] ?? null,
        ]);

        $send = new EmailSender();
        $send->to = 'yudha@indonesiaminer.com';
        $send->from = env('EMAIL_SENDER');
        $send->data = $data;
        $send->subject = 'Contact Us DMC Web';
        $send->template = 'email.contact-us-web';
        $send->sendEmail();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $send;

        return response()->json($response);
    }

    /** Simpan inquiry ke DB; kalau gagal, email tetap terkirim seperti sebelumnya. */
    private function saveInquiry(array $attributes): void
    {
        try {
            ContactInquiry::create($attributes);
        } catch (\Throwable $e) {
            Log::error('Contact inquiry gagal disimpan: ' . $e->getMessage(), ['email' => $attributes['email'] ?? null]);
        }
    }
}
