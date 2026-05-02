<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar una pregunta d'onboarding a JSON.
 */
class OnboardingQuestionResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'id' => (int) ($item['id'] ?? $item->id ?? 0),
            'categoria_id' => (int) ($item['categoria_id'] ?? $item->categoria_id ?? 0),
            'pregunta' => (string) ($item['pregunta'] ?? $item->pregunta ?? ''),
        ];
    }
}
