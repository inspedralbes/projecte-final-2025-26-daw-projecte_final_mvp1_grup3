<?php

namespace App\Http\Controllers;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de lectura d'hàbits.
 */
class HabitController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté els hàbits d'un usuari.
     */
    public function index(Request $request)
    {
        $usuariId = $request->query('user_id');

        // A. Si no hi ha usuari, retornar error
        if (!$usuariId) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ID d\'usuari és requerit.'
            ], 400);
        }

        // B. Recuperar hàbits de l'usuari
        $habits = Habit::where('usuari_id', $usuariId)->get();

        return response()->json([
            'success' => true,
            'habits' => $habits
        ]);
    }
}
