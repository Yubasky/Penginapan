<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Integrasi kuliner</h1>
<div class="panel">
    <p><strong>Server kelompok kuliner:</strong> <?= $aktif ? '<code>' . esc($apiUrl) . '</code>' : '<span class="muted">belum diatur — isi <code>kuliner.apiUrl</code> di file .env lalu restart server</span>' ?></p>
    <p><strong>Data tersimpan:</strong> <?= (int) $jumlah ?> tempat · sinkron terakhir: <?= $terakhir ? tgl_id($terakhir) : 'belum pernah' ?></p>
    <form method="post" action="<?= site_url('admin/kuliner/sinkron') ?>"><?= token_field() ?>
        <button class="btn" type="submit" <?= $aktif ? '' : 'disabled' ?>>Sinkron sekarang</button>
        <span class="small">atau lewat terminal: <code>php spark kuliner:sinkron</code></span></form>
</div>
<h2 style="margin-top:2rem">Terakhir masuk</h2>
<div class="tblwrap"><table class="tbl"><tr><th>Nama</th><th>Kategori</th><th>Kecamatan</th><th>Rating</th></tr>
    <?php foreach ($items as $k): ?><tr><td><?= esc($k['nama']) ?></td><td><?= esc($k['kategori'] ?? '-') ?></td><td><?= esc($k['kecamatan'] ?? '-') ?></td><td><?= $k['rating'] !== null ? esc($k['rating']) : '-' ?></td></tr><?php endforeach; ?>
    <?php if (! $items): ?><tr><td colspan="4" class="muted">Belum ada data.</td></tr><?php endif; ?>
</table></div>
<?= $this->endSection() ?>
