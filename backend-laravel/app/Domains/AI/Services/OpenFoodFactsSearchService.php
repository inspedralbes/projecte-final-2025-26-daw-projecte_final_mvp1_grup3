<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenFoodFactsSearchService
{
    /** @return array<string, mixed> */
    public function cercar(string $query): array
    {
        $searchUrl = (string) config('services.openfoodfacts.search_url', 'https://search.openfoodfacts.org');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Loopy-App/1.0 (contact@loopy.app)',
                'Accept' => 'application/json',
            ])->timeout(8)->retry(1, 300)->get($searchUrl.'/search', [
                'q' => $query,
                'page_size' => 12,
                'fields' => 'product_name,nutriments,image_small_url,_id',
            ]);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'Error Open Food Facts', 'items' => []];
            }

            $payload = $response->json();
            $productes = isset($payload['hits']) && is_array($payload['hits']) ? $payload['hits'] : [];
            $items = [];

            foreach ($productes as $producte) {
                if (!is_array($producte)) {
                    continue;
                }
                $nom = isset($producte['product_name']) && $producte['product_name'] !== ''
                    ? (string) $producte['product_name'] : '';
                if ($nom === '') {
                    continue;
                }

                $nutriments = isset($producte['nutriments']) && is_array($producte['nutriments']) ? $producte['nutriments'] : [];
                $kcal = null;
                foreach (['energy-kcal_100g', 'energy_100g', 'energy-kcal'] as $camp) {
                    if (isset($nutriments[$camp]) && is_numeric($nutriments[$camp])) {
                        $kcal = round((float) $nutriments[$camp]);
                        break;
                    }
                }

                $titol = $kcal !== null ? $nom.' ('.$kcal.' kcal/100g)' : $nom;
                $imatge = isset($producte['image_small_url']) && $producte['image_small_url'] !== ''
                    ? (string) $producte['image_small_url'] : '';
                $apiId = isset($producte['_id']) && $producte['_id'] !== ''
                    ? (string) $producte['_id'] : $nom.'-'.($kcal ?? '0');

                $items[] = [
                    'api_id' => $apiId,
                    'titol' => $titol,
                    'url_imatge' => $imatge,
                    'tipus_api' => 'openfoodfacts',
                ];
                if (count($items) >= 10) {
                    break;
                }
            }

            return ['ok' => true, 'error' => null, 'items' => $items];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Open Food Facts', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Open Food Facts no disponible temporalment', 'items' => []];
        }
    }
}
