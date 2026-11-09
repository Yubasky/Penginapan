<?php

namespace App\Controllers\Api;

use App\Models\HotelModel;
use CodeIgniter\HTTP\ResponseInterface;

/** Ekspor data lengkap — khusus pemegang kunci API. */
class Export extends ApiBase
{
    /** GET /api/export/hotels?format=json|csv */
    public function hotels(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $rows = (new HotelModel())->orderBy('peringkat')->findAll();
        if (strtolower((string) $this->request->getGet('format')) === 'csv') {
            $kolom = ['id', 'nama', 'slug', 'kecamatan', 'alamat', 'latitude', 'longitude', 'rating', 'jumlah_ulasan', 'sarapan', 'link_tripadvisor'];
            $fh    = fopen('php://temp', 'r+');
            fputcsv($fh, $kolom, ',', '"', '');
            foreach ($rows as $h) {
                fputcsv($fh, array_map(static fn ($k) => $h[$k] ?? '', $kolom), ',', '"', '');
            }
            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="hotel-medan.csv"')
                ->setHeader('Access-Control-Allow-Origin', '*')->setBody($csv);
        }

        return $this->json(['status' => 'ok', 'total' => count($rows), 'data' => array_map([Hotels::class, 'format'], $rows)]);
    }

    /** GET /api/export/ulasan — ulasan pengunjung situs kami. */
    public function ulasan(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $rows = db_connect()->table('reviews')
            ->select('reviews.id, reviews.hotel_id, hotels.nama AS hotel, reviews.rating, reviews.judul, reviews.isi, reviews.created_at, users.nama AS penulis')
            ->join('hotels', 'hotels.id = reviews.hotel_id')->join('users', 'users.id = reviews.user_id')
            ->orderBy('reviews.created_at', 'DESC')->get()->getResultArray();

        return $this->json(['status' => 'ok', 'total' => count($rows), 'data' => $rows]);
    }
}
