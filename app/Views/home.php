<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<section class="hero">
    <div class="wrap hero__in">
        <p class="eyebrow eyebrow--gold">Medan · Sumatera Utara</p>
        <h1>Singgah dulu,<br><span>menginap nyaman</span> di Medan.</h1>
        <p class="hero__lead"><?= (int) $stats['total'] ?> penginapan dari hotel bintang hingga homestay, lengkap dengan alamat, rating, dan ulasan tamu.</p>

        <form class="search" action="<?= site_url('penginapan') ?>" method="get">
            <label class="search__field search__field--grow">
                <span>Cari</span>
                <input type="search" name="q" placeholder="Nama hotel atau jalan…">
            </label>
            <label class="search__field">
                <span>Kecamatan</span>
                <select name="kecamatan">
                    <option value="">Semua</option>
                    <?php foreach ($kecamatan as $k): ?>
                        <option value="<?= esc($k['kecamatan'], 'attr') ?>"><?= esc($k['kecamatan']) ?> (<?= (int) $k['jumlah'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="search__field">
                <span>Sarapan</span>
                <select name="sarapan"><option value="">Bebas</option><option value="ya">Termasuk sarapan</option></select>
            </label>
            <button class="btn btn--gold" type="submit">Cari penginapan</button>
        </form>

        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1.2rem;align-items:center">
            <span style="color:#d1fae5;font-size:.85rem;font-weight:600">Pilihan cepat:</span>
            <a class="chip-link" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.82rem" href="<?= site_url('penginapan?rating_min=4.5&sort=rating') ?>">⭐ Rating 4.5+</a>
            <a class="chip-link" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.82rem" href="<?= site_url('penginapan?sarapan=ya') ?>">🍳 Dengan Sarapan</a>
            <a class="chip-link" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.82rem" href="<?= site_url('penginapan?kecamatan=Medan+Petisah') ?>">🏙️ Medan Petisah (Pusat)</a>
            <a class="chip-link" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.82rem" href="<?= site_url('penginapan?kecamatan=Medan+Kota') ?>">🏢 Medan Kota</a>
            <a class="chip-link" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.82rem" href="<?= site_url('peta') ?>">🗺️ Lihat di Peta</a>
        </div>
    </div>
</section>

<section class="wrap stats">
    <div><strong><?= (int) $stats['total'] ?></strong><span>Penginapan</span></div>
    <div><strong><?= (int) $stats['kecamatan'] ?></strong><span>Kecamatan</span></div>
    <div><strong><?= number_format((float) $stats['rata'], 1) ?> ★</strong><span>Rata-rata rating</span></div>
    <div><strong><?= number_format((int) $stats['ulasan'], 0, ',', '.') ?></strong><span>Ulasan tamu</span></div>
</section>

<section class="wrap section">
    <div class="section__head">
        <div>
            <p class="eyebrow">Pilihan tamu</p>
            <h2>Paling disukai &amp; ramai diulas</h2>
        </div>
        <a class="link" href="<?= site_url('penginapan?sort=rating&rating_min=4.5') ?>">Lihat semua →</a>
    </div>
    <div class="grid">
        <?php foreach ($unggulan as $h) { echo hotel_card($h); } ?>
    </div>
</section>

<section class="wrap section">
    <div class="section__head"><div><p class="eyebrow">Jelajah wilayah</p><h2>Menginap di mana?</h2></div></div>
    <div class="chips">
        <?php foreach ($kecamatan as $k): ?>
            <a class="chip-link" href="<?= site_url('penginapan?kecamatan=' . rawurlencode($k['kecamatan'])) ?>">
                <?= esc($k['kecamatan']) ?> <b><?= (int) $k['jumlah'] ?></b>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="wrap section">
    <div class="cta">
        <div>
            <p class="eyebrow eyebrow--gold">Terhubung dengan kuliner</p>
            <h2>Lapar setelah check-in?</h2>
            <p>Setiap halaman penginapan menampilkan tempat makan di kecamatan yang sama, dari data kelompok kuliner Medan.</p>
        </div>
        <a class="btn btn--gold" href="<?= site_url('kuliner') ?>">Jelajah kuliner</a>
    </div>
</section>

<?= $this->endSection() ?>
