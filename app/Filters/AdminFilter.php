<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter proteksi route panel Admin (`/admin/*`).
 * Role diverifikasi dari session yang bersumber dari `app_metadata.role` / DB, bukan `user_metadata`.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        if (! is_array($user) || ($user['role'] ?? '') !== 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Silakan masuk sebagai admin terlebih dahulu.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

