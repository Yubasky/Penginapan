<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run()
    {
        if ($this->db->table('hotels')->countAllResults() > 0) {
            return;
        }

        $fh = fopen(ROOTPATH . 'data/hotels.csv', 'r');
        if (! $fh) {
            throw new \RuntimeException('data/hotels.csv tidak ditemukan');
        }
        $head = fgetcsv($fh, 0, ',', '"', '');
        $rows = [];
        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            $row = array_combine($head, $r);
            foreach ($row as $k => $v) {
                $row[$k] = $v === '' ? null : $v;
            }
            $row['sarapan']            = $row['sarapan'] ?? 'Tidak';
            $row['rating_awal']        = $row['rating'] ?? null;
            $row['jumlah_ulasan_awal'] = $row['jumlah_ulasan'] ?? 0;
            $row['created_at']         = date('Y-m-d H:i:s');
            $rows[]                    = $row;
        }
        fclose($fh);

        if (! empty($rows)) {
            $this->db->table('hotels')->insertBatch($rows);
        }
    }
}
