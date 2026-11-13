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
                if (in_array($ex['url'], $hapusMedia, true)) {
                    $realPath = FCPATH . $ex['url'];
                    if (is_file($realPath)) {
                        @unlink($realPath);
                    }
                } else {
                    $mediaList[] = $ex;
                }
            }
        }

        // Olah file foto/video yang baru diunggah
        $files = $this->request->getFileMultiple('media');
        if ($files) {
            $dir = FCPATH . 'uploads/reviews';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            foreach ($files as $file) {
                if ($file->isValid() && ! $file->hasMoved()) {
                    $mime = $file->getMimeType();
                    $ext  = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
                    $isImg = str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    $isVid = str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'quicktime']);

                    if (! $isImg && ! $isVid) {
                        return redirect()->back()->withInput()->with('error', 'Format berkas "' . esc($file->getClientName()) . '" tidak didukung. Harap unggah foto (JPG, PNG, WEBP) atau video (MP4, WEBM).');
                    }
                    if ($file->getSizeByUnit('mb') > 30) {
                        return redirect()->back()->withInput()->with('error', 'Berkas "' . esc($file->getClientName()) . '" terlalu besar (maksimal 30MB).');
                    }

                    $randomName = 'rev_' . $id . '_' . $uid . '_' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
                    $file->move($dir, $randomName);

                    $mediaList[] = [
                        'url'  => 'uploads/reviews/' . $randomName,
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
            $existing = json_decode($lama['media'] ?? '[]', true) ?: [];
            foreach ($existing as $ex) {
                if (! empty($ex['url']) && is_file(FCPATH . $ex['url'])) {
                    @unlink(FCPATH . $ex['url']);
                }
            }
            $m->delete($lama['id']);
        }

        return redirect()->to(site_url('penginapan/' . $hotel['slug']) . '#ulasan')->with('ok', 'Ulasanmu berhasil dihapus.');
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
