<?php

namespace App\Support;

/**
 * Rincian role di dalam satu job tier — sesuai daftar role di definisi tier
 * (JobTierClassifier::TIERS), mis. T1 → Owner, Commissioner, C suite, ...
 * Dipakai untuk detail chart Job Tier; tidak mengubah tier-nya sendiri.
 *
 * Role dicari hanya di antara role tier si member: aturan per tier dicek
 * berurutan, yang pertama cocok menang. Semua kata kunci di
 * JobTierClassifier::RULES sudah terpetakan ke salah satu role; yang tidak
 * cocok sama sekali (mis. tier diset manual oleh admin) masuk FALLBACK.
 * Kata kunci "a+b" = harus mengandung "a" dan "b".
 */
class JobTierRole
{
    /** Urutan tampil = urutan di definisi tier. */
    const ROLES = [
        1 => ['Owner', 'Commissioner', 'C suite', 'President Director', 'Director'],
        2 => ['General Manager', 'Head', 'Senior Manager', 'Manager'],
        3 => ['Superintendent', 'Supervisor', 'Engineer', 'Geologist', 'Analyst', 'Sales', 'Consultant'],
        4 => ['Staff', 'Junior professional', 'Student', 'Academic', 'Others'],
    ];

    const FALLBACK = [1 => 'Director', 2 => 'Manager', 3 => 'Consultant', 4 => 'Others'];

    /** Urutan pencocokan (beda dengan urutan tampil). */
    const RULES = [
        1 => [
            ['Commissioner', ['commissioner', 'komisaris', 'commisioner', 'comissioner', 'chairman', 'chairwoman',
                'chairperson', 'chair', 'ketua', 'board', 'bod']],
            ['Owner', ['owner', 'pemilik', 'founder', 'co founder', 'cofounder', 'pendiri', 'entrepreneur',
                'pengusaha', 'wiraswasta']],
            // VP dicek sebelum "president" supaya "Vice President" tidak jadi President Director.
            ['Director', ['vice president', 'vp', 'svp', 'evp']],
            ['President Director', ['president', 'presiden', 'dirut', 'direktur utama']],
            ['C suite', ['chief', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'cio', 'cco', 'chro', 'cso', 'general counsel']],
            ['Director', ['director', 'directors', 'direktur', 'md', 'managing partner', 'partner', 'principal',
                'dirjen', 'minister', 'menteri', 'governor', 'gubernur', 'bupati', 'walikota', 'mayor']],
        ],
        2 => [
            ['Head', ['head', 'kepala', 'ktt', 'kabag', 'kadiv']],
            // AGM & senior dicek sebelum "general manager" / "manager".
            ['Senior Manager', ['senior+manager', 'sr+manager', 'snr+manager', 'assistant general manager',
                'asst general manager', 'agm', 'avp']],
            ['General Manager', ['general manager', 'gm']],
            ['Manager', ['manager', 'managers', 'manajer', 'mgr', 'bdm']],
        ],
        3 => [
            ['Superintendent', ['superintendent', 'supt']],
            ['Supervisor', ['supervisor', 'spv', 'pengawas', 'foreman', 'coordinator', 'koordinator', 'lead', 'leader']],
            ['Sales', ['sales', 'marketing', 'business development', 'bussines development', 'bussiness development',
                'bd', 'account executive', 'trader', 'procurement', 'purchasing', 'buyer', 'representative']],
            ['Geologist', ['geologist', 'geology', 'geologi', 'geophysicist', 'hydrogeologist', 'geoscientist']],
            ['Engineer', ['engineer', 'engineering', 'insinyur', 'metallurgist', 'mining', 'surveyor', 'geotechnical',
                'planner', 'operator', 'technician', 'teknisi', 'hse', 'inspector', 'project', 'it', 'programmer',
                'developer']],
            ['Analyst', ['analyst', 'analis', 'accountant', 'akuntan', 'auditor', 'scientist', 'chemist', 'officer',
                'executive', 'associate']],
            ['Consultant', ['consultant', 'konsultan', 'advisor', 'adviser', 'specialist', 'spesialis', 'expert',
                'lawyer', 'legal', 'counsel', 'advocate', 'professional']],
        ],
        4 => [
            ['Student', ['student', 'mahasiswa', 'intern', 'internship', 'magang', 'trainee', 'fresh graduate']],
            ['Academic', ['lecturer', 'dosen', 'professor', 'prof', 'researcher', 'peneliti', 'academic', 'akademisi',
                'teacher', 'guru']],
            ['Junior professional', ['junior']],
            ['Staff', ['staff', 'staf', 'karyawan', 'pegawai', 'employee', 'admin', 'administration', 'assistant',
                'asisten', 'clerk', 'secretary', 'sekretaris']],
        ],
    ];

    public static function roleFor(int $tier, ?string $jobTitle): string
    {
        $normalized = JobTierClassifier::normalize($jobTitle);

        foreach (self::RULES[$tier] ?? [] as $rule) {
            list($role, $keywords) = $rule;
            foreach ($keywords as $keyword) {
                if (self::matches($normalized, $keyword)) {
                    return $role;
                }
            }
        }

        return self::FALLBACK[$tier] ?? '-';
    }

    /** Hitungan nol untuk semua role satu tier, urut sesuai definisi. */
    public static function emptyCounts(int $tier): array
    {
        return array_fill_keys(self::ROLES[$tier] ?? [], 0);
    }

    private static function matches(string $normalized, string $keyword): bool
    {
        foreach (explode('+', $keyword) as $part) {
            if (strpos($normalized, ' ' . $part . ' ') === false) {
                return false;
            }
        }

        return $normalized !== '';
    }
}
