<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Habits\Services\HabitService;
use App\Domains\User\Actions\UpdateShowcaseAction;
use App\Domains\User\Queries\GetPublicProfileQuery;
use App\Domains\User\Services\UserAccountUpdateService;
use App\Http\Controllers\Controller;
use App\Http\Resources\HabitProgressLogResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserProfileController (thin).
 */
class UserProfileController extends Controller
{
    private GetPublicProfileQuery $getPublicProfileQuery;

    private UpdateShowcaseAction $updateShowcaseAction;

    public function __construct(
        GetPublicProfileQuery $getPublicProfileQuery,
        UpdateShowcaseAction $updateShowcaseAction
    ) {
        $this->getPublicProfileQuery = $getPublicProfileQuery;
        $this->updateShowcaseAction = $updateShowcaseAction;
    }

    public function getPublicProfile(int $id): JsonResponse
    {
        return response()->json($this->getPublicProfileQuery->executar($id));
    }

    public function getPublicLogs(int $id, HabitService $habitService): JsonResponse
    {
        User::findOrFail($id);
        $resultat = $habitService->obtenirLogsHistorics($id);

        return (new HabitProgressLogResource($resultat))->toResponse(request());
    }

    public function getSelfProfile(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        return $this->getPublicProfile($user->id);
    }

    public function updateAccount(Request $request, UserAccountUpdateService $userAccountUpdateService): JsonResponse
    {
        $userId = (int) $request->user_id;
        $nom = (string) $request->input('nom', '');
        $email = (string) $request->input('email', '');
        $password = $request->input('password');
        if (! is_string($password)) {
            $password = null;
        }

        $resultat = $userAccountUpdateService->actualitzarPerUsuariId($userId, $nom, $email, $password);

        if (! $resultat['ok']) {
            return response()->json([
                'success' => false,
                'errors' => $resultat['errors'],
            ], 422);
        }

        $u = $resultat['user'];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $u->id,
                'nom' => $u->nom,
                'email' => $u->email,
            ],
        ]);
    }

    public function updateShowcase(Request $request): JsonResponse
    {
        $resultat = $this->updateShowcaseAction->executar($request);

        return response()->json($resultat['body'], $resultat['status']);
    }
}
