<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCoordinatesToKuliner extends Migration
{
    public function up()
    {
        $fields = [];
        if (! $this->db->fieldExists('latitude', 'kuliner')) {
            $fields['latitude'] = [
                'type'       => 'NUMERIC',
                'constraint' => '10,7',
                'null'       => true,
            ];
        }
        if (! $this->db->fieldExists('longitude', 'kuliner')) {
            $fields['longitude'] = [
                'type'       => 'NUMERIC',
                'constraint' => '10,7',
                'null'       => true,
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('kuliner', $fields);
        }
    }

    public function down()
    {
        $fields = [];
        if ($this->db->fieldExists('latitude', 'kuliner')) {
            $fields[] = 'latitude';
        }
        if ($this->db->fieldExists('longitude', 'kuliner')) {
            $fields[] = 'longitude';
        }
        if (! empty($fields)) {
            $this->forge->dropColumn('kuliner', $fields);
        }
    }
}
