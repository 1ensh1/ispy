<?php

namespace App\Services;

class SupabaseStorageService
{
    public function uploadImage(string $imageData, string $filename): ?string
    {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $key     = config('services.supabase.service_role_key');
        $url     = $baseUrl . '/storage/v1/object/vocabulary/' . $filename;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $imageData,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $key,
                'Content-Type: image/jpeg',
                'Content-Length: ' . strlen($imageData),
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return $baseUrl . '/storage/v1/object/public/vocabulary/' . $filename;
    }
}
