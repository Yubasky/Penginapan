<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run()
    {
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
            $row['sarapan']    = $row['sarapan'] ?? 'Tidak';
            $row['created_at'] = date('Y-m-d H:i:s');
            $rows[]            = $row;
        }
        fclose($fh);

        $this->db->table('hotels')->truncate();
        $this->db->table('hotels')->insertBatch($rows);
    }
}
