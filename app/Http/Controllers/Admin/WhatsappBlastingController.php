<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Http\Request;

class WhatsappBlastingController extends Controller
{
    //

    public function index()
    {
        //
    }

    public function sendWhatsAppMessagesAsync()
    {
        $dataToBeSent = [
            '083829314436',
            '083829314436',
        ];

        $delay = 0; // Set penundaan awal menjadi 1 menit

        foreach ($dataToBeSent as $data) {
            SendWhatsAppMessage::dispatch($data, 'Yudha Ganteng 1+1 menit')->delay(now()->addSeconds($delay));
            $delay += 60; // Tambahkan penundaan untuk pesan berikutnya
        }

        return response()->json(['message' => 'WhatsApp messages scheduled successfully']);
    }
}
