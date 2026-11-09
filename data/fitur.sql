-- Singgah Medan: tabel fitur tambahan (jalankan SETELAH data/hotels.sql)
-- pgAdmin: Query Tool -> buka file ini -> F5.   Terminal: psql -d singgah_medan -f data/fitur.sql
BEGIN;

ALTER TABLE hotels ADD COLUMN IF NOT EXISTS latitude  NUMERIC(9,6);
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS longitude NUMERIC(9,6);

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS favorites (
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  hotel_id INTEGER NOT NULL REFERENCES hotels(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT NOW(),
  PRIMARY KEY (user_id, hotel_id)
);

CREATE TABLE IF NOT EXISTS reviews (
  id SERIAL PRIMARY KEY,
  hotel_id INTEGER NOT NULL REFERENCES hotels(id) ON DELETE CASCADE,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  rating SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  judul VARCHAR(150),
  isi TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP,
  UNIQUE (hotel_id, user_id)
);

CREATE TABLE IF NOT EXISTS inquiries (
  id SERIAL PRIMARY KEY,
  hotel_id INTEGER NOT NULL REFERENCES hotels(id) ON DELETE CASCADE,
  user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  telepon VARCHAR(30),
  tgl_checkin DATE,
  tgl_checkout DATE,
  jumlah_tamu SMALLINT,
  pesan TEXT,
  status VARCHAR(12) NOT NULL DEFAULT 'baru',
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_keys (
  id SERIAL PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  key_prefix VARCHAR(8) NOT NULL,
  key_hash CHAR(64) NOT NULL UNIQUE,
  aktif BOOLEAN NOT NULL DEFAULT TRUE,
  last_used_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS kuliner (
  id SERIAL PRIMARY KEY,
  sumber VARCHAR(50) NOT NULL DEFAULT 'kelompok-kuliner',
  sumber_id VARCHAR(100) NOT NULL,
  nama VARCHAR(255) NOT NULL,
  kategori VARCHAR(100),
  alamat TEXT,
  kecamatan VARCHAR(80),
  rating NUMERIC(2,1),
  harga VARCHAR(60),
  foto TEXT,
  url TEXT,
  synced_at TIMESTAMP DEFAULT NOW(),
  UNIQUE (sumber, sumber_id)
);
CREATE INDEX IF NOT EXISTS idx_kuliner_kecamatan ON kuliner (kecamatan);

COMMIT;
