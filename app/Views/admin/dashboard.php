<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Ringkasan</h1>
<div class="adm__cards">
    <a href="<?= site_url('admin/hotel') ?>"><strong><?= $c['hotel'] ?></strong><span>Hotel</span><small><?= $c['geo'] ?> sudah punya koordinat</small></a>
    <a href="<?= site_url('admin/pesan') ?>"><strong><?= $c['pesanBaru'] ?></strong><span>Pesan baru</span><small>dari <?= $c['pesan'] ?> pesan</small></a>
    <a href="<?= site_url('admin/ulasan') ?>"><strong><?= $c['ulasan'] ?></strong><span>Ulasan pengunjung</span></a>
    <a href="<?= site_url('admin/pengguna') ?>"><strong><?= $c['pengguna'] ?></strong><span>Pengguna</span></a>
    <a href="<?= site_url('admin/kuliner') ?>"><strong><?= $c['kuliner'] ?></strong><span>Data kuliner</span><small>sinkron: <?= $syncTerakhir ? tgl_id($syncTerakhir) : 'belum pernah' ?></small></a>
    <a href="<?= site_url('admin/apikey') ?>"><strong><?= $c['apikey'] ?></strong><span>Kunci API aktif</span></a>
</div>
<h2 style="margin-top:2rem">Pesan terbaru</h2>
<table class="tbl">
    <tr><th>Tanggal</th><th>Hotel</th><th>Pengirim</th><th>Status</th></tr>
    <?php foreach ($pesanTerbaru as $p): ?>
        <tr><td><?= tgl_id($p['created_at']) ?></td><td><?= esc($p['hotel_nama']) ?></td><td><?= esc($p['nama']) ?></td><td><span class="chip chip--st st-<?= esc($p['status'], 'attr') ?>"><?= esc($p['status']) ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (! $pesanTerbaru): ?><tr><td colspan="4" class="muted">Belum ada pesan.</td></tr><?php endif; ?>
</table>
<?= $this->endSection() ?>
