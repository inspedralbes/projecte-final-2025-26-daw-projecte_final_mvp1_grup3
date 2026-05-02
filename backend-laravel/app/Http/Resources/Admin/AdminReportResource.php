<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar un report (vista admin) a JSON.
 */
class AdminReportResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuari' => $this->usuari ? $this->usuari->nom : 'Sistema',
            'tipus' => $this->tipus ?? '',
            'contingut' => $this->contingut ?? null,
            'post_id' => $this->post_id ?? null,
            'data' => $this->created_at ? $this->created_at->diffForHumans() : null,
        ];
    }
}
