<?php

namespace App\Controllers\Api;

use App\Models\HotelModel;
use CodeIgniter\HTTP\ResponseInterface;

class Hotels extends ApiBase
{
    public static function format(array $h): array
    {
        return [
            'id'              => (int) $h['id'],
            'nama'            => $h['nama'],
            'slug'            => $h['slug'],
            'kota'            => 'Medan',
            'kecamatan'       => $h['kecamatan'],
            'alamat'          => $h['alamat'],
            'latitude'        => isset($h['latitude']) && $h['latitude'] !== null ? (float) $h['latitude'] : null,
            'longitude'       => isset($h['longitude']) && $h['longitude'] !== null ? (float) $h['longitude'] : null,
            'rating'          => $h['rating'] !== null ? (float) $h['rating'] : null,
            'jumlah_ulasan'   => $h['jumlah_ulasan'] !== null ? (int) $h['jumlah_ulasan'] : null,
            'sarapan'         => $h['sarapan'],
            'url'             => site_url('penginapan/' . $h['slug']),
            'tripadvisor_url' => $h['link_tripadvisor'],
            'ulasan_unggulan' => $h['reviewer'] ? [
                'reviewer' => $h['reviewer'],
                'rating'   => $h['rating_ulasan'] !== null ? (int) $h['rating_ulasan'] : null,
                'tanggal'  => $h['tanggal_ulasan'],
                'judul'    => $h['judul_ulasan'],
            ] : null,
        ];
    }

    /** GET /api/hotels?q=&kecamatan=&sarapan=ya&rating_min=4&sort=rating|ulasan|nama&page=1&limit=20 */
    public function index(): ResponseInterface
    {
        $m = new HotelModel();
        [$page, $limit] = $this->paging();

        $m->filtered($this->filterDari());
        $total = $m->countAllResults(false);
        $rows  = $m->findAll($limit, ($page - 1) * $limit);

        return $this->json([
            'status' => 'ok',
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => (int) ceil($total / $limit)],
            'data'   => array_map([self::class, 'format'], $rows),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $h = (new HotelModel())->find($id);
        if (! $h) {
            return $this->json(['status' => 'error', 'message' => 'Hotel tidak ditemukan'], 404);
        }

        return $this->json(['status' => 'ok', 'data' => self::format($h)]);
    }

    /** GET /api/kecamatan — kunci gabung dengan data kuliner. */
    public function kecamatan(): ResponseInterface
    {
        return $this->json(['status' => 'ok', 'data' => (new HotelModel())->daftarKecamatan()]);
    }
}
