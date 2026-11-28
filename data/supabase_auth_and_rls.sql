-- ============================================================================
-- SINGGAH MEDAN — MIGRASI KE SUPABASE AUTH (auth.users UUID) & RLS POLICIES
-- ============================================================================
-- Jalankan skrip ini di Supabase SQL Editor untuk proyek yyuyyeprvalziinpfxdy.
-- Mengikuti Supabase Security Checklist:
--   1. RLS aktif di seluruh tabel schema public.
--   2. Memakai `TO authenticated` / `TO anon` (bukan auth.role() yang deprecated).
--   3. Memakai `(select auth.uid())` agar dievaluasi sekali per kueri.
--   4. Role otorisasi admin dibaca dari `app_metadata` (bukan `user_metadata`).
--   5. Tabel sensitif (`api_keys`) dikunci penuh dari Data API (anon/authenticated).
-- ============================================================================

BEGIN;

-- 1. Tabel profiles (terhubung 1:1 dengan auth.users.id UUID)
CREATE TABLE IF NOT EXISTS public.profiles (
    id              UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    nama            VARCHAR(150) NOT NULL,
    email           VARCHAR(191) NOT NULL,
    role            VARCHAR(20)  NOT NULL DEFAULT 'user',
    no_hp           VARCHAR(20)  NULL,
    jenis_kelamin   VARCHAR(10)  NULL,
    tanggal_lahir   DATE         NULL,
    kota_asal       VARCHAR(100) NULL,
    notif_dibaca_at TIMESTAMPTZ  NULL,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_profiles_email UNIQUE (email),
    CONSTRAINT chk_profiles_role CHECK (role IN ('user', 'admin')),
    CONSTRAINT chk_profiles_jk   CHECK (jenis_kelamin IS NULL OR jenis_kelamin IN ('Laki-laki', 'Perempuan', 'L', 'P'))
);

-- Trigger otomatis membuat baris di public.profiles saat user mendaftar di auth.users
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
BEGIN
    INSERT INTO public.profiles (id, nama, email, role, no_hp, jenis_kelamin, tanggal_lahir, kota_asal)
    VALUES (
        NEW.id,
        COALESCE(NULLIF(TRIM(NEW.raw_user_meta_data ->> 'nama'), ''), split_part(NEW.email, '@', 1)),
        NEW.email,
        COALESCE(NULLIF(NEW.raw_app_meta_data ->> 'role', ''), 'user'),
        NULLIF(TRIM(NEW.raw_user_meta_data ->> 'no_hp'), ''),
        NULLIF(TRIM(NEW.raw_user_meta_data ->> 'jenis_kelamin'), ''),
        NULLIF(TRIM(NEW.raw_user_meta_data ->> 'tanggal_lahir'), '')::DATE,
        NULLIF(TRIM(NEW.raw_user_meta_data ->> 'kota_asal'), '')
    )
    ON CONFLICT (id) DO NOTHING;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Helper internal untuk cek admin dari JWT app_metadata (aman dari manipulasi user_metadata)
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS BOOLEAN
LANGUAGE sql
STABLE
AS $$
    SELECT COALESCE((select auth.jwt() -> 'app_metadata' ->> 'role') = 'admin', FALSE);
$$;

