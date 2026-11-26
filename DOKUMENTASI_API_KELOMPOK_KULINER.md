# 📖 Dokumentasi Integrasi REST API — Singgah Medan (Kelompok Penginapan)

Dokumentasi ini ditujukan untuk **Kelompok Kuliner** agar dapat mengintegrasikan data penginapan, mencari hotel terdekat, serta melakukan interaksi **CRUD (Create, Read, Delete) Ulasan** langsung dari aplikasi web kuliner ke server Web API Singgah Medan.

---

## 🔑 Kredensial & Konfigurasi API (Server Online via Ngrok)

REST API kami dibangun secara mandiri menggunakan **CodeIgniter 4 (PHP)** dan telah dipublikasikan secara online melalui tunnel **Ngrok**:

* **Base URL API:** `https://snooze-study-gravy.ngrok-free.dev/api`
* **Format Response:** JSON (Standard RESTful)
* **CORS:** Diaktifkan (`Access-Control-Allow-Origin: *`), dapat dipanggil langsung dari JavaScript (Fetch/Axios/React) tanpa hambatan CORS.
* **Kunci Penghubung Antar Kelompok:**
  1. **`kecamatan`** (contoh: `"Medan Petisah"`, `"Medan Barat"`, dll.)
  2. **Koordinat GPS** (`latitude` dan `longitude`) untuk perhitungan jarak terdekat antara hotel dan tempat kuliner.

---

## 🏨 1. Mengambil Data Penginapan (READ)

Endpoint ini bersifat **publik** dan tidak memerlukan header otentikasi khusus.

### A. Ambil Daftar Semua Hotel (dengan Pagination & Filter)
* **Method:** `GET`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/hotels`
* **Query Parameter Opsional:**
  * `page` (default: 1)
  * `limit` (default: 20, maks: 100)
  * `q` (kata kunci pencarian nama hotel)
  * `kecamatan` (filter kecamatan, misal: `Medan Petisah`)
  * `sarapan` (filter: `ya`)
  * `rating_min` (filter minimal rating, misal: `4`)

### B. Filter Hotel Berdasarkan Kecamatan
* **Method:** `GET`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/hotels?kecamatan=Medan%20Petisah`

### C. Ambil Detail Hotel Tertentu Berdasarkan ID
* **Method:** `GET`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/hotels/7`

### D. Ambil Daftar Seluruh Kecamatan & Jumlah Hotel
* **Method:** `GET`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/kecamatan`

### Contoh Struktur Data Response Hotel (JSON):
```json
{
  "status": "ok",
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "total_pages": 3
  },
  "data": [
    {
      "id": 7,
      "nama": "The Reiz Suites, Artotel Curated",
      "slug": "the-reiz-suites-artotel-curated",
      "kota": "Medan",
      "kecamatan": "Medan Barat",
      "alamat": "Jl. Tembakau Deli No. 1, Medan Barat",
      "latitude": 3.592594,
      "longitude": 98.676002,
      "rating": 5.0,
      "jumlah_ulasan": 142,
      "sarapan": "ya",
      "url": "https://snooze-study-gravy.ngrok-free.dev/penginapan/the-reiz-suites-artotel-curated"
    }
  ]
}
```

---

## ⭐ 2. CRUD Ulasan Hotel dari Situs Kuliner

Pengunjung di situs web kuliner Anda dapat memberikan ulasan, membaca ulasan, atau membatalkan ulasan untuk hotel tertentu. Rating dan jumlah ulasan di server penginapan akan otomatis dihitung ulang secara realtime.

### A. CREATE: Kirim Ulasan Baru dari Web Kuliner
Saat pengunjung di situs kuliner Anda mengirimkan review ulasan untuk hotel:

