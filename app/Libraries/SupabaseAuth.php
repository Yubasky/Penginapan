<?php

namespace App\Libraries;

/**
 * Klien ringan Supabase Auth (GoTrue REST API) untuk CodeIgniter 4.
 *
 * Menggunakan Publishable/Anon key pada sisi server PHP via cURL (`Services::curlrequest`)
 * tanpa membocorkan service_role key ke browser/client.
 */
class SupabaseAuth
{
    private string $url;
    private string $apiKey;

    public function __construct()
    {
        $this->url    = rtrim((string) env('supabase.url', env('SUPABASE_URL', '')), '/');
        $this->apiKey = trim((string) env('supabase.key', env('SUPABASE_PUBLISHABLE_KEY', env('SUPABASE_ANON_KEY', ''))));
    }

    public function enabled(): bool
    {
        return $this->url !== '' && $this->apiKey !== '';
    }

    /**
     * Login dengan email & password via Supabase Auth.
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function signInWithPassword(string $email, string $password): array
    {
        return $this->request('POST', '/auth/v1/token?grant_type=password', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * Daftar akun baru via Supabase Auth.
     *
     * @param array<string, mixed> $userMetadata
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function signUp(string $email, string $password, array $userMetadata = []): array
    {
        return $this->request('POST', '/auth/v1/signup', [
            'email'    => $email,
            'password' => $password,
            'data'     => $userMetadata,
        ]);
    }

    /**
     * Perbarui access token menggunakan refresh token.
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function refreshSession(string $refreshToken): array
    {
        return $this->request('POST', '/auth/v1/token?grant_type=refresh_token', [
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Ambil data user aktif langsung dari Supabase Auth (/auth/v1/user) untuk validasi token.
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function getUser(string $accessToken): array
    {
        return $this->request('GET', '/auth/v1/user', null, $accessToken);
    }

    /**
     * Revoke sesi aktif di Supabase Auth saat logout.
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function signOut(string $accessToken): array
    {
        return $this->request('POST', '/auth/v1/logout', null, $accessToken);
    }

    /**
     * Kirim email reset password via Supabase Auth.
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    public function sendPasswordReset(string $email, ?string $redirectTo = null): array
    {
        $query = $redirectTo ? '?redirect_to=' . rawurlencode($redirectTo) : '';

        return $this->request('POST', '/auth/v1/recover' . $query, [
            'email' => $email,
        ]);
    }

    /**
     * Ekstrak role otorisasi hanya dari `app_metadata` (bukan `user_metadata`).
     */
    public static function extractRole(array $supabaseUser): string
    {
        $role = $supabaseUser['app_metadata']['role'] ?? 'user';

        return in_array($role, ['user', 'admin'], true) ? $role : 'user';
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array{ok: bool, data?: array, error?: string, status?: int}
     */
    private function request(string $method, string $path, ?array $body = null, ?string $bearer = null): array
    {
        if (! $this->enabled()) {
            return ['ok' => false, 'status' => 500, 'error' => 'Konfigurasi supabase.url dan supabase.key belum diisi di .env.'];
        }

        $headers = [
            'apikey'       => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
        if ($bearer !== null && $bearer !== '') {
            $headers['Authorization'] = 'Bearer ' . $bearer;
        }

        try {
            $client  = \Config\Services::curlrequest(['timeout' => 10, 'http_errors' => false]);
            $options = ['headers' => $headers];
            if ($body !== null) {
                $options['json'] = $body;
            }

            $res    = $client->request($method, $this->url . $path, $options);
            $status = $res->getStatusCode();
            $json   = json_decode((string) $res->getBody(), true);
            $data   = is_array($json) ? $json : [];

            if ($status >= 200 && $status < 300) {
                return ['ok' => true, 'status' => $status, 'data' => $data];
            }

            $msg = $data['msg'] ?? $data['error_description'] ?? $data['message'] ?? 'Permintaan ke Supabase Auth gagal.';

            return ['ok' => false, 'status' => $status, 'error' => (string) $msg, 'data' => $data];
        } catch (\Throwable $e) {
            log_message('error', 'SupabaseAuth error: {msg}', ['msg' => $e->getMessage()]);

            return ['ok' => false, 'status' => 500, 'error' => 'Tidak dapat terhubung ke server Supabase Auth.'];
        }
    }
}

