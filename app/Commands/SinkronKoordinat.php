<?php

namespace App\Commands;

use App\Models\HotelModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SinkronKoordinat extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'hotel:sinkron-koordinat';
    protected $description = 'Sinkronkan koordinat dari hotels_geocoded_bersih.csv ke tabel hotels';

    public function run(array $params)
    {
        $path = ROOTPATH . 'data/hotels_geocoded_bersih.csv';
        if (! file_exists($path)) {
            CLI::write("File tidak ditemukan: {$path}", 'red');
            return;
        }

        $fh = fopen($path, 'r');
        $head = fgetcsv($fh, 0, ',', '"', '');
        if ($head && isset($head[0])) {
            $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]);
        }

        $m = new HotelModel();
        $diupdate = 0;
        $ditemukan = 0;

        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            if (count($r) !== count($head)) continue;
            $row = array_combine($head, $r);

            $nama = trim($row['nama'] ?? '');
            $lat = trim($row['latitude'] ?? '');
            $lng = trim($row['longitude'] ?? '');

            if ($nama === '' || $lat === '' || $lng === '') continue;

            // Cari hotel berdasarkan nama
            $hotel = $m->where('LOWER(TRIM(nama))', strtolower($nama))->first();
            if ($hotel) {
                $ditemukan++;
                $m->update($hotel['id'], [
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ]);
                $diupdate++;
            }
        }
        fclose($fh);

        // Bersihkan cache
        cache()->delete('stats_hotel');
        cache()->delete('daftar_kecamatan');

        CLI::write("Selesai!", 'green');
        CLI::write("Total hotel yang ditemukan dan diisi koordinatnya: {$diupdate}", 'green');
    }
}

