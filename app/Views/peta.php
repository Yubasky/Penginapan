<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Peta Interaktif</p>
    <h1>Peta Penginapan &amp; Kuliner Medan</h1>
    <p><?= (int) $totalHotel ?> penginapan<?= $totalKuliner > 0 ? ' dan ' . (int) $totalKuliner . ' tempat kuliner' : '' ?> di Kota Medan. Aktifkan GPS untuk menemukan lokasi terdekat!</p>
</div></section>

<div class="wrap" style="padding:1.5rem 0 4rem">
    <form class="mapbar" method="get" action="<?= site_url('peta') ?>" style="align-items:center;gap:.6rem">
        <div class="mapbar__search" style="flex:2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" placeholder="Cari nama atau alamat di peta..." value="<?= esc($f['q'] ?? '') ?>">
        </div>

        <select name="kecamatan" onchange="this.form.submit()" style="flex:1">
            <option value="">Semua kecamatan</option>
            <?php foreach ($kecamatan as $k): ?>
                <option value="<?= esc($k['kecamatan'], 'attr') ?>" <?= ($f['kecamatan'] ?? '') === $k['kecamatan'] ? 'selected' : '' ?>>
                    <?= esc($k['kecamatan']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="kategori" onchange="this.form.submit()" style="flex:1">
            <option value="semua" <?= ($f['kategori'] ?? '') === 'semua' ? 'selected' : '' ?>>Semua Tempat (<?= count($titik) ?>)</option>
            <option value="hotel" <?= ($f['kategori'] ?? '') === 'hotel' ? 'selected' : '' ?>>Hanya Penginapan (<?= (int) $totalHotel ?>)</option>
            <option value="kuliner" <?= ($f['kategori'] ?? '') === 'kuliner' ? 'selected' : '' ?>>Hanya Kuliner (<?= (int) $totalKuliner ?>)</option>
        </select>

        <button type="submit" class="btn btn--sm">Cari</button>

        <button type="button" id="btn-gps" class="btn btn--sm btn--gps" title="Ambil lokasi GPS perangkat Anda saat ini">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="7"/></svg>
            <span id="gps-label">Lokasi Saya</span>
        </button>

        <?php if (! empty($f['q']) || ! empty($f['kecamatan']) || (($f['kategori'] ?? 'semua') !== 'semua')): ?>
            <a href="<?= site_url('peta') ?>" class="btn btn--sm btn--ghost">Reset</a>
        <?php endif; ?>
    </form>

    <div id="gps-status" style="display:none;margin-bottom:1rem" class="gps-banner">
        <div>
            <strong id="gps-status-title">📍 Lokasi Anda Terdeteksi</strong>
            <p id="gps-status-desc" style="margin:.2rem 0 0;font-size:.85rem;color:#0369a1"></p>
        </div>
        <button type="button" onclick="resetUserGps()" style="background:none;border:none;cursor:pointer;color:#0284c7;font-weight:700;font-size:.85rem">Matikan GPS</button>
    </div>

    <?php if ($totalGeo === 0 && $totalKulinerGeo === 0): ?>
        <div class="empty"><h3>Koordinat titik belum diisi</h3>
            <p>Jalankan perintah ini di terminal proyek (butuh internet, sekitar 5–10 menit):</p>
            <p><code>php spark hotel:geocode</code></p>
            <p class="muted">Koordinat yang meleset bisa dikoreksi manual lewat Admin → Kelola Hotel.</p>
        </div>
    <?php elseif (empty($titik)): ?>
        <div class="empty">
            <h3>Lokasi tidak ditemukan pada pencarian ini</h3>
            <p class="muted">Coba gunakan kata kunci lain atau pilih kecamatan yang berbeda.</p>
            <a href="<?= site_url('peta') ?>" class="btn btn--sm">Tampilkan semua di peta</a>
        </div>
    <?php else: ?>
        <div id="map" class="map"></div>

        <!-- Panel Daftar Tempat Terdekat dari GPS User -->
        <div id="nearby-panel" class="nearby-drawer" style="display:none">
            <div class="nearby-drawer__head">
                <h3><span>🧭</span> Rekomendasi Terdekat dari Lokasi Anda</h3>
                <div class="map-tabs">
                    <button type="button" class="map-tab is-active" id="tab-near-all" onclick="filterNearby('all')">Semua</button>
                    <button type="button" class="map-tab" id="tab-near-hotel" onclick="filterNearby('hotel')">🏨 Penginapan</button>
                    <button type="button" class="map-tab" id="tab-near-kuliner" onclick="filterNearby('kuliner')">🍽️ Kuliner</button>
                </div>
            </div>
            <div id="nearby-list" class="nearby-grid"></div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (! empty($titik)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var rawData = <?= json_encode($titik, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
  var map = L.map('map').setView([3.5952, 98.6722], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  // SVG Custom Icons
  var hotelIcon = L.divIcon({
    className: 'custom-pin-hotel',
    html: '<div style="background:#0f766e;color:#fff;width:30px;height:30px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.35);border:2px solid #ffffff"><span style="transform:rotate(45deg);font-size:14px;line-height:1">🏨</span></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
  });

  var kulinerIcon = L.divIcon({
    className: 'custom-pin-kuliner',
    html: '<div style="background:#ea580c;color:#fff;width:30px;height:30px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.35);border:2px solid #ffffff"><span style="transform:rotate(45deg);font-size:14px;line-height:1">🍽️</span></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
  });

  var userMarker = null;
  var userCircle = null;
  var markersList = [];
  var userLocation = null;

  function renderPopup(item, distKm) {
    var box = document.createElement('div');
    box.style.minWidth = '200px';

    var badgeTipe = document.createElement('span');
    badgeTipe.style.display = 'inline-block';
    badgeTipe.style.fontSize = '.72rem';
    badgeTipe.style.fontWeight = '700';
    badgeTipe.style.padding = '1px 7px';
    badgeTipe.style.borderRadius = '99px';
    badgeTipe.style.marginBottom = '6px';
    if (item.tipe === 'hotel') {
      badgeTipe.style.background = '#dff5ee';
      badgeTipe.style.color = '#0a4f4a';
      badgeTipe.textContent = '🏨 Penginapan';
    } else {
      badgeTipe.style.background = '#ffedd5';
      badgeTipe.style.color = '#9a3412';
      badgeTipe.textContent = '🍽️ Kuliner';
    }
    box.appendChild(badgeTipe);

    var a = document.createElement('a');
    a.href = item.url;
    a.textContent = item.nama;
    a.style.fontWeight = '700';
    a.style.fontSize = '1.02rem';
    a.style.display = 'block';
    a.style.marginBottom = '3px';
    a.style.color = '#0f172a';
    a.style.textDecoration = 'none';
    box.appendChild(a);

    var sub = document.createElement('div');
    sub.style.fontSize = '.84rem';
    sub.style.color = '#64748b';
    sub.style.marginBottom = '5px';
    var parts = [];
    if (item.kecamatan) parts.push(item.kecamatan);
    if (item.rating) parts.push('★ ' + item.rating.toFixed(1));
    if (item.harga) parts.push(item.harga);
    sub.textContent = parts.join(' · ');
    box.appendChild(sub);

    if (item.alamat) {
      var addr = document.createElement('div');
      addr.style.fontSize = '.78rem';
      addr.style.color = '#475569';
      addr.style.marginBottom = '8px';
      addr.textContent = item.alamat;
      box.appendChild(addr);
    }

    // Tampilkan info jarak jika GPS aktif
    if (distKm != null) {
      var distBox = document.createElement('div');
      distBox.style.background = '#f0f9ff';
      distBox.style.border = '1px solid #bae6fd';
      distBox.style.borderRadius = '8px';
      distBox.style.padding = '4px 8px';
      distBox.style.fontSize = '.8rem';
      distBox.style.fontWeight = '700';
      distBox.style.color = '#0284c7';
      distBox.style.marginBottom = '8px';
      distBox.textContent = '📍 ' + window.SinggahGPS.formatDistance(distKm) + ' dari posisi Anda';
      box.appendChild(distBox);
    }

    var actions = document.createElement('div');
    actions.style.display = 'flex';
    actions.style.gap = '.8rem';
    actions.style.alignItems = 'center';
    actions.style.marginTop = '4px';

    var btnDetail = document.createElement('a');
    btnDetail.href = item.url;
    btnDetail.textContent = 'Lihat Detail →';
    btnDetail.style.fontSize = '.82rem';
    btnDetail.style.fontWeight = '700';
    btnDetail.style.color = item.tipe === 'hotel' ? '#0f766e' : '#ea580c';
    actions.appendChild(btnDetail);

    if (userLocation) {
      var btnRute = document.createElement('a');
      btnRute.href = 'https://www.google.com/maps/dir/?api=1&origin=' + userLocation.lat + ',' + userLocation.lng + '&destination=' + item.lat + ',' + item.lng;
      btnRute.target = '_blank';
      btnRute.rel = 'noopener';
      btnRute.textContent = '🧭 Rute Maps';
      btnRute.style.fontSize = '.82rem';
      btnRute.style.color = '#0284c7';
      btnRute.style.fontWeight = '600';
      actions.appendChild(btnRute);
    }

    box.appendChild(actions);
    return box;
  }

  // Tambahkan titik ke Leaflet
  var pts = [];
  rawData.forEach(function (item) {
    var icon = item.tipe === 'hotel' ? hotelIcon : kulinerIcon;
    var marker = L.marker([item.lat, item.lng], { icon: icon }).addTo(map);
    marker.bindPopup(renderPopup(item, null));
    pts.push([item.lat, item.lng]);
    markersList.push({ item: item, marker: marker });
  });

  if (pts.length) {
    map.fitBounds(pts, { padding: [35, 35], maxZoom: 15 });
  }

  // Fungsi saat GPS user berhasil didapatkan
  function onGpsSuccess(pos) {
    userLocation = pos;
    window.SinggahGPS.saveLocation(pos.lat, pos.lng);

    var gpsStatus = document.getElementById('gps-status');
    var gpsDesc   = document.getElementById('gps-status-desc');
    var gpsBtn    = document.getElementById('btn-gps');
    var gpsLabel  = document.getElementById('gps-label');

    if (gpsBtn) {
      gpsBtn.classList.add('is-active');
      gpsLabel.textContent = 'GPS Aktif';
    }
    if (gpsStatus && gpsDesc) {
      gpsStatus.style.display = 'flex';
      gpsDesc.textContent = 'Koordinat: ' + pos.lat.toFixed(5) + ', ' + pos.lng.toFixed(5) + ' (Akurasi ±' + Math.round(pos.accuracy || 15) + 'm). Tempat terdekat telah dihitung.';
    }

    // Buat User Marker
    var userIcon = L.divIcon({
      className: 'user-marker-icon',
      html: '<div class="user-marker-pulse"></div><div class="user-marker-dot"></div>',
      iconSize: [24, 24],
      iconAnchor: [0, 0]
    });

    if (userMarker) map.removeLayer(userMarker);
    if (userCircle) map.removeLayer(userCircle);

    userMarker = L.marker([pos.lat, pos.lng], { icon: userIcon, zIndexOffset: 1000 }).addTo(map);
    userMarker.bindPopup('<div style="text-align:center"><strong>📍 Posisi Anda Saat Ini</strong><br><small style="color:#64748b">Akurasi GPS ±' + Math.round(pos.accuracy || 15) + ' m</small></div>');

    if (pos.accuracy && pos.accuracy < 2000) {
      userCircle = L.circle([pos.lat, pos.lng], {
        radius: pos.accuracy,
        color: '#0284c7',
        fillColor: '#38bdf8',
        fillOpacity: 0.12,
        weight: 1
      }).addTo(map);
    }

    // Arahkan kamera peta ke lokasi pengguna
    map.setView([pos.lat, pos.lng], 14, { animate: true });

    // Hitung jarak ke setiap item dan update popup
    markersList.forEach(function (m) {
      var d = window.SinggahGPS.distance(pos.lat, pos.lng, m.item.lat, m.item.lng);
      m.distance = d;
      m.marker.setPopupContent(renderPopup(m.item, d));
    });

    // Render panel rekomendasi tempat terdekat
    renderNearbyPanel();
  }

  function renderNearbyPanel(activeFilter) {
    var panel = document.getElementById('nearby-panel');
    var list = document.getElementById('nearby-list');
    if (!panel || !list || !userLocation) return;

    var filter = activeFilter || 'all';
    var sorted = markersList.slice().sort(function (a, b) {
      return (a.distance || 9999) - (b.distance || 9999);
    });

    if (filter === 'hotel') {
      sorted = sorted.filter(function (m) { return m.item.tipe === 'hotel'; });
    } else if (filter === 'kuliner') {
      sorted = sorted.filter(function (m) { return m.item.tipe === 'kuliner'; });
    }

    var top = sorted.slice(0, 6);
    list.innerHTML = '';

    top.forEach(function (m) {
      var card = document.createElement('div');
      card.className = 'nearby-card';
      card.onclick = function () {
        map.flyTo([m.item.lat, m.item.lng], 16);
        m.marker.openPopup();
      };

      var distLabel = document.createElement('span');
      distLabel.className = 'dist';
      distLabel.textContent = '📍 ' + window.SinggahGPS.formatDistance(m.distance);
      card.appendChild(distLabel);

      var tag = document.createElement('span');
      tag.style.fontSize = '.72rem';
      tag.style.fontWeight = '700';
      tag.style.color = m.item.tipe === 'hotel' ? '#0f766e' : '#ea580c';
      tag.textContent = m.item.tipe === 'hotel' ? '🏨 PENGINAPAN' : '🍽️ KULINER';
      card.appendChild(tag);

      var h4 = document.createElement('h4');
      h4.textContent = m.item.nama;
      card.appendChild(h4);

      var p = document.createElement('p');
      p.textContent = (m.item.kecamatan || '') + (m.item.rating ? ' · ★ ' + m.item.rating.toFixed(1) : '');
      card.appendChild(p);

      list.appendChild(card);
    });

    panel.style.display = 'block';
  }

  window.filterNearby = function (type) {
    document.querySelectorAll('.map-tab').forEach(function (btn) { btn.classList.remove('is-active'); });
    var curBtn = document.getElementById('tab-near-' + type);
    if (curBtn) curBtn.classList.add('is-active');
    renderNearbyPanel(type);
  };

  window.resetUserGps = function () {
    window.SinggahGPS.clearLocation();
    userLocation = null;
    if (userMarker) map.removeLayer(userMarker);
    if (userCircle) map.removeLayer(userCircle);
    userMarker = null;
    userCircle = null;

    var gpsStatus = document.getElementById('gps-status');
    var gpsBtn    = document.getElementById('btn-gps');
    var gpsLabel  = document.getElementById('gps-label');
    var panel     = document.getElementById('nearby-panel');

    if (gpsStatus) gpsStatus.style.display = 'none';
    if (panel) panel.style.display = 'none';
    if (gpsBtn) {
      gpsBtn.classList.remove('is-active');
      gpsLabel.textContent = 'Lokasi Saya';
    }

    markersList.forEach(function (m) {
      m.distance = null;
      m.marker.setPopupContent(renderPopup(m.item, null));
    });

    if (pts.length) {
      map.fitBounds(pts, { padding: [35, 35], maxZoom: 15 });
    }
  };

  // Event handler tombol GPS
  var btnGps = document.getElementById('btn-gps');
  if (btnGps) {
    btnGps.addEventListener('click', function () {
      var gpsLabel = document.getElementById('gps-label');
      gpsLabel.textContent = 'Mendeteksi...';

      window.SinggahGPS.getLocation(function (pos) {
        onGpsSuccess(pos);
      }, function (err) {
        gpsLabel.textContent = 'Lokasi Saya';
        alert('Gagal mendeteksi lokasi GPS: ' + err.message);
      });
    });
  }

  // Jika sebelumnya lokasi sudah pernah diizinkan, auto-restore
  var cached = window.SinggahGPS.getCachedLocation();
  if (cached) {
    onGpsSuccess(cached);
  }
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
