<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\ApiKeyModel;

class ApiKeys extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }

        return view('admin/apikey', [
            'title' => 'Kunci API',
            'items' => (new ApiKeyModel())->orderBy('id', 'DESC')->findAll(),
            'baru'  => session()->getFlashdata('kunci_baru'),
        ]);
    }

    public function buat()
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $nama = trim((string) $this->request->getPost('nama'));
        if ($nama === '' || mb_strlen($nama) > 100) {
            return redirect()->back()->with('error', 'Isi nama kelompok/pemakai kunci (maks 100 karakter).');
        }
        $kunci = (new ApiKeyModel())->buat($nama);

        return redirect()->to(site_url('admin/apikey'))->with('kunci_baru', $kunci)->with('ok', 'Kunci dibuat. Salin sekarang, kunci tidak akan ditampilkan lagi.');
    }

    public function toggle(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        $m = new ApiKeyModel();
        $k = $m->find($id);
        if ($k) {
            $m->update($id, ['aktif' => ($k['aktif'] === true || $k['aktif'] === 't' || $k['aktif'] === '1') ? 'false' : 'true']);
        }

        return redirect()->back()->with('ok', 'Status kunci diperbarui.');
    }

    public function hapus(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        (new ApiKeyModel())->delete($id);

        return redirect()->back()->with('ok', 'Kunci dihapus.');
    }
}
