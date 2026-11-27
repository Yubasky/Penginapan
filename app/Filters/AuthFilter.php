<?php

namespace App\Filters;

use App\Libraries\SupabaseAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter proteksi route web yang mewajibkan pengguna sudah login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        if (! is_array($user) || empty($user['id'])) {
            $kembali = strtolower($request->getMethod()) === 'get'
                ? current_url()
                : (string) $request->getServer('HTTP_REFERER');
            if ($kembali !== '') {
                session()->set('kembali', $kembali);
            }

            return redirect()->to(site_url('masuk'))->with('error', 'Silakan masuk dulu.');
        }

        // Jika sesi memakai Supabase Auth, perbarui access token bila mendekati kedaluwarsa
        $expiresAt    = (int) session('supabase_expires_at');
        $refreshToken = (string) session('supabase_refresh_token');
        if ($expiresAt > 0 && $refreshToken !== '' && time() >= ($expiresAt - 60)) {
            $supa = new SupabaseAuth();
            if ($supa->enabled()) {
                $res = $supa->refreshSession($refreshToken);
                if ($res['ok'] && ! empty($res['data']['access_token'])) {
                    session()->set('supabase_access_token', $res['data']['access_token']);
                    session()->set('supabase_refresh_token', $res['data']['refresh_token'] ?? $refreshToken);
                    session()->set('supabase_expires_at', time() + (int) ($res['data']['expires_in'] ?? 3600));
                } else {
                    session()->remove(['user', 'supabase_access_token', 'supabase_refresh_token', 'supabase_expires_at']);

                    return redirect()->to(site_url('masuk'))->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
                }
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

