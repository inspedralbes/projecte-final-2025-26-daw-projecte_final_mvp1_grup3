<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        $user = \App\Models\User::find($userId);

        if (!$user || $user->nivell < 5) {
            return response()->json(['error' => 'Nivell 5 requerit'], 403);
        }

        $clans = Clan::withCount('members')
            ->orderBy('nom')
            ->paginate(20);

        return response()->json($clans);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $userId = $request->user_id;
        $user = \App\Models\User::find($userId);

        if (!$user || $user->nivell < 5) {
            return response()->json(['error' => 'Nivell 5 requerit'], 403);
        }

        $clan = Clan::with(['members.usuari', 'lider'])->withCount('members')->find($id);

        if (!$clan) {
            return response()->json(['error' => 'Clan no trobat'], 404);
        }

        return response()->json($clan);
    }

    public function myClan(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        
        $member = DB::table('clan_members')
            ->where('usuari_id', $userId)
            ->first();
        
        if (!$member) {
            return response()->json(['clan' => null]);
        }
        
        $clan = Clan::with(['members.usuari', 'lider'])->find($member->clan_id);
        
        return response()->json(['clan' => $clan]);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $user = \App\Models\User::find($userId);

            if (!$user || $user->nivell < 5) {
                return response()->json(['error' => 'Nivell 5 requerit'], 403);
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'categoria_id' => 'nullable|integer|between:1,8',
                'max_membres' => 'required|integer|in:10,15,20',
                'es_public' => 'boolean',
            ]);

            $clan = Clan::create([
                'nom' => $validated['nom'],
                'categoria_id' => $validated['categoria_id'] ?? null,
                'max_membres' => $validated['max_membres'],
                'es_public' => $validated['es_public'] ?? true,
                'lider_id' => $userId,
            ]);

            DB::table('clan_members')->insert([
                'clan_id' => $clan->id,
                'usuari_id' => $userId,
                'rol' => 'lider',
                'data_unio' => now(),
            ]);

            return response()->json($clan, 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $clan = Clan::find($id);

            if (!$clan) {
                return response()->json(['error' => 'Clan no trobat'], 404);
            }

            if ($clan->lider_id !== $userId) {
                return response()->json(['error' => 'Només el líder pot modificar'], 403);
            }

            $validated = $request->validate([
                'nom' => 'string|max:100',
                'max_membres' => 'integer|in:10,15,20',
                'es_public' => 'boolean',
            ]);

            $clan->update($validated);

            return response()->json($clan);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function leave(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $clan = Clan::find($id);

            if (!$clan) {
                return response()->json(['error' => 'Clan no trobat'], 404);
            }

            if ($clan->lider_id === $userId) {
                return response()->json(['error' => 'El líder no pot sortir, ha de transferir o dissoldre'], 400);
            }

            DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->delete();

            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function members(int $id, Request $request): JsonResponse
    {
        $clan = Clan::find($id);

        if (!$clan) {
            return response()->json(['error' => 'Clan no trobat'], 404);
        }

        $members = DB::table('clan_members')
            ->join('usuaris', 'clan_members.usuari_id', '=', 'usuaris.id')
            ->where('clan_members.clan_id', $id)
            ->select('clan_members.rol', 'clan_members.data_unio', 'usuaris.id as usuari_id', 'usuaris.nom', 'usuaris.nivell')
            ->get();

        return response()->json($members);
    }

    public function messages(int $id, Request $request): JsonResponse
    {
        $userId = $request->user_id;

        $isMember = DB::table('clan_members')
            ->where('clan_id', $id)
            ->where('usuari_id', $userId)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'No eres membre'], 403);
        }

        $messages = DB::table('clan_messages')
            ->join('usuaris', 'clan_messages.usuari_id', '=', 'usuaris.id')
            ->where('clan_messages.clan_id', $id)
            ->select('clan_messages.*', 'usuaris.nom as usuari_nom', 'usuaris.id as usuari_id')
            ->orderBy('clan_messages.created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }

    public function sendMessage(int $id, Request $request): JsonResponse
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
                'contingut' => 'required|string|max:1000',
                'habit_id' => 'nullable|integer',
                'plantilla_id' => 'nullable|integer',
            ]);

            $messageId = DB::table('clan_messages')->insertGetId([
                'clan_id' => $id,
                'usuari_id' => $userId,
                'contingut' => $validated['contingut'],
                'habit_id' => $validated['habit_id'] ?? null,
                'plantilla_id' => $validated['plantilla_id'] ?? null,
                'created_at' => now(),
            ]);

            $message = DB::table('clan_messages')
                ->join('usuaris', 'clan_messages.usuari_id', '=', 'usuaris.id')
                ->where('clan_messages.id', $messageId)
                ->select('clan_messages.*', 'usuaris.nom as usuari_nom', 'usuaris.id as usuari_id')
                ->first();

            return response()->json(['id' => $messageId, 'success' => true, 'message' => $message], 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function shareHabit(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;

            $isMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();

            if (!$isMember) {
                return response()->json(['error' => 'No eres membre'], 403);
            }

            $validated = $request->validate([
                'habit_id' => 'required|integer',
            ]);

            $user = \App\Models\User::find($userId);
            $habits = $user->habits()->where('id', $validated['habit_id'])->exists();

            if (!$habits) {
                return response()->json(['error' => 'Hàbit no trobat'], 404);
            }

            $habit = \App\Models\Habit::find($validated['habit_id']);

            $messageId = DB::table('clan_messages')->insertGetId([
                'clan_id' => $id,
                'usuari_id' => $userId,
                'contingut' => 'Hàbit compartit: ' . $habit->titol,
                'habit_id' => $validated['habit_id'],
                'created_at' => now(),
            ]);

            return response()->json(['id' => $messageId, 'success' => true], 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sharePlantilla(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;

            $isMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();

            if (!$isMember) {
                return response()->json(['error' => 'No eres membre'], 403);
            }

            $validated = $request->validate([
                'plantilla_id' => 'required|integer',
            ]);

            $user = \App\Models\User::find($userId);
            $hasPlantilla = $user->plantilles()->where('id', $validated['plantilla_id'])->exists();

            if (!$hasPlantilla) {
                return response()->json(['error' => 'Plantilla no trobada'], 404);
            }

            $plantilla = \App\Models\Plantilla::find($validated['plantilla_id']);

            $messageId = DB::table('clan_messages')->insertGetId([
                'clan_id' => $id,
                'usuari_id' => $userId,
                'contingut' => 'Plantilla compartida: ' . $plantilla->nom,
                'plantilla_id' => $validated['plantilla_id'],
                'created_at' => now(),
            ]);

            return response()->json(['id' => $messageId, 'success' => true], 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importHabit(int $id, int $messageId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;

            $isMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();

            if (!$isMember) {
                return response()->json(['error' => 'No eres membre'], 403);
            }

            $message = DB::table('clan_messages')
                ->where('clan_id', $id)
                ->where('id', $messageId)
                ->whereNotNull('habit_id')
                ->first();

            if (!$message) {
                return response()->json(['error' => 'Missatge no trobat'], 404);
            }

            $originalHabit = \App\Models\Habit::find($message->habit_id);
            if (!$originalHabit) {
                return response()->json(['error' => 'Hàbit original no trobat'], 404);
            }

            $newHabit = $originalHabit->replicate();
            $newHabit->usuari_id = $userId;
            $newHabit->save();

            return response()->json(['success' => true, 'habit_id' => $newHabit->id]);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importPlantilla(int $id, int $messageId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;

            $isMember = DB::table('clan_members')
                ->where('clan_id', $id)
                ->where('usuari_id', $userId)
                ->exists();

            if (!$isMember) {
                return response()->json(['error' => 'No eres membre'], 403);
            }

            $message = DB::table('clan_messages')
                ->where('clan_id', $id)
                ->where('id', $messageId)
                ->whereNotNull('plantilla_id')
                ->first();

            if (!$message) {
                return response()->json(['error' => 'Missatge no trobat'], 404);
            }

            $originalPlantilla = \App\Models\Plantilla::find($message->plantilla_id);
            if (!$originalPlantilla) {
                return response()->json(['error' => 'Plantilla original no trobada'], 404);
            }

            $newPlantilla = $originalPlantilla->replicate();
            $newPlantilla->save();

            return response()->json(['success' => true, 'plantilla_id' => $newPlantilla->id]);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}