-- 2. Tambah kolom auth_user_id (UUID) pada tabel relasi agar mendukung migrasi bertahap
--    dari integer user_id lama ke auth.users(id) UUID tanpa kehilangan data existing.
ALTER TABLE public.reviews   ADD COLUMN IF NOT EXISTS auth_user_id UUID NULL REFERENCES auth.users(id) ON DELETE CASCADE;
ALTER TABLE public.favorites ADD COLUMN IF NOT EXISTS auth_user_id UUID NULL REFERENCES auth.users(id) ON DELETE CASCADE;
ALTER TABLE public.inquiries ADD COLUMN IF NOT EXISTS auth_user_id UUID NULL REFERENCES auth.users(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_reviews_auth_user_created   ON public.reviews (auth_user_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_favorites_auth_user_created ON public.favorites (auth_user_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_inquiries_auth_user_created ON public.inquiries (auth_user_id, created_at DESC);

-- 3. Aktifkan Row-Level Security (RLS) pada seluruh tabel public
ALTER TABLE public.profiles  ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.hotels    ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reviews   ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.favorites ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.inquiries ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.kuliner   ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.api_keys  ENABLE ROW LEVEL SECURITY;

-- ============================================================================
-- 4. RLS POLICIES
-- ============================================================================

-- PROFILES: pengguna hanya bisa baca/ubah profilnya sendiri; admin bisa lihat semua
DROP POLICY IF EXISTS "profiles_select_own_or_admin" ON public.profiles;
CREATE POLICY "profiles_select_own_or_admin"
    ON public.profiles FOR SELECT
    TO authenticated
    USING ((select auth.uid()) = id OR public.is_admin());

DROP POLICY IF EXISTS "profiles_update_own" ON public.profiles;
CREATE POLICY "profiles_update_own"
    ON public.profiles FOR UPDATE
    TO authenticated
    USING ((select auth.uid()) = id)
    WITH CHECK ((select auth.uid()) = id AND role = (SELECT p.role FROM public.profiles p WHERE p.id = (select auth.uid())));

-- HOTELS: publik bisa baca; hanya admin bisa tulis
DROP POLICY IF EXISTS "hotels_select_public" ON public.hotels;
CREATE POLICY "hotels_select_public"
    ON public.hotels FOR SELECT
    TO anon, authenticated
    USING (true);

DROP POLICY IF EXISTS "hotels_write_admin" ON public.hotels;
CREATE POLICY "hotels_write_admin"
    ON public.hotels FOR ALL
    TO authenticated
    USING (public.is_admin())
    WITH CHECK (public.is_admin());

-- KULINER: publik bisa baca; hanya admin bisa tulis
DROP POLICY IF EXISTS "kuliner_select_public" ON public.kuliner;
CREATE POLICY "kuliner_select_public"
    ON public.kuliner FOR SELECT
    TO anon, authenticated
    USING (true);

DROP POLICY IF EXISTS "kuliner_write_admin" ON public.kuliner;
CREATE POLICY "kuliner_write_admin"
    ON public.kuliner FOR ALL
    TO authenticated
    USING (public.is_admin())
    WITH CHECK (public.is_admin());

-- REVIEWS: publik bisa baca; authenticated hanya bisa kelola ulasan miliknya sendiri
DROP POLICY IF EXISTS "reviews_select_public" ON public.reviews;
CREATE POLICY "reviews_select_public"
    ON public.reviews FOR SELECT
    TO anon, authenticated
    USING (true);

DROP POLICY IF EXISTS "reviews_insert_own" ON public.reviews;
CREATE POLICY "reviews_insert_own"
    ON public.reviews FOR INSERT
    TO authenticated
    WITH CHECK ((select auth.uid()) = auth_user_id);

DROP POLICY IF EXISTS "reviews_update_own" ON public.reviews;
CREATE POLICY "reviews_update_own"
    ON public.reviews FOR UPDATE
    TO authenticated
    USING ((select auth.uid()) = auth_user_id)
    WITH CHECK ((select auth.uid()) = auth_user_id);

DROP POLICY IF EXISTS "reviews_delete_own_or_admin" ON public.reviews;
CREATE POLICY "reviews_delete_own_or_admin"
    ON public.reviews FOR DELETE
    TO authenticated
    USING ((select auth.uid()) = auth_user_id OR public.is_admin());

-- FAVORITES: privat per pengguna
DROP POLICY IF EXISTS "favorites_select_own" ON public.favorites;
CREATE POLICY "favorites_select_own"
    ON public.favorites FOR SELECT
    TO authenticated
    USING ((select auth.uid()) = auth_user_id);

DROP POLICY IF EXISTS "favorites_insert_own" ON public.favorites;
CREATE POLICY "favorites_insert_own"
    ON public.favorites FOR INSERT
    TO authenticated
    WITH CHECK ((select auth.uid()) = auth_user_id);

DROP POLICY IF EXISTS "favorites_delete_own" ON public.favorites;
CREATE POLICY "favorites_delete_own"
    ON public.favorites FOR DELETE
    TO authenticated
    USING ((select auth.uid()) = auth_user_id);

-- INQUIRIES: pemilik & admin bisa baca; siapa saja bisa kirim pertanyaan/pesan
DROP POLICY IF EXISTS "inquiries_select_own_or_admin" ON public.inquiries;
CREATE POLICY "inquiries_select_own_or_admin"
    ON public.inquiries FOR SELECT
    TO authenticated
    USING ((select auth.uid()) = auth_user_id OR public.is_admin());

DROP POLICY IF EXISTS "inquiries_insert_public" ON public.inquiries;
CREATE POLICY "inquiries_insert_public"
    ON public.inquiries FOR INSERT
    TO anon, authenticated
    WITH CHECK (auth_user_id IS NULL OR (select auth.uid()) = auth_user_id);

DROP POLICY IF EXISTS "inquiries_update_own_or_admin" ON public.inquiries;
CREATE POLICY "inquiries_update_own_or_admin"
    ON public.inquiries FOR UPDATE
    TO authenticated
    USING ((select auth.uid()) = auth_user_id OR public.is_admin())
    WITH CHECK ((select auth.uid()) = auth_user_id OR public.is_admin());

DROP POLICY IF EXISTS "inquiries_delete_own_or_admin" ON public.inquiries;
CREATE POLICY "inquiries_delete_own_or_admin"
    ON public.inquiries FOR DELETE
    TO authenticated
    USING ((select auth.uid()) = auth_user_id OR public.is_admin());

-- API_KEYS: tidak ada policy untuk anon/authenticated -> terkunci dari Data API!
-- Hanya dapat diakses dari server backend (direct Postgres / service_role).

COMMIT;

