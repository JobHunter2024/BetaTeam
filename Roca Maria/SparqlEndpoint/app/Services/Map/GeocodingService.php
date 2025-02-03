<?php

namespace App\Services\Map;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeocodingService
{

    // public function __construct(?string $countryCode = null)
    // {
    //     $this->nominatimUrl = config('services.nominatim.url', 'https://nominatim.openstreetmap.org/search');
    //     $this->timeout = config('services.nominatim.timeout', 10);
    //     $this->userAgent = config('app.name') . '/1.0 (' . config('app.email') . ')';
    //     $this->countryCode = $countryCode;
    // }


    public function getCoordinates($location)
    {
        $cleanLocation = $this->normalizeAddress($location);
        $cacheKey = 'nominatim_' . md5($cleanLocation);

        return Cache::remember($cacheKey, 86400, function () use ($cleanLocation, $location) {
            // Prima încercare - adresa exactă
            $coordinates = $this->geocodeAddress($cleanLocation);

            // Dacă nu găsim, încercăm doar orașul
            if (!$coordinates) {
                $city = $this->extractCityFromAddress($location);
                if ($city) {
                    $coordinates = $this->geocodeAddress($city . ', Romania');
                }
            }

            return $coordinates ?: null;
        });
    }

    private function geocodeAddress(string $address): ?array
    {
        try {
            // Adaugă delay pentru respectarea rate limit
            sleep(1); // Important pentru Nominatim (max 1 request/sec)

            $response = Http::withHeaders([
                'User-Agent' => config('app.name') . '/1.0 (magdalena.dascalu@ctce.ro)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                        'format' => 'json',
                        'q' => $address,
                        'countrycodes' => 'ro',
                        'limit' => 1
                    ]);

            $data = $response->json();

            return !empty($data) ? [
                'lat' => $data[0]['lat'],
                'lng' => $data[0]['lon'],
                'address' => $data[0]['display_name']
            ] : null;

        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    private function extractCityFromAddress(string $address): ?string
    {
        // Extrage ultimul oraș înainte de Romania
        $pattern = '/(.+?),\s*(?:Jud\.\s*)?([^,]+),\s*Romania$/i';

        if (preg_match($pattern, $address, $matches)) {
            return trim($matches[2]);
        }

        // Fallback pentru adrese fără structură clară
        $parts = explode(',', $address);
        $cityCandidate = trim(end($parts));

        if ($cityCandidate === 'Romania') {
            $cityCandidate = trim(prev($parts));
        }

        return $cityCandidate !== 'Romania' ? $cityCandidate : null;
    }

    private function normalizeAddress(string $address): string
    {
        // Adaugă optimizări suplimentare
        $replacements = [
            '/\b(?:Bd|Bld|Bulev|Blv)\.?/iu' => 'Bulevardul',
            '/\bStr\.?/iu' => 'Strada',
            '/\bNr\.?/iu' => 'numărul',
            '/\bBl?d?g?\.?\b/iu' => 'Bloc',
            '/\bEt\.?\b/iu' => 'Etaj',
            '/\bAp\.?\b/iu' => 'Apartament',
            '/\bSos\.?/iu' => 'Șoseaua',
            '/,[\s,]*/u' => ', ', // Curăță virgule multiple
        ];

        $address = preg_replace(array_keys($replacements), array_values($replacements), $address);
        return trim(preg_replace('/\s+/', ' ', $address));
    }
}