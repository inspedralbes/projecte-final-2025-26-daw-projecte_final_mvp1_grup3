<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\AdminConfiguracio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de configuració global del sistema.
 * Key-value per XP per hàbit, monedes per missió, etc.
 */
class AdminConfiguracioController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna tota la configuració com a objecte clau-valor.
     */
    public function show(): JsonResponse
    {
        $configs = AdminConfiguracio::all();
        $resultat = [];
        // A. Recórrer configuracions i construir mapa clau-valor
        foreach ($configs as $config) {
            $resultat[$config->clau] = $config->valor;
        }

        return response()->json([
            'success' => true,
            'data' => $resultat
        ]);
    }

    /**
     * Actualitza la configuració. Body: { "clau": "valor", ... }
     */
    public function update(Request $request): JsonResponse
    {
        $dades = $request->all();
        // A. Si arriba embolicat sota "data", desempaquetar
        if (isset($dades['data']) && is_array($dades['data'])) {
            $dades = $dades['data'];
        }
        // B. Recórrer totes les claus i actualitzar o crear
        foreach ($dades as $clau => $valor) {
            AdminConfiguracio::updateOrCreate(
                ['clau' => $clau],
                ['valor' => (string) $valor, 'updated_at' => now()]
            );
        }

        return response()->json(['success' => true]);
    }
}
