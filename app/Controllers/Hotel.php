<?php

namespace App\Controllers;

use App\Libraries\KulinerClient;
use App\Models\HotelModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Hotel extends BaseController
{
    public function index()
    {
        helper('hotel');
        $m = new HotelModel();

        $f = [
            'q'          => trim((string) $this->request->getGet('q')),
            'kecamatan'  => (string) $this->request->getGet('kecamatan'),
            'sarapan'    => (string) $this->request->getGet('sarapan'),
            'rating_min' => (string) $this->request->getGet('rating_min'),
            'sort'       => (string) ($this->request->getGet('sort') ?: 'awal'),
        ];

        $hotels = $m->filtered($f)->paginate(12);

        return view('hotels/index', [
            'title'     => 'Daftar Penginapan di Medan — Singgah Medan',
            'f'         => $f,
            'hotels'    => $hotels,
            'total'     => $m->pager->getTotal(),
            'page'      => $m->pager->getCurrentPage(),
            'pages'     => $m->pager->getPageCount(),
            'kecamatan' => (new HotelModel())->daftarKecamatan(),
        ]);
    }

    public function show(string $slug)
    {
        helper('hotel');
        $m     = new HotelModel();
        $hotel = $m->where('slug', $slug)->first();
        if (! $hotel) {
            throw PageNotFoundException::forPageNotFound();
        }

        $kuliner = new KulinerClient();

        return view('hotels/show', [
            'title'          => $hotel['nama'] . ' — Singgah Medan',
            'h'              => $hotel,
            'serupa'         => $m->serupa($hotel, 3),
            'kulinerAktif'   => $kuliner->enabled(),
            'kuliner'        => $kuliner->byKecamatan($hotel['kecamatan'], 6),
        ]);
    }
}
