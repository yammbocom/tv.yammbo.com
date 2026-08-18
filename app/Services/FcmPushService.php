<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * FCM HTTP v1 API client for sending push notifications to topics.
 *
 * Uses a service account JSON to mint OAuth2 access tokens (JWT bearer flow).
 * Token is cached for 50 minutes (Google issues 1h tokens).
 */
class FcmPushService
{
    private array $serviceAccount;

    public function __construct()
    {
        $path = config('services.firebase.sa_path');
        if (! $path || ! is_readable($path)) {
            throw new RuntimeException("Firebase service account not readable at: {$path}");
        }
        $json = file_get_contents($path);
        $sa = json_decode($json, true);
        if (! is_array($sa) || empty($sa['private_key']) || empty($sa['client_email']) || empty($sa['project_id'])) {
            throw new RuntimeException('Invalid Firebase service account JSON');
        }
        $this->serviceAccount = $sa;
    }

    /**
     * Send a notification to an FCM topic.
     *
     * @param  array{title:string, body:string, click_url?:?string, image?:?string}  $payload
     * @return array{success:bool, message_id?:string, error?:string, response:array}
     */
    public function sendToTopic(string $topic, array $payload): array
    {
        $token = $this->getAccessToken();
        $projectId = $this->serviceAccount['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $payload['title'],
                'body' => $payload['body'],
            ],
            'android' => [
                'priority' => 'HIGH',
                'notification' => [
                    'icon' => 'ic_notification',
                    'sound' => 'default',
                    'channel_id' => 'yammbo_news',
                ],
            ],
        ];

        if (! empty($payload['click_url'])) {
            $message['data'] = ['click_url' => (string) $payload['click_url']];
            $message['android']['notification']['click_action'] = 'OPEN_URL';
        }
        if (! empty($payload['image'])) {
            $message['notification']['image'] = (string) $payload['image'];
            $message['android']['notification']['image'] = (string) $payload['image'];
        }

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(15)
            ->post($url, ['message' => $message]);

        $body = $response->json() ?? [];
        if ($response->successful() && ! empty($body['name'])) {
            return [
                'success' => true,
                'message_id' => $body['name'],
                'response' => $body,
            ];
        }

        Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $body]);

        return [
            'success' => false,
            'error' => $body['error']['message'] ?? "HTTP {$response->status()}",
            'response' => $body,
        ];
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'fcm_access_token:'.md5($this->serviceAccount['client_email']);

        return Cache::remember($cacheKey, 3000, function () {
            return $this->mintAccessToken();
        });
    }

    private function mintAccessToken(): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $segments = [
            $this->b64url(json_encode($header)),
            $this->b64url(json_encode($claims)),
        ];
        $signingInput = implode('.', $segments);

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $this->serviceAccount['private_key'], OPENSSL_ALGO_SHA256);
        if (! $ok) {
            throw new RuntimeException('Failed to sign JWT for Firebase');
        }
        $jwt = $signingInput.'.'.$this->b64url($signature);

        $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful() || empty($response->json('access_token'))) {
            throw new RuntimeException('Firebase token exchange failed: '.$response->body());
        }

        return $response->json('access_token');
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
