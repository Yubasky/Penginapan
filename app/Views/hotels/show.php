<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<section class="detail__hero" style="--h: <?= hue_hotel($h) ?>">
    <div class="wrap">
        <a class="back" href="<?= site_url('penginapan') ?>">← Semua penginapan</a>
        <?php if (! empty($h['kecamatan'])): ?><p class="eyebrow eyebrow--gold"><?= esc($h['kecamatan']) ?>, Medan</p><?php endif; ?>
        <h1><?= esc($h['nama']) ?></h1>
        <div class="detail__badges">
            <?= rating_label($h['rating']) ?>
            <?php if ($h['jumlah_ulasan'] !== null): ?><span class="chip"><?= number_format((int) $h['jumlah_ulasan'], 0, ',', '.') ?> ulasan</span><?php endif; ?>
            <span class="chip"><?= $h['sarapan'] === 'Tidak' ? 'Tanpa sarapan' : ($h['sarapan'] === 'All-in' ? 'All-inclusive' : 'Termasuk sarapan') ?></span>
        </div>
    </div>
</section>

<div class="wrap detail">
    <div class="detail__main">
        <section class="panel">
            <h2>Alamat</h2>
            <p class="addr"><?= esc($h['alamat']) ?>, Medan, Sumatera Utara</p>
            <div class="actions">
                <a class="btn" href="<?= esc(peta_url($h), 'attr') ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
                <?php if ($h['link_tripadvisor']): ?>
                    <a class="btn btn--ghost" href="<?= esc($h['link_tripadvisor'], 'attr') ?>" target="_blank" rel="noopener nofollow">Lihat di Tripadvisor</a>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($h['reviewer']): ?>
        <section class="panel">
            <h2>Ulasan tamu</h2>
            <blockquote class="quote">
                <p>“<?= esc($h['judul_ulasan']) ?>”</p>
                <footer>
                    <?= esc($h['reviewer']) ?>
                    <?php if ($h['rating_ulasan']): ?> · <span class="stars"><?= str_repeat('★', (int) $h['rating_ulasan']) ?></span><?php endif; ?>
                    <?php if ($h['tanggal_ulasan']): ?> · <?= esc($h['tanggal_ulasan']) ?><?php endif; ?>
                </footer>
            </blockquote>
        </section>
        <?php endif; ?>

        <section class="panel">
            <h2>Kuliner di sekitar <?= esc($h['kecamatan'] ?? 'sini') ?></h2>
            <?php if ($kuliner): ?>
                <div class="food">
                    <?php foreach ($kuliner as $k): ?>
                        <article class="food__item">
                            <?php if ($k['foto']): ?><img src="<?= esc($k['foto'], 'attr') ?>" alt="" loading="lazy"><?php endif; ?>
                            <div>
                                <h3><?php if ($k['url'] && preg_match('#^https?://#i', $k['url'])): ?><a href="<?= esc($k['url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc($k['nama']) ?></a><?php else: ?><?= esc($k['nama']) ?><?php endif; ?></h3>
                                <p class="muted"><?= esc(trim($k['kategori'] . ($k['harga'] ? ' · ' . $k['harga'] : ''), ' ·')) ?></p>
                                <?php if ($k['alamat']): ?><p class="small"><?= esc($k['alamat']) ?></p><?php endif; ?>
                                <?php if ($k['rating'] !== null): ?><p><?= rating_label($k['rating']) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted"><?= $kulinerAktif
                    ? 'Belum ada data kuliner untuk kecamatan ini, atau server kelompok kuliner sedang tidak dapat dihubungi.'
                    : 'Bagian ini akan terisi otomatis setelah API kelompok kuliner dihubungkan (lihat halaman Tentang &amp; API).' ?></p>
            <?php endif; ?>
        </section>
    </div>

    <aside class="detail__side">
        <?php if ($serupa): ?>
            <h2>Di kecamatan yang sama</h2>
            <?php foreach ($serupa as $s) { echo hotel_card($s); } ?>
        <?php endif; ?>
    </aside>
</div>

<?= $this->endSection() ?>
