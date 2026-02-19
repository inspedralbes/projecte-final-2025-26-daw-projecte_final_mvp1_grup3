<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    /**
     * Obté els hàbits d'un usuari.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ID d\'usuari és requerit.'
            ], 400);
        }

        $habits = Habit::where('usuari_id', $userId)->get();

        return response()->json([
            'success' => true,
            'habits' => $habits
        ]);
    }
}
