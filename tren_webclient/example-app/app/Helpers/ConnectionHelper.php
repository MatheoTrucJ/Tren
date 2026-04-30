<?php

namespace App\Helpers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ConnectionHelper
{
    private string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $configuredBaseUrl = $baseUrl ?? config('services.tren_api.url');

        if (! is_string($configuredBaseUrl) || trim($configuredBaseUrl) === '') {
            throw new RuntimeException('TREN_API_URL is not configured.');
        }

        $this->baseUrl = rtrim($configuredBaseUrl, '/');
    }

    public function get(string $path, array $query = []): Response
    {
        return Http::get($this->buildUrl($path), $query);
    }

    public function post(string $path, array $data = []): Response
    {
        return Http::post($this->buildUrl($path), $data);
    }

    private function buildUrl(string $path): string
    {
        return $this->baseUrl.'/'.ltrim($path, '/');
    }
}
