# Singgah Medan — web penginapan Medan (CodeIgniter 4 + PostgreSQL)

## Pasang (file ini ditimpa ke atas proyek CI4 baru)
1. `composer create-project codeigniter4/appstarter singgah-medan`
2. Salin isi paket ini (`app/`, `public/`, `data/`, `env.contoh`) ke dalamnya, setuju menimpa `app/Config/Routes.php`.
3. `cp env.contoh .env` lalu isi password PostgreSQL.

## Database (urutan penting)
```bash
sudo -u postgres createdb singgah_medan
sudo -u postgres psql -d singgah_medan -f data/hotels.sql   # 240 hotel
sudo -u postgres psql -d singgah_medan -f data/fitur.sql    # akun, ulasan, favorit, pesan, kunci API, kuliner
```
Di pgAdmin: Query Tool -> buka file -> F5 (juga berurutan). Jangan jalankan `hotels.sql` lagi setelah web dipakai (isi tabel hotel direset).

## Akun admin pertama
```bash
php spark akun:admin
```
Isi email, password, nama. Masuk lewat `/masuk`, lalu buka `/admin`.

## Isi koordinat untuk halaman Peta (butuh internet, 5-10 menit)
```bash
php spark hotel:geocode
```
Hasil dari OpenStreetMap bisa meleset; koreksi lewat Admin -> Hotel -> Ubah.

## Bagikan data + koordinat ke kelompok lain
```bash
php spark hotel:ekspor     # menulis data/hotels_koordinat.csv
```
Atau lewat API (pakai kunci): `GET /api/export/hotels?format=csv`.

## Jalankan
```bash
php spark serve
```

## Fitur
Pengunjung: cari & filter hotel, detail + peta, favorit, ulasan bintang, bandingkan 3 hotel, peta semua hotel, kuliner, pencarian gabungan (`/cari`), form tanya/pesan hotel, halaman akun.
Admin (`/admin`): ringkasan, tambah/ubah/hapus hotel, impor CSV, pesan masuk + status, moderasi ulasan, kelola pengguna & peran, kunci API, integrasi kuliner.

## Tukar data dengan kelompok kuliner
- Kami memberi (publik): `GET /api/hotels`, `/api/hotels/{id}`, `/api/kecamatan`, `/api/kuliner`.
- Kami memberi (pakai kunci, header `X-API-KEY`): `GET /api/export/hotels?format=csv|json`, `GET /api/export/ulasan`.
- Mereka mengirim ke kami: `POST /api/kuliner/push` (kunci dibuat di Admin -> Kunci API).
- Kami menarik dari mereka: isi `kuliner.apiUrl` di `.env`, lalu Admin -> Integrasi kuliner -> Sinkron, atau `php spark kuliner:sinkron`.
- Kunci penghubung: nama kecamatan. Daftarnya di `/api/kecamatan`. Detail format JSON ada di halaman `/tentang`.

## Catatan keamanan
Form memakai token anti-CSRF buatan sendiri, password di-hash, kunci API disimpan sebagai hash. Sebelum dipublikasikan ke internet: ubah `CI_ENVIRONMENT` ke `production` dan pakai HTTPS.
