<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Services\ExternalApiProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador proxy de recursos externs.
 */
class ExternalResourceController extends Controller
{
    private ExternalApiProxyService $externalApiProxyService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(ExternalApiProxyService $externalApiProxyService)
    {
        $this->externalApiProxyService = $externalApiProxyService;
    }

    public function books(Request $request): JsonResponse
    {
        $validat = $request->validate([
            'q' => 'required|string|min:2|max:120',
        ]);

        $resultat = $this->externalApiProxyService->cercarLlibres((string) $validat['q']);

        if ($resultat['ok'] !== true) {
            return response()->json([
                'ok' => false,
                'error' => $resultat['error'],
                'items' => [],
            ], 502);
        }

        return response()->json($resultat);
    }

    public function workouts(Request $request): JsonResponse
    {
        $validat = $request->validate([
            'q' => 'required|string|min:2|max:120',
        ]);

        $resultat = $this->externalApiProxyService->cercarRutines((string) $validat['q']);

        if ($resultat['ok'] !== true) {
            return response()->json([
                'ok' => false,
                'error' => $resultat['error'],
                'items' => [],
            ], 502);
        }

        return response()->json($resultat);
    }

    public function nutrition(Request $request): JsonResponse
    {
        $validat = $request->validate([
            'q' => 'required|string|min:2|max:120',
        ]);

        $resultat = $this->externalApiProxyService->cercarAliments((string) $validat['q']);

        if ($resultat['ok'] !== true) {
            return response()->json([
                'ok' => false,
                'error' => $resultat['error'],
                'items' => [],
            ], 502);
        }

        return response()->json($resultat);
    }

    public function videos(Request $request): JsonResponse
    {
        $validat = $request->validate([
            'q' => 'required|string|min:2|max:120',
        ]);

        $resultat = $this->externalApiProxyService->cercarVideos((string) $validat['q']);

        if ($resultat['ok'] !== true) {
            return response()->json([
                'ok' => false,
                'error' => $resultat['error'],
                'items' => [],
            ], 502);
        }

        return response()->json($resultat);
    }

    public function exerciseDetail(Request $request, string $exerciseId): JsonResponse
    {
        if (!ctype_digit($exerciseId) || (int) $exerciseId <= 0) {
            return response()->json(['ok' => false, 'error' => 'ID d\'exercici invàlid'], 422);
        }

        $resultat = $this->externalApiProxyService->obtenirDetallExercici($exerciseId);

        if ($resultat['ok'] !== true) {
            return response()->json($resultat, 502);
        }

        return response()->json($resultat);
    }

    public function weather(Request $request): JsonResponse
    {
        $validat = $request->validate([
            'city' => 'nullable|string|min:2|max:80',
            'lat'  => 'nullable|numeric|between:-90,90',
            'lon'  => 'nullable|numeric|between:-180,180',
        ]);

        $lat  = isset($validat['lat'])  && $validat['lat']  !== null ? (float) $validat['lat']  : null;
        $lon  = isset($validat['lon'])  && $validat['lon']  !== null ? (float) $validat['lon']  : null;
        $city = isset($validat['city']) && $validat['city'] !== ''   ? (string) $validat['city'] : null;

        $resultat = $this->externalApiProxyService->obtenirContextClima($city, $lat, $lon);

        if ($resultat['ok'] !== true) {
            return response()->json($resultat, 502);
        }

        return response()->json($resultat);
    }
}
