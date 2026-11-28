<?php

namespace App\Controllers;

use App\Libraries\SupabaseAuth;
use App\Models\UserModel;

class Auth extends AppBase
{
    private function supabaseAktif(): bool
    {
        $key = trim((string) env('supabase.key', env('SUPABASE_PUBLISHABLE_KEY', env('SUPABASE_ANON_KEY', ''))));

        return $key !== '' && $key !== 'ISI_PUBLISHABLE_OR_ANON_KEY_ANDA' && (new SupabaseAuth())->enabled();
    }

    private function masukkan(array $u, ?array $supaSession = null)
    {
        session()->regenerate();
        session()->set('user', [
            'id'           => $u['id'],
            'auth_user_id' => $u['auth_user_id'] ?? null,
            'nama'         => $u['nama'],
            'email'        => $u['email'],
            'role'         => $u['role'],
        ]);
        if ($supaSession !== null) {
            session()->set('supabase_access_token', $supaSession['access_token'] ?? '');
            session()->set('supabase_refresh_token', $supaSession['refresh_token'] ?? '');
            session()->set('supabase_expires_at', time() + (int) ($supaSession['expires_in'] ?? 3600));
        }
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
            $login    = strtolower(trim((string) $this->request->getPost('email')));
            $password = (string) $this->request->getPost('password');

            if ($this->supabaseAktif() && filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $supa = new SupabaseAuth();
                $res  = $supa->signInWithPassword($login, $password);
                if ($res['ok'] && ! empty($res['data']['user'])) {
                    $su       = $res['data']['user'];
                    $supaUid  = (string) ($su['id'] ?? '');
                    $supaRole = SupabaseAuth::extractRole($su);
                    $nama     = (string) ($su['user_metadata']['nama'] ?? explode('@', $login)[0]);

                    $localUser = (new UserModel())->where('email', $login)->first();
                    $u         = [
                        'id'           => $localUser['id'] ?? $supaUid,
                        'auth_user_id' => $supaUid,
                        'nama'         => $localUser['nama'] ?? $nama,
                        'email'        => $login,
                        'role'         => $supaRole !== 'user' ? $supaRole : ($localUser['role'] ?? 'user'),
                    ];

                    return $this->masukkan($u, $res['data']);
                }

                // Fallback ke user lokal bila belum dimigrasi ke auth.users
                $u = (new UserModel())->findByLogin($login);
                if (! $u || ! password_verify($password, $u['password_hash'])) {
                    return redirect()->back()->withInput()->with('error', $res['error'] ?? 'Email atau password salah.');
                }

                return $this->masukkan($u);
            }

            $u = (new UserModel())->findByLogin($login);
            if (! $u || ! password_verify($password, $u['password_hash'])) {
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
            // Rapikan & seragamkan nomor HP ke format 08... sebelum validasi is_unique
            $noHp = $this->normHp((string) $this->request->getPost('no_hp'));
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
            $supaSession = null;
            $supaUid     = null;
            if ($this->supabaseAktif()) {
                $supa = new SupabaseAuth();
                $res  = $supa->signUp($data['email'], $data['password'], [
                    'nama'          => $data['nama'],
                    'no_hp'         => $data['no_hp'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?: null,
                    'tanggal_lahir' => $data['tanggal_lahir'] ?: null,
                    'kota_asal'     => $data['kota_asal'] ?: null,
                ]);
                if (! $res['ok']) {
                    return redirect()->back()->withInput()->with('error', $res['error'] ?? 'Pendaftaran ke Supabase Auth gagal.');
                }
                $supaUid     = $res['data']['user']['id'] ?? ($res['data']['id'] ?? null);
                $supaSession = ! empty($res['data']['access_token']) ? $res['data'] : null;
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

            if ($this->supabaseAktif() && $supaSession === null) {
                return redirect()->to(site_url('masuk'))->with('ok', 'Pendaftaran berhasil! Silakan cek email untuk verifikasi akun sebelum masuk.');
            }

            $userRow                 = $m->find($id);
            $userRow['auth_user_id'] = $supaUid;

            return $this->masukkan($userRow, $supaSession);
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
        $accessToken = (string) session('supabase_access_token');
        if ($accessToken !== '' && $this->supabaseAktif()) {
            (new SupabaseAuth())->signOut($accessToken);
        }

        session()->remove(['user', 'supabase_access_token', 'supabase_refresh_token', 'supabase_expires_at']);
        session()->regenerate(true);
        session()->setFlashdata('logout_popup', true);

        return redirect()->to(site_url('/'));
    }
}
