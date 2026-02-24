<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador del perfil de l'administrador connectat.
 * MVP1: admin id 1 per defecte.
 */
class AdminPerfilController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna les dades de l'administrador.
     */
    public function show(): JsonResponse
    {
        $admin = Administrador::find(1);
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        return response()->json([
            'id' => $admin->id,
            'nom' => $admin->nom,
            'email' => $admin->email,
            'data_creacio' => $admin->data_creacio?->toIso8601String(),
        ]);
    }

    /**
     * Actualitza nom i email de l'administrador.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:150',
        ]);

        $admin = Administrador::find(1);
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        if ($request->has('nom')) {
            $admin->nom = $request->input('nom');
        }
        if ($request->has('email')) {
            $admin->email = $request->input('email');
        }
        $admin->save();

        return response()->json(['success' => true, 'data' => [
            'id' => $admin->id,
            'nom' => $admin->nom,
            'email' => $admin->email,
        ]]);
    }

    /**
     * Canvia la contrasenya de l'administrador.
     */
    public function canviarPassword(Request $request): JsonResponse
    {
        $request->validate([
            'contrasenya_actual' => 'required|string',
            'contrasenya_nova' => 'required|string|min:6|confirmed',
        ]);

        $admin = Administrador::find(1);
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        if (! Hash::check($request->input('contrasenya_actual'), $admin->contrasenya_hash)) {
            return response()->json(['error' => 'Contrasenya actual incorrecta'], 422);
        }

        $admin->contrasenya_hash = Hash::make($request->input('contrasenya_nova'));
        $admin->save();

        return response()->json(['success' => true]);
    }
}
