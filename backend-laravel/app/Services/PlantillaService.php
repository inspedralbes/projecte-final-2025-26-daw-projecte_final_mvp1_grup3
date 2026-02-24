<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Plantilla;
use App\Models\Habit; // Add this import
use Illuminate\Database\Eloquent\Collection; // Import Collection

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
     * Obté una col·lecció de plantilles basant-se en filtres.
     *
     * @param string $filter 'all' per a totes (públiques + de l'usuari), 'my' per a només les de l'usuari.
     * @param int|null $userId L'ID de l'usuari actual.
     * @return Collection<int, Plantilla>
     */
    public function getPlantilles(string $filter, ?int $userId): Collection
    {
        $query = Plantilla::query();

        if ($filter === 'my' && $userId !== null) {
            // Només plantilles creades per l'usuari especificat
            $query->where('creador_id', $userId);
        } else {
            // Totes les plantilles: públiques O creades per l'usuari (si userId no és null)
            $query->where('es_publica', true);
            if ($userId !== null) {
                $query->orWhere('creador_id', $userId);
            }
        }

        return $query->get();
    }

    /**
     * Processa una acció de plantilles (CRUD) rebuda per Redis.
     * Publica el feedback corresponent.
     *
     * @param  array<string, mixed>  $dades
     */
    public function processarAccioPlantilla(array $dades): void
    {
        // A. Normalitzar entrada i assegurar el compliment de les regles de l'agent
        // Inicialització de l'acció amb un valor per defecte.
        $accio = '';
        // B. Comprovar si l'acció ha estat definida en les dades
        if (isset($dades['action'])) {
            // C. Convertir l'acció a majúscules per estandardització
            $accio = strtoupper((string) $dades['action']);
        }

        // D. L'ID d'usuari per defecte és 1, segons les normes de l'agent (sense autenticació).
        $usuariId = 1;

        // E. Inicialització de l'ID de plantilla amb un valor per defecte.
        $plantillaId = 0;
        // F. Comprovar si l'ID de plantilla ha estat definida en les dades
        if (isset($dades['plantilla_id'])) {
            // G. Assignar l'ID de plantilla
            $plantillaId = (int) $dades['plantilla_id'];
        }

        // H. Inicialització de les dades de plantilla com un array buit per defecte.
        $plantillaData = [];
        // I. Comprovar si les dades de plantilla han estat definides i són un array
        if (isset($dades['plantilla_data']) && is_array($dades['plantilla_data'])) {
            // J. Assignar les dades de plantilla
            $plantillaData = $dades['plantilla_data'];
        }

        $success = false;
        $plantillaModel = null;

        // K. Executar l'acció basada en el tipus d'acció normalitzada
        // L. Comprovar si l'acció és 'CREATE'
        if ($accio === 'CREATE') {
            $plantillaModel = $this->crearPlantilla($usuariId, $plantillaData);
            // M. Establir l'èxit si la plantilla ha estat creada correctament
            if ($plantillaModel !== null) {
                $success = true;
            } else {
                $success = false;
            }
        // N. Comprovar si l'acció és 'UPDATE'
        } elseif ($accio === 'UPDATE') {
            $plantillaModel = $this->actualitzarPlantilla($usuariId, $plantillaId, $plantillaData);
            // O. Establir l'èxit si la plantilla ha estat actualitzada correctament
            if ($plantillaModel !== null) {
                $success = true;
            } else {
                $success = false;
            }
        // P. Comprovar si l'acció és 'DELETE'
        } elseif ($accio === 'DELETE') {
            $plantillaModel = $this->eliminarPlantilla($usuariId, $plantillaId);
            // Q. Establir l'èxit si la plantilla ha estat eliminada correctament
            if ($plantillaModel !== null) {
                $success = true;
            } else {
                $success = false;
            }
        // R. Si l'acció no és reconeguda, llançar una excepció
        } else {
            throw new \InvalidArgumentException('Acció de plantilles no reconeguda.');
        }

        // S. Construir payload de feedback
        $payload = [
            'type' => 'PLANTILLA',
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $success,
        ];
        // T. Afegir la plantilla al payload si existeix
        if ($plantillaModel !== null) {
            $payload['plantilla'] = $plantillaModel->toArray();
        } else {
            $payload['plantilla'] = null;
        }


        // U. Publicar feedback a Redis
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
        
        $habitsIds = [];
        // B. Verificar si existeixen hàbits per associar
        if (isset($plantillaData['habits_ids'])) {
            $habitsIds = $plantillaData['habits_ids'];
        }

        // C. Validar títol
        if (empty($dades['titol'])) {
            return null;
        }

        // D. Assignar usuari creador
        $dades['creador_id'] = $usuariId;

        // E. Crear model de plantilla
        $plantilla = Plantilla::create($dades);

        // F. Si la plantilla es crea correctament i hi ha IDs d'hàbits per associar
        if ($plantilla && !empty($habitsIds)) {
            // G. Trobar els hàbits originals i crear-ne còpies associades a la nova plantilla
            $habitsOriginals = Habit::whereIn('id', $habitsIds)->get();

            // H. Iterar sobre cada hàbit original per crear una còpia associada a la plantilla
            foreach ($habitsOriginals as $habitOriginal) {
                $nouHabit = new Habit();
                // Copy relevant attributes from the original habit
                // Assuming Habit model has these fillable attributes
                $nouHabit->titol = $habitOriginal->titol;
                $nouHabit->dificultat = $habitOriginal->dificultat;
                $nouHabit->frequencia_tipus = $habitOriginal->frequencia_tipus;
                $nouHabit->dies_setmana = $habitOriginal->dies_setmana;
                $nouHabit->objectiu_vegades = $habitOriginal->objectiu_vegades;
                $nouHabit->usuari_id = $usuariId; // Associate with the current user
                $nouHabit->plantilla_id = $plantilla->id; // Associate with the new plantilla
                // Add other habit-specific fields as needed based on Habit model fillable
                $nouHabit->save();
            }
        }

        return $plantilla->load('habits');
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
