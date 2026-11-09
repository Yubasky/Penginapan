<?php

namespace App\Controllers;

use App\Models\HotelModel;

class Bandingkan extends AppBase
{
    public function index()
    {
        $ids    = bandingkan_ids();
        $hotels = [];
        if ($ids) {
            $rows = (new HotelModel())->whereIn('id', $ids)->findAll();
            foreach ($ids as $id) {                      // urut sesuai pilihan
                foreach ($rows as $r) {
                    if ((int) $r['id'] === $id) {
                        $hotels[] = $r;
                    }
                }
            }
        }
        $avg = [];
        if ($ids) {
            foreach (db_connect()->table('reviews')->select('hotel_id, ROUND(AVG(rating),1) AS rata, COUNT(*) AS n')->whereIn('hotel_id', $ids)->groupBy('hotel_id')->get()->getResultArray() as $r) {
                $avg[(int) $r['hotel_id']] = $r;
            }
        }

        return view('bandingkan', ['title' => 'Bandingkan Penginapan — Singgah Medan', 'hotels' => $hotels, 'avg' => $avg]);
    }

    public function toggle(int $id)
    {
        $ids = bandingkan_ids();
        if (in_array($id, $ids, true)) {
            $ids = array_values(array_diff($ids, [$id]));
            $msg = 'Dihapus dari perbandingan.';
        } elseif (count($ids) >= 3) {
            return redirect()->back()->with('error', 'Maksimal 3 hotel untuk dibandingkan.');
        } elseif ((new HotelModel())->find($id)) {
            $ids[] = $id;
            $msg   = 'Ditambahkan. Buka menu Bandingkan untuk melihatnya.';
        } else {
            return redirect()->back();
        }
        session()->set('bandingkan', $ids);

        return redirect()->back()->with('ok', $msg);
    }

    public function kosongkan()
    {
        session()->remove('bandingkan');

        return redirect()->to(site_url('bandingkan'));
    }
}
