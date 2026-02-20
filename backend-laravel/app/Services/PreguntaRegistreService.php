<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\PreguntaRegistre;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de gestió de preguntes de registre.
 * Conté la lògica de negoci per obtenir preguntes per categoria.
 */
class PreguntaRegistreService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté totes les preguntes de registre d'una categoria.
     *
     * A. Valida que l'ID de categoria sigui vàlid.
     * B. Recupera les preguntes de la base de dades filtrades per categoria_id.
     * C. Retorna la col·lecció de preguntes.
     *
     * @param  int  $categoriaId  Identificador de la categoria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenirPreguntesPerCategoria(int $categoriaId)
    {
        // A. Validar que l'ID sigui positiu
        if ($categoriaId <= 0) {
            throw new \InvalidArgumentException('L\'ID de categoria ha de ser un enter positiu.');
        }

        // B. Recuperar preguntes filtrades per categoria
        $preguntes = PreguntaRegistre::where('categoria_id', $categoriaId)->get();

        // C. Retornar la col·lecció
        return $preguntes;
    }
}
