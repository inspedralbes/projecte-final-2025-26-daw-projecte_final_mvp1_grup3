<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use App\Models\User;
use App\Models\UsuariHabit;
use App\Services\MissionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de processament d'hàbits.
 * Gestiona el CRUD d'hàbits i el càlcul d'XP amb ratxes.
 * Totes les operacions a PostgreSQL s'executen dins d'una transacció.
 */
class HabitService
{
    /**
     * Map de dificultat -> XP (segons regles de gamificació).
     */
    private const XP_PER_DIFICULTAT = [
        'facil' => 100,
        'media' => 250,
        'dificil' => 400,
    ];
    /**
     * Map de dificultat -> monedes.
     */
    private const MONEDES_PER_DIFICULTAT = [
        'facil' => 2,
        'media' => 5,
        'dificil' => 10,
    ];

    /**
     * XP per defecte si la dificultat no es reconeix.
     */
    private const XP_DEFECTE = 100;
    /**
     * Monedes per defecte si la dificultat no es reconeix.
     */
    private const MONEDES_DEFECTE = 2;

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
        // A. Normalitzar entrada
        // A1. Validar si s'ha rebut acció
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

        // A3. Validar si s'ha rebut id d'hàbit
        if (isset($dades['habit_id'])) {
            $habitId = (int) $dades['habit_id'];
        } else {
            $habitId = 0;
        }

        // A4. Validar si s'han rebut dades d'hàbit
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

