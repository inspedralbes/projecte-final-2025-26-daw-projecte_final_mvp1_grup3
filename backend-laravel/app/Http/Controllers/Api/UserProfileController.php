<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogroMedalla;
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

        $response = [
            'id' => $user->id,
            'nom' => $user->nom,
            'nivell' => $user->nivell,
            'xp_total' => $user->xp_total,
            'streak' => $ratxaActual,
            'streak_maxima' => $ratxaMaxima,
            'logros_showcase' => $logrosShowcase,
        ];

        return response()->json($response);
    }

    public function getSelfProfile(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        return $this->getPublicProfile($user->id);
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