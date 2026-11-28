<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$foto = $k['foto'] ?? '';
$menus = is_array($liveDetail['menu'] ?? ($liveDetail['menus'] ?? null))
    ? ($liveDetail['menu'] ?? $liveDetail['menus'])
    : [];
$deskripsi = (string) ($liveDetail['deskripsi'] ?? '');
$telepon   = (string) ($liveDetail['telepon'] ?? ($liveDetail['no_hp'] ?? ''));
$infoHalal = (string) ($liveDetail['info_halal'] ?? '');
$jenis     = (string) ($liveDetail['jenis_masakan'] ?? '');
$gmapsUrl  = (string) ($liveDetail['google_maps_url'] ?? ($liveDetail['link_maps'] ?? ''));
if ($gmapsUrl === '') {
    $qMap     = ! empty($k['latitude']) && ! empty($k['longitude'])
        ? $k['latitude'] . ',' . $k['longitude']
        : $k['nama'] . ', ' . ($k['alamat'] ?? 'Medan');
    $gmapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($qMap);
}
$embedQuery = ! empty($k['latitude']) && ! empty($k['longitude'])
    ? $k['latitude'] . ',' . $k['longitude']
    : $k['nama'] . ', ' . ($k['alamat'] ?? 'Medan');
$embedUrl   = 'https://maps.google.com/maps?q=' . rawurlencode($embedQuery) . '&z=16&output=embed';
?>

<section class="detail__hero <?= $foto !== '' ? 'detail__hero--has-cover' : '' ?>" style="--h: 158<?= $foto !== '' ? '; background-image: linear-gradient(to bottom, rgba(10,46,42,0.72), rgba(10,46,42,0.92)), url(\'' . esc($foto, 'attr') . '\'); background-size: cover; background-position: center;' : '' ?>">
    <div class="wrap">
        <a class="back" href="<?= site_url('kuliner') ?>">← Semua kuliner Medan</a>
        <?php if (! empty($k['kecamatan'])): ?><p class="eyebrow eyebrow--gold"><?= esc($k['kecamatan']) ?>, Medan</p><?php endif; ?>
        <h1><?= esc($k['nama']) ?></h1>
        <div class="detail__badges">
            <?php if ($k['rating'] !== null): ?><?= rating_label($k['rating']) ?><?php endif; ?>
            <?php if (! empty($k['kategori'])): ?><span class="chip"><?= esc($k['kategori']) ?></span><?php endif; ?>
            <?php if (! empty($k['harga'])): ?><span class="chip"><?= esc($k['harga']) ?></span><?php endif; ?>
            <?php if ($infoHalal !== ''): ?><span class="chip" style="background:#dcfce7;color:#166534"><?= esc($infoHalal) ?></span><?php endif; ?>
        </div>
    </div>
</section>

