<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Services\HabitService;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Importa hàbits d'una plantilla associada a un post social.
 */
class ImportPlantillaFromPostAction
{
    private HabitService $habitService;

    public function __construct(HabitService $habitService)
    {
        $this->habitService = $habitService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function executar(int $userId, array $validated, ?int $plantillaIdOverride): array
    {
        try {
            $postId = (int) $validated['post_id'];
            $habitIds = $validated['habit_ids'];

            $post = SocialPost::with('plantilla.habits')->findOrFail($postId);

            $targetPlantillaId = $plantillaIdOverride ?? $post->plantilla_id;

            if ($targetPlantillaId === null) {
                return [
                    'success' => false,
                    'message' => 'El post no té cap plantilla associada',
                    'status' => 422,
                ];
            }

            $result = $this->habitService->exportarHabitsDePlantilla(
                $userId,
                (int) $targetPlantillaId,
                $habitIds
            );

            return [
                'success' => true,
                'habits' => $result['habits'],
            ];
        } catch (\Throwable $e) {
            Log::error('Error important plantilla des de post: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error en importar plantilla',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}
