<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> · Singgah Medan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="adm">
<?php
$menu = ['admin' => 'Ringkasan', 'admin/hotel' => 'Hotel', 'admin/pesan' => 'Pesan masuk', 'admin/ulasan' => 'Ulasan', 'admin/pengguna' => 'Pengguna', 'admin/kuliner' => 'Integrasi kuliner'];
$now  = trim(service('uri')->getPath(), '/');
?>
<aside class="adm__side">
    <a class="brand brand--light" href="<?= site_url('admin') ?>"><span class="brand__mark">S</span>Admin</a>
    <nav>
        <?php foreach ($menu as $path => $label): $on = $now === $path || ($path !== 'admin' && str_starts_with($now, $path)); ?>
            <a class="<?= $on ? 'is-on' : '' ?>" href="<?= site_url($path) ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="adm__foot"><a href="<?= site_url('/') ?>">← Lihat situs</a><a href="<?= site_url('keluar') ?>">Keluar</a></div>
</aside>
<main class="adm__main">
    <?php $ok = session()->getFlashdata('ok'); $er = session()->getFlashdata('error'); ?>
    <?php if ($ok || $er): ?><div class="flash <?= $er ? 'flash--err' : 'flash--ok' ?>"><?= esc($er ?: $ok) ?></div><?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
</body>
</html>
