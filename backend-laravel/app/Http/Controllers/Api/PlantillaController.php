<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plantilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de plantilles per usuaris.
 * Retorna plantilles públiques i les creades per l'usuari autenticat.
 */
class PlantillaController extends Controller
{
    /**
     * Llista plantilles disponibles per l'usuari: públiques o creades per ell.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        if (!$userId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $plantilles = Plantilla::where('es_publica', true)
            ->orWhere('creador_id', $userId)
            ->with('creador:id,nom,email')
            ->get();

        return response()->json([
            'success' => true,
            'plantilles' => $plantilles,
        ]);
    }
}
