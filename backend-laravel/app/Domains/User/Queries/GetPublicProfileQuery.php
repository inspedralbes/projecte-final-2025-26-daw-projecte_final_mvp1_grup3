<?php

declare(strict_types=1);

namespace App\Domains\User\Queries;

use App\Models\LogroMedalla;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Perfil públic d'un usuari amb ratxes, logros i cosmètics.
 */
class GetPublicProfileQuery
{
    /**
     * @return array<string, mixed>
     */
    public function executar(int $userId): array
    {
        $user = User::findOrFail($userId);

        $logrosShowcase = [];
        $showcaseValue = $user->logros_showcase;

        if ($showcaseValue && $showcaseValue !== '{}') {
            $showcaseIds = str_replace(['{', '}'], ['', ''], $showcaseValue);
            $showcaseIds = array_filter(array_map('intval', explode(',', $showcaseIds)));
            if (! empty($showcaseIds)) {
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

        $ratxa = DB::table('ratxes')->where('usuari_id', $userId)->first();
        $ratxaActual = $ratxa ? (int) $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? (int) $ratxa->ratxa_maxima : 0;

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

        return [
            'id' => $user->id,
            'nom' => $user->nom,
            'nivell' => (int) $user->nivell,
            'xp_total' => (int) $user->xp_total,
            'xp_actual_nivel' => (int) ($user->xp_actual_nivel ?? 0),
            'xp_objetivo_nivel' => (int) ($user->xp_objetivo_nivel ?? 1000),
            'monedes' => (int) $user->monedes,
            'streak' => $ratxaActual,
            'streak_maxima' => $ratxaMaxima,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'logros' => $logrosAll,
            'logros_showcase' => $logrosShowcase,
            'monstre_tipus' => $user->monstre_tipus,
            'skin_key' => $user->skin_key,
            'fons_key' => $user->fons_key,
        ];
    }
}
