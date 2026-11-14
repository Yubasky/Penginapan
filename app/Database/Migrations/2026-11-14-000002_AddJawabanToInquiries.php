<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJawabanToInquiries extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('jawaban', 'inquiries')) {
            $this->forge->addColumn('inquiries', [
                'jawaban' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('jawaban', 'inquiries')) {
            $this->forge->dropColumn('inquiries', 'jawaban');
        }
    }
}

