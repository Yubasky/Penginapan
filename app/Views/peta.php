<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Peta</p>
    <h1>Penginapan di peta Medan</h1>
    <p><?= count($titik) ?> hotel ditampilkan</p>
</div></section>
<div class="wrap" style="padding:1.5rem 0 4rem">
    <form class="mapbar" method="get" action="<?= site_url('peta') ?>">
        <select name="kecamatan" onchange="this.form.submit()">
            <option value="">Semua kecamatan</option>
            <?php foreach ($kecamatan as $k): ?><option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= $f['kecamatan'] === $k['kecamatan'] ? 'selected' : '' ?>><?= esc($k['kecamatan']) ?></option><?php endforeach; ?>
        </select>
    </form>
    <?php if ($totalGeo === 0): ?>
        <div class="empty"><h3>Koordinat hotel belum diisi</h3>
            <p>Jalankan perintah ini di terminal proyek (butuh internet, sekitar 5–10 menit):</p>
            <p><code>php spark hotel:geocode</code></p>
            <p class="muted">Koordinat yang meleset bisa dikoreksi manual lewat Admin → Kelola Hotel.</p></div>
    <?php else: ?>
        <div id="map" class="map"></div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($totalGeo > 0): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var data = <?= json_encode($titik, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
  var map = L.map('map').setView([3.5952, 98.6722], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
  var pts = [];
  data.forEach(function (h) {
    var box = document.createElement('div');
    var a = document.createElement('a'); a.href = h.url; a.textContent = h.nama; a.style.fontWeight = '700';
    var s = document.createElement('div');
    s.textContent = (h.kecamatan || '') + (h.rating ? ' · ★ ' + h.rating.toFixed(1) : '');
    box.appendChild(a); box.appendChild(s);
    L.marker([h.lat, h.lng]).addTo(map).bindPopup(box);
    pts.push([h.lat, h.lng]);
  });
  if (pts.length) map.fitBounds(pts, {padding: [30, 30], maxZoom: 15});
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
