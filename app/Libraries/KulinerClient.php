<?php

namespace App\Libraries;

/**
 * Klien API kelompok kuliner (API v1).
 * .env:  kuliner.apiUrl = https://irk-charting-shrank.ngrok-free.dev   (atau .../api/v1)
 *        kuliner.apiKey = kunci-dari-kelompok-kuliner                  (dikirim di header X-API-KEY)
 */
class KulinerClient
{
    private string $lastError = '';

    public function enabled(): bool
    {
        return trim((string) env('kuliner.apiUrl', '')) !== '';
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    /** Mengembalikan origin server kuliner tanpa suffix /api/v1 atau /api. */
    public function baseOrigin(): string
    {
        $url = rtrim(trim((string) env('kuliner.apiUrl', '')), '/');
        if ($url === '') {
            return '';
        }

        return (string) preg_replace('#/(api(/v\d+|/kuliner)?)$#i', '', $url);
    }

    private function get(string $path, array $query = []): ?array
    {
        $this->lastError = '';
        $origin          = $this->baseOrigin();
        if ($origin === '') {
            $this->lastError = 'kuliner.apiUrl belum diisi di file .env.';

            return null;
        }

        try {
            $headers = [
                'Accept'                     => 'application/json',
                'ngrok-skip-browser-warning' => '1',
            ];
            $key = trim((string) env('kuliner.apiKey', ''));
            if ($key !== '') {
                $headers['X-API-KEY'] = $key;
            }

            $client = \Config\Services::curlrequest([
                'timeout'         => 10,
                'connect_timeout' => 5,
                'http_errors'     => false,
            ]);
            $res = $client->get($origin . '/api/v1/' . ltrim($path, '/'), [
                'query'   => $query,
                'headers' => $headers,
            ]);

            $status = $res->getStatusCode();
            $body   = (string) $res->getBody();
            $json   = json_decode($body, true);

            if ($status !== 200) {
                $msg = is_array($json) && ! empty($json['message']) ? (string) $json['message'] : '';
                if ($status === 401) {
                    $this->lastError = 'HTTP 401 (Unauthorized): ' . ($msg ?: 'API Key belum diisi atau salah di .env (kuliner.apiKey).');
                } else {
                    $this->lastError = "HTTP {$status}" . ($msg !== '' ? ": {$msg}" : '');
                }

                throw new \RuntimeException($this->lastError);
            }

            if (! is_array($json)) {
                $this->lastError = 'Respons server kuliner bukan JSON valid.';

                return null;
            }

            return $json;
        } catch (\Throwable $e) {
            if ($this->lastError === '') {
                $this->lastError = $e->getMessage();
            }
            log_message('warning', 'API kuliner gagal: ' . $this->lastError);

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
        $limit = min(max($limit, 1), 50);
        $key   = 'kuliner_' . md5(($kecamatan ?? '') . '|' . $limit);
        $hit   = cache($key);
        if (is_array($hit)) {
            return $hit;
        }
        $query = ['per_page' => $limit];
        if ($kecamatan) {
            $query['q'] = $kecamatan;
        }
        $json = $this->get('restoran', $query);
        $out  = $json === null ? [] : array_slice($this->rows($json), 0, $limit);
        cache()->save($key, $out, $json === null ? 60 : 600);

        return $out;
    }

    /** Ambil kuliner terdekat dari koordinat lat & lng melalui /api/v1/restoran/terdekat. */
    public function nearby(float $lat, float $lng, int $limit = 6, float $radiusKm = 3.0): array
    {
        if (! $this->enabled()) {
            return [];
        }
        $limit = min(max($limit, 1), 50);
        $key   = 'kuliner_nearby_' . md5(round($lat, 3) . '|' . round($lng, 3) . '|' . $limit);
        $hit   = cache($key);
        if (is_array($hit)) {
            return $hit;
        }
        $json = $this->get('restoran/terdekat', [
            'lat'    => $lat,
            'lng'    => $lng,
            'radius' => $radiusKm,
            'limit'  => $limit,
        ]);
        $out = $json === null ? [] : array_slice($this->rows($json), 0, $limit);
        cache()->save($key, $out, $json === null ? 60 : 600);

        return $out;
    }

    /** Untuk sinkronisasi daftar restoran: null = gagal. */
    public function halaman(int $page, int $limit = 50): ?array
    {
        $perPage = min(max($limit, 1), 50);
        $json    = $this->get('restoran', ['page' => $page, 'per_page' => $perPage]);
        if ($json === null) {
            return null;
        }

        return [
            'rows'        => $this->rows($json),
            'total_pages' => isset($json['meta']['total_pages']) ? (int) $json['meta']['total_pages'] : null,
            'per_page'    => isset($json['meta']['per_page']) ? (int) $json['meta']['per_page'] : $perPage,
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

        $origin  = $this->baseOrigin();
        $rawFoto = trim((string) ($pick($r, ['foto', 'gambar', 'image', 'photo_url']) ?? ''));
        if ($rawFoto !== '' && preg_match('#^https?://#i', $rawFoto)) {
            $foto = $rawFoto;
        } elseif ($rawFoto !== '' && $origin !== '') {
            $foto = $origin . '/' . ltrim($rawFoto, '/');
        } else {
            $foto = '';
        }

        $rawUrl = trim((string) ($pick($r, ['url', 'link']) ?? ''));
        if ($rawUrl !== '' && preg_match('#^https?://#i', $rawUrl)) {
            $url = $rawUrl;
        } elseif (! empty($r['slug']) && $origin !== '') {
            $url = $origin . '/restoran/' . rawurlencode((string) $r['slug']);
        } else {
            $url = '';
        }

        $lokasi = is_array($r['lokasi'] ?? null) ? $r['lokasi'] : [];
        $lat    = $lokasi['lat'] ?? $lokasi['latitude'] ?? $pick($r, ['latitude', 'lat']);
        $lng    = $lokasi['lng'] ?? $lokasi['longitude'] ?? $pick($r, ['longitude', 'lng', 'lon', 'long']);

        $harga = '';
        if (is_array($r['harga'] ?? null)) {
            $min = isset($r['harga']['min']) && is_numeric($r['harga']['min']) ? (int) $r['harga']['min'] : null;
            $max = isset($r['harga']['max']) && is_numeric($r['harga']['max']) ? (int) $r['harga']['max'] : null;
            if ($min !== null && $max !== null) {
                $harga = 'Rp ' . number_format($min, 0, ',', '.') . ' - Rp ' . number_format($max, 0, ',', '.');
            } elseif ($min !== null) {
                $harga = 'Mulai Rp ' . number_format($min, 0, ',', '.');
            } elseif ($max !== null) {
                $harga = 'Hingga Rp ' . number_format($max, 0, ',', '.');
            }
        } else {
            $harga = (string) ($pick($r, ['harga', 'harga_kisaran', 'price_range']) ?? '');
        }

        $rating = $pick($r, ['rating', 'rating_web']);

        return [
            'id'        => (string) ($pick($r, ['id', 'slug']) ?? ''),
            'nama'      => trim((string) ($pick($r, ['nama', 'name']) ?? '')),
            'kategori'  => (string) ($pick($r, ['kategori', 'category', 'jenis', 'jenis_masakan']) ?? ''),
            'alamat'    => (string) ($pick($r, ['alamat', 'address']) ?? ''),
            'kecamatan' => (string) ($pick($r, ['kecamatan', 'district']) ?? ''),
            'rating'    => $rating,
            'harga'     => $harga,
            'foto'      => $foto,
            'url'       => $url,
            'latitude'  => ($lat !== null && is_numeric($lat)) ? (float) $lat : null,
            'longitude' => ($lng !== null && is_numeric($lng)) ? (float) $lng : null,
            'jarak'     => isset($r['jarak_km']) && is_numeric($r['jarak_km']) ? (float) $r['jarak_km'] : null,
        ];
    }
}
