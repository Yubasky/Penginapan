<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\HotelModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Hotels extends AppBase
{
    private function aturan(): array
    {
        return [
            'nama'             => 'required|max_length[255]',
            'alamat'           => 'required|max_length[500]',
            'kecamatan'        => 'permit_empty|max_length[80]',
            'rating'           => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
            'jumlah_ulasan'    => 'permit_empty|integer|greater_than_equal_to[0]',
            'sarapan'          => 'required|in_list[Tidak,Ya,All-in]',
            'link_tripadvisor' => 'permit_empty|valid_url_strict|max_length[1000]',
            'latitude'         => 'permit_empty|decimal|greater_than_equal_to[-90]|less_than_equal_to[90]',
            'longitude'        => 'permit_empty|decimal|greater_than_equal_to[-180]|less_than_equal_to[180]',
        ];
    }

    private function formView(?array $h, array $errors = [])
    {
        return view('admin/hotel_form', [
            'title'     => $h && ! empty($h['id']) ? 'Ubah Hotel' : 'Tambah Hotel',
            'h'         => $h ?? ['sarapan' => 'Tidak'],
            'errors'    => $errors,
            'kecamatan' => array_column((new HotelModel())->daftarKecamatan(), 'kecamatan'),
        ]);
    }

    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $m = new HotelModel();
        $q = trim((string) $this->request->getGet('q'));
        $m->filtered(['q' => $q, 'sort' => 'nama']);

        return view('admin/hotels', [
            'title'  => 'Kelola Hotel',
            'q'      => $q,
            'hotels' => $m->paginate(15),
            'total'  => $m->pager->getTotal(),
            'page'   => $m->pager->getCurrentPage(),
            'pages'  => $m->pager->getPageCount(),
        ]);
    }

    public function baru()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }

        return $this->formView(null);
    }

    public function ubah(int $id)
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $h = (new HotelModel())->find($id);
        if (! $h) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->formView($h);
    }

    public function simpan()
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $m  = new HotelModel();
        $id = (int) $this->request->getPost('id');
        $in = $this->request->getPost();

        if (! $this->validate($this->aturan())) {
            $in['id'] = $id ?: null;

            return $this->formView($in, $this->validator->getErrors());
        }

        $data = [
            'nama'             => trim($in['nama']),
            'alamat'           => trim($in['alamat']),
            'kecamatan'        => $this->nullable($in['kecamatan'] ?? null),
            'rating'           => $this->nullable($in['rating'] ?? null),
            'jumlah_ulasan'    => $this->nullable($in['jumlah_ulasan'] ?? null),
            'sarapan'          => $in['sarapan'],
            'link_tripadvisor' => $this->nullable($in['link_tripadvisor'] ?? null),
            'latitude'         => $this->nullable($in['latitude'] ?? null),
            'longitude'        => $this->nullable($in['longitude'] ?? null),
        ];

        $hotelLama = $id ? $m->find($id) : null;
        if ($id && ! $hotelLama) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 1. Olah Foto Sampul / Background Hotel (Base64 Cloud Supabase)
        if ($this->request->getPost('hapus_foto')) {
            $data['foto'] = null;
        } else {
            $data['foto'] = $hotelLama['foto'] ?? null;
        }

        $fileFoto = $this->request->getFile('foto');
        if ($fileFoto && $fileFoto->isValid() && ! $fileFoto->hasMoved()) {
            $ext  = strtolower($fileFoto->getClientExtension() ?: $fileFoto->guessExtension() ?: '');
            $mime = $fileFoto->getMimeType() ?: 'image/jpeg';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $data['foto'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fileFoto->getTempName()));
            }
        }

        // 2. Olah Foto Fasilitas Resmi Hotel dari Admin (Base64 Cloud Supabase)
        $facList = [];
        if ($hotelLama && ! empty($hotelLama['foto_fasilitas'])) {
            $existingFac = json_decode($hotelLama['foto_fasilitas'], true) ?: [];
            $hapusFac    = (array) $this->request->getPost('hapus_foto_fasilitas');
            foreach ($existingFac as $ef) {
                if (! in_array($ef['url'], $hapusFac, true)) {
                    $facList[] = $ef;
                }
            }
        }

        $filesFac = $this->request->getFileMultiple('foto_fasilitas');
        if ($filesFac) {
            foreach ($filesFac as $ff) {
                if ($ff->isValid() && ! $ff->hasMoved()) {
                    $ext  = strtolower($ff->getClientExtension() ?: $ff->guessExtension() ?: '');
                    $mime = $ff->getMimeType() ?: 'image/jpeg';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ff->getTempName()));
                        $facList[] = [
                            'url'  => $base64,
                            'type' => 'image',
                            'nama' => $ff->getClientName(),
                        ];
                    }
                }
            }
        }
        $data['foto_fasilitas'] = ! empty($facList) ? json_encode($facList) : null;

        if ($id) {
            $m->update($id, $data);
            $msg = 'Hotel diperbarui.';
        } else {
            $maks             = $this->maksPeringkat();
            $data['peringkat'] = $maks + 1;
            $data['slug']      = $this->slugUnik($data['nama'], $data['peringkat']);
            $m->insert($data);
            $msg = 'Hotel ditambahkan.';
        }

        return redirect()->to(site_url('admin/hotel'))->with('ok', $msg);
    }

    public function hapus(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        (new HotelModel())->delete($id);

        return redirect()->to(site_url('admin/hotel'))->with('ok', 'Hotel dihapus (beserta ulasan, favorit, dan pesan terkait).');
    }

    public function impor()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        if (strtolower($this->request->getMethod()) !== 'post') {
            return view('admin/impor', ['title' => 'Impor CSV Hotel']);
        }
        if (! $this->tokenOk()) {
            return redirect()->back()->with('error', 'Sesi form kedaluwarsa, coba lagi.');
        }
        $file = $this->request->getFile('csv');
        if (! $file || ! $file->isValid() || $file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Pilih file CSV (maks 5 MB).');
        }

        $fh   = fopen($file->getTempName(), 'r');
        $head = fgetcsv($fh, 0, ',', '"', '');
        if ($head && isset($head[0])) {
            $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]); // buang BOM Excel
        }
        if (! $head || ! in_array('nama', $head, true) || ! in_array('alamat', $head, true)) {
            fclose($fh);

            return redirect()->back()->with('error', 'Header CSV harus memuat kolom "nama" dan "alamat" (lihat data/hotels.csv sebagai contoh).');
        }

        $m      = new HotelModel();
        $baru   = $ubah = $lewat = 0;
        $maks   = $this->maksPeringkat();
        $angka  = ['peringkat', 'halaman', 'jumlah_ulasan', 'rating_ulasan', 'rating', 'latitude', 'longitude'];
        $kolom  = ['peringkat', 'halaman', 'nama', 'slug', 'rating', 'jumlah_ulasan', 'sarapan', 'reviewer', 'rating_ulasan', 'tanggal_ulasan', 'judul_ulasan', 'link_tripadvisor', 'alamat', 'kecamatan', 'latitude', 'longitude'];

        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            if (count($r) !== count($head)) {
                $lewat++;
                continue;
            }
            $row = array_combine($head, $r);
            $d   = [];
            foreach ($kolom as $k) {
                if (array_key_exists($k, $row)) {
                    $v    = $this->nullable($row[$k]);
                    $d[$k] = ($v !== null && in_array($k, $angka, true) && ! is_numeric($v)) ? null : $v;
                }
            }
            if (empty($d['nama']) || empty($d['alamat'])) {
                $lewat++;
                continue;
            }
            if (isset($d['sarapan']) && ! in_array($d['sarapan'], ['Tidak', 'Ya', 'All-in'], true)) {
                $d['sarapan'] = 'Tidak';
            }
            $d['sarapan'] = $d['sarapan'] ?? 'Tidak';

            $ada = ! empty($d['slug']) ? $m->where('slug', $d['slug'])->first() : $m->where('nama', $d['nama'])->first();
            if ($ada) {
                $m->update($ada['id'], $d);
                $ubah++;
            } else {
                $d['peringkat'] = $d['peringkat'] ?? ++$maks;
                $maks           = max($maks, (int) $d['peringkat']);
                $d['slug']      = $this->slugUnik($d['nama'], (int) $d['peringkat'], $d['slug'] ?? null);
                $m->insert($d);
                $baru++;
            }
        }
        fclose($fh);

        return redirect()->to(site_url('admin/hotel'))->with('ok', "Impor selesai: {$baru} baru, {$ubah} diperbarui, {$lewat} dilewati.");
    }

    private function maksPeringkat(): int
    {
        return (int) (db_connect()->table('hotels')->selectMax('peringkat')->get()->getRowArray()['peringkat'] ?? 0);
    }

    private function slugUnik(string $nama, int $no, ?string $slug = null): string
    {
        $dasar = $slug ?: url_title($nama, '-', true) . '-' . $no;
        $s     = $dasar;
        $i     = 2;
        $m     = new HotelModel();
        while ($m->where('slug', $s)->first()) {
            $s = $dasar . '-' . $i++;
        }

        return $s;
    }
}
