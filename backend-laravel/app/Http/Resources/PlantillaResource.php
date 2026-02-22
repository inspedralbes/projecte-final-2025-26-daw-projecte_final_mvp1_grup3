<?php
declare(strict_types=1);

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs JSON per transformar el model Plantilla.
 * Format net per consum del frontend (Nuxt 3).
 */
class PlantillaResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma la plantilla a un array associatiu per la resposta JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // A. Mapatge directe dels camps principals del model
        return [
            'id' => $this->id,
            'creador_id' => $this->creador_id,
            'titol' => $this->titol,
            'categoria' => $this->categoria,
            'es_publica' => $this->es_publica,
        ];
    }
}
