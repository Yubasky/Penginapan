<?php

namespace App\Controllers;

use App\Models\HotelModel;
use App\Models\InquiryModel;
use App\Models\ReviewModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Aksi pengunjung: favorit, ulasan, dan pesan/tanya hotel. */
class Interaksi extends AppBase
{
    private function hotelAtau404(int $id): array
    {
        $h = (new HotelModel())->find($id);
        if (! $h) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $h;
    }

    public function favorit(int $id)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $this->hotelAtau404($id);
        $uid = (int) $this->user()['id'];
        $t   = db_connect()->table('favorites');
        $ada = $t->where(['user_id' => $uid, 'hotel_id' => $id])->countAllResults() > 0;
        if ($ada) {
            db_connect()->table('favorites')->where(['user_id' => $uid, 'hotel_id' => $id])->delete();
            $msg = 'Dihapus dari favorit.';
        } else {
            db_connect()->table('favorites')->insert(['user_id' => $uid, 'hotel_id' => $id]);
            $msg = 'Disimpan ke favorit.';
        }

        return redirect()->back()->with('ok', $msg);
    }

    public function ulasan(int $id)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $hotel = $this->hotelAtau404($id);
        $ok    = $this->validate([
            'rating' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
            'judul'  => 'permit_empty|max_length[150]',
            'isi'    => 'required|min_length[10]|max_length[2000]',
        ], [
            'rating' => ['required' => 'Pilih rating bintang 1–5.'],
            'isi'    => ['required' => 'Isi ulasan wajib diisi.', 'min_length' => 'Ulasan minimal 10 karakter.'],
        ]);
        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $m    = new ReviewModel();
        $uid  = (int) $this->user()['id'];
        $lama = $m->where('hotel_id', $id)->where('user_id', $uid)->first();

        // Olah media lama jika sedang mengedit ulasan
        $mediaList = [];
        if ($lama && ! empty($lama['media'])) {
            $existing = json_decode($lama['media'], true) ?: [];
            $hapusMedia = (array) $this->request->getPost('hapus_media');
            foreach ($existing as $ex) {
                if (! in_array($ex['url'], $hapusMedia, true)) {
                    $mediaList[] = $ex;
                }
            }
        }

        // Olah file foto/video yang baru diunggah (Base64 Cloud Supabase)
        $files = $this->request->getFileMultiple('media');
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && ! $file->hasMoved()) {
                    $mime = $file->getMimeType() ?: 'image/jpeg';
                    $ext  = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
                    $isImg = str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    $isVid = str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'quicktime']);

                    if (! $isImg && ! $isVid) {
                        return redirect()->back()->withInput()->with('error', 'Format berkas "' . esc($file->getClientName()) . '" tidak didukung. Harap unggah foto (JPG, PNG, WEBP) atau video (MP4, WEBM).');
                    }
                    if ($file->getSizeByUnit('mb') > 20) {
                        return redirect()->back()->withInput()->with('error', 'Berkas "' . esc($file->getClientName()) . '" terlalu besar (maksimal 20MB).');
                    }

                    $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getTempName()));

                    $mediaList[] = [
                        'url'  => $base64,
                        'type' => $isVid ? 'video' : 'image',
                        'nama' => $file->getClientName(),
                    ];
                }
            }
        }

        $data = [
            'hotel_id' => $id,
            'user_id'  => $uid,
            'rating'   => (int) $this->request->getPost('rating'),
            'judul'    => $this->nullable($this->request->getPost('judul')),
            'isi'      => trim((string) $this->request->getPost('isi')),
            'media'    => ! empty($mediaList) ? json_encode($mediaList) : null,
        ];

        if ($lama) {
            $m->update($lama['id'], $data);
            $msg = 'Ulasanmu berhasil diperbarui.';
        } else {
            $m->insert($data);
            $msg = 'Terima kasih, ulasanmu berhasil tersimpan.';
        }

        // Sinkronkan rating dan jumlah ulasan hotel secara dinamis
        (new HotelModel())->sinkronRating($id);

        return redirect()->to(site_url('penginapan/' . $hotel['slug']) . '#ulasan')->with('ok', $msg);
    }

    public function hapusUlasan(int $id)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $hotel = $this->hotelAtau404($id);
        $m     = new ReviewModel();
        $uid   = (int) $this->user()['id'];
        $lama  = $m->where('hotel_id', $id)->where('user_id', $uid)->first();

        if ($lama) {
            $m->delete($lama['id']);
            // Sinkronkan rating dan jumlah ulasan hotel setelah dihapus
            (new HotelModel())->sinkronRating($id);
        }

        return redirect()->to(site_url('penginapan/' . $hotel['slug']) . '#ulasan')->with('ok', 'Ulasanmu berhasil dihapus.');
    }

    /** Tanya seputar hotel (Q&A Publik). */
    public function tanya(int $id)
    {
        if ($r = $this->gerbangPost(false)) {
            return $r;
        }
        $hotel = $this->hotelAtau404($id);
        $balik = site_url('penginapan/' . $hotel['slug']) . '#tanya';

        if (trim((string) $this->request->getPost('website')) !== '') {
            return redirect()->to($balik)->with('ok', 'Pertanyaanmu terkirim.');
        }

        $u  = $this->user();
        $ok = $this->validate([
            'pertanyaan' => 'required|min_length[5]|max_length[1000]',
            'nama'       => $u ? 'permit_empty' : 'required|max_length[100]',
        ], [
            'pertanyaan' => ['required' => 'Pertanyaan tidak boleh kosong.', 'min_length' => 'Pertanyaan minimal 5 karakter.'],
            'nama'       => ['required' => 'Nama penanya wajib diisi.'],
        ]);
        if (! $ok) {
            return redirect()->to($balik)->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama  = $u ? $u['nama'] : trim((string) $this->request->getPost('nama'));
        $email = $u ? $u['email'] : ($this->request->getPost('email') ? strtolower(trim((string) $this->request->getPost('email'))) : null);

        (new InquiryModel())->insert([
            'hotel_id' => $id,
            'user_id'  => $u['id'] ?? null,
            'nama'     => $nama,
            'email'    => $email,
            'pesan'    => trim((string) $this->request->getPost('pertanyaan')),
            'status'   => 'baru',
            'jawaban'  => json_encode([]),
        ]);

        return redirect()->to($balik)->with('ok', 'Pertanyaanmu berhasil diajukan! Pengelola hotel atau pengunjung lain akan segera menjawab.');
    }

    /** Jawab pertanyaan seputar hotel (bisa dijawab oleh admin maupun sesama pengunjung). */
    public function jawab(int $inquiryId)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $m   = new InquiryModel();
        $inq = $m->find($inquiryId);
        if (! $inq) {
            throw PageNotFoundException::forPageNotFound();
        }
        $hotel = $this->hotelAtau404((int) $inq['hotel_id']);
        $balik = site_url('penginapan/' . $hotel['slug']) . '#tanya';

        $ok = $this->validate([
            'jawaban' => 'required|min_length[3]|max_length[1500]',
        ], [
            'jawaban' => ['required' => 'Jawaban tidak boleh kosong.', 'min_length' => 'Jawaban minimal 3 karakter.'],
        ]);
        if (! $ok) {
            return redirect()->to($balik)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $u    = $this->user();
        $role = $u['role'] ?? 'user';
        $list = json_decode($inq['jawaban'] ?? '[]', true) ?: [];
        $teks = trim((string) $this->request->getPost('jawaban'));

        $list[] = [
            'id'         => bin2hex(random_bytes(6)),
            'user_id'    => (int) $u['id'],
            'nama'       => $u['nama'],
            'role'       => $role,
            'isi'        => $teks,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $updateData = [
            'jawaban' => json_encode($list),
        ];

        if ($role === 'admin') {
            $updateData['balasan_admin'] = $teks;
            $updateData['dibalas_at']    = date('Y-m-d H:i:s');
            $updateData['status']        = 'selesai';
        }

        $m->update($inquiryId, $updateData);

        return redirect()->to($balik)->with('ok', 'Jawaban berhasil dikirim.');
    }

    /** Edit pertanyaan yang diajukan user / admin */
    public function editPertanyaan(int $id)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $m   = new InquiryModel();
        $inq = $m->find($id);
        if (! $inq) {
            throw PageNotFoundException::forPageNotFound();
        }

        $u = $this->user();
        $isAdmin = ($u['role'] ?? '') === 'admin';
        $isOwner = $u && (int) ($inq['user_id'] ?? 0) === (int) $u['id'];

        if (! $isAdmin && ! $isOwner) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengedit pertanyaan ini.');
        }

        $ok = $this->validate([
            'pertanyaan' => 'required|min_length[5]|max_length[1000]',
        ], [
            'pertanyaan' => ['required' => 'Pertanyaan tidak boleh kosong.', 'min_length' => 'Pertanyaan minimal 5 karakter.'],
        ]);
        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $m->update($id, [
            'pesan' => trim((string) $this->request->getPost('pertanyaan')),
        ]);

        return redirect()->back()->with('ok', 'Pertanyaan berhasil diperbarui.');
    }

    /** Hapus pertanyaan yang diajukan user / admin */
    public function hapusPertanyaan(int $id)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $m   = new InquiryModel();
        $inq = $m->find($id);
        if (! $inq) {
            throw PageNotFoundException::forPageNotFound();
        }

        $u = $this->user();
        $isAdmin = ($u['role'] ?? '') === 'admin';
        $isOwner = $u && (int) ($inq['user_id'] ?? 0) === (int) $u['id'];

        if (! $isAdmin && ! $isOwner) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus pertanyaan ini.');
        }

        $m->delete($id);

        return redirect()->back()->with('ok', 'Pertanyaan berhasil dihapus.');
    }

    /** Edit jawaban atas pertanyaan (bisa diedit oleh penulis jawaban atau admin) */
    public function editJawaban(int $inquiryId, string $answerId)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $m   = new InquiryModel();
        $inq = $m->find($inquiryId);
        if (! $inq) {
            throw PageNotFoundException::forPageNotFound();
        }

        $u       = $this->user();
        $isAdmin = ($u['role'] ?? '') === 'admin';
        $list    = json_decode($inq['jawaban'] ?? '[]', true) ?: [];

        $foundIndex = -1;
        foreach ($list as $idx => $ans) {
            if (($ans['id'] ?? '') === $answerId) {
                $foundIndex = $idx;
                break;
            }
        }

        if ($foundIndex === -1 && $answerId === 'admin_legacy' && ! empty($inq['balasan_admin'])) {
            $list[] = [
                'id'         => 'admin_legacy',
                'user_id'    => null,
                'nama'       => 'Pengelola Hotel / Admin',
                'role'       => 'admin',
                'isi'        => $inq['balasan_admin'],
                'created_at' => $inq['dibalas_at'] ?? $inq['updated_at'],
            ];
            $foundIndex = count($list) - 1;
        }

        if ($foundIndex === -1) {
            return redirect()->back()->with('error', 'Jawaban tidak ditemukan.');
        }

        $targetAns  = $list[$foundIndex];
        $isAuthor   = $u && (int) ($targetAns['user_id'] ?? 0) === (int) $u['id'];
        $isAnsAdmin = ($targetAns['role'] ?? '') === 'admin';

        if (! $isAuthor && ! ($isAdmin && $isAnsAdmin) && ! $isAdmin) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengedit jawaban ini.');
        }

        $ok = $this->validate([
            'jawaban' => 'required|min_length[3]|max_length[1500]',
        ], [
            'jawaban' => ['required' => 'Jawaban tidak boleh kosong.', 'min_length' => 'Jawaban minimal 3 karakter.'],
        ]);
        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $teks = trim((string) $this->request->getPost('jawaban'));
        $list[$foundIndex]['isi']        = $teks;
        $list[$foundIndex]['updated_at'] = date('Y-m-d H:i:s');

        $updateData = ['jawaban' => json_encode(array_values($list))];
        if ($isAdmin && ($isAnsAdmin || $targetAns['id'] === 'admin_legacy')) {
            $updateData['balasan_admin'] = $teks;
            $updateData['dibalas_at']    = date('Y-m-d H:i:s');
        }

        $m->update($inquiryId, $updateData);

        return redirect()->back()->with('ok', 'Jawaban berhasil diperbarui.');
    }

    /** Hapus jawaban atas pertanyaan (bisa dihapus oleh penulis jawaban atau admin) */
    public function hapusJawaban(int $inquiryId, string $answerId)
    {
        if ($r = $this->gerbangPost()) {
            return $r;
        }
        $m   = new InquiryModel();
        $inq = $m->find($inquiryId);
        if (! $inq) {
            throw PageNotFoundException::forPageNotFound();
        }

        $u       = $this->user();
        $isAdmin = ($u['role'] ?? '') === 'admin';
        $list    = json_decode($inq['jawaban'] ?? '[]', true) ?: [];

        $newList     = [];
        $deleted     = false;
        $wasAdminAns = false;

        foreach ($list as $ans) {
            if (($ans['id'] ?? '') === $answerId) {
                $isAuthor   = $u && (int) ($ans['user_id'] ?? 0) === (int) $u['id'];
                $isAnsAdmin = ($ans['role'] ?? '') === 'admin';
                if (! $isAuthor && ! $isAdmin) {
                    return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus jawaban ini.');
                }
                $deleted = true;
                if ($isAnsAdmin) {
                    $wasAdminAns = true;
                }
            } else {
                $newList[] = $ans;
            }
        }

        if ($answerId === 'admin_legacy' && $isAdmin) {
            $deleted     = true;
            $wasAdminAns = true;
        }

        if (! $deleted) {
            return redirect()->back()->with('error', 'Jawaban tidak ditemukan.');
        }

        $updateData = ['jawaban' => json_encode(array_values($newList))];
        if ($wasAdminAns) {
            $updateData['balasan_admin'] = null;
            $updateData['dibalas_at']    = null;
        }

        $m->update($inquiryId, $updateData);

        return redirect()->back()->with('ok', 'Jawaban berhasil dihapus.');
    }

    /** Tandai semua notifikasi jawaban sudah dibaca */
    public function bacaNotifikasi()
    {
        $u = $this->user();
        if ($u) {
            $now = date('Y-m-d H:i:s');
            db_connect()->table('users')->where('id', (int) $u['id'])->update(['notif_dibaca_at' => $now]);
            session()->set('notif_read_at', $now);
        }
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function pesan(int $id)
    {
        if ($r = $this->gerbangPost(false)) {
            return $r;
        }
        $hotel = $this->hotelAtau404($id);
        $balik = site_url('penginapan/' . $hotel['slug']) . '#tanya';

        if (trim((string) $this->request->getPost('website')) !== '') { // kolom jebakan bot
            return redirect()->to($balik)->with('ok', 'Pesanmu terkirim.');
        }

        $ok = $this->validate([
            'nama'         => 'required|max_length[100]',
            'email'        => 'required|valid_email|max_length[190]',
            'telepon'      => 'permit_empty|max_length[30]',
            'tgl_checkin'  => 'permit_empty|valid_date[Y-m-d]',
            'tgl_checkout' => 'permit_empty|valid_date[Y-m-d]',
            'jumlah_tamu'  => 'permit_empty|integer|greater_than[0]|less_than[21]',
            'pesan'        => 'permit_empty|max_length[1000]',
        ]);
        if (! $ok) {
            return redirect()->to($balik)->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $in  = $this->nullable($this->request->getPost('tgl_checkin'));
        $out = $this->nullable($this->request->getPost('tgl_checkout'));
        if ($in && $out && $out <= $in) {
            return redirect()->to($balik)->withInput()->with('error', 'Tanggal check-out harus setelah check-in.');
        }

        (new InquiryModel())->insert([
            'hotel_id'     => $id,
            'user_id'      => $this->user()['id'] ?? null,
            'nama'         => trim((string) $this->request->getPost('nama')),
            'email'        => strtolower(trim((string) $this->request->getPost('email'))),
            'telepon'      => $this->nullable($this->request->getPost('telepon')),
            'tgl_checkin'  => $in,
            'tgl_checkout' => $out,
            'jumlah_tamu'  => $this->nullable($this->request->getPost('jumlah_tamu')),
            'pesan'        => $this->nullable($this->request->getPost('pesan')),
            'status'       => 'baru',
        ]);

        return redirect()->to($balik)->with('ok', 'Pesanmu sudah terkirim. Admin akan menghubungi lewat email.');
    }
}
