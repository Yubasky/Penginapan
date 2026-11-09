<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Dari kelompok kuliner</p>
    <h1>Kuliner di Medan</h1>
    <p><?= number_format($total, 0, ',', '.') ?> tempat makan</p>
</div></section>
<div class="wrap" style="padding:1.5rem 0 4rem">
    <form class="mapbar" method="get" action="<?= site_url('kuliner') ?>">
        <input type="search" name="q" value="<?= esc($f['q'], 'attr') ?>" placeholder="Cari nama, kategori, alamat…">
        <select name="kecamatan" onchange="this.form.submit()">
            <option value="">Semua kecamatan</option>
            <?php foreach ($kecamatan as $k): ?><option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= $f['kecamatan'] === $k['kecamatan'] ? 'selected' : '' ?>><?= esc($k['kecamatan']) ?> (<?= (int) $k['jumlah'] ?>)</option><?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Cari</button>
    </form>
    <?php if (! $items): ?>
        <div class="empty"><h3>Belum ada data kuliner</h3><p>Data masuk setelah admin menekan “Sinkron sekarang” (Admin → Integrasi Kuliner) atau kelompok kuliner mengirim lewat API.</p></div>
    <?php else: ?>
        <div class="food food--3"><?php foreach ($items as $k) { echo kuliner_card($k); } ?></div>
        <?= paginasi($page, $pages) ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
