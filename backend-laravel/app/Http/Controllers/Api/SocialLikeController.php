<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\ToggleSocialLikeAction;
use App\Domains\Social\Queries\CheckSocialLikeQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * SocialLikeController (thin).
 */
class SocialLikeController extends Controller
{
    private ToggleSocialLikeAction $toggleLikeAction;

    private CheckSocialLikeQuery $checkLikeQuery;

    public function __construct(
        ToggleSocialLikeAction $toggleLikeAction,
        CheckSocialLikeQuery $checkLikeQuery
    ) {
        $this->toggleLikeAction = $toggleLikeAction;
        $this->checkLikeQuery = $checkLikeQuery;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|in:post,comment',
        ]);

        $userId = (int) $request->user_id;
        $resultat = $this->toggleLikeAction->executar(
            $userId,
            (int) $validated['likeable_id'],
            $validated['likeable_type']
        );

        return response()->json($resultat);
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|in:post,comment',
        ]);

        $userId = (int) $request->user_id;
        $resultat = $this->checkLikeQuery->executar(
            $userId,
            (int) $validated['likeable_id'],
            $validated['likeable_type']
        );

        return response()->json($resultat);
    }
}
