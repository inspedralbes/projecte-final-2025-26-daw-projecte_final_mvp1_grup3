<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use App\Models\User;
use App\Models\UsuariHabit;
use App\Services\MissionService;
use App\Support\GamificationConstants;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de processament d'hàbits.
 * Gestiona el CRUD d'hàbits i el càlcul d'XP amb ratxes.
 * Totes les operacions a PostgreSQL s'executen dins d'una transacció.
 */
class HabitService
{
    /**
     * Configuració de nivell.
     */
    private const XP_BASE_NIVELL = 1000;
    private const MULTIPLICADOR_NIVELL = 1.2;
    private const BONUS_MONEDES_NIVELL = 10;

    /**
     * Servei de feedback per Redis.
     */
    private RedisFeedbackService $feedbackService;

    /**
     * Servei de gestió de logros.
     */
    private LogroService $logroService;

    /**
     * Servei de missions diàries.
     */
    private MissionService $missionService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei de feedback i logros i missions.
     */
    public function __construct(RedisFeedbackService $feedbackService, LogroService $logroService, MissionService $missionService)
    {
        $this->feedbackService = $feedbackService;
        $this->logroService = $logroService;
        $this->missionService = $missionService;
    }

    /**
     * Processa una acció d'hàbits (CRUD o TOGGLE) rebuda per Redis.
     * Centralitza el flux i publica el feedback corresponent.
     *
     * @param  array<string, mixed>  $dades
     */
    public function processarAccioHabit(array $dades): void
    {
        if (isset($dades['action'])) {
            $accio = strtoupper((string) $dades['action']);
        } else {
            $accio = '';
        }

        // A2. Validar usuari obligatori (prové del token JWT via Node)
        if (!isset($dades['user_id']) || (int) $dades['user_id'] < 1) {
            return;
        }
        $usuariId = (int) $dades['user_id'];

        if (isset($dades['habit_id'])) {
            $habitId = (int) $dades['habit_id'];
        } else {
            $habitId = 0;
        }

        if (isset($dades['habit_data']) && is_array($dades['habit_data'])) {
            $habitData = $dades['habit_data'];
        } else {
            $habitData = [];
        }

        $success = false;
        $habitModel = null;
        $xpUpdate = null;
        $missionCompleted = null;
        $progress = null;
        $completedToday = null;
        $message = null;

        if ($accio === 'CREATE') {
            $habitModel = $this->crearHabit($usuariId, $habitData);
            $success = $habitModel !== null;
        } elseif ($accio === 'UPDATE') {
            $habitModel = $this->actualitzarHabit($usuariId, $habitId, $habitData);
            $success = $habitModel !== null;
        } elseif ($accio === 'DELETE') {
            $habitModel = $this->eliminarHabit($usuariId, $habitId);
            $success = $habitModel !== null;
        } elseif ($accio === 'PROGRESS') {
            $delta = isset($dades['valor']) ? (int) $dades['valor'] : 1;
            $resultatProgres = $this->processarProgresHabit($habitId, $usuariId, $delta);
            $habitModel = Habit::find($habitId);
            if ($resultatProgres !== null) {
                $success = true;
                $progress = $resultatProgres['progress'];
                $completedToday = $resultatProgres['completed_today'];
                if (isset($resultatProgres['xp_update']) && is_array($resultatProgres['xp_update'])) {
                    $xpUpdate = $resultatProgres['xp_update'];
                }
            } else {
                $success = false;
                $message = 'No s\'ha pogut actualitzar el progrés.';
            }
        } elseif ($accio === 'FOCUS_UPDATE') {
            $focusData = [
                'habit_id' => $habitId,
                'user_id' => $usuariId,
                'mode' => isset($dades['focus_mode']) ? (string) $dades['focus_mode'] : (isset($dades['mode']) ? (string) $dades['mode'] : null),
                'minutes' => isset($dades['focus_minutes']) ? (int) $dades['focus_minutes'] : (isset($dades['minutes']) ? (int) $dades['minutes'] : 0),
                'event' => isset($dades['focus_event']) ? (string) $dades['focus_event'] : (isset($dades['event']) ? (string) $dades['event'] : null),
                'data' => isset($dades['data']) ? $dades['data'] : null,
            ];
            $resultatFocus = $this->processarActualitzacioFocus($focusData);
            $habitModel = Habit::find($habitId);
            $success = (bool) ($resultatFocus['success'] ?? false);
            if (isset($resultatFocus['progress'])) {
                $progress = (int) $resultatFocus['progress'];
            }
            if (isset($resultatFocus['completed_today'])) {
                $completedToday = (bool) $resultatFocus['completed_today'];
            }
            if (isset($resultatFocus['xp_update']) && is_array($resultatFocus['xp_update'])) {
                $xpUpdate = $resultatFocus['xp_update'];
            }
            if ($success !== true) {
                $message = $resultatFocus['message'] ?? 'No s\'ha pogut processar la sessió de focus.';
            }
        } elseif ($accio === 'COMPLETE') {
            $resultatComplete = $this->processarConfirmacioHabit([
                'habit_id' => $habitId,
                'user_id' => $usuariId,
                'data' => isset($dades['data']) ? $dades['data'] : null,
            ]);
            $habitModel = Habit::find($habitId);
            if ($resultatComplete['success'] === true) {
                $success = true;
                if (isset($resultatComplete['xp_update'])) {
                    $xpUpdate = $resultatComplete['xp_update'];
                }
                if (isset($resultatComplete['completed_today'])) {
                    $completedToday = $resultatComplete['completed_today'];
                }
                $resultatMissio = $this->missionService->comprovarMissioCompletada(
                    $usuariId,
                    $habitId,
                    isset($dades['data']) ? Carbon::parse($dades['data']) : Carbon::now()
                );
                if ($resultatMissio !== null && $resultatMissio['completada'] === true) {
                    $missionCompleted = ['success' => true];
                    if (isset($resultatMissio['missio_objectiu'])) {
                        $missionCompleted['missio_objectiu'] = (int) $resultatMissio['missio_objectiu'];
                    }
                    if (isset($resultatMissio['xp_update']) && is_array($resultatMissio['xp_update'])) {
                        $xpUpdate = array_merge($xpUpdate ?? [], $resultatMissio['xp_update']);
                    }
                }
            } else {
                $success = false;
                $message = $resultatComplete['message'] ?? 'No s\'ha pogut completar l\'hàbit.';
            }
        } elseif ($accio === 'EXPORT_HABITS') {
            $plantillaId = isset($dades['plantilla_id']) ? (int) $dades['plantilla_id'] : 0;
            $hàbitsSeleccionats = isset($dades['selected_habits']) ? $dades['selected_habits'] : [];
            $resultatExport = $this->exportarHabitsDePlantilla($usuariId, $plantillaId, $hàbitsSeleccionats);
            $success = $resultatExport['success'];
            if ($success) {
                $hàbitsExportats = $resultatExport['habits'];
            } else {
                $message = $resultatExport['message'];
            }
        // B7. Acció TOGGLE (compatibilitat antiga)
        } elseif ($accio === 'TOGGLE') {
            $resultatComplete = $this->processarConfirmacioHabit([
                'habit_id' => $habitId,
                'user_id' => $usuariId,
                'data' => isset($dades['data']) ? $dades['data'] : null,
            ]);
            $habitModel = Habit::find($habitId);
            if ($resultatComplete['success'] === true) {
                $success = true;
                if (isset($resultatComplete['xp_update'])) {
                    $xpUpdate = $resultatComplete['xp_update'];
                }
                if (isset($resultatComplete['completed_today'])) {
                    $completedToday = $resultatComplete['completed_today'];
                }
            } else {
                $success = false;
                $message = $resultatComplete['message'] ?? 'No s\'ha pogut completar l\'hàbit.';
            }
        } else {
            throw new \InvalidArgumentException('Acció d\'hàbits no reconeguda.');
        }

        $payload = [
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $success,
        ];

        if ($accio === 'EXPORT_HABITS' && isset($hàbitsExportats)) {
            $payload['exported_habits'] = $hàbitsExportats;
        } elseif ($habitModel !== null) {
            $payload['habit'] = $habitModel->toArray();
        }

        if ($xpUpdate !== null) {
            $payload['xp_update'] = $xpUpdate;
        }
        if ($progress !== null) {
            $payload['progress'] = $progress;
        }
        if ($completedToday !== null) {
            $payload['completed_today'] = $completedToday;
        }
        if ($message !== null) {
            $payload['message'] = $message;
        }

        // C3. Afegir mission_completed si s'ha completat la missió (inclou xp_update per ratxa/monedes en temps real)
        if ($missionCompleted !== null) {
            $missionPayload = $missionCompleted;
            if ($xpUpdate !== null) {
                $missionPayload['xp_update'] = $xpUpdate;
            }
            $payload['mission_completed'] = $missionPayload;
        }
        if (isset($resultatComplete) && is_array($resultatComplete) && isset($resultatComplete['level_up'])) {
            $payload['level_up'] = $resultatComplete['level_up'];
        }

        $this->feedbackService->publicarPayload($payload);
    }

