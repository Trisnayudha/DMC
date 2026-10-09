<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // GET /api/lead-followup-reminders (API\LeadFollowupReminderController), polled by wa-gateway.
    // `since` is the go-live cutoff: only steps that started on/after it are ever reminded about,
    // so the existing overdue backlog doesn't flood the group. Left blank, the feed refuses to run.
    'lead_reminder' => [
        'key' => env('LEAD_REMINDER_API_KEY'),
        'since' => env('LEAD_REMINDER_SINCE'),
    ],

];
