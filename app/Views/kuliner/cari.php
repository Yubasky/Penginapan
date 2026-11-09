<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Pencarian</p>
    <h1>Cari hotel &amp; kuliner</h1>
    <form class="mapbar" method="get" action="<?= site_url('cari') ?>" style="margin-top:1rem">
        <input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Contoh: soto, Medan Petisah, Grand…" autofocus>
        <button class="btn btn--gold" type="submit">Cari</button>
    </form>
</div></section>
<div class="wrap" style="padding:2rem 0 4rem">
<?php if ($q === ''): ?>
    <p class="muted">Ketik kata kunci untuk mencari di data penginapan dan kuliner sekaligus.</p>
<?php else: ?>
    <h2>Penginapan (<?= count($hotels) ?>)</h2>
    <?php if ($hotels): ?><div class="grid"><?php foreach ($hotels as $h) { echo hotel_card($h); } ?></div>
    <?php else: ?><p class="muted">Tidak ada penginapan yang cocok.</p><?php endif; ?>
    <h2 style="margin-top:2.5rem">Kuliner (<?= count($kuliner) ?>)</h2>
    <?php if ($kuliner): ?><div class="food food--3"><?php foreach ($kuliner as $k) { echo kuliner_card($k); } ?></div>
    <?php else: ?><p class="muted">Tidak ada kuliner yang cocok.</p><?php endif; ?>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
