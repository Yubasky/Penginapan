<?php

namespace App\Controllers;

use App\Libraries\KulinerClient;
use App\Models\HotelModel;
use App\Models\KulinerModel;
use App\Models\ReviewModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Hotel extends AppBase
{
    public function index()
    {
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
        $m     = new HotelModel();
        $hotel = $m->where('slug', $slug)->first();
        if (! $hotel) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rm   = new ReviewModel();
        $user = $this->user();

        // Kuliner: pakai data hasil sinkron; kalau kosong, tanya API kelompok kuliner langsung.
        $kuliner = [];
        if (! empty($hotel['kecamatan'])) {
            $kuliner = (new KulinerModel())->where('kecamatan', $hotel['kecamatan'])
                ->orderBy('rating DESC NULLS LAST', '', false)->findAll(6);
        }
        $klien = new KulinerClient();
        if (! $kuliner) {
            $kuliner = $klien->byKecamatan($hotel['kecamatan'], 6);
        }

        $inquiries = (new \App\Models\InquiryModel())
            ->where('hotel_id', (int) $hotel['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        return view('hotels/show', [
            'title'          => $hotel['nama'] . ' — Singgah Medan',
            'h'              => $hotel,
            'serupa'         => $m->serupa($hotel, 3),
            'kulinerAktif'   => $klien->enabled(),
            'kuliner'        => $kuliner,
            'ulasan'         => $rm->forHotel((int) $hotel['id']),
            'ringkasan'      => $rm->ringkasan((int) $hotel['id']),
            'ulasanSaya'     => $user ? $rm->where('hotel_id', $hotel['id'])->where('user_id', $user['id'])->first() : null,
            'mediaFasilitas' => $rm->mediaForHotel((int) $hotel['id']),
            'inquiries'      => $inquiries,
        ]);
    }
}
