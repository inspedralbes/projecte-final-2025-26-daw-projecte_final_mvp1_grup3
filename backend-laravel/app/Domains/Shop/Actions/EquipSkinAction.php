<?php

declare(strict_types=1);

namespace App\Domains\Shop\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Calendar\Services\SnapshotService;
use App\Domains\Shared\Services\RedisFeedbackService;
use App\Models\User;
use App\Models\UsuariItem;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Equipa o desequipa un skin per slot.
 */
class EquipSkinAction
{
    private RedisFeedbackService $feedback;

    private SnapshotService $snapshotService;

    public function __construct(RedisFeedbackService $feedback, SnapshotService $snapshotService)
    {
        $this->feedback = $feedback;
        $this->snapshotService = $snapshotService;
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

        if ($usuariItem->item === null || $usuariItem->item->tipus !== 'skin') {
            return ['success' => false, 'error' => 'Aquest objecte no es pot equipar', 'status' => 400];
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

        $usuari = User::find($userId);
        if ($usuari !== null) {
            $this->snapshotService->refreshMascotaCosmeticsForUser($usuari);
        }

        $kind = 'equipped';
        if ($estavaEquipat) {
            $kind = 'unequipped';
        }

        $this->feedback->publicarPayload([
            'type' => 'SHOP',
            'action' => $estavaEquipat ? 'UNEQUIP' : 'EQUIP',
            'user_id' => $userId,
            'success' => true,
            'shop_event' => [
                'kind' => $kind,
                'usuari_item_id' => $usuariItem->id,
                'slot' => $slot,
                'skin_key' => $metadata['skin_key'] ?? null,
                'item' => $usuariItem,
            ],
        ]);

        return [
            'success' => true,
            'usuari_item' => $usuariItem,
        ];
    }
}

