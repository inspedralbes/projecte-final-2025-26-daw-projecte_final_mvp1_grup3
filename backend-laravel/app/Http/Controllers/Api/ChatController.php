<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Chat\Actions\MarkChatAsReadAction;
use App\Domains\Chat\Actions\SendPrivateMessageAction;
use App\Domains\Chat\Queries\GetChatHistoryQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ChatController (thin).
 */
class ChatController extends Controller
{
    private SendPrivateMessageAction $sendPrivateMessageAction;

    private GetChatHistoryQuery $getChatHistoryQuery;

    private MarkChatAsReadAction $markChatAsReadAction;

    public function __construct(
        SendPrivateMessageAction $sendPrivateMessageAction,
        GetChatHistoryQuery $getChatHistoryQuery,
        MarkChatAsReadAction $markChatAsReadAction
    ) {
        $this->sendPrivateMessageAction = $sendPrivateMessageAction;
        $this->getChatHistoryQuery = $getChatHistoryQuery;
        $this->markChatAsReadAction = $markChatAsReadAction;
    }

    public function sendMessageDebug(Request $request, int $receiverId): JsonResponse
    {
        $resultat = $this->sendPrivateMessageAction->executar($request, $receiverId, true);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function getChatHistoryDebug(Request $request, int $friendId): JsonResponse
    {
        $resultat = $this->getChatHistoryQuery->executar($request, $friendId, true);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function sendMessage(Request $request, int $receiverId): JsonResponse
    {
        $resultat = $this->sendPrivateMessageAction->executar($request, $receiverId, false);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function getChatHistory(Request $request, int $friendId): JsonResponse
    {
        $resultat = $this->getChatHistoryQuery->executar($request, $friendId, false);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function markAsReadDebug(Request $request, int $friendId): JsonResponse
    {
        $resultat = $this->markChatAsReadAction->executar($request, $friendId, true);

        return response()->json($resultat['body'], $resultat['status']);
    }

    public function markAsRead(Request $request, int $friendId): JsonResponse
    {
        $resultat = $this->markChatAsReadAction->executar($request, $friendId, false);

        return response()->json($resultat['body'], $resultat['status']);
    }
}
