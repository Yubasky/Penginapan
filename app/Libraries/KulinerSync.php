<?php

namespace App\Libraries;

use App\Models\KulinerModel;

/** Menarik semua data kuliner dari API kelompok lain ke tabel lokal `kuliner`. */
class KulinerSync
{
    public function jalankan(): array
    {
        $client = new KulinerClient();
        if (! $client->enabled()) {
            return ['ok' => false, 'jumlah' => 0, 'pesan' => 'kuliner.apiUrl belum diisi di file .env'];
        }

        $model  = new KulinerModel();
        $total  = 0;
        $firstP = null;

        for ($p = 1; $p <= 100; $p++) {
            $res = $client->halaman($p, 50);
            if ($res === null) {
                $detail = $client->lastError();

                return [
                    'ok'     => false,
                    'jumlah' => $total,
                    'pesan'  => "Gagal menghubungi API kuliner (halaman {$p}). " . ($detail !== '' ? $detail : 'Cek alamat server dan koneksi.'),
                ];
            }
            $rows = $res['rows'];
            if ($rows === []) {
                break;
            }
            $first = $rows[0]['id'] . '|' . $rows[0]['nama'];
            if ($first === $firstP) { // server mengabaikan parameter page
                break;
            }
            $firstP = $first;

            foreach ($rows as $r) {
                if ($r['nama'] === '') {
                    continue;
                }
                $model->upsert($r);
                $total++;
            }
            $perPage = $res['per_page'] ?? 50;
            if (count($rows) < $perPage || ($res['total_pages'] !== null && $p >= $res['total_pages'])) {
                break;
            }
        }

        return ['ok' => true, 'jumlah' => $total, 'pesan' => "{$total} data kuliner berhasil disinkronkan."];
    }
}
