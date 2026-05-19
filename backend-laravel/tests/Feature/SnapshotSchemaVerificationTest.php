<?php


/**
 * Capa Laravel: SnapshotSchemaVerificationTest.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Tests\Feature;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

//================================ MÈTODES / FUNCIONS ===========

class SnapshotSchemaVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_daily_snapshots_table_contains_expected_columns(): void
    {
        $columnes = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'daily_snapshots')
            ->pluck('column_name')
            ->toArray();

        $esperades = [
            'id',
            'usuari_id',
            'data',
            'mascota_json',
            'habits_json',
            'economia_json',
            'created_at',
        ];

        foreach ($esperades as $columna) {
            $this->assertContains($columna, $columnes);
        }
    }

    public function test_daily_snapshots_has_unique_constraint_for_user_and_date(): void
    {
        $resultat = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM pg_constraint c
            JOIN pg_class t ON t.oid = c.conrelid
            WHERE t.relname = 'daily_snapshots'
              AND c.contype = 'u'
              AND pg_get_constraintdef(c.oid) ILIKE '%(usuari_id, data)%'
        ");

        $this->assertNotNull($resultat);
        $this->assertSame(1, (int) $resultat->total);
    }

    public function test_categories_and_habits_tables_include_calendar_fields(): void
    {
        $categoriesColor = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'categories')
            ->where('column_name', 'color')
            ->exists();

        $habitsMetadata = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'habits')
            ->where('column_name', 'metadata')
            ->exists();

        $this->assertTrue($categoriesColor);
        $this->assertTrue($habitsMetadata);
    }

    public function test_registre_activitat_contains_focus_tracking_columns(): void
    {
        $columnes = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'registre_activitat')
            ->pluck('column_name')
            ->toArray();

        $this->assertContains('focus_minutes', $columnes);
        $this->assertContains('focus_mode', $columnes);
        $this->assertContains('focus_session', $columnes);
    }
}

