<?php

namespace App\Libraries;

/**
 * Klien API kelompok kuliner.
 * .env:  kuliner.apiUrl = http://alamat-server-kuliner   (wajib)
 *        kuliner.apiKey = kunci-dari-kelompok-kuliner   (opsional, dikirim sebagai header X-API-KEY)
 * Endpoint yang diharapkan:  GET {apiUrl}/api/kuliner?kecamatan=..&page=1&limit=100
 * Respons: {"data":[{"id":1,"nama":"..","kategori":"..","alamat":"..","kecamatan":"..","rating":4.5,"harga":"..","foto":"https://..","url":"https://.."}],
 *           "meta":{"total_pages":3}}
 */
class KulinerClient
{
    public function enabled(): bool
    {
        return trim((string) env('kuliner.apiUrl', '')) !== '';
    }

    private function get(array $query): ?array
    {
        try {
            $headers = ['Accept' => 'application/json'];
            $key     = trim((string) env('kuliner.apiKey', ''));
            if ($key !== '') {
                $headers['X-API-KEY'] = $key;
            }
            $client = \Config\Services::curlrequest(['timeout' => 6, 'http_errors' => false]);
            $res    = $client->get(rtrim((string) env('kuliner.apiUrl'), '/') . '/api/kuliner', [
                'query'   => $query,
                'headers' => $headers,
            ]);
            if ($res->getStatusCode() !== 200) {
                throw new \RuntimeException('HTTP ' . $res->getStatusCode());
            }
            $json = json_decode((string) $res->getBody(), true);

            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            log_message('warning', 'API kuliner gagal: ' . $e->getMessage());

            return null;
        }
    }

    private function rows(array $json): array
    {
        $rows = $json['data'] ?? (array_is_list($json) ? $json : []);

        return array_values(array_filter(array_map(
            fn ($r) => is_array($r) ? $this->normalize($r) : null,
            is_array($rows) ? $rows : []
        )));
    }

    /** Dipakai halaman detail bila data lokal (hasil sinkron) belum ada. */
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
        $query = ['limit' => $limit];
        if ($kecamatan) {
            $query['kecamatan'] = $kecamatan;
        }
        $json = $this->get($query);
        $out  = $json === null ? [] : array_slice($this->rows($json), 0, $limit);
        cache()->save($key, $out, $json === null ? 60 : 600);

        return $out;
    }

    /** Untuk sinkronisasi: null = gagal. */
    public function halaman(int $page, int $limit = 100): ?array
    {
        $json = $this->get(['page' => $page, 'limit' => $limit]);
        if ($json === null) {
            return null;
        }

        return [
            'rows'        => $this->rows($json),
            'total_pages' => isset($json['meta']['total_pages']) ? (int) $json['meta']['total_pages'] : null,
        ];
    }

    public function normalize(array $r): array
    {
        $pick = static function (array $r, array $keys) {
            foreach ($keys as $k) {
                if (isset($r[$k]) && $r[$k] !== '' && ! is_array($r[$k])) {
                    return $r[$k];
                }
            }

            return null;
        };
        $foto = (string) $pick($r, ['foto', 'gambar', 'image', 'photo_url']);
        $url  = (string) $pick($r, ['url', 'link']);

        return [
            'id'        => (string) $pick($r, ['id', 'slug']),
            'nama'      => trim((string) $pick($r, ['nama', 'name'])),
            'kategori'  => (string) $pick($r, ['kategori', 'category', 'jenis']),
            'alamat'    => (string) $pick($r, ['alamat', 'address']),
            'kecamatan' => (string) $pick($r, ['kecamatan', 'district']),
            'rating'    => $pick($r, ['rating']),
            'harga'     => (string) $pick($r, ['harga', 'harga_kisaran', 'price_range']),
            'foto'      => preg_match('#^https?://#i', $foto) ? $foto : '',
            'url'       => preg_match('#^https?://#i', $url) ? $url : '',
        ];
    }
}
