<?php

namespace App\Controllers;

use App\Models\HotelModel;

class Peta extends AppBase
{
    public function index()
    {
        $m = new HotelModel();
        $f = [
            'kecamatan' => (string) $this->request->getGet('kecamatan'),
            'q'         => trim((string) $this->request->getGet('q')),
        ];
        $b = $m->where('latitude IS NOT NULL', null, false);
        if ($f['kecamatan'] !== '') {
            $b->where('kecamatan', $f['kecamatan']);
        }
        if ($f['q'] !== '') {
            $b->groupStart()
                ->like('nama', $f['q'], 'both', null, true)
                ->orLike('alamat', $f['q'], 'both', null, true)
            ->groupEnd();
        }
        $rows  = $b->orderBy('peringkat')->findAll();
        $titik = array_map(static fn ($h) => [
            'nama'      => $h['nama'],
            'lat'       => (float) $h['latitude'],
            'lng'       => (float) $h['longitude'],
            'rating'    => $h['rating'] !== null ? (float) $h['rating'] : null,
            'kecamatan' => $h['kecamatan'],
            'alamat'    => $h['alamat'],
            'url'       => site_url('penginapan/' . $h['slug']),
        ], $rows);

        return view('peta', [
            'title'     => 'Peta Penginapan — Singgah Medan',
            'titik'     => $titik,
            'f'         => $f,
            'kecamatan' => $m->daftarKecamatan(),
            'totalGeo'  => db_connect()->table('hotels')->where('latitude IS NOT NULL', null, false)->countAllResults(),
        ]);
    }
}
