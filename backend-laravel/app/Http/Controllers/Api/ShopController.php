<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Shop\Actions\EquipSkinAction;
use App\Domains\Shop\Actions\PurchaseItemAction;
use App\Domains\Shop\Actions\UseConsumibleAction;
use App\Domains\Shop\Queries\GetShopCatalogQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\ShopIndexResource;
use App\Http\Resources\Shop\UsuariItemResource;
use App\Models\BotigaItem;
use App\Models\UsuariItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * ShopController (thin).
 * Operacions: READ | CREATE | UPDATE
 */
class ShopController extends Controller
{
    private GetShopCatalogQuery $catalogQuery;

    private PurchaseItemAction $purchaseAction;

    private EquipSkinAction $equipAction;

    private UseConsumibleAction $useConsumibleAction;

    public function __construct(
        GetShopCatalogQuery $catalogQuery,
        PurchaseItemAction $purchaseAction,
        EquipSkinAction $equipAction,
        UseConsumibleAction $useConsumibleAction
    ) {
        $this->catalogQuery = $catalogQuery;
        $this->purchaseAction = $purchaseAction;
        $this->equipAction = $equipAction;
        $this->useConsumibleAction = $useConsumibleAction;
    }

    /**
     * READ: Catàleg + inventari + monedes.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $dades = $this->catalogQuery->executar($userId);

        return response()->json(
            (new ShopIndexResource($dades))->resolve($request)
        );
    }

    /**
     * CREATE: Compra d'un item.
     */
    public function comprar(Request $request, int $itemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $item = BotigaItem::find($itemId);
        if ($item === null || !$item->actiu) {
            return response()->json(['error' => 'Item no disponible'], 404);
        }

        $resultat = $this->purchaseAction->executar($userId, $item);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json([
            'success' => true,
            'usuari_item' => (new UsuariItemResource($resultat['usuari_item']))->resolve($request),
            'monedes' => $resultat['monedes'],
        ], 201);
    }

    /**
     * UPDATE: Equipar o desequipar skin.
     */
    public function equipar(Request $request, int $usuariItemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuariItem = UsuariItem::with('item')->find($usuariItemId);
        if ($usuariItem === null) {
            return response()->json(['error' => 'Objecte no trobat'], 404);
        }

        $resultat = $this->equipAction->executar($userId, $usuariItem);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json([
            'success' => true,
            'usuari_item' => new UsuariItemResource($resultat['usuari_item']),
        ]);
    }

    /**
     * UPDATE: Consumir item consumible.
     */
    public function usarConsumible(Request $request, int $usuariItemId): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuariItem = UsuariItem::with('item')->find($usuariItemId);
        if ($usuariItem === null) {
            return response()->json(['error' => 'Objecte no trobat'], 404);
        }

        $resultat = $this->useConsumibleAction->executar($userId, $usuariItem);

        if (!$resultat['success']) {
            return response()->json(['error' => $resultat['error']], $resultat['status'] ?? 400);
        }

        return response()->json([
            'success' => true,
            'usuari_item' => new UsuariItemResource($resultat['usuari_item']),
            'ratxa_actual' => $resultat['ratxa_actual'],
            'ratxa_maxima' => $resultat['ratxa_maxima'],
        ]);
    }
}
