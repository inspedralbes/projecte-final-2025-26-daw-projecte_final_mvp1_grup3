<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('search', '');
        $userId = $request->user_id ?? 0;

        if ($userId === 0) {
            return response()->json(['data' => [], 'error' => 'No authentificat'], 401);
        }

        if (strlen($query) < 2) {
            $users = User::where('id', '!=', $userId)
                ->select('id', 'nom', 'nivell', 'xp_total')
                ->limit(50)
                ->get();
        } else {
            $users = User::where('nom', 'ILIKE', '%' . $query . '%')
                ->where('id', '!=', $userId)
                ->select('id', 'nom', 'nivell', 'xp_total')
                ->limit(20)
                ->get();
        }

        return response()->json(['data' => $users]);
    }
}
