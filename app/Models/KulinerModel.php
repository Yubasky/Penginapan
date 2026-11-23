<?php

namespace App\Models;

use CodeIgniter\Model;

class KulinerModel extends Model
{
    protected $table         = 'kuliner';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'sumber', 'sumber_id', 'nama', 'kategori', 'alamat', 'kecamatan',
        'rating', 'harga', 'foto', 'url', 'latitude', 'longitude',
    ];
    protected $useTimestamps = false;

    /** Simpan/perbarui satu data kuliner (kunci: sumber + sumber_id). */
    public function upsert(array $r, string $sumber = 'kelompok-kuliner'): void
    {
        $rating = isset($r['rating']) && is_numeric($r['rating']) ? round(min(5, max(0, (float) $r['rating'])), 1) : null;
        $sid    = (string) ($r['id'] ?? '');
        if ($sid === '') {
            $sid = md5(($r['nama'] ?? '') . '|' . ($r['alamat'] ?? ''));
        }
        $nn  = static fn ($v) => ($v === null || $v === '') ? null : (string) $v;
        $lat = isset($r['latitude']) && is_numeric($r['latitude']) ? (float) $r['latitude'] : (isset($r['lat']) && is_numeric($r['lat']) ? (float) $r['lat'] : null);
        $lng = isset($r['longitude']) && is_numeric($r['longitude']) ? (float) $r['longitude'] : (isset($r['lng']) && is_numeric($r['lng']) ? (float) $r['lng'] : null);

        $this->db->query(
            'INSERT INTO kuliner (sumber, sumber_id, nama, kategori, alamat, kecamatan, rating, harga, foto, url, latitude, longitude, synced_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON CONFLICT (sumber, sumber_id) DO UPDATE SET
               nama = EXCLUDED.nama, kategori = EXCLUDED.kategori, alamat = EXCLUDED.alamat,
               kecamatan = EXCLUDED.kecamatan, rating = EXCLUDED.rating, harga = EXCLUDED.harga,
               foto = EXCLUDED.foto, url = EXCLUDED.url, latitude = EXCLUDED.latitude,
               longitude = EXCLUDED.longitude, synced_at = NOW()',
            [
                $sumber, substr($sid, 0, 100), mb_substr((string) $r['nama'], 0, 255), $nn($r['kategori'] ?? null),
                $nn($r['alamat'] ?? null), $nn($r['kecamatan'] ?? null), $rating, $nn($r['harga'] ?? null),
                $nn($r['foto'] ?? null), $nn($r['url'] ?? null), $lat, $lng,
            ]
        );
    }

    public function filtered(array $f): static
    {
        $userLat = isset($f['lat']) && is_numeric($f['lat']) ? (float) $f['lat'] : null;
        $userLng = isset($f['lng']) && is_numeric($f['lng']) ? (float) $f['lng'] : null;

        if ($userLat !== null && $userLng !== null) {
            $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians({$userLat})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$userLng})) + sin(radians({$userLat})) * sin(radians(latitude))))))";
            $this->select("kuliner.*, CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN {$haversine} ELSE NULL END AS jarak", false);
        } else {
            $this->select('kuliner.*');
        }

        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $this->groupStart()
                ->like('nama', $q, 'both', null, true)
                ->orLike('kategori', $q, 'both', null, true)
                ->orLike('alamat', $q, 'both', null, true)
                ->groupEnd();
        }
        if (! empty($f['kecamatan'])) {
            $this->where('kecamatan', $f['kecamatan']);
        }

        if (($f['sort'] ?? '') === 'terdekat' && $userLat !== null && $userLng !== null) {
            return $this->orderBy('jarak ASC NULLS LAST, rating DESC NULLS LAST, nama ASC', '', false);
        }

        return $this->orderBy('rating DESC NULLS LAST, nama ASC', '', false);
    }

    /** Ambil tempat kuliner terdekat dari koordinat tertentu (misal: koordinat hotel atau lokasi user). */
    public function terdekatDari(float $lat, float $lng, int $limit = 6, ?string $kecamatan = null): array
    {
        $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians({$lat})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$lng})) + sin(radians({$lat})) * sin(radians(latitude))))))";

        $results = $this->select("kuliner.*, {$haversine} AS jarak", false)
            ->where('latitude IS NOT NULL', null, false)
            ->where('longitude IS NOT NULL', null, false)
            ->orderBy('jarak', 'ASC')
            ->findAll($limit);

        // Jika tempat kuliner berkoordinat masih kurang dari limit dan ada info kecamatan, lengkapi dengan kuliner sekecamatan
        if (count($results) < $limit && ! empty($kecamatan)) {
            $ids = array_column($results, 'id');
            $fallback = $this->where('kecamatan', $kecamatan);
            if (! empty($ids)) {
                $fallback->whereNotIn('id', $ids);
            }
            $extra = $fallback->orderBy('rating DESC NULLS LAST', '', false)->findAll($limit - count($results));
            $results = array_merge($results, $extra);
        }

        return $results;
    }

    public function daftarKecamatan(): array
    {
        return $this->db->table($this->table)->select('kecamatan, COUNT(*) AS jumlah')
            ->where('kecamatan IS NOT NULL', null, false)->groupBy('kecamatan')->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();
    }
}
