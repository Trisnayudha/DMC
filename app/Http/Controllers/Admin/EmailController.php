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
        return view('admin.email.index');
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
                null,  // 6) tag
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
