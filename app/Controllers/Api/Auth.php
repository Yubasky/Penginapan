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

        $u = (new UserModel())->findByLogin($login);

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

    /**
     * POST /api/v1/auth/register
     * Registrasi akun baru role 'user' untuk aplikasi client (mengembalikan token JWT).
     */
    public function register(): ResponseInterface
    {
        if ($r = $this->cekRateLimit(20)) {
            return $r;
        }

        $json  = $this->request->getJSON(true);
        $input = is_array($json) ? $json : $this->request->getPost();

        $nama     = trim((string) ($input['nama'] ?? ''));
        $email    = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $noHp     = preg_replace('/[\s\-().]/', '', (string) ($input['no_hp'] ?? ''));

        if (mb_strlen($nama) < 2) {
            return $this->json(['status' => 'error', 'message' => 'nama minimal 2 karakter.'], 422);
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['status' => 'error', 'message' => 'Format email tidak valid.'], 422);
        }
        if (mb_strlen($password) < 8) {
            return $this->json(['status' => 'error', 'message' => 'password minimal 8 karakter.'], 422);
        }

        $um = new UserModel();
        if ($um->where('email', $email)->first()) {
            return $this->json(['status' => 'error', 'message' => 'Email sudah terdaftar.'], 409);
        }

        $id = $um->insert([
            'nama'          => $nama,
            'email'         => $email,
            'no_hp'         => $noHp !== '' ? $noHp : null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'user',
        ]);

        if (! $id) {
            return $this->json(['status' => 'error', 'message' => 'Gagal mendaftarkan pengguna.'], 500);
        }

        $u     = $um->find($id);
        $ttl   = 86400;
        $token = JwtHelper::encode([
            'sub'   => (int) $u['id'],
            'email' => $u['email'],
            'role'  => $u['role'],
        ], $ttl);

        return $this->json([
            'status'  => 'ok',
            'message' => 'Registrasi berhasil.',
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
        ], 201);
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

