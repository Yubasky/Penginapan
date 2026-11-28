<?php

namespace App\Controllers;

use App\Libraries\KulinerClient;
use App\Models\HotelModel;
use App\Models\KulinerModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kuliner extends AppBase
{
    public function index()
    {
        $m = new KulinerModel();
        $latParam = $this->request->getGet('lat');
        $lngParam = $this->request->getGet('lng');
        $userLat  = ($latParam !== null && is_numeric($latParam)) ? (float) $latParam : null;
        $userLng  = ($lngParam !== null && is_numeric($lngParam)) ? (float) $lngParam : null;

        $sort = (string) ($this->request->getGet('sort') ?? '');
        if ($sort === '' && $userLat !== null && $userLng !== null) {
            $sort = 'terdekat';
        }

        $f = [
            'q'         => trim((string) $this->request->getGet('q')),
            'kecamatan' => (string) $this->request->getGet('kecamatan'),
            'lat'       => $userLat,
            'lng'       => $userLng,
            'sort'      => $sort,
        ];

        return view('kuliner/index', [
            'title'     => 'Kuliner di Medan — Singgah Medan',
            'f'         => $f,
            'items'     => $m->filtered($f)->paginate(12),
            'total'     => $m->pager->getTotal(),
            'page'      => $m->pager->getCurrentPage(),
            'pages'     => $m->pager->getPageCount(),
            'kecamatan' => (new KulinerModel())->daftarKecamatan(),
        ]);
    }

    /** Halaman detail restoran kuliner terintegrasi di dalam Singgah Medan. */
    public function show(string $idOrSumber)
    {
        $km = new KulinerModel();
        $k  = is_numeric($idOrSumber) ? $km->find((int) $idOrSumber) : null;
        if (! $k) {
            $k = $km->where('sumber_id', $idOrSumber)->first();
        }
        if (! $k) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Ambil detail lengkap (menu, deskripsi, telepon, ulasan) dari API Kelompok Kuliner bila server mereka aktif
        $klien  = new KulinerClient();
        $slug   = '';
        if (! empty($k['url'])) {
            $path = (string) parse_url((string) $k['url'], PHP_URL_PATH);
            $slug = trim((string) basename($path));
        }
        $liveDetail = $slug !== '' ? $klien->detail($slug) : null;
        $ulasan     = ! empty($k['sumber_id']) ? $klien->ulasan((string) $k['sumber_id']) : [];

        // Cari penginapan terdekat dari titik GPS atau kecamatan tempat makan ini
        $hm     = new HotelModel();
        $hotels = [];
        if (! empty($k['latitude']) && ! empty($k['longitude'])) {
            $hotels = $hm->terdekatDari((float) $k['latitude'], (float) $k['longitude'], 3, $k['kecamatan'] ?? null);
        } elseif (! empty($k['kecamatan'])) {
            $hotels = $hm->select('id, peringkat, nama, slug, rating, jumlah_ulasan, sarapan, alamat, kecamatan, foto, latitude, longitude')
                ->where('kecamatan', $k['kecamatan'])
                ->orderBy('rating DESC NULLS LAST', '', false)
                ->findAll(3);
        }

        return view('kuliner/show', [
            'title'      => $k['nama'] . ' — Kuliner Medan · Singgah Medan',
            'k'          => $k,
            'liveDetail' => $liveDetail,
            'ulasan'     => $ulasan,
            'hotels'     => $hotels,
        ]);
    }

    /** Pencarian gabungan hotel + kuliner. */
    public function cari()
    {
        $q = trim((string) $this->request->getGet('q'));
        $hotels = $kuliner = [];
        if ($q !== '') {
            $hotels  = (new HotelModel())->filtered(['q' => $q, 'sort' => 'rating'])->findAll(8);
            $kuliner = (new KulinerModel())->filtered(['q' => $q])->findAll(8);
        }

        return view('kuliner/cari', ['title' => 'Cari — Singgah Medan', 'q' => $q, 'hotels' => $hotels, 'kuliner' => $kuliner]);
    }
}
