<?php

declare(strict_types=1);

namespace App\Domains\Shop\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\BotigaItem;
use App\Models\User;
use App\Models\UsuariItem;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Catàleg actiu, inventari i monedes de l'usuari.
 */
class GetShopCatalogQuery
{
    /**
     * @return array{items: mixed, inventari: mixed, monedes: int}
     */
    public function executar(int $userId): array
    {
        $items = BotigaItem::where('actiu', true)->orderBy('preu')->get();

        $inventari = UsuariItem::with('item')
            ->where('usuari_id', $userId)
            ->orderByDesc('comprat_at')
            ->get();

        $monedes = (int) (User::where('id', $userId)->value('monedes') ?? 0);

        return [
            'items' => $items,
            'inventari' => $inventari,
            'monedes' => $monedes,
        ];
    }
}
