<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Pesan &amp; tanya hotel</h1>
<p class="tabs"><?php foreach (['' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $v => $l): ?><a class="<?= $status === $v ? 'is-on' : '' ?>" href="<?= site_url('admin/pesan' . ($v ? '?status=' . $v : '')) ?>"><?= $l ?></a><?php endforeach; ?></p>
<?php foreach ($items as $p): ?>
    <div class="panel msg">
        <div class="msg__top">
            <div><strong><?= esc($p['nama']) ?></strong> · <a class="link" href="mailto:<?= esc($p['email'], 'attr') ?>"><?= esc($p['email']) ?></a><?= $p['telepon'] ? ' · ' . esc($p['telepon']) : '' ?>
                <div class="small">untuk <a class="link" href="<?= site_url('penginapan/' . $p['hotel_slug']) ?>" target="_blank"><?= esc($p['hotel_nama']) ?></a> · <?= tgl_id($p['created_at']) ?></div></div>
            <form class="inline" method="post" action="<?= site_url('admin/pesan/' . $p['id'] . '/status') ?>"><?= token_field() ?>
                <select name="status" onchange="this.form.submit()"><?php foreach (['baru', 'diproses', 'selesai'] as $s): ?><option <?= $p['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></form>
        </div>
        <p class="small"><?= $p['tgl_checkin'] ? 'Check-in ' . tgl_id($p['tgl_checkin']) : '' ?> <?= $p['tgl_checkout'] ? '· Check-out ' . tgl_id($p['tgl_checkout']) : '' ?> <?= $p['jumlah_tamu'] ? '· ' . (int) $p['jumlah_tamu'] . ' tamu' : '' ?></p>
        <p><?= nl2br(esc($p['pesan'] ?: '(tanpa pesan)')) ?></p>
    </div>
<?php endforeach; ?>
<?php if (! $items): ?><p class="muted">Tidak ada pesan.</p><?php endif; ?>
<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
