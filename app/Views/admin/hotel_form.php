<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1><?= esc($title) ?></h1>
<?php if ($errors): ?><div class="flash flash--err"><?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<form class="form form--grid panel" method="post" action="<?= site_url('admin/hotel/simpan') ?>">
    <?= token_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($h['id'] ?? 0) ?>">
    <label class="full">Nama hotel<input type="text" name="nama" required maxlength="255" value="<?= esc($h['nama'] ?? '', 'attr') ?>"></label>
    <label class="full">Alamat<input type="text" name="alamat" required maxlength="500" value="<?= esc($h['alamat'] ?? '', 'attr') ?>"></label>
    <label>Kecamatan<input type="text" name="kecamatan" list="kec" maxlength="80" value="<?= esc($h['kecamatan'] ?? '', 'attr') ?>">
        <datalist id="kec"><?php foreach ($kecamatan as $k): ?><option value="<?= esc($k, 'attr') ?>"><?php endforeach; ?></datalist></label>
    <label>Sarapan<select name="sarapan"><?php foreach (['Tidak', 'Ya', 'All-in'] as $o): ?><option <?= ($h['sarapan'] ?? 'Tidak') === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></label>
    <label>Rating (0–5)<input type="text" name="rating" inputmode="decimal" value="<?= esc($h['rating'] ?? '', 'attr') ?>"></label>
    <label>Jumlah ulasan<input type="text" name="jumlah_ulasan" inputmode="numeric" value="<?= esc($h['jumlah_ulasan'] ?? '', 'attr') ?>"></label>
    <label>Latitude<input type="text" name="latitude" inputmode="decimal" placeholder="3.595196" value="<?= esc($h['latitude'] ?? '', 'attr') ?>"></label>
    <label>Longitude<input type="text" name="longitude" inputmode="decimal" placeholder="98.672226" value="<?= esc($h['longitude'] ?? '', 'attr') ?>"></label>
    <label class="full">Link Tripadvisor<input type="text" name="link_tripadvisor" maxlength="1000" value="<?= esc($h['link_tripadvisor'] ?? '', 'attr') ?>"></label>
    <div class="full"><button class="btn" type="submit">Simpan</button> <a class="link" href="<?= site_url('admin/hotel') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
