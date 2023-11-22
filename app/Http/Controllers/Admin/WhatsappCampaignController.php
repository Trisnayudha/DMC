<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappCampaign;
use Illuminate\Http\Request;

class WhatsappCampaignController extends Controller
{
    public function index()
    {
        $list = WhatsappCampaign::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list
        ];
        return view('admin.whatsapp.campaign', $data);
    }

    public function store(Request $request)
    {
        // Use updateOrCreate to insert a new record or update an existing one
        $whatsappCampaign = WhatsappCampaign::updateOrCreate(
            [
                'id' => $request->input('id'),
                // Add other unique keys as needed
            ],
            [
                'name' => $request->input('name'),
                'date' => $request->input('date'),
                // Add other fields as needed
            ]
        );

        // Assuming you want to return the newly created or updated record as a JSON response
        return response()->json($whatsappCampaign);
    }

    public function edit($id)
    {
        $data = WhatsappCampaign::find($id);

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
        $data = WhatsappCampaign::find($id)->delete();
        return response()->json($data);
    }
}
