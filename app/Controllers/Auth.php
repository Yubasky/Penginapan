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
            // Rapikan nomor HP (hapus spasi, tanda hubung, tanda kurung) sebelum divalidasi
            $noHp = preg_replace('/[\s\-().]/', '', (string) $this->request->getPost('no_hp'));
            $data = [
                'nama'          => trim((string) $this->request->getPost('nama')),
                'email'         => strtolower(trim((string) $this->request->getPost('email'))),
                'no_hp'         => $noHp,
                'jenis_kelamin' => (string) $this->request->getPost('jenis_kelamin'),
                'tanggal_lahir' => (string) $this->request->getPost('tanggal_lahir'),
                'kota_asal'     => trim((string) $this->request->getPost('kota_asal')),
                'password'      => (string) $this->request->getPost('password'),
                'password2'     => (string) $this->request->getPost('password2'),
                'setuju'        => (string) $this->request->getPost('setuju'),
            ];
            $ok = $this->validateData($data, [
                'nama'          => 'required|min_length[2]|max_length[100]',
                'email'         => 'required|valid_email|max_length[190]|is_unique[users.email]',
                'no_hp'         => 'required|regex_match[/^(\+62|62|0)8[0-9]{8,12}$/]|is_unique[users.no_hp]',
                'jenis_kelamin' => 'permit_empty|in_list[Laki-laki,Perempuan]',
                'tanggal_lahir' => 'permit_empty|valid_date[Y-m-d]',
                'kota_asal'     => 'permit_empty|max_length[100]',
                'password'      => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*\d).+$/]',
                'password2'     => 'required|matches[password]',
                'setuju'        => 'required',
            ], [
                'email'     => ['is_unique' => 'Email itu sudah terdaftar. Silakan masuk.'],
                'no_hp'     => [
                    'required'    => 'Nomor HP wajib diisi.',
                    'regex_match' => 'Nomor HP tidak valid. Contoh: 081234567890.',
                    'is_unique'   => 'Nomor HP itu sudah terdaftar.',
                ],
                'tanggal_lahir' => ['valid_date' => 'Tanggal lahir tidak valid.'],
                'password'  => ['regex_match' => 'Password harus mengandung huruf dan angka.'],
                'password2' => ['matches' => 'Ulangi password tidak sama.'],
                'setuju'    => ['required' => 'Kamu harus menyetujui syarat & ketentuan.'],
            ]);
            if (! $ok) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }
            if ($data['tanggal_lahir'] !== '') {
                $lahir = new \DateTimeImmutable($data['tanggal_lahir']);
                $umur  = $lahir->diff(new \DateTimeImmutable('today'))->y;
                if ($lahir > new \DateTimeImmutable('today') || $umur < 13) {
                    return redirect()->back()->withInput()->with('error', 'Tanggal lahir tidak valid (usia minimal 13 tahun).');
                }
            }
            $m  = new UserModel();
            $id = $m->insert([
                'nama'          => $data['nama'],
                'email'         => $data['email'],
                'no_hp'         => $data['no_hp'],
                'jenis_kelamin' => $data['jenis_kelamin'] ?: null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?: null,
                'kota_asal'     => $data['kota_asal'] ?: null,
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role'          => 'user',
            ]);

            return $this->masukkan($m->find($id));
        }

        return view('auth/daftar', ['title' => 'Daftar — Singgah Medan']);
    }

    /** Samakan format nomor HP: +62812... / 62812... / 0812... -> 0812... */
    private function normHp(string $hp): string
    {
        $hp = preg_replace('/[\s\-().]/', '', $hp);
        if (str_starts_with($hp, '+62')) {
            return '0' . substr($hp, 3);
        }
        if (str_starts_with($hp, '62')) {
            return '0' . substr($hp, 2);
        }

        return $hp;
    }

    public function lupa()
    {
        if ($this->user()) {
            return redirect()->to(site_url('/'));
        }
        if (strtolower($this->request->getMethod()) === 'post') {
            if (! $this->tokenOk()) {
                return redirect()->back()->with('error', 'Sesi form kedaluwarsa, coba lagi.');
            }
            // Batasi percobaan: maksimal 5 kali per 10 menit per alamat IP
            if (! service('throttler')->check('lupa_' . md5((string) $this->request->getIPAddress()), 5, 600)) {
                return redirect()->back()->with('error', 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.');
            }

            $email = strtolower(trim((string) $this->request->getPost('email')));
            $noHp  = $this->normHp((string) $this->request->getPost('no_hp'));
            $data  = [
                'password'  => (string) $this->request->getPost('password'),
                'password2' => (string) $this->request->getPost('password2'),
            ];
            $ok = $this->validateData($data, [
                'password'  => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*\d).+$/]',
                'password2' => 'required|matches[password]',
            ], [
                'password'  => ['regex_match' => 'Password harus mengandung huruf dan angka.'],
                'password2' => ['matches' => 'Ulangi password tidak sama.'],
            ]);
            if (! $ok) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $m = new UserModel();
            $u = $m->where('email', $email)->first();
            // Pesan sengaja dibuat sama untuk semua kegagalan agar tidak bisa dipakai menebak email yang terdaftar.
            $gagal = 'Email atau nomor HP tidak cocok, atau akun ini tidak bisa direset lewat sini. Hubungi admin.';
            if (
                ! $u
                || ($u['role'] ?? 'user') === 'admin'
                || empty($u['no_hp'])
                || $noHp === ''
                || ! hash_equals($this->normHp((string) $u['no_hp']), $noHp)
            ) {
                return redirect()->back()->withInput()->with('error', $gagal);
            }

            $m->update($u['id'], ['password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)]);

            return redirect()->to(site_url('masuk'))->with('ok', 'Password berhasil diperbarui. Silakan masuk dengan password baru.');
        }

        return view('auth/lupa', ['title' => 'Lupa Sandi — Singgah Medan']);
    }

    public function keluar()
    {
        session()->remove('user');
        session()->regenerate(true);
        session()->setFlashdata('logout_popup', true);

        return redirect()->to(site_url('/'));
    }
}
