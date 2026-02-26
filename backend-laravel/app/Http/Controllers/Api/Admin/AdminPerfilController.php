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
     * Retorna les dades de l'administrador autenticat.
     */
    public function show(Request $request): JsonResponse
    {
        $adminId = $request->admin_id;
        // A1. Si no hi ha administrador, denegar accés
        if (!$adminId) {
            return response()->json(['error' => 'No autoritzat'], 401);
        }
        $admin = Administrador::find($adminId);
        // A2. Si no existeix, retornar 404
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
                'created_at' => $admin->data_creacio?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Actualitza nom i email de l'administrador.
     */
    public function update(Request $request): JsonResponse
    {
        // A. Validació de paràmetres d'entrada
        $request->validate([
            'nom' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:150',
        ]);

        $adminId = $request->admin_id;
        // A1. Si no hi ha administrador, denegar accés
        if (!$adminId) {
            return response()->json(['error' => 'No autoritzat'], 401);
        }
        $admin = Administrador::find($adminId);
        // A2. Si no existeix, retornar 404
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        // B. Actualitzar camps si s'han enviat
        if ($request->has('nom')) {
            $admin->nom = $request->input('nom');
        }
        // B1. Actualitzar email si s'ha enviat
        if ($request->has('email')) {
            $admin->email = $request->input('email');
        }
        $admin->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ]
        ]);
    }

    /**
     * Canvia la contrasenya de l'administrador.
     */
    public function canviarPassword(Request $request): JsonResponse
    {
        // A. Validació de paràmetres d'entrada
        $request->validate([
            'contrasenya_actual' => 'required|string',
            'contrasenya_nova' => 'required|string|min:6|confirmed',
        ]);

        $adminId = $request->admin_id;
        // A1. Si no hi ha administrador, denegar accés
        if (!$adminId) {
            return response()->json(['error' => 'No autoritzat'], 401);
        }
        $admin = Administrador::find($adminId);
        // A2. Si no existeix, retornar 404
        if ($admin === null) {
            return response()->json(['error' => 'Administrador no trobat'], 404);
        }

        // B. Comprovar contrasenya actual
        if (!Hash::check($request->input('contrasenya_actual'), $admin->contrasenya_hash)) {
            return response()->json(['error' => 'Contrasenya actual incorrecta'], 422);
        }

        $admin->contrasenya_hash = Hash::make($request->input('contrasenya_nova'));
        $admin->save();

        return response()->json(['success' => true]);
    }
}
