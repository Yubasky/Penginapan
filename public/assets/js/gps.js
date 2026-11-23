/**
 * Singgah Medan - Modul GPS & Perhitungan Jarak Real-Time
 */
window.SinggahGPS = (function () {
  'use strict';

  var KEY_LAT = 'singgah_user_lat';
  var KEY_LNG = 'singgah_user_lng';
  var KEY_TIME = 'singgah_user_ts';
  var MAX_AGE_MS = 2 * 60 * 60 * 1000; // 2 jam

  /** Rumus Haversine: jarak 2 titik koordinat bumi dalam kilometer */
  function distance(lat1, lon1, lat2, lon2) {
    if (lat1 == null || lon1 == null || lat2 == null || lon2 == null) return null;
    var R = 6371; // km
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  /** Format jarak ramah pengguna: "450 m" atau "2.4 km" */
  function formatDistance(km) {
    if (km == null || isNaN(km)) return '';
    if (km < 1.0) {
      return Math.round(km * 1000) + ' m';
    }
    return km.toFixed(1).replace('.', ',') + ' km';
  }

  /** Simpan lokasi ke localStorage */
  function saveLocation(lat, lng) {
    try {
      localStorage.setItem(KEY_LAT, lat);
      localStorage.setItem(KEY_LNG, lng);
      localStorage.setItem(KEY_TIME, Date.now());
    } catch (e) {}
  }

  /** Ambil lokasi yang tersimpan di browser */
  function getCachedLocation() {
    try {
      var lat = localStorage.getItem(KEY_LAT);
      var lng = localStorage.getItem(KEY_LNG);
      var ts  = localStorage.getItem(KEY_TIME);
      if (lat && lng && ts && (Date.now() - parseInt(ts, 10) < MAX_AGE_MS)) {
        return { lat: parseFloat(lat), lng: parseFloat(lng) };
      }
    } catch (e) {}
    return null;
  }

  /** Hapus lokasi tersimpan */
  function clearLocation() {
    try {
      localStorage.removeItem(KEY_LAT);
      localStorage.removeItem(KEY_LNG);
      localStorage.removeItem(KEY_TIME);
    } catch (e) {}
  }

  /** Minta lokasi GPS perangkat pengguna via Geolocation API */
  function getLocation(onSuccess, onError, options) {
    if (!navigator.geolocation) {
      if (onError) onError(new Error('Browser Anda tidak mendukung fitur Geolocation / GPS.'));
      return;
    }
    var opts = Object.assign({
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 60000
    }, options || {});

    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude;
      var lng = pos.coords.longitude;
      saveLocation(lat, lng);
      if (onSuccess) onSuccess({ lat: lat, lng: lng, accuracy: pos.coords.accuracy });
    }, function (err) {
      var msg = 'Gagal mendeteksi lokasi GPS.';
      if (err.code === 1) msg = 'Izin akses lokasi ditolak oleh pengguna. Silakan izinkan lokasi di pengaturan browser.';
      else if (err.code === 2) msg = 'Sinyal lokasi tidak tersedia. Pastikan GPS aktif.';
      else if (err.code === 3) msg = 'Waktu permintaan lokasi habis (timeout).';
      if (onError) onError(new Error(msg));
    }, opts);
  }

  /** Update tampilan jarak pada kartu hotel & kuliner di halaman */
  function updateCardDistances(userPos) {
    var pos = userPos || getCachedLocation();
    if (!pos) return;

    // 1. Kartu Hotel
    document.querySelectorAll('.card[data-lat][data-lng]').forEach(function (el) {
      var hLat = parseFloat(el.getAttribute('data-lat'));
      var hLng = parseFloat(el.getAttribute('data-lng'));
      if (isNaN(hLat) || isNaN(hLng)) return;

      var d = distance(pos.lat, pos.lng, hLat, hLng);
      var txt = formatDistance(d);

      var chipCover = el.querySelector('.chip--gps');
      if (chipCover) {
        chipCover.textContent = '📍 ' + txt;
        chipCover.style.display = 'inline-block';
      }
      var lbl = el.querySelector('.card__jarak-label');
      if (lbl) {
        var valSpan = lbl.querySelector('.val') || lbl;
        valSpan.textContent = txt;
        lbl.style.display = 'inline-flex';
      }
    });

    // 2. Kartu Kuliner
    document.querySelectorAll('.food__item[data-lat][data-lng]').forEach(function (el) {
      var kLat = parseFloat(el.getAttribute('data-lat'));
      var kLng = parseFloat(el.getAttribute('data-lng'));
      if (isNaN(kLat) || isNaN(kLng)) return;

      var d = distance(pos.lat, pos.lng, kLat, kLng);
      var txt = formatDistance(d);

      var chip = el.querySelector('.chip-jarak');
      if (chip) {
        var valSpan = chip.querySelector('.val') || chip;
        valSpan.textContent = txt;
        chip.style.display = 'inline-flex';
      }
    });
  }

  // Auto update jika sudah ada lokasi tersimpan di browser
  document.addEventListener('DOMContentLoaded', function () {
    var pos = getCachedLocation();
    if (pos) {
      updateCardDistances(pos);
    }
  });

  return {
    distance: distance,
    formatDistance: formatDistance,
    saveLocation: saveLocation,
    getCachedLocation: getCachedLocation,
    clearLocation: clearLocation,
    getLocation: getLocation,
    updateCardDistances: updateCardDistances
  };
})();
