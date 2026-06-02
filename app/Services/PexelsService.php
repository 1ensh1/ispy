<?php

namespace App\Services;

class PexelsService
{
    public function fetchImage(string $query): ?string
    {
        $key = config('services.pexels.api_key');
        $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=1';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: {$key}"],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $error    = curl_errno($ch);
        curl_close($ch);

        if ($error || !$response) {
            return null;
        }

        $data = json_decode($response, true);

        return $data['photos'][0]['src']['medium'] ?? null;
    }
}
