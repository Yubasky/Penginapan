<?php

namespace App\Commands;

use App\Libraries\KulinerSync;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SinkronKuliner extends BaseCommand
{
    protected $group       = 'Singgah';
    protected $name        = 'kuliner:sinkron';
    protected $description = 'Tarik data kuliner dari API kelompok kuliner ke database lokal.';

    public function run(array $params)
    {
        $r = (new KulinerSync())->jalankan();
        CLI::write($r['pesan'], $r['ok'] ? 'green' : 'red');
    }
}
