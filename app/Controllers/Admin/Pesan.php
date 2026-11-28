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

        $db   = db_connect();
        $mInq = new InquiryModel();
        $db->transBegin();

        try {
            $data = [];
            if (in_array($s, ['baru', 'diproses', 'selesai'], true)) {
                $data['status'] = $s;
            }
            if ($this->request->getPost('balasan_admin') !== null) {
                $data['balasan_admin'] = $balasan !== '' ? $balasan : null;
                $data['dibalas_at']    = $balasan !== '' ? date('Y-m-d H:i:s') : null;

                if ($balasan !== '') {
                    $inq = $mInq->findForUpdate($id);
                    if ($inq) {
                        $jawaban   = json_decode($inq['jawaban'] ?? '[]', true) ?: [];
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
            }

            if ($data) {
                $mInq->update($id, $data);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();

                return redirect()->back()->with('error', 'Gagal memperbarui pesan.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin\Pesan::status gagal: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan balasan.');
        }

        return redirect()->back()->with('ok', 'Pesan dan status berhasil diperbarui.');
    }
}
