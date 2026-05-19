<?php

namespace App\Domains\AI\Services;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei proxy per APIs externes.
 * Mant├® les claus al backend i retorna respostes normalitzades.
 */
class ExternalApiProxyService
{
    //================================ M├êTODES / FUNCIONS ===========

    /**
     * Cerca llibres a Google Books.
     *
     * @return array<string, mixed>
     */
    public function cercarLlibres(string $query): array
    {
        $apiKey = (string) config('services.google_books.api_key');
        $baseUrl = (string) config('services.google_books.base_url', 'https://www.googleapis.com/books/v1');

        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => 'Google Books no configurat',
                'items' => [],
            ];
        }

        try {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get($baseUrl . '/volumes', [
                    'q' => $query,
                    'maxResults' => 10,
                    'printType' => 'books',
                    'key' => $apiKey,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Error Google Books',
                    'items' => [],
                ];
            }

            $payload = $response->json();
            $volums = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
            $items = [];

            foreach ($volums as $volum) {
                $info = isset($volum['volumeInfo']) && is_array($volum['volumeInfo']) ? $volum['volumeInfo'] : [];
                $imatges = isset($info['imageLinks']) && is_array($info['imageLinks']) ? $info['imageLinks'] : [];
                $items[] = [
                    'api_id' => isset($volum['id']) ? (string) $volum['id'] : '',
                    'titol' => isset($info['title']) ? (string) $info['title'] : 'Sense t├¡tol',
                    'url_imatge' => isset($imatges['thumbnail']) ? (string) $imatges['thumbnail'] : '',
                    'tipus_api' => 'google_books',
                ];
            }

            return [
                'ok' => true,
                'error' => null,
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Google Books', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'error' => 'Google Books no disponible temporalment',
                'items' => [],
            ];
        }
    }

    /**
     * Mapa de paraules clau (es/ca/en) a IDs de categoria de Wger.
     * Categories Wger: 8=Arms, 9=Legs, 10=Abs, 11=Chest, 12=Back, 13=Shoulders, 14=Calves
     *
     * @var array<string, int>
     */
    private const KEYWORD_CATEGORY_MAP = [
        // Pit / Pecho / Chest
        'pit'          => 11, 'pecho'        => 11, 'chest'        => 11,
        'pectoral'     => 11, 'pectorals'    => 11, 'pectorales'   => 11,
        'banca'        => 11, 'press'        => 11, 'bench'        => 11,
        'fondos'       => 11, 'paralleles'   => 11,

        // Esquena / Espalda / Back
        'esquena'      => 12, 'espalda'      => 12, 'back'         => 12,
        'dorsal'       => 12, 'dorsals'      => 12, 'lumbar'       => 12,
        'remo'         => 12, 'rem'          => 12, 'dominada'     => 12,
        'dominades'    => 12, 'jalon'        => 12, 'jal├│n'        => 12,
        'pullup'       => 12, 'pulldown'     => 12,

        // Bra├ºos / Brazos / Arms
        'bras'         => 8,  'brazo'        => 8,  'bra├ºos'       => 8,
        'brazos'       => 8,  'arms'         => 8,  'arm'          => 8,
        'bicep'        => 8,  'biceps'       => 8,  'tricep'       => 8,
        'triceps'      => 8,  'avantbra├º'    => 8,  'antebrazo'    => 8,
        'curl'         => 8,  'extensio'     => 8,  'extension'    => 8,

        // Cames / Piernas / Legs
        'cama'         => 9,  'cames'        => 9,  'pierna'       => 9,
        'piernas'      => 9,  'legs'         => 9,  'leg'          => 9,
        'cuadricep'    => 9,  'quadricep'    => 9,  'femoral'      => 9,
        'quads'        => 9,  'sentadilla'   => 9,  'sentadilles'  => 9,
        'squat'        => 9,  'llunada'      => 9,  'zancada'      => 9,
        'lunge'        => 9,  'peso'         => 9,  'deadlift'     => 9,

        // Espatlles / Hombros / Shoulders
        'espatlla'     => 13, 'espatlles'    => 13, 'hombro'       => 13,
        'hombros'      => 13, 'shoulder'     => 13, 'shoulders'    => 13,
        'deltoid'      => 13, 'deltoides'    => 13, 'militar'      => 13,
        'elevacion'    => 13, 'elevaci├│'     => 13,

        // Abdomen / Core / Abs
        'abdomen'      => 10, 'abdominal'    => 10, 'abs'          => 10,
        'core'         => 10, 'ventre'       => 10, 'barriga'      => 10,
        'crunch'       => 10, 'plancha'      => 10, 'plank'        => 10,
        'abdominals'   => 10,

        // Bessons / Gemelos / Calves
        'besson'       => 14, 'bessons'      => 14, 'gemelo'       => 14,
        'gemelos'      => 14, 'calves'       => 14, 'calf'         => 14,
        'pantorrilla'  => 14, 'pantorrilles' => 14,
    ];

    /**
     * Cerca exercicis a Wger.
     *
     * Si la query coincideix amb una categoria muscular ÔåÆ filtra per categoria.
     * Sin├│ ÔåÆ cerca per nom a trav├®s de exerciseinfo de m├║ltiples categories.
     *
     * @return array<string, mixed>
     */
    public function cercarRutines(string $query): array
    {
        $baseUrl = (string) config('services.wger.base_url', 'https://wger.de/api/v2');
        $token   = (string) config('services.wger.api_token');
        $headers = [];

        if ($token !== '') {
            $headers['Authorization'] = 'Token ' . $token;
        }

        $queryLower = strtolower(trim($query));
        $categoryId = $this->detectarCategoria($queryLower);

        try {
            $params = ['format' => 'json', 'limit' => 15];

            if ($categoryId !== null) {
                $params['category'] = $categoryId;
            }

            $response = Http::withHeaders($headers)
                ->timeout(12)
                ->retry(2, 500)
                ->get($baseUrl . '/exerciseinfo/', $params);

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

                $nom       = '';
                $traduccions = isset($ex['translations']) && is_array($ex['translations']) ? $ex['translations'] : [];

                foreach ($traduccions as $t) {
                    if (is_array($t) && isset($t['language']) && (int) $t['language'] === 2 && isset($t['name']) && $t['name'] !== '') {
                        $nom = (string) $t['name'];
                        break;
                    }
                }

                if ($nom === '' && !empty($traduccions)) {
                    $first = $traduccions[0];
                    $nom   = is_array($first) && isset($first['name']) ? (string) $first['name'] : '';
                }

                if ($nom === '') {
                    continue;
                }

                $apiId  = isset($ex['id']) ? (string) $ex['id'] : '';
                $imatge = '';

                if (isset($ex['images']) && is_array($ex['images']) && count($ex['images']) > 0) {
                    $img = $ex['images'][0];
                    if (is_array($img) && isset($img['image']) && $img['image'] !== '') {
                        $imatge = (string) $img['image'];
                    }
                }

                $items[] = [
                    'api_id'     => $apiId,
                    'titol'      => $nom,
                    'url_imatge' => $imatge,
                    'tipus_api'  => 'wger',
                ];
            }

            return ['ok' => true, 'error' => null, 'items' => array_values($items)];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Wger', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'El servidor d\'exercicis (Wger) est├á lent ara mateix. Torna-ho a intentar en uns segons.', 'items' => []];
        }
    }

    /**
     * Detecta la categoria Wger a partir de la query de l'usuari.
     */
    private function detectarCategoria(string $queryLower): ?int
    {
        $paraules = preg_split('/\s+/', $queryLower, -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($paraules)) {
            $paraules = [$queryLower];
        }

        foreach ($paraules as $paraula) {
            $paraula = trim((string) $paraula, '.,;:!?');
            if (isset(self::KEYWORD_CATEGORY_MAP[$paraula])) {
                return self::KEYWORD_CATEGORY_MAP[$paraula];
            }
        }

        return null;
    }

    /**
     * Obt├® el detall complet d'un exercici de Wger per ID.
     * Extreu nom, categoria, m├║sculs, equipament i descripci├│.
     *
     * @return array<string, mixed>
     */
    public function obtenirDetallExercici(string $exerciseId): array
    {
        $baseUrl = (string) config('services.wger.base_url', 'https://wger.de/api/v2');
        $token = (string) config('services.wger.api_token');
        $headers = [];

        if ($token !== '') {
            $headers['Authorization'] = 'Token ' . $token;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(12)
                ->retry(1, 500)
                ->get($baseUrl . '/exerciseinfo/' . $exerciseId . '/', [
                    'format' => 'json',
                ]);

            if (!$response->successful()) {
                return [
                    'ok'    => false,
                    'error' => 'Exercici no trobat a Wger',
                ];
            }

            $data = $response->json();

            if (!is_array($data)) {
                return [
                    'ok'    => false,
                    'error' => 'Resposta inv├álida de Wger',
                ];
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
                'ok'       => true,
                'exercise' => [
                    'api_id'             => $exerciseId,
                    'titol'              => $nom !== '' ? $nom : 'Exercici',
                    'categoria'          => $categoria,
                    'muscles'            => $muscles,
                    'muscles_secundaris' => $musclesSecundaris,
                    'equipament'         => $equipament,
                    'descripcio'         => $descripcio,
                    'url_imatge'         => $imatge,
                    'tipus_api'          => 'wger',
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Error obtenint detall exercici Wger', ['error' => $e->getMessage()]);

            return [
                'ok'    => false,
                'error' => 'Wger no disponible temporalment',
            ];
        }
    }

    /**
     * Cerca aliments a Open Food Facts via Meilisearch (search.openfoodfacts.org).
     * Endpoint r├ápid, fiable i sense clau d'API.
     *
     * @return array<string, mixed>
     */
    public function cercarAliments(string $query): array
    {
        $searchUrl = (string) config('services.openfoodfacts.search_url', 'https://search.openfoodfacts.org');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Loopy-App/1.0 (contact@loopy.app)',
                'Accept'     => 'application/json',
            ])->timeout(8)
                ->retry(1, 300)
                ->get($searchUrl . '/search', [
                    'q'         => $query,
                    'page_size' => 12,
                    'fields'    => 'product_name,nutriments,image_small_url,_id',
                ]);

            if (!$response->successful()) {
                return [
                    'ok'    => false,
                    'error' => 'Error Open Food Facts',
                    'items' => [],
                ];
            }

            $payload   = $response->json();
            $productes = isset($payload['hits']) && is_array($payload['hits']) ? $payload['hits'] : [];
            $items    = [];

            foreach ($productes as $producte) {
                if (!is_array($producte)) {
                    continue;
                }

                $nom = '';
                if (isset($producte['product_name']) && $producte['product_name'] !== '') {
                    $nom = (string) $producte['product_name'];
                }

                if ($nom === '') {
                    continue;
                }

                $nutriments = isset($producte['nutriments']) && is_array($producte['nutriments'])
                    ? $producte['nutriments']
                    : [];

                $kcal = null;
                foreach (['energy-kcal_100g', 'energy_100g', 'energy-kcal'] as $camp) {
                    if (isset($nutriments[$camp]) && is_numeric($nutriments[$camp])) {
                        $kcal = round((float) $nutriments[$camp]);
                        break;
                    }
                }

                $titol = $nom;
                if ($kcal !== null) {
                    $titol = $nom . ' (' . $kcal . ' kcal/100g)';
                }

                $imatge = '';
                if (isset($producte['image_small_url']) && $producte['image_small_url'] !== '') {
                    $imatge = (string) $producte['image_small_url'];
                }

                $apiId = isset($producte['_id']) && $producte['_id'] !== ''
                    ? (string) $producte['_id']
                    : $nom . '-' . ($kcal ?? '0');

                $items[] = [
                    'api_id'     => $apiId,
                    'titol'      => $titol,
                    'url_imatge' => $imatge,
                    'tipus_api'  => 'openfoodfacts',
                ];

                if (count($items) >= 10) {
                    break;
                }
            }

            return [
                'ok'    => true,
                'error' => null,
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            Log::warning('Error cercant Open Food Facts', ['error' => $e->getMessage()]);

            return [
                'ok'    => false,
                'error' => 'Open Food Facts no disponible temporalment',
                'items' => [],
            ];
        }
    }

    /**
     * Cerca v├¡deos a YouTube.
     *
     * @return array<string, mixed>
     */
    public function cercarVideos(string $query): array
    {
        $apiKey = (string) config('services.youtube.api_key');
        $baseUrl = (string) config('services.youtube.base_url', 'https://www.googleapis.com/youtube/v3');

        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => 'YouTube no configurat',
                'items' => [],
            ];
        }

        try {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get($baseUrl . '/search', [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => 10,
                    'key' => $apiKey,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Error YouTube',
                    'items' => [],
                ];
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

            $duracionsPerVideo = $this->obtenirDuracionsYoutube($baseUrl, $apiKey, $videoIds);
            $items = [];

            foreach ($resultats as $resultat) {
                $idData = isset($resultat['id']) && is_array($resultat['id']) ? $resultat['id'] : [];
                $snippet = isset($resultat['snippet']) && is_array($resultat['snippet']) ? $resultat['snippet'] : [];
                $thumbnails = isset($snippet['thumbnails']) && is_array($snippet['thumbnails']) ? $snippet['thumbnails'] : [];
                $videoId = isset($idData['videoId']) ? (string) $idData['videoId'] : '';
                $duracio = '';

                if ($videoId !== '' && isset($duracionsPerVideo[$videoId])) {
                    $duracio = (string) $duracionsPerVideo[$videoId];
                }

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

            return [
                'ok' => true,
                'error' => null,
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            Log::warning('Error cercant YouTube', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'error' => 'YouTube no disponible temporalment',
                'items' => [],
            ];
        }
    }

    /**
     * @param array<int, string> $videoIds
     * @return array<string, string>
     */
    private function obtenirDuracionsYoutube(string $baseUrl, string $apiKey, array $videoIds): array
    {
        if (count($videoIds) === 0) {
            return [];
        }

        try {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get($baseUrl . '/videos', [
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
                $duracioFormatejada = $this->formatDuracioYoutube($duracioIso);
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

    private function formatDuracioYoutube(string $duracioIso): string
    {
        if ($duracioIso === '') {
            return '';
        }

        $coincidencies = [];
        $teMatch = preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $duracioIso, $coincidencies);
        if ($teMatch !== 1) {
            return '';
        }

        $hores = 0;
        if (isset($coincidencies[1]) && $coincidencies[1] !== '') {
            $hores = (int) $coincidencies[1];
        }

        $minuts = 0;
        if (isset($coincidencies[2]) && $coincidencies[2] !== '') {
            $minuts = (int) $coincidencies[2];
        }

        $segons = 0;
        if (isset($coincidencies[3]) && $coincidencies[3] !== '') {
            $segons = (int) $coincidencies[3];
        }

        if ($hores > 0) {
            return $hores . ':' . str_pad((string) $minuts, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $segons, 2, '0', STR_PAD_LEFT);
        }

        return $minuts . ':' . str_pad((string) $segons, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Consulta context de clima per h├ábits de la llar.
     * Accepta coordenades (lat/lon) o nom de ciutat.
     * Si s'envien coordenades, primer fa reverse geocoding per obtenir el nom de la ciutat.
     *
     * @return array<string, mixed>
     */
    public function obtenirContextClima(?string $city, ?float $lat = null, ?float $lon = null): array
    {
        $apiKey  = (string) config('services.openweather.api_key');
        $baseUrl = (string) config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');

        if ($apiKey === '') {
            return [
                'ok' => false,
                'error' => 'OpenWeather no configurat',
            ];
        }

        if ($lat !== null && $lon !== null) {
            $ciutatResoluta = $this->reversGeocodificarCiutat($lat, $lon);
            if ($ciutatResoluta !== null) {
                $city = $ciutatResoluta;
            }
        }

        if (($city === null || $city === '')) {
            $city = 'Barcelona';
        }

        $params = [
            'units' => 'metric',
            'appid' => $apiKey,
            'q'     => $city,
        ];

        try {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get($baseUrl . '/weather', $params);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'No s\'ha pogut obtenir el clima',
                ];
            }

            $payload     = $response->json();
            $temp        = isset($payload['main']['temp']) ? (float) $payload['main']['temp'] : null;
            $weatherMain = isset($payload['weather'][0]['main'])        ? (string) $payload['weather'][0]['main']        : '';
            $weatherDesc = isset($payload['weather'][0]['description']) ? (string) $payload['weather'][0]['description'] : '';
            $cityName    = isset($payload['name']) && $payload['name'] !== '' ? (string) $payload['name'] : ($city ?? '');
            $suitable    = $this->esClimaAdequat($weatherMain);

            return [
                'ok'          => true,
                'city'        => $cityName,
                'temp'        => $temp,
                'weather'     => $weatherMain,
                'description' => $weatherDesc,
                'suitable'    => $suitable,
                'message'     => $suitable ? 'El clima ├®s adequat per a aquest h├ábit' : 'El clima no ├®s ideal ara mateix',
            ];
        } catch (\Throwable $e) {
            Log::warning('Error consultant clima', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'error' => 'Servei de clima no disponible',
            ];
        }
    }

    //================================ RUTES / LOGICA PRIVADA ========

    /**
     * Converteix coordenades (lat/lon) en nom de ciutat usant Nominatim (OpenStreetMap).
     * Servei gratu├»t, sense clau d'API.
     */
    private function reversGeocodificarCiutat(float $lat, float $lon): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Loopy-App/1.0 (contact@loopy.app)',
                'Accept'     => 'application/json',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat'    => $lat,
                'lon'    => $lon,
                'format' => 'json',
                'zoom'   => 10,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data    = $response->json();
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
        if ($valor === 'thunderstorm') {
            return false;
        }
        if ($valor === 'rain') {
            return false;
        }
        if ($valor === 'snow') {
            return false;
        }

        return true;
    }
}

