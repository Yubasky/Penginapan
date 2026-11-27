<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nama', 'email', 'password_hash', 'role', 'notif_dibaca_at',
        'no_hp', 'jenis_kelamin', 'tanggal_lahir', 'kota_asal'];
    protected $useTimestamps = true;

    /** Cari user berdasarkan email atau nama tanpa membedakan huruf besar/kecil. */
    public function findByLogin(string $login): ?array
    {
        $clean = mb_strtolower(trim($login));
        if ($clean === '') {
            return null;
        }

        return $this->groupStart()
            ->where('LOWER(email)', $clean)
            ->orWhere('LOWER(nama)', $clean)
            ->groupEnd()
            ->first();
    }
}
