<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappCampaign;
use App\Models\Whatsapp\WhatsappDB;
use Illuminate\Http\Request;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhatsappDBController extends Controller
{
    public function index()
    {
        $list = WhatsappDB::leftJoin('wa_campaign', 'wa_campaign.id', 'wa_db.wa_camp_id')->select('wa_db.*', 'wa_campaign.name as camp_name')->orderby('wa_db.id', 'desc')->get();
        $camp = WhatsappCampaign::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list,
            'camp' => $camp
        ];
        return view('admin.whatsapp.db', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $the_file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range = range(2, $row_limit);
            $column_range = range('E', $column_limit);

            $data = [];

            foreach ($row_range as $row) {
                $WhatsappDB = WhatsappDB::updateOrCreate(
                    [
                        'id' => $request->input('id'),
                        // Add other unique keys as needed
                    ],
                    [
                        'name' => $sheet->getCell('A' . $row)->getValue(),
                        'job_title' => $sheet->getCell('B' . $row)->getValue(),
                        'phone' => $sheet->getCell('C' . $row)->getValue(),
                        'company_name' => $sheet->getCell('D' . $row)->getValue(),
                        'wa_camp_id' => $request->input('camp_id')
                    ]
                );

                $data[] = $WhatsappDB->toArray();
            }

            return response()->json(['data' => $data]);
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return response()->json(['error' => 'There was a problem uploading the data! Error Code: ' . $error_code], 500);
        }
    }


    public function edit($id)
    {
        $data = WhatsappDB::find($id);

        if (!$data) {
            // Handle the case where the record with the given id is not found
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Assuming you have a resource or transformer to format the data
        // If not, you can return the $data directly

        // For example, if you have a WhatsappDBResource:
        // return new WhatsappDBResource($data);

        // Or just return the data directly
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = WhatsappDB::find($id)->delete();
        return response()->json($data);
    }
}
