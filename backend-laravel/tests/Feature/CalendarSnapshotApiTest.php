<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarSnapshotApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array{usuari_id:int,categoria_id:int,habit_id:int}
     */
    private function prepararDadesBasques(string $data): array
    {
        $usuariId = (int) DB::table('usuaris')->insertGetId([
            'nom' => 'Test User',
            'email' => 'test-user-' . uniqid() . '@example.com',
        ]);

        $categoriaId = (int) DB::table('categories')->insertGetId([
            'nom' => 'Salut',
            'color' => '#112233',
        ]);

        $habitId = (int) DB::table('habits')->insertGetId([
            'usuari_id' => $usuariId,
            'plantilla_id' => null,
            'categoria_id' => $categoriaId,
            'titol' => 'Beure aigua',
            'dificultat' => 'facil',
            'frequencia_tipus' => 'diaria',
            'dies_setmana' => null,
            'objectiu_vegades' => 1,
            'unitat' => 'vegades',
            'icona' => 'water',
            'color' => '#445566',
            'metadata' => null,
        ]);

        DB::table('usuaris_habits')->insert([
            'usuari_id' => $usuariId,
            'habit_id' => $habitId,
            'actiu' => true,
        ]);

        DB::table('registre_activitat')->insert([
            'habit_id' => $habitId,
            'data' => $data . ' 12:00:00',
            'valor' => 1,
            'acabado' => true,
            'xp_guanyada' => 100,
            'focus_minutes' => 30,
            'focus_mode' => '25_5',
            'focus_session' => true,
        ]);

        return [
            'usuari_id' => $usuariId,
            'categoria_id' => $categoriaId,
            'habit_id' => $habitId,
        ];
    }

    public function test_snapshot_endpoint_returns_404_when_missing(): void
    {
        $response = $this->get('/api/calendar/snapshot/1/1999-01-01');
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'No snapshot found for this date',
        ]);
    }

    public function test_snapshot_endpoint_returns_json_when_exists(): void
    {
        $data = '2026-05-04';
        $dades = $this->prepararDadesBasques($data);
        $usuariId = $dades['usuari_id'];

        Artisan::call('snapshot:run', ['--date' => $data]);

        $response = $this->get('/api/calendar/snapshot/' . $usuariId . '/' . $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'mascota_json',
            'habits_json',
            'economia_json',
            'data',
        ]);
        $response->assertJson([
            'data' => $data,
        ]);

        $json = $response->json();
        $this->assertIsArray($json['habits_json']);
        $this->assertNotEmpty($json['habits_json']);

        $habit = $json['habits_json'][0];
        $this->assertArrayHasKey('id', $habit);
        $this->assertArrayHasKey('titol', $habit);
        $this->assertArrayHasKey('icona', $habit);
        $this->assertArrayHasKey('color', $habit);
        $this->assertArrayHasKey('dificultat', $habit);
        $this->assertArrayHasKey('categoria_id', $habit);
        $this->assertArrayHasKey('metadata', $habit);
        $this->assertArrayHasKey('acabado', $habit);
        $this->assertArrayHasKey('completed_with_focus', $habit);
        $this->assertArrayHasKey('predominant_focus_mode', $habit);
        $this->assertTrue($habit['acabado']);
        $this->assertTrue((bool) $habit['completed_with_focus']);
        $this->assertSame('25_5', $habit['predominant_focus_mode']);

        $this->assertSame(100, (int) $json['economia_json']['xp_guanyada_avui']);
        $this->assertSame(2, (int) $json['economia_json']['monedes_guanyades_avui']);
    }

    public function test_month_endpoint_returns_category_colors_for_completed_habits(): void
    {
        $data = '2026-05-04';
        $dades = $this->prepararDadesBasques($data);
        $usuariId = $dades['usuari_id'];

        Artisan::call('snapshot:run', ['--date' => $data]);

        $response = $this->get('/api/calendar/month/' . $usuariId . '/2026/5');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertIsArray($json);

        $diaTrobat = null;
        foreach ($json as $element) {
            if (isset($element['day']) && (int) $element['day'] === 4) {
                $diaTrobat = $element;
                break;
            }
        }

        $this->assertNotNull($diaTrobat);
        $this->assertTrue($diaTrobat['has_snapshot']);
        $this->assertContains('#112233', $diaTrobat['category_colors']);
    }

    public function test_month_endpoint_returns_all_days_as_empty_when_no_snapshots(): void
    {
        $usuariId = (int) DB::table('usuaris')->insertGetId([
            'nom' => 'User No Snapshots',
            'email' => 'user-no-snapshots-' . uniqid() . '@example.com',
        ]);

        $response = $this->get('/api/calendar/month/' . $usuariId . '/2026/6');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertCount(30, $json);

        foreach ($json as $element) {
            $this->assertArrayHasKey('day', $element);
            $this->assertArrayHasKey('has_snapshot', $element);
            $this->assertArrayHasKey('category_colors', $element);
            $this->assertFalse($element['has_snapshot']);
            $this->assertSame([], $element['category_colors']);
        }
    }
}

