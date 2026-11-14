<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotifDibacaAtToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('notif_dibaca_at', 'users')) {
            $this->forge->addColumn('users', [
                'notif_dibaca_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('notif_dibaca_at', 'users')) {
            $this->forge->dropColumn('users', 'notif_dibaca_at');
        }
    }
}