    /**
     * Processa una actualització de sessió focus i completa l'hàbit si arriba a l'objectiu.
     *
     * @param array<string,mixed> $dades
     * @return array<string,mixed>
     */
    private function processarActualitzacioFocus(array $dades): array
    {
        $habitId = isset($dades['habit_id']) ? (int) $dades['habit_id'] : 0;
        $usuariId = isset($dades['user_id']) ? (int) $dades['user_id'] : 0;
        $focusMode = isset($dades['mode']) ? strtolower((string) $dades['mode']) : null;
        $minutes = isset($dades['minutes']) ? (int) $dades['minutes'] : 0;
        $event = isset($dades['event']) ? strtolower((string) $dades['event']) : 'update';
        $marcaTemps = isset($dades['data']) && $dades['data'] !== null ? Carbon::parse((string) $dades['data']) : Carbon::now();

        if ($habitId <= 0 || $usuariId <= 0) {
            return ['success' => false, 'message' => 'Dades de focus invàlides.'];
        }

        $habit = Habit::find($habitId);
        if (!$habit || !$this->usuariTeAccesHabit($habitId, $usuariId)) {
            return ['success' => false, 'message' => 'No autoritzat per aquest hàbit.'];
        }

        if (!in_array($focusMode, ['25_5', '50_10'], true)) {
            $focusMode = null;
        }

        if ($minutes < 0) {
            $minutes = 0;
        }

        $registreFocus = RegistreActivitat::create([
            'habit_id' => $habitId,
            'data' => $marcaTemps,
            'valor' => 0,
            'acabado' => false,
            'xp_guanyada' => 0,
            'focus_minutes' => $minutes,
            'focus_mode' => $focusMode,
            'focus_session' => true,
        ]);

        $avui = $marcaTemps->copy()->startOfDay();
        $totalFocusMinutes = (int) RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $avui)
            ->sum('focus_minutes');

        $completedToday = $this->habitCompletatAvui($habitId, $avui);
        $objectiu = (int) ($habit->objectiu_vegades ?? 1);
        $unitat = strtolower((string) ($habit->unitat ?? 'vegades'));

        $llindarMinutes = $unitat === 'minuts' ? $objectiu : $objectiu;
        if ($llindarMinutes <= 0) {
            $llindarMinutes = 1;
        }

