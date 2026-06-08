<?php

namespace App\Services;

use Exception;

class GoogleTTSService
{
    /**
     * Synthesize speech for the given text and return raw MP3 audio bytes.
     */
    public function synthesize(string $text, string $language, string $voice): string
    {
        $token = $this->getAccessToken();

        $payload = json_encode([
            'input'       => ['text' => $text],
            'voice'       => [
                'languageCode' => $language,
                'name'         => $voice,
            ],
            'audioConfig' => [
                'audioEncoding'   => 'MP3',
                'sampleRateHertz' => 24000,
            ],
        ]);

        $ch = curl_init('https://texttospeech.googleapis.com/v1/text:synthesize');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json; charset=utf-8',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('Google TTS request failed: ' . $curlErr);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $message = $data['error']['message'] ?? 'Unknown error from Google TTS API.';
            throw new Exception('Google TTS API error: ' . $message);
        }

        if (empty($data['audioContent'])) {
            throw new Exception('Google TTS API returned no audio content.');
        }

        $binary = base64_decode($data['audioContent'], true);

        if ($binary === false) {
            throw new Exception('Failed to decode audio content from Google TTS API.');
        }

        return $binary;
    }

    /**
     * Generate a short-lived OAuth2 access token from the service account JWT.
     */
    protected function getAccessToken(): string
    {
        $credentials = $this->loadCredentials();

        $now   = time();
        $scope = 'https://www.googleapis.com/auth/cloud-platform';

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $claims = [
            'iss'   => $credentials['client_email'],
            'scope' => $scope,
            'aud'   => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];

        $signingInput = implode('.', $segments);

        $signature = '';
        $success   = openssl_sign($signingInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

        if (! $success) {
            throw new Exception('Failed to sign JWT with the service account private key.');
        }

        $segments[] = $this->base64UrlEncode($signature);
        $jwt        = implode('.', $segments);

        $ch = curl_init($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('OAuth token request failed: ' . $curlErr);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            $message = $data['error_description'] ?? ($data['error'] ?? 'Unknown error obtaining access token.');
            throw new Exception('Google OAuth error: ' . $message);
        }

        return $data['access_token'];
    }

    /**
     * Read and decode the service account JSON credentials file.
     */
    protected function loadCredentials(): array
    {
        $path = config('services.google_tts.credentials');

        if (! $path || ! file_exists($path)) {
            throw new Exception('Google service account credentials file not found.');
        }

        $contents = file_get_contents($path);
        $json     = json_decode($contents, true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new Exception('Google service account credentials file is invalid.');
        }

        return $json;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
