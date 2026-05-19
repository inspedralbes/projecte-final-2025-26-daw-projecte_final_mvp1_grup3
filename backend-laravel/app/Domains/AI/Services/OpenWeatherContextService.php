<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenWeatherContextService
{
    /** @return array<string, mixed> */
    public function obtenirContext(?string $city, ?float $lat = null, ?float $lon = null): array
    {
        $apiKey = (string) config('services.openweather.api_key');
        $baseUrl = (string) config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'OpenWeather no configurat'];
        }

        if ($lat !== null && $lon !== null) {
            $ciutatResoluta = $this->reversGeocodificarCiutat($lat, $lon);
            if ($ciutatResoluta !== null) {
                $city = $ciutatResoluta;
            }
        }

        if ($city === null || $city === '') {
            $city = 'Barcelona';
        }

        try {
            $response = Http::timeout(5)->retry(1, 200)->get($baseUrl.'/weather', [
                'units' => 'metric',
                'appid' => $apiKey,
                'q' => $city,
            ]);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'No s\'ha pogut obtenir el clima'];
            }

            $payload = $response->json();
            $temp = isset($payload['main']['temp']) ? (float) $payload['main']['temp'] : null;
            $weatherMain = isset($payload['weather'][0]['main']) ? (string) $payload['weather'][0]['main'] : '';
            $weatherDesc = isset($payload['weather'][0]['description']) ? (string) $payload['weather'][0]['description'] : '';
            $cityName = isset($payload['name']) && $payload['name'] !== '' ? (string) $payload['name'] : $city;
            $suitable = $this->esClimaAdequat($weatherMain);

            return [
                'ok' => true,
                'city' => $cityName,
                'temp' => $temp,
                'weather' => $weatherMain,
                'description' => $weatherDesc,
                'suitable' => $suitable,
                'message' => $suitable ? 'El clima és adequat per a aquest hàbit' : 'El clima no és ideal ara mateix',
            ];
        } catch (\Throwable $e) {
            Log::warning('Error consultant clima', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Servei de clima no disponible'];
        }
    }

    private function reversGeocodificarCiutat(float $lat, float $lon): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Loopy-App/1.0 (contact@loopy.app)',
                'Accept' => 'application/json',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lon,
                'format' => 'json',
                'zoom' => 10,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $address = isset($data['address']) && is_array($data['address']) ? $data['address'] : [];
            foreach (['city', 'town', 'village', 'municipality', 'county'] as $camp) {
                if (isset($address[$camp]) && $address[$camp] !== '') {
                    return (string) $address[$camp];
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Error en reverse geocoding Nominatim', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function esClimaAdequat(string $weatherMain): bool
    {
        $valor = strtolower(trim($weatherMain));

        return !in_array($valor, ['thunderstorm', 'rain', 'snow'], true);
    }
}
