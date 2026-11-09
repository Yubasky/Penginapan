<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Menulis semua hotel (termasuk latitude/longitude) ke CSV untuk dibagikan ke kelompok lain. */
class EksporHotel extends BaseCommand
{
    protected $group       = 'Singgah';
    protected $name        = 'hotel:ekspor';
    protected $description = 'Ekspor hotel + koordinat ke data/hotels_koordinat.csv.';

    public function run(array $params)
    {
        $rows = db_connect()->table('hotels')
            ->select('id, nama, slug, kecamatan, alamat, latitude, longitude, rating, jumlah_ulasan, sarapan, link_tripadvisor')
            ->orderBy('peringkat')->get()->getResultArray();

        $path = ROOTPATH . 'data/hotels_koordinat.csv';
        $fh   = fopen($path, 'w');
        fwrite($fh, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
        fputcsv($fh, array_keys($rows[0] ?? []), ',', '"', '');
        $punya = 0;
        foreach ($rows as $r) {
            fputcsv($fh, $r, ',', '"', '');
            $punya += $r['latitude'] !== null ? 1 : 0;
        }
        fclose($fh);

        CLI::write(count($rows) . " hotel ditulis ke {$path} ({$punya} sudah punya koordinat).", 'green');
        if ($punya < count($rows)) {
            CLI::write('Jalankan "php spark hotel:geocode" dulu bila banyak yang kosong, lalu ekspor lagi.', 'yellow');
        }
    }
}
