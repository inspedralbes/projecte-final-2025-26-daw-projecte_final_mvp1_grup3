<?php

declare(strict_types=1);

namespace App\Domains\User\Queries;

use App\Domains\User\Support\MonsterPresentation;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Consulta del monstre de l'usuari autenticat.
 */
class GetMonsterQuery
{
    private MonsterPresentation $presentation;

    public function __construct(MonsterPresentation $presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function executar(Request $request): array
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return ['status' => 401, 'body' => ['error' => 'No autentificat']];
        }

        $usuari = User::find($userId);
        if (!$usuari) {
            return ['status' => 404, 'body' => ['error' => 'Usuari no trobat']];
        }

        if ($usuari->monstre_tipus === null) {
            return [
                'status' => 404,
                'body' => [
                    'error' => 'L\'usuari no té un monstre assignat',
                    'has_monster' => false,
                ],
            ];
        }

        return [
            'status' => 200,
            'body' => ['monstre' => $this->presentation->monsterDataFromUser($usuari)],
        ];
    }
}
