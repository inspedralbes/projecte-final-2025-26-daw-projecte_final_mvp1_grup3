<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Enviament d'un missatge al xat del clan.
 */
class SendClanMessageAction
{
    private ClanAccessGuard $guard;

    public function __construct(ClanAccessGuard $guard)
    {
        $this->guard = $guard;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param array<string, mixed> $dades
     * @return array<string, mixed>
     */
    public function executar(int $userId, int $clanId, array $dades): array
    {
        $nivell = $this->guard->validarNivellMinim($userId);
        if ($nivell !== null) {
            return ['success' => false, 'error' => $nivell['error'], 'status' => $nivell['status']];
        }

        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $messageId = DB::table('clan_messages')->insertGetId([
            'clan_id' => $clanId,
            'usuari_id' => $userId,
            'contingut' => $dades['contingut'],
            'habit_id' => $dades['habit_id'] ?? null,
            'plantilla_id' => $dades['plantilla_id'] ?? null,
            'created_at' => now(),
        ]);

        $message = DB::table('clan_messages')
            ->join('usuaris', 'clan_messages.usuari_id', '=', 'usuaris.id')
            ->where('clan_messages.id', $messageId)
            ->select('clan_messages.*', 'usuaris.nom as usuari_nom', 'usuaris.id as usuari_id')
            ->first();

        return [
            'success' => true,
            'id' => $messageId,
            'message' => $message,
        ];
    }
}
