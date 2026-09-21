<?php

namespace App\Models\PartnershipEvent;

use App\Models\Events\Events;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnershipEventVisitor extends Model
{
    use HasFactory;

    protected $table = 'partnership_event_visitors';

    protected $fillable = [
        'events_id',
        'company_name',
        'name',
        'job_title',
        'business_email',
        'mobile_number',
        'office_number',
        'website',
        'address',
        'remarks',
        'merchandise',
    ];

    public function event()
    {
        return $this->belongsTo(Events::class, 'events_id');
    }

    /**
     * Excel dari booth event campur-campur (ALL CAPS, semua kecil, dll).
     * Rapikan ke kapitalisasi nama badan ala KBBI: tiap kata diawali huruf
     * besar, kecuali kata partikel (dan/di/dari/dst) yang tetap kecil
     * kalau bukan kata pertama; singkatan badan hukum (PT/CV/dst) selalu
     * kapital penuh.
     */
    public static function normalizeCompanyName(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $name = preg_replace('/\s+/', ' ', $name);

        $keepUppercase = [
            'PT', 'CV', 'UD', 'PD', 'TBK', 'LLC', 'LTD', 'GMBH', 'INC',
            'CORP', 'PLC', 'SA', 'NV', 'BV', 'AG', 'CO', 'LLP', 'PLLC',
        ];
        $lowercaseParticles = [
            'dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'atau', 'pada', 'dengan',
            'the', 'of', 'and', 'for', 'in',
        ];

        $words = explode(' ', $name);
        $result = [];

        foreach ($words as $i => $word) {
            $clean = rtrim($word, '.,');
            $suffix = substr($word, strlen($clean));

            if ($clean === '') {
                $result[] = $word;
                continue;
            }

            $upper = strtoupper($clean);
            if (in_array($upper, $keepUppercase, true)) {
                $result[] = $upper . $suffix;
                continue;
            }

            $lower = strtolower($clean);
            if ($i > 0 && in_array($lower, $lowercaseParticles, true)) {
                $result[] = $lower . $suffix;
                continue;
            }

            $titled = implode('-', array_map(function ($part) {
                return $part === '' ? $part : mb_strtoupper(mb_substr($part, 0, 1)) . mb_strtolower(mb_substr($part, 1));
            }, explode('-', $clean)));

            $result[] = $titled . $suffix;
        }

        return implode(' ', $result);
    }
}
