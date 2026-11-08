<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHotels extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'auto_increment' => true],
            'peringkat'        => ['type' => 'INT'],
            'halaman'          => ['type' => 'INT', 'null' => true],
            'nama'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'rating'           => ['type' => 'NUMERIC', 'constraint' => '2,1', 'null' => true],
            'jumlah_ulasan'    => ['type' => 'INT', 'null' => true],
            'sarapan'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'Tidak'],
            'reviewer'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'rating_ulasan'    => ['type' => 'SMALLINT', 'null' => true],
            'tanggal_ulasan'   => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'judul_ulasan'     => ['type' => 'TEXT', 'null' => true],
            'link_tripadvisor' => ['type' => 'TEXT', 'null' => true],
            'alamat'           => ['type' => 'TEXT', 'null' => true],
            'kecamatan'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'created_at'       => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('kecamatan');
        $this->forge->addKey('rating');
        $this->forge->createTable('hotels', true);
    }

    public function down()
    {
        $this->forge->dropTable('hotels', true);
    }
}
