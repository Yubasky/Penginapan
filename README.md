# Singgah Medan — web penginapan Medan (CodeIgniter 4 + PostgreSQL)

Isi folder ini adalah **file tambahan** yang ditimpa/ditaruh di atas proyek CI4 baru.

## 1. Siapkan proyek CI4 (Ubuntu)
```bash
sudo apt install php8.3-intl php8.3-pgsql php8.3-curl php8.3-mbstring php8.3-xml composer
composer create-project codeigniter4/appstarter singgah-medan
cd singgah-medan
```
Salin seluruh isi folder ini (`app/`, `public/`, `data/`, `README.md`) ke dalam `singgah-medan/` dan setuju menimpa `app/Config/Routes.php`.
Salin `env.contoh` menjadi `.env`, lalu isi password PostgreSQL.

## 2. Masukkan data ke PostgreSQL — pilih salah satu
**A. Lewat pgAdmin**: buat database `singgah_medan` → klik kanan → *Query Tool* → buka `data/hotels.sql` → Run (F5). Tabel `hotels` + 240 baris langsung jadi.

**B. Lewat terminal (Ubuntu)**: `psql -U postgres -c "CREATE DATABASE singgah_medan"` lalu `psql -U postgres -d singgah_medan -f data/hotels.sql`

**C. Lewat CI4**: buat database kosong, lalu `php spark migrate` dan `php spark db:seed HotelSeeder` (membaca `data/hotels.csv`).

## 3. Jalankan
```bash
php spark serve          # buka http://localhost:8080
```
Di VS Code, ekstensi yang berguna: *PHP Intelephense*, *PostgreSQL* (opsional).

## Halaman
- `/` beranda, `/penginapan` daftar + filter, `/penginapan/{slug}` detail, `/tentang` dokumentasi API.

## Tukar data dengan kelompok kuliner
- **Kami memberi**: `GET /api/hotels`, `/api/hotels/{id}`, `/api/kecamatan` (JSON, CORS terbuka, read-only).
- **Kami mengambil**: `GET {kuliner.apiUrl}/api/kuliner?kecamatan=...&limit=6` → tampil di halaman detail hotel (`app/Libraries/KulinerClient.php`).
- Kunci penghubung = **nama kecamatan** (mis. "Medan Petisah"). Sepakati ejaan ini dengan kelompok kuliner; daftar resminya ada di `/api/kecamatan`.
- Beda laptop: jalankan `php spark serve --host 0.0.0.0` dan pakai IP laptop di jaringan yang sama.
- Nama field kuliner yang diterima: nama, kategori, alamat, rating, harga, foto, url (alias Inggris juga dibaca).

## Catatan data
- Sumber: Tripadvisor, 8 Nov 2026. Harga per malam tidak ada. 12 hotel alamatnya tidak memuat kecamatan (kolom `kecamatan` kosong).
