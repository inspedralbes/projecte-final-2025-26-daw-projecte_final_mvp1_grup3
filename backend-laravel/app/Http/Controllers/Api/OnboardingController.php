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
        // Obtenim la primera pregunta de cada categoria (ID 1 a 8)
        // Nota: En una app real, podríem tenir una columna 'es_representativa' o similar.
        // Aquí agafem el MIN(id) de cada categoria per simplicitat.

        $preguntes = DB::table('preguntes_registre')
            ->select('id', 'categoria_id', 'pregunta')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('preguntes_registre')
                    ->groupBy('categoria_id');
            })
            ->orderBy('categoria_id')
            ->get();

        return response()->json([
            'success' => true,
            'preguntes' => $preguntes
        ]);
    }
}
