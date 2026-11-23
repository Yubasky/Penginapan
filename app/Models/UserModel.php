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
}
