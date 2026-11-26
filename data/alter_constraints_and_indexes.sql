-- ============================================================================
-- SINGGAH MEDAN — PENYESUAIAN CONSTRAINT & INDEX PADA TABEL EXISTING SUPABASE
-- ============================================================================

-- 1. Perbarui Foreign Key agar mendukung ON DELETE CASCADE / SET NULL
ALTER TABLE favorites DROP CONSTRAINT IF EXISTS favorites_user_id_fkey;
ALTER TABLE favorites ADD CONSTRAINT favorites_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE favorites DROP CONSTRAINT IF EXISTS favorites_hotel_id_fkey;
ALTER TABLE favorites ADD CONSTRAINT favorites_hotel_id_fkey
    FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE reviews DROP CONSTRAINT IF EXISTS reviews_hotel_id_fkey;
ALTER TABLE reviews ADD CONSTRAINT reviews_hotel_id_fkey
    FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE reviews DROP CONSTRAINT IF EXISTS reviews_user_id_fkey;
ALTER TABLE reviews ADD CONSTRAINT reviews_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE inquiries DROP CONSTRAINT IF EXISTS inquiries_hotel_id_fkey;
ALTER TABLE inquiries ADD CONSTRAINT inquiries_hotel_id_fkey
    FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE inquiries DROP CONSTRAINT IF EXISTS inquiries_user_id_fkey;
ALTER TABLE inquiries ADD CONSTRAINT inquiries_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- 2. Tambahkan CHECK Constraint yang belum ada (NOT VALID agar tidak terblokir data lama, lalu VALIDATE jika bersih)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_users_role') THEN
        ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('user', 'admin'));
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_hotels_rating') THEN
        ALTER TABLE hotels ADD CONSTRAINT chk_hotels_rating CHECK (rating IS NULL OR (rating >= 0 AND rating <= 5));
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_hotels_sarapan') THEN
        ALTER TABLE hotels ADD CONSTRAINT chk_hotels_sarapan CHECK (sarapan IN ('Tidak', 'Ya', 'All-in'));
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_inquiries_tamu') THEN
        ALTER TABLE inquiries ADD CONSTRAINT chk_inquiries_tamu CHECK (jumlah_tamu IS NULL OR jumlah_tamu >= 1);
    END IF;
END
$$;

-- 3. Tambahkan INDEX kueri utama (idempotent)
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

