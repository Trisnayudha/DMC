<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ladumor\OneSignal\OneSignal;

class NotifController extends Controller
{
    public function index()
    {
        $fields['include_player_ids'] = ['xxxxxxxx-xxxx-xxx-xxxx-yyyyyyyyy'];
        $message = 'hey!! this is test push.!';

        $var = OneSignal::sendPush($fields, $message);

        dd($var);
    }
}
