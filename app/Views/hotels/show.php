<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$u      = session('user');
$isFav  = in_array((int) $h['id'], favorit_ids(), true);
$inCmp  = in_array((int) $h['id'], bandingkan_ids(), true);
?>
<section class="detail__hero" style="--h: <?= hue_hotel($h) ?>">
    <div class="wrap">
        <a class="back" href="<?= site_url('penginapan') ?>">← Semua penginapan</a>
        <?php if (! empty($h['kecamatan'])): ?><p class="eyebrow eyebrow--gold"><?= esc($h['kecamatan']) ?>, Medan</p><?php endif; ?>
        <h1><?= esc($h['nama']) ?></h1>
        <div class="detail__badges">
            <?= rating_label($h['rating']) ?>
            <?php if ($h['jumlah_ulasan'] !== null): ?><span class="chip"><?= number_format((int) $h['jumlah_ulasan'], 0, ',', '.') ?> ulasan Tripadvisor</span><?php endif; ?>
            <span class="chip"><?= $h['sarapan'] === 'Tidak' ? 'Tanpa sarapan' : ($h['sarapan'] === 'All-in' ? 'All-inclusive' : 'Termasuk sarapan') ?></span>
        </div>
    </div>
</section>

<div class="wrap detail">
    <div class="detail__main">
        <section class="panel">
            <h2>Alamat &amp; lokasi</h2>
            <p class="addr"><?= esc($h['alamat']) ?>, Medan, Sumatera Utara</p>
            <iframe class="gmap" title="Peta <?= esc($h['nama'], 'attr') ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= esc(peta_embed_url($h), 'attr') ?>"></iframe>
            <div class="actions">
                <a class="btn" href="<?= esc(peta_url($h), 'attr') ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
                <?php if ($h['link_tripadvisor']): ?>
                    <a class="btn btn--ghost" href="<?= esc($h['link_tripadvisor'], 'attr') ?>" target="_blank" rel="noopener nofollow">Lihat di Tripadvisor</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel" id="ulasan">
            <h2>Ulasan tamu</h2>
            <?php if ($h['reviewer']): ?>
                <p class="eyebrow">Dari Tripadvisor</p>
                <blockquote class="quote">
                    <p>“<?= esc($h['judul_ulasan']) ?>”</p>
                    <footer>
                        <?= esc($h['reviewer']) ?>
                        <?php if ($h['rating_ulasan']): ?> · <span class="stars"><?= str_repeat('★', (int) $h['rating_ulasan']) ?></span><?php endif; ?>
                        <?php if ($h['tanggal_ulasan']): ?> · <?= esc($h['tanggal_ulasan']) ?><?php endif; ?>
                    </footer>
                </blockquote>
            <?php endif; ?>

            <p class="eyebrow" style="margin-top:1.5rem">Dari pengunjung Singgah Medan
                <?php if ($ringkasan['jumlah']): ?>· <?= number_format($ringkasan['rata'], 1) ?> ★ (<?= $ringkasan['jumlah'] ?>)<?php endif; ?></p>
            <?php if ($ulasan): ?>
                <?php foreach ($ulasan as $r): ?>
                    <div class="rev">
                        <div class="rev__top"><strong><?= esc($r['penulis']) ?></strong><span class="stars"><?= str_repeat('★', (int) $r['rating']) ?><span class="stars--off"><?= str_repeat('★', 5 - (int) $r['rating']) ?></span></span><span class="muted small"><?= tgl_id($r['created_at']) ?></span></div>
                        <?php if ($r['judul']): ?><p class="rev__t"><?= esc($r['judul']) ?></p><?php endif; ?>
                        <p><?= nl2br(esc($r['isi'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="muted">Belum ada ulasan dari pengunjung. Jadilah yang pertama!</p>
            <?php endif; ?>

            <?php if ($u): ?>
                <form class="form" method="post" action="<?= site_url('penginapan/' . $h['id'] . '/ulasan') ?>">
                    <?= token_field() ?>
                    <h3><?= $ulasanSaya ? 'Ubah ulasanmu' : 'Tulis ulasan' ?></h3>
                    <?php $rt = (int) (old('rating') ?: ($ulasanSaya['rating'] ?? 0)); ?>
                    <div class="starpick" role="radiogroup" aria-label="Rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="st<?= $i ?>" name="rating" value="<?= $i ?>" <?= $rt === $i ? 'checked' : '' ?>><label for="st<?= $i ?>" title="<?= $i ?> bintang">★</label>
                        <?php endfor; ?>
                    </div>
                    <label>Judul (opsional)<input type="text" name="judul" maxlength="150" value="<?= old('judul', $ulasanSaya['judul'] ?? '') ?>"></label>
                    <label>Ceritakan pengalamanmu<textarea name="isi" rows="4" minlength="10" maxlength="2000" required><?= old('isi', $ulasanSaya['isi'] ?? '') ?></textarea></label>
                    <button class="btn" type="submit">Kirim ulasan</button>
                </form>
            <?php else: ?>
                <p class="muted"><a class="link" href="<?= site_url('masuk') ?>">Masuk</a> atau <a class="link" href="<?= site_url('daftar') ?>">daftar</a> untuk menulis ulasan.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>Kuliner di sekitar <?= esc($h['kecamatan'] ?? 'sini') ?></h2>
            <?php if ($kuliner): ?>
                <div class="food"><?php foreach ($kuliner as $k) { echo kuliner_card($k); } ?></div>
                <p style="margin-top:1rem"><a class="link" href="<?= site_url('kuliner' . (! empty($h['kecamatan']) ? '?kecamatan=' . rawurlencode($h['kecamatan']) : '')) ?>">Lihat semua kuliner di kecamatan ini →</a></p>
            <?php else: ?>
                <p class="muted"><?= $kulinerAktif
                    ? 'Belum ada data kuliner untuk kecamatan ini, atau server kelompok kuliner belum dapat dihubungi.'
                    : 'Bagian ini akan terisi otomatis setelah API kelompok kuliner dihubungkan (lihat halaman API).' ?></p>
            <?php endif; ?>
        </section>

        <section class="panel" id="tanya">
            <h2>Tanya / pesan hotel ini</h2>
            <p class="muted">Kirim pertanyaan atau rencana menginapmu. Admin akan membalas lewat email.</p>
            <form class="form form--grid" method="post" action="<?= site_url('penginapan/' . $h['id'] . '/pesan') ?>">
                <?= token_field() ?>
                <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label>Nama<input type="text" name="nama" required maxlength="100" value="<?= old('nama', $u['nama'] ?? '') ?>"></label>
                <label>Email<input type="email" name="email" required maxlength="190" value="<?= old('email', $u['email'] ?? '') ?>"></label>
                <label>No. HP / WhatsApp<input type="text" name="telepon" maxlength="30" value="<?= old('telepon') ?>"></label>
                <label>Jumlah tamu<input type="number" name="jumlah_tamu" min="1" max="20" value="<?= old('jumlah_tamu', 2) ?>"></label>
                <label>Check-in<input type="date" name="tgl_checkin" min="<?= date('Y-m-d') ?>" value="<?= old('tgl_checkin') ?>"></label>
                <label>Check-out<input type="date" name="tgl_checkout" min="<?= date('Y-m-d') ?>" value="<?= old('tgl_checkout') ?>"></label>
                <label class="full">Pesan<textarea name="pesan" rows="3" maxlength="1000"><?= old('pesan') ?></textarea></label>
                <div class="full"><button class="btn" type="submit">Kirim pesan</button></div>
            </form>
        </section>
    </div>

    <aside class="detail__side">
        <div class="panel panel--tight">
            <form method="post" action="<?= site_url('favorit/' . $h['id']) ?>">
                <?= token_field() ?>
                <button class="btn btn--block <?= $isFav ? '' : 'btn--ghost' ?>" type="submit"><?= $isFav ? '♥ Tersimpan di favorit' : '♡ Simpan ke favorit' ?></button>
            </form>
            <a class="btn btn--block btn--ghost" style="margin-top:.6rem" href="<?= site_url('bandingkan/toggle/' . $h['id']) ?>"><?= $inCmp ? '✓ Ada di perbandingan' : '⇄ Tambah ke perbandingan' ?></a>
        </div>
        <?php if ($serupa): ?>
            <h2>Di kecamatan yang sama</h2>
            <?php foreach ($serupa as $s) { echo hotel_card($s); } ?>
        <?php endif; ?>
    </aside>
</div>
<?= $this->endSection() ?>
