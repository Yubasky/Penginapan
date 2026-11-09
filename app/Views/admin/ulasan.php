<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Ulasan pengunjung</h1>
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
