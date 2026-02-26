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
     * @param string $filtre 'all' per a totes (públiques + pròpies), 'my' per a només les de l'usuari.
     * @param int|null $usuariId L'ID de l'usuari actual.
     * @return Collection
     */
    public function getPlantilles(string $filtre, ?int $usuariId): Collection
    {
        // A. Inicialització de la consulta bàsica
        $query = Plantilla::query();

        // B. Aplicació de filtres segons el tipus de petició
        // B1. Si el filtre és "my", limitar a les plantilles pròpies
        if ($filtre === 'my') {
            // B1.1. Si hi ha usuari, aplicar el filtre de propietat
            if ($usuariId !== null) {
                $query->where('creador_id', $usuariId);
            }
        } else {
            // Filtre general: públiques o pròpies de l'usuari (si s'ha proporcionat ID)
            $query->where('es_publica', true);
            // B2. Si hi ha usuari, afegir també les pròpies
            if ($usuariId !== null) {
                $query->orWhere('creador_id', $usuariId);
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
        // A1. Si arriba l'acció, normalitzar-la
        if (isset($dades['action'])) {
            $accio = strtoupper((string) $dades['action']);
        }

        // B. Identificació de l'usuari (valor 1 per defecte segons normes)
        $usuariId = 1;
        // B1. Si arriba user_id, prioritzar-lo
        if (isset($dades['user_id'])) {
            $usuariId = (int) $dades['user_id'];
        }

        // C. Extracció de l'identificador de la plantilla
        $plantillaId = 0;
        // C1. Si arriba plantilla_id, guardar-lo
        if (isset($dades['plantilla_id'])) {
            $plantillaId = (int) $dades['plantilla_id'];
        }

        // D. Extracció de les dades del formulari de la plantilla
        $dadesPlantilla = [];
        // D1. Si arriba plantilla_data, validar-la
        if (isset($dades['plantilla_data'])) {
            // D1.1. Confirmar que és un array
            if (is_array($dades['plantilla_data'])) {
                $dadesPlantilla = $dades['plantilla_data'];

                // D1.2. Log de depuració amb les dades rebudes
                file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - Dades plantilla rebudes: " . json_encode($dadesPlantilla) . "\n", FILE_APPEND);

                // D1.3. Si l'ID no estava al nivell superior, mirar dins de plantilla_data
                if ($plantillaId === 0) {
                    // D1.3.a. Si hi ha id intern, usar-lo
                    if (isset($dadesPlantilla['id'])) {
                        $plantillaId = (int) $dadesPlantilla['id'];
                    }
                }
            }
        }

        $operacioOk = false;
        $plantillaModel = null;
        $missatgeError = null;

        // E. Execució de la lògica de negoci segons l'acció requerida
        try {
            // E1. Acció CREATE
            if ($accio === 'CREATE') {
                $plantillaModel = $this->crearPlantilla($usuariId, $dadesPlantilla);
                if ($plantillaModel !== null) {
                    $operacioOk = true;
                } else {
                    $missatgeError = "Error: El títol de la plantilla no pot estar buit.";
                }
            // E2. Acció UPDATE
            } elseif ($accio === 'UPDATE') {
                $plantillaModel = $this->actualitzarPlantilla($usuariId, $plantillaId, $dadesPlantilla);
                if ($plantillaModel !== null) {
                    $operacioOk = true;
                } else {
                    $missatgeError = "No tens permís o la plantilla no existeix.";
                }
            // E3. Acció DELETE
            } elseif ($accio === 'DELETE') {
                $plantillaModel = $this->eliminarPlantilla($usuariId, $plantillaId);
                if ($plantillaModel !== null) {
                    $operacioOk = true;
                } else {
                    $missatgeError = "No tens permís o la plantilla no existeix.";
                }
            // E4. Acció no reconeguda
            } else {
                throw new InvalidArgumentException('Acció de plantilles no reconeguda.');
            }
        } catch (Throwable $e) {
            $operacioOk = false;
            $missatgeError = $e->getMessage();
            file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - Excepció capturada: " . $e->getMessage() . "\n", FILE_APPEND);
        }

        // F. Construcció del payload de feedback per a Node.js
        $payload = [
            'type' => 'PLANTILLA',
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => $operacioOk,
            'message' => $missatgeError,
            'plantilla_id' => $plantillaId
        ];
        
        // G. Inclusió del model actualitzat si l'operació ha tingut èxit
        // G1. Si hi ha model, afegir-lo al payload
        if ($plantillaModel !== null) {
            $payload['plantilla'] = $plantillaModel->toArray();
        }

        file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - Payload de feedback: " . json_encode($payload) . "\n", FILE_APPEND);

        // H. Publicació del feedback a Redis
        $this->feedbackService->publicarPayload($payload);
    }

    /**
     * Crea una plantilla nova al sistema.
     *
     * @param int $usuariId
     * @param array $dadesPlantilla
     * @return Plantilla|null
     */
    private function crearPlantilla(int $usuariId, array $dadesPlantilla): ?Plantilla
    {
        file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - crearPlantilla amb usuariId: $usuariId, dades: " . json_encode($dadesPlantilla) . "\n", FILE_APPEND);

        // A. Filtratge i normalització de les dades d'entrada
        $dades = $this->filtrarDadesPlantilla($dadesPlantilla);
        
        // A1. Recuperar IDs d'hàbits si estan disponibles
        $idsHabits = [];
        if (isset($dadesPlantilla['habits_ids'])) {
            $idsHabits = $dadesPlantilla['habits_ids'];
        }

        // B. Validació de camps obligatoris
        if (empty($dades['titol'])) {
            file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - crearPlantilla fallida: títol buit.\n", FILE_APPEND);
            return null;
        }

        // C. Assignació de l'usuari creador
        $dades['creador_id'] = $usuariId;

        // D. Creació del model a la base de dades
        $plantilla = Plantilla::create($dades);

        // E. Vinculació dels hàbits (relació Many-to-Many)
        // E1. Si el model existeix, vincular hàbits
        if ($plantilla) {
            // E1.1. Si hi ha hàbits, associar-los
            if (!empty($idsHabits)) {
                $plantilla->habits()->attach($idsHabits);
            }
        }

        // E2. Log de depuració del resultat
        if ($plantilla) {
            $resultatPlantilla = json_encode($plantilla->toArray());
        } else {
            $resultatPlantilla = "null";
        }
        file_put_contents('/tmp/plantilla_service_debug.log', "PlantillaService - crearPlantilla resultat: " . $resultatPlantilla . "\n", FILE_APPEND);

        // F. Retorn de la plantilla amb els hàbits carregats
        return $plantilla->load('habits');
    }

    /**
     * Actualitza una plantilla existent al sistema, validant la propietat.
     *
     * @param int $usuariId
     * @param int $plantillaId
     * @param array $dadesPlantilla
     * @return Plantilla|null
     */
    private function actualitzarPlantilla(int $usuariId, int $plantillaId, array $dadesPlantilla): ?Plantilla
    {
        // A. Cerca de la plantilla
        $plantilla = Plantilla::find($plantillaId);

        // B. Validació de permisos (només el creador pot editar)
        // B1. Si la plantilla no existeix, cancel·lar
        if (!$plantilla) {
            return null;
        }
        
        // B2. Si l'usuari no és el creador, cancel·lar
        if ((int) $plantilla->creador_id !== $usuariId) {
            return null;
        }

        // C. Filtratge de dades i actualització del model
        $dades = $this->filtrarDadesPlantilla($dadesPlantilla);

        // C1. Si hi ha dades, actualitzar
        if (!empty($dades)) {
            $plantilla->update($dades);
        }

        // D. Sincronització dels hàbits associats
        // D1. Si hi ha hàbits, sincronitzar-los
        if (isset($dadesPlantilla['habits_ids'])) {
            $plantilla->habits()->sync($dadesPlantilla['habits_ids']);
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
        // B1. Si la plantilla no existeix, cancel·lar
        if (!$plantilla) {
            return null;
        }
        
        // B2. Si l'usuari no és el creador, cancel·lar
        if ((int) $plantilla->creador_id !== $usuariId) {
            return null;
        }

        // C. Execució de l'eliminació
        $plantilla->delete();

        return $plantilla;
    }

    /**
     * Filtra les dades de la petició per extreure només els camps vàlids del model.
     *
     * @param array $dadesPlantilla
     * @return array
     */
    private function filtrarDadesPlantilla(array $dadesPlantilla): array
    {
        $dades = [];

        // A. Filtre clàssic per evitar operadors ternaris o assignacions massives insegures
        // A1. Si arriba titol, copiar-lo
        if (isset($dadesPlantilla['titol'])) {
            $dades['titol'] = $dadesPlantilla['titol'];
        }

        // A2. Si arriba categoria, copiar-la
        if (isset($dadesPlantilla['categoria'])) {
            $dades['categoria'] = $dadesPlantilla['categoria'];
        }

        // A3. Si arriba es_publica, normalitzar-la
        if (isset($dadesPlantilla['es_publica'])) {
            $dades['es_publica'] = (bool) $dadesPlantilla['es_publica'];
        }

        return $dades;
    }
}
