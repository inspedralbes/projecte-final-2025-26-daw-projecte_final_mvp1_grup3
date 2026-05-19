<?php


/**
 * Capa Laravel: AdminSocialModerationController.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Domains\Admin\Services\AdminReportBroadcastService;
use App\Domains\Shared\Services\RedisFeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Moderació de posts i comentaris socials des del panell admin.
 */
//================================ MÈTODES / FUNCIONS ===========

class AdminSocialModerationController extends Controller
{
    public function __construct(
        private RedisFeedbackService $redisFeedback,
        private AdminReportBroadcastService $reportBroadcast
    ) {}

    public function showPost(int $id): JsonResponse
    {
        $post = SocialPost::withTrashed()
            ->with(['user:id,nom,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function updatePost(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $post = SocialPost::withTrashed()->findOrFail($id);
        $post->content = $validated['content'];
        $post->save();

        $post->load('user:id,nom');
        $this->redisFeedback->publicarPayload([
            'social_event' => 'post_updated',
            'post' => $post,
        ]);
        $this->reportBroadcast->notificarContingutModerat('UPDATED', [
            'tipus' => 'social_post',
            'post_id' => $post->id,
            'target_contingut' => $post->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post actualitzat correctament.',
            'data' => $post,
        ]);
    }

    public function destroyPost(int $id): JsonResponse
    {
        $post = SocialPost::findOrFail($id);
        $post->delete();

        $this->redisFeedback->publicarPayload([
            'social_event' => 'post_deleted',
            'post_id' => $id,
        ]);
        $this->reportBroadcast->notificarContingutModerat('DELETED', [
            'tipus' => 'social_post',
            'post_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post eliminat correctament.',
        ]);
    }

    public function showComment(int $id): JsonResponse
    {
        $comment = SocialComment::with(['user:id,nom,email', 'post:id,content'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $comment,
        ]);
    }

    public function updateComment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = SocialComment::findOrFail($id);
        $comment->content = $validated['content'];
        $comment->save();

        $comment->load('user:id,nom');
        $this->redisFeedback->publicarPayload([
            'social_event' => 'comment_updated',
            'comment' => $comment,
        ]);
        $this->reportBroadcast->notificarContingutModerat('UPDATED', [
            'tipus' => 'social_comment',
            'post_id' => $comment->id,
            'target_contingut' => $comment->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentari actualitzat correctament.',
            'data' => $comment,
        ]);
    }

    public function destroyComment(int $id): JsonResponse
    {
        $comment = SocialComment::findOrFail($id);
        $postId = $comment->post_id;
        $comment->delete();

        $this->redisFeedback->publicarPayload([
            'social_event' => 'comment_deleted',
            'comment_id' => $id,
            'post_id' => $postId,
        ]);
        $this->reportBroadcast->notificarContingutModerat('DELETED', [
            'tipus' => 'social_comment',
            'post_id' => $id,
            'parent_post_id' => $postId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentari eliminat correctament.',
        ]);
    }
}

