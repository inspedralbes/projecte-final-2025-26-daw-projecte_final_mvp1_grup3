<?php

namespace App\Domains\AI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksSearchService
{
    /** @return array<string, mixed> */
    public function cercar(string $query): array
    {
        $apiKey = (string) config('services.google_books.api_key');
        $baseUrl = (string) config('services.google_books.base_url', 'https://www.googleapis.com/books/v1');

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'Google Books no configurat', 'items' => []];
        }

        try {
            $response = Http::timeout(5)->retry(1, 200)->get($baseUrl.'/volumes', [
                'q' => $query,
                'maxResults' => 10,
                'printType' => 'books',
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'Error Google Books', 'items' => []];
            }

            $payload = $response->json();
            $volums = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
            $items = [];

            foreach ($volums as $volum) {
                $info = isset($volum['volumeInfo']) && is_array($volum['volumeInfo']) ? $volum['volumeInfo'] : [];
                $imatges = isset($info['imageLinks']) && is_array($info['imageLinks']) ? $info['imageLinks'] : [];
                $items[] = [
                    'api_id' => isset($volum['id']) ? (string) $volum['id'] : '',
                    'titol' => isset($info['title']) ? (string) $info['title'] : 'Sense títol',
                    'url_imatge' => isset($imatges['thumbnail']) ? (string) $imatges['thumbnail'] : '',
                    'tipus_api' => 'google_books',
                ];
            }

            return ['ok' => true, 'error' => null, 'items' => $items];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Google Books', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Google Books no disponible temporalment', 'items' => []];
        }
    }
}
