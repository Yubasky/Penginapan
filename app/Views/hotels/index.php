<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<section class="pagehead">
    <div class="wrap">
        <p class="eyebrow eyebrow--gold">Direktori</p>
        <h1>Penginapan di Medan</h1>
        <p><?= number_format($total, 0, ',', '.') ?> hasil<?= $f['q'] !== '' ? ' untuk “' . esc($f['q']) . '”' : '' ?></p>
    </div>
</section>

<div class="wrap layout">
    <aside class="filters">
        <form method="get" action="<?= site_url('penginapan') ?>" id="filter-form">
            <label>Cari
                <input type="search" name="q" value="<?= esc($f['q'], 'attr') ?>" placeholder="Nama atau jalan…">
            </label>
            <label>Kecamatan
                <select name="kecamatan">
                    <option value="">Semua kecamatan</option>
                    <?php foreach ($kecamatan as $k): ?>
                        <option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= $f['kecamatan'] === $k['kecamatan'] ? 'selected' : '' ?>>
                            <?= esc($k['kecamatan']) ?> (<?= (int) $k['jumlah'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Rating minimal
                <select name="rating_min">
                    <?php foreach (['' => 'Semua', '3' => '3.0+', '4' => '4.0+', '4.5' => '4.5+'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $f['rating_min'] === (string) $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="check">
                <input type="checkbox" name="sarapan" value="ya" <?= $f['sarapan'] === 'ya' ? 'checked' : '' ?>> Termasuk sarapan
            </label>
            <label>Urutkan
                <select name="sort">
                    <?php foreach (['awal' => 'Urutan Tripadvisor', 'rating' => 'Rating tertinggi', 'ulasan' => 'Ulasan terbanyak', 'nama' => 'Nama A–Z'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $f['sort'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn" type="submit">Terapkan</button>
            <a class="link" href="<?= site_url('penginapan') ?>">Reset filter</a>
        </form>
    </aside>

    <section class="results">
        <?php if (! $hotels): ?>
            <div class="empty"><h3>Belum ada yang cocok</h3><p>Coba longgarkan filter atau kata kunci.</p></div>
        <?php else: ?>
            <div class="grid grid--2">
                <?php foreach ($hotels as $h) { echo hotel_card($h); } ?>
            </div>
            <?= paginasi($page, $pages) ?>
        <?php endif; ?>
    </section>
</div>

<?= $this->endSection() ?>
