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
        $posts = SocialPost::with(['user', 'habit', 'plantilla.habits'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_current_user' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        foreach ($posts as $post) {
            $post->attachments = $this->loadAttachmentsModels($post);
        }

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

        $post = SocialPost::create([
            'user_id' => $request->user_id,
            'content' => $validated['content'],
            'habit_id' => $validated['habit_id'] ?? null,
            'plantilla_id' => $validated['plantilla_id'] ?? null,
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $post->load(['user', 'habit', 'plantilla.habits']);
        $post->loadCount(['comments', 'likes']);
        $post->loadExists(['likes as liked_by_current_user' => function ($query) use ($request) {
            $query->where('user_id', $request->user_id);
        }]);

        $post->attachments = $this->loadAttachmentsModels($post);

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
        $post = SocialPost::with(['user', 'habit', 'plantilla.habits'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_current_user' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->findOrFail($id);

        $post->attachments = $this->loadAttachmentsModels($post);

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

    protected function loadAttachmentsModels($post)
    {
        $attachments = $post->attachments;
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true);
        }
        if (!is_array($attachments)) {
            $attachments = [];
        }

        if (empty($attachments)) {
            if ($post->habit_id) {
                $attachments[] = [
                    'type' => 'habit',
                    'id' => $post->habit_id,
                ];
            }
            if ($post->plantilla_id) {
                $attachments[] = [
                    'type' => 'plantilla',
                    'id' => $post->plantilla_id,
                ];
            }
        }

        $result = [];
        foreach ($attachments as $att) {
            if (isset($att['type']) && isset($att['id'])) {
                if ($att['type'] === 'habit') {
                    $habit = \App\Models\Habit::find($att['id']);
                    if ($habit) {
                        $att['habit'] = $habit;
                        $att['titol'] = $habit->titol;
                        $result[] = $att;
                    }
                } elseif ($att['type'] === 'plantilla') {
                    $plantilla = \App\Models\Plantilla::with('habits')->find($att['id']);
                    if ($plantilla) {
                        $att['plantilla'] = $plantilla;
                        $att['titol'] = $plantilla->titol;
                        $result[] = $att;
                    }
                }
            }
        }
        return $result;
    }
}