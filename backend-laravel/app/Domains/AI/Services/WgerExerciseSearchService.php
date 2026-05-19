<?php

namespace App\Domains\AI\Services;

use App\Domains\AI\Support\WgerKeywordCategoryMap;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WgerExerciseSearchService
{
    /** @return array<string, mixed> */
    public function cercar(string $query): array
    {
        $baseUrl = (string) config('services.wger.base_url', 'https://wger.de/api/v2');
        $token = (string) config('services.wger.api_token');
        $headers = $token !== '' ? ['Authorization' => 'Token '.$token] : [];

        $queryLower = strtolower(trim($query));
        $categoryId = WgerKeywordCategoryMap::detectarCategoria($queryLower);

        try {
            $params = ['format' => 'json', 'limit' => 15];
            if ($categoryId !== null) {
                $params['category'] = $categoryId;
            }

            $response = Http::withHeaders($headers)->timeout(12)->retry(2, 500)
                ->get($baseUrl.'/exerciseinfo/', $params);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'Error Wger', 'items' => []];
            }

            $payload = $response->json();
            $resultats = isset($payload['results']) && is_array($payload['results']) ? $payload['results'] : [];

            if ($categoryId === null) {
                $resultats = array_filter($resultats, function (mixed $ex) use ($queryLower): bool {
                    if (!is_array($ex)) {
                        return false;
                    }
                    $traduccions = isset($ex['translations']) && is_array($ex['translations']) ? $ex['translations'] : [];
                    foreach ($traduccions as $t) {
                        if (is_array($t) && isset($t['name']) && stripos((string) $t['name'], $queryLower) !== false) {
                            return true;
                        }
                    }

                    return false;
                });
            }

            $items = [];
            foreach ($resultats as $ex) {
                if (!is_array($ex)) {
                    continue;
                }
                $nom = '';
                $traduccions = isset($ex['translations']) && is_array($ex['translations']) ? $ex['translations'] : [];
                foreach ($traduccions as $t) {
                    if (is_array($t) && isset($t['language']) && (int) $t['language'] === 2 && isset($t['name']) && $t['name'] !== '') {
                        $nom = (string) $t['name'];
                        break;
                    }
                }
                if ($nom === '' && !empty($traduccions)) {
                    $first = $traduccions[0];
                    $nom = is_array($first) && isset($first['name']) ? (string) $first['name'] : '';
                }
                if ($nom === '') {
                    continue;
                }
                $apiId = isset($ex['id']) ? (string) $ex['id'] : '';
                $imatge = '';
                if (isset($ex['images']) && is_array($ex['images']) && count($ex['images']) > 0) {
                    $img = $ex['images'][0];
                    if (is_array($img) && isset($img['image']) && $img['image'] !== '') {
                        $imatge = (string) $img['image'];
                    }
                }
                $items[] = [
                    'api_id' => $apiId,
                    'titol' => $nom,
                    'url_imatge' => $imatge,
                    'tipus_api' => 'wger',
                ];
            }

            return ['ok' => true, 'error' => null, 'items' => array_values($items)];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Wger', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'El servidor d\'exercicis (Wger) està lent ara mateix. Torna-ho a intentar en uns segons.', 'items' => []];
        }
    }
}
