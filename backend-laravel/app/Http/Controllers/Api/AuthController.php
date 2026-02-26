<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Habit;
use App\Models\Ratxa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuaris',
            'password' => 'required|string|min:6',
            'categoria_id' => 'required|integer|exists:categories,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Crear l'usuari
                $user = User::create([
                    'nom' => $request->name,
                    'email' => $request->email,
                    'contrasenya_hash' => Hash::make($request->password), // En un entorn real usaríem Hash::make
                    'nivell' => 1,
                    'xp_total' => 0,
                    'monedes' => 0,
                ]);

                // 2. Inicialitzar ratxa
                Ratxa::create([
                    'usuari_id' => $user->id,
                    'ratxa_actual' => 0,
                    'ratxa_maxima' => 0,
                    'ultima_data' => null,
                ]);

                // 3. Assignar hàbits de la categoria seleccionada
                // Busquem hàbits d'exemple que pertanyin a aquesta categoria
                // (En aquest MVP, agafem els que l'admin ha creat com a exemples)
                $habitsExemple = Habit::where('categoria_id', $request->categoria_id)
                    ->where('usuari_id', 1) // Hàbits de l'admin
                    ->get();

                foreach ($habitsExemple as $habitExemple) {
                    // Creem una còpia de l'hàbit per al nou usuari
                    $nouHabit = Habit::create([
                        'usuari_id' => $user->id,
                        'plantilla_id' => $habitExemple->plantilla_id,
                        'categoria_id' => $habitExemple->categoria_id,
                        'titol' => $habitExemple->titol,
                        'dificultat' => $habitExemple->dificultat,
                        'frequencia_tipus' => $habitExemple->frequencia_tipus,
                        'dies_setmana' => $habitExemple->dies_setmana,
                        'objectiu_vegades' => $habitExemple->objectiu_vegades,
                        'icona' => $habitExemple->icona,
                        'color' => $habitExemple->color,
                    ]);

                    // Vincular a usuari_habits
                    DB::table('usuaris_habits')->insert([
                        'usuari_id' => $user->id,
                        'habit_id' => $nouHabit->id,
                        'data_inici' => now(),
                        'actiu' => true,
                        'objetiu_vegades_personalitzat' => $habitExemple->objectiu_vegades,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Usuari registrat correctament',
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'email' => $user->email,
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar l'usuari: ' . $e->getMessage(),
            ], 500);
        }
    }
}
