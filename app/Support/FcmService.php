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
            $pesan = 'FCM belum dikonfigurasi (FIREBASE_PROJECT_ID / service account belum diisi), push notification dilewati.';
            Log::warning($pesan);
            $this->catatActivityLog($pesan, [
                'firebase_project_id_ada' => (bool) $projectId,
                'access_token_berhasil' => (bool) $accessToken,
            ]);
            return $tokensGagal;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($tokens as $token) {
            try {
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
                    $pesan = 'FCM gagal kirim ke token: ' . $errStatus . ' - ' . $response->body();
                    Log::warning($pesan);
                    $this->catatActivityLog('FCM gagal kirim push notification', [
                        'http_status' => $response->status(),
                        'error_status' => $errStatus,
                        'response_body' => $response->body(),
                        'token_dipotong' => substr($token, 0, 12) . '...', // jangan simpan token utuh
                    ]);

                    // Token sudah nggak valid (uninstall/logout) -> tandai buat dihapus
                    if (in_array($errStatus, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
                        $tokensGagal[] = $token;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('FCM error kirim push: ' . $e->getMessage());
                $this->catatActivityLog('FCM exception saat kirim push notification', [
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
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
                $this->catatActivityLog('FCM: file service account tidak ditemukan di server', [
                    'path' => $path,
                ]);
                return null;
            }

            $sa = json_decode(file_get_contents($path), true);

            if (! isset($sa['private_key'], $sa['client_email'])) {
                $this->catatActivityLog('FCM: file service account ada tapi isinya tidak valid (private_key/client_email kosong)', [
                    'path' => $path,
                ]);
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
                $this->catatActivityLog('FCM: gagal sign JWT pakai private key service account (kemungkinan file corrupt / private key salah format)', []);
                return null;
            }

            $jwt = $signatureInput . '.' . $this->base64UrlEncode($signature);

            try {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);
            } catch (\Throwable $e) {
                // Ini yang bakal kena kalau koneksi outbound server ke Google diblokir hosting
                Log::error('FCM: exception saat request access token OAuth2 - ' . $e->getMessage());
                $this->catatActivityLog('FCM: gagal connect ke oauth2.googleapis.com (kemungkinan outbound HTTPS diblokir hosting)', [
                    'error_message' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
                return null;
            }

            if ($response->failed()) {
                $pesan = 'FCM: gagal ambil access token OAuth2 - ' . $response->body();
                Log::error($pesan);
                $this->catatActivityLog('FCM: gagal ambil access token OAuth2 dari Google', [
                    'http_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * Catat kejadian gagal FCM ke Activity Log (database) supaya kelihatan
     * dari halaman admin /activity-Log tanpa perlu buka file log server.
     * Sengaja dibungkus try/catch sendiri: kalau nulis activity log ini
     * gagal (mis. tabel activity_log belum ke-migrate), jangan sampai bikin
     * proses kirim notif utama ikut error.
     */
    private function catatActivityLog(string $pesan, array $properties): void
    {
        try {
            $user = auth()->user() ?? auth('member')->user();

            $log = activity('fcm');

            if ($user) {
                $log->causedBy($user);
            }

            $log->withProperties($properties)->log($pesan);
        } catch (\Throwable $e) {
            Log::error('Gagal catat activity log untuk FCM: ' . $e->getMessage());
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}