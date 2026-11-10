<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\InquiryModel;

class Pesan extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $status = (string) $this->request->getGet('status');
        $m      = new InquiryModel();
        $m->select('inquiries.*, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
            ->join('hotels', 'hotels.id = inquiries.hotel_id')->orderBy('inquiries.created_at', 'DESC');
        if (in_array($status, ['baru', 'diproses', 'selesai'], true)) {
            $m->where('inquiries.status', $status);
        }

        return view('admin/pesan', [
            'title'  => 'Pesan & Tanya Hotel',
            'status' => $status,
            'items'  => $m->paginate(15),
            'page'   => $m->pager->getCurrentPage(),
            'pages'  => $m->pager->getPageCount(),
        ]);
    }

    public function status(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $s       = (string) $this->request->getPost('status');
        $balasan = trim((string) $this->request->getPost('balasan_admin'));

        $data = [];
        if (in_array($s, ['baru', 'diproses', 'selesai'], true)) {
            $data['status'] = $s;
        }
        if ($this->request->getPost('balasan_admin') !== null) {
            $data['balasan_admin'] = $balasan !== '' ? $balasan : null;
            $data['dibalas_at']    = $balasan !== '' ? date('Y-m-d H:i:s') : null;
        }

        if ($data) {
            (new InquiryModel())->update($id, $data);
        }

        return redirect()->back()->with('ok', 'Pesan dan status berhasil diperbarui.');
    }
}
