<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de processament d'hàbits completats.
 * Gestiona el càlcul d'XP, la persistència de ratxes i el registre d'activitat.
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
     * XP per defecte si la dificultat no es reconeix.
     */
    private const XP_DEFECTE = 100;

    /**
     * Servei de feedback per Redis.
     */
    private RedisFeedbackService $feedbackService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei de feedback.
     */
    public function __construct(RedisFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Processa un hàbit completat: calcula XP, actualitza ratxes i registra l'activitat.
     * Es rep un array amb habit_id (obligatori) i opcionalment data, usuari_id.
     *
     * @param  array<string, mixed>  $dades  { habit_id: int, data?: string, usuari_id?: int }
     */
    public function processarHabitCompletat(array $dades): void
    {
        // A. Validació i recuperació de l'hàbit
        if (isset($dades['habit_id'])) {
            $habitId = (int) $dades['habit_id'];
        } else {
            $habitId = 0;
        }

        if ($habitId <= 0) {
            throw new \InvalidArgumentException('El camp habit_id és obligatori i ha de ser un enter positiu.');
        }

        $habit = Habit::find($habitId);

        if (! $habit) {
            throw new \InvalidArgumentException("No s'ha trobat l'hàbit amb id {$habitId}.");
        }

        $usuariId = (int) ($habit->usuari_id);

        // B. Determinar la data de l'activitat (avui per defecte)
        if (isset($dades['data'])) {
            $dataActivitat = Carbon::parse($dades['data'])->startOfDay();
        } else {
            $dataActivitat = Carbon::today();
        }

        // C. Calcular XP segons la dificultat de l'hàbit
        $xpGuanyada = $this->calcularXPSegonsDificultat($habit->dificultat);

        // D. Executar tot dins d'una transacció
        DB::transaction(function () use ($habit, $usuariId, $dataActivitat, $xpGuanyada) {
            // D1. Actualitzar xp_total de l'usuari a la taula USUARIS
            User::where('id', $usuariId)->increment('xp_total', $xpGuanyada);

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

            // D3. Inserir fila a REGISTRE_ACTIVITAT via la relació de l'hàbit
            $habit->registresActivitat()->create([
                'data' => $dataActivitat,
                'acabado' => true,
                'xp_guanyada' => $xpGuanyada,
            ]);
        });

        // E. Recuperar valors finals i publicar feedback a Redis
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

        $this->feedbackService->publicarFeedback(
            $usuariId,
            (int) $usuari->xp_total,
            $ratxaActual,
            $ratxaMaxima
        );
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
            return self::XP_DEFECTE;
        }

        $clau = strtolower(trim($dificultat));
        $mapXp = self::XP_PER_DIFICULTAT;

        if (array_key_exists($clau, $mapXp)) {
            return $mapXp[$clau];
        }

        return self::XP_DEFECTE;
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

        if ($ratxa->ultima_data !== null) {
            $ultimaData = Carbon::parse($ratxa->ultima_data)->startOfDay();
        } else {
            $ultimaData = null;
        }

        $ratxaActual = (int) $ratxa->ratxa_actual;
        $ratxaMaxima = (int) $ratxa->ratxa_maxima;

        // Si és el mateix dia, no modifiquem la ratxa (evitar duplicats)
        if ($ultimaData && $ultimaData->isSameDay($avui)) {
            return;
        }

        // Si és el dia següent al registrat: dia consecutiu, incrementar
        if ($ultimaData && $ultimaData->diffInDays($avui) === 1) {
            $ratxaActual++;
        } else {
            // Si hi ha un gap o és la primera vegada: nova ratxa, començar des d'1
            $ratxaActual = 1;
        }

        // Actualitzar ratxa_maxima si la ratxa actual és major
        if ($ratxaActual > $ratxaMaxima) {
            $ratxaMaxima = $ratxaActual;
        }

        $ratxa->update([
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'ultima_data' => $avui,
        ]);
    }
}
