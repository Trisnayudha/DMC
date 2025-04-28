<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\UserRegister;
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

        if (!empty($check)) {
            $findUsers = User::where('users.id', $check->member_id)
                ->join('company', 'company.users_id', 'users.id')
                ->first();

            $data = [
                'name'          => $findUsers->name,
                'company_name'  => $findUsers->company_name,
                'package'       => $check->package,
            ];

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
                }

                $save->save();
                $data['photo'] = $save->photo;
            }
            try {
                Http::post('https://df65-103-147-8-128.ngrok-free.app/webhook', [
                    'name' => $findUsers->name,
                    'company' => $findUsers->company_name,
                    'package' => $check->package,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send print webhook: ' . $e->getMessage());
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
}
