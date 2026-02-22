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

        // A2. Validar si s'ha rebut usuari
        if (isset($dades['user_id'])) {
            $usuariId = (int) $dades['user_id'];
        } else {
            $usuariId = 1;
        }

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
        // B4. Acció TOGGLE (completar hàbit)
        } elseif ($accio === 'TOGGLE') {
            $xpUpdate = $this->processarHabitCompletat([
                'habit_id' => $habitId,
                'data' => isset($dades['data']) ? $dades['data'] : null,
            ]);
            $habitModel = Habit::find($habitId);
            $success = true;
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
    public function processarHabitCompletat(array $dades): array
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

        $usuariId = (int) ($habit->usuari_id);

        // B. Determinar la data de l'activitat (avui per defecte)
        // B1. Si arriba data, parsejar; si no, usar avui
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

        return [
            'xp_total' => (int) $usuari->xp_total,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
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
            $dades['dies_setmana'] = $habitData['dies_setmana'];
        }
        
        // F. Copiar objectiu_vegades si existeix
        if (isset($habitData['objectiu_vegades'])) {
            $dades['objectiu_vegades'] = $habitData['objectiu_vegades'];
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
}
