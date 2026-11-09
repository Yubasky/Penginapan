<?php

namespace App\Controllers;

use App\Models\HotelModel;

class Home extends AppBase
{
    public function index()
    {
        $m = new HotelModel();

        return view('home', [
            'title'     => 'Singgah Medan — Temukan penginapan terbaik di Medan',
            'stats'     => $m->stats(),
            'unggulan'  => $m->unggulan(6),
            'kecamatan' => $m->daftarKecamatan(),
        ]);
    }

    public function tentang()
    {
        return view('tentang', ['title' => 'Tentang & API — Singgah Medan']);
    }
}
