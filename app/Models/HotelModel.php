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
        'latitude', 'longitude',
    ];
    protected $useTimestamps = true;

    /** Terapkan filter dari query string (dipakai web & API). */
    public function filtered(array $f): static
    {
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
            case 'rating':
                $this->orderBy('rating DESC NULLS LAST, jumlah_ulasan DESC NULLS LAST', '', false);
                break;
            case 'ulasan':
                $this->orderBy('jumlah_ulasan DESC NULLS LAST', '', false);
                break;
            case 'nama':
                $this->orderBy('nama', 'ASC');
                break;
            default:
                $this->orderBy('peringkat', 'ASC');
        }

        return $this;
    }

    public function stats(): array
    {
        return $this->db->table($this->table)
            ->select('COUNT(*) AS total, COUNT(rating) AS berating, ROUND(AVG(rating), 2) AS rata, COALESCE(SUM(jumlah_ulasan), 0) AS ulasan, COUNT(DISTINCT kecamatan) AS kecamatan')
            ->get()->getRowArray();
    }

    public function daftarKecamatan(): array
    {
        return $this->db->table($this->table)
            ->select('kecamatan, COUNT(*) AS jumlah')
            ->where('kecamatan IS NOT NULL', null, false)
            ->groupBy('kecamatan')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();
    }

    public function unggulan(int $n = 6): array
    {
        return $this->where('rating >=', 4.5)
            ->where('jumlah_ulasan >=', 100)
            ->orderBy('jumlah_ulasan', 'DESC')
            ->findAll($n);
    }

    public function serupa(array $hotel, int $n = 3): array
    {
        if (empty($hotel['kecamatan'])) {
            return [];
        }
        return $this->where('kecamatan', $hotel['kecamatan'])
            ->where('id !=', $hotel['id'])
            ->orderBy('rating DESC NULLS LAST', '', false)
            ->findAll($n);
    }
}
