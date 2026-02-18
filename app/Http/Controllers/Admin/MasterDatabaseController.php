<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDatabaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /**
         * Unique logic:
         * - unique_key = email kalau ada
         * - kalau email kosong => phone/fullphone
         * - kalau duplikat: prioritas ambil dari users (priority 1) dibanding xtwp (priority 2)
         *
         * NOTE: butuh MySQL 8 karena pakai ROW_NUMBER().
         */

        $sql = "
            SELECT * FROM (
                SELECT
                    z.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY z.unique_key
                        ORDER BY z.priority ASC, z.created_at DESC
                    ) AS rn
                FROM (
                    /* ===== SOURCE A: users + profiles + company ===== */
                    SELECT
                        u.id AS id,
                        'users' AS source,
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

                        COALESCE(NULLIF(u.email,''), NULLIF(COALESCE(pr.fullphone, pr.phone), ''), CONCAT('users#', u.id)) AS unique_key,
                        1 AS priority
                    FROM users u
                    LEFT JOIN profiles pr ON pr.users_id = u.id
                    LEFT JOIN company c ON c.id = pr.company_id
                    WHERE u.isStatus IS NOT NULL

                    UNION ALL

                    /* ===== SOURCE B: xtwp_users_dmc ===== */
                    SELECT
                        x.id AS id,
                        'xtwp' AS source,
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

                        COALESCE(NULLIF(x.email,''), NULLIF(x.fullphone,''), NULLIF(x.phone,''), CONCAT('xtwp#', x.id)) AS unique_key,
                        2 AS priority
                    FROM xtwp_users_dmc x
                ) z
            ) ranked
            WHERE rn = 1
            ORDER BY created_at DESC
        ";

        $list = DB::select($sql);

        return view('admin.master_database.index', [
            'list' => $list
        ]);
    }
}
