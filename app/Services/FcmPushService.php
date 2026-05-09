<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FcmPushService
{
    private ?string $cachedAccessToken = null;

    private ?Carbon $cachedAccessTokenExpiresAt = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        if (! $user->fcm_token) {
            return;
        }

        try {
            $accessToken = $this->getAccessToken();
            $endpoint = $this->messageEndpoint();
        } catch (RuntimeException) {
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'message' => [
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->normalizeData($data),
            ],
        ]);

        if (! $response->successful()) {
            Log::warning('FCM push delivery failed.', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = is_scalar($value) || $value === null
                ? (string) ($value ?? '')
                : json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $normalized;
    }

    private function messageEndpoint(): string
    {
        $projectId = config('fcm.project_id');

        if (! is_string($projectId) || $projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        return "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    }

    private function getAccessToken(): string
    {
        if ($this->cachedAccessToken !== null && $this->cachedAccessTokenExpiresAt?->isFuture()) {
            return $this->cachedAccessToken;
        }

        $clientEmail = config('fcm.client_email');
        $privateKey = config('fcm.private_key');
        $tokenUri = config('fcm.oauth_token_uri');
        $scope = config('fcm.scope');

        if (! is_string($clientEmail) || $clientEmail === '') {
            throw new RuntimeException('FIREBASE_CLIENT_EMAIL is not configured.');
        }

        if (! is_string($privateKey) || $privateKey === '') {
            throw new RuntimeException('FIREBASE_PRIVATE_KEY is not configured.');
        }

        if (! is_string($tokenUri) || $tokenUri === '') {
            throw new RuntimeException('FIREBASE_OAUTH_TOKEN_URI is not configured.');
        }

        if (! is_string($scope) || $scope === '') {
            throw new RuntimeException('FIREBASE_OAUTH_SCOPE is not configured.');
        }

        $issuedAt = time();
        $expiresAt = $issuedAt + 3600;
        $normalizedPrivateKey = str_replace('\n', "\n", $privateKey);
        $jwt = $this->buildSignedJwt(
            clientEmail: $clientEmail,
            privateKey: $normalizedPrivateKey,
            scope: $scope,
            tokenUri: $tokenUri,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
        );

        $response = Http::asForm()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch Firebase OAuth token.');
        }

        $accessToken = $response->json('access_token');
        $ttlSeconds = (int) ($response->json('expires_in') ?? 3600);

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Firebase OAuth response is missing access_token.');
        }

        $this->cachedAccessToken = $accessToken;
        $this->cachedAccessTokenExpiresAt = now()->addSeconds(max(60, $ttlSeconds - 60));

        return $accessToken;
    }

    private function buildSignedJwt(
        string $clientEmail,
        string $privateKey,
        string $scope,
        string $tokenUri,
        int $issuedAt,
        int $expiresAt,
    ): string {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => $scope,
            'aud' => $tokenUri,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ], JSON_THROW_ON_ERROR));

        $signatureInput = "{$header}.{$payload}";
        $signature = '';
        $signed = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Unable to sign Firebase service account JWT.');
        }

        return "{$signatureInput}.{$this->base64UrlEncode($signature)}";
    }

    private function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
