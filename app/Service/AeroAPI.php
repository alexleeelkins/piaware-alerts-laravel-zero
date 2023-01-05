<?php

namespace App\Service;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class AeroAPI
{
    protected const BASE_ENDPOINT = 'https://aeroapi.flightaware.com/aeroapi';

    public function __construct(protected string $aeroApiKey)
    {
    }

    public function getFlight(string $flightNumber): ?array
    {
        $response = Http::withHeaders(['x-apikey' => $this->aeroApiKey,])
                        ->get(sprintf('%s/flights/%s', self::BASE_ENDPOINT, $flightNumber));

        if ($response->failed()) {
            return null;
        }

        if (count(Arr::get($response->json(), 'flights') ?? []) < 1) {
            return null;
        }

        foreach (Arr::get($response->json(), 'flights') as $flight) {
            if (Arr::get($flight, 'blocked') ?? false) {
                continue;
            }

            if (Arr::get($flight, 'position_only') ?? false) {
                if (Arr::get($flight, 'status') !== 'En Route') {
                    continue;
                }
            } elseif (Arr::get($flight, 'progress_percent') === 0 || Arr::get($flight, 'progress_percent') === 100) {
                continue;
            }

            return $flight;
        }

        return null;
    }

    public function getOperator(string $operatorCode): ?array
    {
        $response = Http::withHeaders(['x-apikey' => $this->aeroApiKey,])
                        ->get(sprintf('%s/operators/%s', self::BASE_ENDPOINT, $operatorCode));

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    public function getAircraftType(string $aircraftCode): ?array
    {
        $response = Http::withHeaders(['x-apikey' => $this->aeroApiKey,])
                        ->get(sprintf('%s/aircraft/types/%s', self::BASE_ENDPOINT, $aircraftCode));

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }
}
