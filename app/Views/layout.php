<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Singgah Medan') ?></title>
    <meta name="description" content="Cari dan bandingkan 240 penginapan di Medan: alamat, rating, ulasan, dan info sarapan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('head') ?>
</head>
<body>
<header class="nav">
    <div class="wrap nav__in">
        <a class="brand" href="<?= site_url('/') ?>"><span class="brand__mark">S</span>Singgah <em>Medan</em></a>
        
        <div style="display:flex;align-items:center;gap:1.2rem">
            <nav class="nav__links" id="menu">
                <a href="<?= site_url('/') ?>">Beranda</a>
                <a href="<?= site_url('penginapan') ?>">Penginapan</a>
                <a href="<?= site_url('peta') ?>">Peta</a>
                <a href="<?= site_url('kuliner') ?>">Kuliner</a>
                <?php $u = session('user'); ?>
                <?php if ($u): ?>
                    <?php if (($u['role'] ?? '') === 'admin'): ?><a href="<?= site_url('admin') ?>">Admin</a><?php endif; ?>
                    <a href="<?= site_url('akun') ?>">Profil</a>
                    <a href="<?= site_url('keluar') ?>" onclick="konfirmasiKeluar(event)">Keluar</a>
                <?php else: ?>
                    <a class="nav__cta" href="<?= site_url('masuk') ?>">Masuk</a>
                <?php endif; ?>
            </nav>

            <?php if (! empty($u)): ?>
                <?php $userNotif = ambil_notifikasi_user($u); ?>
                <div class="nav__notif-wrap">
                    <button type="button" class="nav__notif-btn" id="notif-toggle-btn" aria-label="Notifikasi" onclick="toggleNotifDropdown(event)" title="Pemberitahuan">
                        🔔
                        <?php if ($userNotif['unread'] > 0): ?>
                            <span class="nav__notif-badge" id="notif-badge"><?= $userNotif['unread'] ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="nav__notif-dropdown" id="notif-dropdown" style="display:none">
                        <div class="nav__notif-header">
                            <strong style="color:#0f172a">Pemberitahuan</strong>
                            <?php if ($userNotif['unread'] > 0): ?>
                                <button type="button" class="link small" style="background:none;border:none;cursor:pointer;color:var(--teal);font-size:.8rem;font-weight:600" onclick="tandaiSemuaDibaca(event)">Tandai dibaca</button>
                            <?php endif; ?>
                        </div>
                        <div class="nav__notif-list">
                            <?php if (! empty($userNotif['items'])): ?>
                                <?php foreach ($userNotif['items'] as $item): ?>
                                    <a href="<?= site_url('penginapan/' . $item['hotel_slug']) ?>#tanya" class="nav__notif-item <?= $item['is_unread'] ? 'is-unread' : '' ?>" onclick="tandaiSemuaDibaca()">
                                        <div class="nav__notif-avatar"><?= $item['role'] === 'admin' ? '🏨' : '👤' ?></div>
                                        <div class="nav__notif-text">
                                            <p>
                                                <?php if (! empty($item['is_new_q'])): ?>
                                                    <strong><?= esc($item['penjawab']) ?></strong> bertanya di <em><?= esc($item['hotel_nama']) ?></em>
                                                <?php else: ?>
                                                    <strong><?= esc($item['penjawab']) ?></strong> menjawab pertanyaanmu di <em><?= esc($item['hotel_nama']) ?></em>
                                                <?php endif; ?>
                                            </p>
                                            <div class="nav__notif-snippet">"<?= esc(mb_strimwidth($item['isi'], 0, 75, '...')) ?>"</div>
                                            <span class="muted small" style="font-size:.72rem;display:block;margin-top:.25rem"><?= tgl_id($item['waktu']) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="nav__notif-empty">Belum ada pemberitahuan baru.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <button class="nav__toggle" aria-label="Menu" aria-expanded="false">☰</button>
        </div>
    </div>
</header>

<!-- Modal Konfirmasi Keluar (Sebelum Logout) -->
<div id="confirm-logout-modal" class="login-modal" style="display:none" role="dialog" aria-modal="true">
    <div class="login-modal__card">
        <button type="button" class="login-modal__close" onclick="batalKeluar()" aria-label="Tutup">✕</button>
        <div class="login-modal__icon" style="animation:none">🚪</div>
        <h2>Konfirmasi Keluar</h2>
        <p>Apakah Anda yakin ingin keluar dari akun <strong>Singgah Medan</strong>?</p>
        <div class="login-modal__actions">
            <a href="<?= site_url('keluar') ?>" class="btn" style="background:#dc2626;border-color:#dc2626">Ya, Keluar</a>
            <button type="button" class="btn btn--ghost" onclick="batalKeluar()">Batal</button>
        </div>
    </div>
</div>

<!-- Modal Pop-up Berhasil Keluar (Setelah Logout) -->
<?php if (session('logout_popup')): ?>
<div id="logout-modal" class="login-modal" role="dialog" aria-modal="true">
    <div class="login-modal__card">
        <button type="button" class="login-modal__close" onclick="closeLogoutModal()" aria-label="Tutup">✕</button>
        <div class="login-modal__icon" style="animation:none">🔒</div>
        <h2>Berhasil Keluar</h2>
        <p>Anda telah berhasil keluar dari akun. Sampai jumpa kembali di <strong>Singgah Medan</strong>!</p>
        <div class="login-modal__actions">
            <button type="button" class="btn" onclick="closeLogoutModal()">OK, Mengerti</button>
            <a href="<?= site_url('masuk') ?>" class="btn btn--ghost">Masuk Kembali</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function konfirmasiKeluar(e) {
    if (e) e.preventDefault();
    var m = document.getElementById('confirm-logout-modal');
    if (m) m.style.display = 'flex';
}
function batalKeluar() {
    var m = document.getElementById('confirm-logout-modal');
    if (m) m.style.display = 'none';
}
function closeLogoutModal() {
    var m = document.getElementById('logout-modal');
    if (m) {
        m.classList.add('is-closing');
        setTimeout(function() { m.remove(); }, 250);
    }
}
window.addEventListener('click', function(e) {
    var c = document.getElementById('confirm-logout-modal');
    if (e.target === c) batalKeluar();
    var l = document.getElementById('logout-modal');
    if (e.target === l) closeLogoutModal();
});
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        batalKeluar();
        closeLogoutModal();
    }
});
</script>

