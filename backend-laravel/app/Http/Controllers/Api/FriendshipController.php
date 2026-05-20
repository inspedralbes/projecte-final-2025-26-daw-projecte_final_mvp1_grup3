<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\AcceptFriendRequestAction;
use App\Domains\Social\Actions\RejectFriendRequestAction;
use App\Domains\Social\Actions\RemoveFriendAction;
use App\Domains\Social\Actions\SendFriendRequestAction;
use App\Domains\Social\Queries\ListFriendsQuery;
use App\Domains\Social\Queries\ListPendingFriendRequestsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * FriendshipController (thin).
 */
class FriendshipController extends Controller
{
    private SendFriendRequestAction $sendRequestAction;

    private AcceptFriendRequestAction $acceptRequestAction;

    private RejectFriendRequestAction $rejectRequestAction;

    private RemoveFriendAction $removeFriendAction;

    private ListFriendsQuery $listFriendsQuery;

    private ListPendingFriendRequestsQuery $listPendingQuery;

    public function __construct(
        SendFriendRequestAction $sendRequestAction,
        AcceptFriendRequestAction $acceptRequestAction,
        RejectFriendRequestAction $rejectRequestAction,
        RemoveFriendAction $removeFriendAction,
        ListFriendsQuery $listFriendsQuery,
        ListPendingFriendRequestsQuery $listPendingQuery
    ) {
        $this->sendRequestAction = $sendRequestAction;
        $this->acceptRequestAction = $acceptRequestAction;
        $this->rejectRequestAction = $rejectRequestAction;
        $this->removeFriendAction = $removeFriendAction;
        $this->listFriendsQuery = $listFriendsQuery;
        $this->listPendingQuery = $listPendingQuery;
    }

    public function sendRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'addressee_id' => 'required|integer|exists:usuaris,id',
        ]);

        $requesterId = (int) ($request->user_id ?? 0);
        if ($requesterId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $resultat = $this->sendRequestAction->executar($requesterId, (int) $validated['addressee_id']);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['friendship'], 201);
    }

    public function acceptRequest(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $resultat = $this->acceptRequestAction->executar($userId, $id);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['friendship']);
    }

    public function rejectRequest(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $resultat = $this->rejectRequestAction->executar($userId, $id);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['friendship']);
    }

    public function removeFriend(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $resultat = $this->removeFriendAction->executar($userId, $id);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json(['success' => true]);
    }

    public function getFriendsList(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $friends = $this->listFriendsQuery->executar($userId);

        return response()->json($friends);
    }

    public function getPendingRequests(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $pending = $this->listPendingQuery->executar($userId);

        return response()->json($pending);
    }
}
