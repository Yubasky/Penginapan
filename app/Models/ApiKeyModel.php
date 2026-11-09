<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table         = 'api_keys';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nama', 'key_prefix', 'key_hash', 'aktif', 'last_used_at'];
    protected $useTimestamps = false;

    /** Membuat kunci baru. Kunci asli HANYA dikembalikan di sini (yang disimpan hanya hash-nya). */
    public function buat(string $nama): string
    {
        $key = 'sm_' . bin2hex(random_bytes(20));
        $this->insert([
            'nama'       => $nama,
            'key_prefix' => substr($key, 0, 8),
            'key_hash'   => hash('sha256', $key),
            'aktif'      => 'true',
        ]);

        return $key;
    }

    public function cek(string $key): ?array
    {
        if ($key === '') {
            return null;
        }
        $row = $this->where('key_hash', hash('sha256', $key))->where('aktif', 'true')->first();
        if ($row) {
            $this->db->table('api_keys')->where('id', $row['id'])->update(['last_used_at' => date('Y-m-d H:i:s')]);
        }

        return $row;
    }
}
