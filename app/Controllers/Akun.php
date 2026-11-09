<?php

namespace App\Controllers;

use App\Models\UserModel;

class Akun extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhLogin()) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $db  = db_connect();

        $user = (new UserModel())->find($uid);
        if (! $user) {
            session()->destroy();
            return redirect()->to(site_url('masuk'))->with('error', 'Akun tidak ditemukan.');
        }

        return view('akun', [
            'title'    => 'Profil & Akun Saya — Singgah Medan',
            'user'     => $user,
            'favorit'  => $db->table('favorites')->select('hotels.*')->join('hotels', 'hotels.id = favorites.hotel_id')
                ->where('favorites.user_id', $uid)->orderBy('favorites.created_at', 'DESC')->get()->getResultArray(),
            'ulasan'   => $db->table('reviews')->select('reviews.*, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
                ->join('hotels', 'hotels.id = reviews.hotel_id')->where('reviews.user_id', $uid)
                ->orderBy('reviews.created_at', 'DESC')->get()->getResultArray(),
            'pesan'    => $db->table('inquiries')->select('inquiries.*, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
                ->join('hotels', 'hotels.id = inquiries.hotel_id')->where('inquiries.user_id', $uid)
                ->orderBy('inquiries.created_at', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function update()
    {
        if ($r = $this->butuhLogin()) {
            return $r;
        }
        if (! $this->tokenOk()) {
            return redirect()->back()->with('error', 'Sesi form kedaluwarsa. Silakan coba lagi.');
        }

        $uid         = (int) $this->user()['id'];
        $userModel   = new UserModel();
        $currentUser = $userModel->find($uid);

        if (! $currentUser) {
            session()->destroy();
            return redirect()->to(site_url('masuk'))->with('error', 'Akun tidak ditemukan.');
        }

        $nama   = trim((string) $this->request->getPost('nama'));
        $email  = strtolower(trim((string) $this->request->getPost('email')));
        $pwBaru = (string) $this->request->getPost('password_baru');
        $pwKonf = (string) $this->request->getPost('password_konfirmasi');

        if ($nama === '') {
            return redirect()->back()->withInput()->with('error', 'Nama lengkap wajib diisi.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Format email tidak valid.');
        }

        // Cek email apakah dipakai akun lain
        $duplikat = $userModel->where('email', $email)->where('id !=', $uid)->first();
        if ($duplikat) {
            return redirect()->back()->withInput()->with('error', 'Email tersebut sudah digunakan oleh akun lain.');
        }

        $updateData = [
            'nama'       => $nama,
            'email'      => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Ganti password jika diisi
        if ($pwBaru !== '') {
            if (strlen($pwBaru) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password baru minimal 6 karakter.');
            }
            if ($pwBaru !== $pwKonf) {
                return redirect()->back()->withInput()->with('error', 'Konfirmasi password baru tidak cocok.');
            }
            $updateData['password_hash'] = password_hash($pwBaru, PASSWORD_DEFAULT);
        }

        $userModel->update($uid, $updateData);

        // Perbarui data di session
        session()->set('user', [
            'id'    => $uid,
            'nama'  => $nama,
            'email' => $email,
            'role'  => $currentUser['role'],
        ]);

        return redirect()->to(site_url('akun'))->with('ok', 'Profil berhasil diperbarui!');
    }
}
