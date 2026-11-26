-- ============================================================================
-- SINGGAH MEDAN — USER & HAK AKSES DATABASE (K2: Least Privilege)
-- ============================================================================

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'app_penginapan') THEN
        CREATE ROLE app_penginapan LOGIN PASSWORD 'GANTI_PASSWORD_AMAN_DISINI'
            NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT;
    END IF;
END
$$;

-- Izinkan akses ke schema public (tanpa hak CREATE TABLE / DROP TABLE)
GRANT USAGE ON SCHEMA public TO app_penginapan;

-- Hak akses CRUD standar pada tabel aplikasi
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_penginapan;

-- Hak akses sequence untuk kolom SERIAL / auto-increment
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_penginapan;

-- Hak eksekusi function/stored procedure aplikasi
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO app_penginapan;

-- Default privileges untuk objek baru yang dibuat oleh owner ke depannya
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_penginapan;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO app_penginapan;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT EXECUTE ON FUNCTIONS TO app_penginapan;

-- ============================================================================
-- Opsional: Role khusus Read-Only untuk aplikasi/mitra eksternal (misal pelaporan)
-- ============================================================================
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'app_penginapan_readonly') THEN
        CREATE ROLE app_penginapan_readonly LOGIN PASSWORD 'GANTI_PASSWORD_READONLY_DISINI'
            NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT;
    END IF;
END
$$;

GRANT USAGE ON SCHEMA public TO app_penginapan_readonly;
GRANT SELECT ON hotels, reviews, kuliner, v_hotel_ringkasan, v_statistik_kecamatan TO app_penginapan_readonly;
GRANT EXECUTE ON FUNCTION fn_hotel_terdekat(DOUBLE PRECISION, DOUBLE PRECISION, INT) TO app_penginapan_readonly;

