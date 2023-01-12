<?php

namespace App\Service;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class HexDB
{
    protected const BASE_ENDPOINT = 'https://hexdb.io/api/v1';

    public function getRegistrationCode(?string $hex): ?string
    {
        $response = Http::get(sprintf('%s/aircraft/%s', self::BASE_ENDPOINT, $hex));

        if ($response->failed()) {
            return null;
        }

        return Arr::get($response->json(), 'Registration');
    }
}
