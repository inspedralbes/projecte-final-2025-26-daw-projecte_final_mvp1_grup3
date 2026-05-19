<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeDurationResolver
{
    /**
     * @param  array<int, string>  $videoIds
     * @return array<string, string>
     */
    public function obtenirDuracions(string $baseUrl, string $apiKey, array $videoIds): array
    {
        if (count($videoIds) === 0) {
            return [];
        }

        try {
            $response = Http::timeout(5)->retry(1, 200)->get($baseUrl.'/videos', [
                'part' => 'contentDetails',
                'id' => implode(',', $videoIds),
                'maxResults' => 50,
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $payload = $response->json();
            $resultats = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
            $duracionsPerVideo = [];

            foreach ($resultats as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = isset($item['id']) ? (string) $item['id'] : '';
                $contentDetails = isset($item['contentDetails']) && is_array($item['contentDetails']) ? $item['contentDetails'] : [];
                $duracioIso = isset($contentDetails['duration']) ? (string) $contentDetails['duration'] : '';
                $duracioFormatejada = $this->formatDuracioIso($duracioIso);
                if ($id !== '' && $duracioFormatejada !== '') {
                    $duracionsPerVideo[$id] = $duracioFormatejada;
                }
            }

            return $duracionsPerVideo;
        } catch (\Throwable $e) {
            Log::warning('Error obtenint duracions de YouTube', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function formatDuracioIso(string $duracioIso): string
    {
        if ($duracioIso === '') {
            return '';
        }

        $coincidencies = [];
        $teMatch = preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $duracioIso, $coincidencies);
        if ($teMatch !== 1) {
            return '';
        }

        $hores = isset($coincidencies[1]) && $coincidencies[1] !== '' ? (int) $coincidencies[1] : 0;
        $minuts = isset($coincidencies[2]) && $coincidencies[2] !== '' ? (int) $coincidencies[2] : 0;
        $segons = isset($coincidencies[3]) && $coincidencies[3] !== '' ? (int) $coincidencies[3] : 0;

        if ($hores > 0) {
            return $hores.':'.str_pad((string) $minuts, 2, '0', STR_PAD_LEFT).':'.str_pad((string) $segons, 2, '0', STR_PAD_LEFT);
        }

        return $minuts.':'.str_pad((string) $segons, 2, '0', STR_PAD_LEFT);
    }
}
