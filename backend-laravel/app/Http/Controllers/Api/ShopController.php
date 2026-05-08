<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\BotigaItem;
use App\Models\Ratxa;
use App\Models\User;
use App\Models\UsuariItem;
use App\Services\RedisFeedbackService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//================================ CONTROLLER ====================

/**
 * ShopController.
 * Gestiona les operacions de la tenda Loopy:
 * llistar catàleg + inventari, comprar items, equipar skins i
 * consumir el "Recuperador de Ratxa". Tota l'economia (monedes)
 * passa per transaccions amb lockForUpdate per evitar carreres.
 */
class ShopController extends Controller
{
    private RedisFeedbackService $feedback;

    public function __construct(RedisFeedbackService $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Catàleg + inventari del usuari + saldo de monedes.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $items = BotigaItem::where('actiu', true)->orderBy('preu')->get();

        $inventari = UsuariItem::with('item')
            ->where('usuari_id', $userId)
            ->orderByDesc('comprat_at')
            ->get();

        $monedes = (int) (User::where('id', $userId)->value('monedes') ?? 0);

        return response()->json([
            'items' => $items,
            'inventari' => $inventari,
            'monedes' => $monedes,
        ]);
    }

    /**
     * Compra un item del catàleg.
     * Valida el saldo, descompta monedes i crea la fila a USUARIS_ITEMS.
     * Skins entren desequipats; els consumibles, sense consumir.
     */
    public function comprar(Request $request, int $itemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $item = BotigaItem::find($itemId);
        if ($item === null || ! $item->actiu) {
            return response()->json(['error' => 'Item no disponible'], 404);
        }

        $resultat = DB::transaction(function () use ($userId, $item) {
            $usuari = User::where('id', $userId)->lockForUpdate()->first();
            if ($usuari === null) {
                return ['error' => 'Usuari no trobat', 'status' => 404];
            }

            $preu = (int) $item->preu;
            $saldo = (int) $usuari->monedes;
            if ($saldo < $preu) {
                return ['error' => 'Saldo insuficient', 'status' => 402];
            }

            // Skins permanents: si ja en té un, no permetem comprar-lo dues vegades.
            // Consumibles: es poden acumular tantes vegades com el jugador vulgui.
            if ($item->tipus === 'skin') {
                $jaEnTe = UsuariItem::where('usuari_id', $userId)
                    ->where('item_id', $item->id)
                    ->exists();
                if ($jaEnTe) {
                    return ['error' => 'Ja tens aquest objecte', 'status' => 409];
                }
            }

            $usuari->decrement('monedes', $preu);

            $usuariItem = UsuariItem::create([
                'usuari_id' => $userId,
                'item_id' => $item->id,
                'comprat_at' => Carbon::now(),
                'equipat' => false,
                'consumit_at' => null,
            ]);

            $usuari->refresh();

            return [
                'usuari_item' => $usuariItem,
                'monedes' => (int) $usuari->monedes,
            ];
        });

        if (isset($resultat['error'])) {
            return response()->json(['error' => $resultat['error']], $resultat['status']);
        }

        $usuariItem = $resultat['usuari_item']->load('item');

        $this->feedback->publicarPayload([
            'type' => 'SHOP',
            'action' => 'PURCHASE',
            'user_id' => $userId,
            'success' => true,
            'shop_event' => [
                'kind' => 'purchased',
                'usuari_item_id' => $usuariItem->id,
                'item' => $usuariItem,
            ],
            'xp_update' => [
                'monedes' => $resultat['monedes'],
            ],
        ]);

        return response()->json([
            'success' => true,
            'usuari_item' => $usuariItem,
            'monedes' => $resultat['monedes'],
        ], 201);
    }

    /**
     * Equipa o desequipa un skin (toggle).
     * Si l'objecte ja està equipat, el desequipa. Si no, el desequipa tots
     * els skins del mateix slot i l'equipa.
     */
    public function equipar(Request $request, int $usuariItemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuariItem = UsuariItem::with('item')->find($usuariItemId);
        if ($usuariItem === null || $usuariItem->usuari_id !== $userId) {
            return response()->json(['error' => 'Objecte no trobat'], 404);
        }

        if ($usuariItem->item === null || $usuariItem->item->tipus !== 'skin') {
            return response()->json(['error' => 'Aquest objecte no es pot equipar'], 400);
        }

        $metadata = $usuariItem->item->metadata ?? [];
        $slot = isset($metadata['slot']) ? (string) $metadata['slot'] : 'cap';
        $estavaEquipat = (bool) $usuariItem->equipat;

        DB::transaction(function () use ($userId, $usuariItem, $slot, $estavaEquipat) {
            if ($estavaEquipat) {
                $usuariItem->equipat = false;
                $usuariItem->save();
                return;
            }

            // Desequipar tots els skins del usuari del mateix slot.
            UsuariItem::where('usuari_id', $userId)
                ->where('equipat', true)
                ->whereHas('item', function ($query) use ($slot) {
                    $query->where('tipus', 'skin')
                        ->where('metadata->slot', $slot);
                })
                ->update(['equipat' => false]);

            $usuariItem->equipat = true;
            $usuariItem->save();
        });

        $usuariItem->refresh()->load('item');

        $this->feedback->publicarPayload([
            'type' => 'SHOP',
            'action' => $estavaEquipat ? 'UNEQUIP' : 'EQUIP',
            'user_id' => $userId,
            'success' => true,
            'shop_event' => [
                'kind' => $estavaEquipat ? 'unequipped' : 'equipped',
                'usuari_item_id' => $usuariItem->id,
                'slot' => $slot,
                'skin_key' => $metadata['skin_key'] ?? null,
                'item' => $usuariItem,
            ],
        ]);

        return response()->json([
            'success' => true,
            'usuari_item' => $usuariItem,
        ]);
    }

    /**
     * Consumeix el "Recuperador de Ratxa".
     * Restaura ratxa_actual al valor de ratxa_maxima i actualitza ultima_data.
     */
    public function usarConsumible(Request $request, int $usuariItemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuariItem = UsuariItem::with('item')->find($usuariItemId);
        if ($usuariItem === null || $usuariItem->usuari_id !== $userId) {
            return response()->json(['error' => 'Objecte no trobat'], 404);
        }

        if ($usuariItem->item === null || $usuariItem->item->tipus !== 'consumible') {
            return response()->json(['error' => 'Aquest objecte no es pot usar'], 400);
        }

        if ($usuariItem->consumit_at !== null) {
            return response()->json(['error' => 'Aquest objecte ja s\'ha consumit'], 409);
        }

        $metadata = $usuariItem->item->metadata ?? [];
        $efecte = isset($metadata['effect']) ? (string) $metadata['effect'] : '';

        if ($efecte !== 'restore_streak') {
            return response()->json(['error' => 'Efecte no implementat'], 400);
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
            return response()->json(['error' => $resultat['error']], $resultat['status']);
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

        return response()->json([
            'success' => true,
            'usuari_item' => $usuariItem,
            'ratxa_actual' => $resultat['ratxa_actual'],
            'ratxa_maxima' => $resultat['ratxa_maxima'],
        ]);
    }
}
