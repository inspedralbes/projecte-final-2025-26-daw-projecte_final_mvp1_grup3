<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Plantilla;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de processament de plantilles.
 * Gestiona el CRUD de plantilles via Redis.
 */
class PlantillaService
{
    /**
     * Servei de feedback per Redis.
     */
    private RedisFeedbackService $feedbackService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei de feedback.
     */
    public function __construct(RedisFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Processa una acció de plantilles (CRUD) rebuda per Redis.
     * Publica el feedback corresponent.
     *
     * @param  array<string, mixed>  $dades
     */
    public function processarAccioPlantilla(array $dades): void
    {
        // A. Normalitzar entrada
        $accio = isset($dades['action']) ? strtoupper((string) $dades['action']) : '';
        $usuariId = isset($dades['user_id']) ? (int) $dades['user_id'] : 1;
        $plantillaId = isset($dades['plantilla_id']) ? (int) $dades['plantilla_id'] : 0;
        if (isset($dades['plantilla_data']) && is_array($dades['plantilla_data'])) {
            $plantillaData = $dades['plantilla_data'];
        } else {
            $plantillaData = [];
        }

        $success = false;
        $plantillaModel = null;

        // B. Executar l'acció
        if ($accio === 'CREATE') {
            $plantillaModel = $this->crearPlantilla($usuariId, $plantillaData);
            $success = $plantillaModel !== null;
        } elseif ($accio === 'UPDATE') {
            $plantillaModel = $this->actualitzarPlantilla($usuariId, $plantillaId, $plantillaData);
            $success = $plantillaModel !== null;
        } elseif ($accio === 'DELETE') {
            $plantillaModel = $this->eliminarPlantilla($usuariId, $plantillaId);
            $success = $plantillaModel !== null;
        } else {
            throw new \InvalidArgumentException('Acció de plantilles no reconeguda.');
        }

        // C. Construir payload de feedback
        $payload = [
            'type' => 'PLANTILLA',
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $success,
            'plantilla' => $plantillaModel ? $plantillaModel->toArray() : null,
        ];

        // D. Publicar feedback a Redis
        $this->feedbackService->publicarPayload($payload);
    }

    /**
     * Crea una plantilla nova per a l'usuari.
     *
     * @param  int  $usuariId
     * @param  array<string, mixed>  $plantillaData
     */
    private function crearPlantilla(int $usuariId, array $plantillaData): ?Plantilla
    {
        // A. Normalitzar dades d'entrada
        $dades = $this->filtrarDadesPlantilla($plantillaData);

        // B. Validar títol
        if (empty($dades['titol'])) {
            return null;
        }

        // C. Assignar usuari creador
        $dades['creador_id'] = $usuariId;

        // D. Crear model
        return Plantilla::create($dades);
    }

    /**
     * Actualitza una plantilla existent.
     *
     * @param  int  $usuariId
     * @param  int  $plantillaId
     * @param  array<string, mixed>  $plantillaData
     */
    private function actualitzarPlantilla(int $usuariId, int $plantillaId, array $plantillaData): ?Plantilla
    {
        // A. Recuperar plantilla
        $plantilla = Plantilla::find($plantillaId);

        // A1. Validar existència i propietat
        if (! $plantilla || (int) $plantilla->creador_id !== $usuariId) {
            return null;
        }

        // B. Actualitzar camps permesos
        $dades = $this->filtrarDadesPlantilla($plantillaData);

        if (! empty($dades)) {
            $plantilla->update($dades);
        }

        return $plantilla->fresh();
    }

    /**
     * Elimina una plantilla existent.
     *
     * @param  int  $usuariId
     * @param  int  $plantillaId
     */
    private function eliminarPlantilla(int $usuariId, int $plantillaId): ?Plantilla
    {
        // A. Recuperar plantilla
        $plantilla = Plantilla::find($plantillaId);

        // A1. Validar existència i propietat
        if (! $plantilla || (int) $plantilla->creador_id !== $usuariId) {
            return null;
        }

        // B. Eliminar
        $plantilla->delete();

        return $plantilla;
    }

    /**
     * Filtra i normalitza les dades d'una plantilla.
     *
     * @param  array<string, mixed>  $plantillaData
     * @return array<string, mixed>
     */
    private function filtrarDadesPlantilla(array $plantillaData): array
    {
        $dades = [];

        if (isset($plantillaData['titol'])) {
            $dades['titol'] = $plantillaData['titol'];
        }

        if (isset($plantillaData['categoria'])) {
            $dades['categoria'] = $plantillaData['categoria'];
        }

        if (isset($plantillaData['es_publica'])) {
            $dades['es_publica'] = (bool) $plantillaData['es_publica'];
        }

        return $dades;
    }
}
