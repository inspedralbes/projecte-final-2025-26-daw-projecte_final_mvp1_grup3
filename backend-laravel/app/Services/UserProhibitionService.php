<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Calcula l'estat d'un ban d'usuari (durada, dies restants, motiu).
 */
class UserProhibitionService
{
  /** @var int Marca ban permanent a la columna dies_prohibicio */
    public const DIES_PERMANENT = -1;

    /**
     * Omple els camps de prohibició (cal cridar save() després).
     */
    public static function omplirProhibicio(User $usuari, ?string $motiu, ?string $duradaKey): void
    {
        $usuari->prohibit = true;
        $usuari->data_prohibicio = now();
        $usuari->dies_prohibicio = self::diesDesDeDuradaKey($duradaKey);
        $usuari->motiu_prohibicio = self::normalitzarMotiuProhibicio($motiu, $duradaKey);
    }

    /**
     * Converteix la clau del formulari admin (7_dies, permanent...) a dies o -1 permanent.
     */
    public static function diesDesDeDuradaKey(?string $duradaKey): ?int
    {
        if ($duradaKey === null || trim($duradaKey) === '') {
            return null;
        }
        $clau = strtolower(trim($duradaKey));
        if (str_contains($clau, 'permanent')) {
            return self::DIES_PERMANENT;
        }

        return (new self())->diesDesDeClau($clau);
    }

    /**
     * Normalitza el motiu amb etiqueta de durada llegible i tag machine-readable [durada:X].
     */
    public static function normalitzarMotiuProhibicio(?string $motiu, ?string $duradaKey = null): string
    {
        $text = trim((string) ($motiu ?? ''));
        $text = trim(preg_replace('/^\[[^\]]+\]\s*/', '', $text) ?? $text);
        $text = trim(preg_replace('/\[durada:[^\]]+\]\s*/i', '', $text) ?? $text);

        if ($text === '') {
            $text = 'Violació de les normes de la comunitat';
        }

        $clau = $duradaKey ? strtolower(trim($duradaKey)) : null;
        if ($clau === null && $motiu !== null && preg_match('/\[durada:([^\]]+)\]/i', $motiu, $tag)) {
            $clau = strtolower($tag[1]);
        }
        if ($clau === null && $motiu !== null && preg_match('/^\[([^\]]+)\]/', $motiu, $etiqueta)) {
            $clau = strtolower($etiqueta[1]);
        }

        if ($clau === null) {
            return $text;
        }

        if (str_contains($clau, 'permanent')) {
            return '[durada:permanent] [Permanent] ' . $text;
        }

        $dies = (new self())->diesDesDeClau($clau);
        if ($dies === null) {
            return $text;
        }

        $etiquetaLegible = (new self())->etiquetaLegibleDesDeDies($dies);

        return '[durada:' . $clau . '] [' . $etiquetaLegible . '] ' . $text;
    }

    /**
     * Retorna null si no està prohibit o el ban ha expirat (i el desactiva).
     *
     * @return array<string, mixed>|null
     */
    public function evaluarProhibicio(User $usuari): ?array
    {
        if (empty($usuari->prohibit)) {
            return null;
        }

        $motiuComplet = (string) ($usuari->motiu_prohibicio ?? '');
        $motiuText = $this->extreureMotiuSenseDurada($motiuComplet);

        $diesBan = null;
        if ($usuari->dies_prohibicio === self::DIES_PERMANENT) {
            return [
                'permanent' => true,
                'dies_restant' => null,
                'dies_total' => null,
                'motiu' => $motiuText,
            ];
        }
        if (is_numeric($usuari->dies_prohibicio) && (int) $usuari->dies_prohibicio > 0) {
            $diesBan = (int) $usuari->dies_prohibicio;
        }

        if ($diesBan === null && $this->esPermanent($motiuComplet)) {
            return [
                'permanent' => true,
                'dies_restant' => null,
                'dies_total' => null,
                'motiu' => $motiuText,
            ];
        }

        if ($diesBan === null) {
            $diesBan = $this->parseDiesDurada($motiuComplet);
        }

        if ($diesBan === null) {
            return [
                'permanent' => false,
                'dies_restant' => null,
                'dies_total' => null,
                'durada_desconeguda' => true,
                'motiu' => $motiuText,
            ];
        }

        $inici = $usuari->data_prohibicio
            ? Carbon::parse($usuari->data_prohibicio)->startOfDay()
            : now()->startOfDay();

        $diesTranscorreguts = (int) $inici->diffInDays(now()->startOfDay());
        $diesRestants = max(0, $diesBan - $diesTranscorreguts);

        if ($diesRestants <= 0) {
            $this->levantarProhibicio($usuari);

            return null;
        }

        return [
            'permanent' => false,
            'dies_restant' => $diesRestants,
            'dies_total' => $diesBan,
            'motiu' => $motiuText,
        ];
    }

    public function levantarProhibicio(User $usuari): void
    {
        $usuari->update([
            'prohibit' => false,
            'data_prohibicio' => null,
            'motiu_prohibicio' => null,
            'dies_prohibicio' => null,
        ]);
    }

    private function esPermanent(string $motiu): bool
    {
        if (preg_match('/\[durada:([^\]]+)\]/i', $motiu, $tag)) {
            return str_contains(strtolower($tag[1]), 'permanent');
        }

        if (preg_match_all('/\[([^\]]+)\]/', $motiu, $coincidencies) && !empty($coincidencies[1])) {
            foreach ($coincidencies[1] as $etiqueta) {
                if (stripos($etiqueta, 'permanent') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function parseDiesDurada(string $motiu): ?int
    {
        if (preg_match('/\[durada:([^\]]+)\]/i', $motiu, $tag)) {
            $dies = $this->diesDesDeClau(strtolower($tag[1]));
            if ($dies !== null) {
                return $dies;
            }
        }

        if (preg_match_all('/\[([^\]]+)\]/', $motiu, $coincidencies) && !empty($coincidencies[1])) {
            foreach ($coincidencies[1] as $etiqueta) {
                if (stripos($etiqueta, 'permanent') !== false) {
                    return null;
                }
                $dies = $this->diesDesDeClau(strtolower($etiqueta));
                if ($dies !== null) {
                    return $dies;
                }
            }
        }

        return null;
    }

    private function diesDesDeClau(string $clau): ?int
    {
        if (str_contains($clau, 'permanent')) {
            return null;
        }
        if (str_contains($clau, '30') || str_contains($clau, 'mes')) {
            return 30;
        }
        if (str_contains($clau, '7') || str_contains($clau, 'setmana')) {
            return 7;
        }
        if (str_contains($clau, '3_dies') || preg_match('/^3[\s_]|3\s+dies/', $clau)) {
            return 3;
        }
        if (str_contains($clau, '1_dia') || preg_match('/^1[\s_]|1\s+dia/', $clau)) {
            return 1;
        }

        return null;
    }

    private function etiquetaLegibleDesDeDies(int $dies): string
    {
        return match ($dies) {
            1 => '1 Dia',
            3 => '3 Dies',
            7 => '7 Dies',
            30 => '30 Dies',
            default => $dies . ' Dies',
        };
    }

    private function extreureMotiuSenseDurada(string $motiu): string
    {
        $text = $motiu;
        $text = preg_replace('/\[durada:[^\]]+\]\s*/i', '', $text) ?? $text;
        while (preg_match('/^\[[^\]]+\]\s*/', $text)) {
            $text = preg_replace('/^\[[^\]]+\]\s*/', '', $text) ?? $text;
        }
        $text = trim($text);

        return $text !== '' ? $text : 'Violació de les normes de la comunitat';
    }
}
