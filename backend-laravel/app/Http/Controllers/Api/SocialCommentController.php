<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialCommentController extends Controller
{
    public function index(Request $request, int $postId): JsonResponse
    {
        $userId = $request->user_id;
        $comments = SocialComment::with('user:id,nom')
            ->withCount('likes')
            ->withExists(['likes as liked_by_current_user' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->where('post_id', $postId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:social_posts,id',
            'parent_id' => 'nullable|integer|exists:social_comments,id',
            'content' => 'required|string|max:1000',
        ]);

        $depthLevel = 0;
        if (!empty($validated['parent_id'])) {
            $parent = SocialComment::findOrFail($validated['parent_id']);
            if ($parent->depth_level >= 2) {
                return response()->json([
                    'message' => 'No es pot respondre a un comentari de profunditat 2',
                ], 422);
            }
            $depthLevel = $parent->depth_level + 1;
        }

        $comment = SocialComment::create([
            'post_id' => $validated['post_id'],
            'user_id' => $request->user_id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'depth_level' => $depthLevel,
        ]);

        $comment->load('user:id,nom');
        $comment->loadCount('likes');
        $comment->loadExists(['likes as liked_by_current_user' => function ($query) use ($request) {
            $query->where('user_id', $request->user_id);
        }]);

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = SocialComment::where('id', $id)
            ->where('user_id', $request->user_id)
            ->firstOrFail();

        $comment->delete();

        return response()->json(['message' => 'Comentari eliminat']);
    }
}