<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\User\Actions\StoreMonsterChoiceAction;
use App\Domains\User\Queries\GetMonsterQuery;
use App\Domains\User\Support\MonsterPresentation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MonsterChoiceController (thin).
 */
class MonsterChoiceController extends Controller
{
    private StoreMonsterChoiceAction $storeMonsterChoiceAction;

    private GetMonsterQuery $getMonsterQuery;

    public function __construct(
        StoreMonsterChoiceAction $storeMonsterChoiceAction,
        GetMonsterQuery $getMonsterQuery
    ) {
        $this->storeMonsterChoiceAction = $storeMonsterChoiceAction;
        $this->getMonsterQuery = $getMonsterQuery;
    }

    public function store(Request $request): JsonResponse
    {
        $resultat = $this->storeMonsterChoiceAction->executar($request);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function show(Request $request): JsonResponse
    {
        $resultat = $this->getMonsterQuery->executar($request);

        return response()->json($resultat['body'], $resultat['status']);
    }

    /** @deprecated Utilitza MonsterPresentation::calculateStage */
    public static function calculateStage(int $nivel): string
    {
        return MonsterPresentation::calculateStage($nivel);
    }

    /** @deprecated Utilitza MonsterPresentation::calculateSpriteName */
    public static function calculateSpriteName(?string $tipus, int $nivel): ?string
    {
        return MonsterPresentation::calculateSpriteName($tipus, $nivel);
    }
}