* **Method:** `POST`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/reviews`
* **Headers:**
  ```http
  Content-Type: application/json
  Accept: application/json
  ```
* **Body JSON:**
  ```json
  {
    "hotel_id": 7,
    "user_id": 2,
    "rating": 5,
    "judul": "Lokasi sangat strategis dekat kuliner!",
    "isi": "Hotel bersih dan sangat dekat dengan restoran kuliner khas Medan."
  }
  ```
* **Response (HTTP 201 Created):**
  ```json
  {
    "status": "ok",
    "message": "Ulasan berhasil ditambahkan.",
    "data": {
      "id": 15,
      "hotel_id": 7,
      "user_id": 2,
      "rating": 5,
      "judul": "Lokasi sangat strategis dekat kuliner!",
      "isi": "Hotel bersih dan sangat dekat dengan restoran kuliner khas Medan.",
      "created_at": "2026-11-24 15:30:00"
    }
  }
  ```

---

### B. READ: Melihat Ulasan Suatu Hotel
Untuk menampilkan ulasan-ulasan hotel tertentu di situs kuliner Anda:

* **Method:** `GET`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/reviews?hotel_id=7`
* **Response:**
  ```json
  {
    "status": "ok",
    "total": 3,
    "data": [
      {
        "id": 15,
        "hotel_id": 7,
        "hotel": "The Reiz Suites, Artotel Curated",
        "user_id": 2,
        "penulis": "Bayu Prayoga",
        "rating": 5,
        "judul": "Lokasi sangat strategis dekat kuliner!",
        "isi": "Hotel bersih dan sangat dekat dengan restoran kuliner khas Medan.",
        "created_at": "2026-11-24 15:30:00"
      }
    ]
  }
  ```

---

### C. DELETE: Menghapus Ulasan
Bila ulasan ingin dihapus berdasarkan ID ulasan:

* **Method:** `DELETE`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/reviews/15`
* **Response (HTTP 200 OK):**
  ```json
  {
    "status": "ok",
    "message": "Ulasan berhasil dihapus."
  }
  ```

---

## 🍽️ 3. Mendorong / Sinkron Data Kuliner Anda ke Server Kami (Opsional)

Jika kelompok kuliner ingin data kulinernya tampil di web Penginapan kami, Anda dapat mengirimkan data kuliner Anda secara berkala (*push*) ke endpoint aplikasi kami:

* **Method:** `POST`
* **URL:** `https://snooze-study-gravy.ngrok-free.dev/api/kuliner/push`
* **Headers:**
  ```http
  Content-Type: application/json
  X-API-KEY: sm_xxxxxxxxxxxx (minta ke tim penginapan)
  ```
* **Body JSON:**
  ```json
  {
    "data": [
      {
        "id": "k01",
        "nama": "Soto Kesawan Enak",
        "kategori": "Soto",
        "alamat": "Jl. Ahmad Yani No. 116",
        "kecamatan": "Medan Barat",
        "rating": 4.7,
        "harga": "Rp 25.000 - Rp 40.000",
        "latitude": 3.593120,
        "longitude": 98.675430,
        "foto": "https://url-gambar.com/soto.jpg",
        "url": "https://web-kuliner.com/detail/soto-kesawan"
      }
    ]
  }
  ```

---

## 💻 Contoh Implementasi Pemanggilan Kode

### Contoh Menggunakan JavaScript (Fetch):
```javascript
const BASE_URL = 'https://snooze-study-gravy.ngrok-free.dev/api';

// 1. Mengambil data hotel (filter kecamatan)
async function getHotelsByKecamatan(kecamatan) {
  const res = await fetch(`${BASE_URL}/hotels?kecamatan=${encodeURIComponent(kecamatan)}`);
  const result = await res.json();
  console.log('Daftar Hotel:', result.data);
  return result.data;
}

// 2. Mengirim ulasan hotel dari web kuliner
async function kirimUlasan(hotelId, rating, judul, isi) {
  const res = await fetch(`${BASE_URL}/reviews`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      hotel_id: hotelId,
      rating: rating,
      judul: judul,
      isi: isi
    })
  });
  const result = await res.json();
  console.log('Hasil Kirim Ulasan:', result);
  return result;
}
```

### Contoh Menggunakan PHP:
```php
<?php
$apiUrl = 'https://snooze-study-gravy.ngrok-free.dev/api/hotels?kecamatan=' . urlencode('Medan Barat');

$response = file_get_contents($apiUrl);
$result = json_decode($response, true);

if ($result['status'] === 'ok') {
    $hotels = $result['data'];
    print_r($hotels);
}
```

---

## 📞 Kontak Integrasi
Jika ada kendala format data, kebutuhan koordinat, atau endpoint tambahan, silakan hubungi tim **Penginapan (Singgah Medan)**.
