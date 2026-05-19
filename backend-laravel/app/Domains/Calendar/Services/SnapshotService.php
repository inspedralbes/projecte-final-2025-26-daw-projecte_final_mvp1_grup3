<?php

namespace App\Domains\Calendar\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Calendar\Support\SnapshotCosmetics;
use App\Models\DailySnapshot;
use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\User;
use App\Models\UsuariItem;
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
            if ($data === Carbon::today()->format('Y-m-d')) {
                $existent->mascota_json = $this->buildMascotaJson($user);
                $existent->save();
            }

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

    /**
     * Actualitza només mascota_json (gorra/fons) del snapshot d'un dia.
     */
    public function refreshMascotaCosmeticsForUser(User $user, ?string $data = null): void
    {
        $data = $data ?? Carbon::today()->format('Y-m-d');

        $snapshot = DailySnapshot::where('usuari_id', $user->id)
            ->where('data', $data)
            ->first();

        if ($snapshot === null) {
            return;
        }

        $snapshot->mascota_json = $this->buildMascotaJson($user);
        $snapshot->save();
    }

    //================================ RUTES / LOGICA PRIVADA ========

    /**
     * Construeix mascota_json a partir de la taula USUARIS.
     */
    private function buildMascotaJson(User $user): array
    {
        $skinKey = null;
        $fonsKey = null;

        $skinsEquipades = UsuariItem::where('usuari_id', $user->id)
            ->where('equipat', true)
            ->whereHas('item', function ($q) {
                $q->where('tipus', 'skin');
            })
            ->with('item')
            ->get();

        foreach ($skinsEquipades as $equipada) {
            if ($equipada->item === null) {
                continue;
            }
            $metadata = $equipada->item->metadata;
            if (!is_array($metadata) || !isset($metadata['skin_key'])) {
                continue;
            }
            $slot = $metadata['slot'] ?? null;
            if ($slot === 'fons') {
                $fonsKey = $metadata['skin_key'];
            } else {
                $skinKey = $metadata['skin_key'];
            }
        }

        $ratxa = Ratxa::where('usuari_id', $user->id)->first();
        $ratxaActual = $ratxa !== null ? (int) $ratxa->ratxa_actual : 0;

        $cosmetics = SnapshotCosmetics::build($skinKey, $fonsKey);

        return array_merge([
            'nivell' => $user->nivell,
            'xp_total' => $user->xp_total,
            'xp_actual_nivel' => $user->xp_actual_nivel,
            'xp_objetivo_nivel' => $user->xp_objetivo_nivel,
            'monstre_tipus' => $user->monstre_tipus,
            'ratxa' => $ratxaActual,
            'monedes' => (int) $user->monedes,
        ], $cosmetics);
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

        $focusPerHabit = DB::select("
            SELECT ra.habit_id,
                   SUM(CASE WHEN ra.focus_mode = '25_5' THEN COALESCE(ra.focus_minutes, 0) ELSE 0 END) AS minutes_25_5,
                   SUM(CASE WHEN ra.focus_mode = '50_10' THEN COALESCE(ra.focus_minutes, 0) ELSE 0 END) AS minutes_50_10,
                   MAX(CASE WHEN ra.focus_session = true AND ra.acabado = true THEN 1 ELSE 0 END) AS completed_with_focus
            FROM registre_activitat ra
            INNER JOIN habits h ON h.id = ra.habit_id
            INNER JOIN usuaris_habits uh ON uh.habit_id = h.id
            WHERE uh.usuari_id = ?
              AND uh.actiu = true
              AND DATE(ra.data) = ?
            GROUP BY ra.habit_id
        ", [$user->id, $data]);

        $focusMap = [];
        foreach ($focusPerHabit as $focusRow) {
            $focusMap[(int) $focusRow->habit_id] = [
                'minutes_25_5' => (int) $focusRow->minutes_25_5,
                'minutes_50_10' => (int) $focusRow->minutes_50_10,
                'completed_with_focus' => ((int) $focusRow->completed_with_focus) === 1,
            ];
        }

        $resultat = [];
        foreach ($habitsActius as $habit) {
            $metadata = null;
            if ($habit->metadata !== null) {
                $decoded = json_decode($habit->metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $metadata = $decoded;
                }
            }

            $focusInfo = $focusMap[(int) $habit->id] ?? [
                'minutes_25_5' => 0,
                'minutes_50_10' => 0,
                'completed_with_focus' => false,
            ];
            $predominantFocusMode = null;
            if ($focusInfo['minutes_25_5'] > 0 || $focusInfo['minutes_50_10'] > 0) {
                $predominantFocusMode = $focusInfo['minutes_50_10'] > $focusInfo['minutes_25_5'] ? '50_10' : '25_5';
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
                'completed_with_focus' => $focusInfo['completed_with_focus'],
                'predominant_focus_mode' => $predominantFocusMode,
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

