-- ============================================================================
-- SINGGAH MEDAN — OBJEK DATABASE SERVER (K2: View, Function/SP, Trigger)
-- ============================================================================
-- Memenuhi syarat minimal >= 3 objek database pada PostgreSQL Server.
-- Idempotent (aman dijalankan ulang di Supabase SQL Editor / psql).
-- ============================================================================

-- 1. VIEW: v_hotel_ringkasan
--    Menampilkan ringkasan hotel beserta total ulasan pengguna & jumlah favorit.
CREATE OR REPLACE VIEW v_hotel_ringkasan AS
SELECT
    h.id,
    h.peringkat,
    h.nama,
    h.slug,
    h.kecamatan,
    h.alamat,
    h.sarapan,
    h.rating,
    h.jumlah_ulasan,
    h.latitude,
    h.longitude,
    COUNT(DISTINCT r.id) AS total_ulasan_pengguna,
    COUNT(DISTINCT f.id) AS total_favorit
FROM hotels h
LEFT JOIN reviews r   ON r.hotel_id = h.id
LEFT JOIN favorites f ON f.hotel_id = h.id
GROUP BY h.id;

-- 2. VIEW: v_statistik_kecamatan
--    Menghubungkan data hotel dan kuliner berdasarkan kunci penghubung `kecamatan`.
CREATE OR REPLACE VIEW v_statistik_kecamatan AS
SELECT
    k_kec.kecamatan,
    COALESCE(h.jumlah_hotel, 0)   AS jumlah_hotel,
    h.rata_rating_hotel,
    COALESCE(k.jumlah_kuliner, 0) AS jumlah_kuliner
FROM (
    SELECT kecamatan FROM hotels WHERE kecamatan IS NOT NULL
    UNION
    SELECT kecamatan FROM kuliner WHERE kecamatan IS NOT NULL
) k_kec
LEFT JOIN (
    SELECT kecamatan, COUNT(*) AS jumlah_hotel, ROUND(AVG(rating), 2) AS rata_rating_hotel
    FROM hotels
    WHERE kecamatan IS NOT NULL
    GROUP BY kecamatan
) h ON h.kecamatan = k_kec.kecamatan
LEFT JOIN (
    SELECT kecamatan, COUNT(*) AS jumlah_kuliner
    FROM kuliner
    WHERE kecamatan IS NOT NULL
    GROUP BY kecamatan
) k ON k.kecamatan = k_kec.kecamatan
ORDER BY jumlah_hotel DESC, k_kec.kecamatan ASC;

-- 3. FUNCTION / STORED PROCEDURE: fn_sinkron_rating_hotel(p_hotel_id INT)
--    Menghitung ulang rating gabungan (baseline data awal + ulasan user) secara atomik.
CREATE OR REPLACE FUNCTION fn_sinkron_rating_hotel(p_hotel_id INT)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_base_rating NUMERIC(2,1);
    v_base_count  INT;
    v_user_count  INT;
    v_user_sum    NUMERIC;
    v_total_count INT;
    v_final_rating NUMERIC(2,1);
BEGIN
    SELECT rating_awal, COALESCE(jumlah_ulasan_awal, 0)
    INTO v_base_rating, v_base_count
    FROM hotels
    WHERE id = p_hotel_id;

    IF NOT FOUND THEN
        RETURN;
    END IF;

    SELECT COUNT(*), COALESCE(SUM(rating), 0)
    INTO v_user_count, v_user_sum
    FROM reviews
    WHERE hotel_id = p_hotel_id;

    v_total_count := v_base_count + v_user_count;

    IF v_total_count > 0 THEN
        v_final_rating := ROUND(
            (
                (CASE WHEN v_base_rating IS NOT NULL AND v_base_count > 0 THEN v_base_rating * v_base_count ELSE 0 END)
                + v_user_sum
            ) / v_total_count,
            1
        );
    ELSE
        v_final_rating := NULL;
    END IF;

    UPDATE hotels
    SET rating        = v_final_rating,
        jumlah_ulasan = v_total_count,
        updated_at    = CURRENT_TIMESTAMP
    WHERE id = p_hotel_id;
END;
$$;

-- 4. TRIGGER FUNCTION & TRIGGER: trg_reviews_sinkron_rating
--    Otomatis memperbarui rating & jumlah_ulasan pada tabel `hotels`
--    setiap kali ada INSERT, UPDATE, atau DELETE di tabel `reviews`.
CREATE OR REPLACE FUNCTION fn_trg_reviews_sinkron_rating()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        PERFORM fn_sinkron_rating_hotel(OLD.hotel_id);
        RETURN OLD;
    ELSIF TG_OP = 'UPDATE' THEN
        IF OLD.hotel_id IS DISTINCT FROM NEW.hotel_id THEN
            PERFORM fn_sinkron_rating_hotel(OLD.hotel_id);
        END IF;
        PERFORM fn_sinkron_rating_hotel(NEW.hotel_id);
        RETURN NEW;
    ELSE
        PERFORM fn_sinkron_rating_hotel(NEW.hotel_id);
        RETURN NEW;
    END IF;
END;
$$;

DROP TRIGGER IF EXISTS trg_reviews_sinkron_rating ON reviews;
CREATE TRIGGER trg_reviews_sinkron_rating
AFTER INSERT OR UPDATE OR DELETE ON reviews
FOR EACH ROW
EXECUTE FUNCTION fn_trg_reviews_sinkron_rating();

-- 5. FUNCTION: fn_hotel_terdekat(p_lat, p_lng, p_limit)
--    Mencari hotel terdekat dari koordinat GPS menggunakan rumus Haversine (km).
CREATE OR REPLACE FUNCTION fn_hotel_terdekat(
    p_lat   DOUBLE PRECISION,
    p_lng   DOUBLE PRECISION,
    p_limit INT DEFAULT 10
)
RETURNS TABLE (
    id            INT,
    nama          VARCHAR,
    kecamatan     VARCHAR,
    alamat        TEXT,
    rating        NUMERIC,
    jumlah_ulasan INT,
    jarak_km      DOUBLE PRECISION
)
LANGUAGE sql
STABLE
AS $$
    SELECT
        h.id,
        h.nama,
        h.kecamatan,
        h.alamat,
        h.rating,
        h.jumlah_ulasan,
        ROUND((
            6371 * ACOS(
                LEAST(1.0, GREATEST(-1.0,
                    COS(RADIANS(p_lat)) * COS(RADIANS(h.latitude::DOUBLE PRECISION))
                    * COS(RADIANS(h.longitude::DOUBLE PRECISION) - RADIANS(p_lng))
                    + SIN(RADIANS(p_lat)) * SIN(RADIANS(h.latitude::DOUBLE PRECISION))
                ))
            )
        )::NUMERIC, 2)::DOUBLE PRECISION AS jarak_km
    FROM hotels h
    WHERE h.latitude IS NOT NULL
      AND h.longitude IS NOT NULL
    ORDER BY jarak_km ASC, h.rating DESC NULLS LAST
    LIMIT p_limit;
$$;

