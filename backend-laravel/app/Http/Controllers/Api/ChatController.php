<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\PrivateMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // Debug endpoint that ACTUALLY saves to DB
    public function sendMessageDebug(Request $request, int $receiverId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'contingut' => 'required|string|max:2000',
                'sender_id' => 'required|integer',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validació fallida: ' . $e->getMessage()], 400);
        }

        $senderId = $validated['sender_id'];
        
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

    public function getChatHistoryDebug(Request $request, int $friendId): JsonResponse
    {
        $userId = $request->input('user_id', 1);

        $messages = PrivateMessage::where(function ($query) use ($userId, $friendId) {
            $query->where(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $userId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $userId);
            });
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $messages]);
    }
    public function sendMessage(Request $request, int $receiverId): JsonResponse
    {
        $validated = $request->validate([
            'contingut' => 'required|string|max:2000',
            'sender_id' => 'required|integer|min:1',
        ]);

        $senderId = $validated['sender_id'];

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
        $userId = $request->user_id ?? 0;

        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $messages = PrivateMessage::where(function ($query) use ($userId, $friendId) {
            $query->where(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $userId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $userId);
            });
        })
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json($messages);
    }

    public function markAsReadDebug(Request $request, int $friendId): JsonResponse
    {
        $userId = $request->input('user_id', 1);

        PrivateMessage::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAsRead(Request $request, int $friendId): JsonResponse
    {
        $userId = $request->user_id ?? 0;

        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        PrivateMessage::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}