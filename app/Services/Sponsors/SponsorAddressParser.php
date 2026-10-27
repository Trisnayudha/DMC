<?php

namespace App\Services\Sponsors;

/**
 * Rule-based parser that pulls city, country and postal code out of a
 * free-text sponsor address (sponsors table has no structured columns).
 *
 * Add new keywords to the maps below when an export shows a blank/wrong value.
 */
class SponsorAddressParser
{
    // Explicit city mentions, checked in order (more specific first). Pattern => canonical city.
    private const CITY_PATTERNS = [
        '/\b(jakarta\s+selatan|south\s+jakarta)\b/i'   => 'South Jakarta',
        '/\b(jakarta\s+pusat|central\s+jakarta)\b/i'   => 'Central Jakarta',
        '/\b(jakarta\s+utara|north\s+jakarta)\b/i'     => 'North Jakarta',
        '/\b(jakarta\s+timur|east\s+jakarta)\b/i'      => 'East Jakarta',
        '/\b(jakarta\s+barat|west\s+jakarta)\b/i'      => 'West Jakarta',
        '/\b(tangerang\s+selatan|south\s+tangerang)\b/i' => 'South Tangerang',
        '/\bbalikpapan\b/i'                             => 'Balikpapan',
        '/\bsamarinda\b/i'                              => 'Samarinda',
        '/\bsurabaya\b/i'                               => 'Surabaya',
        '/\bbandung\b/i'                                => 'Bandung',
        '/\bbatam\b/i'                                  => 'Batam',
        '/\bmedan\b/i'                                  => 'Medan',
        '/\bmakassar\b/i'                               => 'Makassar',
        '/\bbekasi\b/i'                                 => 'Bekasi',
        '/\bbogor\b/i'                                  => 'Bogor',
        '/\bdepok\b/i'                                  => 'Depok',
        '/\btangerang\b/i'                              => 'Tangerang',
        '/\bjakarta\b/i'                                => 'Jakarta',
        '/\bbrisbane\b/i'                               => 'Brisbane',
        '/\bsydney\b/i'                                 => 'Sydney',
        '/\bperth\b/i'                                  => 'Perth',
        '/\bmelbourne\b/i'                              => 'Melbourne',
        '/\bsingapore\b/i'                              => 'Singapore',
        '/\bkuala\s+lumpur\b/i'                         => 'Kuala Lumpur',
    ];

    // Area / landmark hints used when no explicit city is written.
    private const AREA_HINTS = [
        '/\b(scbd|senayan|senopati|kebayoran|cilandak|setiabudi|kuningan|simatupang|rasuna\s+said|kartini)\b/i' => 'South Jakarta',
        '/\b(menteng|gondangdia|thamrin|wahid\s+hasyim)\b/i' => 'Central Jakarta',
        '/\b(pluit|penjaringan|sunter)\b/i'               => 'North Jakarta',
        '/\b(pulo\s*gadung)\b/i'                          => 'East Jakarta',
        '/\b(serpong|bsd)\b/i'                            => 'South Tangerang',
        '/\b(cikupa|tigaraksa)\b/i'                       => 'Tangerang',
        '/\b(kewdale)\b/i'                                => 'Perth',
        '/\b(eagle\s+farm)\b/i'                           => 'Brisbane',
        '/\b(castle\s+hill)\b/i'                          => 'Sydney',
        '/\b(exton)\b/i'                                  => 'Exton',
    ];

    // Jakarta postal code prefix => administrative city.
    private const JAKARTA_POSTAL_PREFIX = [
        '10' => 'Central Jakarta',
        '11' => 'West Jakarta',
        '12' => 'South Jakarta',
        '13' => 'East Jakarta',
        '14' => 'North Jakarta',
    ];

    private const COUNTRY_PATTERNS = [
        '/\bindonesia\b/i'                                    => 'Indonesia',
        '/\baustralia\b/i'                                    => 'Australia',
        '/\bsingapore\b/i'                                    => 'Singapore',
        '/\bmalaysia\b/i'                                     => 'Malaysia',
        '/\b(united\s+states|usa|u\.s\.a)\b/i'                => 'United States',
        '/\b(united\s+kingdom|uk|england)\b/i'                => 'United Kingdom',
        '/\bcanada\b/i'                                       => 'Canada',
        '/\b(china|prc)\b/i'                                  => 'China',
        '/\bjapan\b/i'                                        => 'Japan',
    ];

    // Region / state hints used when no country is written.
    private const COUNTRY_HINTS = [
        '/\b(jl\.?|jln\.?|jalan|kav\.?|kec\.?|kel\.?|kelurahan|kecamatan|kota|rt\.?\s*\d|jakarta|kalimantan|banten|jawa|sumatera|sulawesi)\b/i' => 'Indonesia',
        '/\b(queensland|qld|nsw|new\s+south\s+wales|victoria|tasmania)\b/i' => 'Australia',
        '/\b(pennsylvania|texas|california|new\s+york|houston|colorado)\b/i' => 'United States',
    ];

    // Words that, right before a 4-5 digit number, mean it is NOT a postal code.
    private const NON_POSTAL_PREFIX = '(?:no|nomor|kav|kavling|lot|lt|lantai|level|floor|unit|suite|blok|block|rt|rw|tower)';

    public function parse(?string $address): array
    {
        $address = trim((string) $address);

        if ($address === '') {
            return ['city' => '', 'country' => '', 'postal_code' => ''];
        }

        $postalCode = $this->detectPostalCode($address);

        return [
            'city'        => $this->detectCity($address, $postalCode),
            'country'     => $this->detectCountry($address),
            'postal_code' => $postalCode,
        ];
    }

    private function detectPostalCode(string $address): string
    {
        if (!preg_match_all('/(?<![\w.#-])\d{4,5}(?![\w-])/', $address, $matches, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        // Postal code is normally written at the end, so scan from the back.
        foreach (array_reverse($matches[0]) as [$number, $offset]) {
            $before = substr($address, max(0, $offset - 15), min($offset, 15));

            if (!preg_match('/\b' . self::NON_POSTAL_PREFIX . '\.?\s*$/i', $before)) {
                return $number;
            }
        }

        return '';
    }

    private function detectCity(string $address, string $postalCode): string
    {
        $city = $this->firstMatch(self::CITY_PATTERNS, $address)
            ?: $this->firstMatch(self::AREA_HINTS, $address);

        // Generic "Jakarta" → refine to the administrative city.
        if ($city === 'Jakarta') {
            $city = $this->firstMatch(self::AREA_HINTS, $address)
                ?: $this->jakartaCityFromPostal($postalCode)
                ?: 'Jakarta';
        }

        return $city;
    }

    private function detectCountry(string $address): string
    {
        return $this->firstMatch(self::COUNTRY_PATTERNS, $address)
            ?: $this->firstMatch(self::COUNTRY_HINTS, $address);
    }

    private function jakartaCityFromPostal(string $postalCode): string
    {
        if (strlen($postalCode) !== 5) {
            return '';
        }

        return self::JAKARTA_POSTAL_PREFIX[substr($postalCode, 0, 2)] ?? '';
    }

    private function firstMatch(array $patterns, string $address): string
    {
        foreach ($patterns as $pattern => $value) {
            if (preg_match($pattern, $address)) {
                return $value;
            }
        }

        return '';
    }
}
