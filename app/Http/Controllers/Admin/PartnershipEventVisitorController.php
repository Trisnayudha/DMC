<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartnershipEventVisitorExport;
use App\Exports\PartnershipEventVisitorImportTemplate;
use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Imports\PartnershipEventVisitorImport;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\PartnershipEvent\PartnershipEventVisitor;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Kelola data visitor yang mengunjungi booth DMC di event partnership.
 * Visitor di sini BUKAN member — sengaja disimpan di tabel terpisah
 * (partnership_event_visitors), tidak pernah masuk ke tabel users.
 */
class PartnershipEventVisitorController extends Controller
{
    /**
     * Cuma event_type "Partnership Event" — event pihak lain yang DMC
     * pasang booth di sana. "DMC Partnership Event" sengaja TIDAK
     * dimasukkan karena itu event buatan DMC sendiri (bukan booth visitor
     * di event partner), sama seperti konvensi yang sudah dipakai di
     * Repositories\Events::listAllEventsOnlySearchPartnership.
     */
    private array $partnershipEventTypes = ['Partnership Event'];

    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $events = Events::whereIn('event_type', $this->partnershipEventTypes)
            ->when($search !== '', fn ($q) => $q->where('name', 'LIKE', '%' . $search . '%'))
            ->withCount(['partnershipEventVisitors as visitors_count'])
            ->orderBy('start_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.partnership_event.index', compact('events', 'search'));
    }

    public function show(Request $request, $slug)
    {
        $event = $this->findEvent($slug);
        $search = trim((string) $request->query('search', ''));
        $membership = (string) $request->query('membership', '');

        // Dihitung dari SELURUH visitor event ini (bukan cuma halaman aktif),
        // supaya card ringkasan & filter member/visitor akurat lepas dari
        // pagination.
        $allVisitorsForEvent = PartnershipEventVisitor::where('events_id', $event->id)->get(['id', 'business_email']);
        $memberEmails = $this->activeMemberEmailsFor($allVisitorsForEvent);

        $totalVisitors = $allVisitorsForEvent->count();
        $memberCount = $allVisitorsForEvent->filter(
            fn ($v) => $v->business_email && $memberEmails->contains(strtolower(trim($v->business_email)))
        )->count();
        $nonMemberCount = $totalVisitors - $memberCount;

        $visitors = $this->filteredVisitorsQuery($event->id, $search, $membership, $memberEmails)
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.partnership_event.show', compact(
            'event',
            'visitors',
            'search',
            'membership',
            'memberEmails',
            'totalVisitors',
            'memberCount',
            'nonMemberCount'
        ));
    }

    public function export(Request $request, $slug)
    {
        $event = $this->findEvent($slug);
        $search = trim((string) $request->query('search', ''));
        $membership = (string) $request->query('membership', '');

        $allVisitorsForEvent = PartnershipEventVisitor::where('events_id', $event->id)->get(['id', 'business_email']);
        $memberEmails = $this->activeMemberEmailsFor($allVisitorsForEvent);

        $visitors = $this->filteredVisitorsQuery($event->id, $search, $membership, $memberEmails)
            ->orderBy('id', 'asc')
            ->get();

        $filename = 'visitor-' . $event->slug . '-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new PartnershipEventVisitorExport($visitors, $memberEmails), $filename);
    }

    public function store(Request $request, $slug)
    {
        $event = $this->findEvent($slug);
        $data = $this->validateVisitor($request);
        $data['events_id'] = $event->id;

        PartnershipEventVisitor::create($data);

        return redirect()->back()->with('success', 'Visitor berhasil ditambahkan.');
    }

    public function update(Request $request, $slug, $id)
    {
        $event = $this->findEvent($slug);
        $visitor = PartnershipEventVisitor::where('events_id', $event->id)->findOrFail($id);

        $visitor->update($this->validateVisitor($request));

        return redirect()->back()->with('success', 'Visitor berhasil diupdate.');
    }

    public function destroy($slug, $id)
    {
        $event = $this->findEvent($slug);
        PartnershipEventVisitor::where('events_id', $event->id)->findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Visitor berhasil dihapus.');
    }

    /**
     * Daftarkan visitor jadi calon member — mirror 1:1 ke alur registrasi
     * web asli (FormMemberController::store): status_member = 'pending'
     * ("Pending Verification" di menu Members DMC), isStatus = 'Active'
     * (wajib supaya kehitung di funnel member), lalu kirim email
     * waiting-approval yang sama persis dipakai pendaftaran web. Bukan
     * langsung aktif — tetap butuh approval admin di menu Members DMC.
     *
     * Email yang SUDAH jadi user penuh (bukan akun provisional/kosong)
     * tidak didaftar ulang — dicegah sama seperti di flow web asli.
     */
    public function registerAsMember($slug, $id)
    {
        $event = $this->findEvent($slug);
        $visitor = PartnershipEventVisitor::where('events_id', $event->id)->findOrFail($id);

        $email = strtolower(trim((string) $visitor->business_email));
        if ($email === '') {
            return redirect()->back()->with('error', 'Visitor ini belum punya Business Email — lengkapi emailnya dulu sebelum didaftarkan sebagai member.');
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser && !$this->isProvisionalUser($existingUser)) {
            return redirect()->back()->with('error', "Email {$email} sudah terdaftar sebagai member (status: " . ($existingUser->status_member ?: '-') . "). Tidak didaftarkan ulang.");
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'          => $visitor->name ?: $visitor->company_name,
                'isStatus'      => 'Active',
                'status_member' => 'pending',
                'source'        => 'Event Partnership',
            ];

            if ($existingUser) {
                $user = $existingUser;
                $user->update($userData);
            } else {
                $user = User::create(array_merge($userData, ['email' => $email, 'password' => null]));
                $user->assignRole('guest');
            }

            $company = CompanyModel::updateOrCreate(
                ['users_id' => $user->id],
                array_filter([
                    'company_name'    => $visitor->company_name,
                    'company_website' => $visitor->website,
                    'address'         => $visitor->address,
                    'office_number'   => $visitor->office_number,
                ], fn ($v) => $v !== null && $v !== '')
            );

            ProfileModel::updateOrCreate(
                ['users_id' => $user->id],
                array_filter([
                    'phone'      => $visitor->mobile_number,
                    'job_title'  => $visitor->job_title,
                    'company_id' => $company->id,
                ], fn ($v) => $v !== null && $v !== '')
            );

            $send = new EmailSender();
            $send->subject     = 'Thank You for Registering – Your Membership Application Is Under Review';
            $send->template    = 'email.waiting-approval';
            $send->data        = [
                'users_name'  => $user->name,
                'events_name' => $event->name,
            ];
            $send->name        = $user->name;
            $send->from        = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to          = $email;
            $send->sendEmail();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mendaftarkan visitor sebagai member, silakan coba lagi.');
        }

        return redirect()->back()->with('success', "Visitor berhasil didaftarkan sebagai calon member ({$email}). Email waiting-approval sudah dikirim, status: Pending Verification — lanjutkan verifikasi di menu Members DMC.");
    }

    private function isProvisionalUser(User $user): bool
    {
        return empty($user->password) || is_null($user->verify_email) || is_null($user->verify_phone);
    }

    public function importTemplate()
    {
        return Excel::download(new PartnershipEventVisitorImportTemplate(), 'template-import-partnership-event-visitor.xlsx');
    }

    public function import(Request $request, $slug)
    {
        $event = $this->findEvent($slug);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new PartnershipEventVisitorImport($event->id);
        Excel::import($import, $request->file('file'));

        $message = "Import selesai. {$import->getCreated()} visitor ditambahkan, {$import->getUpdated()} diupdate.";
        if ($import->getSkipped() > 0) {
            $message .= " {$import->getSkipped()} row dilewati.";
        }

        if (!empty($import->getErrors())) {
            return back()->with('success', $message)->with('import_errors', $import->getErrors());
        }

        return back()->with('success', $message);
    }

    public function toggleGiveaway(Request $request, $slug)
    {
        $event = $this->findEvent($slug);
        $event->has_giveaway = !$event->has_giveaway;
        $event->save();

        $status = $event->has_giveaway ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Giveaway untuk event \"{$event->name}\" berhasil {$status}.");
    }

    /**
     * $memberEmails: lowercased active-member email set untuk event ini
     * (hasil activeMemberEmailsFor), dipakai buat filter membership=member/
     * visitor. Wajib di-pass kalau $membership diisi — dihitung sekali di
     * caller (show/export) supaya tidak query berulang.
     */
    private function filteredVisitorsQuery(int $eventId, ?string $search = '', ?string $membership = '', $memberEmails = null)
    {
        $search = (string) $search;
        $membership = (string) $membership;

        $query = PartnershipEventVisitor::where('events_id', $eventId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('company_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('business_email', 'LIKE', '%' . $search . '%');
                });
            });

        if (in_array($membership, ['member', 'visitor'], true)) {
            $emails = ($memberEmails ?? collect())->values();
            $placeholders = $emails->isEmpty() ? '' : implode(',', array_fill(0, $emails->count(), '?'));

            if ($membership === 'member') {
                if ($emails->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereRaw("LOWER(TRIM(business_email)) IN ($placeholders)", $emails->all());
                }
            } elseif ($emails->isNotEmpty()) {
                $query->where(function ($q) use ($placeholders, $emails) {
                    $q->whereNull('business_email')
                        ->orWhere('business_email', '')
                        ->orWhereRaw("LOWER(TRIM(business_email)) NOT IN ($placeholders)", $emails->all());
                });
            }
        }

        return $query;
    }

    /**
     * Lowercased set of business_email dari $visitors yang match ke users
     * dengan status_member = active (satu-satunya syarat "sudah member" —
     * lihat memory Member verification flow). Batch query per halaman,
     * bukan per-row, supaya tabel visitor tidak N+1.
     */
    private function activeMemberEmailsFor($visitors)
    {
        $emails = $visitors->pluck('business_email')->filter()->map(fn ($e) => trim($e))->filter()->unique()->values();

        if ($emails->isEmpty()) {
            return collect();
        }

        return User::whereIn('email', $emails)
            ->where('status_member', 'active')
            ->pluck('email')
            ->map(fn ($e) => strtolower(trim($e)));
    }

    private function findEvent($slug): Events
    {
        return Events::where('slug', $slug)
            ->whereIn('event_type', $this->partnershipEventTypes)
            ->firstOrFail();
    }

    private function validateVisitor(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'company_name'   => 'nullable|string|max:255',
            'name'           => 'nullable|string|max:255',
            'job_title'      => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'mobile_number'  => 'nullable|string|max:50',
            'office_number'  => 'nullable|string|max:50',
            'website'        => 'nullable|string|max:255',
            'address'        => 'nullable|string',
            'remarks'        => 'nullable|string',
            'merchandise'    => 'nullable|string|max:255',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (empty($request->company_name) && empty($request->name) && empty($request->business_email) && empty($request->mobile_number)) {
                $validator->errors()->add('name', 'Isi minimal salah satu: Company Name, Name, Business Email, atau Mobile Number.');
            }
        });

        $data = $validator->validate();

        if (!empty($data['company_name'])) {
            $data['company_name'] = PartnershipEventVisitor::normalizeCompanyName($data['company_name']);
        }

        return $data;
    }
}
