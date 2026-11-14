<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateUploadsToBase64 extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Migrasi hotels.foto dan hotels.foto_fasilitas
        $hotels = $db->table('hotels')->get()->getResultArray();
        foreach ($hotels as $h) {
            $update = [];
            if (! empty($h['foto']) && str_starts_with($h['foto'], 'uploads/')) {
                $path = FCPATH . $h['foto'];
                if (is_file($path)) {
                    $mime = mime_content_type($path) ?: 'image/jpeg';
                    $update['foto'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                }
            }
            if (! empty($h['foto_fasilitas'])) {
                $fac = json_decode($h['foto_fasilitas'], true) ?: [];
                $facUpdated = false;
                foreach ($fac as &$fItem) {
                    if (! empty($fItem['url']) && str_starts_with($fItem['url'], 'uploads/')) {
                        $path = FCPATH . $fItem['url'];
                        if (is_file($path)) {
                            $mime = mime_content_type($path) ?: 'image/jpeg';
                            $fItem['url'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                            $facUpdated = true;
                        }
                    }
                }
                unset($fItem);
                if ($facUpdated) {
                    $update['foto_fasilitas'] = json_encode($fac);
                }
            }

            if ($update) {
                $db->table('hotels')->where('id', $h['id'])->update($update);
            }
        }

        // 2. Migrasi reviews.media
        $reviews = $db->table('reviews')->get()->getResultArray();
        foreach ($reviews as $rev) {
            if (! empty($rev['media'])) {
                $media = json_decode($rev['media'], true) ?: [];
                $medUpdated = false;
                foreach ($media as &$mItem) {
                    if (! empty($mItem['url']) && str_starts_with($mItem['url'], 'uploads/')) {
                        $path = FCPATH . $mItem['url'];
                        if (is_file($path)) {
                            $mime = mime_content_type($path) ?: 'image/jpeg';
                            $mItem['url'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                            $medUpdated = true;
                        }
                    }
                }
                unset($mItem);
                if ($medUpdated) {
                    $db->table('reviews')->where('id', $rev['id'])->update(['media' => json_encode($media)]);
                }
            }
        }

        // 3. Sinkronkan rating untuk hotel-hotel yang memiliki ulasan di reviews
        $hotelIdsWithReviews = $db->table('reviews')->select('hotel_id')->distinct()->get()->getResultArray();
        $hotelModel = new \App\Models\HotelModel();
        foreach ($hotelIdsWithReviews as $rRow) {
            $hotelModel->sinkronRating((int) $rRow['hotel_id']);
        }
    }

    public function down()
    {
    }
}

