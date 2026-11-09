<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Kunci API</h1>
<p class="muted">Beri kunci ke kelompok lain agar mereka bisa mengirim data kuliner (<code>POST /api/kuliner/push</code>) dan mengekspor data kami. Kunci hanya tampil sekali saat dibuat.</p>
<?php if ($baru): ?><div class="panel keybox"><strong>Kunci baru — salin sekarang:</strong><code><?= esc($baru) ?></code></div><?php endif; ?>
<form class="mapbar" method="post" action="<?= site_url('admin/apikey/buat') ?>"><?= token_field() ?>
    <input type="text" name="nama" placeholder="Nama pemakai, mis. Kelompok Kuliner" required maxlength="100"><button class="btn" type="submit">Buat kunci</button></form>
<div class="tblwrap"><table class="tbl">
    <tr><th>Nama</th><th>Awalan</th><th>Status</th><th>Terakhir dipakai</th><th></th></tr>
    <?php foreach ($items as $k): $on = in_array($k['aktif'], [true, 't', '1', 1, 'true'], true); ?>
        <tr><td><?= esc($k['nama']) ?></td><td><code><?= esc($k['key_prefix']) ?>…</code></td>
            <td><span class="chip chip--st <?= $on ? 'st-selesai' : 'st-baru' ?>"><?= $on ? 'aktif' : 'nonaktif' ?></span></td>
            <td><?= $k['last_used_at'] ? tgl_id($k['last_used_at']) : '–' ?></td>
            <td class="nowrap">
                <form class="inline" method="post" action="<?= site_url('admin/apikey/' . $k['id'] . '/toggle') ?>"><?= token_field() ?><button class="linkbtn" type="submit"><?= $on ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
                <form class="inline" method="post" action="<?= site_url('admin/apikey/' . $k['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus kunci ini?')"><?= token_field() ?><button class="linkbtn danger" type="submit">Hapus</button></form></td></tr>
    <?php endforeach; ?>
    <?php if (! $items): ?><tr><td colspan="5" class="muted">Belum ada kunci.</td></tr><?php endif; ?>
</table></div>
<?= $this->endSection() ?>
