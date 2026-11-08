<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\HotelModel;
use CodeIgniter\HTTP\ResponseInterface;

class Hotels extends BaseController
{
    private function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept')
            ->setJSON($payload);
    }

    private function format(array $h): array
    {
        return [
            'id'               => (int) $h['id'],
            'nama'             => $h['nama'],
            'slug'             => $h['slug'],
            'kota'             => 'Medan',
            'kecamatan'        => $h['kecamatan'],
            'alamat'           => $h['alamat'],
            'rating'           => $h['rating'] !== null ? (float) $h['rating'] : null,
            'jumlah_ulasan'    => $h['jumlah_ulasan'] !== null ? (int) $h['jumlah_ulasan'] : null,
            'sarapan'          => $h['sarapan'],
            'url'              => site_url('penginapan/' . $h['slug']),
            'tripadvisor_url'  => $h['link_tripadvisor'],
            'ulasan_unggulan'  => $h['reviewer'] ? [
                'reviewer' => $h['reviewer'],
                'rating'   => $h['rating_ulasan'] !== null ? (int) $h['rating_ulasan'] : null,
                'tanggal'  => $h['tanggal_ulasan'],
                'judul'    => $h['judul_ulasan'],
            ] : null,
        ];
    }

    public function options(): ResponseInterface
    {
        return $this->json([], 204);
    }

    /** GET /api/hotels?q=&kecamatan=&sarapan=ya&rating_min=4&sort=rating|ulasan|nama&page=1&limit=20 */
    public function index(): ResponseInterface
    {
        $m = new HotelModel();
        $f = [
            'q'          => (string) $this->request->getGet('q'),
            'kecamatan'  => (string) $this->request->getGet('kecamatan'),
            'sarapan'    => (string) $this->request->getGet('sarapan'),
            'rating_min' => (string) $this->request->getGet('rating_min'),
            'sort'       => (string) $this->request->getGet('sort'),
        ];
        $limit = min(max((int) ($this->request->getGet('limit') ?: 20), 1), 100);
        $page  = max((int) $this->request->getGet('page'), 1);

        $m->filtered($f);
        $total = $m->countAllResults(false);
        $rows  = $m->findAll($limit, ($page - 1) * $limit);

        return $this->json([
            'status' => 'ok',
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => (int) ceil($total / $limit)],
            'data'   => array_map([$this, 'format'], $rows),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        $h = (new HotelModel())->find($id);
        if (! $h) {
            return $this->json(['status' => 'error', 'message' => 'Hotel tidak ditemukan'], 404);
        }

        return $this->json(['status' => 'ok', 'data' => $this->format($h)]);
    }

    /** GET /api/kecamatan — kunci gabung dengan data kuliner. */
    public function kecamatan(): ResponseInterface
    {
        return $this->json(['status' => 'ok', 'data' => (new HotelModel())->daftarKecamatan()]);
    }
}
