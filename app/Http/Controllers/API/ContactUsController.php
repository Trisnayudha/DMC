<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
}
