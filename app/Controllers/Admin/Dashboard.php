<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;

class Dashboard extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $db = db_connect();
        $n  = static fn (string $t, ?array $w = null) => $w ? $db->table($t)->where($w)->countAllResults() : $db->table($t)->countAllResults();

        return view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'c'     => [
                'hotel'     => $n('hotels'),
                'geo'       => $db->table('hotels')->where('latitude IS NOT NULL', null, false)->countAllResults(),
                'pengguna'  => $n('users'),
                'ulasan'    => $n('reviews'),
                'pesan'     => $n('inquiries'),
                'pesanBaru' => $n('inquiries', ['status' => 'baru']),
                'kuliner'   => $n('kuliner'),
                'apikey'    => $n('api_keys', ['aktif' => 'true']),
            ],
            'pesanTerbaru' => $db->table('inquiries')->select('inquiries.*, hotels.nama AS hotel_nama')
                ->join('hotels', 'hotels.id = inquiries.hotel_id')->orderBy('inquiries.created_at', 'DESC')->limit(5)->get()->getResultArray(),
            'syncTerakhir' => $db->table('kuliner')->selectMax('synced_at')->get()->getRowArray()['synced_at'] ?? null,
        ]);
    }
}
