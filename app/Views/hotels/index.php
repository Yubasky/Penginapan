<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<section class="pagehead">
    <div class="wrap">
        <p class="eyebrow eyebrow--gold">Direktori</p>
        <h1>Penginapan di Medan</h1>
        <p><?= number_format($total, 0, ',', '.') ?> hasil<?= $f['q'] !== '' ? ' untuk “' . esc($f['q']) . '”' : '' ?><?= ! empty($f['lat']) && ! empty($f['lng']) ? ' (diurutkan berdasarkan jarak terdekat)' : '' ?></p>
    </div>
</section>

<div class="wrap layout">
    <aside class="filters">
        <form method="get" action="<?= site_url('penginapan') ?>" id="filter-form">
            <input type="hidden" name="lat" id="gps-lat" value="<?= esc($f['lat'] ?? '', 'attr') ?>">
            <input type="hidden" name="lng" id="gps-lng" value="<?= esc($f['lng'] ?? '', 'attr') ?>">

            <div style="margin-bottom:1.2rem">
                <button type="button" id="btn-cari-gps" class="btn btn--sm btn--gps <?= ! empty($f['lat']) && ! empty($f['lng']) ? 'is-active' : '' ?>" style="width:100%;justify-content:center;padding:.65rem 1rem">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="7"/></svg>
                    <span id="cari-gps-text"><?= ! empty($f['lat']) && ! empty($f['lng']) ? '📍 Lokasi Terdeteksi' : '📍 Cari Terdekat dari Saya' ?></span>
                </button>
            </div>

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
                <select name="sort" id="sort-select">
                    <?php 
                    $sortOptions = [
                        'terdekat' => '📍 Terdekat dari saya',
                        'acak'     => 'Acak (Default)',
                        'rating'   => 'Rating tertinggi',
                        'ulasan'   => 'Ulasan terbanyak',
                        'nama'     => 'Nama A–Z',
                        'awal'     => 'Urutan Tripadvisor',
                    ];
                    foreach ($sortOptions as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $f['sort'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn" type="submit">Terapkan</button>
            <a class="link" href="<?= site_url('penginapan') ?>" onclick="window.SinggahGPS.clearLocation()">Reset filter</a>
        </form>
    </aside>

    <section class="results">
        <?php if (! empty($f['lat']) && ! empty($f['lng'])): ?>
            <div class="gps-banner">
                <span>📍 Menampilkan hasil urutan penginapan terdekat dari posisi Anda saat ini.</span>
                <a href="<?= site_url('penginapan') ?>" onclick="window.SinggahGPS.clearLocation()">Matikan GPS</a>
            </div>
        <?php endif; ?>

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

<?= $this->section('scripts') ?>
<script>
(function () {
  var btn = document.getElementById('btn-cari-gps');
  var txt = document.getElementById('cari-gps-text');
  var form = document.getElementById('filter-form');
  var inLat = document.getElementById('gps-lat');
  var inLng = document.getElementById('gps-lng');
  var selSort = document.getElementById('sort-select');

  if (btn) {
    btn.addEventListener('click', function () {
      txt.textContent = 'Mendeteksi lokasi...';
      window.SinggahGPS.getLocation(function (pos) {
        inLat.value = pos.lat;
        inLng.value = pos.lng;
        selSort.value = 'terdekat';
        txt.textContent = 'Lokasi Terdeteksi!';
        form.submit();
      }, function (err) {
        txt.textContent = '📍 Cari Terdekat dari Saya';
        alert('Tidak dapat mendeteksi GPS: ' + err.message);
      });
    });
  }

  // Jika user memilih urutkan "terdekat" tapi koordinat belum ada di input
  if (selSort) {
    selSort.addEventListener('change', function () {
      if (selSort.value === 'terdekat' && (!inLat.value || !inLng.value)) {
        var cached = window.SinggahGPS.getCachedLocation();
        if (cached) {
          inLat.value = cached.lat;
          inLng.value = cached.lng;
          form.submit();
        } else {
          window.SinggahGPS.getLocation(function (pos) {
            inLat.value = pos.lat;
            inLng.value = pos.lng;
            form.submit();
          }, function (err) {
            alert('Akses lokasi diperlukan untuk mengurutkan dari terdekat: ' + err.message);
          });
        }
      }
    });
  }
})();
</script>
<?= $this->endSection() ?>
