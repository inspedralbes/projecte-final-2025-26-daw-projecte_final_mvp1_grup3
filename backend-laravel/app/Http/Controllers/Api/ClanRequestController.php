<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Actions\AcceptClanInvitationAction;
use App\Domains\Clan\Actions\AcceptClanRequestAction;
use App\Domains\Clan\Actions\InviteToClanAction;
use App\Domains\Clan\Actions\JoinPublicClanAction;
use App\Domains\Clan\Actions\RejectClanRequestAction;
use App\Domains\Clan\Actions\RemoveClanMemberAction;
use App\Domains\Clan\Actions\RequestJoinClanAction;
use App\Domains\Clan\Queries\GetPendingClanRequestsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * ClanRequestController (thin).
 * Comentaris: agents/backend/AgentLaravel.md
 */
class ClanRequestController extends Controller
{
    private JoinPublicClanAction $joinPublicAction;

    private RequestJoinClanAction $requestJoinAction;

    private InviteToClanAction $inviteAction;

    private AcceptClanRequestAction $acceptRequestAction;

    private RejectClanRequestAction $rejectRequestAction;

    private AcceptClanInvitationAction $acceptInvitationAction;

    private RemoveClanMemberAction $removeMemberAction;

    private GetPendingClanRequestsQuery $pendingRequestsQuery;

    public function __construct(
        JoinPublicClanAction $joinPublicAction,
        RequestJoinClanAction $requestJoinAction,
        InviteToClanAction $inviteAction,
        AcceptClanRequestAction $acceptRequestAction,
        RejectClanRequestAction $rejectRequestAction,
        AcceptClanInvitationAction $acceptInvitationAction,
        RemoveClanMemberAction $removeMemberAction,
        GetPendingClanRequestsQuery $pendingRequestsQuery
    ) {
        $this->joinPublicAction = $joinPublicAction;
        $this->requestJoinAction = $requestJoinAction;
        $this->inviteAction = $inviteAction;
        $this->acceptRequestAction = $acceptRequestAction;
        $this->rejectRequestAction = $rejectRequestAction;
        $this->acceptInvitationAction = $acceptInvitationAction;
        $this->removeMemberAction = $removeMemberAction;
        $this->pendingRequestsQuery = $pendingRequestsQuery;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function joinPublic(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->joinPublicAction->executar($userId, $id);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function requestJoin(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->requestJoinAction->executar($userId, $id);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function invite(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:usuaris,id',
            ]);

            $resultat = $this->inviteAction->executar($userId, $id, (int) $validated['user_id']);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptRequest(int $requestId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->acceptRequestAction->executar($userId, $requestId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectRequest(int $requestId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->rejectRequestAction->executar($userId, $requestId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptInvitation(int $invitationId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->acceptInvitationAction->executar($userId, $invitationId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            if (isset($resultat['message'])) {
                return response()->json(['success' => true, 'message' => $resultat['message']]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeMember(int $clanId, int $memberId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->removeMemberAction->executar($userId, $clanId, $memberId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPendingRequests(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        $resultat = $this->pendingRequestsQuery->executar($id, $userId);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['requests']);
    }
}
