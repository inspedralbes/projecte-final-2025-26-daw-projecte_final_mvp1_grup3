<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\LogroMedalla;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de gestió de logros i medalles.
 * Conté la lògica de negoci per als logros.
 */
class LogroService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté tots els logros i medalles disponibles.
     *
     * A. Recupera tots els registres de la taula logros_medalles.
     * B. Retorna la col·lecció de models.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function llistarTotsElsLogros()
    {
        // A. Recuperar tots els logros
        $logros = LogroMedalla::all();

        // B. Retornar la col·lecció
        return $logros;
    }
}
