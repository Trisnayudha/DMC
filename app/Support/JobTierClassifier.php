<?php

namespace App\Support;

/**
 * Klasifikasi job title → position tier (Internal Operating Guideline 2026,
 * "Internal member composition"). Terpisah dari users.tier (reguler/black)
 * dan dari company category — keduanya dilaporkan sendiri-sendiri.
 *
 *   Tier 1  Decision makers & institutional leaders  → Priority relationship
 *   Tier 2  Business & technical influencers          → Targeted engagement
 *   Tier 3  Industry professionals & practitioners    → Event nurture
 *   Tier 4  Emerging, support or unclear profile      → Standard nurture
 *
 * Rule berbasis kata kunci (EN + ID), dicek berurutan; yang pertama cocok
 * menang. Hasilnya cuma tebakan awal — admin bisa override per user
 * (profiles.job_tier_manual), dan override tidak ditimpa ulang.
 */
class JobTierClassifier
{
    /**
     * `roles` = keterangan yang ditampilkan di UI (dropdown, tooltip, filter, chart).
     * `color` = ramp biru ordinal (T1 paling gelap = prioritas tertinggi), lolos
     * validasi ordinal (monoton, beda terang tiap step, ujung terang >= 2:1).
     * `text` = warna teks di atas badge; T3/T4 terlalu terang untuk teks putih.
     */
    const TIERS = [
        1 => ['roles' => 'Owner, Commissioner, C suite, President Director, Director',         'action' => 'Priority relationship', 'color' => '#104281', 'text' => '#ffffff'],
        2 => ['roles' => 'General Manager, Head, Senior Manager, Manager, Superintendent',     'action' => 'Targeted engagement',   'color' => '#256abf', 'text' => '#ffffff'],
        3 => ['roles' => 'Supervisor, engineer, geologist, analyst, sales and consultant',    'action' => 'Event nurture',         'color' => '#5598e7', 'text' => '#0d366b'],
        4 => ['roles' => 'Staff, junior professional, student, academic or unconfirmed role', 'action' => 'Standard nurture',      'color' => '#86b6ef', 'text' => '#0d366b'],
    ];

    /**
     * Pola dicek sesuai urutan array ini (bukan urutan tier): penanda
     * "support/emerging" yang kuat dicek dulu supaya "Assistant to Director"
     * atau "Director's Secretary" tidak ikut jadi Tier 1, lalu Tier 1 → 2,
     * baru "staff" (supaya "Chief of Staff" tetap Tier 1), lalu Tier 3.
     */
    const RULES = [
        [4, ['student', 'mahasiswa', 'intern', 'internship', 'magang', 'trainee', 'fresh graduate',
             'junior', 'secretary', 'sekretaris', 'personal assistant', 'assistant to', 'asisten pribadi',
             'lecturer', 'dosen', 'professor', 'prof', 'researcher', 'peneliti', 'academic', 'akademisi',
             'teacher', 'guru']],
        [1, ['owner', 'pemilik', 'founder', 'co founder', 'cofounder', 'pendiri', 'commissioner', 'komisaris',
             'commisioner', 'comissioner', 'general counsel', 'chairman', 'chairwoman', 'chairperson', 'chair', 'ketua', 'president', 'presiden', 'vice president',
             'vp', 'svp', 'evp', 'chief', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'cio', 'cco', 'chro', 'cso',
             'director', 'directors', 'direktur', 'dirut', 'managing partner', 'partner', 'principal',
             'board', 'bod', 'md', 'entrepreneur', 'pengusaha', 'wiraswasta', 'minister', 'menteri',
             'governor', 'gubernur', 'bupati', 'walikota', 'mayor', 'dirjen', 'director general']],
        [2, ['general manager', 'gm', 'manager', 'managers', 'manajer', 'mgr', 'head', 'kepala', 'ktt',
             'superintendent', 'kabag', 'kadiv', 'bdm', 'avp']],
        [4, ['staff', 'staf', 'karyawan', 'pegawai', 'employee', 'admin', 'administration', 'assistant',
             'asisten', 'clerk']],
        [3, ['supervisor', 'spv', 'pengawas', 'engineer', 'engineering', 'insinyur', 'geologist', 'geology',
             'geologi', 'geophysicist', 'metallurgist', 'mining', 'surveyor', 'analyst', 'analis', 'sales',
             'marketing', 'consultant', 'konsultan', 'advisor', 'adviser', 'specialist', 'spesialis', 'expert',
             'executive', 'officer', 'coordinator', 'koordinator', 'associate', 'trader', 'accountant',
             'akuntan', 'auditor', 'lawyer', 'legal', 'planner', 'foreman', 'operator', 'technician', 'teknisi',
             'business development', 'bussines development', 'bussiness development', 'bd', 'procurement',
             'hydrogeologist', 'geoscientist', 'geotechnical', 'hse', 'counsel', 'advocate', 'representative', 'purchasing', 'buyer', 'lead', 'leader', 'it',
             'programmer', 'developer', 'scientist', 'chemist', 'inspector', 'project', 'professional']],
    ];

    /** Tier default bila tidak ada kata kunci yang cocok (judul kosong / tidak jelas). */
    const DEFAULT_TIER = 4;

    public static function classify(?string $jobTitle): int
    {
        $normalized = self::normalize($jobTitle);
        if ($normalized === '') {
            return self::DEFAULT_TIER;
        }

        foreach (self::RULES as $rule) {
            list($tier, $keywords) = $rule;
            foreach ($keywords as $keyword) {
                if (strpos($normalized, ' ' . $keyword . ' ') !== false) {
                    return $tier;
                }
            }
        }

        return self::DEFAULT_TIER;
    }

    public static function label(?int $tier): string
    {
        return isset(self::TIERS[$tier]) ? 'Tier ' . $tier : '-';
    }

    /** Mis. "Tier 1 — Owner, Commissioner, C suite, President Director, Director". */
    public static function description(?int $tier): string
    {
        return isset(self::TIERS[$tier]) ? self::label($tier) . ' — ' . self::TIERS[$tier]['roles'] : '-';
    }

    /** Tooltip badge job tier di tabel Members. */
    public static function tooltip(int $tier, bool $manual): string
    {
        return self::description($tier) . ' · ' . ($manual ? 'diset manual' : 'otomatis dari job title') . '. Klik untuk ubah.';
    }

    public static function color(?int $tier): string
    {
        return self::TIERS[$tier]['color'] ?? '#adb5bd';
    }

    public static function textColor(?int $tier): string
    {
        return self::TIERS[$tier]['text'] ?? '#ffffff';
    }

    /**
     * Lowercase, semua non-huruf/angka jadi spasi, diapit spasi — supaya
     * pencocokan per kata utuh ("it" tidak cocok di "security", "md" tidak
     * cocok di "admin") cukup pakai strpos(' kata ').
     */
    private static function normalize(?string $jobTitle): string
    {
        $text = strtolower(trim((string) $jobTitle));
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $text = trim($text);

        return $text === '' ? '' : ' ' . $text . ' ';
    }
}
