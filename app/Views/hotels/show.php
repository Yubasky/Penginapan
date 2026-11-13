<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$u      = session('user');
$isFav  = in_array((int) $h['id'], favorit_ids(), true);
$inCmp  = in_array((int) $h['id'], bandingkan_ids(), true);
?>
<section class="detail__hero <?= ! empty($h['foto']) ? 'detail__hero--has-cover' : '' ?>" style="--h: <?= hue_hotel($h) ?><?= ! empty($h['foto']) ? '; background-image: linear-gradient(to bottom, rgba(10,46,42,0.7), rgba(10,46,42,0.92)), url(\'' . base_url(esc($h['foto'])) . '\'); background-size: cover; background-position: center;' : '' ?>">
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

        <section class="panel" id="fasilitas">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem">
                <h2 style="margin:0">📸 Foto &amp; Video Fasilitas Hotel</h2>
                <?php 
                $adminFasilitas = ! empty($h['foto_fasilitas']) ? (json_decode($h['foto_fasilitas'], true) ?: []) : [];
                $semuaFasilitas = [];
                foreach ($adminFasilitas as $af) {
                    if (! empty($af['url'])) {
                        $semuaFasilitas[] = [
                            'url'     => $af['url'],
                            'type'    => 'image',
                            'sumber'  => 'hotel',
                            'penulis' => 'Pihak Hotel',
                            'caption' => 'Foto Resmi Fasilitas Hotel (Pihak Hotel)',
                        ];
                    }
                }
                foreach ($mediaFasilitas as $mf) {
                    $semuaFasilitas[] = [
                        'url'     => $mf['url'],
                        'type'    => $mf['type'] ?? 'image',
                        'sumber'  => 'tamu',
                        'penulis' => $mf['penulis'],
                        'rating'  => $mf['rating'] ?? null,
                        'caption' => 'Fasilitas oleh Tamu: ' . $mf['penulis'],
                    ];
                }
                ?>
                <?php if (! empty($semuaFasilitas)): ?>
                    <span class="chip"><?= count($semuaFasilitas) ?> dokumentasi fasilitas</span>
                <?php endif; ?>
            </div>
            <p class="muted small" style="margin-top:0;margin-bottom:1rem">Dokumentasi nyata fasilitas (kamar, sarapan, kolam renang, toilet, dsb.) dari pihak hotel dan ulasan pengunjung.</p>
            
            <?php if (! empty($semuaFasilitas)): ?>
                <div class="media-gallery">
                    <?php foreach ($semuaFasilitas as $sf): ?>
                        <div class="media-gallery__item">
                            <?php if ($sf['type'] === 'video'): ?>
                                <div class="media-video-wrap">
                                    <video src="<?= base_url(esc($sf['url'])) ?>" controls preload="metadata"></video>
                                    <span class="media-badge">🎥 Video Fasilitas</span>
                                </div>
                            <?php else: ?>
                                <a href="<?= base_url(esc($sf['url'])) ?>" class="media-img-link js-lightbox" data-gallery="fasilitas-utama" data-caption="Fasilitas <?= esc($h['nama']) ?> · <?= esc($sf['caption']) ?>" title="Klik untuk melihat foto">
                                    <img src="<?= base_url(esc($sf['url'])) ?>" alt="Fasilitas <?= esc($h['nama']) ?>" loading="lazy">
                                    <span class="media-badge <?= $sf['sumber'] === 'hotel' ? 'media-badge--hotel' : '' ?>">
                                        <?= $sf['sumber'] === 'hotel' ? '🏢 Pihak Hotel' : '📷 Oleh Tamu' ?>
                                    </span>
                                </a>
                            <?php endif; ?>
                            <div class="media-gallery__caption">
                                <?php if ($sf['sumber'] === 'hotel'): ?>
                                    <span class="media-author">Oleh <strong>Pihak Hotel</strong></span>
                                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:0.72rem;font-weight:700">Resmi</span>
                                <?php else: ?>
                                    <span class="media-author">Oleh <strong><?= esc($sf['penulis']) ?></strong></span>
                                    <?php if (! empty($sf['rating'])): ?>
                                        <span class="stars"><?= str_repeat('★', (int) $sf['rating']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background:#faf8f2;border:1.5px dashed var(--line);border-radius:12px;padding:1.2rem;text-align:center">
                    <p class="muted" style="margin:0">Belum ada foto atau video fasilitas. Pernah menginap di sini? Tulis ulasanmu di bawah dan sertakan foto/video fasilitas hotel!</p>
                </div>
            <?php endif; ?>
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
                        <div class="rev__top">
                            <strong><?= esc($r['penulis']) ?></strong>
                            <span class="stars"><?= str_repeat('★', (int) $r['rating']) ?><span class="stars--off"><?= str_repeat('★', 5 - (int) $r['rating']) ?></span></span>
                            <span class="muted small"><?= tgl_id($r['created_at']) ?></span>
                        </div>
                        <?php if ($r['judul']): ?><p class="rev__t"><?= esc($r['judul']) ?></p><?php endif; ?>
                        <p><?= nl2br(esc($r['isi'])) ?></p>

                        <?php 
                        $rMedia = ! empty($r['media']) ? (json_decode($r['media'], true) ?: []) : [];
                        if ($rMedia): ?>
                            <div class="rev__media">
                                <?php foreach ($rMedia as $rmItem): ?>
                                    <?php if (($rmItem['type'] ?? '') === 'video'): ?>
                                        <div class="rev__video-box">
                                            <video src="<?= base_url(esc($rmItem['url'])) ?>" controls preload="metadata"></video>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= base_url(esc($rmItem['url'])) ?>" class="rev__thumb js-lightbox" data-gallery="ulasan-<?= $r['id'] ?>" data-caption="Foto fasilitas ulasan dari <?= esc($r['penulis']) ?>" title="Klik untuk melihat foto">
                                            <img src="<?= base_url(esc($rmItem['url'])) ?>" alt="Foto fasilitas ulasan" loading="lazy">
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="muted">Belum ada ulasan dari pengunjung. Jadilah yang pertama!</p>
            <?php endif; ?>

            <div style="margin-top:2rem;border-top:1px solid var(--line);padding-top:1.5rem">
            <?php if ($u): ?>
                <?php if ($ulasanSaya): ?>
                    <!-- Kartu ulasan milik pengguna (Hanya 1 ulasan) -->
                    <div class="my-review-card">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.7rem">
                            <div>
                                <span class="chip" style="background:#0f766e;color:#fff;font-weight:700">✓ Ulasan Anda</span>
                                <p class="muted small" style="margin:.4rem 0 0">Setiap pengguna hanya dapat memberikan 1 ulasan untuk penginapan ini. Anda dapat mengedit ulasan ini kapan saja.</p>
                            </div>
                            <div style="display:flex;gap:.5rem;align-items:center">
                                <button type="button" class="btn btn--sm" onclick="toggleEditUlasan()">✏️ Edit Ulasan</button>
                                <form method="post" action="<?= site_url('penginapan/' . $h['id'] . '/ulasan/hapus') ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                    <?= token_field() ?>
                                    <button type="submit" class="btn btn--sm btn--ghost" style="color:#dc2626;border-color:#fca5a5">🗑️ Hapus</button>
                                </form>
                            </div>
                        </div>

                        <div style="margin-top:1rem;background:#fff;border-radius:10px;padding:1rem;border:1px solid #bbf7d0">
                            <div class="rev__top">
                                <strong><?= esc($u['nama']) ?></strong>
                                <span class="stars"><?= str_repeat('★', (int) $ulasanSaya['rating']) ?><span class="stars--off"><?= str_repeat('★', 5 - (int) $ulasanSaya['rating']) ?></span></span>
                                <span class="muted small"><?= tgl_id($ulasanSaya['created_at']) ?></span>
                            </div>
                            <?php if (! empty($ulasanSaya['judul'])): ?>
                                <p class="rev__t"><?= esc($ulasanSaya['judul']) ?></p>
                            <?php endif; ?>
                            <p><?= nl2br(esc($ulasanSaya['isi'])) ?></p>

                            <?php 
                            $myMedia = ! empty($ulasanSaya['media']) ? (json_decode($ulasanSaya['media'], true) ?: []) : [];
                            if ($myMedia): ?>
                                <div class="rev__media">
                                    <?php foreach ($myMedia as $mmItem): ?>
                                        <?php if (($mmItem['type'] ?? '') === 'video'): ?>
                                            <div class="rev__video-box">
                                                <video src="<?= base_url(esc($mmItem['url'])) ?>" controls preload="metadata"></video>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= base_url(esc($mmItem['url'])) ?>" class="rev__thumb js-lightbox" data-gallery="ulasan-saya" data-caption="Foto fasilitas ulasan Anda" title="Klik untuk melihat foto">
                                                <img src="<?= base_url(esc($mmItem['url'])) ?>" alt="Foto fasilitas" loading="lazy">
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Formulir Edit Ulasan (Toggle) -->
                    <form id="form-edit-ulasan" class="form" method="post" action="<?= site_url('penginapan/' . $h['id'] . '/ulasan') ?>" enctype="multipart/form-data" style="display:<?= session('error') ? 'flex' : 'none' ?>;background:#fbfbfb;border:1.5px solid var(--teal);padding:1.3rem;border-radius:14px">
                        <?= token_field() ?>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <h3 style="margin:0;color:var(--teal)">Edit Ulasan Anda</h3>
                            <button type="button" class="btn btn--sm btn--ghost" onclick="toggleEditUlasan()">✕ Batal</button>
                        </div>
                        <?php $rt = (int) (old('rating') ?: ($ulasanSaya['rating'] ?? 0)); ?>
                        <label>Rating Bintang
                            <div class="starpick" role="radiogroup" aria-label="Rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="st_edit_<?= $i ?>" name="rating" value="<?= $i ?>" <?= $rt === $i ? 'checked' : '' ?> required><label for="st_edit_<?= $i ?>" title="<?= $i ?> bintang">★</label>
                                <?php endfor; ?>
                            </div>
                        </label>
                        <label>Judul Ulasan (opsional)
                            <input type="text" name="judul" maxlength="150" value="<?= esc(old('judul', $ulasanSaya['judul'] ?? '')) ?>" placeholder="Contoh: Menginap yang sangat nyaman!">
                        </label>
                        <label>Isi Ulasan Pengalaman Menginap
                            <textarea name="isi" rows="4" minlength="10" maxlength="2000" required><?= esc(old('isi', $ulasanSaya['isi'] ?? '')) ?></textarea>
                        </label>

                        <?php if ($myMedia): ?>
                            <div>
                                <label style="margin-bottom:.4rem">Foto / Video Fasilitas yang Sudah Diunggah:</label>
                                <div style="display:flex;flex-wrap:wrap;gap:.8rem">
                                    <?php foreach ($myMedia as $idx => $mItem): ?>
                                        <div style="border:1px solid #cbd5e1;border-radius:8px;padding:.5rem;background:#fff;display:flex;flex-direction:column;align-items:center;gap:.3rem">
                                            <?php if (($mItem['type'] ?? '') === 'video'): ?>
                                                <video src="<?= base_url(esc($mItem['url'])) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px"></video>
                                            <?php else: ?>
                                                <img src="<?= base_url(esc($mItem['url'])) ?>" alt="Media" style="width:80px;height:80px;object-fit:cover;border-radius:6px">
                                            <?php endif; ?>
                                            <label style="font-size:.75rem;color:#dc2626;cursor:pointer;flex-direction:row;align-items:center;gap:.2rem">
                                                <input type="checkbox" name="hapus_media[]" value="<?= esc($mItem['url'], 'attr') ?>"> Hapus
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <label>Tambah Foto / Video Fasilitas Baru (opsional)
                            <span class="muted small">Pilih berkas foto (JPG, PNG, WEBP) atau video (MP4, WEBM) fasilitas hotel seperti kamar, toilet, kolam renang, sarapan, dll.</span>
                            <input type="file" name="media[]" multiple accept="image/*,video/mp4,video/webm">
                        </label>

                        <div style="display:flex;gap:.7rem;margin-top:.5rem">
                            <button class="btn" type="submit">Simpan Perubahan Ulasan</button>
                            <button class="btn btn--ghost" type="button" onclick="toggleEditUlasan()">Batal</button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Formulir Tulis Ulasan Baru -->
                    <form class="form" method="post" action="<?= site_url('penginapan/' . $h['id'] . '/ulasan') ?>" enctype="multipart/form-data">
                        <?= token_field() ?>
                        <h3>Tulis Ulasan Pengalaman Menginap</h3>
                        <p class="muted small" style="margin-top:-.3rem">Bagikan pengalaman dan fasilitas hotel untuk membantu wisatawan lain.</p>
                        <?php $rt = (int) old('rating', 0); ?>
                        <label>Rating Bintang
                            <div class="starpick" role="radiogroup" aria-label="Rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="st<?= $i ?>" name="rating" value="<?= $i ?>" <?= $rt === $i ? 'checked' : '' ?> required><label for="st<?= $i ?>" title="<?= $i ?> bintang">★</label>
                                <?php endfor; ?>
                            </div>
                        </label>
                        <label>Judul Ulasan (opsional)
                            <input type="text" name="judul" maxlength="150" value="<?= esc(old('judul')) ?>" placeholder="Contoh: Kamar luas, sarapan enak!">
                        </label>
                        <label>Ceritakan Pengalamanmu
                            <textarea name="isi" rows="4" minlength="10" maxlength="2000" placeholder="Bagaimana kondisi kamar, pelayanan, dan fasilitas hotel selama kamu menginap?" required><?= esc(old('isi')) ?></textarea>
                        </label>
                        <label>Foto &amp; Video Fasilitas Hotel (opsional)
                            <span class="muted small">Unggah dokumentasi foto (JPG, PNG, WEBP) atau video (MP4, WEBM) fasilitas seperti kamar tidur, kamar mandi, kolam renang, dll. (maks 30MB)</span>
                            <input type="file" name="media[]" multiple accept="image/*,video/mp4,video/webm">
                        </label>
                        <div>
                            <button class="btn" type="submit">Kirim Ulasan</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted"><a class="link" href="<?= site_url('masuk') ?>">Masuk</a> atau <a class="link" href="<?= site_url('daftar') ?>">daftar</a> untuk menulis ulasan dan mengunggah foto/video fasilitas.</p>
            <?php endif; ?>
            </div>
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

<!-- Modal Lightbox Galeri Foto -->
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-hidden="true">
    <button class="lightbox__btn lightbox__close" id="lb-close" aria-label="Tutup pratinjau">✕</button>
    <div class="lightbox__counter" id="lb-counter"></div>
    <button class="lightbox__btn lightbox__prev" id="lb-prev" aria-label="Foto sebelumnya">❮</button>
    <div class="lightbox__content">
        <img class="lightbox__img" id="lb-img" src="" alt="Pratinjau foto fasilitas">
        <div class="lightbox__caption" id="lb-caption"></div>
    </div>
    <button class="lightbox__btn lightbox__next" id="lb-next" aria-label="Foto berikutnya">❯</button>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function toggleEditUlasan() {
    var form = document.getElementById('form-edit-ulasan');
    if (!form) return;
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'flex';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        form.style.display = 'none';
    }
}

