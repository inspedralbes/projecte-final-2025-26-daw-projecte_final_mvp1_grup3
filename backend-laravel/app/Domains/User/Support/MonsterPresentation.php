<?php

declare(strict_types=1);

namespace App\Domains\User\Support;

use App\Models\User;
use Carbon\Carbon;

/**
 * Càlcul d'etapa i sprite del monstre segons nivell i tipus.
 */
class MonsterPresentation
{
    private const VALID_TYPES = ['VV', 'VR', 'VL', 'VA'];

    private const STAGE_THRESHOLDS = [
        'B' => 5,
        'N' => 15,
        'A' => 30,
        'M' => PHP_INT_MAX,
    ];

    /**
     * @return array<string, mixed>
     */
    public function monsterDataFromUser(User $usuari): array
    {
        $tipus = (string) $usuari->monstre_tipus;
        $nivell = (int) $usuari->nivell;
        $etapa = $this->etapaPerNivell($nivell);

        return [
            'tipus' => $tipus,
            'etapa' => $etapa,
            'nivell' => $nivell,
            'sprite' => $this->spriteName($tipus, $etapa),
            'data_naixement' => $this->formatDataNaixement($usuari),
        ];
    }

    private function formatDataNaixement(User $usuari): ?string
    {
        $data = $usuari->data_naixement_monstre;
        if ($data === null) {
            return null;
        }
        if ($data instanceof \DateTimeInterface) {
            return $data->format(\DateTimeInterface::ATOM);
        }
        if (is_string($data) && $data !== '') {
            return Carbon::parse($data)->toIso8601String();
        }

        return null;
    }

    public function esTipusValid(?string $tipus): bool
    {
        return $tipus !== null && in_array($tipus, self::VALID_TYPES, true);
    }

    public function etapaPerNivell(int $nivell): string
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

    public function spriteName(string $tipus, string $etapa): string
    {
        $colorCode = substr($tipus, 0, 1);

        return 'M' . $colorCode . $etapa . '.png';
    }

    public static function calculateStage(int $nivel): string
    {
        return (new self())->etapaPerNivell($nivel);
    }

    public static function calculateSpriteName(?string $tipus, int $nivel): ?string
    {
        if ($tipus === null) {
            return null;
        }

        $self = new self();

        return $self->spriteName($tipus, $self->etapaPerNivell($nivel));
    }
}
