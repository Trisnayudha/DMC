<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Whatsapp\WhatsappBlast;
use App\Models\Whatsapp\WhatsappCampaign;
use App\Models\Whatsapp\WhatsappDB;
use App\Models\Whatsapp\WhatsappSender;
use App\Models\Whatsapp\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappBlastingController extends Controller
{
    //

    public function index()
    {
        $list = WhatsappBlast::orderby('id', 'desc')->get();
        $camp = WhatsappCampaign::orderby('id', 'desc')->get();
        $temp = WhatsappTemplate::orderby('id', 'desc')->get();
        $send = WhatsappSender::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list,
            'camp' => $camp,
            'temp' => $temp,
            'send' => $send
        ];
        return view('admin.whatsapp.blasting', $data);
    }

    public function store(Request $request)
    {
        $camp_id = $request->input('camp_id');
        $wa_temp_id = $request->input('temp_id');
        $wa_sender_id = $request->input('send_id');

        // Validate input data
        if ($camp_id == '' || $wa_temp_id == '' || $wa_sender_id == '') {
            return response()->json(['error' => 'Invalid input data']);
        }

        $callDB = WhatsappDB::where('wa_camp_id', $camp_id)->get();
        $getMessage = WhatsappTemplate::find($wa_temp_id);

        foreach ($callDB as $dbData) {
            $dataToBeSent = [$dbData->phone];

            $delay = 0; // Set initial delay to 0 seconds

            foreach ($dataToBeSent as $phoneNumber) {
                // Check if phone number is not empty
                if (!empty($phoneNumber)) {
                    $res = SendWhatsAppMessage::dispatch($phoneNumber, $getMessage->text)->delay(now()->addSeconds($delay));
                    $delay += 30; // Add delay for the next message
                }
            }

            // Use a unique identifier other than 'id' if possible
            $WhatsappBlast = WhatsappBlast::create([
                'wa_db_id' => $dbData->id,
                'wa_temp_id' => $wa_temp_id,
                'wa_sender_id' => $wa_sender_id,
                'status' => 'success',
                // Add other fields as needed
            ]);
        }

        return response()->json(['message' => 'WhatsApp messages scheduled successfully']);
    }


    public function edit($id)
    {
        $data = WhatsappBlast::find($id);

        if (!$data) {
            // Handle the case where the record with the given id is not found
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Assuming you have a resource or transformer to format the data
        // If not, you can return the $data directly

        // For example, if you have a WhatsappCampaignResource:
        // return new WhatsappCampaignResource($data);

        // Or just return the data directly
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = WhatsappBlast::find($id)->delete();
        return response()->json($data);
    }

    public function sendWhatsAppMessagesAsync()
    {
        $dataToBeSent = [
            '083829314436',
            '083829314436',
        ];

        $delay = 0; // Set penundaan awal menjadi 1 menit
        $message = "Hai Yudha\nPerkenalkan saya Jason\nhahaha";

        foreach ($dataToBeSent as $data) {
            SendWhatsAppMessage::dispatch($data, $message)->delay(now()->addSeconds($delay));
            $delay += 60; // Tambahkan penundaan untuk pesan berikutnya
        }

        return response()->json(['message' => 'WhatsApp messages scheduled successfully']);
    }
}
