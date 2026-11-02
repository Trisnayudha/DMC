<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDatabaseController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'deduped'); // deduped | duplicates | raw
        $memberRegistered = Schema::hasColumn('users', 'member_registered_at') ? 'u.member_registered_at' : 'NULL';

        $baseSql = "
        SELECT
            z.*,
            FIRST_VALUE(LOWER(TRIM(z.member_status))) OVER (
                PARTITION BY z.unique_key ORDER BY z.is_member DESC, z.priority ASC, z.created_at DESC, z.id DESC
            ) AS identity_member_status,
            COUNT(*) OVER (PARTITION BY z.unique_key) AS dup_count,
            ROW_NUMBER() OVER (
                PARTITION BY z.unique_key
                ORDER BY z.is_member DESC, z.priority ASC, z.created_at DESC, z.id DESC
            ) AS rn,
            MAX(z.is_member) OVER (PARTITION BY z.unique_key) AS in_members,
            MAX(z.has_event) OVER (PARTITION BY z.unique_key) AS in_events,
            MAX(z.is_partnership) OVER (PARTITION BY z.unique_key) AS in_partnership,
            MAX(z.is_intake) OVER (PARTITION BY z.unique_key) AS in_intake
        FROM (
            SELECT
                u.id AS id,
                'users' AS source,
                u.status_member AS member_status,
                u.source AS user_source,
                {$memberRegistered} AS member_registered_at,
                EXISTS(SELECT 1 FROM payment p WHERE p.member_id = u.id AND p.events_id IS NOT NULL) AS has_event,
                CASE WHEN LOWER(TRIM(COALESCE(u.status_member, ''))) IN ('active', 'pending', 'declined', 'deactivated') THEN 1 ELSE 0 END AS is_member,
                0 AS is_partnership,
                0 AS is_intake,
                u.created_at AS created_at,
                u.name AS name,
                pr.job_title AS job_title,
                c.company_name AS company_name,
                u.email AS email,
                COALESCE(pr.fullphone, pr.phone) AS phone,
                COALESCE(c.full_office_number, c.office_number) AS office_number,
                c.address AS address,
                c.company_website AS company_website,
                c.company_category AS company_category,
                c.cci AS cci,
                c.explore AS explore,
                COALESCE(CONCAT('email:', NULLIF(LOWER(TRIM(u.email)),'')), CONCAT('users#', u.id)) AS unique_key,
                1 AS priority
            FROM users u
            LEFT JOIN profiles pr ON pr.id = (SELECT MAX(p2.id) FROM profiles p2 WHERE p2.users_id = u.id)
            LEFT JOIN company c ON c.id = pr.company_id

            UNION ALL

            SELECT
                x.id AS id,
                'xtwp' AS source,
                NULL AS member_status,
                NULL AS user_source,
                NULL AS member_registered_at,
                0 AS has_event,
                0 AS is_member,
                0 AS is_partnership,
                1 AS is_intake,
                x.created_at AS created_at,
                x.name AS name,
                x.job_title AS job_title,
                x.company_name AS company_name,
                x.email AS email,
                COALESCE(NULLIF(x.fullphone,''), NULLIF(x.phone,'')) AS phone,
                COALESCE(NULLIF(x.full_office_number,''), NULLIF(x.office_number,'')) AS office_number,
                x.address AS address,
                x.company_website AS company_website,
                x.company_category AS company_category,
                x.cci AS cci,
                x.explore AS explore,
                COALESCE(CONCAT('email:', NULLIF(LOWER(TRIM(x.email)),'')), CONCAT('xtwp#', x.id)) AS unique_key,
                2 AS priority
            FROM xtwp_users_dmc x

            UNION ALL

            SELECT
                v.id AS id,
                'partnership' AS source,
                NULL AS member_status,
                NULL AS user_source,
                NULL AS member_registered_at,
                0 AS has_event,
                0 AS is_member,
                1 AS is_partnership,
                0 AS is_intake,
                v.created_at AS created_at,
                v.name AS name,
                v.job_title AS job_title,
                v.company_name AS company_name,
                v.business_email AS email,
                v.mobile_number AS phone,
                v.office_number AS office_number,
                v.address AS address,
                v.website AS company_website,
                NULL AS company_category,
                NULL AS cci,
                NULL AS explore,
                COALESCE(CONCAT('email:', NULLIF(LOWER(TRIM(v.business_email)),'')), CONCAT('partnership#', v.id)) AS unique_key,
                3 AS priority
            FROM partnership_event_visitors v
        ) z
    ";

        if ($mode === 'raw') {
            $sql = "SELECT * FROM ({$baseSql}) t ORDER BY t.created_at DESC";
        } elseif ($mode === 'duplicates') {
            $sql = "SELECT * FROM ({$baseSql}) t WHERE t.dup_count > 1 ORDER BY t.unique_key ASC, t.priority ASC, t.created_at DESC";
        } else {
            $mode = 'deduped'; // normalize mode invalid
            $sql = "SELECT * FROM ({$baseSql}) t WHERE t.rn = 1 ORDER BY t.created_at DESC";
        }

        $list = DB::select($sql);
        $people = $mode === 'deduped' ? $list : DB::select("SELECT * FROM ({$baseSql}) t WHERE t.rn=1");
        $breakdown = array_fill_keys(['intake', 'active', 'pending', 'declined', 'deactivated', 'event', 'other'], 0);
        foreach ($list as $row) {
            $row->category = self::categoryFor($row);
            $row->contact_group = self::contactGroupFor($row);
        }
        foreach ($people as $row) {
            $breakdown[self::categoryFor($row)]++;
        }
        $sourceCounts = ['members' => 0, 'events' => 0, 'partnership' => 0, 'intake' => 0];
        $sourceMatched = ['events' => 0, 'intake' => 0];
        $eventOnlyCount = 0;
        $groupCounts = array_fill_keys(['members', 'events', 'partnership', 'intake', 'other'], 0);
        // $sourceOverlap[source][group]: contacts in this source that are counted under another card.
        $sourceOverlap = array_fill_keys(['events', 'partnership', 'intake'], []);
        foreach ($people as $row) {
            $group = self::contactGroupFor($row);
            $groupCounts[$group]++;
            foreach ($sourceOverlap as $key => $counts) {
                if ($row->{'in_' . $key} && $group !== $key) {
                    $sourceOverlap[$key][$group] = ($counts[$group] ?? 0) + 1;
                }
            }
            foreach ($sourceCounts as $key => $count) {
                if ($row->{'in_' . $key}) {
                    $sourceCounts[$key]++;
                }
            }
            foreach ($sourceMatched as $key => $count) {
                if ($row->{'in_' . $key} && $row->dup_count > 1) {
                    $sourceMatched[$key]++;
                }
            }
            if ($row->in_events && !$row->in_members && empty($row->member_registered_at)) {
                $eventOnlyCount++;
            }
        }

        // counts (biar summary bener & jadi bukti mode jalan)
        $totalRaw = DB::selectOne("SELECT COUNT(*) AS c FROM ({$baseSql}) t")->c;
        $totalDeduped = DB::selectOne("SELECT COUNT(*) AS c FROM ({$baseSql}) t WHERE t.rn=1")->c;
        $totalDuplicateRows = DB::selectOne("SELECT COUNT(*) AS c FROM ({$baseSql}) t WHERE t.dup_count>1")->c;

        return view('admin.master_database.index', [
            'list' => $list,
            'mode' => $mode,
            'totalRaw' => $totalRaw,
            'totalDeduped' => $totalDeduped,
            'totalDuplicatesRows' => $totalDuplicateRows,
            'breakdown' => $breakdown,
            'sourceCounts' => $sourceCounts,
            'groupCounts' => $groupCounts,
            'sourceOverlap' => $sourceOverlap,
            'sourceMatched' => $sourceMatched,
            'eventOnlyCount' => $eventOnlyCount,
        ]);
    }

    public static function contactGroupFor(object $row): string
    {
        // Each identity contributes to exactly one group, in this order.
        foreach (['members', 'events', 'partnership', 'intake'] as $group) {
            if (!empty($row->{'in_' . $group})) {
                return $group;
            }
        }
        return 'other';
    }

    public static function categoryFor(object $row): string
    {
        if ($row->source === 'xtwp') {
            return 'intake';
        }
        $status = strtolower(trim((string) $row->member_status));
        if (in_array($status, ['active', 'pending', 'declined', 'deactivated'], true)) {
            return $status;
        }
        $source = strtolower(trim((string) $row->user_source));
        $eventSource = strpos($source, 'event') === 0 || strpos($source, 'dmc event') === 0
            || strpos($source, 'ep/') === 0 || strpos($source, 'e/') === 0;
        if ($status === '' && empty($row->member_registered_at) && ($eventSource || $row->has_event)) {
            return 'event';
        }
        return 'other';
    }
}
