<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfilToUsers extends Migration
{
    private array $kolom = ['no_hp', 'jenis_kelamin', 'tanggal_lahir', 'kota_asal'];

    public function up()
    {
        $tambah = [];
        if (! $this->db->fieldExists('no_hp', 'users')) {
            $tambah['no_hp'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true];
        }
        if (! $this->db->fieldExists('jenis_kelamin', 'users')) {
            $tambah['jenis_kelamin'] = ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true];
        }
        if (! $this->db->fieldExists('tanggal_lahir', 'users')) {
            $tambah['tanggal_lahir'] = ['type' => 'DATE', 'null' => true];
        }
        if (! $this->db->fieldExists('kota_asal', 'users')) {
            $tambah['kota_asal'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if ($tambah) {
            $this->forge->addColumn('users', $tambah);
        }
    }

    public function down()
    {
        foreach ($this->kolom as $k) {
            if ($this->db->fieldExists($k, 'users')) {
                $this->forge->dropColumn('users', $k);
            }
        }
    }
}
