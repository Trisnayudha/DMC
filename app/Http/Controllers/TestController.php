<?php

namespace App\Http\Controllers;

use App\Exports\DtiExport;
use App\Helpers\EmailSender;
use App\Helpers\Notification;
use App\Helpers\WhatsappApi;
use App\Models\BookingContact\BookingContact;
use App\Models\BusinessCard\BusinessCard;
use App\Models\Company\CompanyModel;
use App\Models\Contact;
use App\Models\Events\UserRegister;
use App\Models\Exhibitor;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\QrCode;
use Illuminate\Support\Str;
use Xendit\Invoice;
use Xendit\Xendit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{

    public function test()
    {
        // return view('test');
    }


    public function miningIndo()
    {
        return view('test');
    }

    // List id, name, pavilion dari file public/json/mining-indo.json
    public function miningIndoData(Request $request)
    {
        $path = public_path('json/electric.json');
        if (!file_exists($path)) {
            return response()->json(['message' => 'JSON file not found'], 404);
        }
        $json = json_decode(file_get_contents($path), true);
        $rows = $json['rows'] ?? [];

        // ambil hanya 3 kolom
        $rows = array_map(fn($r) => [
            'id'       => $r['id'] ?? null,
            'name'     => $r['name'] ?? '',
            'pavilion' => $r['pavilion'] ?? '',
        ], $rows);

        // optional search & pagination ringan
        $q   = trim((string) $request->get('q', ''));
        $per = max(1, min(100, (int)$request->get('per_page', 20)));
        $pg  = max(1, (int)$request->get('page', 1));

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $rows = array_values(array_filter(
                $rows,
                fn($r) =>
                mb_stripos(($r['name'] ?? '') . ' ' . ($r['pavilion'] ?? '') . ' ' . ($r['id'] ?? ''), $qq) !== false
            ));
        }

        $total = count($rows);
        $slice = array_slice($rows, ($pg - 1) * $per, $per);

        return response()->json([
            'page' => $pg,
            'per_page' => $per,
            'total' => $total,
            'total_page' => (int)ceil($total / $per),
            'rows' => $slice
        ]);
    }

    // Import detail 1 exhibitor by id dari API eksternal lalu simpan ke DB
    public function importExhibitor(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        $id = (int) $request->input('id');
        $url = "https://vexpo.iee-series.com/iee/pc/exhibitor/{$id}";

        // Ambil data dari API eksternal
        $resp = Http::timeout(20)->get($url);
        if (!$resp->ok()) {
            return response()->json(['ok' => false, 'message' => 'Fetch failed', 'status' => $resp->status()], 502);
        }

        $payload = $resp->json();
        $data = $payload['data'] ?? null;
        if (!$data || !isset($data['id'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid API response'], 422);
        }

        // Map field penting
        $mapped = [
            'id'           => (int)($data['id'] ?? $id),
            'name'         => $data['name']         ?? null,
            'country'      => $data['country']      ?? null,
            'desc'         => $data['desc']         ?? null,
            'category1'    => $data['category1']    ?? null,
            'category2'    => $data['category2']    ?? null,
            'website'      => $data['website']      ?? null,
            'contact'      => $data['contact']      ?? null,
            'contact_email' => $data['contactEmail'] ?? null,
            'venue_hall'   => $data['venueHall']    ?? null,
            'pavilion'     => $data['pavilion']     ?? null,
            'raw_json'     => $data, // simpan full untuk audit (optional)
        ];

        // Upsert
        Exhibitor::updateOrCreate(['id' => $mapped['id']], $mapped);

        return response()->json(['ok' => true, 'message' => 'Imported', 'id' => $mapped['id']]);
    }

    public function importExhibitorBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1'
        ]);

        $ids = array_values(array_unique($request->input('ids', [])));

        $ok = 0;
        $fail = 0;
        $results = [];

        foreach ($ids as $id) {
            try {
                $resp = \Illuminate\Support\Facades\Http::timeout(20)->get("https://vexpo.iee-series.com/iee/pc/exhibitor/{$id}");
                if (!$resp->ok()) {
                    $fail++;
                    $results[] = ['id' => $id, 'ok' => false, 'status' => $resp->status()];
                    continue;
                }
                $payload = $resp->json();
                $data = $payload['data'] ?? null;
                if (!$data || !isset($data['id'])) {
                    $fail++;
                    $results[] = ['id' => $id, 'ok' => false, 'status' => 'invalid'];
                    continue;
                }

                $mapped = [
                    'id'            => (int)($data['id'] ?? $id),
                    'name'          => $data['name'] ?? null,
                    'country'       => $data['country'] ?? null,
                    'desc'          => $data['desc'] ?? null,
                    'category1'     => $data['category1'] ?? null,
                    'category2'     => $data['category2'] ?? null,
                    'website'       => $data['website'] ?? null,
                    'contact'       => $data['contact'] ?? null,
                    'contact_email' => $data['contactEmail'] ?? null,
                    'venue_hall'    => $data['venueHall'] ?? null,
                    'pavilion'      => $data['pavilion'] ?? null,
                    'raw_json'      => $data,
                ];

                \App\Models\Exhibitor::updateOrCreate(['id' => $mapped['id']], $mapped);
                $ok++;
                $results[] = ['id' => $id, 'ok' => true];
            } catch (\Throwable $e) {
                $fail++;
                $results[] = ['id' => $id, 'ok' => false, 'status' => 'exception'];
            }
        }

        return response()->json([
            'ok' => true,
            'imported' => $ok,
            'failed' => $fail,
            'total' => count($ids),
            'results' => $results,
        ]);
    }


    public function testEmail()
    {
        // $send = Mail::send('email.test', [], function ($message) {
        //     $message->from(env('EMAIL_SENDER'));
        //     $message->to('yudha@indonesiaminer.com');
        //     $message->subject('IT DMC TEST SEND MESSAGE');
        // });
        $send = new  EmailSender();
        $send->template = 'email.test';
        $send->name_sender = 'Secretariat';
        $send->from = 'secretariat@djakarta-miningclub.com';
        // $send->to = 'ray.ratumbanua@mammothequip.co.id';
        // $send->to = 'yudha@indonesiaminer.com';
        $send->to = 'erina@djakarta-miningclub.com';
        $send->subject = 'IT DMC TEST SEND MESSAGE';
        $send->sendEmail();
        dd($send);
    }


    public function storeBusinessCard(Request $request)
    {
        // Validasi input, hanya email yang required
        $validatedData = $request->validate([
            'company' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'required|email|unique:business_card,email',
            'mobile' => 'nullable|string|max:15',
        ]);

        // Simpan data ke database
        $businessCard = new BusinessCard();
        $businessCard->company = $validatedData['company'] ?? null;
        $businessCard->name = $validatedData['name'] ?? null;
        $businessCard->job_title = $validatedData['job_title'] ?? null;
        $businessCard->email = $validatedData['email'];
        $businessCard->mobile = $validatedData['mobile'] ?? null;
        $businessCard->save();

        // Response sukses
        return response()->json([
            'message' => 'Business card successfully saved!',
            'data' => $businessCard
        ], 201);
    }

    public function collectAndStoreExhibitorData(Request $request)
    {
        // Allow the script to run for up to 10 minutes (600 seconds)
        // Ambil array rows dari request
        $rows = $request->input('rows', []);

        if (!is_array($rows) || empty($rows)) {
            return response()->json([
                'message'       => 'No data to save.',
                'success_count' => 0,
                'fail_count'    => 0,
            ], 422);
        }

        $now         = now();
        $totalRows   = count($rows);
        $dataToInsert = [];

        foreach ($rows as $item) {
            $dataToInsert[] = [
                'id'         => $item['ID'] ?? null,              // kalau pakai custom PK
                'username'   => $item['Username'] ?? null,
                'nama'       => $item['Nama'] ?? null,
                'lastname'   => $item['LastName'] ?? null,
                'email'      => $item['Email'] ?? null,
                'telepon'    => $item['Telepon'] ?? null,
                'reg_as'     => $item['RegAs'] ?? null,
                'job_title'  => $item['JobTitle'] ?? null,
                'job_level'  => $item['JobLevel'] ?? null,
                'job_function' => $item['JobFunction'] ?? null,
                'company'    => $item['Company'] ?? null,
                'country'    => $item['Country'] ?? null,
                'photo'      => $item['Photo'] ?? null,
                'linkedin'   => $item['Linkedin'] ?? null,
                'deleted_at' => $item['DeletedAt'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert dan abaikan duplikat unique (username/email)
        // insertOrIgnore() akan mengembalikan jumlah baris yang berhasil di-insert
        $inserted = DB::table('dti_users')->insertOrIgnore($dataToInsert);

        $successCount = $inserted;
        $failCount    = $totalRows - $inserted;

        return response()->json([
            'message'       => "Attempted to insert {$totalRows} rows.",
            'success_count' => $successCount,
            'fail_count'    => $failCount,
        ]);
    }

    public function fetchAndStoreContactData(Request $request)
    {
        // Mendapatkan IDs dari parameter 'ids' di query string
        $idsParam = $request->query('ids');

        if (!$idsParam) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

        $ids = explode(',', $idsParam);

        // Validasi bahwa $ids adalah array dan tidak kosong
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'Invalid or missing IDs provided'], 400);
        }

        // Mengatur batas waktu eksekusi
        set_time_limit(600);

        // Nilai cookie session Anda
        $sessionCookieValue = 'eyJpdiI6ImhZZ1JLcTczTk5aSTN3RFp2Q0NQdVE9PSIsInZhbHVlIjoibUVzbFIwZzFha0l0R3RVRkNHRWxzczVCaTczdU81Q0Y0VDA2S0w4emF0OFJnY2dxUmw3cHZqVjI0L3JTbGNiZHNSdU1hVzIzWlZLaS9CTEh3bWhxMitWdWZNMEJJUWUwMGZDQVBCVlFDMnhORTBpZlF0VGl2WE1QTnphUUlwUHMiLCJtYWMiOiIwZGY4ZWNlZWJkYjEwMWMzZDNiOTIyODJiYTNkNzczM2QwMmMxMWZiMDJjZGMyMzc3OGFjOTg0NGM0NmZlNThiIiwidGFnIjoiIn0%3D';

        foreach ($ids as $id) {
            $id = trim($id); // Menghilangkan spasi di awal/akhir ID

            // Melakukan permintaan GET ke API dengan menyertakan cookie session
            $response = Http::withCookies([
                'eventware_session' => $sessionCookieValue,
            ], '.v4.eventnetworking.com') // Menentukan domain cookie
                ->get("https://beacon.v4.eventnetworking.com/imarc-2024/contacts/{$id}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['contact'])) {
                    $contact = $data['contact'];

                    // Menyimpan data kontak ke database
                    Contact::updateOrCreate(
                        ['contact_id' => $contact['id']], // Memastikan kontak tidak duplikat
                        [
                            'display_name' => $contact['display_name'] ?? 'N/A',
                            'avatar_url' => $contact['avatar_url'] ?? null,
                            'bio' => $contact['bio'] ?? null,
                            'country_name' => $contact['country_name'] ?? null,
                            'flourish_text' => $contact['flourish_text'] ?? null,
                            'job_title' => $contact['job_title'] ?? null,
                            'company_display_name' => $contact['company']['display_name'] ?? null,
                        ]
                    );
                } else {
                    return response()->json(['error' => "Contact data not found for ID: {$id}"], 500);
                }
            } else {
                return response()->json(['error' => "Failed to fetch data for contact ID: {$id}"], 500);
            }
        }

        return response()->json(['message' => 'Contact data collected and stored successfully']);
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $searchArr = $request->get('search');

        $orderArr = !empty($columnIndex_arr) ? $columnIndex_arr : [['column' => 0, 'dir' => 'asc']];

        $columnIndex = $orderArr[0]['column'];
        $columnName = isset($columnName_arr[$columnIndex]) ? $columnName_arr[$columnIndex]['data'] : null;
        $columnSortOrder = $orderArr[0]['dir'];
        $searchValue = $searchArr['value'];

        // Log::info('DataTables Request Params:', $request->all()); // Aktifkan jika perlu debug

        // --- BACA DATA DARI FILE JSON ---
        $jsonPath = public_path('list_of_attendance_30_July.json');

        if (!file_exists($jsonPath)) {
            Log::error('JSON file not found: ' . $jsonPath);
            return response()->json([
                "draw" => intval($draw),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "JSON file not found. Please check the path."
            ]);
        }

        $fileContents = file_get_contents($jsonPath);
        $jsonData = json_decode($fileContents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error: ' . json_last_error_msg() . ' in file: ' . $jsonPath);
            return response()->json([
                "draw" => intval($draw),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Invalid JSON format in file."
            ]);
        }

        if (!is_array($jsonData)) {
            $jsonData = [$jsonData];
            // Log::info('JSON Data was a single object, converted to array. New count: ' . count($jsonData));
        }

        $allData = collect($jsonData);
        // Log::info('Total records in JSON file: ' . $allData->count());
        // ------------------------------------

        $totalRecords = $allData->count();

        // --- FILTER DATA ---
        $filteredData = $allData->filter(function ($item) use ($searchValue) {
            if (empty($searchValue)) {
                return true;
            }
            foreach ($item as $key => $value) {
                if (!in_array($key, ['ID', 'DeletedAt', 'Photo', 'Linkedin']) && stripos((string) $value, $searchValue) !== false) {
                    return true;
                }
            }
            return false;
        })->values();

        $totalRecordswithFilter = $filteredData->count();
        // Log::info('Filtered Data Count: ' . $totalRecordswithFilter);

        // --- SORTING ---
        if (!empty($filteredData) && $columnName && $columnName !== 'No.' && isset($columnName_arr[$columnIndex]) && $columnName_arr[$columnIndex]['orderable'] == 'true') {
            if ($filteredData->first() && array_key_exists($columnName, $filteredData->first())) {
                $filteredData = $filteredData->sortBy($columnName, SORT_REGULAR, ($columnSortOrder === 'desc'));
                // Log::info('Data sorted by ' . $columnName . ' ' . $columnSortOrder);
            } else {
                Log::warning('Column for sorting not found in data item: ' . $columnName);
            }
        }

        // --- PAGINATION ---
        $dataToSend = $filteredData->slice($start, $rowperpage)->values()->all();
        // Log::info('Returning paginated data (start: ' . $start . ', length: ' . $rowperpage . '). Count: ' . count($dataToSend));

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $dataToSend
        );

        return response()->json($response);
    }

    /**
     * Mengunduh semua data pengguna ke file Excel menggunakan Maatwebsite/Laravel-Excel.
     * Dinamai "dtiExport" sesuai permintaan.
     */
    public function dtiExport(Request $request)
    {
        // --- BACA DATA DARI FILE JSON (SELURUHNYA) UNTUK EKSPOR ---
        $jsonPath = public_path('list_of_attendance_30_July.json');

        if (!file_exists($jsonPath)) {
            Log::error('JSON file not found for export: ' . $jsonPath);
            return back()->with('error', 'File data tidak ditemukan untuk ekspor.');
        }

        $fileContents = file_get_contents($jsonPath);
        $jsonData = json_decode($fileContents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error for export: ' . json_last_error_msg() . ' in file: ' . $jsonPath);
            return back()->with('error', 'Format data JSON tidak valid untuk ekspor.');
        }

        // Jika JSON adalah objek tunggal, bungkus ke dalam array
        if (!is_array($jsonData)) {
            $jsonData = [$jsonData];
        }
        // ------------------------------------

        // Opsional: Implementasikan filter di sini jika Anda ingin ekspor hanya data yang
        // sedang difilter di tabel. Anda perlu mengirimkan parameter pencarian dari frontend
        // ke endpoint ini melalui URL atau AJAX. Untuk saat ini, kita ekspor semua data.

        $filename = 'dti_Export_Users_Data_' . date('Ymd_His') . '.xlsx';

        try {
            // Menggunakan UsersExport class untuk membuat file Excel
            return Excel::download(new DtiExport($jsonData), $filename);
        } catch (\Exception $e) {
            Log::error('Error exporting Excel via dtiExport: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data ke Excel. Error: ' . $e->getMessage());
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $codePayment = strtoupper(Str::random(7));
        $image = QrCode::format('png')
            ->size(300)->errorCorrection('H')
            ->generate($codePayment);
        $output_file = '/public/uploads/qr-code/img-' . time() . '.png';
        $db = '/storage/uploads/qr-code/img-' . time() . '.png';
        Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png



        // storage/app/images/file.png
        dd($db);
    }

    public function saveInvoice(Request $request)
    {
        $db = null;
        $data = [
            'users_name' => 'Yudha',
            'users_email' => 'yudha@indonesiaminer.com',
            'phone' => '083829314436',
            'company_name' => 'Indonesia Miner',
            'company_address' => 'Gg Samsi',
            'status' => 'Paid Off',
            'events_name' => 'Djakarta Mining Club and Coal Club Indonesia',
            'code_payment' => 'QZKdS8',
            'create_date' => date('d, M Y H:i'),
            'package_name' => 'Premium',
            'price' => number_format('1000000', 0, ',', '.'),
            'total_price' => number_format('1000000', 0, ',', '.'),
            'voucher_price' => number_format(0, 0, ',', '.'),
            'image' => $db,
            'job_title' => 'IT OFFICER'
        ];
        ini_set('max_execution_time', 300);
        $pdf = Pdf::loadView('email.invoice-new', $data);
        // Generate a unique filename for the PDF
        $filename = 'invoice_' . time() . '.pdf';

        // Store the PDF in the desired directory within the storage folder
        $pdfPath = 'public/invoice/' . $filename;
        $db = '/storage/invoice/' . $filename;
        Storage::put($pdfPath, $pdf->output());

        $send = new WhatsappApi();
        $send->phone = '083829314436';
        $send->document = asset($db);
        $send->WhatsappMessageWithDocument();
        dd($send);
    }
}
