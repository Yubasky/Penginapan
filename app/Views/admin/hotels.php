<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="adm__top"><h1>Kelola hotel <small>(<?= $total ?>)</small></h1>
    <div>
        <a class="btn btn--outline" href="<?= site_url('admin/laporan/hotel') ?>" target="_blank" style="margin-right:.4rem">🖨️ Cetak Laporan</a>
        <a class="btn btn--ghost" href="<?= site_url('admin/hotel/impor') ?>">Impor CSV</a> 
        <a class="btn" href="<?= site_url('admin/hotel/baru') ?>">+ Tambah hotel</a>
    </div></div>
<form class="mapbar" method="get"><input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Cari nama atau alamat…"><button class="btn" type="submit">Cari</button></form>
<div class="tblwrap"><table class="tbl">
    <tr><th>#</th><th>Nama</th><th>Kecamatan</th><th>Rating</th><th>Koordinat</th><th></th></tr>
    <?php foreach ($hotels as $h): ?>
        <tr>
            <td><?= (int) $h['peringkat'] ?></td>
            <td><a class="link" href="<?= site_url('penginapan/' . $h['slug']) ?>" target="_blank"><?= esc($h['nama']) ?></a><br><span class="small"><?= esc($h['alamat']) ?></span></td>
            <td><?= esc($h['kecamatan'] ?? '-') ?></td>
            <td><?= $h['rating'] !== null ? number_format((float) $h['rating'], 1) : '-' ?></td>
            <td><?= $h['latitude'] !== null ? '✓' : '<span class="muted">–</span>' ?></td>
            <td class="nowrap">
                <a class="link" href="<?= site_url('admin/hotel/' . $h['id'] . '/ubah') ?>">Ubah</a>
                <form class="inline" method="post" action="<?= site_url('admin/hotel/' . $h['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus hotel ini beserta ulasan & pesannya?')"><?= token_field() ?><button class="linkbtn danger" type="submit">Hapus</button></form>
            </td>
        </tr>
    <?php endforeach; ?>
</table></div>
<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
