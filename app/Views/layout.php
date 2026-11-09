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
        <button class="nav__toggle" aria-label="Menu" aria-expanded="false">☰</button>
        <nav class="nav__links" id="menu">
            <a href="<?= site_url('/') ?>">Beranda</a>
            <a href="<?= site_url('penginapan') ?>">Penginapan</a>
            <a href="<?= site_url('peta') ?>">Peta</a>
            <a href="<?= site_url('kuliner') ?>">Kuliner</a>
            <a href="<?= site_url('bandingkan') ?>">Bandingkan<?php if ($nb = count(bandingkan_ids())): ?> <b class="badge"><?= $nb ?></b><?php endif; ?></a>
            <?php $u = session('user'); ?>
            <?php if ($u): ?>
                <?php if (($u['role'] ?? '') === 'admin'): ?><a href="<?= site_url('admin') ?>">Admin</a><?php endif; ?>
                <a href="<?= site_url('akun') ?>">Profil</a>
                <a href="<?= site_url('keluar') ?>">Keluar</a>
            <?php else: ?>
                <a class="nav__cta" href="<?= site_url('masuk') ?>">Masuk</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php $ok = session()->getFlashdata('ok'); $er = session()->getFlashdata('error'); ?>
<?php if ($ok || $er): ?>
    <div class="wrap"><div class="flash <?= $er ? 'flash--err' : 'flash--ok' ?>" role="status"><?= esc($er ?: $ok) ?></div></div>
<?php endif; ?>
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
