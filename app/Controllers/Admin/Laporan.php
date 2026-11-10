<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\HotelModel;
use App\Models\InquiryModel;

class Laporan extends AppBase
{
    public function hotel()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }

        $db     = db_connect();
        $hotels = $db->table('hotels')->orderBy('peringkat', 'ASC')->get()->getResultArray();

        $stats = [
            'total'       => count($hotels),
            'sarapan'     => count(array_filter($hotels, fn ($h) => in_array($h['sarapan'], ['Ya', 'All-in']))),
            'terkoordinat'=> count(array_filter($hotels, fn ($h) => ! empty($h['latitude']))),
            'berating'    => count(array_filter($hotels, fn ($h) => ! empty($h['rating']))),
        ];

        return view('admin/laporan/hotel', [
            'title'  => 'Laporan Rekapitulasi Data Hotel — Singgah Medan',
            'hotels' => $hotels,
            'stats'  => $stats,
            'tgl'    => date('d F Y, H:i'),
        ]);
    }

    public function pesan()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }

        $db = db_connect();
        $pesan = $db->table('inquiries')
            ->select('inquiries.*, hotels.nama AS hotel_nama')
            ->join('hotels', 'hotels.id = inquiries.hotel_id', 'left')
            ->orderBy('inquiries.created_at', 'DESC')
            ->get()->getResultArray();

        $stats = [
            'total'    => count($pesan),
            'baru'     => count(array_filter($pesan, fn ($p) => $p['status'] === 'baru')),
            'diproses' => count(array_filter($pesan, fn ($p) => $p['status'] === 'diproses')),
            'selesai'  => count(array_filter($pesan, fn ($p) => $p['status'] === 'selesai')),
        ];

        return view('admin/laporan/pesan', [
            'title' => 'Laporan Rekapitulasi Pesan & Reservasi Tamu — Singgah Medan',
            'pesan' => $pesan,
            'stats' => $stats,
            'tgl'   => date('d F Y, H:i'),
        ]);
    }
}

