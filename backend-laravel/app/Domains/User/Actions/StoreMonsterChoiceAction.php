<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

use App\Domains\User\Support\MonsterPresentation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Assigna el tipus de monstre a un usuari (onboarding monstre).
 */
class StoreMonsterChoiceAction
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
        $userId = (int) ($request->input('user_id') ?? $request->user_id ?? 0);
        if ($userId <= 0) {
            return ['status' => 401, 'body' => ['error' => 'No autentificat']];
        }

        $usuari = User::find($userId);
        if (!$usuari) {
            return ['status' => 404, 'body' => ['error' => 'Usuari no trobat']];
        }

        if ($usuari->monstre_tipus !== null) {
            return [
                'status' => 409,
                'body' => [
                    'error' => 'Ja tens un monstre assignat',
                    'monstre' => $this->presentation->monsterDataFromUser($usuari),
                ],
            ];
        }

        $tipus = $request->input('monstre_tipus');
        if (!$this->presentation->esTipusValid(is_string($tipus) ? $tipus : null)) {
            return [
                'status' => 422,
                'body' => [
                    'error' => 'Tipus de monstre invàlid. Valors permesos: VV, VR, VL, VA',
                ],
            ];
        }

        try {
            $usuari->monstre_tipus = $tipus;
            $usuari->data_naixement_monstre = Carbon::now();
            $usuari->save();
        } catch (QueryException $e) {
            Log::error('StoreMonsterChoiceAction: error guardant monstre', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => 500,
                'body' => [
                    'error' => 'No s\'ha pogut guardar el monstre. Comprova que la taula usuaris té les columnes monstre_tipus i data_naixement_monstre.',
                ],
            ];
        }

        return [
            'status' => 201,
            'body' => [
                'success' => true,
                'monstre' => $this->presentation->monsterDataFromUser($usuari),
            ],
        ];
    }
}