<div class="wrap detail">
    <div class="detail__main">
        <section class="panel">
            <h2 style="margin-top:0">Alamat &amp; Lokasi Restoran</h2>
            <p class="addr"><?= esc($k['alamat'] ?: 'Kota Medan, Sumatera Utara') ?></p>
            <?php if (! empty($k['latitude']) && ! empty($k['longitude'])): ?>
                <p class="small muted" style="margin-top:-.4rem;margin-bottom:.8rem">
                    Koordinat GPS: <code><?= number_format((float) $k['latitude'], 6) ?>, <?= number_format((float) $k['longitude'], 6) ?></code>
                </p>
            <?php endif; ?>
            <iframe class="gmap" title="Peta <?= esc($k['nama'], 'attr') ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= esc($embedUrl, 'attr') ?>"></iframe>
            <div class="actions" style="margin-top:.8rem;display:flex;flex-wrap:wrap;gap:.6rem">
                <a class="btn" href="<?= esc($gmapsUrl, 'attr') ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
                <?php if (! empty($k['url'])): ?>
                    <a class="btn btn--ghost" href="<?= esc($k['url'], 'attr') ?>" target="_blank" rel="noopener">Lihat di Web Kelompok Kuliner ↗</a>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($deskripsi !== '' || ! empty($menus)): ?>
            <section class="panel">
                <h2 style="margin-top:0">🍽️ Informasi &amp; Daftar Menu</h2>
                <?php if ($deskripsi !== ''): ?>
                    <p style="line-height:1.65"><?= nl2br(esc($deskripsi)) ?></p>
                <?php endif; ?>
                <?php if (! empty($menus)): ?>
                    <div class="tblwrap" style="margin-top:1rem">
                        <table class="tbl">
                            <tr><th>Menu</th><th>Kategori</th><th>Harga</th></tr>
                            <?php foreach ($menus as $mn): ?>
                                <tr>
                                    <td><strong><?= esc($mn['nama'] ?? '-') ?></strong><?php if (! empty($mn['deskripsi'])): ?><br><span class="small muted"><?= esc($mn['deskripsi']) ?></span><?php endif; ?></td>
                                    <td><?= esc($mn['kategori'] ?? '-') ?></td>
                                    <td><?= isset($mn['harga']) && is_numeric($mn['harga']) ? 'Rp ' . number_format((int) $mn['harga'], 0, ',', '.') : esc($mn['harga'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (! empty($ulasan)): ?>
            <section class="panel">
                <h2 style="margin-top:0">⭐ Ulasan Pengunjung Restoran (<?= count($ulasan) ?>)</h2>
                <div class="reviews">
                    <?php foreach ($ulasan as $u): ?>
                        <article class="rev">
                            <div class="rev__top">
                                <strong><?= esc($u['pengguna'] ?? 'Pengunjung') ?></strong>
                                <span><?= rating_label($u['rating'] ?? null) ?></span>
                            </div>
                            <?php if (! empty($u['komentar'])): ?>
                                <p style="margin:.4rem 0 0"><?= nl2br(esc($u['komentar'])) ?></p>
                            <?php endif; ?>
                            <?php if (! empty($u['dibuat'])): ?>
                                <p class="small muted" style="margin:.35rem 0 0"><?= esc($u['dibuat']) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:.4rem">
                <h2 style="margin:0">🏨 Penginapan Terdekat di Sekitar Kuliner Ini</h2>
                <?php if ($hotels): ?>
                    <span class="chip"><?= count($hotels) ?> Hotel</span>
                <?php endif; ?>
            </div>
            <p class="muted small" style="margin-top:0;margin-bottom:1.1rem">
                Rekomendasi penginapan terdekat<?= ! empty($k['kecamatan']) ? ' di kawasan ' . esc($k['kecamatan']) : '' ?>:
            </p>
            <?php if ($hotels): ?>
                <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(230px,1fr))">
                    <?php foreach ($hotels as $h): echo hotel_card($h); endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">Belum ada penginapan terdekat yang tercatat di kawasan ini.</p>
            <?php endif; ?>
        </section>
    </div>

    <aside class="detail__side">
        <div class="panel">
            <h3 style="margin-top:0">Ringkasan Kuliner</h3>
            <dl style="display:grid;gap:.65rem;margin:0;font-size:.92rem">
                <div><dt class="small muted">Kategori</dt><dd style="margin:0;font-weight:600"><?= esc($k['kategori'] ?: '-') ?></dd></div>
                <?php if ($jenis !== ''): ?><div><dt class="small muted">Jenis Masakan</dt><dd style="margin:0;font-weight:600"><?= esc($jenis) ?></dd></div><?php endif; ?>
                <div><dt class="small muted">Kisaran Harga</dt><dd style="margin:0;font-weight:600"><?= esc($k['harga'] ?: '-') ?></dd></div>
                <div><dt class="small muted">Kecamatan</dt><dd style="margin:0;font-weight:600"><?= esc($k['kecamatan'] ?: '-') ?></dd></div>
                <?php if ($telepon !== ''): ?><div><dt class="small muted">Telepon</dt><dd style="margin:0;font-weight:600"><?= esc($telepon) ?></dd></div><?php endif; ?>
                <div><dt class="small muted">Sumber Data</dt><dd style="margin:0"><span class="chip">API Kelompok Kuliner</span></dd></div>
                <?php if (! empty($k['synced_at'])): ?><div><dt class="small muted">Terakhir Disinkronkan</dt><dd style="margin:0" class="small"><?= tgl_id($k['synced_at']) ?></dd></div><?php endif; ?>
            </dl>
        </div>
    </aside>
</div>
<?= $this->endSection() ?>

