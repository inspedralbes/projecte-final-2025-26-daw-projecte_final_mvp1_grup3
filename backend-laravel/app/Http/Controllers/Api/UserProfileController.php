<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function getPublicProfile(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $response = [
            'id' => $user->id,
            'nom' => $user->nom,
            'nivell' => $user->nivell,
            'xp_total' => $user->xp_total,
            'streak' => $user->xp_actual_nivel,
        ];

        return response()->json($response);
    }

    public function getSelfProfile(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        return $this->getPublicProfile($user->id);
    }
}