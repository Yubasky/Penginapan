<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends AppBase
{
    private function masukkan(array $u)
    {
        session()->regenerate();
        session()->set('user', ['id' => (int) $u['id'], 'nama' => $u['nama'], 'email' => $u['email'], 'role' => $u['role']]);
        $kembali = (string) session()->get('kembali');
        session()->remove('kembali');
        if ($kembali === '' || ! str_starts_with($kembali, base_url())) {
            $kembali = $u['role'] === 'admin' ? site_url('admin') : site_url('/');
        }

        return redirect()->to($kembali)->with('login_popup', $u['nama']);
    }

    public function masuk()
    {
        if ($this->user()) {
            return redirect()->to(site_url('/'));
        }
        if (strtolower($this->request->getMethod()) === 'post') {
            if (! $this->tokenOk()) {
                return redirect()->back()->with('error', 'Sesi form kedaluwarsa, coba lagi.');
            }
            $login = strtolower(trim((string) $this->request->getPost('email')));
            $u     = (new UserModel())->groupStart()
                ->where('email', $login)
                ->orWhere('nama', $login)
                ->groupEnd()->first();
            if (! $u || ! password_verify((string) $this->request->getPost('password'), $u['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Email/username atau password salah.');
            }

            return $this->masukkan($u);
        }

        return view('auth/masuk', ['title' => 'Masuk — Singgah Medan']);
    }

    public function daftar()
    {
        if ($this->user()) {
            return redirect()->to(site_url('/'));
        }
        if (strtolower($this->request->getMethod()) === 'post') {
            if (! $this->tokenOk()) {
                return redirect()->back()->with('error', 'Sesi form kedaluwarsa, coba lagi.');
            }
            $ok = $this->validate([
                'nama'      => 'required|min_length[2]|max_length[100]',
                'email'     => 'required|valid_email|max_length[190]|is_unique[users.email]',
                'password'  => 'required|min_length[8]|max_length[72]',
                'password2' => 'required|matches[password]',
            ], [
                'email'     => ['is_unique' => 'Email itu sudah terdaftar. Silakan masuk.'],
                'password2' => ['matches' => 'Ulangi password tidak sama.'],
            ]);
            if (! $ok) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }
            $m  = new UserModel();
            $id = $m->insert([
                'nama'          => trim((string) $this->request->getPost('nama')),
                'email'         => strtolower(trim((string) $this->request->getPost('email'))),
                'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'role'          => 'user',
            ]);

            return $this->masukkan($m->find($id));
        }

        return view('auth/daftar', ['title' => 'Daftar — Singgah Medan']);
    }

    public function keluar()
    {
        session()->remove('user');
        session()->regenerate(true);
        session()->setFlashdata('logout_popup', true);

        return redirect()->to(site_url('/'));
    }
}
