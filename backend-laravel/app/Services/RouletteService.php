<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Ratxa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de ruleta diària.
 * Gestiona la validació de tirada i l'aplicació de recompenses.
 */
class RouletteService
{
    /**
     * Servei per publicar feedback a Redis.
     */
    private RedisFeedbackService $feedbackService;

    /**
     * Llista de premis disponibles.
     *
     * @return array<int, array<string, mixed>>
     */
    private function obtenirPremis(): array
    {
        return [
            ['key' => 'xp_50', 'type' => 'xp', 'amount' => 50, 'label' => '50 XP'],
            ['key' => 'xp_150', 'type' => 'xp', 'amount' => 150, 'label' => '150 XP'],
            ['key' => 'xp_500', 'type' => 'xp', 'amount' => 500, 'label' => '500 XP'],
            ['key' => 'coins_1', 'type' => 'coins', 'amount' => 1, 'label' => '1 moneda'],
            ['key' => 'coins_5', 'type' => 'coins', 'amount' => 5, 'label' => '5 monedes'],
            ['key' => 'coins_10', 'type' => 'coins', 'amount' => 10, 'label' => '10 monedes'],
            ['key' => 'shop_item', 'type' => 'shop_item', 'amount' => null, 'label' => 'Objecte de botiga'],
        ];
    }

    public function __construct(RedisFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Processa una tirada de ruleta.
     *
     * @param  array<string, mixed>  $dades
     */
    public function processarTirada(array $dades): void
    {
        // A. Validar usuari i dades d'entrada
        if (isset($dades['user_id'])) {
            $usuariId = (int) $dades['user_id'];
        } else {
            $usuariId = 0;
        }
        if ($usuariId <= 0) {
            return;
        }

        // B. Recuperar usuari de base de dades
        $usuari = User::find($usuariId);
        if ($usuari === null) {
            $this->feedbackService->publicarPayload([
                'type' => 'ROULETTE',
                'action' => 'SPIN',
                'user_id' => $usuariId,
                'success' => false,
                'roulette_result' => [
                    'error' => 'Usuari no trobat',
                    'can_spin_roulette' => false,
                ],
            ]);
            return;
        }

        // C. Comprovar si ja ha tirat avui
        $avui = Carbon::today();
        $ultimaTirada = $usuari->ruleta_ultima_tirada;
        if ($ultimaTirada !== null) {
            $dataTirada = Carbon::parse($ultimaTirada)->startOfDay();
            if ($dataTirada->isSameDay($avui)) {
                $this->feedbackService->publicarPayload([
                    'type' => 'ROULETTE',
                    'action' => 'SPIN',
                    'user_id' => $usuariId,
                    'success' => false,
                    'roulette_result' => [
                        'error' => 'Ja has tirat la ruleta avui',
                        'can_spin_roulette' => false,
                        'ruleta_ultima_tirada' => $ultimaTirada,
                    ],
                ]);
                return;
            }
        }

        // D. Validar premi rebut del frontend
        $premi = $this->obtenirPremiDelPayload($dades);
        if ($premi === null) {
            $this->feedbackService->publicarPayload([
                'type' => 'ROULETTE',
                'action' => 'SPIN',
                'user_id' => $usuariId,
                'success' => false,
                'roulette_result' => [
                    'error' => 'Premi invàlid',
                    'can_spin_roulette' => true,
                ],
            ]);
            return;
        }

        // E. Preparar increments de recompensa
        $incrementXp = 0;
        $incrementMonedes = 0;
        if ($premi['type'] === 'xp') {
            $incrementXp = (int) $premi['amount'];
        } elseif ($premi['type'] === 'coins') {
            $incrementMonedes = (int) $premi['amount'];
        }

        // F. Aplicar la recompensa i guardar última tirada
        DB::transaction(function () use ($usuariId, $incrementXp, $incrementMonedes, $avui) {
            if ($incrementXp > 0) {
                User::where('id', $usuariId)->increment('xp_total', $incrementXp);
            }
            if ($incrementMonedes > 0) {
                User::where('id', $usuariId)->increment('monedes', $incrementMonedes);
            }
            DB::table('usuaris')->where('id', $usuariId)->update([
                'ruleta_ultima_tirada' => $avui->toDateString(),
            ]);
        });

        // G. Preparar feedback amb estat actualitzat
        $usuariActualitzat = User::find($usuariId);
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        if ($ratxa !== null) {
            $ratxaActual = (int) $ratxa->ratxa_actual;
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
        } else {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        }
        if ($usuariActualitzat !== null) {
            $xpTotal = (int) $usuariActualitzat->xp_total;
            $monedesTotal = (int) $usuariActualitzat->monedes;
        } else {
            $xpTotal = 0;
            $monedesTotal = 0;
        }

        $this->feedbackService->publicarPayload([
            'type' => 'ROULETTE',
            'action' => 'SPIN',
            'user_id' => $usuariId,
            'success' => true,
            'xp_update' => [
                'xp_total' => $xpTotal,
                'ratxa_actual' => $ratxaActual,
                'ratxa_maxima' => $ratxaMaxima,
                'monedes' => $monedesTotal,
            ],
            'roulette_result' => [
                'key' => $premi['key'],
                'type' => $premi['type'],
                'amount' => $premi['amount'],
                'label' => $premi['label'],
                'can_spin_roulette' => false,
                'ruleta_ultima_tirada' => $avui->toDateString(),
            ],
        ]);
    }

    /**
     * Valida el premi rebut i retorna el premi definit.
     *
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>|null
     */
    private function obtenirPremiDelPayload(array $dades): ?array
    {
        $premiPayload = null;
        if (isset($dades['data']) && is_array($dades['data'])) {
            if (isset($dades['data']['prize']) && is_array($dades['data']['prize'])) {
                $premiPayload = $dades['data']['prize'];
            }
        }
        if (! is_array($premiPayload) || ! isset($premiPayload['key'])) {
            return null;
        }

        $premis = $this->obtenirPremis();
        foreach ($premis as $premi) {
            if ($premi['key'] === $premiPayload['key']) {
                if (! isset($premiPayload['type']) || $premiPayload['type'] !== $premi['type']) {
                    return null;
                }
                if (! array_key_exists('amount', $premiPayload)) {
                    return null;
                }
                if ((string) $premiPayload['amount'] !== (string) $premi['amount']) {
                    return null;
                }
                if (! isset($premiPayload['label']) || $premiPayload['label'] !== $premi['label']) {
                    return null;
                }
                return $premi;
            }
        }
        return null;
    }
}
