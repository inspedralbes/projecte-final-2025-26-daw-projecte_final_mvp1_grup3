<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar el model LogroMedalla a JSON.
 */
class LogroResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // A. Definició del format de sortida per al frontend
        // S'afegeix el camp obtingut, que indica si l'usuari actual l'ha aconseguit.
        $obtingut = false;
        if (isset($this->desbloquejat)) {
            $obtingut = (bool) $this->desbloquejat;
        }

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'descripcio' => $this->descripcio,
            'tipus' => $this->tipus,
            'obtingut' => $obtingut,
        ];
    }
}
