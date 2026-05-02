<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicRoutesTest extends TestCase
{
    /**
     * Comprueba que las rutas públicas responden correctamente.
     *
     * @return void
     */
    public function test_onboarding_questions_route_returns_successful_response()
    {
        $response = $this->get('/api/onboarding/questions');
        $response->assertStatus(200);
    }
}
