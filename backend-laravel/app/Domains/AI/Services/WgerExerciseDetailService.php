<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WgerExerciseDetailService
{
    /** @return array<string, mixed> */
    public function obtenirDetall(string $exerciseId): array
    {
        $baseUrl = (string) config('services.wger.base_url', 'https://wger.de/api/v2');
        $token = (string) config('services.wger.api_token');
        $headers = $token !== '' ? ['Authorization' => 'Token '.$token] : [];

        try {
            $response = Http::withHeaders($headers)->timeout(12)->retry(1, 500)
                ->get($baseUrl.'/exerciseinfo/'.$exerciseId.'/', ['format' => 'json']);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'Exercici no trobat a Wger'];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return ['ok' => false, 'error' => 'Resposta invàlida de Wger'];
            }

            $nom = '';
            $descripcio = '';
            $traduccions = isset($data['translations']) && is_array($data['translations']) ? $data['translations'] : [];
            foreach ($traduccions as $traduccio) {
                if (is_array($traduccio) && isset($traduccio['language']) && (int) $traduccio['language'] === 2) {
                    $nom = isset($traduccio['name']) ? (string) $traduccio['name'] : '';
                    $htmlDesc = isset($traduccio['description']) ? (string) $traduccio['description'] : '';
                    $descripcio = strip_tags($htmlDesc);
                    break;
                }
            }

            $categoria = '';
            if (isset($data['category']) && is_array($data['category']) && isset($data['category']['name'])) {
                $categoria = (string) $data['category']['name'];
            }

            $muscles = [];
            if (isset($data['muscles']) && is_array($data['muscles'])) {
                foreach ($data['muscles'] as $muscle) {
                    if (is_array($muscle) && isset($muscle['name_en']) && $muscle['name_en'] !== '') {
                        $muscles[] = (string) $muscle['name_en'];
                    }
                }
            }

            $musclesSecundaris = [];
            if (isset($data['muscles_secondary']) && is_array($data['muscles_secondary'])) {
                foreach ($data['muscles_secondary'] as $muscle) {
                    if (is_array($muscle) && isset($muscle['name_en']) && $muscle['name_en'] !== '') {
                        $musclesSecundaris[] = (string) $muscle['name_en'];
                    }
                }
            }

            $equipament = [];
            if (isset($data['equipment']) && is_array($data['equipment'])) {
                foreach ($data['equipment'] as $equip) {
                    if (is_array($equip) && isset($equip['name']) && $equip['name'] !== '') {
                        $equipament[] = (string) $equip['name'];
                    }
                }
            }

            $imatge = '';
            if (isset($data['images']) && is_array($data['images']) && count($data['images']) > 0) {
                $primeraImatge = $data['images'][0];
                if (is_array($primeraImatge) && isset($primeraImatge['image'])) {
                    $imatge = (string) $primeraImatge['image'];
                }
            }

            return [
                'ok' => true,
                'exercise' => [
                    'api_id' => $exerciseId,
                    'titol' => $nom !== '' ? $nom : 'Exercici',
                    'categoria' => $categoria,
                    'muscles' => $muscles,
                    'muscles_secundaris' => $musclesSecundaris,
                    'equipament' => $equipament,
                    'descripcio' => $descripcio,
                    'url_imatge' => $imatge,
                    'tipus_api' => 'wger',
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Error obtenint detall exercici Wger', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Wger no disponible temporalment'];
        }
    }
}
