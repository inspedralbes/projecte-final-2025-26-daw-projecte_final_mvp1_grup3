<?php

declare(strict_types=1);

namespace App\Domains\Shop\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Shop\Support\ShopBalanceGuard;
use App\Models\BotigaItem;
use App\Models\User;
use App\Models\UsuariItem;
use App\Services\RedisFeedbackService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Compra d'un item de la botiga amb transacció i feedback Redis.
 */
class PurchaseItemAction
{
    private ShopBalanceGuard $balanceGuard;

    private RedisFeedbackService $feedback;

    public function __construct(ShopBalanceGuard $balanceGuard, RedisFeedbackService $feedback)
    {
        $this->balanceGuard = $balanceGuard;
        $this->feedback = $feedback;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function executar(int $userId, BotigaItem $item): array
    {
        $resultat = DB::transaction(function () use ($userId, $item) {
            $usuari = User::where('id', $userId)->lockForUpdate()->first();
            if ($usuari === null) {
                return ['success' => false, 'error' => 'Usuari no trobat', 'status' => 404];
            }

            $validacio = $this->balanceGuard->validarCompra($usuari, $item, $userId);
            if (isset($validacio['error'])) {
                return [
                    'success' => false,
                    'error' => $validacio['error'],
                    'status' => $validacio['status'] ?? 400,
                ];
            }

            $preu = (int) $item->preu;
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
                'success' => true,
                'usuari_item' => $usuariItem->load('item'),
                'monedes' => (int) $usuari->monedes,
            ];
        });

        if (($resultat['success'] ?? false) !== true) {
            return $resultat;
        }

        $usuariItem = $resultat['usuari_item'];

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

        return $resultat;
    }
}
