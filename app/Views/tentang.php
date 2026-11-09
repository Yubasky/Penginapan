<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Integrasi data</p>
    <h1>Tentang &amp; API</h1>
    <p>Data penginapan dibuka lewat API JSON agar bisa dipakai kelompok lain, dan sebaliknya.</p>
</div></section>

<div class="wrap prose">
    <h2>Endpoint publik (tanpa kunci)</h2>
    <table class="tbl">
        <tr><th>Endpoint</th><th>Keterangan</th></tr>
        <tr><td><code>GET /api/hotels</code></td><td>Daftar hotel. Parameter: <code>q</code>, <code>kecamatan</code>, <code>sarapan=ya</code>, <code>rating_min</code>, <code>sort=rating|ulasan|nama</code>, <code>page</code>, <code>limit</code> (maks 100)</td></tr>
        <tr><td><code>GET /api/hotels/{id}</code></td><td>Detail satu hotel</td></tr>
        <tr><td><code>GET /api/kecamatan</code></td><td>Daftar kecamatan + jumlah hotel (kunci penghubung dengan data kuliner)</td></tr>
        <tr><td><code>GET /api/kuliner</code></td><td>Salinan data kuliner yang tersimpan di server kami (<code>q</code>, <code>kecamatan</code>, <code>page</code>, <code>limit</code>)</td></tr>
    </table>
    <p>Contoh: <a href="<?= site_url('api/hotels?kecamatan=Medan%20Petisah&limit=3') ?>"><code>/api/hotels?kecamatan=Medan Petisah&amp;limit=3</code></a></p>

    <h2>Endpoint dengan kunci API</h2>
    <p>Minta kunci ke admin kami. Kirim di header <code>X-API-KEY: sm_...</code> (atau <code>Authorization: Bearer sm_...</code>).</p>
    <table class="tbl">
        <tr><th>Endpoint</th><th>Keterangan</th></tr>
        <tr><td><code>POST /api/kuliner/push</code></td><td>Dorong data kuliner ke server kami. Body JSON <code>{"data":[...]}</code>, maks 500 per permintaan. Data dengan <code>id</code> yang sama diperbarui.</td></tr>
        <tr><td><code>GET /api/export/hotels?format=json|csv</code></td><td>Ekspor lengkap semua hotel (termasuk koordinat)</td></tr>
        <tr><td><code>GET /api/export/ulasan</code></td><td>Ulasan pengunjung situs kami</td></tr>
    </table>
<pre><code>curl -X POST http://SERVER-KAMI:8080/api/kuliner/push \
  -H "X-API-KEY: sm_xxxxxxxx" -H "Content-Type: application/json" \
  -d '{"data":[{"id":"k1","nama":"Soto Kesawan","kategori":"Soto","alamat":"Jl. Contoh 1",
       "kecamatan":"Medan Barat","rating":4.6,"harga":"Rp 20-40 rb"}]}'</code></pre>

    <h2>Yang kami ambil dari kelompok kuliner</h2>
    <p>Kunci penghubung adalah nama <strong>kecamatan</strong> (misal “Medan Petisah”). Kami memanggil:</p>
    <pre><code>GET {kuliner.apiUrl}/api/kuliner?page=1&amp;limit=100        (sinkronisasi penuh)
GET {kuliner.apiUrl}/api/kuliner?kecamatan=Medan Petisah   (cadangan bila data lokal kosong)

{
  "status": "ok",
  "meta": { "total_pages": 3 },
  "data": [
    { "id": "k1", "nama": "...", "kategori": "Soto", "alamat": "...", "kecamatan": "Medan Petisah",
      "rating": 4.6, "harga": "Rp 20-50 rb", "foto": "https://...", "url": "https://..." }
  ]
}</code></pre>
    <p>Bila kelompok kuliner memakai kunci API, isi <code>kuliner.apiKey</code> di <code>.env</code>; kami kirim sebagai header <code>X-API-KEY</code>.</p>
</div>
<?= $this->endSection() ?>
