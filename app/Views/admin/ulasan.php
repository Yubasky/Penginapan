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
        <?php 
        $admMedia = ! empty($r['media']) ? (json_decode($r['media'], true) ?: []) : [];
        if ($admMedia): ?>
            <div class="rev__media">
                <?php foreach ($admMedia as $am): ?>
                    <?php if (($am['type'] ?? '') === 'video'): ?>
                        <div class="rev__video-box"><video src="<?= foto_url($am['url']) ?>" controls preload="metadata"></video></div>
                    <?php else: ?>
                        <a href="<?= foto_url($am['url']) ?>" target="_blank" rel="noopener" class="rev__thumb" title="Lihat foto fasilitas"><img src="<?= foto_url($am['url']) ?>" alt="Fasilitas" loading="lazy"></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if (! $items): ?><p class="muted">Belum ada ulasan.</p><?php endif; ?>
<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
