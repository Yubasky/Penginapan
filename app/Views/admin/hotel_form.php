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

    <!-- Link Foto Sampul / Background Hotel -->
    <div class="full" style="border-top:1px solid var(--line);padding-top:1rem">
        <label>Link Foto Sampul / Background Hotel (URL)
            <span class="muted small">Masukkan link URL gambar online (misal dari Unsplash, hosting gambar, atau website hotel). Tampil di kartu hotel dan background header.</span>
            <input type="url" name="foto" placeholder="https://images.unsplash.com/... atau https://..." value="<?= esc(old('foto', $h['foto'] ?? '')) ?>" maxlength="1000">
            <?php if (! empty($h['foto'])): ?>
                <div style="display:flex;align-items:center;gap:1rem;margin-top:0.5rem">
                    <img src="<?= foto_url($h['foto']) ?>" alt="Cover" style="height:80px;width:130px;object-fit:cover;border-radius:8px;border:1px solid #cbd5e1" onerror="this.style.display='none'">
                    <label style="font-size:0.85rem;color:#dc2626;cursor:pointer;flex-direction:row;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hapus_foto" value="1"> Hapus foto sampul saat ini
                    </label>
                </div>
            <?php endif; ?>
        </label>
    </div>

    <!-- Link Foto Fasilitas Hotel -->
    <div class="full" style="border-top:1px solid var(--line);padding-top:1rem">
        <?php 
        $facs = ! empty($h['foto_fasilitas']) ? (json_decode($h['foto_fasilitas'], true) ?: []) : [];
        $facText = '';
        if ($facs) {
            $urls = array_filter(array_column($facs, 'url'));
            $facText = implode("\n", $urls);
        }
        ?>
        <label>Link Foto Fasilitas Hotel (1 baris = 1 link URL)
            <span class="muted small">Masukkan link foto dokumentasi fasilitas resmi (kamar, sarapan, kolam renang, toilet, dsb.). Tulis 1 link per baris.</span>
            <textarea name="foto_fasilitas" rows="4" placeholder="https://images.unsplash.com/...&#10;https://example.com/kolam-renang.jpg"><?= esc(old('foto_fasilitas', $facText)) ?></textarea>
            <?php if ($facs): ?>
                <div style="display:flex;flex-wrap:wrap;gap:0.6rem;margin-top:0.6rem">
                    <?php foreach ($facs as $fItem): ?>
                        <?php if (! empty($fItem['url'])): ?>
                            <img src="<?= foto_url($fItem['url']) ?>" alt="Fasilitas" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1" onerror="this.style.display='none'">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </label>
    </div>

    <div class="full"><button class="btn" type="submit">Simpan</button> <a class="link" href="<?= site_url('admin/hotel') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
