<?php

namespace App\Http\Controllers\API;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Events\UserRegister;
use App\Models\Ngrok\NgrokModel;
use App\Models\Payments\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;


class PrintController extends Controller
{
    public function scan(Request $request)
    {
        $check = Payment::where('code_payment', $request->input_text)->first();
        $nosave = $request->noscan;
        $ngrok  = $request->ngrok;
        $company_name = $request->company_name;
        $name = $request->name;
        if (!empty($check)) {
            $findUsers = User::where('users.id', $check->member_id)
                ->join('company', 'company.users_id', 'users.id')
                ->select('users.name', 'company.company_name')
                ->first();
            $data = [
                'name'          => $name ? $name : $findUsers->name,
                'company_name'  => $company_name ? $company_name : $findUsers->company_name,
                'package'       => $check->package,
            ];
            if ($ngrok) {
                $this->sendWebhook($ngrok, $data);
            }
            if ($nosave == 'false') {
                $save = UserRegister::where('payment_id', $check->id)->first();
                if (empty($save)) {
                    $save = new UserRegister();
                }

                $save->users_id  = $check->member_id;
                $save->events_id = $check->events_id;
                $save->payment_id = $check->id;
                $save->present   = Carbon::now();

                if ($request->hasFile('photo')) {
                    // Baca file yang diupload
                    $photo = $request->file('photo');

                    // Resize foto menggunakan Intervention Image
                    $resizedImage = Image::make($photo->getRealPath())->resize(3000, 3000, function ($constraint) {
                        $constraint->aspectRatio(); // Pertahankan aspek rasio
                        $constraint->upsize(); // Hindari membesarkan gambar kecil
                    });

                    // Simpan foto yang diresize
                    $path = 'photos/' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $resizedImage->save(storage_path('app/public/' . $path));

                    // Simpan path ke database
                    $save->photo = url('storage/' . $path);

                    // --- VALIDASI TAMBAHAN DI SINI ---
                    // Hanya kirim notifikasi jika package adalah 'Sponsor' atau 'Speaker'
                    if (in_array($check->package, ['sponsor', 'speaker'])) {
                        $notif = new WhatsappApi();
                        $notif->phone = '120363234928717023';

                        // Get the current check-in time
                        $checkInTime = Carbon::now()->format('H:i'); // Format: HH:MM (e.g., 15:30)

                        // Modify the WhatsApp message here
                        $message = "Update Check-in:\n" .
                            "*" . ($data['name'] ?? 'Unknown Participant') . "* (" . ($data['company_name'] ?? 'Unknown Company/Institution') . ")\n" .
                            "*[" . ($data['package'] ?? 'Unknown Package') . "]* has successfully checked in at *" . $checkInTime . "*!\n\n" .
                            "Photo: " . $save->photo;

                        $notif->message = $message;
                        $notif->WhatsappMessageGroup();
                    }
                    // --- AKHIR VALIDASI ---
                }

                $save->save();
                $data['photo'] = $save->photo;
            }
            $response['status']  = 1;
            $response['message'] = 'Success Scan QR Code';
            $response['data']    = $data;
        } else {
            $response['status']  = 0;
            $response['message'] = 'Qr Code tidak terdaftar di sistem';
            $response['data']    = null;
        }
        return response()->json($response);
    }

    public function delegateList(Request $request)
    {
        $limit = $request->limit ?? 5;
        $findUser = Payment::join('users', 'users.id', 'payment.member_id')
            ->join('events_tickets', 'events_tickets.id', 'payment.tickets_id')
            ->join('profiles', 'profiles.users_id', 'users.id')
            ->join('company', 'company.users_id', 'users.id')
            ->whereIn('payment.status_registration', ['Paid Off', 'free'])
            ->where('payment.events_id', 55)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('users.name', 'like', '%' . $request->search . '%')
                        ->orWhere('company.company_name', 'like', '%' . $request->search . '%');
                });
            })
            ->select(
                'payment.id',
                'payment.code_payment',
                'users.name',
                'profiles.job_title',
                'company.company_name',
                'payment.package'
            )
            ->paginate($limit);

        $response['status']  = 1;
        $response['message'] = 'Success show list delegate';
        $response['data']    = $findUser;
        return response()->json($response);
    }

    public function ngrokList()
    {
        $list = NgrokModel::get();
        $response['status']  = 1;
        $response['message'] = 'Success show list ngrok';
        $response['data']    = $list;
        return response()->json($response);
    }

    private function sendWebhook($url, $payload)
    {
        try {
            $response = Http::timeout(15)->post($url, $payload);

            if ($response->status() !== 200) {
                Log::error("Webhook failed with status: {$response->status()}, Response: {$response->body()}");
            }
        } catch (\Exception $e) {
            Log::error("Error sending webhook: " . $e->getMessage());
        }
    }
}
