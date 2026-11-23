<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Dari kelompok kuliner</p>
    <h1>Kuliner di Medan</h1>
    <p><?= number_format($total, 0, ',', '.') ?> tempat makan<?= ! empty($f['lat']) && ! empty($f['lng']) ? ' (diurutkan berdasarkan jarak terdekat dari lokasi Anda)' : '' ?></p>
</div></section>

<div class="wrap" style="padding:1.5rem 0 4rem">
    <form class="mapbar" method="get" action="<?= site_url('kuliner') ?>" id="kuliner-form" style="align-items:center;gap:.6rem">
        <input type="hidden" name="lat" id="gps-lat" value="<?= esc($f['lat'] ?? '', 'attr') ?>">
        <input type="hidden" name="lng" id="gps-lng" value="<?= esc($f['lng'] ?? '', 'attr') ?>">
        <input type="hidden" name="sort" id="gps-sort" value="<?= esc($f['sort'] ?? '', 'attr') ?>">

        <div class="mapbar__search" style="flex:2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" value="<?= esc($f['q'], 'attr') ?>" placeholder="Cari nama tempat, kategori, alamat…">
        </div>

        <select name="kecamatan" onchange="this.form.submit()" style="flex:1">
            <option value="">Semua kecamatan</option>
            <?php foreach ($kecamatan as $k): ?>
                <option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= $f['kecamatan'] === $k['kecamatan'] ? 'selected' : '' ?>>
                    <?= esc($k['kecamatan']) ?> (<?= (int) $k['jumlah'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn--sm" type="submit">Cari</button>

        <button type="button" id="btn-kuliner-gps" class="btn btn--sm btn--gps <?= ! empty($f['lat']) && ! empty($f['lng']) ? 'is-active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="7"/></svg>
            <span id="kuliner-gps-text"><?= ! empty($f['lat']) && ! empty($f['lng']) ? '📍 Terdekat (Aktif)' : '📍 Terdekat dari Saya' ?></span>
        </button>

        <?php if (! empty($f['q']) || ! empty($f['kecamatan']) || ! empty($f['lat'])): ?>
            <a href="<?= site_url('kuliner') ?>" class="btn btn--sm btn--ghost" onclick="window.SinggahGPS.clearLocation()">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (! empty($f['lat']) && ! empty($f['lng'])): ?>
        <div class="gps-banner">
            <span>📍 Menampilkan tempat kuliner terdekat dari koordinat posisi Anda saat ini.</span>
            <a href="<?= site_url('kuliner') ?>" onclick="window.SinggahGPS.clearLocation()">Matikan GPS</a>
        </div>
    <?php endif; ?>

    <?php if (! $items): ?>
        <div class="empty"><h3>Belum ada data kuliner</h3><p>Data masuk setelah admin menekan “Sinkron sekarang” (Admin → Integrasi Kuliner) atau kelompok kuliner mengirim lewat API.</p></div>
    <?php else: ?>
        <div class="food food--3"><?php foreach ($items as $k) { echo kuliner_card($k); } ?></div>
        <?= paginasi($page, $pages) ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var btn = document.getElementById('btn-kuliner-gps');
  var txt = document.getElementById('kuliner-gps-text');
  var form = document.getElementById('kuliner-form');
  var inLat = document.getElementById('gps-lat');
  var inLng = document.getElementById('gps-lng');
  var inSort = document.getElementById('gps-sort');

  if (btn) {
    btn.addEventListener('click', function () {
      txt.textContent = 'Mendeteksi...';
      window.SinggahGPS.getLocation(function (pos) {
        inLat.value = pos.lat;
        inLng.value = pos.lng;
        inSort.value = 'terdekat';
        txt.textContent = 'Lokasi Terdeteksi!';
        form.submit();
      }, function (err) {
        txt.textContent = '📍 Terdekat dari Saya';
        alert('Gagal mendeteksi lokasi GPS: ' + err.message);
      });
    });
  }
})();
</script>
<?= $this->endSection() ?>
