<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\DailySnapshot;
use App\Models\Habit;
use App\Models\User;
use App\Support\GamificationConstants;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de generació de snapshots diaris.
 * Captura l'estat complet d'un usuari en un moment concret.
 */
class SnapshotService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Captura el snapshot d'un usuari per a una data concreta.
     * Si ja existeix, retorna el registre existent sense duplicar.
     */
    public function captureForUser(User $user, string $data): ?DailySnapshot
    {
        $existent = DailySnapshot::where('usuari_id', $user->id)
            ->where('data', $data)
            ->first();

        if ($existent !== null) {
            return $existent;
        }

        $mascotaJson = $this->buildMascotaJson($user);
        $habitsJson = $this->buildHabitsJson($user, $data);
        $economiaJson = $this->buildEconomiaJson($user, $data);

        $snapshot = DailySnapshot::create([
            'usuari_id' => $user->id,
            'data' => $data,
            'mascota_json' => $mascotaJson,
            'habits_json' => $habitsJson,
            'economia_json' => $economiaJson,
            'created_at' => Carbon::now(),
        ]);

        return $snapshot;
    }

    /**
     * Captura snapshots per a tots els usuaris no prohibits.
     */
    public function captureForAllUsers(string $data): int
    {
        $comptador = 0;

        User::where('prohibit', false)
            ->chunk(100, function ($usuaris) use ($data, &$comptador) {
                foreach ($usuaris as $usuari) {
                    $resultat = $this->captureForUser($usuari, $data);
                    if ($resultat !== null) {
                        $comptador = $comptador + 1;
                    }
                }
            });

        return $comptador;
    }

    //================================ RUTES / LOGICA PRIVADA ========

    /**
     * Construeix mascota_json a partir de la taula USUARIS.
     */
    private function buildMascotaJson(User $user): array
    {
        return [
            'nivell' => $user->nivell,
            'xp_total' => $user->xp_total,
            'xp_actual_nivel' => $user->xp_actual_nivel,
            'xp_objetivo_nivel' => $user->xp_objetivo_nivel,
        ];
    }

    /**
     * Construeix habits_json: hàbits actius amb estat de completació del dia.
     */
    private function buildHabitsJson(User $user, string $data): array
    {
        $habitsActius = DB::select("
            SELECT h.id, h.titol, h.icona, h.color, h.dificultat, h.categoria_id, h.metadata,
                   COALESCE(ra.acabado, false) as acabado
            FROM habits h
            INNER JOIN usuaris_habits uh ON uh.habit_id = h.id
            LEFT JOIN registre_activitat ra ON ra.habit_id = h.id AND DATE(ra.data) = ?
            WHERE uh.usuari_id = ?
              AND uh.actiu = true
        ", [$data, $user->id]);

        $resultat = [];
        foreach ($habitsActius as $habit) {
            $metadata = null;
            if ($habit->metadata !== null) {
                $decoded = json_decode($habit->metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $metadata = $decoded;
                }
            }

            $element = [
                'id' => $habit->id,
                'titol' => $habit->titol,
                'icona' => $habit->icona,
                'color' => $habit->color,
                'dificultat' => $habit->dificultat,
                'categoria_id' => $habit->categoria_id,
                'metadata' => $metadata,
                'acabado' => (bool) $habit->acabado,
            ];
            $resultat[] = $element;
        }

        return $resultat;
    }

    /**
     * Construeix economia_json: XP i monedes guanyades aquell dia.
     */
    private function buildEconomiaJson(User $user, string $data): array
    {
        $registres = DB::select("
            SELECT h.dificultat, ra.xp_guanyada
            FROM registre_activitat ra
            INNER JOIN habits h ON h.id = ra.habit_id
            INNER JOIN usuaris_habits uh ON uh.habit_id = h.id AND uh.usuari_id = ?
            WHERE DATE(ra.data) = ?
              AND ra.acabado = true
        ", [$user->id, $data]);

        $xpTotal = 0;
        $monedesTotal = 0;

        foreach ($registres as $registre) {
            $xpTotal = $xpTotal + (int) $registre->xp_guanyada;

            $dificultat = $registre->dificultat;
            if (isset(GamificationConstants::MONEDES_PER_DIFICULTAT[$dificultat])) {
                $monedesTotal = $monedesTotal + GamificationConstants::MONEDES_PER_DIFICULTAT[$dificultat];
            } else {
                $monedesTotal = $monedesTotal + GamificationConstants::MONEDES_DEFECTE;
            }
        }

        return [
            'xp_guanyada_avui' => $xpTotal,
            'monedes_guanyades_avui' => $monedesTotal,
        ];
    }
}
