<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Libraries\KulinerClient;
use App\Libraries\KulinerSync;
use App\Models\KulinerModel;

class KulinerAdmin extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $m = new KulinerModel();

        return view('admin/kuliner', [
            'title'   => 'Integrasi Kuliner',
            'aktif'   => (new KulinerClient())->enabled(),
            'apiUrl'  => (string) env('kuliner.apiUrl', ''),
            'jumlah'  => $m->countAllResults(),
            'terakhir' => db_connect()->table('kuliner')->selectMax('synced_at')->get()->getRowArray()['synced_at'] ?? null,
            'items'   => (new KulinerModel())->orderBy('synced_at', 'DESC')->findAll(10),
        ]);
    }

    public function sinkron()
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        set_time_limit(120);
        $hasil = (new KulinerSync())->jalankan();

        return redirect()->to(site_url('admin/kuliner'))->with($hasil['ok'] ? 'ok' : 'error', $hasil['pesan']);
    }
}
