<?php

namespace App\Controllers;

use App\Models\HotelModel;
use App\Models\KulinerModel;

class Peta extends AppBase
{
    public function index()
    {
        $m  = new HotelModel();
        $km = new KulinerModel();

        $f = [
            'kecamatan' => (string) $this->request->getGet('kecamatan'),
            'q'         => trim((string) $this->request->getGet('q')),
            'kategori'  => (string) ($this->request->getGet('kategori') ?? 'semua'),
        ];

        // 1. Titik Hotel
        $titikHotel = [];
        if ($f['kategori'] !== 'kuliner') {
            $b = $m->where('latitude IS NOT NULL', null, false)->where('longitude IS NOT NULL', null, false);
            if ($f['kecamatan'] !== '') {
                $b->where('kecamatan', $f['kecamatan']);
            }
            if ($f['q'] !== '') {
                $b->groupStart()
                    ->like('nama', $f['q'], 'both', null, true)
                    ->orLike('alamat', $f['q'], 'both', null, true)
                ->groupEnd();
            }
            $rows = $b->select('id, nama, latitude, longitude, rating, jumlah_ulasan, sarapan, kecamatan, alamat, slug, foto')
                ->orderBy('peringkat')
                ->findAll();

            $titikHotel = array_map(static fn ($h) => [
                'id'        => (int) $h['id'],
                'tipe'      => 'hotel',
                'nama'      => $h['nama'],
                'lat'       => (float) $h['latitude'],
                'lng'       => (float) $h['longitude'],
                'rating'    => $h['rating'] !== null ? (float) $h['rating'] : null,
                'ulasan'    => $h['jumlah_ulasan'] !== null ? (int) $h['jumlah_ulasan'] : null,
                'sarapan'   => $h['sarapan'],
                'kecamatan' => $h['kecamatan'],
                'alamat'    => $h['alamat'],
                'foto'      => $h['foto'] ? foto_url($h['foto']) : '',
                'url'       => site_url('penginapan/' . $h['slug']),
            ], $rows);
        }

        // 2. Titik Kuliner (yang memiliki koordinat latitude & longitude)
        $titikKuliner = [];
        if ($f['kategori'] !== 'hotel') {
            $kb = $km->where('latitude IS NOT NULL', null, false)->where('longitude IS NOT NULL', null, false);
            if ($f['kecamatan'] !== '') {
                $kb->where('kecamatan', $f['kecamatan']);
            }
            if ($f['q'] !== '') {
                $kb->groupStart()
                    ->like('nama', $f['q'], 'both', null, true)
                    ->orLike('kategori', $f['q'], 'both', null, true)
                    ->orLike('alamat', $f['q'], 'both', null, true)
                ->groupEnd();
            }
            $kRows = $kb->select('id, nama, kategori, latitude, longitude, rating, harga, kecamatan, alamat, foto, url')
                ->orderBy('rating DESC NULLS LAST, nama ASC', '', false)
                ->findAll(300);

            $titikKuliner = array_map(static fn ($k) => [
                'id'        => (int) $k['id'],
                'tipe'      => 'kuliner',
                'nama'      => $k['nama'],
                'kategori'  => $k['kategori'],
                'lat'       => (float) $k['latitude'],
                'lng'       => (float) $k['longitude'],
                'rating'    => $k['rating'] !== null ? (float) $k['rating'] : null,
                'harga'     => $k['harga'],
                'kecamatan' => $k['kecamatan'],
                'alamat'    => $k['alamat'],
                'foto'      => $k['foto'] ?? '',
                'url'       => $k['url'] ?: '#',
            ], $kRows);
        }

        $titik = array_merge($titikHotel, $titikKuliner);

        $totalGeo = cache('total_geo');
        if (! is_numeric($totalGeo)) {
            $totalGeo = db_connect()->table('hotels')->where('latitude IS NOT NULL', null, false)->countAllResults();
            cache()->save('total_geo', $totalGeo, 600);
        }

        $totalKulinerGeo = db_connect()->table('kuliner')->where('latitude IS NOT NULL', null, false)->countAllResults();

        return view('peta', [
            'title'           => 'Peta Penginapan & Kuliner — Singgah Medan',
            'titik'           => $titik,
            'totalHotel'      => count($titikHotel),
            'totalKuliner'    => count($titikKuliner),
            'f'               => $f,
            'kecamatan'       => $m->daftarKecamatan(),
            'totalGeo'        => $totalGeo,
            'totalKulinerGeo' => $totalKulinerGeo,
        ]);
    }
}
