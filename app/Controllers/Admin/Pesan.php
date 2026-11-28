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

        $db = db_connect();
        $db->transBegin();
        try {
            $locked = $db->query('SELECT id, jawaban FROM inquiries WHERE id = ? FOR UPDATE', [$id])->getRowArray();
            if ($locked && $this->request->getPost('balasan_admin') !== null) {
                $data['balasan_admin'] = $balasan !== '' ? $balasan : null;
                $data['dibalas_at']    = $balasan !== '' ? date('Y-m-d H:i:s') : null;

                if ($balasan !== '') {
                    $jawaban   = json_decode($locked['jawaban'] ?? '[]', true) ?: [];
                    $jawaban[] = [
                        'id'         => bin2hex(random_bytes(6)),
                        'user_id'    => session('user')['id'] ?? null,
                        'nama'       => session('user')['nama'] ?? 'Admin Pengelola',
                        'role'       => 'admin',
                        'isi'        => $balasan,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $data['jawaban'] = json_encode($jawaban);
                }
            }

            if ($data) {
                (new InquiryModel())->update($id, $data);
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin\Pesan::status gagal: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui pesan.');
        }

        return redirect()->back()->with('ok', 'Pesan dan status berhasil diperbarui.');
    }
}
