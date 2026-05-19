<?php

declare(strict_types=1);

namespace App\Domains\Calendar\Support;

/**
 * Camps de cosmètics dins mascota_json del snapshot diari.
 */
final class SnapshotCosmetics
{
    /**
     * @return array{skin_key: ?string, fons_key: ?string, te_gorra: bool, te_fons: bool}
     */
    public static function build(?string $skinKey, ?string $fonsKey): array
    {
        $skinKey = self::normalitzarClau($skinKey);
        $fonsKey = self::normalitzarClau($fonsKey);

        return [
            'skin_key' => $skinKey,
            'fons_key' => $fonsKey,
            'te_gorra' => $skinKey !== null,
            'te_fons' => $fonsKey !== null,
        ];
    }

    /**
     * Llegeix cosmètics d'un mascota_json (compatible amb snapshots antics).
     *
     * @param array<string, mixed>|null $mascotaJson
     * @return array{skin_key: ?string, fons_key: ?string, te_gorra: bool, te_fons: bool}
     */
    public static function fromMascotaJson(?array $mascotaJson): array
    {
        if ($mascotaJson === null) {
            return self::build(null, null);
        }

        $skinKey = self::normalitzarClau(isset($mascotaJson['skin_key']) ? (string) $mascotaJson['skin_key'] : null);
        $fonsKey = self::normalitzarClau(isset($mascotaJson['fons_key']) ? (string) $mascotaJson['fons_key'] : null);

        $teGorra = array_key_exists('te_gorra', $mascotaJson)
            ? (bool) $mascotaJson['te_gorra']
            : $skinKey !== null;
        $teFons = array_key_exists('te_fons', $mascotaJson)
            ? (bool) $mascotaJson['te_fons']
            : $fonsKey !== null;

        return [
            'skin_key' => $skinKey,
            'fons_key' => $fonsKey,
            'te_gorra' => $teGorra,
            'te_fons' => $teFons,
        ];
    }

    private static function normalitzarClau(?string $clau): ?string
    {
        if ($clau === null || trim($clau) === '') {
            return null;
        }

        return trim($clau);
    }
}