// Interaktif Lightbox Foto Fasilitas
(function () {
    var lb = document.getElementById('lightbox');
    if (!lb) return;
    var lbImg = document.getElementById('lb-img');
    var lbCaption = document.getElementById('lb-caption');
    var lbCounter = document.getElementById('lb-counter');
    var lbPrev = document.getElementById('lb-prev');
    var lbNext = document.getElementById('lb-next');
    var lbClose = document.getElementById('lb-close');

    var currentGallery = [];
    var currentIndex = 0;

    function openLightbox(galleryItems, index) {
        if (!galleryItems.length) return;
        currentGallery = galleryItems;
        currentIndex = index;
        updateLightbox();
        lb.classList.add('is-open');
        lb.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lb.classList.remove('is-open');
        lb.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    function updateLightbox() {
        if (!currentGallery.length) return;
        var item = currentGallery[currentIndex];
        lbImg.style.opacity = '0';
        setTimeout(function () {
            lbImg.src = item.src;
            lbImg.alt = item.caption || 'Foto Fasilitas';
            lbImg.onload = function () {
                lbImg.style.opacity = '1';
            };
        }, 60);

        lbCaption.textContent = item.caption || '';

        if (currentGallery.length > 1) {
            lbCounter.style.display = 'block';
            lbCounter.textContent = (currentIndex + 1) + ' / ' + currentGallery.length;
            lbPrev.style.display = 'grid';
            lbNext.style.display = 'grid';
        } else {
            lbCounter.style.display = 'none';
            lbPrev.style.display = 'none';
            lbNext.style.display = 'none';
        }
    }

    function prevImage() {
        if (currentGallery.length <= 1) return;
        currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
        updateLightbox();
    }

    function nextImage() {
        if (currentGallery.length <= 1) return;
        currentIndex = (currentIndex + 1) % currentGallery.length;
        updateLightbox();
    }

    // Sambungkan ke semua tautan berkelas js-lightbox dengan deduplikasi URL
    document.querySelectorAll('.js-lightbox').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            var gName = el.getAttribute('data-gallery') || 'default';
            var groupElements = Array.from(document.querySelectorAll('.js-lightbox[data-gallery="' + gName + '"]'));
            
            var items = [];
            var seen = new Set();
            var targetSrc = el.getAttribute('href');
            var targetIdx = 0;

            groupElements.forEach(function (elem) {
                var s = elem.getAttribute('href');
                if (!seen.has(s)) {
                    seen.add(s);
                    if (s === targetSrc) {
                        targetIdx = items.length;
                    }
                    items.push({
                        src: s,
                        caption: elem.getAttribute('data-caption') || ''
                    });
                }
            });

            openLightbox(items, targetIdx);
        });
    });

    lbClose.addEventListener('click', closeLightbox);
    lbPrev.addEventListener('click', function (e) { e.stopPropagation(); prevImage(); });
    lbNext.addEventListener('click', function (e) { e.stopPropagation(); nextImage(); });

    // Klik di area gelap luar gambar untuk menutup
    lb.addEventListener('click', function (e) {
        if (e.target === lb || e.target.classList.contains('lightbox__content')) {
            closeLightbox();
        }
    });

    // Navigasi keyboard (Panah Kiri, Panah Kanan, Escape)
    window.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        else if (e.key === 'ArrowLeft') prevImage();
        else if (e.key === 'ArrowRight') nextImage();
    });
})();
</script>
<?= $this->endSection() ?>
