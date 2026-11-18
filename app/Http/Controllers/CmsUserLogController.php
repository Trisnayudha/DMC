<?php

namespace App\Http\Controllers;

use App\Exports\CmsUserLogExport;
use App\Models\CmsUserLog;
use App\Support\CmsAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class CmsUserLogController extends Controller
{
    private const PAGE_SIZES = [10, 25, 50, 100];

    public function index(Request $request)
    {
        return $this->report($request);
    }

    public function show(Request $request, $admin, $menu)
    {
        return $this->report($request, $admin, $menu);
    }

    public function export(Request $request, $admin = null, $menu = null)
    {
        return $this->report($request, $admin, $menu, true);
    }

    private function report(Request $request, $admin = null, $menu = null, bool $export = false)
    {
        app()->setLocale('en');
        $rules = [
            'admin_id' => 'nullable|integer|min:1',
            'menu' => 'nullable|string|max:80',
            'action' => ['nullable', Rule::in(array_keys(CmsUserLog::ACTIONS))],
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d' . ($request->filled('from') ? '|after_or_equal:from' : ''),
            'per_page' => ['nullable', 'integer', Rule::in(self::PAGE_SIZES)],
            'through_id' => 'nullable|integer|min:0',
        ];
        $filters = array_filter($request->validate($rules), fn ($value) => $value !== null);
        $adminId = $admin ?? ($filters['admin_id'] ?? null);
        $menuKey = $menu ?? ($filters['menu'] ?? null);
        // Opening a page or downloading a report is itself audited. Keep the same set of
        // entries across page links, drill-downs and exports while new activity arrives.
        $latestId = (int) CmsUserLog::max('id');
        $filters['through_id'] = min((int) ($filters['through_id'] ?? $latestId), $latestId);
        $query = CmsUserLog::where('id', '<=', $filters['through_id']);
        if ($adminId) {
            $query->where('cms_user_id', $adminId);
        }
        if ($menuKey) {
            $query->where('menu', $menuKey);
        }
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (isset($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from'] . ' 00:00:00');
        }
        if (isset($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to'] . ' 23:59:59');
        }

        // Names at the time of each activity stay on the individual rows.
        $admins = CmsUserLog::whereIn('id', CmsUserLog::selectRaw('MAX(id)')
            ->where('id', '<=', $filters['through_id'])->groupBy('cms_user_id'))
            ->orderBy('admin_name')->pluck('admin_name', 'cms_user_id');
        $menus = CmsAudit::menuLabels();
        $detail = $admin !== null;
        $summary = (clone $query)->select('cms_user_id', 'menu')
            ->selectRaw('COUNT(*) AS total, COUNT(method) AS server_total, MAX(occurred_at) AS last_activity')
            ->groupBy('cms_user_id', 'menu')->orderByDesc('last_activity')->orderBy('cms_user_id')->orderBy('menu');
        $activities = $query->orderByDesc('occurred_at')->orderByDesc('id');

        if ($export) {
            $workbook = new CmsUserLogExport($activities, $detail ? null : $summary, $admins, $menus);
            $name = $detail ? "users-log-{$admin}-{$menu}" : 'users-log';
            // Laravel Excel changes the process-wide cell binder when writing a sheet.
            $previousBinder = Cell::getValueBinder();
            try {
                return Excel::download($workbook, $name . '-' . now()->format('Ymd-His') . '.xlsx');
            } finally {
                Cell::setValueBinder($previousBinder);
            }
        }

        $pageSizes = self::PAGE_SIZES;
        $logs = ($detail ? $activities : $summary)->paginate((int) ($filters['per_page'] ?? 25))->appends($filters);
        return view('admin.users_log.index', compact('detail', 'admins', 'menus', 'adminId', 'menuKey', 'filters', 'logs', 'pageSizes'));
    }

    public function interaction(Request $request)
    {
        $user = $request->user('cms');
        abort_unless($user->canAccessModule('dashboard'), 403);
        $data = $request->validate([
            'page' => 'required|uuid',
            'action' => ['required', Rule::in(['click', 'field'])],
            'control' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/D'],
        ]);
        if ($data['action'] === 'field' && !CmsAudit::safeField($data['control'])) {
            return response()->noContent();
        }
        $page = CmsUserLog::where('page_key', $data['page'])->where('cms_user_id', $user->id)->firstOrFail();
        CmsAudit::record([
            'cms_user_id' => $user->id,
            'admin_name' => (string) $user->name,
            'menu' => $page->menu,
            'route' => $page->route,
            'record_ids' => $page->record_ids,
            'page_log_id' => $page->id,
            'action' => $data['action'],
            'control' => $data['control'],
            'occurred_at' => now(),
        ]);
        return response()->noContent();
    }
}
