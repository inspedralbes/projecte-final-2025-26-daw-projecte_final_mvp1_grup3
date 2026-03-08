<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar un usuari o administrador (vista admin) a JSON.
 */
class AdminUsuariResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $isAdmin = $model instanceof \App\Models\Administrador;

        $result = [
            'id' => $model->id,
            'nom' => $model->nom ?? '',
            'email' => $model->email ?? '',
        ];

        if (!$isAdmin) {
            $result['prohibit'] = (bool) ($model->prohibit ?? false);
            $result['motiu_prohibicio'] = $model->motiu_prohibicio ?? null;
            $result['data_prohibicio'] = isset($model->data_prohibicio)
                ? ($model->data_prohibicio instanceof \DateTimeInterface ? $model->data_prohibicio->toIso8601String() : $model->data_prohibicio)
                : null;
        } else {
            $result['created_at'] = isset($model->data_creacio)
                ? ($model->data_creacio instanceof \DateTimeInterface ? $model->data_creacio->toIso8601String() : $model->data_creacio)
                : null;
        }

        return $result;
    }
}
