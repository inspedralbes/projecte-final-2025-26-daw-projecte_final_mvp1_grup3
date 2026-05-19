<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Onboarding\Actions\AssignOnboardingHabitsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OnboardingHabitAssignController (thin).
 */
class OnboardingHabitAssignController extends Controller
{
    private AssignOnboardingHabitsAction $assignOnboardingHabitsAction;

    public function __construct(AssignOnboardingHabitsAction $assignOnboardingHabitsAction)
    {
        $this->assignOnboardingHabitsAction = $assignOnboardingHabitsAction;
    }

    public function assign(Request $request): JsonResponse
    {
        $resultat = $this->assignOnboardingHabitsAction->executar($request);

        return response()->json($resultat['body'], $resultat['status']);
    }
}
