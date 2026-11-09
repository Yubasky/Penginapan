<?php

namespace App\Controllers;

class Akun extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhLogin()) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $db  = db_connect();

        return view('akun', [
            'title'    => 'Akun Saya — Singgah Medan',
            'favorit'  => $db->table('favorites')->select('hotels.*')->join('hotels', 'hotels.id = favorites.hotel_id')
                ->where('favorites.user_id', $uid)->orderBy('favorites.created_at', 'DESC')->get()->getResultArray(),
            'ulasan'   => $db->table('reviews')->select('reviews.*, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
                ->join('hotels', 'hotels.id = reviews.hotel_id')->where('reviews.user_id', $uid)
                ->orderBy('reviews.created_at', 'DESC')->get()->getResultArray(),
            'pesan'    => $db->table('inquiries')->select('inquiries.*, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
                ->join('hotels', 'hotels.id = inquiries.hotel_id')->where('inquiries.user_id', $uid)
                ->orderBy('inquiries.created_at', 'DESC')->get()->getResultArray(),
        ]);
    }
}