        $xpUpdate = null;
        if ($completedToday === false && $totalFocusMinutes >= $llindarMinutes) {
            $resultatComplete = $this->processarConfirmacioHabit([
                'habit_id' => $habitId,
                'user_id' => $usuariId,
                'data' => $marcaTemps->toDateTimeString(),
            ]);
            if (($resultatComplete['success'] ?? false) === true) {
                $completedToday = true;
                // Perquè el calendari pugui marcar `completed_with_focus`,
                // la fila de sessió de focus que ha disparat el completat també
                // ha de quedar amb `acabado=true`.
                $registreFocus->acabado = true;
                $registreFocus->save();
                if (isset($resultatComplete['xp_update']) && is_array($resultatComplete['xp_update'])) {
                    $xpUpdate = $resultatComplete['xp_update'];
                }
            }
        }

        return [
            'success' => true,
            'event' => $event,
            'progress' => $totalFocusMinutes,
            'completed_today' => $completedToday,
            'xp_update' => $xpUpdate,
        ];
    }

    /**
     * Processa un hàbit completat: calcula XP, actualitza ratxes i registra l'activitat.
     * Es rep un array amb habit_id (obligatori) i opcionalment data.
     *
     * @param  array<string, mixed>  $dades  { habit_id: int, data?: string }
     * @return array<string, int>
     */
    public function processarConfirmacioHabit(array $dades): array
    {
        if (isset($dades['habit_id'])) {
            $habitId = (int) $dades['habit_id'];
        } else {
            $habitId = 0;
        }

        if ($habitId <= 0) {
            throw new \InvalidArgumentException('El camp habit_id és obligatori i ha de ser un enter positiu.');
        }

        $habit = Habit::find($habitId);

        if (!$habit) {
            throw new \InvalidArgumentException("No s'ha trobat l'hàbit amb id {$habitId}.");
        }

        // Usuari que completa: del payload JWT (preferent) o propietari de l'hàbit com a fallback
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

        // B2. Data només per a la lògica de ratxa (startOfDay en timezone de l'app)
        $timezone = config('app.timezone', 'Europe/Madrid');
        $dataActivitat = $timestampComplet->copy()->setTimezone($timezone)->startOfDay();

        if (!$this->usuariTeAccesHabit($habitId, $usuariId)) {
            return [
                'success' => false,
                'message' => 'No autoritzat per completar aquest hàbit.',
            ];
        }

        $progresAvui = $this->obtenirProgresDiari($habitId, $dataActivitat);
        if ($progresAvui < (int) $habit->objectiu_vegades) {
            return [
                'success' => false,
                'message' => 'Has de completar l\'objectiu abans de finalitzar l\'hàbit.',
            ];
        }

        $jaCompletat = RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $dataActivitat)
            ->where('acabado', true)
            ->exists();
        if ($jaCompletat) {
            return [
                'success' => false,
                'message' => 'Aquest hàbit ja s\'ha completat avui.',
            ];
        }

        $xpGuanyada = $this->calcularXPSegonsDificultat($habit->dificultat);
        $monedesGuanyades = $this->calcularMonedesSegonsDificultat($habit->dificultat);

        $levelUpData = null;

        DB::transaction(function () use ($habit, $usuariId, $dataActivitat, $timestampComplet, $xpGuanyada, $monedesGuanyades, &$levelUpData) {
            $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
            if ($usuari === null) {
                throw new \RuntimeException('Usuari no trobat.');
            }

            $nivellData = $this->aplicarXpINivell($usuari, $xpGuanyada);
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
                    'bonus_monedes' => self::BONUS_MONEDES_NIVELL,
                    'xp_total' => $nivellData['xp_total'],
                    'monedes' => $monedesTotals,
                ];
            }

            $ratxa = Ratxa::firstOrCreate(
                ['usuari_id' => $usuariId],
                [
                    'ratxa_actual' => 0,
                    'ratxa_maxima' => 0,
                    'ultima_data' => null,
                ]
            );

            $this->actualitzarRatxa($ratxa, $dataActivitat);

            // D3. Inserir fila a REGISTRE_ACTIVITAT amb timestamp complet (hora real)
            $habit->registresActivitat()->create([
                'data' => $timestampComplet,
                'valor' => 0,
                'acabado' => true,
                'xp_guanyada' => $xpGuanyada,
            ]);
        });

        // D5. Comprovar i atorgar logros un cop guardada l'activitat de l'hàbit
        $this->logroService->comprovarLogros($usuariId, $habit);

        $usuari = User::find($usuariId);

        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();

        if ($ratxa === null) {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        } else {
            if (isset($ratxa->ratxa_actual)) {
                $ratxaActual = (int) $ratxa->ratxa_actual;
            } else {
                $ratxaActual = 0;
            }

            if (isset($ratxa->ratxa_maxima)) {
                $ratxaMaxima = (int) $ratxa->ratxa_maxima;
            } else {
                $ratxaMaxima = 0;
            }
        }

        $monedes = isset($usuari->monedes) ? (int) $usuari->monedes : 0;
        $nivell = isset($usuari->nivell) ? (int) $usuari->nivell : 1;
        $xpActualNivell = isset($usuari->xp_actual_nivel) ? (int) $usuari->xp_actual_nivel : 0;
        $xpObjectiuNivell = isset($usuari->xp_objetivo_nivel) ? (int) $usuari->xp_objetivo_nivel : self::XP_BASE_NIVELL;

        return [
            'success' => true,
            'completed_today' => true,
            'xp_update' => [
                'xp_total' => (int) $usuari->xp_total,
                'nivell' => $nivell,
                'xp_actual_nivel' => $xpActualNivell,
                'xp_objetivo_nivel' => $xpObjectiuNivell,
                'ratxa_actual' => $ratxaActual,
                'ratxa_maxima' => $ratxaMaxima,
                'monedes' => $monedes,
            ],
            'level_up' => $levelUpData,
        ];
    }

    /**
     * Calcula l'XP segons la dificultat de l'hàbit.
     * Fàcil: 100 XP, Mitjà: 250 XP, Difícil: 400 XP.
     *
     * @param  string|null  $dificultat
     */
    private function calcularXPSegonsDificultat(?string $dificultat): int
    {
        if ($dificultat === null || $dificultat === '') {
            return GamificationConstants::XP_DEFECTE;
        }

        $clau = strtolower(trim($dificultat));
        $mapXp = GamificationConstants::XP_PER_DIFICULTAT;

        if (array_key_exists($clau, $mapXp)) {
            return $mapXp[$clau];
        }

        return GamificationConstants::XP_DEFECTE;
    }

    /**
     * Calcula les monedes segons la dificultat de l'hàbit.
     *
     * @param  string|null  $dificultat
     */
    private function calcularMonedesSegonsDificultat(?string $dificultat): int
    {
        if ($dificultat === null || $dificultat === '') {
            return GamificationConstants::MONEDES_DEFECTE;
        }

        $clau = strtolower(trim($dificultat));
        $mapMonedes = GamificationConstants::MONEDES_PER_DIFICULTAT;

        if (array_key_exists($clau, $mapMonedes)) {
            return $mapMonedes[$clau];
        }

        return GamificationConstants::MONEDES_DEFECTE;
    }

    /**
     * Normalitza dies_setmana a format Postgres array {t,f,...}.
     *
     * @param mixed $diesSetmana
     */
    private function normalitzarDiesSetmana($diesSetmana): string
    {
        if (is_array($diesSetmana)) {
            $valors = [];
            for ($i = 0; $i < count($diesSetmana); $i++) {
                $valors[] = $diesSetmana[$i] ? 't' : 'f';
            }
            return '{' . implode(',', $valors) . '}';
        }
        if (is_string($diesSetmana)) {
            return $diesSetmana;
        }
        return '{t,t,t,t,t,t,t}';
    }

    /**
     * Retorna true si l'usuari té accés a l'hàbit (propietari o assignat).
     */
    private function usuariTeAccesHabit(int $habitId, int $usuariId): bool
    {
        $habit = Habit::find($habitId);
        if ($habit && (int) $habit->usuari_id === $usuariId) {
            return true;
        }
        return UsuariHabit::where('habit_id', $habitId)
            ->where('usuari_id', $usuariId)
            ->exists();
    }

    /**
     * Obté el progrés diari d'un hàbit (sumatori de valor).
     */
    private function obtenirProgresDiari(int $habitId, Carbon $dataActivitat): int
    {
        $inici = $dataActivitat->copy()->startOfDay();
        $fi = $dataActivitat->copy()->endOfDay();

        $sum = RegistreActivitat::where('habit_id', $habitId)
            ->whereBetween('data', [$inici, $fi])
            ->sum('valor');

        return (int) $sum;
    }

    /**
     * Processa increment/decrement del progrés diari.
     * Si es desfà una completació (restar quan estava completat), es resten XP i monedes.
     *
     * @return array{progress:int, completed_today:bool}|null
     */
    private function processarProgresHabit(int $habitId, int $usuariId, int $delta): ?array
    {
        $habit = Habit::find($habitId);
        if (!$habit || !$this->usuariTeAccesHabit($habitId, $usuariId)) {
            return null;
        }

        $ara = Carbon::now();
        $progresActual = $this->obtenirProgresDiari($habitId, $ara);
        $objectiu = (int) ($habit->objectiu_vegades ?? 1);
        if ($objectiu <= 0) {
            $objectiu = 1;
        }

        if ($delta < 0 && $progresActual <= 0) {
            return [
                'progress' => 0,
                'completed_today' => $this->habitCompletatAvui($habitId, $ara),
            ];
        }

        if ($delta < 0 && ($progresActual + $delta) < 0) {
            $delta = -$progresActual;
        }

        $desferCompletacio = false;
        if ($delta < 0 && $this->habitCompletatAvui($habitId, $ara) && ($progresActual + $delta) < $objectiu) {
            $desferCompletacio = true;
        }

        if ($desferCompletacio) {
            return $this->desferCompletacioIRestarProgres($habit, $usuariId, $ara, $progresActual, $delta);
        }

        RegistreActivitat::create([
            'habit_id' => $habitId,
            'data' => $ara,
            'valor' => $delta,
            'acabado' => false,
            'xp_guanyada' => 0,
        ]);

        $nouProgres = $progresActual + $delta;

        return [
            'progress' => (int) $nouProgres,
            'completed_today' => $this->habitCompletatAvui($habitId, $ara),
        ];
    }

    /**
     * Desfà la completació d'un hàbit: elimina el registre acabado, resta XP i monedes a l'usuari
     * i afegeix el registre de progrés negatiu.
     *
     * @param  Habit  $habit
     * @param  int  $usuariId
     * @param  Carbon  $ara
     * @param  int  $progresActual
     * @param  int  $delta
     * @return array{progress:int, completed_today:bool}
     */
    private function desferCompletacioIRestarProgres(Habit $habit, int $usuariId, Carbon $ara, int $progresActual, int $delta): array
    {
        $habitId = (int) $habit->id;
        $xpARestar = $this->calcularXPSegonsDificultat($habit->dificultat);
        $monedesARestar = $this->calcularMonedesSegonsDificultat($habit->dificultat);

        DB::transaction(function () use ($habitId, $usuariId, $ara, $progresActual, $delta, $xpARestar, $monedesARestar) {
            $registreCompletat = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $ara)
                ->where('acabado', true)
                ->first();

            if ($registreCompletat !== null) {
                $xpReal = (int) ($registreCompletat->xp_guanyada ?? 0);
                if ($xpReal > 0) {
                    $xpARestar = $xpReal;
                }
                $registreCompletat->delete();
            }

            $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
            if ($usuari !== null) {
                $nouXpTotal = max(0, (int) $usuari->xp_total - $xpARestar);
                $novesMonedes = (int) $usuari->monedes - $monedesARestar;
                $nivellData = $this->recalcularNivellDesDeXpTotal($nouXpTotal);

                $usuari->update([
                    'xp_total' => $nouXpTotal,
                    'nivell' => $nivellData['nivell'],
                    'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                    'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                    'monedes' => $novesMonedes,
                ]);
            }

            RegistreActivitat::create([
                'habit_id' => $habitId,
                'data' => $ara,
                'valor' => $delta,
                'acabado' => false,
                'xp_guanyada' => 0,
            ]);
        });

        $nouProgres = $progresActual + $delta;
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        $ratxaActual = $ratxa ? (int) $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? (int) $ratxa->ratxa_maxima : 0;
        $usuari = User::find($usuariId);
        $monedes = $usuari ? (int) $usuari->monedes : 0;
        $nivellData = $this->recalcularNivellDesDeXpTotal($usuari ? (int) $usuari->xp_total : 0);

        return [
            'progress' => (int) $nouProgres,
            'completed_today' => false,
            'xp_update' => [
                'xp_total' => $usuari ? (int) $usuari->xp_total : 0,
                'nivell' => $nivellData['nivell'],
                'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                'ratxa_actual' => $ratxaActual,
                'ratxa_maxima' => $ratxaMaxima,
                'monedes' => $monedes,
            ],
        ];
    }

    /**
     * Recalcula nivell, xp_actual_nivel i xp_objetivo_nivel a partir del xp_total.
     *
     * @return array{nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int}
     */
    private function recalcularNivellDesDeXpTotal(int $xpTotal): array
    {
        if ($xpTotal <= 0) {
            return [
                'nivell' => 1,
                'xp_actual_nivel' => 0,
                'xp_objetivo_nivel' => self::XP_BASE_NIVELL,
            ];
        }
        $nivell = 1;
        $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        $restant = $xpTotal;
        while ($restant >= $xpObjectiu) {
            $restant -= $xpObjectiu;
            $nivell++;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }
        return [
            'nivell' => $nivell,
            'xp_actual_nivel' => $restant,
            'xp_objetivo_nivel' => $xpObjectiu,
        ];
    }

    /**
     * Retorna si l'hàbit està completat avui.
     */
    private function habitCompletatAvui(int $habitId, Carbon $dataActivitat): bool
    {
        return RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $dataActivitat)
            ->where('acabado', true)
            ->exists();
    }

    /**
     * Actualitza la ratxa de l'usuari segons la data de l'activitat.
     * Incrementa ratxa_actual si és dia consecutiu; reseteja a zero si hi ha falta d'activitat.
     * Actualitza ratxa_maxima si la ratxa actual la supera.
     *
     * @param  Ratxa  $ratxa
     * @param  Carbon  $dataActivitat
     */
    private function actualitzarRatxa(Ratxa $ratxa, Carbon $dataActivitat): void
    {
        $timezone = config('app.timezone', 'Europe/Madrid');
        $avui = $dataActivitat->copy()->startOfDay();

        // A. Si hi ha data prèvia, parsejar-la (mateix timezone per a comparacions coherents)
        if ($ratxa->ultima_data !== null) {
            $ultimaData = Carbon::parse($ratxa->ultima_data, $timezone)->startOfDay();
        } else {
            $ultimaData = null;
        }

        $ratxaActual = (int) $ratxa->ratxa_actual;
        $ratxaMaxima = (int) $ratxa->ratxa_maxima;

        // B. Si és el mateix dia, no modifiquem la ratxa (evitar duplicats)
        if ($ultimaData !== null && $ultimaData->isSameDay($avui)) {
            return;
        }

        if ($ultimaData !== null && $avui->gt($ultimaData) && (int) $ultimaData->diffInDays($avui, true) === 1) {
            $ratxaActual++;
        } else {
            // Si hi ha un gap o és la primera vegada: nova ratxa, començar des d'1.
            $ratxaActual = 1;
        }

        $ratxaMaxima = max($ratxaMaxima, $ratxaActual);

        $ratxa->update([
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'ultima_data' => $avui,
        ]);
    }

    /**
     * Reseteja ratxes per inactivitat diària segons timezone Europe/Madrid.
     * Retorna el nombre de ratxes resetejades i emet feedback per a cada usuari.
     */
    public function processarResetRatxesDiaries(?Carbon $dataActual = null): int
    {
        $avui = $dataActual ? $dataActual->copy() : Carbon::now('Europe/Madrid');
        $avui = $avui->setTimezone('Europe/Madrid')->startOfDay();
        $ahir = $avui->copy()->subDay();

        $ratxes = Ratxa::where('ratxa_actual', '>', 0)->get();
        $resetejades = 0;

        foreach ($ratxes as $ratxa) {
            if ($ratxa->ultima_data === null) {
                continue;
            }

            $ultimaData = Carbon::parse($ratxa->ultima_data, 'Europe/Madrid')->startOfDay();

            if ($ultimaData->lt($ahir)) {
                $ratxaAnterior = (int) $ratxa->ratxa_actual;
                $ratxa->update([
                    'ratxa_actual' => 0,
                    'ultima_data' => null,
                ]);

                $this->feedbackService->publicarPayload([
                    'event' => 'streak_broken',
                    'action' => 'STREAK_BROKEN',
                    'user_id' => (int) $ratxa->usuari_id,
                    'ratxa_anterior' => $ratxaAnterior,
                    'ratxa_actual' => 0,
                    'data' => $avui->toDateString(),
                    'message' => "Tu racha de {$ratxaAnterior} días se ha roto!",
                ]);

                $resetejades++;
            }
        }

        return $resetejades;
    }

    /**
     * Crea un hàbit nou per a l'usuari.
     *
     * @param  int  $usuariId
     * @param  array<string, mixed>  $habitData
     */
    private function crearHabit(int $usuariId, array $habitData): ?Habit
    {
        if (!$this->validarShapeMetadata($habitData)) {
            return null;
        }

        $dades = $this->filtrarDadesHabit($habitData);
        $dades['usuari_id'] = $usuariId;

        $habit = Habit::create($dades);

        return $habit;
    }

    /**
     * Actualitza un hàbit existent.
     *
     * @param  int  $usuariId
     * @param  int  $habitId
     * @param  array<string, mixed>  $habitData
     */
    private function actualitzarHabit(int $usuariId, int $habitId, array $habitData): ?Habit
    {
        if (!$this->validarShapeMetadata($habitData)) {
            return null;
        }

        $habit = Habit::find($habitId);

        if (!$habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        $dades = $this->filtrarDadesHabit($habitData);

        if (!empty($dades)) {
            $habit->update($dades);
        }

        return $habit->fresh();
    }

    /**
     * Elimina un hàbit existent.
     *
     * @param  int  $usuariId
     * @param  int  $habitId
     */
    private function eliminarHabit(int $usuariId, int $habitId): ?Habit
    {
        $habit = Habit::find($habitId);

        if (!$habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        $habit->delete();

        return $habit;
    }

    /**
     * Filtra i normalitza les dades d'un hàbit.
     *
     * @param  array<string, mixed>  $habitData
     * @return array<string, mixed>
     */
    private function filtrarDadesHabit(array $habitData): array
    {
        $dades = [];

        if (isset($habitData['plantilla_id'])) {
            $dades['plantilla_id'] = $habitData['plantilla_id'];
        }

        if (isset($habitData['titol'])) {
            $dades['titol'] = $habitData['titol'];
        }

        if (isset($habitData['dificultat'])) {
            $dades['dificultat'] = $habitData['dificultat'];
        }

        if (isset($habitData['frequencia_tipus'])) {
            $dades['frequencia_tipus'] = $habitData['frequencia_tipus'];
        }

        if (isset($habitData['dies_setmana'])) {
            $dades['dies_setmana'] = $this->normalitzarDiesSetmana($habitData['dies_setmana']);
        }

        if (isset($habitData['objectiu_vegades'])) {
            $dades['objectiu_vegades'] = $habitData['objectiu_vegades'];
        }

        if (isset($habitData['unitat'])) {
            $dades['unitat'] = $habitData['unitat'];
        }

        if (isset($habitData['categoria_id'])) {
            $dades['categoria_id'] = $habitData['categoria_id'];
        }

        if (isset($habitData['icona'])) {
            $dades['icona'] = $habitData['icona'];
        }

        if (isset($habitData['color'])) {
            $dades['color'] = $habitData['color'];
        }

        if (array_key_exists('metadata', $habitData)) {
            $meta = $this->normalitzarMetadata($habitData['metadata']);
            $columnaMetadata = $this->obtenirColumnaMetadataHabits();
            if ($meta !== null && $columnaMetadata !== null) {
                $dades[$columnaMetadata] = $meta;
            }
        }

        return $dades;
    }

    /**
     * Compatibilitat d'esquema: alguns entorns antics guarden la columna com "metadada".
     */
    private function obtenirColumnaMetadataHabits(): ?string
    {
        static $columna = null;
        static $inicialitzat = false;

        if ($inicialitzat) {
            return $columna;
        }

        $inicialitzat = true;

        if (Schema::hasColumn('habits', 'metadata')) {
            $columna = 'metadata';
            return $columna;
        }

        if (Schema::hasColumn('habits', 'metadada')) {
            $columna = 'metadada';
            return $columna;
        }

        return null;
    }

    /**
     * Normalitza metadata externa i evita claus sensibles.
     *
     * @param mixed $metadata
     * @return array<string, string>|null
     */
    private function normalitzarMetadata($metadata): ?array
    {
        if ($metadata === null) {
            return null;
        }

        if (!is_array($metadata)) {
            return null;
        }

        $clausPermeses = ['api_id', 'titol', 'url_imatge', 'tipus_api'];
        $resultat = [];

        foreach ($clausPermeses as $clau) {
            if (!array_key_exists($clau, $metadata)) {
                continue;
            }
            $valor = $metadata[$clau];
            if ($valor === null) {
                $resultat[$clau] = '';
                continue;
            }
            if (!is_scalar($valor)) {
                $resultat[$clau] = '';
                continue;
            }
            $resultat[$clau] = mb_substr((string) $valor, 0, 500);
        }

        if (empty($resultat)) {
            return null;
        }

        return $resultat;
    }

    /**
     * Valida shape de metadata per accions CREATE/UPDATE.
     */
    private function validarShapeMetadata(array $habitData): bool
    {
        if (!array_key_exists('metadata', $habitData)) {
            return true;
        }

        $validator = Validator::make(
            ['metadata' => $habitData['metadata']],
            [
                'metadata' => 'nullable|array',
                'metadata.api_id' => 'nullable|string|max:500',
                'metadata.titol' => 'nullable|string|max:500',
                'metadata.url_imatge' => 'nullable|string|max:500',
                'metadata.tipus_api' => 'nullable|string|max:100',
            ]
        );

        return !$validator->fails();
    }

    /**
     * Calcula l'objectiu d'XP per al nivell indicat.
     */
    private function calcularObjectiuNivell(int $nivell): int
    {
        if ($nivell < 1) {
            $nivell = 1;
        }
        $objectiu = self::XP_BASE_NIVELL * pow(self::MULTIPLICADOR_NIVELL, $nivell - 1);
        return (int) round($objectiu);
    }

    /**
     * Normalitza nivells a partir del total d'XP si cal.
     *
     * @return array{nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int}
     */
    private function normalitzarNivell(User $usuari): array
    {
        $nivell = isset($usuari->nivell) ? (int) $usuari->nivell : 1;
        $xpActual = isset($usuari->xp_actual_nivel) ? (int) $usuari->xp_actual_nivel : 0;
        $xpObjectiu = isset($usuari->xp_objetivo_nivel) ? (int) $usuari->xp_objetivo_nivel : 0;

        if ($xpObjectiu <= 0) {
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }

        if ($xpActual < 0 || $xpActual >= $xpObjectiu) {
            $xpTotal = isset($usuari->xp_total) ? (int) $usuari->xp_total : 0;
            $nivell = 1;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
            $restant = $xpTotal;
            while ($restant >= $xpObjectiu) {
                $restant -= $xpObjectiu;
                $nivell++;
                $xpObjectiu = $this->calcularObjectiuNivell($nivell);
            }
            $xpActual = $restant;
        }

        return [
            'nivell' => $nivell,
            'xp_actual_nivel' => $xpActual,
            'xp_objetivo_nivel' => $xpObjectiu,
        ];
    }

    /**
     * Aplica XP i calcula canvi de nivell.
     *
     * @return array{xp_total:int,nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int,level_up:bool,bonus_monedes:int}
     */
    private function aplicarXpINivell(User $usuari, int $xpAfegida): array
    {
        $nivellData = $this->normalitzarNivell($usuari);
        $nivell = $nivellData['nivell'];
        $xpActual = $nivellData['xp_actual_nivel'];
        $xpObjectiu = $nivellData['xp_objetivo_nivel'];

        $xpActual += $xpAfegida;
        $levelUp = false;
        $bonusMonedes = 0;

        while ($xpActual >= $xpObjectiu) {
            $xpActual -= $xpObjectiu;
            $nivell++;
            $levelUp = true;
            $bonusMonedes += self::BONUS_MONEDES_NIVELL;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }

        $xpTotal = isset($usuari->xp_total) ? (int) $usuari->xp_total : 0;
        $xpTotal += $xpAfegida;

        return [
            'xp_total' => $xpTotal,
            'nivell' => $nivell,
            'xp_actual_nivel' => $xpActual,
            'xp_objetivo_nivel' => $xpObjectiu,
            'level_up' => $levelUp,
            'bonus_monedes' => $bonusMonedes,
        ];
    }

    /**
     * Processa l'XP proporcional diari per hàbits incomplets.
     */
    public function processarXpProporcionalDiari(?Carbon $dataActual = null): int
    {
        $avui = $dataActual ? $dataActual->copy() : Carbon::now('Europe/Madrid');
        $avui = $avui->setTimezone('Europe/Madrid')->startOfDay();
        $diaObjectiu = $avui->copy()->subDay();

        $habits = Habit::all();
        $processats = 0;

        foreach ($habits as $habit) {
            $habitId = (int) $habit->id;
            $usuariId = (int) $habit->usuari_id;
            $objectiu = (int) ($habit->objectiu_vegades ?? 0);
            if ($objectiu <= 0 || $usuariId <= 0) {
                continue;
            }

            $jaCompletat = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $diaObjectiu)
                ->where('acabado', true)
                ->exists();
            if ($jaCompletat) {
                continue;
            }

            $jaXpParcial = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $diaObjectiu)
                ->where('xp_guanyada', '>', 0)
                ->exists();
            if ($jaXpParcial) {
                continue;
            }

            $progres = RegistreActivitat::where('habit_id', $habitId)
                ->whereBetween('data', [$diaObjectiu->copy()->startOfDay(), $diaObjectiu->copy()->endOfDay()])
                ->sum('valor');
            $progres = (int) $progres;
            if ($progres <= 0) {
                continue;
            }

            $percentatge = min($progres / $objectiu, 1);
            $xpBase = $this->calcularXPSegonsDificultat($habit->dificultat);
            $xpGuanyada = (int) floor($xpBase * $percentatge);
            if ($xpGuanyada <= 0) {
                continue;
            }

            DB::transaction(function () use ($usuariId, $habit, $diaObjectiu, $xpGuanyada, &$processats) {
                $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
                if ($usuari === null) {
                    return;
                }

                $nivellData = $this->aplicarXpINivell($usuari, $xpGuanyada);
                $monedesTotals = (int) $usuari->monedes + $nivellData['bonus_monedes'];

                $usuari->update([
                    'xp_total' => $nivellData['xp_total'],
                    'nivell' => $nivellData['nivell'],
                    'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                    'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                    'monedes' => $monedesTotals,
                ]);

                $habit->registresActivitat()->create([
                    'data' => $diaObjectiu->copy()->endOfDay(),
                    'valor' => 0,
                    'acabado' => false,
                    'xp_guanyada' => $xpGuanyada,
                ]);

                $payload = [
                    'action' => 'PARTIAL_XP',
                    'user_id' => $usuariId,
                    'success' => true,
                    'xp_update' => [
                        'xp_total' => $nivellData['xp_total'],
                        'nivell' => $nivellData['nivell'],
                        'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                        'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                        'monedes' => $monedesTotals,
                    ],
                ];

                if ($nivellData['level_up'] === true) {
                    $payload['level_up'] = [
                        'nivell' => $nivellData['nivell'],
                        'bonus_monedes' => self::BONUS_MONEDES_NIVELL,
                        'xp_total' => $nivellData['xp_total'],
                        'monedes' => $monedesTotals,
                    ];
                }

                $this->feedbackService->publicarPayload($payload);
                $processats++;
            });
        }

        return $processats;
    }

    /**
     * Exporta hàbits d'una plantilla cap a un usuari.
     * Crea l'hàbit a la taula HABITS i la relació a USUARIS_HABITS per a la persistència.
     * Filtra els hàbits que ja existeixen per a l'usuari (mateix títol).
     *
     * @param  int  $usuariId
     * @param  int  $plantillaId
     * @param  array<int>  $hàbitsSeleccionats
     * @return array<string, mixed>
     */
    public function exportarHabitsDePlantilla(int $usuariId, int $plantillaId, array $hàbitsSeleccionats): array
    {
        try {
            $nousHabits = [];

            $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');

            $titolsExistents = Habit::where('usuari_id', $usuariId)
                ->orWhereIn('id', $habitIdsAssignats)
                ->pluck('titol')
                ->toArray();

            // Normalitzem els títols per a una comparació més robusta (minúscules)
            $titolsNormalitzats = [];
            foreach ($titolsExistents as $titol) {
                $titolsNormalitzats[] = strtolower(trim((string) $titol));
            }

            DB::transaction(function () use ($usuariId, $hàbitsSeleccionats, $titolsNormalitzats, &$nousHabits) {
                $originals = Habit::whereIn('id', $hàbitsSeleccionats)->get();

                foreach ($originals as $original) {
                    /** @var Habit $original */
                    $titolNou = strtolower(trim((string) $original->titol));

                    if (in_array($titolNou, $titolsNormalitzats)) {
                        continue;
                    }

                    $nou = $original->replicate();
                    $nou->usuari_id = $usuariId;
                    $nou->save();

                    UsuariHabit::create([
                        'usuari_id' => $usuariId,
                        'habit_id' => $nou->id,
                        'data_inici' => Carbon::now(),
                        'actiu' => true,
                        'objetiu_vegades_personalitzat' => $nou->objectiu_vegades
                    ]);

                    $nousHabits[] = $nou->toArray();
                }
            });

            return [
                'success' => true,
                'habits' => $nousHabits,
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportant hàbits: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al exportar hàbits: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obté el progrés d'avui per a tots els hàbits de l'usuari.
     *
     * @param  int  $usuariId
     * @return array<int, array<string, mixed>>
     */
    public function obtenirProgresAvui(int $usuariId): array
    {
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $diaIndex = (int) now()->dayOfWeekIso;
        $habits = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->where(function ($q) use ($diaIndex) {
                $q->whereNull('dies_setmana')
                    ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
            })
            ->get(['id', 'objectiu_vegades', 'titol', 'unitat', 'dificultat', 'icona', 'color']);

        $habitIds = $habits->pluck('id')->toArray();
        $avui = Carbon::today();

        if (empty($habitIds)) {
            return [];
        }

        $progres = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COALESCE(SUM(valor), 0) as progress'))
            ->whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->groupBy('habit_id')
            ->get()
            ->keyBy('habit_id');

        $completats = RegistreActivitat::whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->where('acabado', true)
            ->pluck('habit_id')
            ->toArray();

        $resultat = [];
        foreach ($habits as $habit) {
            $progress = 0;
            if (isset($progres[$habit->id])) {
                $progress = (int) $progres[$habit->id]->progress;
            }
            $resultat[] = [
                'habit_id' => $habit->id,
                'progress' => $progress,
                'completed_today' => in_array($habit->id, $completats),
                'objectiu_vegades' => (int) $habit->objectiu_vegades,
                'titol' => $habit->titol,
                'unitat' => $habit->unitat,
                'icona' => $habit->icona,
                'color' => $habit->color,
            ];
        }

        return $resultat;
    }

    /**
     * Obté els logs diaris agregats per hàbit.
     *
     * @param  int  $usuariId
     * @return array<int, array<string, mixed>>
     */
    public function obtenirLogsHistorics(int $usuariId): array
    {
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $habitIds = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->pluck('id')
            ->toArray();

        if (empty($habitIds)) {
            return [];
        }

        $files = DB::table('registre_activitat as ra')
            ->join('habits as h', 'ra.habit_id', '=', 'h.id')
            ->whereIn('h.id', $habitIds)
            ->selectRaw('DATE(ra.data) as dia')
            ->selectRaw('h.id as habit_id, h.titol, h.unitat, h.objectiu_vegades, h.dificultat, h.icona, h.color')
            ->selectRaw('COALESCE(SUM(ra.valor), 0) as progreso_diario')
            ->selectRaw('MAX(CASE WHEN ra.acabado = true THEN 1 ELSE 0 END) as completado')
            ->selectRaw('COALESCE(SUM(CASE WHEN ra.acabado = true THEN ra.xp_guanyada ELSE 0 END), 0) as xp_ganada')
            ->groupBy('dia', 'h.id', 'h.titol', 'h.unitat', 'h.objectiu_vegades', 'h.dificultat', 'h.icona', 'h.color')
            ->orderBy('dia', 'desc')
            ->get();

        $resultat = [];
        foreach ($files as $fila) {
            $monedes = 2;
            $dificultat = strtolower((string) $fila->dificultat);
            if ((int) $fila->completado === 1) {
                if ($dificultat === 'facil') {
                    $monedes = 2;
                } elseif ($dificultat === 'media') {
                    $monedes = 5;
                } elseif ($dificultat === 'dificil') {
                    $monedes = 10;
                }
            }
            $resultat[] = [
                'dia' => $fila->dia,
                'habit_id' => (int) $fila->habit_id,
                'titol' => $fila->titol,
                'unitat' => $fila->unitat,
                'icona' => $fila->icona,
                'color' => $fila->color,
                'objectiu_vegades' => (int) $fila->objectiu_vegades,
                'progreso_diario' => (int) $fila->progreso_diario,
                'completado' => ((int) $fila->completado === 1),
                'xp_ganada' => (int) $fila->xp_ganada,
                'monedes_ganadas' => $monedes,
            ];
        }

        return $resultat;
    }
}
