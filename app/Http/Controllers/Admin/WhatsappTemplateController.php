<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $list = WhatsappTemplate::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list
        ];
        return view('admin.whatsapp.template', $data);
    }

    public function store(Request $request)
    {
        // Validate the request data, including the file upload if needed
        $request->validate([
            'text' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the allowed file types and size as needed
        ]);

        // Use updateOrCreate to insert a new record or update an existing one
        $WhatsappTemplate = WhatsappTemplate::updateOrCreate(
            [
                'id' => $request->input('id'),
                // Add other unique keys as needed
            ],
            [
                'text' => $request->input('text'),
                // Add other fields as needed
            ]
        );

        // Handle file upload if an image is provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();

            // Store the image in the storage directory
            $image->storeAs('public/template', $imageName);

            // Assuming you have an 'image' field in your database table
            $WhatsappTemplate->image = asset('storage/template/' . $imageName);
            $WhatsappTemplate->save();
        }
        // Assuming you want to return the newly created or updated record as a JSON response
        return response()->json($WhatsappTemplate);
    }

    public function edit($id)
    {
        $data = WhatsappTemplate::find($id);

        if (!$data) {
            // Handle the case where the record with the given id is not found
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Assuming you have a resource or transformer to format the data
        // If not, you can return the $data directly

        // For example, if you have a WhatsappTemplateResource:
        // return new WhatsappTemplateResource($data);

        // Or just return the data directly
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = WhatsappTemplate::find($id)->delete();
        return response()->json($data);
    }
}
