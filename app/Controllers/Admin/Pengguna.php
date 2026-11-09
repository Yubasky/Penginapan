<?php

namespace App\Controllers\Admin;

use App\Controllers\AppBase;
use App\Models\UserModel;

class Pengguna extends AppBase
{
    public function index()
    {
        if ($r = $this->butuhAdmin()) {
            return $r;
        }
        $m = new UserModel();
        $m->orderBy('created_at', 'DESC');

        return view('admin/pengguna', ['title' => 'Pengguna', 'items' => $m->paginate(20), 'page' => $m->pager->getCurrentPage(), 'pages' => $m->pager->getPageCount()]);
    }

    public function peran(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        if ($id === (int) $this->user()['id']) {
            return redirect()->back()->with('error', 'Tidak bisa mengubah peran akunmu sendiri.');
        }
        $m = new UserModel();
        $u = $m->find($id);
        if ($u) {
            $m->update($id, ['role' => $u['role'] === 'admin' ? 'user' : 'admin']);
        }

        return redirect()->back()->with('ok', 'Peran diperbarui.');
    }

    public function hapus(int $id)
    {
        if ($r = $this->gerbangAdminPost()) {
            return $r;
        }
        if ($id === (int) $this->user()['id']) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus akunmu sendiri.');
        }
        (new UserModel())->delete($id);

        return redirect()->back()->with('ok', 'Pengguna dihapus.');
    }
}
