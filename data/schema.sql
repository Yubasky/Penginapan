-- ============================================================================
-- SINGGAH MEDAN — SKEMA DATABASE POSTGRESQL (K1: Perancangan Database)
-- ============================================================================

-- 1. Tabel users (Pengguna & Admin)
CREATE TABLE IF NOT EXISTS users (
    id              SERIAL PRIMARY KEY,
    nama            VARCHAR(150) NOT NULL,
    email           VARCHAR(191) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            VARCHAR(20)  NOT NULL DEFAULT 'user',
    no_hp           VARCHAR(20)  NULL,
    jenis_kelamin   VARCHAR(10)  NULL,
    tanggal_lahir   DATE         NULL,
    kota_asal       VARCHAR(100) NULL,
    notif_dibaca_at TIMESTAMP    NULL,
    created_at      TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT chk_users_role CHECK (role IN ('user', 'admin')),
    CONSTRAINT chk_users_jk   CHECK (jenis_kelamin IS NULL OR jenis_kelamin IN ('Laki-laki', 'Perempuan', 'L', 'P'))
);

-- 2. Tabel hotels (Data Penginapan Medan)
CREATE TABLE IF NOT EXISTS hotels (
    id                  SERIAL PRIMARY KEY,
    peringkat           INT           NOT NULL,
    halaman             INT           NULL,
    nama                VARCHAR(255)  NOT NULL,
    slug                VARCHAR(255)  NOT NULL,
    rating              NUMERIC(2,1)  NULL,
    jumlah_ulasan       INT           NULL DEFAULT 0,
    rating_awal         NUMERIC(2,1)  NULL,
    jumlah_ulasan_awal  INT           NULL DEFAULT 0,
    sarapan             VARCHAR(10)   NOT NULL DEFAULT 'Tidak',
    reviewer            VARCHAR(150)  NULL,
    rating_ulasan       SMALLINT      NULL,
    tanggal_ulasan      VARCHAR(30)   NULL,
    judul_ulasan        TEXT          NULL,
    link_tripadvisor    TEXT          NULL,
    alamat              TEXT          NULL,
    kecamatan           VARCHAR(80)   NULL,
    latitude            NUMERIC(10,7) NULL,
    longitude           NUMERIC(10,7) NULL,
    foto                TEXT          NULL,
    foto_fasilitas      TEXT          NULL,
    created_at          TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_hotels_slug     UNIQUE (slug),
    CONSTRAINT chk_hotels_rating  CHECK (rating IS NULL OR (rating >= 0 AND rating <= 5)),
    CONSTRAINT chk_hotels_ulasan  CHECK (jumlah_ulasan IS NULL OR jumlah_ulasan >= 0),
    CONSTRAINT chk_hotels_sarapan CHECK (sarapan IN ('Tidak', 'Ya', 'All-in')),
    CONSTRAINT chk_hotels_lat     CHECK (latitude IS NULL OR (latitude >= -90 AND latitude <= 90)),
    CONSTRAINT chk_hotels_lng     CHECK (longitude IS NULL OR (longitude >= -180 AND longitude <= 180))
);

-- 3. Tabel reviews (Ulasan Pengunjung Web & API Client)
CREATE TABLE IF NOT EXISTS reviews (
    id          SERIAL PRIMARY KEY,
    hotel_id    INT          NOT NULL,
    user_id     INT          NOT NULL,
    rating      SMALLINT     NOT NULL,
    judul       VARCHAR(150) NULL,
    isi         TEXT         NOT NULL,
    media       TEXT         NULL,
    created_at  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
);

