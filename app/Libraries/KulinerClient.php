<?php

namespace App\Libraries;

/**
 * Mengambil data kuliner dari API kelompok lain.
 * Atur di .env:  kuliner.apiUrl = http://alamat-server-kelompok-kuliner
 * Endpoint yang diharapkan:  GET {apiUrl}/api/kuliner?kecamatan=Medan Petisah&limit=6
 * Respons:  {"data":[{"nama":"...","kategori":"...","alamat":"...","rating":4.5,"harga":"Rp 20-50rb","foto":"https://..."}]}
 */
class KulinerClient
{
    public function enabled(): bool
    {
        return trim((string) env('kuliner.apiUrl', '')) !== '';
    }

    public function byKecamatan(?string $kecamatan, int $limit = 6): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $key = 'kuliner_' . md5(($kecamatan ?? '') . '|' . $limit);
        $hit = cache($key);
        if (is_array($hit)) {
            return $hit;
        }

        try {
            $client = \Config\Services::curlrequest(['timeout' => 4, 'http_errors' => false]);
            $query  = ['limit' => $limit];
            if ($kecamatan) {
                $query['kecamatan'] = $kecamatan;
            }
            $res = $client->get(rtrim((string) env('kuliner.apiUrl'), '/') . '/api/kuliner', [
                'query'   => $query,
                'headers' => ['Accept' => 'application/json'],
            ]);
            if ($res->getStatusCode() !== 200) {
                throw new \RuntimeException('HTTP ' . $res->getStatusCode());
            }
            $json = json_decode((string) $res->getBody(), true);
            $rows = is_array($json) ? ($json['data'] ?? (array_is_list($json) ? $json : [])) : [];
            $out  = array_map([$this, 'normalize'], array_slice($rows, 0, $limit));
            cache()->save($key, $out, 600);

            return $out;
        } catch (\Throwable $e) {
            log_message('warning', 'API kuliner gagal: ' . $e->getMessage());
            cache()->save($key, [], 60); // jangan menunggu timeout berulang kali

            return [];
        }
    }

    private function normalize(array $r): array
    {
        $pick = static function (array $r, array $keys) {
            foreach ($keys as $k) {
                if (isset($r[$k]) && $r[$k] !== '') {
                    return $r[$k];
                }
            }
            return null;
        };

        $foto = (string) $pick($r, ['foto', 'gambar', 'image', 'photo_url']);

        return [
            'nama'     => (string) $pick($r, ['nama', 'name']),
            'kategori' => (string) $pick($r, ['kategori', 'category', 'jenis']),
            'alamat'   => (string) $pick($r, ['alamat', 'address']),
            'rating'   => $pick($r, ['rating']),
            'harga'    => (string) $pick($r, ['harga', 'harga_kisaran', 'price_range']),
            'foto'     => preg_match('#^https?://#i', $foto) ? $foto : '',
            'url'      => (string) $pick($r, ['url', 'link']),
        ];
    }
}
