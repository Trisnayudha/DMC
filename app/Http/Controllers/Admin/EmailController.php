<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email\PostmarkCallback;
use App\Models\User;
use Illuminate\Http\Request;
use Postmark\PostmarkClient;

class EmailController extends Controller
{
    protected $postmarkClient;
    protected $fromEmail;

    public function __construct()
    {
        // Pastikan variabel environment sesuai (misal, POSTMARK_API_KEY)
        $this->postmarkClient = new PostmarkClient(env('POSTMARK_API'));
        $this->fromEmail = 'register@djakarta-miningclub.com';
    }

    // Tampilkan halaman utama (Inbox + Compose Modal dalam 1 halaman)
    public function index()
    {
        $list = PostmarkCallback::orderBy('id', 'desc')
            ->get()
            ->unique(function ($item) {
                return $item->message_id . '-' . $item->record_type;
            });
        $data = [
            'list' => $list
        ];
        return view('admin.email.index', $data);
    }
    public function refreshInbox(Request $request)
    {
        // Ambil data email terbaru (urut berdasarkan id menurun)
        $emails = PostmarkCallback::orderBy('id', 'desc')
            ->get()
            ->unique(function ($item) {
                return $item->message_id . '-' . $item->record_type;
            });

        // Kembalikan data email sebagai JSON
        return response()->json([
            'status' => 'success',
            'emails' => $emails,
        ]);
    }

    public function deleteEmails(Request $request)
    {
        // Validasi input
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        // Mengambil array ID
        $ids = $request->input('ids');

        // Menghapus record dari tabel (misal menggunakan model PostmarkCallback)
        $deleted = PostmarkCallback::whereIn('id', $ids)->delete();

        if ($deleted) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Email berhasil dihapus!'
            ]);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus email.'
            ], 500);
        }
    }


    public function detailAjax($messageId)
    {
        $client = $this->postmarkClient;

        try {
            $messageDetails = $client->getOutboundMessageDetails($messageId);

            // Cast objek ke array, karena properti protected akan muncul dengan key khusus
            $detailsArray = (array) $messageDetails;

            // Cari key yang berisi "container"
            $containerKey = null;
            foreach (array_keys($detailsArray) as $key) {
                if (strpos($key, 'container') !== false) {
                    $containerKey = $key;
                    break;
                }
            }

            if (!$containerKey || !isset($detailsArray[$containerKey])) {
                throw new \Exception("Property container tidak ditemukan");
            }

            $container = $detailsArray[$containerKey];

            // Mapping field-field yang diperlukan
            $mapped = [
                'from'          => $container['from'] ?? null,
                'subject'       => $container['subject'] ?? null,
                'date'          => $container['receivedat'] ?? null,
                'to'            => $container['to'] ?? [],
                'messageevents' => $container['messageevents'] ?? [],
                'textbody'      => $container['textbody'] ?? null,
                'htmlbody'      => $container['htmlbody'] ?? null,
            ];

            return response()->json([
                'status'  => 'success',
                'details' => $mapped,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }

    // Proses pengiriman email via AJAX
    public function sendEmail(Request $request)
    {
        // 1. Validasi input: to (array email), subject, body, dsb.
        $request->validate([
            'to'            => 'required|array',         // misal: ["foo@bar.com", "john@doe.com"]
            'to.*'          => 'email',
            'subject'       => 'required|string',
            'body'          => 'required|string',
            'cc'            => 'nullable|array',
            'cc.*'          => 'email',
            'bcc'           => 'nullable|array',
            'bcc.*'         => 'email',
            'attachments.*' => 'nullable|file|max:10240', // max 10MB per file
        ]);

        // 2. Konversi array email => string dengan koma
        $to      = implode(',', $request->input('to'));
        $cc      = $request->has('cc')  ? implode(',', $request->input('cc'))  : null;
        $bcc     = $request->has('bcc') ? implode(',', $request->input('bcc')) : null;
        $subject = $request->input('subject');

        // 3. Body HTML & plain text
        $htmlBody = $request->input('body');
        $textBody = strip_tags($htmlBody);
        // dd($request->all());
        // 4. Siapkan attachments (opsional)
        $attachments = [];
        if ($request->hasFile('attachments')) {
            // Ambil data file
            $files = $request->file('attachments');

            // Jika $files bukan array (hanya 1 file), jadikan array agar foreach bisa jalan
            if (!is_array($files)) {
                $files = [$files];
            }
            // Sekarang $files pasti array
            foreach ($files as $file) {
                $fileContent = file_get_contents($file->getRealPath());
                $attachments[] = [
                    'Name'        => $file->getClientOriginalName(),
                    'Content'     => base64_encode($fileContent),
                    'ContentType' => $file->getMimeType(),
                ];
            }
        }
        try {
            // 5. Panggil Postmark (VERSI TANPA METADATA)
            //    Sesuaikan urutan argumen dengan versi library Anda.
            //    Contoh ini: (from, to, subject, htmlBody, textBody, tag, trackOpens, trackLinks, messageStream, cc, bcc, replyTo, headers, attachments)
            $this->postmarkClient->sendEmail(
                $request->fromEmail,
                $to,
                $subject,
                $htmlBody,
                $textBody,
                $subject,  // 6) tag
                null,  // 7) trackOpens
                null,  // 8) trackLinks
                $cc,   // 10) cc
                $bcc,  // 11) bcc
                [],    // 13) headers
                $attachments // 14) attachments
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Email berhasil dikirim!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showHistory($messageId)
    {
        // Ambil semua event Postmark yang punya message_id sama
        $events = PostmarkCallback::where('message_id', $messageId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.email.index', compact('events', 'messageId'));
    }


    // Endpoint callback Postmark (webhook)
    public function postmarkCallback(Request $request)
    {
        // Ambil seluruh payload
        $payload = $request->all();

        // Contoh pengambilan beberapa field kunci
        $recordType = $payload['RecordType'] ?? null;
        $messageId  = $payload['MessageID']  ?? null;
        $recipient  = $payload['Recipient']  ?? null;
        $tag        = $payload['Tag']        ?? null;

        // Metadata sering ditempatkan di $payload['Metadata']
        // tapi bisa saja null, jadi cek dulu
        $metadata   = isset($payload['Metadata']) ? $payload['Metadata'] : null;

        // Simpan ke DB
        PostmarkCallback::create([
            'record_type' => $recordType,
            'message_id'  => $messageId,
            'recipient'   => $recipient,
            'tag'         => $tag,
            'metadata'    => $metadata,            // array
            'payload'     => $payload,             // simpan semua
        ]);

        // Balas OK ke Postmark
        return response()->json(['status' => 'success'], 200);
    }
}
