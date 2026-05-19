<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\CreateSocialPostAction;
use App\Domains\Social\Actions\DeleteSocialPostAction;
use App\Domains\Social\Queries\GetSocialPostQuery;
use App\Domains\Social\Queries\ListSocialPostsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * SocialPostController (thin).
 */
class SocialPostController extends Controller
{
    private ListSocialPostsQuery $listPostsQuery;

    private GetSocialPostQuery $getPostQuery;

    private CreateSocialPostAction $createPostAction;

    private DeleteSocialPostAction $deletePostAction;

    public function __construct(
        ListSocialPostsQuery $listPostsQuery,
        GetSocialPostQuery $getPostQuery,
        CreateSocialPostAction $createPostAction,
        DeleteSocialPostAction $deletePostAction
    ) {
        $this->listPostsQuery = $listPostsQuery;
        $this->getPostQuery = $getPostQuery;
        $this->createPostAction = $createPostAction;
        $this->deletePostAction = $deletePostAction;
    }

    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user_id;
        $posts = $this->listPostsQuery->executar($userId);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'habit_id' => 'nullable|integer|exists:habits,id',
            'plantilla_id' => 'nullable|integer|exists:plantilles,id',
            'attachments' => 'nullable|array',
        ]);

        $userId = (int) $request->user_id;
        $post = $this->createPostAction->executar($userId, $validated);

        return response()->json($post, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user_id;
        $post = $this->getPostQuery->executar($userId, $id);

        return response()->json($post);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user_id;
        $this->deletePostAction->executar($userId, $id);

        return response()->json(['message' => 'Post eliminat']);
    }
}
