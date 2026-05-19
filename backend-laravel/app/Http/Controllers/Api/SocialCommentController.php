<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\CreateSocialCommentAction;
use App\Domains\Social\Actions\DeleteSocialCommentAction;
use App\Domains\Social\Queries\ListSocialCommentsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * SocialCommentController (thin).
 */
class SocialCommentController extends Controller
{
    private ListSocialCommentsQuery $listCommentsQuery;

    private CreateSocialCommentAction $createCommentAction;

    private DeleteSocialCommentAction $deleteCommentAction;

    public function __construct(
        ListSocialCommentsQuery $listCommentsQuery,
        CreateSocialCommentAction $createCommentAction,
        DeleteSocialCommentAction $deleteCommentAction
    ) {
        $this->listCommentsQuery = $listCommentsQuery;
        $this->createCommentAction = $createCommentAction;
        $this->deleteCommentAction = $deleteCommentAction;
    }

    public function index(Request $request, int $postId): JsonResponse
    {
        $userId = (int) $request->user_id;
        $comments = $this->listCommentsQuery->executar($userId, $postId);

        return response()->json($comments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:social_posts,id',
            'parent_id' => 'nullable|integer|exists:social_comments,id',
            'content' => 'required|string|max:1000',
        ]);

        $userId = (int) $request->user_id;
        $resultat = $this->createCommentAction->executar($userId, $validated);

        if (!$resultat['success']) {
            return response()->json(['message' => $resultat['error']], $resultat['status'] ?? 422);
        }

        return response()->json($resultat['comment'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user_id;
        $this->deleteCommentAction->executar($userId, $id);

        return response()->json(['message' => 'Comentari eliminat']);
    }
}
