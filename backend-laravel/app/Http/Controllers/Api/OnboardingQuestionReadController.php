<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\OnboardingQuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per al flux d'onboarding.
 *
 * Operacions:
 *   - READ: questions (8 preguntes representatives, una per categoria)
 */
class OnboardingQuestionReadController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Retorna 8 preguntes representatives (una per categoria).
     */
    public function questions(): JsonResponse
    {
        try {
            if (!Schema::hasTable('preguntes_registre')) {
                return response()->json([
                    'success' => true,
                    'preguntes' => [],
                ]);
            }

            $categories = [1, 2, 3, 4, 5, 6, 7, 8];
            $preguntesFinals = collect();

            foreach ($categories as $catId) {
                $preguntesCategoria = DB::table('preguntes_registre')
                    ->select('id', 'categoria_id', 'pregunta')
                    ->where('categoria_id', $catId)
                    ->inRandomOrder()
                    ->limit(2)
                    ->get();

                $preguntesFinals = $preguntesFinals->concat($preguntesCategoria);
            }

            $preguntesFinals = $preguntesFinals->shuffle();
            $preguntes = OnboardingQuestionResource::collection($preguntesFinals)->resolve(request());
            $preguntesArray = $preguntes['data'] ?? $preguntes;
        } catch (\Throwable $e) {
            $preguntesArray = [];
        }

        return response()->json([
            'success' => true,
            'preguntes' => $preguntesArray,
        ]);
    }
}
