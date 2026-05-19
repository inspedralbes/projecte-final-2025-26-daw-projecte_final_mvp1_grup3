<?php


/**
 * Capa Laravel: ExternalResourceProxyTest.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Tests\Feature;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

//================================ MÈTODES / FUNCIONS ===========

class ExternalResourceProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\EnsureUserToken::class);
    }

    public function test_books_proxy_returns_normalized_items(): void
    {
        config()->set('services.google_books.api_key', 'test-key');
        config()->set('services.google_books.base_url', 'https://books.test');

        Http::fake([
            'https://books.test/volumes*' => Http::response([
                'items' => [
                    [
                        'id' => 'abc-1',
                        'volumeInfo' => [
                            'title' => 'Atomic Habits',
                            'imageLinks' => [
                                'thumbnail' => 'https://img.test/atomic.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/external/books?q=atomic');

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'items' => [
                [
                    'api_id' => 'abc-1',
                    'titol' => 'Atomic Habits',
                    'url_imatge' => 'https://img.test/atomic.jpg',
                    'tipus_api' => 'google_books',
                ],
            ],
        ]);
    }

    public function test_workouts_proxy_returns_normalized_items(): void
    {
        config()->set('services.wger.base_url', 'https://wger.test');

        Http::fake([
            'https://wger.test/exerciseinfo/*' => Http::response([
                'count'    => 2,
                'results'  => [
                    [
                        'id'           => 192,
                        'translations' => [
                            ['language' => 2, 'name' => 'Bench Press', 'description' => ''],
                        ],
                        'images'       => [
                            ['image' => 'https://img.test/bench.jpg'],
                        ],
                    ],
                    [
                        'id'           => 210,
                        'translations' => [
                            ['language' => 2, 'name' => 'Incline Bench Press', 'description' => ''],
                        ],
                        'images'       => [],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/external/workouts?q=pecho');

        $response->assertOk();
        $response->assertJson([
            'ok'    => true,
            'items' => [
                [
                    'api_id'     => '192',
                    'titol'      => 'Bench Press',
                    'url_imatge' => 'https://img.test/bench.jpg',
                    'tipus_api'  => 'wger',
                ],
                [
                    'api_id'     => '210',
                    'titol'      => 'Incline Bench Press',
                    'url_imatge' => '',
                    'tipus_api'  => 'wger',
                ],
            ],
        ]);
    }

    public function test_provider_proxies_return_controlled_error_on_failure(): void
    {
        config()->set('services.google_books.api_key', 'test-key');
        config()->set('services.google_books.base_url', 'https://books.test');
        config()->set('services.wger.base_url', 'https://wger.test');
        config()->set('services.openfoodfacts.search_url', 'https://off-search.test');
        config()->set('services.youtube.api_key', 'test-key');
        config()->set('services.youtube.base_url', 'https://youtube.test');

        Http::fake([
            'https://books.test/*'      => Http::response([], 500),
            'https://wger.test/*'       => Http::response([], 500),
            'https://off-search.test/*' => Http::response([], 500),
            'https://youtube.test/*'    => Http::response([], 500),
        ]);

        $respostaBooks   = $this->getJson('/api/external/books?q=abc');
        $respostaWger    = $this->getJson('/api/external/workouts?q=abc');
        $respostaNutricio = $this->getJson('/api/external/nutrition?q=apple');
        $respostaYoutube = $this->getJson('/api/external/videos?q=abc');

        $respostaBooks->assertStatus(502)->assertJson(['ok' => false]);
        $respostaWger->assertStatus(502)->assertJson(['ok' => false]);
        $respostaNutricio->assertStatus(502)->assertJson(['ok' => false]);
        $respostaYoutube->assertStatus(502)->assertJson(['ok' => false]);
    }

    public function test_videos_proxy_returns_duration_in_mm_ss_format(): void
    {
        config()->set('services.youtube.api_key', 'test-key');
        config()->set('services.youtube.base_url', 'https://youtube.test');

        Http::fake([
            'https://youtube.test/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'vid-1'],
                        'snippet' => [
                            'title' => 'LoFi Session',
                            'thumbnails' => [
                                'medium' => ['url' => 'https://img.test/vid1.jpg'],
                            ],
                        ],
                    ],
                ],
            ], 200),
            'https://youtube.test/videos*' => Http::response([
                'items' => [
                    [
                        'id' => 'vid-1',
                        'contentDetails' => [
                            'duration' => 'PT3M7S',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/external/videos?q=lofi');

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'items' => [
                [
                    'api_id' => 'vid-1',
                    'titol' => 'LoFi Session',
                    'url_imatge' => 'https://img.test/vid1.jpg',
                    'duracio' => '3:07',
                    'tipus_api' => 'youtube',
                ],
            ],
        ]);
    }

    public function test_exercise_detail_proxy_returns_normalized_data(): void
    {
        config()->set('services.wger.base_url', 'https://wger.test');

        Http::fake([
            'https://wger.test/exerciseinfo/192/*' => Http::response([
                'category'          => ['id' => 11, 'name' => 'Chest'],
                'muscles'           => [['name_en' => 'Pectoralis major']],
                'muscles_secondary' => [['name_en' => 'Anterior deltoid']],
                'equipment'         => [['name' => 'Barbell']],
                'images'            => [['image' => 'https://img.test/bench.jpg']],
                'translations'      => [
                    ['language' => 2, 'name' => 'Bench Press', 'description' => '<p>Exercici de pit bàsic.</p>'],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/external/exercise/192');

        $response->assertOk();
        $response->assertJson([
            'ok'       => true,
            'exercise' => [
                'api_id'             => '192',
                'titol'              => 'Bench Press',
                'categoria'          => 'Chest',
                'muscles'            => ['Pectoralis major'],
                'muscles_secundaris' => ['Anterior deltoid'],
                'equipament'         => ['Barbell'],
                'descripcio'         => 'Exercici de pit bàsic.',
                'url_imatge'         => 'https://img.test/bench.jpg',
                'tipus_api'          => 'wger',
            ],
        ]);
    }

    public function test_exercise_detail_returns_422_for_invalid_id(): void
    {
        $response = $this->getJson('/api/external/exercise/abc');
        $response->assertStatus(422)->assertJson(['ok' => false]);
    }

    public function test_nutrition_proxy_returns_normalized_items(): void
    {
        config()->set('services.openfoodfacts.search_url', 'https://off-search.test');

        Http::fake([
            'https://off-search.test/search*' => Http::response([
                'hits' => [
                    [
                        '_id'             => '0123456789',
                        'product_name'    => 'Apple',
                        'nutriments'      => [
                            'energy-kcal_100g' => 52.0,
                        ],
                        'image_small_url' => 'https://img.test/apple.jpg',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/external/nutrition?q=apple');

        $response->assertOk();
        $response->assertJson([
            'ok'    => true,
            'items' => [
                [
                    'api_id'     => '0123456789',
                    'titol'      => 'Apple (52 kcal/100g)',
                    'url_imatge' => 'https://img.test/apple.jpg',
                    'tipus_api'  => 'openfoodfacts',
                ],
            ],
        ]);
    }
}
