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
        $request->validate([
            'to'            => 'required|array',
            'to.*'          => 'email',
            'subject'       => 'required|string',
            'body'          => 'required|string',
            'cc'            => 'nullable|array',
            'cc.*'          => 'email',
            'bcc'           => 'nullable|array',
            'bcc.*'         => 'email',
            'attachments.*' => 'nullable|file|max:51200', // 50MB per file utk upload ke storage (bukan utk email)
        ]);

        // === Konstanta batas Postmark ===
        $POSTMARK_MAX = 10 * 1024 * 1024; // 10 MB total
        $BODY_BUFFER  = 512 * 1024;       // 0.5 MB buffer utk body & header
        $MAX_ATTACH_BYTES_BASE64 = $POSTMARK_MAX - $BODY_BUFFER;

        // Ambil file
        $files = $request->file('attachments', []);
        if (!is_array($files)) $files = [$files];

        // Hitung ukuran base64 (base64 ~ naik 4/3; rumus pasti: 4 * ceil(n/3))
        $totalBase64Bytes = 0;
        foreach ($files as $f) {
            if (!$f) continue;
            $n = $f->getSize();
            $totalBase64Bytes += 4 * ceil($n / 3);
        }

        $useLinksInstead = $totalBase64Bytes > $MAX_ATTACH_BYTES_BASE64;

        // Siapkan body
        $htmlBody = $request->input('body');
        $textBody = strip_tags($htmlBody);

        // Siapkan recipients
        $to  = implode(',', $request->input('to'));
        $cc  = $request->has('cc')  ? implode(',', $request->input('cc'))  : null;
        $bcc = $request->has('bcc') ? implode(',', $request->input('bcc')) : null;

        // Siapkan attachments atau links
        $attachments = [];

        if (!$useLinksInstead && !empty($files)) {
            foreach ($files as $file) {
                if (!$file) continue;
                $attachments[] = [
                    'Name'        => $file->getClientOriginalName(),
                    'Content'     => base64_encode(file_get_contents($file->getRealPath())),
                    'ContentType' => $file->getMimeType(),
                ];
            }
        } else if (!empty($files)) {
            // === Opsi A: simpan ke storage 'public' (cocok di cPanel) ===
            // Pastikan sudah: php artisan storage:link  => /public/storage
            $links = [];
            foreach ($files as $file) {
                if (!$file) continue;
                $path = $file->store('email-uploads', ['disk' => 'public']);
                $links[] = asset('storage/' . $path);
            }

            // === Opsi B: kalau pakai S3, ganti 3 baris di atas dengan:
            // $path = $file->store('email-uploads', ['disk' => 's3']);
            // $links[] = Storage::disk('s3')->temporaryUrl($path, now()->addDays(3));

            // Sisipkan daftar link di body
            $htmlBody .= '<hr><p><strong>Attachments:</strong><br>'
                . implode('<br>', array_map(fn($u) => "<a href=\"$u\">$u</a>", $links)) . '</p>';
            $textBody .= "\n\nAttachments:\n" . implode("\n", $links);
        }

        try {
            // Kirim ke Postmark
            $this->postmarkClient->sendEmail(
                $request->fromEmail,           // from
                $to,                           // to
                $request->subject,             // subject
                $htmlBody,                     // htmlBody
                $textBody,                     // textBody
                $request->subject,             // tag (boleh ganti)
                null,                          // trackOpens
                null,                          // trackLinks
                $cc,                           // cc
                $bcc,                          // bcc
                [],                            // headers
                $attachments                   // attachments (kosong kalau pakai link)
            );

            return response()->json(['status' => 'success', 'message' => 'Email berhasil dikirim!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
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
