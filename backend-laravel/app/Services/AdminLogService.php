<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei per registrar accions d'administració.
 * Cada acció es desa amb estat abans i després per auditoria.
 */
class AdminLogService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Registra una acció d'administració a la taula ADMIN_LOGS.
     * Pas A: Validar i normalitzar paràmetres.
     * Pas B: Crear el registre a la base de dades.
     * Pas C: En cas d'error, registrar a logs del sistema.
     *
     * @param  int  $administradorId  ID de l'administrador que fa l'acció (MVP1: 1).
     * @param  string  $accio  Tipus d'acció (ex: "Crear plantilla", "Prohibir usuari").
     * @param  string|null  $detall  Text resum de l'acció.
     * @param  array|null  $abans  Estat o dades anteriors (JSONB).
     * @param  array|null  $despres  Estat o dades posteriors (JSONB).
     * @param  string|null  $ip  Adreça IP de la petició.
     */
    public function registrar(
        int $administradorId,
        string $accio,
        ?string $detall = null,
        ?array $abans = null,
        ?array $despres = null,
        ?string $ip = null
    ): void {
        // A. Validar i normalitzar paràmetres
        if ($administradorId < 1) {
            $administradorId = 1;
        }

        // B. Crear el registre a la base de dades
        try {
            AdminLog::create([
                'administrador_id' => $administradorId,
                'accio' => $accio,
                'detall' => $detall,
                'abans' => $abans,
                'despres' => $despres,
                'ip' => $ip,
            ]);
        } catch (\Throwable $e) {
            // C. En cas d'error, registrar a logs del sistema
            Log::error('AdminLogService::registrar error: '.$e->getMessage(), [
                'accio' => $accio,
                'administrador_id' => $administradorId,
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir la IP de la petició HTTP actual.
     */
    public function obtenirIpDeRequest(?Request $request = null): ?string
    {
        if ($request === null) {
            return null;
        }

        $ip = $request->ip();
        if (is_string($ip)) {
            return $ip;
        }

        return null;
    }
}
