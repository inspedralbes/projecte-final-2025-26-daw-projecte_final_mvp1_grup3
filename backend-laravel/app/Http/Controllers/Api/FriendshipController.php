<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\Usuari;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'addressee_id' => 'required|integer|exists:usuaris,id',
        ]);

        $requesterId = $request->user_id;

        if ($requesterId === $validated['addressee_id']) {
            return response()->json(['error' => 'No pots enviar-te sol·licitud d\'amistat a tu mateix'], 400);
        }

        $existing = Friendship::where(function ($query) use ($requesterId, $validated) {
            $query->where(function ($q) use ($requesterId, $validated) {
                $q->where('requester_id', $requesterId)->where('addressee_id', $validated['addressee_id']);
            })->orWhere(function ($q) use ($requesterId, $validated) {
                $q->where('requester_id', $validated['addressee_id'])->where('addressee_id', $requesterId);
            });
        })->first();

        if ($existing) {
            return response()->json(['error' => 'Ja existeix una relació d\'amistat'], 409);
        }

        $friendship = Friendship::create([
            'requester_id' => $requesterId,
            'addressee_id' => $validated['addressee_id'],
            'status' => 'pending',
        ]);

        return response()->json($friendship, 201);
    }

    public function acceptRequest(int $id, Request $request): JsonResponse
    {
        $friendship = Friendship::findOrFail($id);

        if ($friendship->addressee_id !== $request->user_id) {
            return response()->json(['error' => 'No tens permís per acceptar aquesta sol·licitud'], 403);
        }

        if ($friendship->status !== 'pending') {
            return response()->json(['error' => 'La sol·licitud no està pendent'], 400);
        }

        $friendship->update(['status' => 'accepted']);
        $this->checkAchievement($request->user_id);

        return response()->json($friendship);
    }

    public function rejectRequest(int $id, Request $request): JsonResponse
    {
        $friendship = Friendship::findOrFail($id);

        if ($friendship->addressee_id !== $request->user_id) {
            return response()->json(['error' => 'No tens permís per rebutjar aquesta sol·licitud'], 403);
        }

        if ($friendship->status !== 'pending') {
            return response()->json(['error' => 'La sol·licitud no està pendent'], 400);
        }

        $friendship->update(['status' => 'rejected']);

        return response()->json($friendship);
    }

    public function getFriendsList(Request $request): JsonResponse
    {
        $userId = $request->user_id;

        $friends = Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                ->orWhere('addressee_id', $userId);
        })
            ->where('status', 'accepted')
            ->with(['requester:id,nom,nivell,xp_total', 'addressee:id,nom,nivell,xp_total'])
            ->get()
            ->map(function ($friendship) use ($userId) {
                $friend = $friendship->requester_id === $userId ? $friendship->addressee : $friendship->requester;
                return [
                    'id' => $friendship->id,
                    'friend' => $friend,
                    'created_at' => $friendship->created_at,
                ];
            });

        return response()->json($friends);
    }

    public function getPendingRequests(Request $request): JsonResponse
    {
        $userId = $request->user_id;

        $pending = Friendship::where('addressee_id', $userId)
            ->where('status', 'pending')
            ->with('requester:id,nom,nivell,xp_total')
            ->get()
            ->map(function ($friendship) {
                return [
                    'id' => $friendship->id,
                    'requester' => $friendship->requester,
                    'created_at' => $friendship->created_at,
                ];
            });

        return response()->json($pending);
    }

    private function checkAchievement(int $userId): void
    {
        $friendCount = Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                ->orWhere('addressee_id', $userId);
        })
            ->where('status', 'accepted')
            ->count();

        if ($friendCount === 5) {
            DB::table('usuaris_logros')->insertOrIgnore([
                'usuari_id' => $userId,
                'logro_id' => 1,
                'data_obtencio' => now()->toDateString(),
            ]);
        }
    }
}