<?php if ($loginUser = session('login_popup')): ?>
<div id="login-modal" class="login-modal" role="dialog" aria-modal="true">
    <div class="login-modal__card">
        <button type="button" class="login-modal__close" onclick="closeLoginModal()" aria-label="Tutup">✕</button>
        <div class="login-modal__icon">👋</div>
        <h2>Halo, <?= esc($loginUser) ?>!</h2>
        <p>Senang melihatmu kembali di <strong>Singgah Medan</strong>. Akunmu berhasil masuk. Yuk, temukan penginapan idaman dan jelajahi rekomendasi kuliner lezat di Kota Medan!</p>
        <div class="login-modal__actions">
            <a href="<?= site_url('penginapan') ?>" class="btn">Mulai Jelajah Penginapan</a>
            <button type="button" class="btn btn--ghost" onclick="closeLoginModal()">Tutup</button>
        </div>
    </div>
</div>
<script>
function closeLoginModal() {
    var m = document.getElementById('login-modal');
    if (m) {
        m.classList.add('is-closing');
        setTimeout(function() { m.remove(); }, 250);
    }
}
window.addEventListener('click', function(e) {
    var m = document.getElementById('login-modal');
    if (e.target === m) closeLoginModal();
});
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLoginModal();
});
</script>
<?php endif; ?>

<?php $ok = session()->getFlashdata('ok'); $er = session()->getFlashdata('error'); ?>
<?php if ($ok || $er): ?>
<div id="flash-modal" class="login-modal" role="dialog" aria-modal="true">
    <div class="login-modal__card">
        <button type="button" class="login-modal__close" onclick="closeFlashModal()" aria-label="Tutup">✕</button>
        <div class="login-modal__icon" style="animation:none"><?= $er ? '⚠️' : '✅' ?></div>
        <h2><?= $er ? 'Pemberitahuan' : 'Berhasil!' ?></h2>
        <p><?= esc($er ?: $ok) ?></p>
        <div class="login-modal__actions">
            <button type="button" class="btn <?= $er ? 'btn--ghost' : '' ?>" onclick="closeFlashModal()">OK, Mengerti</button>
        </div>
    </div>
</div>
<script>
function closeFlashModal() {
    var m = document.getElementById('flash-modal');
    if (m) {
        m.classList.add('is-closing');
        setTimeout(function() { m.remove(); }, 250);
    }
}
window.addEventListener('click', function(e) {
    var m = document.getElementById('flash-modal');
    if (e.target === m) closeFlashModal();
});
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeFlashModal();
});
</script>
    <div class="wrap"><div class="flash <?= $er ? 'flash--err' : 'flash--ok' ?>" role="status"><?= esc($er ?: $ok) ?></div></div>
<?php endif; ?>

<script>
function toggleNotifDropdown(e) {
    if (e) e.stopPropagation();
    var dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
}

function tandaiSemuaDibaca(e) {
    if (e) e.stopPropagation();
    fetch('<?= site_url('notifikasi/baca') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: '_token=<?= session('_token') ?>'
    }).then(function() {
        var badge = document.getElementById('notif-badge');
        if (badge) badge.remove();
        document.querySelectorAll('.nav__notif-item.is-unread').forEach(function(el) {
            el.classList.remove('is-unread');
        });
    }).catch(function(err) {
        console.error('Gagal menandai notifikasi:', err);
    });
}

window.addEventListener('click', function(e) {
    var dd = document.getElementById('notif-dropdown');
    var btn = document.getElementById('notif-toggle-btn');
    if (dd && dd.style.display === 'block') {
        if (!dd.contains(e.target) && (!btn || !btn.contains(e.target))) {
            dd.style.display = 'none';
        }
    }
});
</script>
<main><?= $this->renderSection('content') ?></main>

<footer class="foot">
    <div class="wrap foot__in">
        <div>
            <a class="brand brand--light" href="<?= site_url('/') ?>"><span class="brand__mark">S</span>Singgah <em>Medan</em></a>
            <p>Panduan penginapan di Kota Medan, Sumatera Utara.</p>
        </div>
        <div>
            <p class="foot__h">Jelajah</p>
            <a href="<?= site_url('penginapan') ?>">Semua penginapan</a>
            <a href="<?= site_url('penginapan?sarapan=ya') ?>">Dengan sarapan</a>
            <a href="<?= site_url('peta') ?>">Peta</a>
            <a href="<?= site_url('kuliner') ?>">Kuliner</a>
        </div>
        <div>
            <p class="foot__h">Sumber data</p>
            <p>Rating &amp; ulasan: Tripadvisor (diambil 8 Nov 2026). Harga per malam tidak tersedia.</p>
        </div>
    </div>
    <div class="wrap foot__copy">© <?= date('Y') ?> Singgah Medan · Proyek kelompok</div>
</footer>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