        // B. Executar l'acció
        // B1. Acció CREATE
        if ($accio === 'CREATE') {
            $habitModel = $this->crearHabit($usuariId, $habitData);
            $success = $habitModel !== null;
        // B2. Acció UPDATE
        } elseif ($accio === 'UPDATE') {
            $habitModel = $this->actualitzarHabit($usuariId, $habitId, $habitData);
            $success = $habitModel !== null;
        // B3. Acció DELETE
        } elseif ($accio === 'DELETE') {
            $habitModel = $this->eliminarHabit($usuariId, $habitId);
            $success = $habitModel !== null;
        // B4. Acció PROGRESS (increment/decrement)
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
        // B5. Acció COMPLETE (confirmar finalització)
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
                // B5.1. Comprovar missió diària (després de completar)
                $resultatMissio = $this->missionService->comprovarMissioCompletada(
                    $usuariId,
                    $habitId,
                    isset($dades['data']) ? Carbon::parse($dades['data']) : Carbon::now()
                );
                if ($resultatMissio !== null && $resultatMissio['completada'] === true) {
                    $missionCompleted = ['success' => true];
                    if (isset($resultatMissio['xp_update'])) {
                        $xpUpdate = $resultatMissio['xp_update'];
                    }
                }
            } else {
                $success = false;
                $message = $resultatComplete['message'] ?? 'No s\'ha pogut completar l\'hàbit.';
            }
        // B6. Acció TOGGLE (compatibilitat antiga)
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
        // B5. Acció no reconeguda
        } else {
            throw new \InvalidArgumentException('Acció d\'hàbits no reconeguda.');
        }

        // C. Construir payload de feedback
        // C1. Preparar l'hàbit per a resposta
        if ($habitModel !== null) {
            $habitPayload = $habitModel->toArray();
        } else {
            $habitPayload = null;
        }

        $payload = [
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $success,
            'habit' => $habitPayload,
        ];

        // C2. Afegir XP si hi ha actualització
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

        // C3. Afegir mission_completed si s'ha completat la missió
        if ($missionCompleted !== null) {
            $payload['mission_completed'] = $missionCompleted;
        }
        if (isset($resultatComplete) && is_array($resultatComplete) && isset($resultatComplete['level_up'])) {
            $payload['level_up'] = $resultatComplete['level_up'];
        }

        // D. Publicar feedback a Redis
        $this->feedbackService->publicarPayload($payload);
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
        // A. Validació i recuperació de l'hàbit
        // A1. Comprovar si hi ha habit_id
        if (isset($dades['habit_id'])) {
            $habitId = (int) $dades['habit_id'];
        } else {
            $habitId = 0;
        }

        // A2. Validar que l'id sigui positiu
        if ($habitId <= 0) {
            throw new \InvalidArgumentException('El camp habit_id és obligatori i ha de ser un enter positiu.');
        }

        $habit = Habit::find($habitId);

        // A3. Validar que l'hàbit existeixi
        if (! $habit) {
            throw new \InvalidArgumentException("No s'ha trobat l'hàbit amb id {$habitId}.");
        }

        // A4. Usuari que completa (del payload o propietari de l'hàbit)
        if (isset($dades['user_id']) && $dades['user_id'] > 0) {
            $usuariId = (int) $dades['user_id'];
        } else {
            $usuariId = (int) ($habit->usuari_id);
        }

        // B. Determinar la data/hora de l'activitat (avui ara per defecte)
        // B1. Si arriba data, parsejar (conservar hora); si no, usar ara
        if (isset($dades['data']) && $dades['data'] !== null) {
            $timestampComplet = Carbon::parse($dades['data']);
        } else {
            $timestampComplet = Carbon::now();
        }

        // B2. Data només per a la lògica de ratxa (startOfDay)
        $dataActivitat = $timestampComplet->copy()->startOfDay();

        // B3. Verificar accés de l'usuari a l'hàbit
        if (! $this->usuariTeAccesHabit($habitId, $usuariId)) {
            return [
                'success' => false,
                'message' => 'No autoritzat per completar aquest hàbit.',
            ];
        }

        // C. Obtenir progrés d'avui i validar objectiu
        $progresAvui = $this->obtenirProgresDiari($habitId, $dataActivitat);
        if ($progresAvui < (int) $habit->objectiu_vegades) {
            return [
                'success' => false,
                'message' => 'Has de completar l\'objectiu abans de finalitzar l\'hàbit.',
            ];
        }

        // C1. Comprovar si ja s'ha completat avui
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

        // D. Calcular XP i monedes segons la dificultat de l'hàbit
        $xpGuanyada = $this->calcularXPSegonsDificultat($habit->dificultat);
        $monedesGuanyades = $this->calcularMonedesSegonsDificultat($habit->dificultat);

        $levelUpData = null;

        // D. Executar tot dins d'una transacció
        DB::transaction(function () use (
            $habit,
            $usuariId,
            $dataActivitat,
            $timestampComplet,
            $xpGuanyada,
            $monedesGuanyades,
            &$levelUpData
        ) {
            // D1. Actualitzar XP/Nivell/Monedes de l'usuari
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

            // D2. Obtenir o crear la ratxa de l'usuari i actualitzar-la
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

        // E. Recuperar valors finals per retornar el resultat
        $usuari = User::find($usuariId);

        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();

        // E1. Si no hi ha ratxa, inicialitzar valors
        if ($ratxa === null) {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        } else {
            // E2. Validar ratxa_actual
            if (isset($ratxa->ratxa_actual)) {
                $ratxaActual = (int) $ratxa->ratxa_actual;
            } else {
                $ratxaActual = 0;
            }

            // E3. Validar ratxa_maxima
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
        // A. Si la dificultat no està informada, usar XP per defecte
        if ($dificultat === null || $dificultat === '') {
            return self::XP_DEFECTE;
        }

        $clau = strtolower(trim($dificultat));
        $mapXp = self::XP_PER_DIFICULTAT;

        // B. Si la clau existeix al mapa, retornar XP corresponent
        if (array_key_exists($clau, $mapXp)) {
            return $mapXp[$clau];
        }

        return self::XP_DEFECTE;
    }

    /**
     * Calcula les monedes segons la dificultat de l'hàbit.
     *
     * @param  string|null  $dificultat
     */
    private function calcularMonedesSegonsDificultat(?string $dificultat): int
    {
        if ($dificultat === null || $dificultat === '') {
            return self::MONEDES_DEFECTE;
        }

        $clau = strtolower(trim($dificultat));
        $mapMonedes = self::MONEDES_PER_DIFICULTAT;

        if (array_key_exists($clau, $mapMonedes)) {
            return $mapMonedes[$clau];
        }

        return self::MONEDES_DEFECTE;
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
        if (! $habit || ! $this->usuariTeAccesHabit($habitId, $usuariId)) {
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
        $avui = $dataActivitat->copy()->startOfDay();

        // A. Si hi ha data prèvia, parsejar-la
        if ($ratxa->ultima_data !== null) {
            $ultimaData = Carbon::parse($ratxa->ultima_data)->startOfDay();
        } else {
            $ultimaData = null;
        }

        $ratxaActual = (int) $ratxa->ratxa_actual;
        $ratxaMaxima = (int) $ratxa->ratxa_maxima;

        // B. Si és el mateix dia, no modifiquem la ratxa (evitar duplicats)
        if ($ultimaData && $ultimaData->isSameDay($avui)) {
            return;
        }

        // C. Si és el dia següent al registrat: dia consecutiu, incrementar
        if ($ultimaData && $ultimaData->diffInDays($avui) === 1) {
            $ratxaActual++;
        } else {
            // Si hi ha un gap o és la primera vegada: nova ratxa, començar des d'1
            $ratxaActual = 1;
        }

        // D. Actualitzar ratxa_maxima si la ratxa actual és major
        if ($ratxaActual > $ratxaMaxima) {
            $ratxaMaxima = $ratxaActual;
        }

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

            // Si l'última activitat és anterior a ahir, la ratxa es trenca
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
        // A. Normalitzar dades d'entrada
        $dades = $this->filtrarDadesHabit($habitData);

        // B. Assignar usuari per defecte
        $dades['usuari_id'] = $usuariId;

        // C. Crear model
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
        // A. Recuperar hàbit
        $habit = Habit::find($habitId);

        // A1. Validar existència i propietat
        if (! $habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        // B. Actualitzar camps permesos
        $dades = $this->filtrarDadesHabit($habitData);

        // B1. Si hi ha dades, actualitzar
        if (! empty($dades)) {
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
        // A. Recuperar hàbit
        $habit = Habit::find($habitId);

        // A1. Validar existència i propietat
        if (! $habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        // B. Guardar dades abans d'eliminar
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

        // A. Copiar plantilla_id si existeix
        if (isset($habitData['plantilla_id'])) {
            $dades['plantilla_id'] = $habitData['plantilla_id'];
        }
        
        // B. Copiar titol si existeix
        if (isset($habitData['titol'])) {
            $dades['titol'] = $habitData['titol'];
        }
        
        // C. Copiar dificultat si existeix
        if (isset($habitData['dificultat'])) {
            $dades['dificultat'] = $habitData['dificultat'];
        }
        
        // D. Copiar frequencia_tipus si existeix
        if (isset($habitData['frequencia_tipus'])) {
            $dades['frequencia_tipus'] = $habitData['frequencia_tipus'];
        }
        
        // E. Copiar dies_setmana si existeix
        if (isset($habitData['dies_setmana'])) {
            $dades['dies_setmana'] = $this->normalitzarDiesSetmana($habitData['dies_setmana']);
        }
        
        // F. Copiar objectiu_vegades si existeix
        if (isset($habitData['objectiu_vegades'])) {
            $dades['objectiu_vegades'] = $habitData['objectiu_vegades'];
        }

        // F2. Copiar unitat si existeix
        if (isset($habitData['unitat'])) {
            $dades['unitat'] = $habitData['unitat'];
        }

        // G. Copiar categoria_id si existeix
        if (isset($habitData['categoria_id'])) {
            $dades['categoria_id'] = $habitData['categoria_id'];
        }

        // H. Copiar icona si existeix
        if (isset($habitData['icona'])) {
            $dades['icona'] = $habitData['icona'];
        }

        // I. Copiar color si existeix
        if (isset($habitData['color'])) {
            $dades['color'] = $habitData['color'];
        }

        return $dades;
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
}
