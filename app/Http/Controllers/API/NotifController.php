<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ladumor\OneSignal\OneSignal;

class NotifController extends Controller
{
    public function index()
    {

        $notif = new Notification();
        $notif->id = 3;
        $notif->message = 'YUDHA TAMVAN SEKALI 123';
        $notif->NotifApp();
        return 'succes';
    }
}
