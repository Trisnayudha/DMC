<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SponsorContactDirectoryExport;
use App\Http\Controllers\Controller;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorLog;
use App\Models\Sponsors\SponsorPic;
use App\Models\Sponsors\SponsorRepresentative;
use App\Models\User;
use App\Services\Sponsors\SponsorContactRowBuilder;
use App\Support\JobTierClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SponsorContactDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $package = $request->get('package', '');

        $sponsors = $this->getSponsorsWithContactRows($search, $package);

        $totalPics     = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'pic')->count(); });
        $totalBillings = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'billing')->count(); });
        $totalReps     = $sponsors->sum(function ($s) { return $s->contactRows->where('role', 'representative')->count(); });

        return view('admin.sponsor.contact_directory', compact(
            'sponsors', 'search', 'package',
            'totalPics', 'totalBillings', 'totalReps'
        ));
    }

    public function export(Request $request)
    {
        $role    = $request->get('role', 'all');
        $search  = $request->get('search', '');
        $package = $request->get('package', '');

        $sponsors = $this->getSponsorsWithContactRows($search, $package);

        $suffix   = $role === 'all' ? 'contacts' : $role;
        $filename = 'sponsor-contact-directory-' . $suffix . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SponsorContactDirectoryExport($sponsors, $role), $filename);
    }

    /**
     * Edit satu baris kontak: semua record PIC, representative & profil member yang
     * tergabung di baris itu diperbarui sekaligus. Billing tetap lewat form Edit Sponsor.
     * PIC/representative tercatat di Sponsor Log lewat model; member juga di user_edit_logs.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sponsor_id'   => 'required|exists:sponsors,id',
            'pic_ids'      => 'array',
            'pic_ids.*'    => 'integer',
            'rep_ids'      => 'array',
            'rep_ids.*'    => 'integer',
            'member_ids'   => 'array',
            'member_ids.*' => 'integer',
            'name'         => 'required|string|max:255',
            'title'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'instagram'    => 'nullable|string|max:255',
            'linkedin'     => 'nullable|string|max:255',
        ]);
        $sponsorId = (int) $validated['sponsor_id'];

        $pics = SponsorPic::where('sponsor_id', $sponsorId)->whereIn('id', $validated['pic_ids'] ?? [])->get();
        $reps = SponsorRepresentative::where('sponsor_id', $sponsorId)->whereIn('id', $validated['rep_ids'] ?? [])->get();
        // Member hanya yang memang terhubung ke sponsor ini (didaftarkan lewat payment sponsor).
        $members = User::whereIn('id', $validated['member_ids'] ?? [])
            ->whereIn('id', DB::table('payment')->where('sponsor_id', $sponsorId)->select('member_id'))
            ->get();

        if ($pics->isEmpty() && $reps->isEmpty() && $members->isEmpty()) {
            return back()->with('error', 'Contact not found for this sponsor.');
        }

        DB::transaction(function () use ($pics, $reps, $members, $validated, $sponsorId) {
            $this->updatePics($pics, $validated);
            $this->updateReps($reps, $validated);
            foreach ($members as $member) {
                $this->updateMember($member, $validated, $sponsorId);
            }
        });

        return back()
            ->with('success', 'Contact ' . $validated['name'] . ' updated successfully.')
            ->with('open_sponsor', $sponsorId);
    }

    private function updatePics($pics, array $data): void
    {
        foreach ($pics as $pic) {
            $pic->update([
                'name'  => $data['name'],
                'title' => $data['title'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);
        }
    }

    private function updateReps($reps, array $data): void
    {
        foreach ($reps as $rep) {
            $rep->name      = $data['name'];
            $rep->job_title = $data['title'] ?? null;
            $rep->email     = $data['email'] ?? null;
            $rep->instagram = $data['instagram'] ?? null;
            $rep->linkedin  = $data['linkedin'] ?? null;
            $rep->save();
        }
    }

    /**
     * Profil member: nama, job title & phone (email = login, tidak diubah di sini).
     * Pola sama dengan edit di Members: phone = fullphone, job tier auto ikut job title.
     */
    private function updateMember(User $member, array $data, int $sponsorId): void
    {
        $profile = ProfileModel::firstOrNew(['users_id' => $member->id]);
        $changes = [];
        $set = function ($model, $field, $value, $logField = null) use (&$changes) {
            if ((string) $model->{$field} !== (string) $value) {
                $changes[$logField ?: $field] = ['old' => $model->{$field}, 'new' => $value];
                $model->{$field} = $value;
            }
        };

        $set($member, 'name', $data['name']);
        $set($profile, 'job_title', $data['title'] ?? null);
        $set($profile, 'fullphone', $data['phone'] ?? null, 'phone');
        $profile->phone = $profile->fullphone;

        if (isset($changes['job_title']) && ProfileModel::hasJobTierColumn()
            && $member->status_member === 'active' && !$profile->job_tier_manual) {
            $set($profile, 'job_tier', JobTierClassifier::classify($profile->job_title));
        }

        if (!$changes) {
            return;
        }
        $member->save();
        $profile->save();

        $admin = Auth::user();
        DB::table('user_edit_logs')->insert([
            'user_id'    => $member->id,
            'admin_id'   => $admin ? $admin->id : null,
            'admin_name' => $admin ? $admin->name : null,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        SponsorLog::record($sponsorId, 'Member Contact', $member->id, 'updated', $changes, 'Member profile ' . $member->email . ' edited from Contact Directory');
    }

    private function getSponsorsWithContactRows($search, $package)
    {
        $query = Sponsor::with(['pics', 'billings', 'representatives', 'members'])
            ->where('status', 'publish')
            ->orderBy('name');

        if ($package) {
            $query->where('package', $package);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $sponsors = $query->get();
        $builder  = new SponsorContactRowBuilder();

        foreach ($sponsors as $sponsor) {
            $sponsor->contactRows = $builder->build($sponsor);
        }

        return $sponsors;
    }
}
