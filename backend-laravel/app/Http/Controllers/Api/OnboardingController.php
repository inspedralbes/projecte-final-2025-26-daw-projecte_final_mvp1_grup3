<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador per al flux d'onboarding.
 * Gestiona l'obtenció de preguntes representatives per determinar la categoria.
 */
class OnboardingController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna 8 preguntes representatives (una per categoria).
     * S'utilitza per a l'onboarding dinàmic sense selecció prèvia.
     */
    public function questions(): JsonResponse
    {
        // Obtenim totes les categories per iterar (MVP1: 1 a 8)
        $categories = [1, 2, 3, 4, 5, 6, 7, 8];
        $preguntesFinals = collect();

        foreach ($categories as $catId) {
            // Agafem 2 preguntes aleatòries de cada categoria
            $preguntesCategoria = DB::table('preguntes_registre')
                ->select('id', 'categoria_id', 'pregunta')
                ->where('categoria_id', $catId)
                ->inRandomOrder()
                ->limit(2)
                ->get();

            $preguntesFinals = $preguntesFinals->concat($preguntesCategoria);
        }

        // Aleatoritzem l'ordre de totes les preguntes (perquè no surtin per blocs de categoria)
        $preguntesFinals = $preguntesFinals->shuffle();

        return response()->json([
            'success' => true,
            'preguntes' => $preguntesFinals
        ]);
    }
}
