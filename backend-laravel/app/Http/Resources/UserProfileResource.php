<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar el perfil d'usuari a JSON.
 * Rep User model amb ratxa_actual, ratxa_maxima assignats i relació logros carregada.
 */
class UserProfileResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logros = [];
        if ($this->resource->relationLoaded('logros')) {
            foreach ($this->resource->logros as $logro) {
                $dataObtencio = null;
                if ($logro->pivot && isset($logro->pivot->data_obtencio)) {
                    $dataObtencio = $logro->pivot->data_obtencio;
                }
                $logros[] = [
                    'id' => $logro->id,
                    'nom' => $logro->nom,
                    'descripcio' => $logro->descripcio,
                    'tipus' => $logro->tipus,
                    'data_obtencio' => $dataObtencio,
                ];
            }
        }

        return [
            'id' => $this->resource->id,
            'nom' => $this->resource->nom,
            'email' => $this->resource->email,
            'nivell' => (int) $this->resource->nivell,
            'xp_total' => (int) $this->resource->xp_total,
            'xp_actual_nivel' => (int) ($this->resource->xp_actual_nivel ?? 0),
            'xp_objetivo_nivel' => (int) ($this->resource->xp_objetivo_nivel ?? 1000),
            'monedes' => (int) $this->resource->monedes,
            'ratxa_actual' => (int) ($this->resource->ratxa_actual ?? 0),
            'ratxa_maxima' => (int) ($this->resource->ratxa_maxima ?? 0),
            'logros' => $logros,
        ];
    }
}
