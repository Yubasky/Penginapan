<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRatingAwalToHotels extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Tambah kolom rating_awal dan jumlah_ulasan_awal jika belum ada
        if (! $db->fieldExists('rating_awal', 'hotels')) {
            $this->forge->addColumn('hotels', [
                'rating_awal'        => ['type' => 'NUMERIC', 'constraint' => '2,1', 'null' => true],
                'jumlah_ulasan_awal' => ['type' => 'INT', 'null' => true],
            ]);
        }

        // 2. Baca data/hotels.csv untuk mengisi baseline data awal
        $csvPath = ROOTPATH . 'data/hotels.csv';
        if (is_file($csvPath) && ($fh = fopen($csvPath, 'r')) !== false) {
            $head = fgetcsv($fh, 0, ',', '"', '');
            while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
                $row = array_combine($head, $r);
                $id  = isset($row['peringkat']) ? (int) $row['peringkat'] : null;
                $slug = $row['slug'] ?? null;
                $baseRating = ($row['rating'] !== '' && $row['rating'] !== null) ? (float) $row['rating'] : null;
                $baseCount  = ($row['jumlah_ulasan'] !== '' && $row['jumlah_ulasan'] !== null) ? (int) $row['jumlah_ulasan'] : 0;

                $builder = $db->table('hotels');
                if ($slug) {
                    $builder->where('slug', $slug);
                } elseif ($id) {
                    $builder->where('id', $id);
                }
                $builder->update([
                    'rating_awal'        => $baseRating,
                    'jumlah_ulasan_awal' => $baseCount,
                ]);
            }
            fclose($fh);
        }

        // 3. Sinkronkan seluruh rating hotel dengan rumus gabungan data awal + ulasan pengguna
        $hotelModel = new \App\Models\HotelModel();
        $allHotels = $db->table('hotels')->select('id')->get()->getResultArray();
        foreach ($allHotels as $h) {
            $hotelModel->sinkronRating((int) $h['id']);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('rating_awal', 'hotels')) {
            $this->forge->dropColumn('hotels', ['rating_awal', 'jumlah_ulasan_awal']);
        }
    }
}

