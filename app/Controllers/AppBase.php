<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Dasar semua controller halaman web: helper, login, admin, dan cek token form. */
abstract class AppBase extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper(['hotel', 'form', 'url', 'text']);
    }

    protected function user(): ?array
    {
        return session('user');
    }

    protected function butuhLogin(): ?RedirectResponse
    {
        if ($this->user()) {
            return null;
        }
        $kembali = strtolower($this->request->getMethod()) === 'post'
            ? (string) $this->request->getServer('HTTP_REFERER')
            : current_url();
        session()->set('kembali', $kembali);

        return redirect()->to(site_url('masuk'))->with('error', 'Silakan masuk dulu.');
    }

    protected function butuhAdmin(): ?RedirectResponse
    {
        if (($this->user()['role'] ?? '') !== 'admin') {
            return redirect()->to(site_url('admin'))->with('error', 'Silakan masuk sebagai admin terlebih dahulu.');
        }

        return null;
    }

    protected function tokenOk(): bool
    {
        $t = (string) $this->request->getPost('_token');
        $s = (string) session('_token');

        return $t !== '' && $s !== '' && hash_equals($s, $t);
    }

    /** Gabungan cek login + token untuk aksi POST biasa. Mengembalikan redirect bila gagal. */
    protected function gerbangPost(bool $loginWajib = true): ?RedirectResponse
    {
        if ($loginWajib && ($r = $this->butuhLogin())) {
            return $r;
        }
        if (! $this->tokenOk()) {
            return redirect()->back()->with('error', 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        }

        return null;
    }

    protected function gerbangAdminPost(): ?RedirectResponse
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        if (! $this->tokenOk()) {
            return redirect()->back()->with('error', 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        }

        return null;
    }

    /** Ubah string kosong menjadi null untuk kolom Postgres bertipe angka/tanggal. */
    protected function nullable($v)
    {
        $v = is_string($v) ? trim($v) : $v;

        return ($v === '' || $v === null) ? null : $v;
    }
}
