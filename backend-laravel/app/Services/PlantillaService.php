<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Plantilla;
use App\Models\Habit;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Throwable;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de processament de plantilles.
 * Gestiona el CRUD de plantilles via Redis i consultes directes.
 */
class PlantillaService
{
    /**
     * Instància del servei de feedback per Redis.
     * @var RedisFeedbackService
     */
    private RedisFeedbackService $feedbackService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció de dependències.
     *
     * @param RedisFeedbackService $feedbackService
     */
    public function __construct(RedisFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Obté una col·lecció de plantilles basant-se en filtres.
     *
     * @param string $filter 'all' per a totes (públiques + pròpies), 'my' per a només les de l'usuari.
     * @param int|null $userId L'ID de l'usuari actual.
     * @return Collection
     */
    public function getPlantilles(string $filter, ?int $userId): Collection
    {
        // A. Inicialització de la consulta bàsica
        $query = Plantilla::query();

        // B. Aplicació de filtres segons el tipus de petició
        if ($filter === 'my') {
            if ($userId !== null) {
                // Filtre per plantilles pròpies de l'usuari
                $query->where('creador_id', $userId);
            }
        } else {
            // Filtre general: públiques o pròpies de l'usuari (si s'ha proporcionat ID)
            $query->where('es_publica', true);
            if ($userId !== null) {
                $query->orWhere('creador_id', $userId);
            }
        }

        // C. Execució de la consulta i retorn dels resultats
        return $query->get();
    }

    /**
     * Processa una acció de plantilles (CRUD) rebuda des del Bridge de Redis.
     * Publica el feedback corresponent al canal de sortida.
     *
     * @param array $dades
     * @return void
     */
    public function processarAccioPlantilla(array $dades): void
    {
        // A. Normalització de l'acció
        $accio = '';
        if (isset($dades['action'])) {
            $accio = strtoupper((string) $dades['action']);
        }

        // B. Identificació de l'usuari (valor 1 per defecte segons normes)
        $usuariId = 1;
        if (isset($dades['user_id'])) {
            $usuariId = (int) $dades['user_id'];
        }

        // C. Extracció de l'identificador de la plantilla
        $plantillaId = 0;
        if (isset($dades['plantilla_id'])) {
            $plantillaId = (int) $dades['plantilla_id'];
        }

        // D. Extracció de les dades del formulari de la plantilla
        $plantillaData = [];
        if (isset($dades['plantilla_data'])) {
            if (is_array($dades['plantilla_data'])) {
                $plantillaData = $dades['plantilla_data'];
                
                // Si l'ID no estava al nivell superior, mirem dins de plantilla_data
                if ($plantillaId === 0) {
                    if (isset($plantillaData['id'])) {
                        $plantillaId = (int) $plantillaData['id'];
                    }
                }
            }
        }

        $success = false;
        $plantillaModel = null;
        $errorMessage = null;

        // E. Execució de la lògica de negoci segons l'acció requerida
        try {
            if ($accio === 'CREATE') {
                $plantillaModel = $this->crearPlantilla($usuariId, $plantillaData);
                if ($plantillaModel !== null) {
                    $success = true;
                }
            } elseif ($accio === 'UPDATE') {
                $plantillaModel = $this->actualitzarPlantilla($usuariId, $plantillaId, $plantillaData);
                if ($plantillaModel !== null) {
                    $success = true;
                } else {
                    $errorMessage = "No tens permís o la plantilla no existeix.";
                }
            } elseif ($accio === 'DELETE') {
                $plantillaModel = $this->eliminarPlantilla($usuariId, $plantillaId);
                if ($plantillaModel !== null) {
                    $success = true;
                } else {
                    $errorMessage = "No tens permís o la plantilla no existeix.";
                }
            } else {
                throw new InvalidArgumentException('Acció de plantilles no reconeguda.');
            }
        } catch (Throwable $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }

        // F. Construcció del payload de feedback per a Node.js
        $payload = [
            'type' => 'PLANTILLA',
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $success,
            'message' => $errorMessage,
            'plantilla_id' => $plantillaId
        ];
        
        // G. Inclusió del model actualitzat si l'operació ha tingut èxit
        if ($plantillaModel !== null) {
            $payload['plantilla'] = $plantillaModel->toArray();
        }

        // H. Publicació del feedback a Redis
        $this->feedbackService->publicarPayload($payload);
    }

    /**
     * Crea una plantilla nova al sistema.
     *
     * @param int $usuariId
     * @param array $plantillaData
     * @return Plantilla|null
     */
    private function crearPlantilla(int $usuariId, array $plantillaData): ?Plantilla
    {
        // A. Filtratge i normalització de les dades d'entrada
        $dades = $this->filtrarDadesPlantilla($plantillaData);
        
        $habitsIds = [];
        if (isset($plantillaData['habits_ids'])) {
            $habitsIds = $plantillaData['habits_ids'];
        }

        // B. Validació de camps obligatoris
        if (empty($dades['titol'])) {
            return null;
        }

        // C. Assignació de l'usuari creador
        $dades['creador_id'] = $usuariId;

        // D. Creació del model a la base de dades
        $plantilla = Plantilla::create($dades);

        // E. Vinculació dels hàbits (relació Many-to-Many)
        if ($plantilla) {
            if (!empty($habitsIds)) {
                $plantilla->habits()->attach($habitsIds);
            }
        }

        // F. Retorn de la plantilla amb els hàbits carregats
        return $plantilla->load('habits');
    }

    /**
     * Actualitza una plantilla existent al sistema, validant la propietat.
     *
     * @param int $usuariId
     * @param int $plantillaId
     * @param array $plantillaData
     * @return Plantilla|null
     */
    private function actualitzarPlantilla(int $usuariId, int $plantillaId, array $plantillaData): ?Plantilla
    {
        // A. Cerca de la plantilla
        $plantilla = Plantilla::find($plantillaId);

        // B. Validació de permisos (només el creador pot editar)
        if (!$plantilla) {
            return null;
        }
        
        if ((int)$plantilla->creador_id !== $usuariId) {
            return null;
        }

        // C. Filtratge de dades i actualització del model
        $dades = $this->filtrarDadesPlantilla($plantillaData);

        if (!empty($dades)) {
            $plantilla->update($dades);
        }

        // D. Sincronització dels hàbits associats
        if (isset($plantillaData['habits_ids'])) {
            $plantilla->habits()->sync($plantillaData['habits_ids']);
        }

        // E. Retorn de la versió actualitzada
        return $plantilla->fresh()->load('habits');
    }

    /**
     * Elimina una plantilla del sistema, validant la propietat.
     *
     * @param int $usuariId
     * @param int $plantillaId
     * @return Plantilla|null
     */
    private function eliminarPlantilla(int $usuariId, int $plantillaId): ?Plantilla
    {
        // A. Cerca de la plantilla
        $plantilla = Plantilla::find($plantillaId);

        // B. Validació de permisos (només el creador pot eliminar)
        if (!$plantilla) {
            return null;
        }
        
        if ((int)$plantilla->creador_id !== $usuariId) {
            return null;
        }

        // C. Execució de l'eliminació
        $plantilla->delete();

        return $plantilla;
    }

    /**
     * Filtra les dades de la petició per extreure només els camps vàlids del model.
     *
     * @param array $plantillaData
     * @return array
     */
    private function filtrarDadesPlantilla(array $plantillaData): array
    {
        $dades = [];

        // Filtre clàssic per evitar operadors ternaris o assignacions massives insegures
        if (isset($plantillaData['titol'])) {
            $dades['titol'] = $plantillaData['titol'];
        }

        if (isset($plantillaData['categoria'])) {
            $dades['categoria'] = $plantillaData['categoria'];
        }

        if (isset($plantillaData['es_publica'])) {
            $dades['es_publica'] = (bool)$plantillaData['es_publica'];
        }

        return $dades;
    }
}
