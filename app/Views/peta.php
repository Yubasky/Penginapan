<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Peta</p>
    <h1>Penginapan di peta Medan</h1>
    <p><?= count($titik) ?> hotel ditampilkan<?= ! empty($f['q']) ? ' untuk pencarian “' . esc($f['q']) . '”' : '' ?><?= ! empty($f['kecamatan']) ? ' di ' . esc($f['kecamatan']) : '' ?></p>
</div></section>
<div class="wrap" style="padding:1.5rem 0 4rem">
    <form class="mapbar" method="get" action="<?= site_url('peta') ?>">
        <div class="mapbar__search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" placeholder="Cari nama hotel atau alamat di peta..." value="<?= esc($f['q'] ?? '') ?>">
        </div>
        <select name="kecamatan" onchange="this.form.submit()">
            <option value="">Semua kecamatan</option>
            <?php foreach ($kecamatan as $k): ?><option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= $f['kecamatan'] === $k['kecamatan'] ? 'selected' : '' ?>><?= esc($k['kecamatan']) ?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn--sm">Cari</button>
        <?php if (! empty($f['q']) || ! empty($f['kecamatan'])): ?>
            <a href="<?= site_url('peta') ?>" class="btn btn--sm btn--ghost">Reset</a>
        <?php endif; ?>
    </form>
    <?php if ($totalGeo === 0): ?>
        <div class="empty"><h3>Koordinat hotel belum diisi</h3>
            <p>Jalankan perintah ini di terminal proyek (butuh internet, sekitar 5–10 menit):</p>
            <p><code>php spark hotel:geocode</code></p>
            <p class="muted">Koordinat yang meleset bisa dikoreksi manual lewat Admin → Kelola Hotel.</p></div>
    <?php elseif (empty($titik)): ?>
        <div class="empty">
            <h3>Hotel tidak ditemukan pada pencarian ini</h3>
            <p class="muted">Coba gunakan kata kunci lain atau pilih kecamatan yang berbeda.</p>
            <a href="<?= site_url('peta') ?>" class="btn btn--sm">Tampilkan semua hotel di peta</a>
        </div>
    <?php else: ?>
        <div id="map" class="map"></div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if (! empty($titik)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var data = <?= json_encode($titik, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
  var map = L.map('map').setView([3.5952, 98.6722], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
  var pts = [];
  data.forEach(function (h) {
    var box = document.createElement('div');
    box.style.minWidth = '180px';
    var a = document.createElement('a'); a.href = h.url; a.textContent = h.nama; a.style.fontWeight = '700'; a.style.fontSize = '1rem'; a.style.display = 'block'; a.style.marginBottom = '3px';
    var s = document.createElement('div');
    s.style.fontSize = '.85rem'; s.style.color = '#64748b'; s.style.marginBottom = '5px';
    s.textContent = (h.kecamatan || '') + (h.rating ? ' · ★ ' + h.rating.toFixed(1) : '');
    box.appendChild(a); box.appendChild(s);
    if (h.alamat) {
      var addr = document.createElement('div');
      addr.style.fontSize = '.78rem'; addr.style.color = '#475569'; addr.style.marginBottom = '8px';
      addr.textContent = h.alamat;
      box.appendChild(addr);
    }
    var btn = document.createElement('a');
    btn.href = h.url; btn.textContent = 'Lihat Detail →'; btn.style.display = 'inline-block'; btn.style.fontSize = '.8rem'; btn.style.fontWeight = '600'; btn.style.color = '#0f766e';
    box.appendChild(btn);

    L.marker([h.lat, h.lng]).addTo(map).bindPopup(box);
    pts.push([h.lat, h.lng]);
  });
  if (pts.length) map.fitBounds(pts, {padding: [30, 30], maxZoom: 15});
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
