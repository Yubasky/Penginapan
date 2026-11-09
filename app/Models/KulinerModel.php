<?php

namespace App\Models;

use CodeIgniter\Model;

class KulinerModel extends Model
{
    protected $table         = 'kuliner';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['sumber', 'sumber_id', 'nama', 'kategori', 'alamat', 'kecamatan', 'rating', 'harga', 'foto', 'url'];
    protected $useTimestamps = false;

    /** Simpan/perbarui satu data kuliner (kunci: sumber + sumber_id). */
    public function upsert(array $r, string $sumber = 'kelompok-kuliner'): void
    {
        $rating = isset($r['rating']) && is_numeric($r['rating']) ? round(min(5, max(0, (float) $r['rating'])), 1) : null;
        $sid    = (string) ($r['id'] ?? '');
        if ($sid === '') {
            $sid = md5(($r['nama'] ?? '') . '|' . ($r['alamat'] ?? ''));
        }
        $nn = static fn ($v) => ($v === null || $v === '') ? null : (string) $v;

        $this->db->query(
            'INSERT INTO kuliner (sumber, sumber_id, nama, kategori, alamat, kecamatan, rating, harga, foto, url, synced_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON CONFLICT (sumber, sumber_id) DO UPDATE SET
               nama = EXCLUDED.nama, kategori = EXCLUDED.kategori, alamat = EXCLUDED.alamat,
               kecamatan = EXCLUDED.kecamatan, rating = EXCLUDED.rating, harga = EXCLUDED.harga,
               foto = EXCLUDED.foto, url = EXCLUDED.url, synced_at = NOW()',
            [
                $sumber, substr($sid, 0, 100), mb_substr((string) $r['nama'], 0, 255), $nn($r['kategori'] ?? null),
                $nn($r['alamat'] ?? null), $nn($r['kecamatan'] ?? null), $rating, $nn($r['harga'] ?? null),
                $nn($r['foto'] ?? null), $nn($r['url'] ?? null),
            ]
        );
    }

    public function filtered(array $f): static
    {
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

        return $this->orderBy('rating DESC NULLS LAST, nama ASC', '', false);
    }

    public function daftarKecamatan(): array
    {
        return $this->db->table($this->table)->select('kecamatan, COUNT(*) AS jumlah')
            ->where('kecamatan IS NOT NULL', null, false)->groupBy('kecamatan')->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();
    }
}
