<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Services\RedisFeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    protected RedisFeedbackService $redisFeedback;

    public function __construct(RedisFeedbackService $redisFeedback)
    {
        $this->redisFeedback = $redisFeedback;
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        $posts = SocialPost::with(['user:id,nom', 'habit', 'plantilla.habits'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_current_user' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'habit_id' => 'nullable|integer|exists:habits,id',
            'plantilla_id' => 'nullable|integer|exists:plantilles,id',
        ]);

        $post = SocialPost::create([
            'user_id' => $request->user_id,
            'content' => $validated['content'],
            'habit_id' => $validated['habit_id'] ?? null,
            'plantilla_id' => $validated['plantilla_id'] ?? null,
        ]);

        $post->load(['user:id,nom', 'habit', 'plantilla.habits']);
        $post->loadCount(['comments', 'likes']);
        $post->loadExists(['likes as liked_by_current_user' => function ($query) use ($request) {
            $query->where('user_id', $request->user_id);
        }]);

        // Emetre esdeveniment per a temps real
        $this->redisFeedback->publicarPayload([
            'social_event' => 'new_post',
            'post' => $post
        ]);

        return response()->json($post, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user_id;
        $post = SocialPost::with(['user:id,nom', 'habit', 'plantilla.habits'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_current_user' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->findOrFail($id);

        return response()->json($post);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = SocialPost::where('id', $id)
            ->where('user_id', $request->user_id)
            ->firstOrFail();

        $post->delete();

        return response()->json(['message' => 'Post eliminat']);
    }
}