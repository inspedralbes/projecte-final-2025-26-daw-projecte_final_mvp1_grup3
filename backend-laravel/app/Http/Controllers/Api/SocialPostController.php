<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $posts = SocialPost::with(['user:id,nom', 'habit', 'plantilla'])
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

        $post->load(['user:id,nom', 'habit', 'plantilla']);

        return response()->json($post, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $post = SocialPost::with(['user:id,nom', 'habit', 'plantilla'])
            ->withCount('comments')
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