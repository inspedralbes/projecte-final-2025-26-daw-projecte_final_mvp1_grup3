<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeVideoSearchService
{
    public function __construct(
        private YoutubeDurationResolver $durationResolver
    ) {}

    /** @return array<string, mixed> */
    public function cercar(string $query): array
    {
        $apiKey = (string) config('services.youtube.api_key');
        $baseUrl = (string) config('services.youtube.base_url', 'https://www.googleapis.com/youtube/v3');

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'YouTube no configurat', 'items' => []];
        }

        try {
            $response = Http::timeout(5)->retry(1, 200)->get($baseUrl.'/search', [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'video',
                'maxResults' => 10,
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'Error YouTube', 'items' => []];
            }

            $payload = $response->json();
            $resultats = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
            $videoIds = [];
            foreach ($resultats as $resultatId) {
                if (!is_array($resultatId)) {
                    continue;
                }
                $idData = isset($resultatId['id']) && is_array($resultatId['id']) ? $resultatId['id'] : [];
                if (isset($idData['videoId']) && $idData['videoId'] !== '') {
                    $videoIds[] = (string) $idData['videoId'];
                }
            }

            $duracionsPerVideo = $this->durationResolver->obtenirDuracions($baseUrl, $apiKey, $videoIds);
            $items = [];

            foreach ($resultats as $resultat) {
                $idData = isset($resultat['id']) && is_array($resultat['id']) ? $resultat['id'] : [];
                $snippet = isset($resultat['snippet']) && is_array($resultat['snippet']) ? $resultat['snippet'] : [];
                $thumbnails = isset($snippet['thumbnails']) && is_array($snippet['thumbnails']) ? $snippet['thumbnails'] : [];
                $videoId = isset($idData['videoId']) ? (string) $idData['videoId'] : '';
                $duracio = ($videoId !== '' && isset($duracionsPerVideo[$videoId])) ? (string) $duracionsPerVideo[$videoId] : '';

                $urlImatge = '';
                if (isset($thumbnails['medium']['url'])) {
                    $urlImatge = (string) $thumbnails['medium']['url'];
                } elseif (isset($thumbnails['default']['url'])) {
                    $urlImatge = (string) $thumbnails['default']['url'];
                }

                $items[] = [
                    'api_id' => $videoId,
                    'titol' => isset($snippet['title']) ? (string) $snippet['title'] : 'Video',
                    'url_imatge' => $urlImatge,
                    'duracio' => $duracio,
                    'tipus_api' => 'youtube',
                ];
            }

            return ['ok' => true, 'error' => null, 'items' => $items];
        } catch (\Throwable $e) {
            Log::warning('Error cercant YouTube', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'YouTube no disponible temporalment', 'items' => []];
        }
    }
}
