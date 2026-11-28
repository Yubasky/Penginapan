<?php

namespace App\Models;

use CodeIgniter\Model;

class HotelModel extends Model
{
    protected $table         = 'hotels';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'peringkat', 'halaman', 'nama', 'slug', 'rating', 'jumlah_ulasan', 'sarapan', 'reviewer',
        'rating_ulasan', 'tanggal_ulasan', 'judul_ulasan', 'link_tripadvisor', 'alamat', 'kecamatan',
        'latitude', 'longitude', 'foto', 'foto_fasilitas', 'rating_awal', 'jumlah_ulasan_awal',
    ];
    protected $useTimestamps = true;

    /** Terapkan filter dari query string (dipakai web & API). */
    public function filtered(array $f): static
    {
        $userLat = isset($f['lat']) && is_numeric($f['lat']) ? (float) $f['lat'] : null;
        $userLng = isset($f['lng']) && is_numeric($f['lng']) ? (float) $f['lng'] : null;

        if ($userLat !== null && $userLng !== null) {
            $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians({$userLat})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$userLng})) + sin(radians({$userLat})) * sin(radians(latitude))))))";
            $this->select("id, peringkat, nama, slug, rating, jumlah_ulasan, sarapan, alamat, kecamatan, foto, latitude, longitude, updated_at, CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN {$haversine} ELSE NULL END AS jarak", false);
        } else {
            // Hanya ambil kolom penting agar tidak menarik data berat
            $this->select('id, peringkat, nama, slug, rating, jumlah_ulasan, sarapan, alamat, kecamatan, foto, latitude, longitude, updated_at');
        }

        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $this->groupStart()
                ->like('nama', $q, 'both', null, true)
                ->orLike('alamat', $q, 'both', null, true)
                ->groupEnd();
        }
        if (! empty($f['kecamatan'])) {
            $this->where('kecamatan', $f['kecamatan']);
        }
        if (($f['sarapan'] ?? '') === 'ya') {
            $this->whereIn('sarapan', ['Ya', 'All-in']);
        }
        if (! empty($f['rating_min']) && is_numeric($f['rating_min'])) {
            $this->where('rating >=', (float) $f['rating_min']);
        }

        switch ($f['sort'] ?? 'awal') {
            case 'terdekat':
                if ($userLat !== null && $userLng !== null) {
                    $this->orderBy('jarak ASC NULLS LAST, rating DESC NULLS LAST', '', false);
                } else {
                    $this->orderBy('rating DESC NULLS LAST', '', false);
                }
                break;
            case 'rating':
                $this->orderBy('rating DESC NULLS LAST, jumlah_ulasan DESC NULLS LAST', '', false);
                break;
            case 'ulasan':
                $this->orderBy('jumlah_ulasan DESC NULLS LAST', '', false);
                break;
            case 'nama':
                $this->orderBy('nama', 'ASC');
                break;
            case 'acak':
            case 'random':
                $seed = (int) ($f['seed'] ?? 0);
                if ($seed > 0) {
                    $this->orderBy("md5(CONCAT(id, '{$seed}'))", '', false);
                } else {
                    $this->orderBy('RANDOM()', '', false);
                }
                break;
            case 'awal':
            default:
                $this->orderBy('peringkat', 'ASC');
        }

        return $this;
    }

    public function stats(): array
    {
        $hit = cache('stats_hotel');
        if (is_array($hit)) {
            return $hit;
        }

        $data = $this->db->table($this->table)
            ->select('COUNT(*) AS total, COUNT(rating) AS berating, ROUND(AVG(rating), 2) AS rata, COALESCE(SUM(jumlah_ulasan), 0) AS ulasan, COUNT(DISTINCT kecamatan) AS kecamatan')
            ->get()->getRowArray() ?: [];

        cache()->save('stats_hotel', $data, 600);

        return $data;
    }

    public function daftarKecamatan(): array
    {
        $hit = cache('daftar_kecamatan');
        if (is_array($hit)) {
            return $hit;
        }

        $data = $this->db->table($this->table)
            ->select('kecamatan, COUNT(*) AS jumlah')
            ->where('kecamatan IS NOT NULL', null, false)
            ->groupBy('kecamatan')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray() ?: [];

        cache()->save('daftar_kecamatan', $data, 600);

        return $data;
    }

    public function unggulan(int $n = 6): array
    {
        return $this->select('id, peringkat, nama, slug, rating, jumlah_ulasan, sarapan, alamat, kecamatan, foto')
            ->where('rating >=', 4.5)
            ->where('jumlah_ulasan >=', 100)
            ->orderBy('jumlah_ulasan', 'DESC')
            ->findAll($n);
    }

    public function serupa(array $hotel, int $n = 3): array
    {
        if (empty($hotel['kecamatan'])) {
            return [];
        }
        return $this->select('id, peringkat, nama, slug, rating, jumlah_ulasan, sarapan, alamat, kecamatan, foto')
            ->where('kecamatan', $hotel['kecamatan'])
            ->where('id !=', $hotel['id'])
            ->orderBy('rating DESC NULLS LAST', '', false)
            ->findAll($n);
    }

    /** Sinkronisasi rating dan jumlah ulasan hotel secara dinamis dari gabungan data awal + ulasan pengguna. */
    public function sinkronRating(int $hotelId): void
    {
        $hotel = $this->find($hotelId);
        if (! $hotel) {
            return;
        }

        $db = \Config\Database::connect();
        $r = $db->table('reviews')
            ->select('COUNT(*) AS total_user, SUM(rating) AS sum_user_rating')
            ->where('hotel_id', $hotelId)
            ->get()
            ->getRowArray();

        $userCount = (int) ($r['total_user'] ?? 0);
        $userSum   = (float) ($r['sum_user_rating'] ?? 0);

        $baseRating = ($hotel['rating_awal'] !== null && $hotel['rating_awal'] !== '') ? (float) $hotel['rating_awal'] : null;
        $baseCount  = ($hotel['jumlah_ulasan_awal'] !== null && $hotel['jumlah_ulasan_awal'] !== '') ? (int) $hotel['jumlah_ulasan_awal'] : 0;

        $totalCount = $baseCount + $userCount;

        if ($totalCount > 0) {
            $baseScore   = ($baseRating !== null && $baseCount > 0) ? ($baseRating * $baseCount) : 0;
            $totalScore  = $baseScore + $userSum;
            $finalRating = round($totalScore / $totalCount, 1);
        } else {
            $finalRating = null;
        }

        $this->update($hotelId, [
            'rating'        => $finalRating,
            'jumlah_ulasan' => $totalCount,
        ]);

        self::hapusCache();
    }

    /** Hapus seluruh cache ringkasan hotel agar perubahan admin/rating langsung tampil. */
    public static function hapusCache(): void
    {
        $cache = cache();
        $cache->delete('stats_hotel');
        $cache->delete('daftar_kecamatan');
        $cache->delete('total_geo');
    }
}
