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
        $dari    = trim((string) $this->request->getGet('dari'));
        $sampai  = trim((string) $this->request->getGet('sampai'));
        $perPage = (string) $this->request->getGet('per_page');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $dari = '';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $sampai = '';
        }
        if (! in_array($perPage, ['20', '50', 'all'], true)) {
            $perPage = '20';
        }

        $m = new ReviewModel();
        $m->select('reviews.*, users.nama AS penulis, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
            ->join('users', 'users.id = reviews.user_id')->join('hotels', 'hotels.id = reviews.hotel_id');

        if ($dari !== '') {
            $m->where('reviews.created_at >=', $dari . ' 00:00:00');
        }
        if ($sampai !== '') {
            $m->where('reviews.created_at <=', $sampai . ' 23:59:59');
        }

        $m->orderBy('reviews.created_at', 'DESC');

        if ($perPage === 'all') {
            $items = $m->findAll();
            $page  = 1;
            $pages = 1;
            $total = count($items);
        } else {
            $limit = (int) $perPage;
            $items = $m->paginate($limit);
            $page  = $m->pager->getCurrentPage();
            $pages = $m->pager->getPageCount();
            $total = $m->pager->getTotal();
        }

        return view('admin/ulasan', [
            'title'   => 'Ulasan Pengunjung',
            'items'   => $items,
            'page'    => $page,
            'pages'   => $pages,
            'total'   => $total,
            'dari'    => $dari,
            'sampai'  => $sampai,
            'perPage' => $perPage,
        ]);
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
