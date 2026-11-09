<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Impor hotel dari CSV</h1>
<div class="panel">
    <p>Format kolom sama dengan <code>data/hotels.csv</code>. Wajib: <code>nama</code>, <code>alamat</code>. Opsional: <code>slug, peringkat, kecamatan, rating, jumlah_ulasan, sarapan (Tidak/Ya/All-in), link_tripadvisor, latitude, longitude, ...</code>.
    Hotel dengan <code>slug</code> (atau nama, bila slug kosong) yang sudah ada akan <strong>diperbarui</strong>, selain itu ditambahkan.</p>
    <form class="form" method="post" enctype="multipart/form-data" action="<?= site_url('admin/hotel/impor') ?>">
        <?= token_field() ?>
        <label>File CSV (UTF-8)<input type="file" name="csv" accept=".csv,text/csv" required></label>
        <button class="btn" type="submit">Impor</button>
    </form>
</div>
<?= $this->endSection() ?>
