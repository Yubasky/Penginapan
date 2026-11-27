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
            'foto'             => 'permit_empty|max_length[1000]',
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

        // 1. Link Foto Sampul / Background Hotel (URL Link)
        $fotoUrl = trim((string) $this->request->getPost('foto'));
        if ($this->request->getPost('hapus_foto')) {
            $data['foto'] = null;
        } else {
            $data['foto'] = $fotoUrl !== '' ? $fotoUrl : ($hotelLama['foto'] ?? null);
        }

        // 2. Link Foto Fasilitas Resmi Hotel dari Admin (1 baris 1 link URL)
        $rawFac = trim((string) $this->request->getPost('foto_fasilitas'));
        $facList = [];
        if ($rawFac !== '') {
            $lines = preg_split('/[\r\n]+/', $rawFac);
            foreach ($lines as $line) {
                $u = trim($line);
                if ($u !== '') {
                    $facList[] = [
                        'url'     => $u,
                        'type'    => 'image',
                        'caption' => 'Fasilitas ' . $data['nama'],
                    ];
                }
            }
        }
        $data['foto_fasilitas'] = ! empty($facList) ? json_encode($facList) : null;

        if ($id) {
            $m->update($id, $data);
            $msg = 'Hotel diperbarui.';
        } else {
            $maks                       = $this->maksPeringkat();
            $data['peringkat']          = $maks + 1;
            $data['slug']               = $this->slugUnik($data['nama'], $data['peringkat']);
            $data['rating_awal']        = $data['rating'];
            $data['jumlah_ulasan_awal'] = $data['jumlah_ulasan'] ?? 0;
            $m->insert($data);
            $msg = 'Hotel ditambahkan.';
        }

        $m->hapusCache();

        return redirect()->to(site_url('admin/hotel'))->with('ok', $msg);
    }

    public function hapus(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $m = new HotelModel();
        $m->delete($id);
        $m->hapusCache();

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
        $db     = db_connect();
        $baru   = $ubah = $lewat = 0;
        $maks   = $this->maksPeringkat();
        $angka  = ['peringkat', 'halaman', 'jumlah_ulasan', 'rating_ulasan', 'rating', 'latitude', 'longitude'];
        $kolom  = ['peringkat', 'halaman', 'nama', 'slug', 'rating', 'jumlah_ulasan', 'sarapan', 'reviewer', 'rating_ulasan', 'tanggal_ulasan', 'judul_ulasan', 'link_tripadvisor', 'alamat', 'kecamatan', 'latitude', 'longitude'];
        $batas  = ['nama' => 255, 'slug' => 255, 'kecamatan' => 80, 'reviewer' => 150, 'tanggal_ulasan' => 30];

        $db->transBegin();

        try {
            while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
                if (count($r) !== count($head)) {
                    $lewat++;
                    continue;
                }
                $row = array_combine($head, $r);
                $d   = [];
                foreach ($kolom as $k) {
                    if (array_key_exists($k, $row)) {
                        $v     = $this->nullable($row[$k]);
                        $d[$k] = ($v !== null && in_array($k, $angka, true) && ! is_numeric($v)) ? null : $v;
                    }
                }
                if (empty($d['nama']) || empty($d['alamat']) || mb_strlen($d['nama']) > $batas['nama']) {
                    $lewat++;
                    continue;
                }

                // Kosongkan nilai numerik yang di luar batas CHECK constraint
                if (isset($d['rating']) && ((float) $d['rating'] < 0 || (float) $d['rating'] > 5)) {
                    $d['rating'] = null;
                }
                if (isset($d['rating_ulasan']) && ((int) $d['rating_ulasan'] < 1 || (int) $d['rating_ulasan'] > 5)) {
                    $d['rating_ulasan'] = null;
                }
                if (isset($d['jumlah_ulasan']) && (int) $d['jumlah_ulasan'] < 0) {
                    $d['jumlah_ulasan'] = null;
                }
                if (isset($d['latitude']) && ((float) $d['latitude'] < -90 || (float) $d['latitude'] > 90)) {
                    $d['latitude'] = null;
                }
                if (isset($d['longitude']) && ((float) $d['longitude'] < -180 || (float) $d['longitude'] > 180)) {
                    $d['longitude'] = null;
                }

                // Lewati nilai teks opsional yang melebihi panjang kolom VARCHAR
                foreach ($batas as $col => $maxLen) {
                    if ($col !== 'nama' && isset($d[$col]) && mb_strlen((string) $d[$col]) > $maxLen) {
                        $d[$col] = null;
                    }
                }

                if (isset($d['sarapan']) && ! in_array($d['sarapan'], ['Tidak', 'Ya', 'All-in'], true)) {
                    $d['sarapan'] = 'Tidak';
                }
                $d['sarapan'] = $d['sarapan'] ?? 'Tidak';

                $ada = null;
                if (! empty($d['slug'])) {
                    $ada = $m->where('slug', $d['slug'])->first();
                }
                if (! $ada && ! empty($d['nama'])) {
                    $ada = $m->where('LOWER(TRIM(nama))', strtolower(trim($d['nama'])))->first();
                }

                if ($ada) {
                    $updateData = [];
                    foreach ($d as $col => $val) {
                        if ($val !== null && $val !== '') {
                            $updateData[$col] = $val;
                        }
                    }
                    if (! empty($updateData)) {
                        $m->update($ada['id'], $updateData);
                    }
                    $ubah++;
                } else {
                    $d['peringkat']          = $d['peringkat'] ?? ++$maks;
                    $maks                    = max($maks, (int) $d['peringkat']);
                    $d['slug']               = $this->slugUnik($d['nama'], (int) $d['peringkat'], $d['slug'] ?? null);
                    $d['rating_awal']        = $d['rating'] ?? null;
                    $d['jumlah_ulasan_awal'] = $d['jumlah_ulasan'] ?? 0;
                    $m->insert($d);
                    $baru++;
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                fclose($fh);

                return redirect()->back()->with('error', 'Impor CSV dibatalkan karena kesalahan database.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            fclose($fh);
            log_message('error', 'Admin\Hotels::impor gagal: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Impor CSV gagal dan seluruh perubahan dibatalkan.');
        }

        fclose($fh);
        $m->hapusCache();

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