-- 4. Tabel favorites (Penginapan Favorit User — Composite Primary Key)
CREATE TABLE IF NOT EXISTS favorites (
    user_id     INT       NOT NULL,
    hotel_id    INT       NOT NULL,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT favorites_pkey PRIMARY KEY (user_id, hotel_id),
    CONSTRAINT fk_favorites_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_favorites_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 5. Tabel inquiries (Pesan & Tanya Jawab Hotel)
CREATE TABLE IF NOT EXISTS inquiries (
    id            SERIAL PRIMARY KEY,
    hotel_id      INT          NOT NULL,
    user_id       INT          NULL,
    nama          VARCHAR(150) NOT NULL,
    email         VARCHAR(191) NOT NULL,
    telepon       VARCHAR(30)  NULL,
    tgl_checkin   DATE         NULL,
    tgl_checkout  DATE         NULL,
    jumlah_tamu   SMALLINT     NULL DEFAULT 1,
    pesan         TEXT         NULL,
    jawaban       TEXT         NULL,
    status        VARCHAR(30)  NOT NULL DEFAULT 'baru',
    balasan_admin TEXT         NULL,
    dibalas_at    TIMESTAMP    NULL,
    created_at    TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inquiries_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_inquiries_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_inquiries_tamu CHECK (jumlah_tamu IS NULL OR jumlah_tamu >= 1),
    CONSTRAINT chk_inquiries_tgl  CHECK (tgl_checkout IS NULL OR tgl_checkin IS NULL OR tgl_checkout >= tgl_checkin)
);

-- 6. Tabel api_keys (Kunci API Mitra / Kelompok Lain — disimpan sebagai hash SHA-256)
CREATE TABLE IF NOT EXISTS api_keys (
    id           SERIAL PRIMARY KEY,
    nama         VARCHAR(150) NOT NULL,
    key_prefix   VARCHAR(16)  NOT NULL,
    key_hash     CHAR(64)     NOT NULL,
    aktif        BOOLEAN      NOT NULL DEFAULT TRUE,
    last_used_at TIMESTAMP    NULL,
    created_at   TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_api_keys_hash UNIQUE (key_hash)
);

-- 7. Tabel kuliner (Cache/Sinkronisasi Data dari Kelompok Kuliner)
CREATE TABLE IF NOT EXISTS kuliner (
    id          SERIAL PRIMARY KEY,
    sumber      VARCHAR(80)   NOT NULL DEFAULT 'kelompok-kuliner',
    sumber_id   VARCHAR(100)  NOT NULL,
    nama        VARCHAR(255)  NOT NULL,
    kategori    VARCHAR(100)  NULL,
    alamat      TEXT          NULL,
    kecamatan   VARCHAR(80)   NULL,
    rating      NUMERIC(2,1)  NULL,
    harga       VARCHAR(100)  NULL,
    foto        TEXT          NULL,
    url         TEXT          NULL,
    latitude    NUMERIC(10,7) NULL,
    longitude   NUMERIC(10,7) NULL,
    synced_at   TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_kuliner_sumber UNIQUE (sumber, sumber_id),
    CONSTRAINT chk_kuliner_rating CHECK (rating IS NULL OR (rating >= 0 AND rating <= 5))
);

-- ============================================================================
-- INDEKS KINERJA KUERI UTAMA (K1)
-- ============================================================================
CREATE INDEX IF NOT EXISTS idx_hotels_kecamatan  ON hotels (kecamatan);
CREATE INDEX IF NOT EXISTS idx_hotels_rating     ON hotels (rating DESC NULLS LAST);
CREATE INDEX IF NOT EXISTS idx_hotels_peringkat  ON hotels (peringkat ASC);
CREATE INDEX IF NOT EXISTS idx_hotels_sarapan    ON hotels (sarapan);

CREATE INDEX IF NOT EXISTS idx_reviews_hotel_id  ON reviews (hotel_id);
CREATE INDEX IF NOT EXISTS idx_reviews_user_id   ON reviews (user_id);
CREATE INDEX IF NOT EXISTS idx_reviews_created   ON reviews (created_at DESC);

CREATE INDEX IF NOT EXISTS idx_inquiries_hotel   ON inquiries (hotel_id);
CREATE INDEX IF NOT EXISTS idx_inquiries_user    ON inquiries (user_id);
CREATE INDEX IF NOT EXISTS idx_inquiries_status  ON inquiries (status);

CREATE INDEX IF NOT EXISTS idx_kuliner_kecamatan ON kuliner (kecamatan);
CREATE INDEX IF NOT EXISTS idx_kuliner_rating    ON kuliner (rating DESC NULLS LAST);

