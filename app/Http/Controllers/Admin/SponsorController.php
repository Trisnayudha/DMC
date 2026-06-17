<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SponsorRenewalsExport;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\SocialMediaEngagement;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBilling;
use App\Models\Sponsors\SponsorPic;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Helpers\WhatsappApi;
use App\Helpers\ScrapeHelper;
use App\Services\Sponsors\SponsorBenefitService;
use App\Support\QrCode;
use Maatwebsite\Excel\Facades\Excel;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $statusFilter = $request->get('status');

        // filter baru renewal
        $renewalYear = $request->get('renewal_year');
        $renewalState = $request->get('renewal_state');

        // Data sponsor: filtered by type (package), status, dan renewal jika ada
        // Default ke status=publish jika tidak ada filter status dari request
        $effectiveStatus = $statusFilter ?: 'publish';

        $data = Sponsor::with(['renewals', 'currentRenewal', 'firstPic'])
            ->when($type, function ($query, $type) {
                return $query->where('package', $type);
            })
            ->where('status', $effectiveStatus)

            // sponsor yang SUDAH renew (renewal/upgrade) di tahun tertentu
            ->when($renewalYear && $renewalState === 'renewed', function ($query) use ($renewalYear) {
                return $query->whereHas('renewals', function ($q) use ($renewalYear) {
                    $q->where('renewal_year', $renewalYear)
                        ->where('renewal_status', 'renewed')
                        ->whereNotIn('renewal_type', ['new', 'new_member']);
                });
            })

            // sponsor yang TIDAK renew di tahun tertentu (ada record not_renewed)
            ->when($renewalYear && $renewalState === 'not_renewed', function ($query) use ($renewalYear) {
                return $query->whereHas('renewals', function ($q) use ($renewalYear) {
                    $q->where('renewal_year', $renewalYear)
                        ->where('renewal_status', 'not_renewed');
                });
            })

            // sponsor baru (new/new_member) di tahun tertentu
            ->when($renewalYear && $renewalState === 'new_sponsor', function ($query) use ($renewalYear) {
                return $query->whereHas('renewals', function ($q) use ($renewalYear) {
                    $q->where('renewal_year', $renewalYear)
                        ->where('renewal_status', 'renewed')
                        ->whereIn('renewal_type', ['new', 'new_member']);
                });
            })

            ->orderBy('id', 'desc')
            ->get();

        // Dropdown tahun dynamic dari histori renewal
        $availableYears = \App\Models\Sponsors\SponsorRenewal::select('renewal_year')
            ->distinct()
            ->orderBy('renewal_year', 'desc')
            ->pluck('renewal_year');

        // Sponsor counts — ikuti filter jika ada, default ke semua aktif
        $isFiltered = $renewalYear || $renewalState || $type || $statusFilter;
        if ($isFiltered) {
            $platinumCount = $data->where('package', 'platinum')->count();
            $goldCount     = $data->where('package', 'gold')->count();
            $silverCount   = $data->where('package', 'silver')->count();
            $totalCount    = $data->count();
        } else {
            $platinumCount = Sponsor::where('package', 'platinum')->where('status', 'publish')->count();
            $goldCount     = Sponsor::where('package', 'gold')->where('status', 'publish')->count();
            $silverCount   = Sponsor::where('package', 'silver')->where('status', 'publish')->count();
            $totalCount    = Sponsor::where('status', 'publish')->count();
        }

        // Top 5 Sponsor Representative Attend (menggunakan data Payment)
        $currentYear = Carbon::now()->year;
        $topSponsors = Payment::selectRaw('company.company_name as company, COUNT(DISTINCT payment.member_id) as count_attend')
            ->join('profiles', 'payment.member_id', '=', 'profiles.users_id')
            ->join('company', 'profiles.company_id', '=', 'company.id')
            ->whereNotNull('payment.sponsor_id')
            ->whereYear('payment.created_at', $currentYear)
            ->groupBy('company.company_name')
            ->orderByDesc('count_attend')
            ->limit(5)
            ->get();

        // Benefit usage summary — hanya period aktif per sponsor
        $activePeriodExpr = "IF(LEFT(s.contract_start,4) = LEFT(s.contract_end,4), LEFT(s.contract_start,4), CONCAT(LEFT(s.contract_start,4),'-',LEFT(s.contract_end,4)))";

        $benefitBase = DB::table('sponsor_benefit_usage as sbu')
            ->join('sponsors as s', 's.id', '=', 'sbu.sponsor_id')
            ->where('s.status', 'publish')
            ->whereNotNull('s.contract_start')
            ->whereNotNull('s.contract_end')
            ->whereRaw("sbu.period = ({$activePeriodExpr})");

        $totalBenefitsAssigned = (clone $benefitBase)->count();
        $totalBenefitsUsed     = (clone $benefitBase)->where('sbu.status', 'used')->count();
        $totalBenefitsUnused   = (clone $benefitBase)->where('sbu.status', 'unused')->count();

        $benefitUsageRate = $totalBenefitsAssigned > 0
            ? round(($totalBenefitsUsed / $totalBenefitsAssigned) * 100)
            : 0;

        $nearEndSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') >= ?", [Carbon::now()->format('Y-m-01')])
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') <= ?", [Carbon::now()->addMonths(3)->format('Y-m-01')])
            ->orderByRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') ASC")
            ->limit(5)
            ->get();

        $engagements = SocialMediaEngagement::with('sponsor')
            ->whereYear('activity_date', Carbon::now()->format('Y'))
            ->orderBy('activity_date', 'desc')
            ->limit(5)
            ->get();

        $engagementCount = $engagements->groupBy(function ($engagement) {
            return optional($engagement->sponsor)->id;
        })->map(function ($group) {
            return $group->count();
        });

        $allSponsors = Sponsor::where('status', 'publish')->limit(5)->get();

        $expiredSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') < ?", [Carbon::now()->format('Y-m-01')])
            ->get();

        $renewalSponsors = Sponsor::where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') >= ?", [Carbon::now()->format('Y-m-01')])
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') <= ?", [Carbon::now()->addMonths(3)->format('Y-m-01')])
            ->orderByRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') ASC")
            ->get();

        // Recent sponsor inquiries
        $recentInquiries = DB::table('sponsors_inquiry as si')
            ->leftJoin('sponsors_representative as sr', 'si.sponsors_representative_id', '=', 'sr.id')
            ->leftJoin('sponsors as s', 'sr.sponsor_id', '=', 's.id')
            ->leftJoin('users as u', 'si.users_id', '=', 'u.id')
            ->leftJoin('company as c', 'u.id', '=', 'c.users_id')
            ->select(
                'si.id',
                'si.message',
                'si.created_at',
                'sr.name as rep_name',
                'sr.job_title as rep_title',
                's.name as sponsor_name',
                'u.name as user_name',
                'c.company_name'
            )
            ->orderByDesc('si.created_at')
            ->limit(10)
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
            'type',
            'statusFilter',
            'renewalYear',
            'renewalState',
            'availableYears',
            'recentInquiries'
        ));
    }

    public function nearingContract(Request $request)
    {
        $type   = $request->get('type');
        $search = trim((string) $request->get('search', ''));

        $data = Sponsor::with(['renewals', 'currentRenewal', 'firstPic'])
            ->where('status', 'publish')
            ->whereNotNull('contract_end')
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') >= ?", [Carbon::now()->format('Y-m-01')])
            ->whereRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') <= ?", [Carbon::now()->addMonths(3)->format('Y-m-01')])
            ->when($type, fn($q) => $q->where('package', $type))
            ->when($search, fn($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->orderByRaw("STR_TO_DATE(CONCAT(contract_end, '-01'), '%Y-%m-%d') ASC")
            ->get();

        return view('admin.sponsor.nearing_contract', compact('data', 'type', 'search'));
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
            'branding_name' => $request->input('branding_name'),
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

        \App\Models\Sponsors\SponsorRenewal::create([
            'sponsor_id'      => $sponsor->id,
            'renewal_year'    => \Carbon\Carbon::createFromFormat('Y-m', $request->input('contract_start'))->year,
            'contract_start'  => $request->input('contract_start'),
            'contract_end'    => $request->input('contract_end'),
            'package'         => $request->input('package'),
            'renewal_status'  => 'renewed',
            'is_current'      => 1,
        ]);

        SponsorBenefitService::generateForSponsor($sponsor);

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

        $this->storeBillings($request, $sponsor);

        return redirect()->route('sponsors.index')->with('success', 'Sponsor berhasil disimpan');
    }

    // Simpan data billing baru (dipakai saat create sponsor)
    private function storeBillings(Request $request, Sponsor $sponsor)
    {
        if (!$request->has('billing') || !is_array($request->input('billing.name'))) {
            return;
        }

        $billingTitles = $request->input('billing.title');
        $billingEmails = $request->input('billing.email');
        $billingPhones = $request->input('billing.phone');

        foreach ($request->input('billing.name') as $index => $billingName) {
            if (!empty($billingName)) {
                SponsorBilling::create([
                    'sponsor_id' => $sponsor->id,
                    'name'  => $billingName,
                    'title' => $billingTitles[$index] ?? null,
                    'email' => $billingEmails[$index] ?? null,
                    'phone' => $billingPhones[$index] ?? null,
                ]);
            }
        }
    }

    // Update/tambah/hapus billing mengikuti isi form edit (pola sama dengan PIC)
    private function syncBillings(Request $request, Sponsor $sponsor)
    {
        if (!$request->has('billing') || !is_array($request->input('billing.name'))) {
            SponsorBilling::where('sponsor_id', $sponsor->id)->delete();
            return;
        }

        $oldBillingIds = $sponsor->billings()->pluck('id')->toArray();

        $billingTitles = $request->input('billing.title');
        $billingEmails = $request->input('billing.email');
        $billingPhones = $request->input('billing.phone');
        $billingIds    = $request->input('billing.id', []);

        $submittedIds = [];
        foreach ($request->input('billing.name') as $index => $billingName) {
            if (empty($billingName)) {
                continue;
            }

            $data = [
                'name'  => $billingName,
                'title' => $billingTitles[$index] ?? null,
                'email' => $billingEmails[$index] ?? null,
                'phone' => $billingPhones[$index] ?? null,
            ];

            $billing = isset($billingIds[$index]) && !empty($billingIds[$index])
                ? SponsorBilling::find($billingIds[$index])
                : null;

            if ($billing) {
                $billing->update($data);
            } else {
                $billing = SponsorBilling::create($data + ['sponsor_id' => $sponsor->id]);
            }
            $submittedIds[] = $billing->id;
        }

        $toDelete = array_diff($oldBillingIds, $submittedIds);
        if (!empty($toDelete)) {
            SponsorBilling::whereIn('id', $toDelete)->delete();
        }
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
            'company_website'        => 'nullable|url',
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
        $sponsor->branding_name = $request->input('branding_name');
        $sponsor->email = $request->input('email');
        $sponsor->company_website = $request->input('company_website');
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

        $this->syncBillings($request, $sponsor);

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
        $company = Sponsor::where('status', 'publish')->get();
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

    public function getKmkRate()
    {
        try {
            $rate = ScrapeHelper::scrapeExchangeRate();
            return response()->json(['success' => true, 'rate' => $rate]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'rate' => null, 'message' => 'Failed to fetch KMK rate.']);
        }
    }

    public function getNextQuotationNumber(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        return response()->json(['next' => SponsorRenewal::generateQuotationNumber($year)]);
    }

    /**
     * Memproses update contract / renewal sponsor.
     */
    public function updateContract(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $validated = $request->validate([
            'contract_start'    => 'required|date_format:Y-m',
            'contract_end'      => 'required|date_format:Y-m',
            'renewal_type'      => 'required|in:renewal,upgrade,new,new_member',
            'package'           => 'required|in:platinum,gold,silver',
            'amount_usd'        => 'nullable|numeric|min:0',
            'amount_idr'        => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:1000',
            'quotation_number'  => 'nullable|string|max:30|unique:sponsor_renewals,quotation_number',
        ]);

        $start = Carbon::createFromFormat('Y-m', $validated['contract_start']);
        $end   = Carbon::createFromFormat('Y-m', $validated['contract_end']);

        if ($end->lt($start)) {
            return response()->json([
                'success' => false,
                'message' => 'Contract end must be after contract start.'
            ], 422);
        }

        $quotationNumber = !empty($validated['quotation_number'])
            ? $validated['quotation_number']
            : SponsorRenewal::generateQuotationNumber($start->year);

        DB::transaction(function () use ($sponsor, $validated, $start, $quotationNumber) {
            SponsorRenewal::where('sponsor_id', $sponsor->id)
                ->where('is_current', 1)
                ->update(['is_current' => 0]);

            SponsorRenewal::create([
                'sponsor_id'       => $sponsor->id,
                'renewal_year'     => $start->year,
                'contract_start'   => $validated['contract_start'],
                'contract_end'     => $validated['contract_end'],
                'package'          => $validated['package'],
                'renewal_type'     => $validated['renewal_type'],
                'renewal_status'   => 'renewed',
                'amount_usd'       => $validated['amount_usd'] ?? null,
                'amount_idr'       => $validated['amount_idr'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'is_current'       => 1,
                'quotation_number' => $quotationNumber,
                'quotation_date'   => now()->toDateString(),
            ]);

            $sponsor->contract_start = $validated['contract_start'];
            $sponsor->contract_end   = $validated['contract_end'];
            $sponsor->package        = $validated['package'];
            $sponsor->save();
        });

        // Generate benefit usage untuk periode kontrak yang baru (idempotent, tidak duplikat)
        $sponsor->refresh();
        SponsorBenefitService::generateForSponsor($sponsor);

        // Kirim notifikasi WhatsApp ke group finance
        try {
            $sponsor->refresh();
            $pic     = $sponsor->firstPic;
            $kmkRate = null;
            try { $kmkRate = ScrapeHelper::scrapeExchangeRate(); } catch (\Throwable $e) {}
            $formUrl = config('app.url') . '/admin/sponsors/' . $sponsor->id . '/renewal-form/preview';
            $message = $this->buildContractUpdateMessage($sponsor, $validated, $pic, $kmkRate, $quotationNumber, $formUrl);
            $wa = new WhatsappApi();
            $wa->phone   = '120363429723388586@g.us';
            $wa->message = $message;
            $wa->WhatsappMessageGroup();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('WA contract notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully.'
        ]);
    }

    private function buildContractUpdateMessage($sponsor, array $validated, $pic, ?int $kmkRate = null, ?string $quotationNumber = null, ?string $formUrl = null): string
    {
        $months = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];

        $typeLabels = [
            'renewal'    => 'Renewal',
            'upgrade'    => 'Renewal – Upgrade',
            'new'        => 'New Sponsor',
            'new_member' => 'New Member',
        ];

        $packageLabels = [
            'platinum' => 'Platinum / Major',
            'gold'     => 'Gold',
            'silver'   => 'Silver',
        ];

        [$sy, $sm] = explode('-', $validated['contract_start']);
        [$ey, $em] = explode('-', $validated['contract_end']);
        $periode = ($months[$sm] ?? $sm) . ' ' . $sy . ' – ' . ($months[$em] ?? $em) . ' ' . $ey;

        $renewalType  = $typeLabels[$validated['renewal_type']] ?? $validated['renewal_type'];
        $packageLabel = $packageLabels[$validated['package']] ?? ucfirst($validated['package']);

        $amountLine = '';
        if (!empty($validated['amount_usd'])) {
            $amountLine .= '• USD: *USD ' . number_format($validated['amount_usd'], 0, '.', '.') . "*\n";
            if ($kmkRate) {
                $amountLine .= '• KMK Rate: *IDR ' . number_format($kmkRate, 0, '.', '.') . "/USD*\n";
            }
        }
        if (!empty($validated['amount_idr'])) {
            $amountLine .= '• IDR: *IDR ' . number_format($validated['amount_idr'], 0, '.', '.') . "*\n";
        }
        if (!$amountLine) {
            $amountLine = "• _Belum dicantumkan_\n";
        }

        $picSection = '';
        if ($pic) {
            $picSection  = "👤 *PIC / Contact Person*\n";
            $picSection .= '• Nama: ' . $pic->name . "\n";
            if ($pic->title)  $picSection .= '• Jabatan: ' . $pic->title . "\n";
            if ($pic->email)  $picSection .= '• Email: ' . $pic->email . "\n";
            if ($pic->phone)  $picSection .= '• Phone: ' . $pic->phone . "\n";
        } else {
            $picSection = "👤 *PIC:* _Data tidak tersedia_\n";
        }

        $notesLine = '';
        if (!empty($validated['notes'])) {
            $notesLine = "\n📝 *Catatan:* " . $validated['notes'] . "\n";
        }

        $now = Carbon::now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') . ' WIB';

        $quotLine = $quotationNumber ? '• Quotation No: *' . $quotationNumber . "*\n" : '';
        $formLine = $formUrl ? "\n📄 *Renewal Form:*\n" . $formUrl . "\n" : '';

        return implode('', [
            "🏢 *SPONSOR CONTRACT UPDATE*\n",
            "━━━━━━━━━━━━━━━━━━━━━\n",
            "📋 *Djakarta Mining Club*\n",
            "Berikut informasi renewal/update kontrak sponsor yang baru dicatat di sistem:\n\n",
            "🏷️ *Detail Sponsor*\n",
            '• Perusahaan: *' . $sponsor->name . "*\n",
            '• Paket: *' . $packageLabel . " Sponsorship*\n",
            '• Tipe: *' . $renewalType . "*\n",
            '• Periode: *' . $periode . "*\n",
            $quotLine,
            "\n",
            "💰 *Nilai Sponsorship*\n",
            $amountLine,
            "\n",
            $picSection,
            $notesLine,
            $formLine,
            "\n━━━━━━━━━━━━━━━━━━━━━\n",
            "🕐 _Diperbarui: " . $now . "_\n\n",
            "⚠️ _Jika pembayaran sudah diproses secara manual atau sponsor sudah melunasi, mohon abaikan pesan ini._\n\n",
            "_— DMC Finance Notification System_",
        ]);
    }

    /**
     * Catat sponsor sebagai tidak renew di tahun/periode tertentu.
     */
    public function markNotRenewed(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $validated = $request->validate([
            'renewal_year'   => 'required|integer|min:2020|max:2100',
            'contract_start' => 'required|date_format:Y-m',
            'contract_end'   => 'required|date_format:Y-m',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $alreadyRecorded = SponsorRenewal::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->where('renewal_status', 'not_renewed')
            ->exists();

        if ($alreadyRecorded) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsor sudah tercatat tidak renew di tahun ' . $validated['renewal_year'] . '.',
            ], 422);
        }

        SponsorRenewal::create([
            'sponsor_id'     => $sponsor->id,
            'renewal_year'   => $validated['renewal_year'],
            'contract_start' => $validated['contract_start'],
            'contract_end'   => $validated['contract_end'],
            'package'        => $sponsor->package,
            'renewal_status' => 'not_renewed',
            'renewal_type'   => null,
            'amount_usd'     => null,
            'amount_idr'     => null,
            'notes'          => $validated['notes'] ?? null,
            'is_current'     => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => $sponsor->name . ' dicatat tidak renew untuk tahun ' . $validated['renewal_year'] . '.',
        ]);
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

    public function exportRenewals(Request $request)
    {
        $year = $request->get('renewal_year');
        $state = $request->get('renewal_state');

        $filename = 'sponsor-renewals';
        if ($year) {
            $filename .= '-' . $year;
        }
        if ($state) {
            $filename .= '-' . $state;
        }
        $filename .= '.xlsx';

        return Excel::download(new SponsorRenewalsExport($year, $state), $filename);
    }

}
