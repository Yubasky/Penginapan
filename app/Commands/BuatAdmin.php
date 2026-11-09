<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BuatAdmin extends BaseCommand
{
    protected $group       = 'Singgah';
    protected $name        = 'akun:admin';
    protected $description = 'Membuat akun admin baru (atau menjadikan email yang sudah ada sebagai admin).';
    protected $usage       = 'akun:admin [email]';

    public function run(array $params)
    {
        $email = strtolower(trim($params[0] ?? CLI::prompt('Email admin')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Email tidak valid.');
            return;
        }
        $db   = db_connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        $pw = CLI::prompt('Password (minimal 8 karakter)');
        if (strlen($pw) < 8) {
            CLI::error('Password terlalu pendek.');
            return;
        }

        if ($user) {
            $db->table('users')->where('id', $user['id'])->update([
                'role' => 'admin', 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            CLI::write("Akun {$email} diperbarui menjadi admin.", 'green');
            return;
        }

        $nama = CLI::prompt('Nama', 'Admin');
        $db->table('users')->insert([
            'nama' => $nama, 'email' => $email, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => 'admin',
        ]);
        CLI::write("Admin {$email} dibuat. Masuk lewat /masuk lalu buka /admin.", 'green');
    }
}
