<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1><?= esc($title) ?></h1>
<?php if ($errors): ?><div class="flash flash--err"><?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<form class="form form--grid panel" method="post" action="<?= site_url('admin/hotel/simpan') ?>" enctype="multipart/form-data">
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

    <!-- Foto Sampul / Background Hotel -->
    <div class="full" style="border-top:1px solid var(--line);padding-top:1rem">
        <label>Foto Sampul / Background Hotel
            <span class="muted small">Foto utama hotel yang tampil di kartu hotel dan background header detail hotel (JPG, PNG, WEBP).</span>
            <?php if (! empty($h['foto'])): ?>
                <div style="display:flex;align-items:center;gap:1rem;margin:0.5rem 0">
                    <img src="<?= base_url(esc($h['foto'])) ?>" alt="Cover" style="height:90px;width:140px;object-fit:cover;border-radius:8px;border:1px solid #cbd5e1">
                    <label style="font-size:0.85rem;color:#dc2626;cursor:pointer;flex-direction:row;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hapus_foto" value="1"> Hapus foto sampul saat ini
                    </label>
                </div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*">
        </label>
    </div>

    <!-- Foto Fasilitas Hotel dari Pihak Hotel / Admin -->
    <div class="full" style="border-top:1px solid var(--line);padding-top:1rem">
        <label>Foto Fasilitas Hotel (Resmi dari Pihak Hotel / Admin)
            <span class="muted small">Unggah dokumentasi resmi fasilitas hotel (kamar, sarapan, kolam renang, gym, lobby). Bisa pilih beberapa foto sekaligus.</span>
            <?php 
            $facs = ! empty($h['foto_fasilitas']) ? (json_decode($h['foto_fasilitas'], true) ?: []) : [];
            if ($facs): ?>
                <div style="display:flex;flex-wrap:wrap;gap:0.8rem;margin:0.6rem 0">
                    <?php foreach ($facs as $fItem): ?>
                        <div style="border:1px solid #cbd5e1;border-radius:8px;padding:0.4rem;background:#fff;display:flex;flex-direction:column;align-items:center;gap:0.3rem">
                            <img src="<?= base_url(esc($fItem['url'])) ?>" alt="Fasilitas" style="width:90px;height:70px;object-fit:cover;border-radius:6px">
                            <label style="font-size:0.75rem;color:#dc2626;cursor:pointer;flex-direction:row;align-items:center;gap:0.2rem">
                                <input type="checkbox" name="hapus_foto_fasilitas[]" value="<?= esc($fItem['url'], 'attr') ?>"> Hapus
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <input type="file" name="foto_fasilitas[]" multiple accept="image/*">
        </label>
    </div>

    <div class="full"><button class="btn" type="submit">Simpan</button> <a class="link" href="<?= site_url('admin/hotel') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
