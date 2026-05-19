<?php

declare(strict_types=1);

namespace App\Domains\Shop\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Ratxa;
use App\Models\UsuariItem;
use App\Services\RedisFeedbackService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Consumeix un item consumible (recuperador de ratxa).
 */
class UseConsumibleAction
{
    private RedisFeedbackService $feedback;

    public function __construct(RedisFeedbackService $feedback)
    {
        $this->feedback = $feedback;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function executar(int $userId, UsuariItem $usuariItem): array
    {
        if ($usuariItem->usuari_id !== $userId) {
            return ['success' => false, 'error' => 'Objecte no trobat', 'status' => 404];
        }

        if ($usuariItem->item === null || $usuariItem->item->tipus !== 'consumible') {
            return ['success' => false, 'error' => 'Aquest objecte no es pot usar', 'status' => 400];
        }

        if ($usuariItem->consumit_at !== null) {
            return ['success' => false, 'error' => 'Aquest objecte ja s\'ha consumit', 'status' => 409];
        }

        $metadata = $usuariItem->item->metadata ?? [];
        $efecte = isset($metadata['effect']) ? (string) $metadata['effect'] : '';

        if ($efecte !== 'restore_streak') {
            return ['success' => false, 'error' => 'Efecte no implementat', 'status' => 400];
        }

        $resultat = DB::transaction(function () use ($userId, $usuariItem) {
            $ratxa = Ratxa::where('usuari_id', $userId)->lockForUpdate()->first();
            if ($ratxa === null) {
                return ['error' => 'No tens cap ratxa registrada', 'status' => 400];
            }

            $ratxaActual = (int) $ratxa->ratxa_actual;
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;

            if ($ratxaMaxima <= 0) {
                return ['error' => 'No tens cap ratxa màxima per recuperar', 'status' => 400];
            }
            if ($ratxaActual > 0) {
                return ['error' => 'Només pots usar el Recuperador si has perdut la ratxa', 'status' => 400];
            }

            $ratxa->ratxa_actual = $ratxaMaxima;
            $ratxa->ultima_data = Carbon::today()->toDateString();
            $ratxa->save();

            $usuariItem->consumit_at = Carbon::now();
            $usuariItem->save();

            return [
                'ratxa_actual' => $ratxaMaxima,
                'ratxa_maxima' => $ratxaMaxima,
            ];
        });

        if (isset($resultat['error'])) {
            return [
                'success' => false,
                'error' => $resultat['error'],
                'status' => $resultat['status'] ?? 400,
            ];
        }

        $usuariItem->refresh()->load('item');

        $this->feedback->publicarPayload([
            'type' => 'SHOP',
            'action' => 'CONSUME',
            'user_id' => $userId,
            'success' => true,
            'shop_event' => [
                'kind' => 'consumed',
                'usuari_item_id' => $usuariItem->id,
                'effect' => 'restore_streak',
                'item' => $usuariItem,
            ],
            'xp_update' => [
                'ratxa_actual' => $resultat['ratxa_actual'],
                'ratxa_maxima' => $resultat['ratxa_maxima'],
            ],
        ]);

        return [
            'success' => true,
            'usuari_item' => $usuariItem,
            'ratxa_actual' => $resultat['ratxa_actual'],
            'ratxa_maxima' => $resultat['ratxa_maxima'],
        ];
    }
}
