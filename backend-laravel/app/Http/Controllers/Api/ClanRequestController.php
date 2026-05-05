<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClanRequestController extends Controller
{
    public function joinPublic(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $user = \App\Models\User::find($userId);
            
            if (!$user || $user->nivell < 5) {
                return response()->json(['error' => 'Nivell 5 requerit'], 403);
            }
            
            $clan = \App\Models\Clan::find($id);
            if (!$clan) {
                return response()->json(['error' => 'Clan no trobat'], 404);
            }
            
            if (!$clan->es_public) {
                return response()->json(['error' => 'Clan privat, has de sol·licitar entrada'], 400);
            }
            
            $memberCount = DB::table('clan_members')->where('clan_id', $id)->count();
            if ($memberCount >= $clan->max_membres) {
                return response()->json(['error' => 'Clan ple'], 400);
            }
            
            $exists = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();
            
            if ($exists) {
                return response()->json(['error' => 'Ja eres membre'], 400);
            }
            
            DB::table('clan_members')->insert([
                'clan_id' => $id,
                'usuari_id' => $userId,
                'rol' => 'miembro',
                'data_unio' => now(),
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function requestJoin(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $user = \App\Models\User::find($userId);
            
            if (!$user || $user->nivell < 5) {
                return response()->json(['error' => 'Nivell 5 requerit'], 403);
            }
            
            $clan = \App\Models\Clan::find($id);
            if (!$clan) {
                return response()->json(['error' => 'Clan no trobat'], 404);
            }
            
            if ($clan->es_public) {
                return response()->json(['error' => 'Clan públic, uneix-te directament'], 400);
            }
            
            $exists = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();
            
            if ($exists) {
                return response()->json(['error' => 'Ja eres membre'], 400);
            }
            
            $pendingRequest = DB::table('clan_requests')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->where('estat', 'pendent')
                ->exists();
            
            if ($pendingRequest) {
                return response()->json(['error' => 'Ja has enviat una sol·licitud'], 400);
            }
            
            DB::table('clan_requests')->insert([
                'clan_id' => $id,
                'usuari_id' => $userId,
                'tipus' => 'solicitud',
                'estat' => 'pendent',
                'created_at' => now(),
            ]);
            
            return response()->json(['success' => true], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function invite(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $user = \App\Models\User::find($userId);
            
            if (!$user || $user->nivell < 5) {
                return response()->json(['error' => 'Nivell 5 requerit'], 403);
            }
            
            $isMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();
            
            if (!$isMember) {
                return response()->json(['error' => 'No eres membre'], 403);
            }
            
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:usuaris,id',
            ]);
            
            $targetUser = \App\Models\User::find($validated['user_id']);
            if (!$targetUser || $targetUser->nivell < 5) {
                return response()->json(['error' => 'Usuari necessita nivell 5'], 400);
            }
            
            $clan = \App\Models\Clan::find($id);
            
            $alreadyMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $validated['user_id'])
                ->exists();
            
            if ($alreadyMember) {
                return response()->json(['error' => 'Ja es membre'], 400);
            }
            
            DB::table('clan_requests')->insert([
                'clan_id' => $id,
                'usuari_id' => $validated['user_id'],
                'tipus' => 'invitacion',
                'estat' => 'pendent',
                'invitador_id' => $userId,
                'created_at' => now(),
            ]);
            
            if ($clan->es_public) {
                DB::table('clan_members')->insert([
                    'clan_id' => $id,
                    'usuari_id' => $validated['user_id'],
                    'rol' => 'miembro',
                    'data_unio' => now(),
                ]);
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptRequest(int $requestId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            
            $clanRequest = DB::table('clan_requests')->find($requestId);
            if (!$clanRequest) {
                return response()->json(['error' => 'Sol·licitud no trobada'], 404);
            }
            
            $clan = \App\Models\Clan::find($clanRequest->clan_id);
            if ($clan->lider_id !== $userId) {
                return response()->json(['error' => 'Només el líder pot acceptar'], 403);
            }
            
            if ($clanRequest->estat !== 'pendent') {
                return response()->json(['error' => 'Sol·licitud ja processada'], 400);
            }
            
            $memberCount = DB::table('clan_members')->where('clan_id', $clanRequest->clan_id)->count();
            if ($memberCount >= $clan->max_membres) {
                return response()->json(['error' => 'Clan ple'], 400);
            }
            
            DB::table('clan_members')->insert([
                'clan_id' => $clanRequest->clan_id,
                'usuari_id' => $clanRequest->usuari_id,
                'rol' => 'miembro',
                'data_unio' => now(),
            ]);
            
            DB::table('clan_requests')
                ->where('id', $requestId)
                ->update(['estat' => 'acceptat']);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectRequest(int $requestId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            
            $clanRequest = DB::table('clan_requests')->find($requestId);
            if (!$clanRequest) {
                return response()->json(['error' => 'Sol·licitud no trobada'], 404);
            }
            
            $clan = \App\Models\Clan::find($clanRequest->clan_id);
            if ($clan->lider_id !== $userId) {
                return response()->json(['error' => 'Només el líder pot rebutjar'], 403);
            }
            
            DB::table('clan_requests')
                ->where('id', $requestId)
                ->update(['estat' => 'rebutjat']);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptInvitation(int $invitationId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            
            $invitation = DB::table('clan_requests')->find($invitationId);
            if (!$invitation || $invitation->usuari_id !== $userId) {
                return response()->json(['error' => 'Invitació no trobada'], 404);
            }
            
            $clan = \App\Models\Clan::find($invitation->clan_id);
            
            if (!$clan->es_public) {
                $pendingRequest = DB::table('clan_requests')
                    ->where('clan_id', $invitation->clan_id)
                    ->where('usuari_id', $userId)
                    ->where('tipus', 'solicitud')
                    ->where('estat', 'pendent')
                    ->exists();
                
                if (!$pendingRequest) {
                    DB::table('clan_requests')->insert([
                        'clan_id' => $invitation->clan_id,
                        'usuari_id' => $userId,
                        'tipus' => 'solicitud',
                        'estat' => 'pendent',
                        'invitador_id' => $invitation->invitador_id,
                        'created_at' => now(),
                    ]);
                }
                
                DB::table('clan_requests')
                    ->where('id', $invitationId)
                    ->update(['estat' => 'acceptat']);
                
                return response()->json(['success' => true, 'message' => 'Sol·licitud-enviada']);
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeMember(int $clanId, int $memberId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $clan = \App\Models\Clan::find($clanId);
            
            if (!$clan) {
                return response()->json(['error' => 'Clan no trobat'], 404);
            }
            
            if ($clan->lider_id !== $userId) {
                return response()->json(['error' => 'Només el líder pot expulsar'], 403);
            }
            
            if ($clan->lider_id === $memberId) {
                return response()->json(['error' => 'No pots expulsar el líder'], 400);
            }
            
            DB::table('clan_members')
                ->where('clan_id', $clanId)
                ->where('usuari_id', $memberId)
                ->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPendingRequests(int $id, Request $request): JsonResponse
    {
        $userId = $request->user_id;
        $clan = \App\Models\Clan::find($id);
        
        if (!$clan) {
            return response()->json(['error' => 'Clan no trobat'], 404);
        }
        
        if ($clan->lider_id !== $userId) {
            return response()->json(['error' => 'Només el líder pot veure'], 403);
        }
        
        $requests = DB::table('clan_requests')
            ->join('usuaris', 'clan_requests.usuari_id', '=', 'usuaris.id')
            ->where('clan_requests.clan_id', $id)
            ->where('clan_requests.estat', 'pendent')
            ->select('clan_requests.*', 'usuaris.nom as usuari_nom', 'usuaris.nivell')
            ->get();
        
        return response()->json($requests);
    }
}