<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Integrasi data</p>
    <h1>Tentang &amp; API v1</h1>
    <p>REST API Singgah Medan (CodeIgniter 4 + PostgreSQL) mendukung autentikasi Bearer JWT &amp; API Key, versioning <code>/api/v1</code>, dan rate limiting.</p>
</div></section>

<div class="wrap prose">
    <h2>Autentikasi (JWT Bearer &amp; API Key)</h2>
    <p>Login lewat <code>POST /api/v1/auth/login</code> untuk memperoleh token <code>Bearer</code> (dipakai aplikasi client), atau gunakan <code>X-API-KEY: sm_...</code> (untuk integrasi antar kelompok). Request tanpa kredensial ditolak dengan <code>401 Unauthorized</code>, dan akses tanpa hak peran ditolak dengan <code>403 Forbidden</code>.</p>
    <pre><code>POST /api/v1/auth/login
Content-Type: application/json

{"email": "user@contoh.com", "password": "password"}</code></pre>

    <h2>Endpoint Data Penginapan &amp; Ulasan (<code>/api/v1/*</code>)</h2>
    <table class="tbl">
        <tr><th>Endpoint</th><th>Akses</th><th>Keterangan</th></tr>
        <tr><td><code>POST /api/v1/auth/login</code></td><td>Publik</td><td>Login aplikasi client untuk mendapatkan token JWT</td></tr>
        <tr><td><code>GET /api/v1/auth/me</code></td><td>Token / Key</td><td>Melihat profil aktor yang sedang login</td></tr>
        <tr><td><code>GET /api/v1/hotels</code></td><td>Token / Key</td><td>Daftar hotel (<code>q</code>, <code>kecamatan</code>, <code>sarapan=ya</code>, <code>rating_min</code>, <code>sort</code>, <code>page</code>, <code>limit</code>)</td></tr>
        <tr><td><code>GET /api/v1/hotels/{id}</code></td><td>Token / Key</td><td>Detail satu hotel</td></tr>
        <tr><td><code>POST /api/v1/hotels</code></td><td>Admin (403 bila bukan)</td><td>Tambah hotel baru (validasi 422, transaksi ACID)</td></tr>
        <tr><td><code>PUT /api/v1/hotels/{id}</code></td><td>Admin (403 bila bukan)</td><td>Ubah hotel (mendukung <code>updated_at</code> untuk deteksi konflik 409)</td></tr>
        <tr><td><code>DELETE /api/v1/hotels/{id}</code></td><td>Admin (403 bila bukan)</td><td>Hapus hotel beserta relasinya</td></tr>
        <tr><td><code>GET /api/v1/kecamatan</code></td><td>Token / Key</td><td>Daftar kecamatan + jumlah hotel</td></tr>
        <tr><td><code>GET /api/v1/reviews?hotel_id={id}</code></td><td>Token / Key</td><td>Membaca daftar ulasan hotel</td></tr>
        <tr><td><code>POST /api/v1/reviews</code></td><td>Token / Key</td><td>Kirim ulasan baru (rating hotel otomatis disinkronkan)</td></tr>
        <tr><td><code>PUT /api/v1/reviews/{id}</code></td><td>Pemilik / Admin</td><td>Ubah ulasan (cek kepemilikan 403 &amp; konflik 409)</td></tr>
        <tr><td><code>DELETE /api/v1/reviews/{id}</code></td><td>Pemilik / Admin</td><td>Hapus ulasan berdasarkan ID</td></tr>
        <tr><td><code>GET /api/v1/kuliner</code></td><td>Token / Key</td><td>Data kuliner yang tersinkron di server kami</td></tr>
        <tr><td><code>POST /api/v1/kuliner/push</code></td><td>Admin / Mitra</td><td>Dorong batch data kuliner ke server kami</td></tr>
        <tr><td><code>GET /api/v1/export/hotels?format=json|csv</code></td><td>Admin / Mitra</td><td>Ekspor lengkap semua hotel</td></tr>
        <tr><td><code>GET /api/v1/export/ulasan</code></td><td>Admin / Mitra</td><td>Ekspor ulasan pengunjung</td></tr>
    </table>
    <p><em>Kompatibilitas: prefix <code>/api/*</code> tanpa <code>v1</code> tetap aktif agar integrasi lama tetap berjalan.</em></p>

    <h2>Yang kami ambil dari kelompok kuliner</h2>
    <p>Kunci penghubung adalah koordinat GPS (<code>lat</code>, <code>lng</code>) dan nama <strong>kecamatan</strong>. Kami memanggil API kelompok kuliner (<code>/api/v1/restoran</code> dan <code>/api/v1/restoran/terdekat</code>) dengan header <code>X-API-KEY</code> sesuai nilai <code>kuliner.apiKey</code> di <code>.env</code>.</p>
</div>
<?= $this->endSection() ?>
