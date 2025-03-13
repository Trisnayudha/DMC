<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\SocialMediaEngagement;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use App\Models\Sponsors\SponsorPic;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');

        // Data sponsor: filtered by type (package) jika ada
        $data = Sponsor::when($type, function ($query, $type) {
            return $query->where('package', $type);
        })->orderBy('id', 'desc')->get();

        // Sponsor counts per package
        $platinumCount = Sponsor::where('package', 'platinum')->where('status', 'publish')->count();
        $goldCount     = Sponsor::where('package', 'gold')->where('status', 'publish')->count();
        $silverCount   = Sponsor::where('package', 'silver')->where('status', 'publish')->count();
        $totalCount    = Sponsor::where('status', 'publish')->count();

        // Top 5 Sponsor Representative Attend (menggunakan data Payment)
        $currentYear = Carbon::now()->year;
        $topSponsors = Payment::selectRaw('company.company_name as company, COUNT(DISTINCT payment.member_id) as count_attend')
            ->join('profiles', 'payment.member_id', '=', 'profiles.users_id')
            ->join('company', 'profiles.company_id', '=', 'company.id')
            ->whereYear('payment.created_at', $currentYear)
            ->groupBy('company.company_name')
            ->orderByDesc('count_attend')
            ->limit(5)
            ->get();

        // Benefit usage summary
        $totalBenefitsAssigned = SponsorBenefitUsage::whereHas('sponsor', function ($q) {
            $q->where('status', 'publish');
        })->count();

        $totalBenefitsUsed = SponsorBenefitUsage::where('status', 'used')
            ->whereHas('sponsor', function ($q) {
                $q->where('status', 'publish');
            })->count();

        $totalBenefitsUnused = SponsorBenefitUsage::where('status', 'unused')
            ->whereHas('sponsor', function ($q) {
                $q->where('status', 'publish');
            })->count();

        $benefitUsageRate = $totalBenefitsAssigned > 0
            ? round(($totalBenefitsUsed / $totalBenefitsAssigned) * 100)
            : 0;

        // Near End Sponsors: sponsor dengan contract_end (format "YYYY-MM") yang sudah tidak melebihi bulan ini
        $nearEndSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') <= ?", [Carbon::now()->format('Y-m-01')])
            ->orderByRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') DESC")
            ->limit(5)
            ->get();

        // Engagement Data (SocialMediaEngagement) untuk tahun berjalan
        $engagements = SocialMediaEngagement::with('sponsor')
            ->whereYear('activity_date', Carbon::now()->format('Y'))
            ->orderBy('activity_date', 'desc')
            ->limit(5)
            ->get();

        // Kelompokkan engagement berdasarkan sponsor_id dan hitung jumlahnya
        $engagementCount = $engagements->groupBy(function ($engagement) {
            return $engagement->sponsor->id;
        })->map(function ($group) {
            return $group->count();
        });

        // Ambil semua sponsor aktif agar sponsor yang tidak memiliki engagement tampil dengan count 0
        $allSponsors = Sponsor::where('status', 'publish')->limit(5)->get();

        // Notifikasi Alert:
        // Expired: contract_end < awal bulan ini
        $expiredSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') < ?", [Carbon::now()->format('Y-m-01')])
            ->get();

        // Renewal Soon: contract_end antara awal bulan depan dan awal bulan dua bulan ke depan
        $renewalSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [
                Carbon::now()->addMonth()->format('Y-m-01'),
                Carbon::now()->addMonths(2)->format('Y-m-01')
            ])
            ->get();

        return view('admin.sponsor.sponsor', compact(
            'data',
            'platinumCount',
            'goldCount',
            'silverCount',
            'totalCount',
            'topSponsors',
            'totalBenefitsAssigned',
            'totalBenefitsUsed',
            'totalBenefitsUnused',
            'benefitUsageRate',
            'nearEndSponsors',
            'engagementCount',
            'allSponsors',
            'expiredSponsors',
            'renewalSponsors',
            'type'
        ));
    }


    public function create()
    {
        return view('admin.sponsor.create');
        // Show the create task form
    }

    public function store(Request $request)
    {
        // Validasi data sponsor
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sponsors,email',
            'website' => 'required|url',
            'address' => 'required|string',
            'description' => 'required|string',
            'package' => 'required|string',
            'status' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|url',
            'contract_start' => 'required|date_format:Y-m',
            'contract_end' => 'required|date_format:Y-m'
        ]);

        // Proses upload gambar jika ada
        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $imagePath = $request->file('image')->storeAs('public/sponsor', $imageName);
            $imageUrl = asset('storage/sponsor/' . $imageName);
        } else {
            $imageUrl = null;
        }

        // Membuat slug dari nama sponsor
        $slug = Str::slug($request->input('name'));

        // Simpan data sponsor dan ambil model yang baru dibuat
        $sponsor = Sponsor::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'company_website' => $request->input('website'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'package' => $request->input('package'),
            'status' => $request->input('status'),
            'image' => $imageUrl,
            'slug' => $slug,
            'founded' => $request->input('founded'),
            'location_office' => $request->input('location_office'),
            'employees' => $request->input('employees'),
            'company_category' => $request->input('company_category'),
            'instagram' => $request->input('instagram'),
            'facebook' => $request->input('facebook'),
            'linkedin' => $request->input('linkedin'),
            'video' => $request->input('video'),
            'contract_start' => $request->input('contract_start'),
            'contract_end' => $request->input('contract_end')
        ]);

        // Simpan data PIC jika ada input PIC
        if ($request->has('pic') && is_array($request->input('pic.name'))) {
            $picNames = $request->input('pic.name');
            $picTitles = $request->input('pic.title');
            $picEmails = $request->input('pic.email');
            $picPhones = $request->input('pic.phone');

            // Looping setiap PIC yang diinput
            foreach ($picNames as $index => $picName) {
                // Hanya simpan jika nama PIC tidak kosong
                if (!empty($picName)) {
                    SponsorPic::create([
                        'sponsor_id' => $sponsor->id,
                        'name' => $picName,
                        'title' => $picTitles[$index] ?? null,
                        'email' => $picEmails[$index] ?? null,
                        'phone' => $picPhones[$index] ?? null,
                    ]);
                }
            }
        }

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
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:sponsors,email,' . $id,
            'website'        => 'nullable|url',
            'address'        => 'nullable|string',
            'description'    => 'nullable|string',
            'package'        => 'required|string',
            'status'         => 'required|string',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video'          => 'nullable|url',
            'contract_start' => 'nullable|date_format:Y-m',
            'contract_end'   => 'nullable|date_format:Y-m'
        ]);

        // Ambil data sponsor yang akan diupdate
        $sponsor = Sponsor::findOrFail($id);

        // Simpan daftar ID PIC lama (sebelum update) untuk referensi penghapusan
        $oldPicIds = $sponsor->pics()->pluck('id')->toArray();

        // Update data sponsor
        $sponsor->name = $request->input('name');
        $sponsor->email = $request->input('email');
        $sponsor->company_website = $request->input('website');
        $sponsor->address = $request->input('address');
        $sponsor->description = $request->input('description');
        $sponsor->package = $request->input('package');
        $sponsor->status = $request->input('status');
        $sponsor->founded = $request->input('founded');
        $sponsor->location_office = $request->input('location_office');
        $sponsor->employees = $request->input('employees');
        $sponsor->company_category = $request->input('company_category');
        $sponsor->instagram = $request->input('instagram');
        $sponsor->facebook = $request->input('facebook');
        $sponsor->linkedin = $request->input('linkedin');
        $sponsor->video = $request->input('video');
        $sponsor->contract_start = $request->input('contract_start');
        $sponsor->contract_end = $request->input('contract_end');

        // Proses pembuatan slug
        $slug = \Illuminate\Support\Str::slug($request->input('name'));
        if (Sponsor::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . time();
        }
        $sponsor->slug = $slug;

        // Proses update gambar jika ada
        if ($request->hasFile('image')) {
            if ($sponsor->image) {
                \Illuminate\Support\Facades\Storage::delete('public/sponsor/' . basename($sponsor->image));
            }
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('public/sponsor', $imageName);
            $imageUrl = asset('storage/sponsor/' . $imageName);
            $sponsor->image = $imageUrl;
        }

        $sponsor->save();

        // Proses update/penambahan data PIC
        if ($request->has('pic') && is_array($request->input('pic.name'))) {
            $picNames = $request->input('pic.name');
            $picTitles = $request->input('pic.title');
            $picEmails = $request->input('pic.email');
            $picPhones = $request->input('pic.phone');
            $picIds = $request->input('pic.id', []); // Bisa kosong untuk PIC baru

            $submittedPicIds = [];
            foreach ($picNames as $index => $picName) {
                if (!empty($picName)) {
                    // Jika terdapat ID, update record PIC yang sudah ada
                    if (isset($picIds[$index]) && !empty($picIds[$index])) {
                        $pic = SponsorPic::find($picIds[$index]);
                        if ($pic) {
                            $pic->update([
                                'name'  => $picName,
                                'title' => $picTitles[$index] ?? null,
                                'email' => $picEmails[$index] ?? null,
                                'phone' => $picPhones[$index] ?? null,
                            ]);
                            $submittedPicIds[] = $pic->id;
                        }
                    } else {
                        // Jika tidak ada ID, buat record PIC baru
                        $newPic = SponsorPic::create([
                            'sponsor_id' => $sponsor->id,
                            'name'  => $picName,
                            'title' => $picTitles[$index] ?? null,
                            'email' => $picEmails[$index] ?? null,
                            'phone' => $picPhones[$index] ?? null,
                        ]);
                        $submittedPicIds[] = $newPic->id;
                    }
                }
            }

            // Hapus PIC yang ada di database tapi tidak ada dalam submittedPicIds
            if (!empty($oldPicIds)) {
                $toDelete = array_diff($oldPicIds, $submittedPicIds);
                if (!empty($toDelete)) {
                    SponsorPic::whereIn('id', $toDelete)->delete();
                }
            }
        } else {
            // Jika tidak ada input PIC, hapus semua PIC untuk sponsor ini
            SponsorPic::where('sponsor_id', $sponsor->id)->delete();
        }

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

    public function editContract($id)
    {
        $sponsor = Sponsor::findOrFail($id);
        return view('admin.sponsor.edit-contract', compact('sponsor'));
    }

    /**
     * Memproses update contract (contract_start dan contract_end).
     */
    public function updateContract(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $validated = $request->validate([
            'contract_start' => 'required|date_format:Y-m',
            'contract_end'   => 'required|date_format:Y-m',
        ]);

        $start = \Carbon\Carbon::createFromFormat('Y-m', $validated['contract_start']);
        $end = \Carbon\Carbon::createFromFormat('Y-m', $validated['contract_end']);
        if ($end->lt($start)) {
            return response()->json(['success' => false, 'message' => 'Contract end must be after contract start.'], 422);
        }

        $sponsor->contract_start = $validated['contract_start'];
        $sponsor->contract_end   = $validated['contract_end'];
        $sponsor->save();

        return response()->json(['success' => true, 'message' => 'Contract updated successfully.']);
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
