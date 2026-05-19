<?php

declare(strict_types=1);

namespace App\Domains\Shop\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\BotigaItem;
use App\Models\User;
use App\Models\UsuariItem;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Validacions de saldo i duplicats de la botiga.
 */
class ShopBalanceGuard
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{error?: string, status?: int}
     */
    public function validarCompra(User $usuari, BotigaItem $item, int $userId): array
    {
        $preu = (int) $item->preu;
        $saldo = (int) $usuari->monedes;
        if ($saldo < $preu) {
            return ['error' => 'Saldo insuficient', 'status' => 402];
        }

        if ($item->tipus === 'skin') {
            $jaEnTe = UsuariItem::where('usuari_id', $userId)
                ->where('item_id', $item->id)
                ->exists();
            if ($jaEnTe) {
                return ['error' => 'Ja tens aquest objecte', 'status' => 409];
            }
        }

        return [];
    }
}
