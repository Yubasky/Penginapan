<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\ReviewModel;

class Ulasan extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $m = new ReviewModel();
        $m->select('reviews.*, users.nama AS penulis, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
            ->join('users', 'users.id = reviews.user_id')->join('hotels', 'hotels.id = reviews.hotel_id')
            ->orderBy('reviews.created_at', 'DESC');

        return view('admin/ulasan', ['title' => 'Ulasan Pengunjung', 'items' => $m->paginate(15), 'page' => $m->pager->getCurrentPage(), 'pages' => $m->pager->getPageCount()]);
    }

    public function hapus(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $m   = new ReviewModel();
        $rev = $m->find($id);
        if ($rev) {
            $hotelId = (int) $rev['hotel_id'];
            $m->delete($id);
            (new \App\Models\HotelModel())->sinkronRating($hotelId);
        }

        return redirect()->back()->with('ok', 'Ulasan dihapus.');
    }
}
