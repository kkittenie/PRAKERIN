<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Kirim notifikasi ke semua device milik satu member.
     *
     * @param  array<string>  $tokens  daftar FCM token milik member tsb
     * @param  array<string, string>  $data  payload tambahan (mis. url deep-link, transaksi_id)
     * @return array daftar token yang GAGAL/invalid (buat dibersihkan dari DB)
     */
    public function kirimKeTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokensGagal = [];

        if (empty($tokens)) {
            return $tokensGagal;
        }

        $accessToken = $this->ambilAccessToken();
        $projectId = config('services.firebase.project_id');

        if (! $accessToken || ! $projectId) {
            Log::warning('FCM belum dikonfigurasi (FIREBASE_PROJECT_ID / service account belum diisi), push notification dilewati.');
            return $tokensGagal;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($tokens as $token) {
            try {
                // Sengaja pakai 'notification' + 'data' bareng (BUKAN data-only).
                // Alasannya: kalau data-only, pas app member ke-KILL TOTAL (bukan
                // cuma minimize) -- apalagi di HP yang agresif battery-saver-nya
                // kaya MIUI/Xiaomi -- OS sering nggak mau "bangunin" app buat
                // jalanin onMessageReceived(), jadi notifnya nggak muncul sama
                // sekali. Dengan 'notification' di sini, Android System sendiri
                // yang nampilin notifnya (nggak butuh app-nya hidup), jadi selalu
                // muncul kaya WA. Titik yang perlu dijaga: pas notif ini di-tap
                // (bukan lewat tampilkanNotifikasi() custom di Kotlin, tapi lewat
                // intent bawaan OS), payload 'data' ini ikut ditaro jadi extras di
                // intent-nya pakai KEY ASLINYA ('url', bukan 'deep_link_url') --
                // makanya MainActivity.kt & SplashActivity.kt dibikin baca extra
                // "url" ini juga sebagai fallback.
                $response = Http::withToken($accessToken)->post($endpoint, [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_map('strval', $data),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'status_pesanan',
                            ],
                        ],
                    ],
                ]);

                if ($response->failed()) {
                    $errStatus = $response->json('error.status');
                    Log::warning('FCM gagal kirim ke token: ' . $errStatus . ' - ' . $response->body());

                    // Token sudah nggak valid (uninstall/logout) -> tandai buat dihapus
                    if (in_array($errStatus, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
                        $tokensGagal[] = $token;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('FCM error kirim push: ' . $e->getMessage());
            }
        }

        return $tokensGagal;
    }

    /**
     * Generate OAuth2 access token dari service account JSON, di-cache 55
     * menit (token FCM valid 1 jam) supaya nggak generate ulang tiap kirim.
     */
    private function ambilAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', now()->addMinutes(55), function () {
            $path = storage_path('app/firebase-service-account.json');

            if (! file_exists($path)) {
                return null;
            }

            $sa = json_decode(file_get_contents($path), true);

            if (! isset($sa['private_key'], $sa['client_email'])) {
                return null;
            }

            $now = time();

            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            $claim = $this->base64UrlEncode(json_encode([
                'iss' => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signatureInput = "{$header}.{$claim}";

            $signature = '';
            $ok = openssl_sign($signatureInput, $signature, $sa['private_key'], 'SHA256');

            if (! $ok) {
                Log::error('FCM: gagal sign JWT service account.');
                return null;
            }

            $jwt = $signatureInput . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->failed()) {
                Log::error('FCM: gagal ambil access token OAuth2 - ' . $response->body());
                return null;
            }

            return $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}