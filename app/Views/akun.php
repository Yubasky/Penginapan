<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Akun saya</p>
    <h1><?= esc(session('user')['nama']) ?></h1>
    <p><?= esc(session('user')['email']) ?></p>
</div></section>
<div class="wrap" style="padding:2rem 0 4rem">
    <h2>Hotel favorit (<?= count($favorit) ?>)</h2>
    <?php if ($favorit): ?><div class="grid"><?php foreach ($favorit as $h) { echo hotel_card($h); } ?></div>
    <?php else: ?><p class="muted">Belum ada favorit. Klik ♡ pada kartu hotel untuk menyimpannya.</p><?php endif; ?>

    <h2 style="margin-top:2.5rem">Ulasan saya (<?= count($ulasan) ?>)</h2>
    <?php foreach ($ulasan as $r): ?>
        <div class="rev"><div class="rev__top"><a class="link" href="<?= site_url('penginapan/' . $r['hotel_slug']) ?>#ulasan"><strong><?= esc($r['hotel_nama']) ?></strong></a><span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span><span class="muted small"><?= tgl_id($r['created_at']) ?></span></div><p><?= esc($r['isi']) ?></p></div>
    <?php endforeach; ?>
    <?php if (! $ulasan): ?><p class="muted">Belum ada ulasan.</p><?php endif; ?>

    <h2 style="margin-top:2.5rem">Pesan ke hotel (<?= count($pesan) ?>)</h2>
    <?php foreach ($pesan as $p): ?>
        <div class="rev"><div class="rev__top"><a class="link" href="<?= site_url('penginapan/' . $p['hotel_slug']) ?>"><strong><?= esc($p['hotel_nama']) ?></strong></a><span class="chip chip--st st-<?= esc($p['status'], 'attr') ?>"><?= esc($p['status']) ?></span><span class="muted small"><?= tgl_id($p['created_at']) ?></span></div><p><?= esc($p['pesan'] ?: '(tanpa pesan)') ?></p></div>
    <?php endforeach; ?>
    <?php if (! $pesan): ?><p class="muted">Belum ada pesan.</p><?php endif; ?>
</div>
<?= $this->endSection() ?>
