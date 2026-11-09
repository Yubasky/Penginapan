<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\UserModel;

class Dashboard extends AppBase
{
    public function index()
    {
        $user    = $this->user();
        $isAdmin = ($user['role'] ?? '') === 'admin';

        // Jika belum login sebagai admin
        if (! $isAdmin) {
            // Jika ada form submit POST untuk login admin langsung di /admin
            if (strtolower($this->request->getMethod()) === 'post') {
                if (! $this->tokenOk()) {
                    return redirect()->to(site_url('admin'))->with('error', 'Sesi form kedaluwarsa. Silakan coba lagi.');
                }

                $login    = strtolower(trim((string) $this->request->getPost('email')));
                $password = (string) $this->request->getPost('password');

                $u = (new UserModel())->groupStart()
                    ->where('email', $login)
                    ->orWhere('nama', $login)
                    ->groupEnd()->first();

                if (! $u || ! password_verify($password, $u['password_hash'])) {
                    return redirect()->to(site_url('admin'))->withInput()->with('error', 'Email/username atau password admin salah.');
                }

                if ($u['role'] !== 'admin') {
                    return redirect()->to(site_url('admin'))->withInput()->with('error', 'Akun ini bukan admin.');
                }

                session()->regenerate();
                session()->set('user', [
                    'id'    => (int) $u['id'],
                    'nama'  => $u['nama'],
                    'email' => $u['email'],
                    'role'  => $u['role'],
                ]);

                return redirect()->to(site_url('admin'))->with('ok', 'Selamat datang di panel Admin, ' . $u['nama'] . '!');
            }

            // Tampilkan halaman login admin khusus langsung di /admin
            return view('admin/login', [
                'title'       => 'Masuk Admin — Singgah Medan',
                'currentUser' => $user,
            ]);
        }

        // Tampilkan Dashboard Admin
        $db = db_connect();
        $n  = static fn (string $t, ?array $w = null) => $w ? $db->table($t)->where($w)->countAllResults() : $db->table($t)->countAllResults();

        return view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'c'     => [
                'hotel'     => $n('hotels'),
                'geo'       => $db->table('hotels')->where('latitude IS NOT NULL', null, false)->countAllResults(),
                'pengguna'  => $n('users'),
                'ulasan'    => $n('reviews'),
                'pesan'     => $n('inquiries'),
                'pesanBaru' => $n('inquiries', ['status' => 'baru']),
                'kuliner'   => $n('kuliner'),
                'apikey'    => $n('api_keys', ['aktif' => 'true']),
            ],
            'pesanTerbaru' => $db->table('inquiries')->select('inquiries.*, hotels.nama AS hotel_nama')
                ->join('hotels', 'hotels.id = inquiries.hotel_id')->orderBy('inquiries.created_at', 'DESC')->limit(5)->get()->getResultArray(),
            'syncTerakhir' => $db->table('kuliner')->selectMax('synced_at')->get()->getRowArray()['synced_at'] ?? null,
        ]);
    }
}
