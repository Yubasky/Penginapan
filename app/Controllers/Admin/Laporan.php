<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;

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
}

