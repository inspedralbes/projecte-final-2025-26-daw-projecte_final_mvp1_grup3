<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogroMedalla;
use App\Services\UserAccountUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function getPublicProfile(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $logrosShowcase = [];
        $showcaseValue = $user->logros_showcase;

        if ($showcaseValue && $showcaseValue !== '{}') {
            $showcaseIds = str_replace(['{', '}'], ['', ''], $showcaseValue);
            $showcaseIds = array_filter(array_map('intval', explode(',', $showcaseIds)));
            if (is_array($showcaseIds) && !empty($showcaseIds)) {
                $logros = LogroMedalla::whereIn('id', $showcaseIds)->get();
                foreach ($showcaseIds as $logroId) {
                    $logro = $logros->firstWhere('id', $logroId);
                    if ($logro) {
                        $logrosShowcase[] = [
                            'id' => $logro->id,
                            'nom' => $logro->nom,
                            'descripcio' => $logro->descripcio,
                            'tipus' => $logro->tipus,
                        ];
                    }
                }
            }
        }

        $ratxa = \DB::table('ratxes')->where('usuari_id', $id)->first();
        $ratxaActual = $ratxa ? $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? $ratxa->ratxa_maxima : 0;

        $user->load('logros');
        $logrosAll = [];
        foreach ($user->logros as $logro) {
            $dataObtencio = null;
            if ($logro->pivot && isset($logro->pivot->data_obtencio)) {
                $dataObtencio = $logro->pivot->data_obtencio;
            }
            $logrosAll[] = [
                'id' => $logro->id,
                'nom' => $logro->nom,
                'descripcio' => $logro->descripcio,
                'tipus' => $logro->tipus,
                'data_obtencio' => $dataObtencio,
            ];
        }

        $response = [
            'id' => $user->id,
            'nom' => $user->nom,
            'nivell' => (int) $user->nivell,
            'xp_total' => (int) $user->xp_total,
            'xp_actual_nivel' => (int) ($user->xp_actual_nivel ?? 0),
            'xp_objetivo_nivel' => (int) ($user->xp_objetivo_nivel ?? 1000),
            'monedes' => (int) $user->monedes,
            'streak' => (int) $ratxaActual,
            'streak_maxima' => (int) $ratxaMaxima,
            'ratxa_actual' => (int) $ratxaActual,
            'ratxa_maxima' => (int) $ratxaMaxima,
            'logros' => $logrosAll,
            'logros_showcase' => $logrosShowcase,
            'monstre_tipus' => $user->monstre_tipus,
        ];

        return response()->json($response);
    }

    public function getPublicLogs(int $id, \App\Services\HabitService $habitService): JsonResponse
    {
        User::findOrFail($id);

        $resultat = $habitService->obtenirLogsHistorics($id);

        return (new \App\Http\Resources\HabitProgressLogResource($resultat))->toResponse(request());
    }


    public function getSelfProfile(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        return $this->getPublicProfile($user->id);
    }

    /**
     * PUT. Actualitza nom, email i opcionalment contrasenya de l'usuari autenticat.
     */
    public function updateAccount(Request $request, UserAccountUpdateService $userAccountUpdateService): JsonResponse
    {
        $userId = (int) $request->user_id;
        $nom = (string) $request->input('nom', '');
        $email = (string) $request->input('email', '');
        $password = $request->input('password');
        if (!is_string($password)) {
            $password = null;
        }

        $resultat = $userAccountUpdateService->actualitzarPerUsuariId($userId, $nom, $email, $password);

        if (!$resultat['ok']) {
            return response()->json([
                'success' => false,
                'errors' => $resultat['errors'],
            ], 422);
        }

        $u = $resultat['user'];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $u->id,
                'nom' => $u->nom,
                'email' => $u->email,
            ],
        ]);
    }

    public function updateShowcase(Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->user_id);

            $logroIds = $request->input('logros', []);

            if (!is_array($logroIds)) {
                return response()->json(['error' => 'Invalid format'], 400);
            }

            if (count($logroIds) > 3) {
                return response()->json(['error' => 'Maximum 3 logros'], 400);
            }

            $user->logros_showcase = '{' . implode(',', $logroIds) . '}';
            $user->save();

            return response()->json(['success' => true, 'logros_showcase' => $logroIds]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}