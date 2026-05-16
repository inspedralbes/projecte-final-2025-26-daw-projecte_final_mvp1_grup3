<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonsterChoiceController extends Controller
{
    private const VALID_TYPES = ['VV', 'VR', 'VL', 'VA'];
    private const STAGE_THRESHOLDS = [
        'B' => 5,
        'N' => 15,
        'A' => 30,
        'M' => PHP_INT_MAX,
    ];

    public function store(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuari = User::find($userId);
        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        if ($usuari->monstre_tipus !== null) {
            return response()->json([
                'error' => 'Ja tens un monstre assignat',
                'monstre' => $this->getMonsterData($usuari),
            ], 409);
        }

        $tipus = $request->input('monstre_tipus');
        if (!in_array($tipus, self::VALID_TYPES, true)) {
            return response()->json([
                'error' => 'Tipus de monstre invàlid. Valors permesos: VV, VR, VL, VA',
            ], 422);
        }

        $usuari->monstre_tipus = $tipus;
        $usuari->data_naixement_monstre = Carbon::now();
        $usuari->save();

        return response()->json([
            'success' => true,
            'monstre' => $this->getMonsterData($usuari),
        ], 201);
    }

    public function show(Request $request): JsonResponse
    {
        $userId = (int) ($request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $usuari = User::find($userId);
        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        if ($usuari->monstre_tipus === null) {
            return response()->json([
                'error' => 'L\'usuari no té un monstre assignat',
                'has_monster' => false,
            ], 404);
        }

        return response()->json([
            'monstre' => $this->getMonsterData($usuari),
        ]);
    }

    private function getMonsterData(User $usuari): array
    {
        $tipus = $usuari->monstre_tipus;
        $nivell = (int) $usuari->nivell;
        $etapa = $this->getEtapa($nivell);
        $sprite = $this->getSpriteName($tipus, $etapa);

        return [
            'tipus' => $tipus,
            'etapa' => $etapa,
            'nivell' => $nivell,
            'sprite' => $sprite,
            'data_naixement' => $usuari->data_naixement_monstre?->toIso8601String(),
        ];
    }

    private function getEtapa(int $nivell): string
    {
        if ($nivell <= self::STAGE_THRESHOLDS['B']) {
            return 'B';
        }
        if ($nivell <= self::STAGE_THRESHOLDS['N']) {
            return 'N';
        }
        if ($nivell <= self::STAGE_THRESHOLDS['A']) {
            return 'A';
        }
        return 'M';
    }

    private function getSpriteName(string $tipus, string $etapa): string
    {
        $colorCode = substr($tipus, 0, 1);
        return 'M' . $colorCode . $etapa . '.png';
    }

    public static function calculateStage(int $nivel): string
    {
        if ($nivel <= 5) {
            return 'B';
        }
        if ($nivel <= 15) {
            return 'N';
        }
        if ($nivel <= 30) {
            return 'A';
        }
        return 'M';
    }

    public static function calculateSpriteName(?string $tipus, int $nivel): ?string
    {
        if ($tipus === null) {
            return null;
        }

        $colorCode = substr($tipus, 0, 1);
        $etapa = self::calculateStage($nivel);

        return 'M' . $colorCode . $etapa . '.png';
    }
}