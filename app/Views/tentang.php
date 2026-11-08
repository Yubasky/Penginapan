<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Integrasi data</p>
    <h1>Tentang &amp; API</h1>
    <p>Data penginapan dibuka lewat API JSON agar bisa dipakai kelompok lain, dan sebaliknya.</p>
</div></section>

<div class="wrap prose">
    <h2>Endpoint kami (read-only)</h2>
    <table class="tbl">
        <tr><th>Endpoint</th><th>Keterangan</th></tr>
        <tr><td><code>GET /api/hotels</code></td><td>Daftar hotel. Parameter: <code>q</code>, <code>kecamatan</code>, <code>sarapan=ya</code>, <code>rating_min</code>, <code>sort=rating|ulasan|nama</code>, <code>page</code>, <code>limit</code> (maks 100)</td></tr>
        <tr><td><code>GET /api/hotels/{id}</code></td><td>Detail satu hotel</td></tr>
        <tr><td><code>GET /api/kecamatan</code></td><td>Daftar kecamatan + jumlah hotel (kunci penghubung dengan data kuliner)</td></tr>
    </table>
    <p>Contoh: <a href="<?= site_url('api/hotels?kecamatan=Medan%20Petisah&limit=3') ?>"><code>/api/hotels?kecamatan=Medan Petisah&amp;limit=3</code></a></p>

    <h2>Yang kami harapkan dari kelompok kuliner</h2>
    <p>Kunci penghubung adalah nama <strong>kecamatan</strong> (misal “Medan Petisah”). Endpoint yang dipanggil halaman detail kami:</p>
    <pre><code>GET {kuliner.apiUrl}/api/kuliner?kecamatan=Medan Petisah&amp;limit=6

{
  "status": "ok",
  "data": [
    { "nama": "...", "kategori": "Soto", "alamat": "...", "kecamatan": "Medan Petisah",
      "rating": 4.6, "harga": "Rp 20-50 rb", "foto": "https://...", "url": "https://..." }
  ]
}</code></pre>
    <p>Aktifkan dengan mengisi <code>kuliner.apiUrl</code> di file <code>.env</code>. Jika server mereka mati, halaman tetap tampil normal.</p>
</div>
<?= $this->endSection() ?>
