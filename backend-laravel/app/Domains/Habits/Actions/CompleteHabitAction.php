<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitAccessGuard;
use App\Domains\Habits\Support\HabitProgressReader;
use App\Domains\Habits\Support\HabitRewardCalculator;
use App\Domains\Habits\Support\UserLevelCalculator;
use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use App\Models\User;
use App\Services\GamificationService;
use App\Services\LogroService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Processa la confirmació d'un hàbit completat (XP, ratxes, registre).
 */
class CompleteHabitAction
{
    private HabitAccessGuard $accessGuard;

    private HabitProgressReader $progressReader;

    private HabitRewardCalculator $rewardCalculator;

    private UserLevelCalculator $levelCalculator;

    private GamificationService $gamificationService;

    private LogroService $logroService;

    public function __construct(
        HabitAccessGuard $accessGuard,
        HabitProgressReader $progressReader,
        HabitRewardCalculator $rewardCalculator,
        UserLevelCalculator $levelCalculator,
        GamificationService $gamificationService,
        LogroService $logroService
    ) {
        $this->accessGuard = $accessGuard;
        $this->progressReader = $progressReader;
        $this->rewardCalculator = $rewardCalculator;
        $this->levelCalculator = $levelCalculator;
        $this->gamificationService = $gamificationService;
        $this->logroService = $logroService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    public function executar(array $dades): array
    {
        $habitId = isset($dades['habit_id']) ? (int) $dades['habit_id'] : 0;
        if ($habitId <= 0) {
            throw new \InvalidArgumentException('El camp habit_id és obligatori i ha de ser un enter positiu.');
        }

        $habit = Habit::find($habitId);
        if (!$habit) {
            throw new \InvalidArgumentException("No s'ha trobat l'hàbit amb id {$habitId}.");
        }

        if (isset($dades['user_id']) && $dades['user_id'] > 0) {
            $usuariId = (int) $dades['user_id'];
        } else {
            $usuariId = (int) ($habit->usuari_id);
        }

        if (isset($dades['data']) && $dades['data'] !== null) {
            $timestampComplet = Carbon::parse($dades['data']);
        } else {
            $timestampComplet = Carbon::now();
        }

        $timezone = config('app.timezone', 'Europe/Madrid');
        $dataActivitat = $timestampComplet->copy()->setTimezone($timezone)->startOfDay();

        if (!$this->accessGuard->usuariTeAccesHabit($habitId, $usuariId)) {
            return ['success' => false, 'message' => 'No autoritzat per completar aquest hàbit.'];
        }

        $progresAvui = $this->progressReader->obtenirProgresDiari($habitId, $dataActivitat);
        if ($progresAvui < (int) $habit->objectiu_vegades) {
            return ['success' => false, 'message' => 'Has de completar l\'objectiu abans de finalitzar l\'hàbit.'];
        }

        $jaCompletat = RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $dataActivitat)->where('acabado', true)->exists();
        if ($jaCompletat) {
            return ['success' => false, 'message' => 'Aquest hàbit ja s\'ha completat avui.'];
        }

        $xpGuanyada = $this->rewardCalculator->calcularXPSegonsDificultat($habit->dificultat);
        $monedesGuanyades = $this->rewardCalculator->calcularMonedesSegonsDificultat($habit->dificultat);
        $levelUpData = null;

        DB::transaction(function () use ($habit, $usuariId, $timestampComplet, $xpGuanyada, $monedesGuanyades, &$levelUpData) {
            $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
            if ($usuari === null) {
                throw new \RuntimeException('Usuari no trobat.');
            }

            $nivellData = $this->levelCalculator->aplicarXpINivell($usuari, $xpGuanyada);
            $monedesTotals = (int) $usuari->monedes + $monedesGuanyades + $nivellData['bonus_monedes'];
            $usuari->update([
                'xp_total' => $nivellData['xp_total'],
                'nivell' => $nivellData['nivell'],
                'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                'monedes' => $monedesTotals,
            ]);

            if ($nivellData['level_up'] === true) {
                $levelUpData = [
                    'nivell' => $nivellData['nivell'],
                    'bonus_monedes' => UserLevelCalculator::BONUS_MONEDES_NIVELL,
                    'xp_total' => $nivellData['xp_total'],
                    'monedes' => $monedesTotals,
                ];
            }

            Ratxa::firstOrCreate(
                ['usuari_id' => $usuariId],
                ['ratxa_actual' => 0, 'ratxa_maxima' => 0, 'ultima_data' => null]
            );
            $this->gamificationService->actualitzarRatxa($usuariId);
            $habit->registresActivitat()->create([
                'data' => $timestampComplet,
                'valor' => 0,
                'acabado' => true,
                'xp_guanyada' => $xpGuanyada,
            ]);
        });

        $this->logroService->comprovarLogros($usuariId, $habit);
        $usuari = User::find($usuariId);
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        $ratxaActual = $ratxa ? (int) $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? (int) $ratxa->ratxa_maxima : 0;

        return [
            'success' => true,
            'completed_today' => true,
            'xp_update' => $this->levelCalculator->construirXpUpdateAmbRatxa($usuari, $ratxaActual, $ratxaMaxima),
            'level_up' => $levelUpData,
        ];
    }
}
