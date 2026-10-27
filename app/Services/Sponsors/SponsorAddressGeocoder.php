<?php

namespace App\Services\Sponsors;

use Illuminate\Support\Facades\Http;

/**
 * Turns a free-text sponsor address into coordinates (mobile app), a Google Maps
 * embed link (website) and a country name matching public/country-flag-flagcdn.json.
 *
 * Coordinates come from Photon (OpenStreetMap, free, no key). Office addresses are
 * noisy ("10th floor, Kav. 52-53, RT.1/RW.1"), so the noise is stripped and several
 * progressively broader queries are tried until one hits.
 */
class SponsorAddressGeocoder
{
    private const PHOTON_URL = 'https://photon.komoot.io/api/';

    // Floor / unit / plot fragments that geocoders cannot match.
    private const NOISE_PATTERN = '/\b(\d+(st|nd|rd|th)?\s*(fl\.?|floor)|\d+\/f|(lt|lantai|level|suite|unit|wing|zone|blok|block|blk|lot)\.?\s*[\w-]+(\s*-\s*\w+)?|rt\.?\s*\d+(\s*\/\s*rw\.?\s*\d+)?|rw\.?\s*\d+|kav\.?\s*[\w#.-]+(\s*-\s*\d+)?|no\.?\s*[\w-]+|#\s*[\d-]+(\s*to\s*\d+)?|po box \d+|km\s*[\d.,]+)\b/i';

    // Photon result types that point at a building or street, not just an area.
    private const PRECISE_TYPES = ['house', 'street', 'other'];

    private $addressParser;

    private $countries;

    public function __construct(SponsorAddressParser $addressParser)
    {
        $this->addressParser = $addressParser;
    }

    public function geocode(string $address): array
    {
        $address = $this->stripLabel($address);
        $parsed = $this->addressParser->parse($address);
        $expectedCountry = $parsed['country'];
        $queries = $this->candidateQueries($address);

        // Last resort: city centre, so the pin is at least in the right area
        if ($parsed['city']) {
            $queries[] = trim($parsed['city'] . ', ' . $expectedCountry, ', ');
        }

        $hit = $this->findCoordinates($queries, $expectedCountry);

        return [
            'lat'        => $hit['lat'] ?? null,
            'lng'        => $hit['lng'] ?? null,
            'precise'    => $hit['precise'] ?? false,
            'country'    => $this->countryName($hit['country_code'] ?? null) ?: $expectedCountry,
            'link_gmaps' => $this->embedLink($address),
        ];
    }

    public function embedLink(string $address): string
    {
        return 'https://maps.google.com/maps?q=' . urlencode($this->stripLabel($address)) . '&z=16&output=embed';
    }

    public function countries(): array
    {
        if ($this->countries === null) {
            $path = public_path('country-flag-flagcdn.json');
            $this->countries = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        }

        return $this->countries;
    }

    public function flagFor(?string $country): ?string
    {
        foreach ($this->countries() as $item) {
            if ($item['country'] === $country) {
                return $item['flag'];
            }
        }

        return null;
    }

    private function findCoordinates(array $queries, string $expectedCountry): ?array
    {
        foreach (array_values(array_unique($queries)) as $index => $query) {
            $feature = $this->searchPhoton($query);

            if (!$feature) {
                continue;
            }

            $countryCode = strtolower($feature['properties']['countrycode'] ?? '');

            // Skip hits in another country (e.g. a same-named street abroad).
            if ($expectedCountry && $this->countryName($countryCode) !== $expectedCountry) {
                continue;
            }

            [$lng, $lat] = $feature['geometry']['coordinates'];

            return [
                'lat'          => round($lat, 7),
                'lng'          => round($lng, 7),
                'country_code' => $countryCode,
                'precise'      => in_array($feature['properties']['type'] ?? '', self::PRECISE_TYPES, true)
                    && $index < 2,
            ];
        }

        return null;
    }

    /**
     * Full cleaned address first, then drop leading segments one by one
     * (building → street → district), then "building, city" as a last try.
     */
    private function candidateQueries(string $address): array
    {
        $cleaned = preg_replace(self::NOISE_PATTERN, '', $address);
        $segments = array_values(array_filter(array_map(function ($part) {
            return trim(preg_replace('/\s{2,}/', ' ', $part), " .-\u{2013}");
        }, preg_split('/[,\/|]| - | \x{2013} /u', $cleaned)), function ($part) {
            return mb_strlen($part) > 2;
        }));

        if (!$segments) {
            return [];
        }

        $queries = [implode(', ', $segments)];

        for ($i = 1; $i < count($segments) - 1; $i++) {
            $queries[] = implode(', ', array_slice($segments, $i));
        }

        if (count($segments) > 1) {
            $queries[] = $segments[0] . ', ' . end($segments);
        }

        return array_unique($queries);
    }

    private function searchPhoton(string $query): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => config('app.name') . ' admin geocoder'])
                ->get(self::PHOTON_URL, ['q' => $query, 'limit' => 1]);
        } catch (\Exception $e) {
            return null;
        }

        return $response->successful() ? ($response->json('features')[0] ?? null) : null;
    }

    private function countryName(?string $code): string
    {
        if (!$code) {
            return '';
        }

        foreach ($this->countries() as $item) {
            if (($item['code'] ?? '') === $code) {
                return $item['country'];
            }
        }

        return '';
    }

    // "Main Office : Cyber 2 Tower ..." → "Cyber 2 Tower ..."
    private function stripLabel(string $address): string
    {
        return trim(preg_replace('/^[^:]{0,40}:\s*/', '', trim($address)));
    }
}
