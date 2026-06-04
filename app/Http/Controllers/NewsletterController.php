<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Newsletter\NewsletterFacade as Newsletter;

class NewsletterController extends Controller
{
    public function show()
    {
        return view('newsletter.subscribe');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
        ]);

        $email     = $request->input('email');
        $firstName = $request->input('first_name');
        $lastName  = $request->input('last_name');

        if (Newsletter::isSubscribed($email)) {
            return back()->with('info', 'This email is already subscribed to our newsletter.');
        }

        $mergeFields = [];
        if ($firstName) $mergeFields['FNAME'] = $firstName;
        if ($lastName)  $mergeFields['LNAME']  = $lastName;

        $result = Newsletter::subscribeOrUpdate($email, $mergeFields);

        if (!$result) {
            $error = Newsletter::getLastError();
            return back()->withInput()->with('error', 'Subscription failed. Please try again. ' . $error);
        }

        return back()->with('success', 'Thank you for subscribing! Please check your email to confirm.');
    }
}
