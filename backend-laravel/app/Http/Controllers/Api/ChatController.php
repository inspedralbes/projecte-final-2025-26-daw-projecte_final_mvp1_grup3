<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\PrivateMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request, int $receiverId): JsonResponse
    {
        $validated = $request->validate([
            'contingut' => 'required|string|max:2000',
        ]);

        $senderId = $request->user_id;

        $friendship = Friendship::where(function ($query) use ($senderId, $receiverId) {
            $query->where(function ($q) use ($senderId, $receiverId) {
                $q->where('requester_id', $senderId)->where('addressee_id', $receiverId);
            })->orWhere(function ($q) use ($senderId, $receiverId) {
                $q->where('requester_id', $receiverId)->where('addressee_id', $senderId);
            });
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['error' => 'Has de ser amic per enviar missatges'], 403);
        }

        $message = PrivateMessage::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'contingut' => $validated['contingut'],
        ]);

        return response()->json($message, 201);
    }

    public function getChatHistory(Request $request, int $friendId): JsonResponse
    {
        $userId = $request->user_id;

        $messages = PrivateMessage::where(function ($query) use ($userId, $friendId) {
            $query->where(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $userId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $userId);
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }

    public function markAsRead(Request $request, int $friendId): JsonResponse
    {
        $userId = $request->user_id;

        PrivateMessage::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}