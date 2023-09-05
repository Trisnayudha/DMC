<?php

namespace App\Http\Controllers;

use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SponsorController extends Controller
{
    public function index()
    {
        $data = Sponsor::orderBy('id', 'desc')->get();
        return view('admin.sponsor.sponsor', ['data' => $data]);
        // Show a list of tasks
    }

    public function create()
    {
        return view('admin.sponsor.create');
        // Show the create task form
    }

    public function store(Request $request)
    {
        // Validasi data yang dikirim dari formulir
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sponsors,email',
            'website' => 'required|url',
            'address' => 'required|string',
            'description' => 'required|string',
            'package' => 'required|string',
            'status' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Contoh validasi untuk upload gambar
        ]);

        // Proses upload gambar jika ada
        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp; // Mengambil timestamp saat ini
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension(); // Nama gambar menjadi timestamp.extensi
            $imagePath = $request->file('image')->storeAs('public/sponsor', $imageName); // Simpan gambar ke dalam direktori penyimpanan sponsor dengan nama timestamp
            $imageUrl = asset('storage/sponsor/' . $imageName); // Buat URL penyimpanan gambar
        } else {
            $imageName = null; // Atur menjadi null jika tidak ada gambar yang diunggah
            $imageUrl = null; // Atur menjadi null jika tidak ada gambar yang diunggah
        }

        // Membuat slug dari nama sponsor
        $slug = Str::slug($request->input('name'));
        // Simpan data ke dalam model Sponsors dengan create()
        Sponsor::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'company_website' => $request->input('website'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'package' => $request->input('package'),
            'status' => $request->input('status'),
            'image' => $imageUrl, // Simpan URL gambar ke dalam kolom "image" dalam database
            'slug' => $slug, // Simpan slug ke dalam kolom "slug" dalam database
        ]);

        // Redirect ke halaman lain atau tampilkan pesan sukses
        return redirect()->route('sponsors.index')->with('success', 'Sponsor berhasil disimpan');
    }




    public function show($id)
    {
        // Show a specific task
    }

    public function edit($id)
    {
        $sponsor = Sponsor::find($id);

        if (!$sponsor) {
            return redirect()->route('sponsors.index')->with('error', 'Sponsor tidak ditemukan');
        }

        return view('admin.sponsor.edit', compact('sponsor'));
    }


    public function update(Request $request, $id)
    {
        // Validasi data yang dikirim dari formulir
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sponsors,email,' . $id, // Tambahkan $id untuk mengabaikan email saat ini
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'package' => 'required|string',
            'status' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Ambil data sponsor yang akan diupdate
        $sponsor = Sponsor::findOrFail($id);

        // Update data sponsor sesuai dengan data yang dikirim dari formulir
        $sponsor->name = $request->input('name');
        $sponsor->email = $request->input('email');
        $sponsor->company_website = $request->input('website');
        $sponsor->address = $request->input('address');
        $sponsor->description = $request->input('description');
        $sponsor->package = $request->input('package');
        $sponsor->status = $request->input('status');
        // Proses pembuatan slug
        $slug = Str::slug($request->input('name')); // Membuat slug dari nama sponsor

        // Periksa apakah slug sudah digunakan oleh sponsor lain
        if (Sponsor::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . time(); // Tambahkan timestamp jika slug sudah digunakan
        }

        $sponsor->slug = $slug; // Set slug ke dalam model Sponsor

        // Proses update gambar jika ada
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($sponsor->image) {
                Storage::delete('public/sponsor/' . $sponsor->image);
            }

            $timestamp = now()->timestamp; // Mengambil timestamp saat ini
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension(); // Nama gambar menjadi timestamp.extensi
            $imagePath = $request->file('image')->storeAs('public/sponsor', $imageName); // Simpan gambar ke dalam direktori penyimpanan sponsor dengan nama timestamp
            $imageUrl = asset('storage/sponsor/' . $imageName); // Buat URL penyimpanan gambar
            $sponsor->image = $imageUrl;
        }

        $sponsor->save();

        // Redirect ke halaman lain atau tampilkan pesan sukses
        return redirect()->route('sponsors.index')->with('success', 'Sponsor berhasil diperbarui');
    }


    public function destroy($id)
    {
        $sponsor = Sponsor::find($id);

        if (!$sponsor) {
            return response()->json(['success' => false, 'message' => 'Sponsor tidak ditemukan']);
        }

        // Hapus gambar terkait jika ada (jika Anda menyimpan gambar dalam storage)
        if ($sponsor->image) {
            // Hapus gambar dari penyimpanan (storage)
            Storage::delete('public/sponsor/' . $sponsor->image);
        }

        $sponsor->delete();

        return response()->json(['success' => true, 'message' => 'Sponsor berhasil dihapus']);
    }



    public function updateStatus(Request $request, $id)
    {
        $sponsor = Sponsor::find($id);
        if (!$sponsor) {
            return response()->json(['success' => false, 'message' => 'Sponsor tidak ditemukan']);
        }

        $status = $request->input('status');
        $sponsor->status = $status;
        $sponsor->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui']);
    }

    public function sponsor($slug)
    {
        $event = Events::where('slug', $slug)->first();
        $company = Sponsor::get();
        $data = [
            'events' => $event,
            'company' => $company
        ];
        return view('register_event.sponsor', $data);
    }

    public function show_sponsor($id)
    {
        $detail = Sponsor::findOrFail($id);

        return response()->json($detail);
    }

    public function register_sponsor(Request $request)
    {
        try {
            $findSponsor = Sponsor::find($request->company);
            $findEvent = Events::where('id', $request->events_id)->first();
            foreach ($request->name as $key => $value) {
                $uname = strtoupper(Str::random(7));
                $codePayment = strtoupper(Str::random(7));

                $findUser = User::firstOrNew(['email' => $request->email[$key]]);
                $findUser->name = $request->name[$key];
                $findUser->email = $request->email[$key];
                $findUser->password = Hash::make('DMC2023');
                $findUser->uname = $uname;
                $findUser->save();

                $findCompany = CompanyModel::where('users_id', $findUser->id)->first();
                if (empty($findCompany)) {
                    $findCompany = new CompanyModel();
                }
                $findCompany->company_name = $findSponsor->name;
                $findCompany->office_number = $request->office_number;
                $findCompany->address = $request->address;
                $findCompany->company_website = $request->company_website;
                $findCompany->users_id = $findUser->id;
                $findCompany->save();

                $findProfile = ProfileModel::where('users_id', $findUser->id)->first();
                if (empty($findProfile)) {
                    $findProfile = new ProfileModel();
                }
                $findProfile->users_id = $findUser->id;
                $findProfile->fullphone = preg_replace('/[^0-9]/', '', $request->phone[$key]);
                $findProfile->job_title = $request->job_title[$key];
                $findProfile->phone = substr($findProfile->fullphone, 2);
                $findProfile->prefix_phone = substr($findProfile->fullphone, 1, 3);
                $findProfile->company_id = $findCompany->id;
                $findProfile->save();

                $image = QrCode::format('png')
                    ->size(200)->errorCorrection('H')
                    ->generate($codePayment);
                $output_file = '/public/uploads/payment/qr-code/img-' . time() . '.png';
                $db = '/storage/uploads/payment/qr-code/img-' . time() . '.png';
                Storage::disk('local')->put($output_file, $image);

                $paymentData = [
                    'member_id' => $findUser->id,
                    'package' => 'sponsor',
                    'code_payment' => $codePayment,
                    'link' => null,
                    'events_id' => $findEvent->id,
                    'tickets_id' => 3,
                    'status_registration' => 'Paid Off',
                    'groupby_users_id' => $findUser->id,
                    'qr_code' => $db
                ];

                Payment::updateOrCreate(['member_id' => $findUser->id, 'events_id' => $findEvent->id], $paymentData);

                $data = [
                    'code_payment' => $codePayment,
                    'create_date' => date('d, M Y H:i'),
                    'users_name' => $request->name[$key],
                    'users_email' => $request->email[$key],
                    'phone' => $request->phone[$key],
                    'job_title' => $request->job_title[$key],
                    'company_name' => $findCompany->company_name,
                    'company_address' => $findCompany->address,
                    'events_name' => $findEvent->name,
                    'start_date' => $findEvent->start_date,
                    'end_date' => $findEvent->end_date,
                    'start_time' => $findEvent->start_time,
                    'end_time' => $findEvent->end_time,
                    'image' => $db
                ];
                $email = $request->email[$key];

                $pdf = Pdf::loadView('email.ticket', $data);
                Mail::send('email.approval-event', $data, function ($message) use ($email, $pdf, $codePayment, $findEvent) {
                    $message->from(env('EMAIL_SENDER'));
                    $message->to($email);
                    $message->subject($codePayment . ' - Your registration is approved for ' . $findEvent->name);
                    $message->attachData($pdf->output(), $codePayment . '-' . time() . '.pdf');
                });
            }

            return redirect()->back()->with('alert', 'Successfully Registering Sponsor');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
