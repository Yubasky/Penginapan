<?php

namespace App\Controllers\Api;

use App\Libraries\JwtHelper;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/** Autentikasi terpadu berbasis token JWT untuk client Web / Mobile. */
class Auth extends ApiBase
{
    /**
     * POST /api/v1/auth/login
     * Body JSON / form: {"email": "...", "password": "..."}
     */
    public function login(): ResponseInterface
    {
        if ($r = $this->cekRateLimit(30)) {
            return $r;
        }

        $json     = $this->request->getJSON(true);
        $input    = is_array($json) ? $json : $this->request->getPost();
        $login    = strtolower(trim((string) ($input['email'] ?? $input['username'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if ($login === '' || $password === '') {
            return $this->json([
                'status'  => 'error',
                'message' => 'Email/username dan password wajib diisi.',
            ], 422);
        }

        $u = (new UserModel())
            ->groupStart()
            ->where('email', $login)
            ->orWhere('nama', $login)
            ->groupEnd()
            ->first();

        if (! $u || ! password_verify($password, (string) $u['password_hash'])) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Email/username atau password salah.',
            ], 401);
        }

        $ttl   = 86400; // 24 jam
        $token = JwtHelper::encode([
            'sub'   => (int) $u['id'],
            'email' => $u['email'],
            'role'  => $u['role'],
        ], $ttl);

        return $this->json([
            'status'  => 'ok',
            'message' => 'Login berhasil.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => $ttl,
                'user'       => [
                    'id'    => (int) $u['id'],
                    'nama'  => $u['nama'],
                    'email' => $u['email'],
                    'role'  => $u['role'],
                ],
            ],
        ]);
    }

    /** GET /api/v1/auth/me — melihat profil user dari token aktif. */
    public function me(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }

        return $this->json([
            'status' => 'ok',
            'data'   => $this->authActor,
        ]);
    }
}

