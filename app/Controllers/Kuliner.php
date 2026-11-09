<?php

namespace App\Controllers;

use App\Models\HotelModel;
use App\Models\KulinerModel;

class Kuliner extends AppBase
{
    public function index()
    {
        $m = new KulinerModel();
        $f = ['q' => trim((string) $this->request->getGet('q')), 'kecamatan' => (string) $this->request->getGet('kecamatan')];

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
