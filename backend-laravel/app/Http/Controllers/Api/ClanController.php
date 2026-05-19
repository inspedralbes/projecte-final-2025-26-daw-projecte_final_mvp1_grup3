<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Actions\CreateClanAction;
use App\Domains\Clan\Actions\ImportHabitFromClanMessageAction;
use App\Domains\Clan\Actions\ImportPlantillaFromClanMessageAction;
use App\Domains\Clan\Actions\LeaveClanAction;
use App\Domains\Clan\Actions\SendClanMessageAction;
use App\Domains\Clan\Actions\ShareHabitInClanAction;
use App\Domains\Clan\Actions\SharePlantillaInClanAction;
use App\Domains\Clan\Actions\UpdateClanAction;
use App\Domains\Clan\Queries\GetClanMembersQuery;
use App\Domains\Clan\Queries\GetClanMessagesQuery;
use App\Domains\Clan\Queries\GetClanQuery;
use App\Domains\Clan\Queries\GetMyClanQuery;
use App\Domains\Clan\Queries\ListClansQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * ClanController (thin).
 * Comentaris: agents/backend/AgentLaravel.md
 */
class ClanController extends Controller
{
    private ListClansQuery $listClansQuery;

    private GetClanQuery $getClanQuery;

    private GetMyClanQuery $getMyClanQuery;

    private GetClanMembersQuery $getClanMembersQuery;

    private GetClanMessagesQuery $getClanMessagesQuery;

    private CreateClanAction $createClanAction;

    private UpdateClanAction $updateClanAction;

    private LeaveClanAction $leaveClanAction;

    private SendClanMessageAction $sendClanMessageAction;

    private ShareHabitInClanAction $shareHabitAction;

    private SharePlantillaInClanAction $sharePlantillaAction;

    private ImportHabitFromClanMessageAction $importHabitAction;

    private ImportPlantillaFromClanMessageAction $importPlantillaAction;

    public function __construct(
        ListClansQuery $listClansQuery,
        GetClanQuery $getClanQuery,
        GetMyClanQuery $getMyClanQuery,
        GetClanMembersQuery $getClanMembersQuery,
        GetClanMessagesQuery $getClanMessagesQuery,
        CreateClanAction $createClanAction,
        UpdateClanAction $updateClanAction,
        LeaveClanAction $leaveClanAction,
        SendClanMessageAction $sendClanMessageAction,
        ShareHabitInClanAction $shareHabitAction,
        SharePlantillaInClanAction $sharePlantillaAction,
        ImportHabitFromClanMessageAction $importHabitAction,
        ImportPlantillaFromClanMessageAction $importPlantillaAction
    ) {
        $this->listClansQuery = $listClansQuery;
        $this->getClanQuery = $getClanQuery;
        $this->getMyClanQuery = $getMyClanQuery;
        $this->getClanMembersQuery = $getClanMembersQuery;
        $this->getClanMessagesQuery = $getClanMessagesQuery;
        $this->createClanAction = $createClanAction;
        $this->updateClanAction = $updateClanAction;
        $this->leaveClanAction = $leaveClanAction;
        $this->sendClanMessageAction = $sendClanMessageAction;
        $this->shareHabitAction = $shareHabitAction;
        $this->sharePlantillaAction = $sharePlantillaAction;
        $this->importHabitAction = $importHabitAction;
        $this->importPlantillaAction = $importPlantillaAction;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function index(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        $resultat = $this->listClansQuery->executar($userId);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['clans']);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        $resultat = $this->getClanQuery->executar($userId, $id);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['clan']);
    }

    public function myClan(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        $resultat = $this->getMyClanQuery->executar($userId);

        return response()->json($resultat);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'categoria_id' => 'nullable|integer|between:1,8',
                'max_membres' => 'required|integer|in:10,15,20',
                'es_public' => 'boolean',
            ]);

            $resultat = $this->createClanAction->executar($userId, $validated);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json($resultat['clan'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'nom' => 'string|max:100',
                'max_membres' => 'integer|in:10,15,20',
                'es_public' => 'boolean',
            ]);

            $resultat = $this->updateClanAction->executar($userId, $id, $validated);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json($resultat['clan']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function leave(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->leaveClanAction->executar($userId, $id);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function members(int $id, Request $request): JsonResponse
    {
        $resultat = $this->getClanMembersQuery->executar($id);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['members']);
    }

    public function messages(int $id, Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        $resultat = $this->getClanMessagesQuery->executar($id, $userId);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json($resultat['messages']);
    }

    public function sendMessage(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'contingut' => 'required|string|max:1000',
                'habit_id' => 'nullable|integer',
                'plantilla_id' => 'nullable|integer',
            ]);

            $resultat = $this->sendClanMessageAction->executar($userId, $id, $validated);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json([
                'id' => $resultat['id'],
                'success' => true,
                'message' => $resultat['message'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function shareHabit(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'habit_id' => 'required|integer',
            ]);

            $resultat = $this->shareHabitAction->executar($userId, $id, (int) $validated['habit_id']);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['id' => $resultat['id'], 'success' => true], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sharePlantilla(int $id, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $validated = $request->validate([
                'plantilla_id' => 'required|integer',
            ]);

            $resultat = $this->sharePlantillaAction->executar($userId, $id, (int) $validated['plantilla_id']);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['id' => $resultat['id'], 'success' => true], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importHabit(int $id, int $messageId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->importHabitAction->executar($userId, $id, $messageId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true, 'habit_id' => $resultat['habit_id']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importPlantilla(int $id, int $messageId, Request $request): JsonResponse
    {
        try {
            $userId = (int) ($request->user_id ?? 0);
            $resultat = $this->importPlantillaAction->executar($userId, $id, $messageId);

            if (!$resultat['success']) {
                return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
            }

            return response()->json(['success' => true, 'plantilla_id' => $resultat['plantilla_id']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
