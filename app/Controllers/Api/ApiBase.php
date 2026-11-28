<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JwtHelper;
use App\Libraries\SupabaseAuth;
use App\Models\ApiKeyModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

abstract class ApiBase extends BaseController
{
    /** Info aktor terautentikasi: ['type' => 'user'|'partner', 'id' => int|null, 'nama' => string, 'email' => ?string, 'role' => string] */
    protected ?array $authActor = null;

    protected function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, X-API-KEY, Authorization, ngrok-skip-browser-warning')
            ->setJSON($payload);
    }

    /** Batas laju permintaan per IP (default 120 req/menit). */
    protected function cekRateLimit(int $maxPerMinute = 120): ?ResponseInterface
    {
        try {
            $throttler = \Config\Services::throttler();
            $ip        = (string) ($this->request->getIPAddress() ?: '127.0.0.1');
            if ($throttler->check('api_' . md5($ip), $maxPerMinute, MINUTE) === false) {
                return $this->json([
                    'status'  => 'error',
                    'message' => 'Terlalu banyak permintaan. Coba lagi dalam beberapa saat.',
                ], 429);
            }
        } catch (\Throwable $e) {
            // Jangan gagalkan request bila cache throttler tidak tersedia
            log_message('debug', 'Rate limit dilewati: ' . $e->getMessage());
        }

        return null;
    }

    /** Mengembalikan response 401/429 bila autentikasi gagal atau rate limit tercapai; null bila boleh lanjut. */
    protected function butuhKey(): ?ResponseInterface
    {
        if ($r = $this->cekRateLimit()) {
            return $r;
        }

        $token = trim((string) $this->request->getHeaderLine('X-API-KEY'));
        if ($token === '') {
            $auth = (string) $this->request->getHeaderLine('Authorization');
            if (stripos($auth, 'Bearer ') === 0) {
                $token = trim(substr($auth, 7));
            }
        }

        if ($token === '') {
            return $this->json([
                'status'  => 'error',
                'message' => 'Autentikasi diperlukan. Kirim header X-API-KEY atau Authorization: Bearer <token>.',
            ], 401);
        }

        // 1. Cek API Key mitra (prefiks sm_)
        if (str_starts_with($token, 'sm_')) {
            $keyRow = (new ApiKeyModel())->cek($token);
            if (! $keyRow) {
                return $this->json(['status' => 'error', 'message' => 'Kunci API tidak valid atau nonaktif.'], 401);
            }
            $this->authActor = [
                'type'  => 'partner',
                'id'    => null,
                'nama'  => (string) $keyRow['nama'],
                'email' => null,
                'role'  => 'partner',
            ];

            return null;
        }

        // 2. Cek Bearer JWT Token dari Supabase Auth (/auth/v1/user) bila aktif
        $supaKey = trim((string) env('supabase.key', env('SUPABASE_PUBLISHABLE_KEY', env('SUPABASE_ANON_KEY', ''))));
        if ($supaKey !== '' && $supaKey !== 'ISI_PUBLISHABLE_OR_ANON_KEY_ANDA') {
            $supa = new SupabaseAuth();
            if ($supa->enabled()) {
                $res = $supa->getUser($token);
                if ($res['ok'] && ! empty($res['data']['id'])) {
                    $su        = $res['data'];
                    $email     = (string) ($su['email'] ?? '');
                    $localUser = $email !== '' ? (new UserModel())->select('id, nama, email, role')->where('email', $email)->first() : null;
                    $role      = SupabaseAuth::extractRole($su);

                    $this->authActor = [
                        'type'         => 'user',
                        'id'           => $localUser ? (int) $localUser['id'] : null,
                        'auth_user_id' => (string) $su['id'],
                        'nama'         => (string) ($localUser['nama'] ?? ($su['user_metadata']['nama'] ?? $email)),
                        'email'        => $email,
                        'role'         => $role !== 'user' ? $role : (string) ($localUser['role'] ?? 'user'),
                    ];

                    return null;
                }
            }
        }

        // 3. Fallback: Cek Bearer JWT Token lokal hasil /api/v1/auth/login
        $payload = JwtHelper::decode($token);
        if (! $payload || empty($payload['sub'])) {
            return $this->json(['status' => 'error', 'message' => 'Token tidak valid atau sudah kedaluwarsa.'], 401);
        }

        $u = (new UserModel())->select('id, nama, email, role')->find((int) $payload['sub']);
        if (! $u) {
            return $this->json(['status' => 'error', 'message' => 'Akun pengguna tidak ditemukan.'], 401);
        }

        $this->authActor = [
            'type'  => 'user',
            'id'    => (int) $u['id'],
            'nama'  => (string) $u['nama'],
            'email' => (string) $u['email'],
            'role'  => (string) $u['role'],
        ];

        return null;
    }

    /** Memastikan aktor terautentikasi memiliki salah satu role yang diizinkan (403 bila tidak). */
    protected function butuhRole(array $allowedRoles): ?ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $role = $this->authActor['role'] ?? '';
        if (! in_array($role, $allowedRoles, true)) {
            return $this->json([
                'status'  => 'error',
                'message' => "Akses ditolak (403): peran '{$role}' tidak memiliki izin untuk aksi ini.",
            ], 403);
        }

        return null;
    }

    public function options(): ResponseInterface
    {
        return $this->json([], 204);
    }

    protected function filterDari(): array
    {
        return [
            'q'          => (string) $this->request->getGet('q'),
            'kecamatan'  => (string) $this->request->getGet('kecamatan'),
            'sarapan'    => (string) $this->request->getGet('sarapan'),
            'rating_min' => (string) $this->request->getGet('rating_min'),
            'sort'       => (string) $this->request->getGet('sort'),
        ];
    }

    protected function paging(): array
    {
        $limit = min(max((int) ($this->request->getGet('limit') ?: 20), 1), 100);
        $page  = max((int) $this->request->getGet('page'), 1);

        return [$page, $limit];
    }
}
