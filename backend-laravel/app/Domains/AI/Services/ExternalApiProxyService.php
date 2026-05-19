<?php

namespace App\Domains\AI\Services;

/**
 * Fachada fina: delega als clients d'API externa per domini.
 */
class ExternalApiProxyService
{
    public function __construct(
        private GoogleBooksSearchService $googleBooks,
        private WgerExerciseSearchService $wgerSearch,
        private WgerExerciseDetailService $wgerDetail,
        private OpenFoodFactsSearchService $openFoodFacts,
        private YoutubeVideoSearchService $youtube,
        private OpenWeatherContextService $openWeather
    ) {}

    /** @return array<string, mixed> */
    public function cercarLlibres(string $query): array
    {
        return $this->googleBooks->cercar($query);
    }

    /** @return array<string, mixed> */
    public function cercarRutines(string $query): array
    {
        return $this->wgerSearch->cercar($query);
    }

    /** @return array<string, mixed> */
    public function obtenirDetallExercici(string $exerciseId): array
    {
        return $this->wgerDetail->obtenirDetall($exerciseId);
    }

    /** @return array<string, mixed> */
    public function cercarAliments(string $query): array
    {
        return $this->openFoodFacts->cercar($query);
    }

    /** @return array<string, mixed> */
    public function cercarVideos(string $query): array
    {
        return $this->youtube->cercar($query);
    }

    /** @return array<string, mixed> */
    public function obtenirContextClima(?string $city, ?float $lat = null, ?float $lon = null): array
    {
        return $this->openWeather->obtenirContext($city, $lat, $lon);
    }
}
