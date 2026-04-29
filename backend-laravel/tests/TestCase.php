<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static bool $schemaLoaded = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchemaLoaded();
        $this->resetDataForTest();
    }

    protected function tokenForUser(int $userId, string $email = 'user@example.com'): string
    {
        $user = \App\Models\User::find($userId);

        if ($user === null) {
            throw new RuntimeException('No se pudo generar JWT: usuario no encontrado.');
        }

        return JWTAuth::fromUser($user, [
            'role' => 'user',
            'user_id' => $user->id,
            'email' => $email,
        ]);
    }

    private function ensureSchemaLoaded(): void
    {
        if (self::$schemaLoaded) {
            return;
        }

        $sqlPath = dirname(base_path()).'/database/init.sql';
        if (!file_exists($sqlPath)) {
            throw new RuntimeException('No existe database/init.sql para montar el esquema de tests.');
        }

        DB::unprepared(file_get_contents($sqlPath));

        self::$schemaLoaded = true;
    }

    private function resetDataForTest(): void
    {
        DB::statement('TRUNCATE TABLE usuaris_habits, habits, usuaris RESTART IDENTITY CASCADE');
    }
}
