<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Administrador;
use App\Models\Habit;
use App\Models\LogroMedalla;
use App\Models\MissioDiaria;
use App\Models\Plantilla;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei per processar accions CUD d'admin rebudes per admin_queue.
 * Processa plantilla, usuari, admin, habit, logro, missio.
 */
class AdminActionService
{
    private AdminLogService $adminLogService;

    private RedisFeedbackService $feedbackService;

    public function __construct(AdminLogService $adminLogService, RedisFeedbackService $feedbackService)
    {
        $this->adminLogService = $adminLogService;
        $this->feedbackService = $feedbackService;
    }

    /**
     * Processa una acció d'admin rebuda de la cua Redis.
     * Pas A: Validar payload (entity, action, admin_id, data).
     * Pas B: Executar l'acció segons entity i action.
     * Pas C: Registrar log i publicar feedback.
     *
     * @param  array<string, mixed>  $dades
     */
    public function processarAccio(array $dades): void
    {
        $adminId = 1;
        if (isset($dades['admin_id'])) {
            $adminId = (int) $dades['admin_id'];
        }
        if ($adminId < 1) {
            $adminId = 1;
        }

        $entity = '';
        if (isset($dades['entity'])) {
            $entity = $dades['entity'];
        }
        $action = '';
        if (isset($dades['action'])) {
            $action = strtoupper((string) $dades['action']);
        }
        $data = [];
        if (isset($dades['data'])) {
            $data = $dades['data'];
        }

        if ($entity === '' || $action === '') {
            $this->enviarFeedback($adminId, $entity, $action, false, ['error' => 'entity o action mancant']);

            return;
        }

        try {
            DB::beginTransaction();

            if ($entity === 'plantilla') {
                $this->processarPlantilla($adminId, $action, $data);
            } elseif ($entity === 'usuari') {
                $this->processarUsuari($adminId, $action, $data);
            } elseif ($entity === 'admin') {
                $this->processarAdmin($adminId, $action, $data);
            } elseif ($entity === 'habit') {
                $this->processarHabit($adminId, $action, $data);
            } elseif ($entity === 'logro') {
                $this->processarLogro($adminId, $action, $data);
            } elseif ($entity === 'missio') {
                $this->processarMissio($adminId, $action, $data);
            } else {
                $this->enviarFeedback($adminId, $entity, $action, false, ['error' => 'entity desconeguda: '.$entity]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->adminLogService->registrar($adminId, 'Error: '.$e->getMessage(), (string) $e->getMessage(), null, null, null);
            $this->enviarFeedback($adminId, $entity, $action, false, ['error' => $e->getMessage()]);
        }
    }

    private function processarPlantilla(int $adminId, string $action, array $data): void
    {
        $abans = null;
        $despres = null;

        if ($action === 'CREATE') {
            $plantilla = Plantilla::create([
                'creador_id' => $data['creador_id'] ?? 1,
                'titol' => $data['titol'] ?? '',
                'categoria' => $data['categoria'] ?? null,
                'es_publica' => $data['es_publica'] ?? false,
            ]);
            $despres = $plantilla->toArray();
            $this->adminLogService->registrar($adminId, 'Crear plantilla', 'Plantilla ID '.$plantilla->id.': '.$plantilla->titol, null, $despres, null);
            $this->enviarFeedback($adminId, 'plantilla', 'CREATE', true, $plantilla->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $plantilla = Plantilla::find($id);
            if ($plantilla === null) {
                throw new \RuntimeException('Plantilla no trobada');
            }
            $abans = $plantilla->toArray();
            $plantilla->titol = $data['titol'] ?? $plantilla->titol;
            $plantilla->categoria = $data['categoria'] ?? $plantilla->categoria;
            $plantilla->es_publica = $data['es_publica'] ?? $plantilla->es_publica;
            $plantilla->save();
            $despres = $plantilla->toArray();
            $this->adminLogService->registrar($adminId, 'Editar plantilla', 'Plantilla ID '.$id.': '.$plantilla->titol, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'plantilla', 'UPDATE', true, $plantilla->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            $plantilla = Plantilla::find($id);
            if ($plantilla === null) {
                throw new \RuntimeException('Plantilla no trobada');
            }
            $abans = $plantilla->toArray();
            $plantilla->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar plantilla', 'Plantilla ID '.$id.': '.$plantilla->titol, $abans, null, null);
            $this->enviarFeedback($adminId, 'plantilla', 'DELETE', true, ['id' => $id]);
        }
    }

    private function processarUsuari(int $adminId, string $action, array $data): void
    {
        if ($action === 'CREATE') {
            $usuari = User::create([
                'nom' => $data['nom'] ?? '',
                'email' => $data['email'] ?? '',
                'contrasenya_hash' => bcrypt($data['contrasenya'] ?? 'password'),
            ]);
            $despres = $usuari->toArray();
            unset($despres['contrasenya_hash']);
            $this->adminLogService->registrar($adminId, 'Crear usuari', 'Usuari ID '.$usuari->id.': '.$usuari->nom, null, $despres, null);
            $this->enviarFeedback($adminId, 'usuari', 'CREATE', true, $usuari->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $usuari = User::find($id);
            if ($usuari === null) {
                throw new \RuntimeException('Usuari no trobat');
            }
            $abans = $usuari->toArray();
            unset($abans['contrasenya_hash']);
            $usuari->nom = $data['nom'] ?? $usuari->nom;
            $usuari->email = $data['email'] ?? $usuari->email;
            if (! empty($data['contrasenya'])) {
                $usuari->contrasenya_hash = bcrypt($data['contrasenya']);
            }
            $usuari->save();
            $despres = $usuari->toArray();
            unset($despres['contrasenya_hash']);
            $this->adminLogService->registrar($adminId, 'Editar usuari', 'Usuari ID '.$id.': '.$usuari->nom, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'usuari', 'UPDATE', true, $usuari->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            $usuari = User::find($id);
            if ($usuari === null) {
                throw new \RuntimeException('Usuari no trobat');
            }
            $abans = $usuari->toArray();
            unset($abans['contrasenya_hash']);
            $usuari->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar usuari', 'Usuari ID '.$id, $abans, null, null);
            $this->enviarFeedback($adminId, 'usuari', 'DELETE', true, ['id' => $id]);
        }
    }

    private function processarAdmin(int $adminId, string $action, array $data): void
    {
        if ($action === 'CREATE') {
            $admin = Administrador::create([
                'nom' => $data['nom'] ?? '',
                'email' => $data['email'] ?? '',
                'contrasenya_hash' => bcrypt($data['contrasenya'] ?? 'password'),
            ]);
            $despres = $admin->toArray();
            unset($despres['contrasenya_hash']);
            $this->adminLogService->registrar($adminId, 'Crear administrador', 'Admin ID '.$admin->id.': '.$admin->nom, null, $despres, null);
            $this->enviarFeedback($adminId, 'admin', 'CREATE', true, $admin->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $admin = Administrador::find($id);
            if ($admin === null) {
                throw new \RuntimeException('Administrador no trobat');
            }
            $abans = $admin->toArray();
            unset($abans['contrasenya_hash']);
            $admin->nom = $data['nom'] ?? $admin->nom;
            $admin->email = $data['email'] ?? $admin->email;
            if (! empty($data['contrasenya'])) {
                $admin->contrasenya_hash = bcrypt($data['contrasenya']);
            }
            $admin->save();
            $despres = $admin->toArray();
            unset($despres['contrasenya_hash']);
            $this->adminLogService->registrar($adminId, 'Editar administrador', 'Admin ID '.$id.': '.$admin->nom, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'admin', 'UPDATE', true, $admin->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            if ($id === 1) {
                throw new \RuntimeException('No es pot eliminar l\'administrador principal');
            }
            $admin = Administrador::find($id);
            if ($admin === null) {
                throw new \RuntimeException('Administrador no trobat');
            }
            $abans = $admin->toArray();
            unset($abans['contrasenya_hash']);
            $admin->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar administrador', 'Admin ID '.$id, $abans, null, null);
            $this->enviarFeedback($adminId, 'admin', 'DELETE', true, ['id' => $id]);
        }
    }

    private function processarHabit(int $adminId, string $action, array $data): void
    {
        if ($action === 'CREATE') {
            $habit = Habit::create([
                'usuari_id' => $data['usuari_id'] ?? 1,
                'plantilla_id' => $data['plantilla_id'] ?? null,
                'categoria_id' => $data['categoria_id'] ?? null,
                'titol' => $data['titol'] ?? '',
                'dificultat' => $data['dificultat'] ?? null,
                'frequencia_tipus' => $data['frequencia_tipus'] ?? null,
                'dies_setmana' => $data['dies_setmana'] ?? null,
                'objectiu_vegades' => $data['objectiu_vegades'] ?? 1,
            ]);
            $despres = $habit->toArray();
            $this->adminLogService->registrar($adminId, 'Crear habit', 'Habit ID '.$habit->id.': '.$habit->titol, null, $despres, null);
            $this->enviarFeedback($adminId, 'habit', 'CREATE', true, $habit->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $habit = Habit::find($id);
            if ($habit === null) {
                throw new \RuntimeException('Habit no trobat');
            }
            $abans = $habit->toArray();
            $habit->titol = $data['titol'] ?? $habit->titol;
            $habit->dificultat = $data['dificultat'] ?? $habit->dificultat;
            $habit->frequencia_tipus = $data['frequencia_tipus'] ?? $habit->frequencia_tipus;
            $habit->dies_setmana = $data['dies_setmana'] ?? $habit->dies_setmana;
            $habit->objectiu_vegades = $data['objectiu_vegades'] ?? $habit->objectiu_vegades;
            $habit->save();
            $despres = $habit->toArray();
            $this->adminLogService->registrar($adminId, 'Editar habit', 'Habit ID '.$id.': '.$habit->titol, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'habit', 'UPDATE', true, $habit->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            $habit = Habit::find($id);
            if ($habit === null) {
                throw new \RuntimeException('Habit no trobat');
            }
            $abans = $habit->toArray();
            $habit->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar habit', 'Habit ID '.$id, $abans, null, null);
            $this->enviarFeedback($adminId, 'habit', 'DELETE', true, ['id' => $id]);
        }
    }

    private function processarLogro(int $adminId, string $action, array $data): void
    {
        if ($action === 'CREATE') {
            $logro = LogroMedalla::create([
                'nom' => $data['nom'] ?? '',
                'descripcio' => $data['descripcio'] ?? null,
                'tipus' => $data['tipus'] ?? null,
            ]);
            $despres = $logro->toArray();
            $this->adminLogService->registrar($adminId, 'Crear logro', 'Logro ID '.$logro->id.': '.$logro->nom, null, $despres, null);
            $this->enviarFeedback($adminId, 'logro', 'CREATE', true, $logro->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $logro = LogroMedalla::find($id);
            if ($logro === null) {
                throw new \RuntimeException('Logro no trobat');
            }
            $abans = $logro->toArray();
            $logro->nom = $data['nom'] ?? $logro->nom;
            $logro->descripcio = $data['descripcio'] ?? $logro->descripcio;
            $logro->tipus = $data['tipus'] ?? $logro->tipus;
            $logro->save();
            $despres = $logro->toArray();
            $this->adminLogService->registrar($adminId, 'Editar logro', 'Logro ID '.$id.': '.$logro->nom, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'logro', 'UPDATE', true, $logro->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            $logro = LogroMedalla::find($id);
            if ($logro === null) {
                throw new \RuntimeException('Logro no trobat');
            }
            $abans = $logro->toArray();
            $logro->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar logro', 'Logro ID '.$id, $abans, null, null);
            $this->enviarFeedback($adminId, 'logro', 'DELETE', true, ['id' => $id]);
        }
    }

    private function processarMissio(int $adminId, string $action, array $data): void
    {
        if ($action === 'CREATE') {
            $missio = MissioDiaria::create([
                'titol' => $data['titol'] ?? '',
            ]);
            $despres = $missio->toArray();
            $this->adminLogService->registrar($adminId, 'Crear missio', 'Missio ID '.$missio->id.': '.$missio->titol, null, $despres, null);
            $this->enviarFeedback($adminId, 'missio', 'CREATE', true, $missio->toArray());
        } elseif ($action === 'UPDATE') {
            $id = $data['id'] ?? 0;
            $missio = MissioDiaria::find($id);
            if ($missio === null) {
                throw new \RuntimeException('Missio no trobada');
            }
            $abans = $missio->toArray();
            $missio->titol = $data['titol'] ?? $missio->titol;
            $missio->save();
            $despres = $missio->toArray();
            $this->adminLogService->registrar($adminId, 'Editar missio', 'Missio ID '.$id.': '.$missio->titol, $abans, $despres, null);
            $this->enviarFeedback($adminId, 'missio', 'UPDATE', true, $missio->toArray());
        } elseif ($action === 'DELETE') {
            $id = $data['id'] ?? 0;
            $missio = MissioDiaria::find($id);
            if ($missio === null) {
                throw new \RuntimeException('Missio no trobada');
            }
            $abans = $missio->toArray();
            $missio->delete();
            $this->adminLogService->registrar($adminId, 'Eliminar missio', 'Missio ID '.$id, $abans, null, null);
            $this->enviarFeedback($adminId, 'missio', 'DELETE', true, ['id' => $id]);
        }
    }

    private function enviarFeedback(int $adminId, string $entity, string $action, bool $success, array $data): void
    {
        $this->feedbackService->publicarPayload([
            'admin_id' => $adminId,
            'entity' => $entity,
            'action' => $action,
            'success' => $success,
            'data' => $data,
        ]);
    }
}
