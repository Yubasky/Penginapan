<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Ulasan pengunjung</h1>
<form class="panel" method="get" action="<?= site_url('admin/ulasan') ?>" style="display:flex;gap:.8rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem">
    <div>
        <label class="small" for="f-dari" style="display:block;margin-bottom:.25rem">Dari tanggal</label>
        <input id="f-dari" type="date" name="dari" value="<?= esc($dari) ?>">
    </div>
    <div>
        <label class="small" for="f-sampai" style="display:block;margin-bottom:.25rem">Sampai tanggal</label>
        <input id="f-sampai" type="date" name="sampai" value="<?= esc($sampai) ?>">
    </div>
    <div>
        <label class="small" for="f-per-page" style="display:block;margin-bottom:.25rem">Tampilkan</label>
        <select id="f-per-page" name="per_page">
            <option value="20" <?= $perPage === '20' ? 'selected' : '' ?>>20 per halaman</option>
            <option value="50" <?= $perPage === '50' ? 'selected' : '' ?>>50 per halaman</option>
            <option value="all" <?= $perPage === 'all' ? 'selected' : '' ?>>Tampilkan semua</option>
        </select>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center">
        <button class="btn" type="submit">Terapkan</button>
        <?php if ($dari !== '' || $sampai !== '' || $perPage !== '20'): ?>
            <a class="btn btn--outline" href="<?= site_url('admin/ulasan') ?>">Reset</a>
        <?php endif; ?>
    </div>
</form>

<p class="small muted" style="margin-bottom:1rem">
    Menampilkan <?= count($items) ?> dari <?= (int) $total ?> ulasan<?= $perPage !== 'all' ? ' (maks. ' . (int) $perPage . ' per halaman — navigasi halaman otomatis muncul bila data &gt; ' . (int) $perPage . ')' : '' ?>.
</p>

<?php foreach ($items as $r): ?>
    <div class="panel msg">
        <div class="msg__top"><div><strong><?= esc($r['penulis']) ?></strong> <span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span>
            <div class="small">di <a class="link" href="<?= site_url('penginapan/' . $r['hotel_slug']) ?>#ulasan" target="_blank"><?= esc($r['hotel_nama']) ?></a> · <?= tgl_id($r['created_at']) ?></div></div>
            <form class="inline" method="post" action="<?= site_url('admin/ulasan/' . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus ulasan ini?')"><?= token_field() ?><button class="linkbtn danger" type="submit">Hapus</button></form></div>
        <?php if ($r['judul']): ?><p class="rev__t"><?= esc($r['judul']) ?></p><?php endif; ?>
        <p><?= nl2br(esc($r['isi'])) ?></p>
    </div>
<?php endforeach; ?>
<?php if (! $items): ?><p class="muted">Belum ada ulasan.</p><?php endif; ?>
<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
