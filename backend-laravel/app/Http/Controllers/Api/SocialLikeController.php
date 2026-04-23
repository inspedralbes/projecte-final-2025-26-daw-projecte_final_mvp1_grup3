<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialLike;
use App\Models\SocialPost;
use App\Models\SocialComment;
use App\Services\RedisFeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialLikeController extends Controller
{
    protected RedisFeedbackService $redisFeedback;

    public function __construct(RedisFeedbackService $redisFeedback)
    {
        $this->redisFeedback = $redisFeedback;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|in:post,comment',
        ]);

        $likeableId = $validated['likeable_id'];
        $likeableType = $validated['likeable_type'];

        $likeableModel = $likeableType === 'post'
            ?SocialPost::class
            : SocialComment::class;

        $existing = SocialLike::where('user_id', $request->user_id)
            ->where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->first();

        if ($existing) {
            $existing->delete();
            $count = SocialLike::where('likeable_id', $likeableId)
                ->where('likeable_type', $likeableModel)
                ->count();

            $this->redisFeedback->publicarPayload([
                'social_event' => 'like_update',
                'likeable_id' => $likeableId,
                'likeable_type' => $likeableType,
                'likes_count' => $count
            ]);

            return response()->json(['liked' => false, 'likes_count' => $count]);
        }

        SocialLike::create([
            'user_id' => $request->user_id,
            'likeable_id' => $likeableId,
            'likeable_type' => $likeableModel,
        ]);

        $count = SocialLike::where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->count();

        $this->redisFeedback->publicarPayload([
            'social_event' => 'like_update',
            'likeable_id' => $likeableId,
            'likeable_type' => $likeableType,
            'likes_count' => $count
        ]);

        return response()->json(['liked' => true, 'likes_count' => $count]);
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|in:post,comment',
        ]);

        $likeableId = $validated['likeable_id'];
        $likeableType = $validated['likeable_type'];

        $likeableModel = $likeableType === 'post'
            ?SocialPost::class
            : SocialComment::class;

        $liked = SocialLike::where('user_id', $request->user_id)
            ->where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->exists();

        $count = SocialLike::where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }
}