<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Pengguna</h1>
<div class="tblwrap"><table class="tbl">
    <tr><th>Nama</th><th>Email</th><th>Peran</th><th>Daftar</th><th></th></tr>
    <?php foreach ($items as $u): $saya = (int) $u['id'] === (int) session('user')['id']; ?>
        <tr><td><?= esc($u['nama']) ?></td><td><?= esc($u['email']) ?></td>
            <td><span class="chip chip--st <?= $u['role'] === 'admin' ? 'st-selesai' : 'st-baru' ?>"><?= esc($u['role']) ?></span></td><td><?= tgl_id($u['created_at']) ?></td>
            <td class="nowrap"><?php if (! $saya): ?>
                <form class="inline" method="post" action="<?= site_url('admin/pengguna/' . $u['id'] . '/peran') ?>"><?= token_field() ?><button class="linkbtn" type="submit"><?= $u['role'] === 'admin' ? 'Jadikan user' : 'Jadikan admin' ?></button></form>
                <form class="inline" method="post" action="<?= site_url('admin/pengguna/' . $u['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus pengguna ini?')"><?= token_field() ?><button class="linkbtn danger" type="submit">Hapus</button></form>
            <?php else: ?><span class="muted small">(kamu)</span><?php endif; ?></td></tr>
    <?php endforeach; ?>
</table></div>
<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
