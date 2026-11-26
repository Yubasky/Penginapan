<?php

namespace App\Controllers\Api;

use App\Models\KulinerModel;
use CodeIgniter\HTTP\ResponseInterface;

class Kuliner extends ApiBase
{
    /** GET /api/kuliner — salinan data kuliner yang tersimpan di server kami (publik). */
    public function index(): ResponseInterface
    {
        $m = new KulinerModel();
        [$page, $limit] = $this->paging();
        $m->filtered(['q' => (string) $this->request->getGet('q'), 'kecamatan' => (string) $this->request->getGet('kecamatan')]);
        $total = $m->countAllResults(false);
        $rows  = $m->findAll($limit, ($page - 1) * $limit);

        return $this->json([
            'status' => 'ok',
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => (int) ceil($total / $limit)],
            'data'   => array_map(static fn ($r) => [
                'id' => $r['sumber_id'], 'nama' => $r['nama'], 'kategori' => $r['kategori'], 'alamat' => $r['alamat'],
                'kecamatan' => $r['kecamatan'], 'rating' => $r['rating'] !== null ? (float) $r['rating'] : null,
                'harga' => $r['harga'], 'foto' => $r['foto'], 'url' => $r['url'],
                'latitude'  => isset($r['latitude']) && $r['latitude'] !== null ? (float) $r['latitude'] : null,
                'longitude' => isset($r['longitude']) && $r['longitude'] !== null ? (float) $r['longitude'] : null,
            ], $rows),
        ]);
    }

    /**
     * POST /api/kuliner/push  (header X-API-KEY wajib)
     * Body JSON: {"data":[{...}]} atau langsung array. Kelompok kuliner bisa mendorong datanya ke sini.
     */
    public function push(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $json = json_decode((string) $this->request->getBody(), true);
        $rows = is_array($json) ? ($json['data'] ?? (array_is_list($json) ? $json : [])) : [];
        if (! is_array($rows) || $rows === []) {
            return $this->json(['status' => 'error', 'message' => 'Body JSON kosong atau format salah. Kirim {"data":[...]}.'], 422);
        }
        if (count($rows) > 500) {
            return $this->json(['status' => 'error', 'message' => 'Maksimal 500 data per permintaan.'], 422);
        }

        $klien = new \App\Libraries\KulinerClient();
        $m     = new KulinerModel();
        $db    = db_connect();
        $ok    = 0;
        $gagal = [];

        $db->transBegin();
        try {
            foreach ($rows as $i => $r) {
                $n = is_array($r) ? $klien->normalize($r) : ['nama' => ''];
                if ($n['nama'] === '') {
                    $gagal[] = ['index' => $i, 'alasan' => 'nama kosong'];
                    continue;
                }
                $m->upsert($n);
                $ok++;
            }

            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menyimpan batch data kuliner.'], 500);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Kuliner::push gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server saat memproses data kuliner.'], 500);
        }

        return $this->json(['status' => 'ok', 'diterima' => $ok, 'ditolak' => $gagal]);
    }
}
