--
-- PostgreSQL database dump
--


-- Dumped from database version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)
-- Dumped by pg_dump version 18.6 (Ubuntu 18.6-0ubuntu0.26.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: api_keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.api_keys (
    id integer NOT NULL,
    nama character varying(100) NOT NULL,
    key_prefix character varying(8) NOT NULL,
    key_hash character(64) NOT NULL,
    aktif boolean DEFAULT true NOT NULL,
    last_used_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: api_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.api_keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: api_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.api_keys_id_seq OWNED BY public.api_keys.id;


--
-- Name: favorites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.favorites (
    user_id integer NOT NULL,
    hotel_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


--
-- Name: hotels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.hotels (
    id integer NOT NULL,
    peringkat integer NOT NULL,
    halaman integer,
    nama character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    rating numeric(2,1),
    jumlah_ulasan integer,
    sarapan character varying(10) DEFAULT 'Tidak'::character varying NOT NULL,
    reviewer character varying(150),
    rating_ulasan smallint,
    tanggal_ulasan character varying(30),
    judul_ulasan text,
    link_tripadvisor text,
    alamat text,
    kecamatan character varying(80),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone,
    latitude numeric(9,6),
    longitude numeric(9,6)
);


--
-- Name: hotels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.hotels_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: hotels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.hotels_id_seq OWNED BY public.hotels.id;


--
-- Name: inquiries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inquiries (
    id integer NOT NULL,
    hotel_id integer NOT NULL,
    user_id integer,
    nama character varying(100) NOT NULL,
    email character varying(190) NOT NULL,
    telepon character varying(30),
    tgl_checkin date,
    tgl_checkout date,
    jumlah_tamu smallint,
    pesan text,
    status character varying(12) DEFAULT 'baru'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone
);


--
-- Name: inquiries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inquiries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inquiries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inquiries_id_seq OWNED BY public.inquiries.id;


--
-- Name: kuliner; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.kuliner (
    id integer NOT NULL,
    sumber character varying(50) DEFAULT 'kelompok-kuliner'::character varying NOT NULL,
    sumber_id character varying(100) NOT NULL,
    nama character varying(255) NOT NULL,
    kategori character varying(100),
    alamat text,
    kecamatan character varying(80),
    rating numeric(2,1),
    harga character varying(60),
    foto text,
    url text,
    synced_at timestamp without time zone DEFAULT now()
);


--
-- Name: kuliner_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.kuliner_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: kuliner_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.kuliner_id_seq OWNED BY public.kuliner.id;


--
-- Name: reviews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reviews (
    id integer NOT NULL,
    hotel_id integer NOT NULL,
    user_id integer NOT NULL,
    rating smallint NOT NULL,
    judul character varying(150),
    isi text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone,
    CONSTRAINT reviews_rating_check CHECK (((rating >= 1) AND (rating <= 5)))
);


--
-- Name: reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reviews_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reviews_id_seq OWNED BY public.reviews.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    nama character varying(100) NOT NULL,
    email character varying(190) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(10) DEFAULT 'user'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: api_keys id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys ALTER COLUMN id SET DEFAULT nextval('public.api_keys_id_seq'::regclass);


--
-- Name: hotels id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hotels ALTER COLUMN id SET DEFAULT nextval('public.hotels_id_seq'::regclass);


--
-- Name: inquiries id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inquiries ALTER COLUMN id SET DEFAULT nextval('public.inquiries_id_seq'::regclass);


--
-- Name: kuliner id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.kuliner ALTER COLUMN id SET DEFAULT nextval('public.kuliner_id_seq'::regclass);


--
-- Name: reviews id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews ALTER COLUMN id SET DEFAULT nextval('public.reviews_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: api_keys; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: favorites; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: hotels; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.hotels VALUES (1, 1, 1, 'Adimulia Hotel', 'adimulia-hotel-1', 4.1, 346, 'Ya', 'Samuel Pasaribu', 4, '31 Agu 2024', 'Desainnya mewah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10340836-Reviews-Adimulia_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Pangeran Diponegoro No. 8, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (2, 2, 1, 'Aiho Hotel Medan', 'aiho-hotel-medan-2', 4.7, 1007, 'Tidak', 'livia a', 5, '7 Nov 2026', 'staff & karyawannya super ramah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11878375-Reviews-AIHO_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Merbabu No. 32-34, Pusat Pasar, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (3, 3, 1, 'Aryaduta Medan', 'aryaduta-medan-3', 4.3, 1551, 'All-in', 'Navigate36121998740', 4, '26 Okt 2026', 'Arya duta', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1077917-Reviews-Aryaduta_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Maulana Lubis No. 8, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (4, 4, 1, 'Cambridge Hotel Medan', 'cambridge-hotel-medan-4', 4.1, 967, 'Tidak', 'Jeffry', 4, '10 Agu 2025', 'Cambridge ⭐⭐⭐⭐⭐', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1446654-Reviews-Cambridge_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. S. Parman No. 217, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (5, 5, 1, 'Hotel Santika Premiere Dyandra', 'hotel-santika-premiere-dyandra-5', 4.0, 805, 'All-in', 'Oetami D', 4, '10 Jun 2024', 'Feeling like at home', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2587201-Reviews-Hotel_Santika_Premiere_Dyandra_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Maulana Lubis No. 7, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (6, 6, 1, 'Grand Cityhall Hotel & Serviced Residences', 'grand-cityhall-hotel-serviced-residences-6', 4.8, 3956, 'Ya', 'Shafia P', 5, '7 Nov 2026', 'Excellent hotel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1635140-Reviews-Grand_Cityhall_Hotel_Serviced_Residences-Medan_North_Sumatra_Sumatra.html', 'Jl. Pemuda No. 3, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (7, 7, 1, 'The Reiz Suites, Artotel Curated', 'the-reiz-suites-artotel-curated-7', 5.0, 464, 'Tidak', 'GoPlaces63092722630', 5, '31 Okt 2026', 'Bersih dan nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23714251-Reviews-The_Reiz_Suites_Artotel_Curated_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Tembakau Deli No. 1, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (8, 8, 1, 'Hotel Danau Toba International', 'hotel-danau-toba-international-8', 3.0, 163, 'Ya', 'Rebekka pohan', 3, '18 Nov 2022', 'Kekurangan Danau Toba Hotel Medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1174405-Reviews-Hotel_Danau_Toba_International-Medan_North_Sumatra_Sumatra.html', 'Jl. Imam Bonjol No. 17, Madras Hulu, Kec. Medan Polonia', 'Medan Polonia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (9, 9, 1, 'JW MARRIOTT HOTEL MEDAN', 'jw-marriott-hotel-medan-9', 4.4, 1439, 'Tidak', 'Shilfanni Beatrix', 4, '21 Jun 2023', 'AIR PANAS MATI HAMPIR 1 MINGGU', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1225872-Reviews-JW_MARRIOTT_HOTEL_MEDAN-Medan_North_Sumatra_Sumatra.html', 'Jl. Putri Hijau No. 10, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (10, 10, 1, 'Le Polonia Hotel & Convention Medan Managed By Topotels', 'le-polonia-hotel-convention-medan-managed-by-topotels-10', 4.1, 280, 'Ya', 'Jefry Prw', 4, '8 Nov 2024', 'Tingkatkan Variasi dan Kualitas Makanan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1207189-Reviews-Le_Polonia_Hotel_Convention_Medan_Managed_By_Topotels-Medan_North_Sumatra_Sumatra.html', 'Jl. Jend. Sudirman No. 14-18, Madras Hulu, Kec. Medan Polonia', 'Medan Polonia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (11, 11, 1, 'Grand Sakura Hotel', 'grand-sakura-hotel-11', 3.5, 182, 'Ya', 'Ismayadi', 3, '11 Jun 2019', 'Lumayan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1975933-Reviews-Grand_Sakura_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Prof. HM. Yamin No. 41, Perintis, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (12, 12, 1, 'Favehotel S. Parman Medan', 'favehotel-s-parman-medan-12', 4.7, 262, 'Ya', 'Go42839549293', 5, '5 Nov 2026', 'Hoteell', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12433657-Reviews-Favehotel_S_Parman_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. S. Parman No. 217A, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (13, 13, 1, 'Grand Mercure Maha Cipta Medan Angkasa', 'grand-mercure-maha-cipta-medan-angkasa-13', 4.7, 2724, 'Ya', 'Safari32608121992', 5, '6 Nov 2026', 'Sangat nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d583007-Reviews-Grand_Mercure_Maha_Cipta_Medan_Angkasa-Medan_North_Sumatra_Sumatra.html', 'Jl. Sutomo No. 1, Perintis, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (14, 14, 1, 'Grand Central Premier', 'grand-central-premier-14', 4.6, 97, 'Ya', 'Resort16808807249', 5, '11 Jul 2025', 'Pengalaman menginap di grand premier center', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25567782-Reviews-Grand_Central_Premier-Medan_North_Sumatra_Sumatra.html', 'Jl. Merbau No. 1, Sekip, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (15, 15, 1, 'Hotel GranDhika Setiabudi Medan', 'hotel-grandhika-setiabudi-medan-15', 4.1, 117, 'Ya', 'Trek09148020006', 4, '7 Agu 2023', 'pegawainy hospitalityny bagus, trmaksih mbk gloria, mbk putri,mas saut dan halifi', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10712284-Reviews-Hotel_GranDhika_Setiabudi_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 169, Tanjung Sengkuang, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (16, 16, 1, 'Grand Inna Medan', 'grand-inna-medan-16', 3.6, 121, 'Ya', 'Sonia B', 4, '13 Feb 2026', 'Berkesan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d306829-Reviews-Grand_Inna_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Balai Kota No. 2, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (17, 17, 1, 'Karibia Boutique Hotel', 'karibia-boutique-hotel-17', 3.5, 273, 'Ya', 'DeddyAja20', 3, '11 Mar 2020', 'Kamar tidak kedap suara', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3784880-Reviews-Karibia_Boutique_Hotel-Medan_North_Sumatra_Sumatra.html', 'Komplek Centre Point, Jl. Jawa No. 8, Gang Buntu, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (18, 18, 1, 'The KNO Hotel', 'the-kno-hotel-18', 3.6, 10, 'Tidak', 'dickyardiansyah18', 4, '3 Mar 2019', 'Hotel Transit di KNO', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12292267-Reviews-The_KNO_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Bandara Kualanamu / Batang Kuis, Kec. Beringin, Kab. Deli Serdang', 'Beringin', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (19, 19, 1, 'Swiss-Belinn Gajah Mada Medan', 'swiss-belinn-gajah-mada-medan-19', 4.6, 467, 'Ya', 'Quest49983565208', 5, '5 Nov 2026', 'Pasangan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12690387-Reviews-Swiss_Belinn_Gajah_Mada_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Gajah Mada No. 49, Babura, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (20, 20, 1, 'The Batik Hotel', 'the-batik-hotel-20', 3.3, 71, 'Tidak', 'ryannusix', 3, '3 Apr 2019', 'Budget!', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8114446-Reviews-The_Batik_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Abdullah Lubis No. 75, Merdeka, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (21, 21, 1, 'ibis Styles Medan Pattimura', 'ibis-styles-medan-pattimura-21', 4.3, 196, 'Ya', 'Faiz F', 4, '6 Mar 2026', 'Hotel Rekomendasi di Tengah Kota Medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13836587-Reviews-Ibis_Styles_Medan_Pattimura-Medan_North_Sumatra_Sumatra.html', 'Jl. Pattimura No. 442, Darat, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (22, 22, 1, 'Hermes Palace Hotel Medan', 'hermes-palace-hotel-medan-22', 3.5, 194, 'Ya', 'Rizky Ramadhian... S', 3, '19 Okt 2019', 'Hotel berbintang tapi pas di kantong', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3176364-Reviews-Hermes_Palace_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Pemuda No. 22, Aur, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (23, 23, 1, 'Wisma Sederhana RedPartner', 'wisma-sederhana-redpartner-23', 3.3, 52, 'Tidak', 'l1ng3rman', 3, '7 Apr 2018', 'Bagus dan harga yang reasonable', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d648218-Reviews-Wisma_Sederhana_RedPartner-Medan_North_Sumatra_Sumatra.html', 'Jl. Selat Panjang No. 11, Pasar Baru, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (24, 24, 1, 'Miyana Hotel', 'miyana-hotel-24', 3.3, 28, 'Ya', 'Ruli R', 3, '25 Jan 2017', 'akses tol mudah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d5519567-Reviews-Miyana_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. H. Anif No. 28, Sampali, Kec. Percut Sei Tuan / Medan Timur', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (25, 25, 1, 'Elais by ARTOTEL', 'elais-by-artotel-25', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34277966-Reviews-Elais_by_ARTOTEL-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Muslim No. 111, Helvetia, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (26, 26, 1, 'Hotel Deli River', 'hotel-deli-river-26', 4.6, 486, 'Ya', 'Glen Simangunsong', 5, '20 Mei 2026', 'wisata', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d593866-Reviews-Hotel_Deli_River-Medan_North_Sumatra_Sumatra.html', 'Jl. Raya Medan-Delitua Km 10.5, Kedai Durian, Kec. Deli Tua', 'Deli Tua', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (27, 27, 1, 'Cordela Inn Millennium Medan', 'cordela-inn-millennium-medan-27', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21370989-Reviews-Cordela_Inn_Millennium_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Muslim No. 111, Helvetia, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (28, 28, 1, 'Grand Impression Hotel Medan', 'grand-impression-hotel-medan-28', 5.0, 1, 'Tidak', 'GrandTour41892948251', 5, '17 Des 2025', 'Liburan yg menarik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23172645-Reviews-Grand_Impression_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 159, Tanjung Sengkuang, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (29, 29, 1, 'De Paris Hotel', 'de-paris-hotel-29', 4.5, 32, 'Tidak', 'Explore51114155896', 5, '17 Jan 2026', 'Bagus Bangett Buat Remaja', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21410922-Reviews-De_Paris_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Danau Singkarak No. 18, Sei Agul, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (30, 30, 1, 'Grand Central Medan', 'grand-central-medan-30', 3.4, 15, 'Tidak', 'Rebecca S', 3, '23 Nov 2018', 'Hadehhh', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12103974-Reviews-Grand_Central_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Belutu No. 17, Merdeka, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (31, 31, 2, 'Four Points by Sheraton Medan', 'four-points-by-sheraton-medan-31', 4.0, 481, 'Tidak', 'Hermanto K', 4, '9 Jan 2026', 'Seharusnya bagus namun perlu peningkatan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3804566-Reviews-Four_Points_by_Sheraton_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Gatot Subroto No. 395, Sei Sikambing D, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (32, 32, 2, 'Swiss-Belinn Medan', 'swiss-belinn-medan-32', 3.6, 639, 'Ya', 'Anggita Deska', 4, '28 Agu 2025', 'Lokasi strategis, kamar luas, ada meja kerja', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1923799-Reviews-Swiss_Belinn_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Surabaya No. 88, Pasar Baru, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (33, 33, 2, 'Sishezu Hotel', 'sishezu-hotel-33', 5.0, 25, 'Ya', 'Nora T', 5, '5 Agu 2024', 'Perjalanan. Medan jakarta sangat melelahkan apalagi kalau ke universitas sumatera utara,', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26724300-Reviews-Sishezu_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 128, Padang Bulan, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (34, 34, 2, 'Skyview Setiabudi Apartment Medan', 'skyview-setiabudi-apartment-medan-34', 4.5, 37, 'Tidak', 'Resort00399504768', 5, '6 Jul 2026', 'Gumir heven', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17383285-Reviews-Skyview_Setiabudi_Apartment_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 79, Tanjung Sare, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (35, 35, 2, 'Madani Hotel Medan', 'madani-hotel-medan-35', 3.6, 135, 'Ya', 'Raihan Al Hafizh', 4, '17 Jan 2025', 'menginap di hotel Madani , adalah hal yang paling menyenangkan hotel nya juga termasuk Bagus dan pokok nya gak nyesal memilih ho', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d797811-Reviews-Madani_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 1, Mesjid, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (36, 36, 2, 'Emerald Garden Hotel', 'emerald-garden-hotel-36', 3.6, 117, 'Ya', 'Johannes', 4, '15 Okt 2026', 'Hotel Dan Staffnya sangat oke', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d788977-Reviews-Emerald_Garden_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. KL Yos Sudarso No. 1, Silalas, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (37, 37, 2, 'Desatu Hotel', 'desatu-hotel-37', 4.6, 9, 'Tidak', 'Kevin D', 5, '24 Feb 2024', 'lokasi luar biasa', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19972452-Reviews-Desatu_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Merbabu No. 1, Pusat Pasar, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (38, 38, 2, 'Cordela Hotel Medan', 'cordela-hotel-medan-38', 3.8, 52, 'Ya', 'Santoso84', 4, '5 Okt 2021', 'Trip medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d5519584-Reviews-Cordela_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Prof. HM. Yamin No. 90, Lebak Mantras, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (39, 39, 2, 'Kama Hotel', 'kama-hotel-39', 3.3, 90, 'Tidak', 'Mufti1971', 3, '3 Sep 2019', 'Hotel Sederhana untuk Singgah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2272389-Reviews-Kama_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. A. Yani No. 97/99, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (40, 40, 2, 'Antares Hotel', 'antares-hotel-40', 3.3, 26, 'Tidak', 'Riza Muthia', 3, '17 Des 2016', 'Hotel Lama dengan Lokasi Strategis', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1174226-Reviews-Antares_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 328, Siti Rejo I, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (41, 41, 2, 'M Hotel Medan', 'm-hotel-medan-41', 4.8, 12, 'Tidak', 'Chris W', 5, '2 Nov 2026', 'Memuaskan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33101506-Reviews-M_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Rotan No. 12-14, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (42, 42, 2, 'Harper Wahid Hasyim - Medan', 'harper-wahid-hasyim-medan-42', 4.6, 64, 'Tidak', 'Coastal09610318233', 5, '5 Jul 2026', 'Hotel bintang 3 terbaik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17796110-Reviews-Harper_Wahid_Hasyim_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. K.H. Wahid Hasyim No. 53, Babura, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (43, 43, 2, 'Dazhong Hostel Backpacker', 'dazhong-hostel-backpacker-43', 4.2, 24, 'Ya', 'LENNI5y', 4, '31 Jul 2018', 'Transit', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12801836-Reviews-Dazhong_Hostel_Backpacker-Medan_North_Sumatra_Sumatra.html', 'Jl. Cirebon No. 43, Pasar Komoditi, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (44, 44, 2, 'K77 Guest House Medan', 'k77-guest-house-medan-44', 4.4, 104, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6898003-Reviews-K77_Guest_House_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 77, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (45, 45, 2, 'Roemah 28', 'roemah-28-45', 4.1, 7, 'Tidak', 'intanmeutia', 4, '23 Des 2017', 'Hotel tenang dan nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10769506-Reviews-Roemah_28-Medan_North_Sumatra_Sumatra.html', 'Jl. Seamless / Dr. Mansyur No. 28, Medan Baru', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (46, 46, 2, 'd''primahotel Medan', 'd-primahotel-medan-46', 3.9, 183, 'Ya', 'riqbest', 4, '6 Nov 2019', 'Hotel didalam stasiun kreta', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6434963-Reviews-D_primahotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Stasiun Kereta Api Medan, Jl. Stasiun No. 1, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (47, 47, 2, 'Capital O 2023 Medan Ville Hotel', 'capital-o-2023-medan-ville-hotel-47', 3.9, 134, 'Tidak', 'Rahmat A', 4, '25 Okt 2019', 'Oke lah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2044613-Reviews-Capital_O_2023_Medan_Ville_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Taman Multatuli Indah No. E1, Aur, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (48, 48, 2, 'Tiara Medan Hotel & Convention Center', 'tiara-medan-hotel-convention-center-48', 3.6, 110, 'Tidak', 'jonnysutejo', 4, '12 Feb 2018', 'Cakap - cakap seru bersama Grab', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d583008-Reviews-Tiara_Medan_Hotel_Convention_Center-Medan_North_Sumatra_Sumatra.html', 'Jl. Cut Mutia No. 1, Madras Hulu, Kec. Medan Polonia', 'Medan Polonia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (49, 49, 2, 'Semalam at Sun Yat Sen Boutique Hostel', 'semalam-at-sun-yat-sen-boutique-hostel-49', 4.8, 11, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17789507-Reviews-Semalam_at_Sun_Yat_Sen_Boutique_Hostel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sun Yat Sen No. 86, Sei Rengas I, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (50, 50, 2, 'Permata Inn', 'permata-inn-50', 3.7, 60, 'Tidak', 'Iyan', 4, '26 Feb 2018', 'HOTEL BUDGET YANG CUKUP NYAMAN', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8720769-Reviews-Permata_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Gajah Mada No. 29, Babura, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (51, 51, 2, 'Gandhi Inn', 'gandhi-inn-51', 3.9, 40, 'Tidak', 'Eitym', 4, '23 Nov 2016', 'Dekat dengan Angsapura', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6678359-Reviews-Gandhi_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Gandhi No. 125A, Sei Rengas II, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (52, 52, 2, 'E Spa & Hotel Medan', 'e-spa-hotel-medan-52', 5.0, 7, 'Tidak', 'Davidchandra', 5, '25 Sep 2020', 'Fresh', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21248445-Reviews-E_Spa_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Krakatau No. 112, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (53, 53, 2, 'Uno Capsule Hostel', 'uno-capsule-hostel-53', 4.8, 9, 'Tidak', 'Tien Tetty Simamora', 4, '10 Apr 2019', 'Hostel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d16827836-Reviews-Uno_Capsule_Hostel-Medan_North_Sumatra_Sumatra.html', 'Jl. Hindu No. 12, Kesawan, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (54, 54, 2, 'Linnea Resort Tangkahan', 'linnea-resort-tangkahan-54', 3.8, 26, 'Ya', 'Derbi G', 5, '18 Okt 2016', 'Jalan2', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3435272-Reviews-Linnea_Resort_Tangkahan-Medan_North_Sumatra_Sumatra.html', 'Kawasan Wisata Tangkahan, Namu Sialang, Kec. Batang Serangan', 'Batang Serangan', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (55, 55, 2, 'Jiyo Boutique Inn', 'jiyo-boutique-inn-55', 5.0, 4, 'Tidak', 'safarudin', 5, '29 Nov 2023', 'Bersih nyaman tenang', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23674324-Reviews-Jiyo_Boutique_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. T. Amir Hamzah No. 48, Helvetia, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (56, 56, 2, 'MM capsule hostel', 'mm-capsule-hostel-56', 4.4, 12, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12868339-Reviews-MM_Capsule_Hostel-Medan_North_Sumatra_Sumatra.html', 'Jl. Rahmadsyah No. 18, Matsum, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (57, 57, 2, 'Golden Eleven Hotel', 'golden-eleven-hotel-57', 5.0, 4, 'Ya', 'Dream24318625391', 5, '13 Nov 2023', 'Hotel yang sangat bagus dan nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3784891-Reviews-Golden_Eleven_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Batang Hari No. 11, Babura Sunggal, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (58, 58, 2, 'Hotel Kanasha', 'hotel-kanasha-58', 4.3, 11, 'Ya', 'sukma', 4, '5 Nov 2019', 'Right choice to stay', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11695701-Reviews-Kanasha_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. TD Pardede No. 9, Petisah Upper, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (59, 59, 2, 'Grand Impression Hotel', 'grand-impression-hotel-59', 3.6, 30, 'Tidak', 'marlizaharahap', 4, '27 Agu 2019', 'Hotel murah dan bagus', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d7593904-Reviews-Grand_Impression_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 159, Tanjung Sengkuang, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (60, 60, 2, 'Resort Kapsul4D And Restoran', 'resort-kapsul4d-and-restoran-60', 5.0, 2, 'Tidak', 'Explorer34635238412', 5, '8 Jul 2026', 'KAPSUL4D Medan Sumatera Utara', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d27126011-Reviews-Resort_Kapsul4D_And_Restoran-Medan_North_Sumatra_Sumatra.html', 'Jl. Ring Road / Gagak Hitam No. 4D, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (61, 61, 3, 'Arabia Style Hotel', 'arabia-style-hotel-61', 5.0, 2, 'Tidak', 'Dream31042894277', 5, '14 Jun 2023', 'Sangat senang staycation di property ini', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25443091-Reviews-Arabia_Style_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sriwijaya No. 10, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (62, 62, 3, 'Bobotel Gatot Subroto', 'bobotel-gatot-subroto-62', 3.5, 8, 'Ya', 'Abdi9', 3, '25 Agu 2021', 'Kmr oke', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d18114316-Reviews-Bobotel_Gatot_Subroto-Medan_North_Sumatra_Sumatra.html', 'Jl. Gatot Subroto No. 120, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (63, 63, 3, 'Travellers Suites Serviced Apartments', 'travellers-suites-serviced-apartments-63', 3.5, 15, 'Tidak', 'Kasim K', 4, '19 Okt 2017', 'Bagus', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d482359-Reviews-Travellers_Suites_Serviced_Apartments-Medan_North_Sumatra_Sumatra.html', 'Jl. Kejaksaan No. 85, Petisah Tengah, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (64, 64, 3, 'Jangga House Bed & Breakfast', 'jangga-house-bed-breakfast-64', 3.5, 18, 'Tidak', 'malin', 4, '24 Des 2019', 'penginapan yang bersih', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3170274-Reviews-Jangga_House_Bed_Breakfast-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Skambing No. 37, Petisah Utama, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (65, 65, 3, 'Oyo 93672 Swasana Hotel Medan', 'oyo-93672-swasana-hotel-medan-65', 5.0, 1, 'Tidak', 'Jack', 5, '17 Feb 2024', 'Hotel budget terbaik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25246232-Reviews-Oyo_93672_Swasana_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Krakatau No. 70, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (66, 66, 3, 'Vivo Stay', 'vivo-stay-66', 3.6, 23, 'Ya', 'Hendra Simangunsong', 4, '16 Jul 2018', 'Murah Meriah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10771208-Reviews-Vivo_Stay-Medan_North_Sumatra_Sumatra.html', 'Jl. Pelajar No. 28, Teladan Timur, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (67, 67, 3, 'The Palace Inn', 'the-palace-inn-67', 3.7, 15, 'Tidak', 'swidarta', 4, '15 Feb 2017', 'Aseeekkkk', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2044213-Reviews-The_Palace_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Jend. Sudirman No. 40, Kec. Medan Polonia', 'Medan Polonia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (68, 68, 3, 'Orinko City', 'orinko-city-68', 3.7, 7, 'Tidak', 'santosoirawan54', 3, '18 Feb 2020', 'Nyaman untuk istirahat', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d7288931-Reviews-Orinko_City-Medan_North_Sumatra_Sumatra.html', 'Jl. Gaya Motor No. 1, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (69, 69, 3, 'Pantai Cermin Resort', 'pantai-cermin-resort-69', 3.4, 15, 'Tidak', 'Rizal V', 3, '30 Agu 2017', 'Pantai Cermin', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d7276776-Reviews-Pantai_Cermin_Resort-Medan_North_Sumatra_Sumatra.html', 'Kawasan Wisata Pantai Cermin, Serdang Bedagai', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (70, 70, 3, 'Pancur Gading Hotel & Resort', 'pancur-gading-hotel-resort-70', 3.6, 12, 'Ya', 'Rizki Hasibuan', 4, '27 Agu 2018', 'Hotel & Resort yang Nyaman dan Asri', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13530486-Reviews-Pancur_Gading_Hotel_Resort-Medan_North_Sumatra_Sumatra.html', 'Jl. Kuala Simpang, Namo Rambe, Kabupaten Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (71, 71, 3, 'Hanlis House Medan', 'hanlis-house-medan-71', 4.3, 3, 'Tidak', 'John Khalis', 5, '7 Sep 2020', 'Hanlis house Luar biasa', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8683315-Reviews-Hanlis_House_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Rokan No. 30, Babura Sunggal, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (72, 72, 3, 'Oyo 279 Deli Homestay', 'oyo-279-deli-homestay-72', 4.4, 5, 'Tidak', 'FerdyDenny_Tulong', 4, '3 Agu 2018', 'keadaan yang nyaman sekaligus mendebarkan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13290922-Reviews-Oyo_279_Deli_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Masuk Bandar Baru, Sibolangit', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (73, 73, 3, 'Grand Delta Hotel', 'grand-delta-hotel-73', 3.7, 17, 'Tidak', 'suwandytanjung', 4, '27 Nov 2015', 'Hotel Yang Lengkap', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2437805-Reviews-Grand_Delta_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Juanda No. 3C, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (74, 74, 3, 'Kuala namu Guesthouse', 'kuala-namu-guesthouse-74', 3.9, 9, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12139505-Reviews-Kuala_namu_Guesthouse-Medan_North_Sumatra_Sumatra.html', 'Jl. Bandara Kualanamu, Batang Kuis, Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (75, 75, 3, 'Hotel Citi International Sun Yat Sen', 'hotel-citi-international-sun-yat-sen-75', 3.3, 29, 'Ya', 'Lisa Tanoko', 3, '21 Jul 2015', 'Hotel Murah tapi nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1885243-Reviews-Hotel_Citi_International_Sun_Yat_Sen-Medan_North_Sumatra_Sumatra.html', 'Jl. Sun Yat Sen No. 77, Sei Rengas I, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (76, 76, 3, 'Hotel Grand Dhika Setiabudi Medan', 'hotel-grand-dhika-setiabudi-medan-76', 3.9, 9, 'Tidak', 'tjutjukbs', 4, '7 Apr 2017', 'Hotel Menawan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11771681-Reviews-Hotel_Grand_Dhika_Setiabudi_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 169, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (77, 77, 3, 'Sg Premium Guest House by Helocus', 'sg-premium-guest-house-by-helocus-77', 5.0, 1, 'Tidak', 'Nomad65761335669', 5, '10 Mei 2026', 'Sangat cocok untuk menginap yang low budget', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33219817-Reviews-Sg_Premium_Guest_House_by_Helocus-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Gebang No. 12, Babura, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (78, 78, 3, 'Capsule Inn Medan', 'capsule-inn-medan-78', 4.0, 6, 'Ya', 'Syaiful', 5, '23 Mar 2019', 'salah satu penginapan berbentuk capsule di Medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d15292396-Reviews-Capsule_Inn_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Zainul Arifin No. 14, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (79, 79, 3, 'The Little White House Medan', 'the-little-white-house-medan-79', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21040186-Reviews-The_Little_White_House_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Besitang No. 18, Sei Sikambing D, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (80, 80, 3, 'Royal Suite Condotel', 'royal-suite-condotel-80', 3.4, 28, 'Tidak', 'Gnes17', 3, '20 Jul 2016', 'Pilihan jika rombongan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d7890681-Reviews-Royal_Suite_Condotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Palang Merah No. 11, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (81, 81, 3, 'Haifa Residence', 'haifa-residence-81', 5.0, 1, 'Tidak', 'Daniela G', 5, '2 Jun 2025', 'Mantappp', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21294568-Reviews-Haifa_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur / Sei Padang No. 5, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (82, 82, 3, 'Hotel Robinson', 'hotel-robinson-82', 5.0, 1, 'Tidak', 'Odyssey29086965256', 5, '17 Feb 2025', 'd respon kak', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21040140-Reviews-Hotel_Robinson-Medan_North_Sumatra_Sumatra.html', 'Jl. Stasiun / Pandu No. 14, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (83, 83, 3, 'Anaya Hotel and Ballroom', 'anaya-hotel-and-ballroom-83', 5.0, 1, 'Tidak', 'Sasca N', 5, '17 Jul 2024', 'Pengalaman menginap di Hotel Anaya', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25175845-Reviews-Anaya_Hotel_and_Ballroom-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 136, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (84, 84, 3, 'Arabia Style Hotel Sriwijaya Managed by 3 Smart Hotel', 'arabia-style-hotel-sriwijaya-managed-by-3-smart-hotel-84', 5.0, 1, 'Tidak', 'Basyir', 5, '24 Jun 2024', 'Hotel Arabia style Sriwijaya layak di huni', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26774251-Reviews-Arabia_Style_Hotel_Sriwijaya_Managed_by_3_Smart_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sriwijaya No. 10, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (85, 85, 3, 'Griya 24 Homestay', 'griya-24-homestay-85', 4.3, 3, 'Ya', 'Denny W', 3, '15 Sep 2017', 'Penginapan nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11772238-Reviews-Griya_24_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Griya No. 24, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (86, 86, 3, 'Capital O 293 Mutiara Hijau Suites Syariah', 'capital-o-293-mutiara-hijau-suites-syariah-86', 3.8, 4, 'Tidak', 'vani', 4, '11 Jun 2022', 'Menginap dengan keluarga', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17387279-Reviews-Capital_O_293_Mutiara_Hijau_Suites_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 80, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (87, 87, 3, 'Lewi House Syariah', 'lewi-house-syariah-87', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d15210140-Reviews-Lewi_House_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Lewi / Dr. Mansyur, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (88, 88, 3, 'LJ Hotel', 'lj-hotel-88', 3.6, 9, 'Ya', 'm s', 4, '4 Nov 2017', 'Pelayanan Hotel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12562302-Reviews-LJ_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Perintis Kemerdekaan No. 8, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (89, 89, 3, 'Wisma Garuda', 'wisma-garuda-89', 3.9, 8, 'Tidak', 'jonnysutejo', 4, '16 Jan 2018', 'Rencana Liburan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d4108920-Reviews-Wisma_Garuda-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 27, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (90, 90, 3, 'Guest House Syariah Grand Jamee', 'guest-house-syariah-grand-jamee-90', 3.7, 7, 'Ya', 'Souvenir K', 4, '31 Jan 2018', 'Silaturahmi', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3316295-Reviews-Guest_House_Syariah_Grand_Jamee-Medan_North_Sumatra_Sumatra.html', 'Jl. Ring Road / Gagak Hitam No. 8, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (91, 91, 4, 'OYO 294 Andrea Residence Syariah', 'oyo-294-andrea-residence-syariah-91', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17387276-Reviews-OYO_294_Andrea_Residence_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Andrea / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (92, 92, 4, 'Hotel Grand Antares', 'hotel-grand-antares-92', 3.1, 60, 'Tidak', 'honestkaro', 3, '22 Okt 2018', 'Standard', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2182288-Reviews-Hotel_Grand_Antares-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 328, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (93, 93, 4, 'Deli Hotel', 'deli-hotel-93', 4.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d16856399-Reviews-Deli_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. H. Adam Malik No. 5, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (94, 94, 4, 'Hotel Putra Mulia', 'hotel-putra-mulia-94', 3.4, 19, 'Tidak', 'MukhtarHadi', 3, '6 Nov 2017', 'Nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21363632-Reviews-Hotel_Putra_Mulia-Medan_North_Sumatra_Sumatra.html', 'Jl. Gatot Subroto No. 62, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (95, 95, 4, 'Hotel Apartemen Huber Medan', 'hotel-apartemen-huber-medan-95', 4.0, 4, 'Tidak', 'Sondang', 1, '19 Okt 2019', 'Huber apartemen medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d24141921-Reviews-Hotel_Apartemen_Huber_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Huber / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (96, 96, 4, 'Sibayak Leuser Hotel', 'sibayak-leuser-hotel-96', 5.0, 2, 'Tidak', 'Mahatmya W', 5, '29 Mar 2017', 'recomended hotel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3482600-Reviews-Sibayak_Leuser_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Pasar Merah No. 10, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (97, 97, 4, 'Hotel Amaliun', 'hotel-amaliun-97', 3.2, 9, 'Ya', 'dyoty a', 3, '15 Feb 2018', 'Pelayanan Ramah, Fasilitas Lain Biasa', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8776717-Reviews-Amaliun_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Amaliun No. 21, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (98, 98, 4, 'MBS HOTEL Medan', 'mbs-hotel-medan-98', 4.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34098622-Reviews-MBS_HOTEL_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 88, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (99, 99, 4, 'Carolina Transit Medan RedPartner', 'carolina-transit-medan-redpartner-99', 3.3, 19, 'Tidak', 'Hadi V', 1, '19 Jul 2016', 'Not Recomended', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d5889082-Reviews-Carolina_Transit_Medan_RedPartner-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 56, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (100, 100, 4, 'Papakoel Pembangunan', 'papakoel-pembangunan-100', 5.0, 1, 'Tidak', 'putra', 5, '2 Mar 2021', 'Memuaskan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21364204-Reviews-Papakoel_Pembangunan-Medan_North_Sumatra_Sumatra.html', 'Jl. Pembangunan No. 14, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (101, 101, 4, 'Pardede International Hotel', 'pardede-international-hotel-101', 3.3, 21, 'Tidak', 'leimah', 3, '2 Des 2019', 'Mudik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1380528-Reviews-Pardede_International_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Ir. H. Juanda No. 14, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (102, 102, 4, 'Bukit Menday Guest House', 'bukit-menday-guest-house-102', 4.5, 2, 'Ya', 'Sarah J', 4, '8 Mar 2017', 'asri', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10446708-Reviews-Bukit_Menday_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Menday / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (103, 103, 4, 'Medan Hostel', 'medan-hostel-103', 4.5, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10711885-Reviews-Medan_Hostel-Medan_North_Sumatra_Sumatra.html', 'Jl. HM Yamin No. 55, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (104, 104, 4, 'House of Zaza Zizi', 'house-of-zaza-zizi-104', 3.2, 44, 'Tidak', 'YunieErwin', 3, '22 Feb 2016', 'Hotel budget', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6023749-Reviews-House_of_Zaza_Zizi-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Batang Hari No. 66, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (105, 105, 4, 'Ayahanda Residence Syariah', 'ayahanda-residence-syariah-105', 5.0, 1, 'Tidak', 'Roni Agustinus', 5, '27 Sep 2019', 'Kos dengan fasilitas lengkap seperti di Hotel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13231421-Reviews-Ayahanda_Residence_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Ayahanda No. 28, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (106, 106, 4, 'Hotel candi', 'hotel-candi-106', 3.3, 21, 'Tidak', 'safiranh', 3, '28 Jul 2018', 'Cocok untuk mahasiswa', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3170355-Reviews-Candi_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Candi Borobudur No. 4, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (107, 107, 4, 'RedDoorz Near Hermes Place Polonia Medan', 'reddoorz-near-hermes-place-polonia-medan-107', 4.0, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d15037913-Reviews-RedDoorz_Near_Hermes_Place_Polonia_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Mongonsidi No. 45, Kec. Medan Polonia', 'Medan Polonia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (108, 108, 4, 'Marco''s Inn', 'marco-s-inn-108', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13417476-Reviews-Marco_s_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Multatuli No. 12, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (109, 109, 4, 'Ben House', 'ben-house-109', 5.0, 1, 'Tidak', 'Grafitasi N', 5, '11 Jun 2018', 'Hunian yang aman, nyaman dan berkelas di Medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13848427-Reviews-Ben_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Besitang No. 8, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (110, 110, 4, 'Sofyan Hotel Saka Medan', 'sofyan-hotel-saka-medan-110', 3.5, 2, 'Tidak', 'hara', 3, '20 Mar 2023', 'Ulasan hotel', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23207039-Reviews-Sofyan_Hotel_Saka_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Gagak Hitam / Ring Road No. 14, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (111, 111, 4, 'Sukma City Hotel', 'sukma-city-hotel-111', 3.8, 4, 'Tidak', 'Jack B', 4, '11 Jan 2016', 'murah dan dipusat kota', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3841380-Reviews-Sukma_City_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sukmul / SM Raja No. 30, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (112, 112, 4, 'Aceh House Hotel Islami Setiabudi', 'aceh-house-hotel-islami-setiabudi-112', 4.0, 2, 'Tidak', 'yoseptian suheri', 4, '24 Agu 2016', 'Bagus dan cocok dengan harga', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6206391-Reviews-Aceh_House_Hotel_Islami_Setiabudi-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 102, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (113, 113, 4, 'Sei Arakundo Residence', 'sei-arakundo-residence-113', 5.0, 1, 'Tidak', 'yusmantara', 5, '5 Des 2015', 'Inde kost elite di medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6828119-Reviews-Sei_Arakundo_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Arakundo No. 18, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (114, 114, 4, 'Roemah Moesi', 'roemah-moesi-114', 3.7, 3, 'Tidak', 'imelda n', 4, '25 Mar 2015', 'TEMPAT KOS YANG NYAMAN', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3168972-Reviews-Roemah_Moesi-Medan_North_Sumatra_Sumatra.html', 'Jl. Musi No. 8, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (115, 115, 4, 'Grand Istana Indonesia', 'grand-istana-indonesia-115', 5.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3189550-Reviews-Grand_Istana_Indonesia-Medan_North_Sumatra_Sumatra.html', 'Jl. Istana No. 5, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (116, 116, 4, 'Aresidencia - Sei Padang', 'aresidencia-sei-padang-116', 4.0, 1, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d15596417-Reviews-Aresidencia_Sei_Padang-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Padang No. 42, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (117, 117, 4, 'Grand Kanaya Hotel', 'grand-kanaya-hotel-117', 3.1, 52, 'Tidak', 'Angga Saputra Sinaga', 3, '4 Jul 2019', 'Cukup ok', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3464290-Reviews-Grand_Kanaya_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Darussalam No. 12, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (118, 118, 4, 'Ibunda Hotel', 'ibunda-hotel-118', 3.2, 6, 'Tidak', 'AaDaniBadra', 3, '7 Nov 2015', 'Hotel Murah di tengah kota Medan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6851041-Reviews-Ibunda_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 11, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (119, 119, 4, 'Griya Hotel Medan', 'griya-hotel-medan-119', 3.0, 4, 'Tidak', 'suwandytanjung', 3, '28 Jan 2015', 'breakfastnya seperti hotel berbintang', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3316305-Reviews-Griya_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Ring Road / Gagak Hitam No. 128, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (120, 120, 4, 'Raz Hotel & Convention', 'raz-hotel-convention-120', 2.8, 13, 'Tidak', 'salqaura', 3, '25 Nov 2016', 'TOILETNYA KURANG MAINTANANCE,Gak ada air kerannya 😔😔', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6840794-Reviews-Raz_Hotel_Convention-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 186, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (121, 121, 5, 'LARIZ Depari Hotel', 'lariz-depari-hotel-121', 3.3, 6, 'Ya', 'ferry_le', 3, '14 Jul 2019', 'Terlalu mahal (Tidak ada sarapan)', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d14920092-Reviews-LARIZ_Depari_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Ring Road / Gagak Hitam No. 9, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (122, 122, 5, 'Cemara Asri INN', 'cemara-asri-inn-122', 3.5, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10232691-Reviews-Cemara_Asri_INN-Medan_North_Sumatra_Sumatra.html', 'Komplek Cemara Asri, Kec. Percut Sei Tuan', 'Percut Sei Tuan', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (123, 123, 5, 'Kailani Inn', 'kailani-inn-123', 4.0, 1, 'Tidak', 'eko k', 4, '29 Mei 2017', 'Brand Hotel Lokal Rasa Nasional', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d4106308-Reviews-Kailani_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Kailani / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (124, 124, 5, 'Hotel O tel Transit Syariah', 'hotel-o-tel-transit-syariah-124', 4.0, 1, 'Tidak', 'Ecolodge Bukit Lawang', 4, '19 Mar 2017', 'Pelayanan Baik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8789624-Reviews-Hotel_O_tel_Transit_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Akses Bandara Kualanamu, Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (125, 125, 5, 'Amir Hamzah Residence 123', 'amir-hamzah-residence-123-125', 4.0, 1, 'Tidak', 'Masageng Satrio S', 4, '7 Des 2016', 'Medan Trip', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11871055-Reviews-Amir_Hamzah_Residence_123-Medan_North_Sumatra_Sumatra.html', 'Jl. T. Amir Hamzah No. 123, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (126, 126, 5, 'Wesley House', 'wesley-house-126', 4.0, 1, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d9738761-Reviews-Wesley_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Wesley No. 3, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (127, 127, 5, 'Cherry Pink Hotel', 'cherry-pink-hotel-127', 3.5, 2, 'Tidak', 'BartosJr', 3, '15 Jul 2016', 'Pengalaman tak terlupakan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2512214-Reviews-Cherry_Pink_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Green Pink / KH Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (128, 128, 5, 'Semarak International Hotel', 'semarak-international-hotel-128', 4.0, 1, 'Tidak', 'Lisa Tanoko', 4, '25 Agu 2015', 'Nyaman dan tenang', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3170434-Reviews-Semarak_International_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 44, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (129, 129, 5, 'Hotel Al''Jayri', 'hotel-al-jayri-129', 4.0, 1, 'Tidak', 'klub_tgvc', 4, '8 Agu 2015', 'Hotel islami yang nyaman untuk keluarga dan pribadi', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6023598-Reviews-Hotel_Al_Jayri-Medan_North_Sumatra_Sumatra.html', 'Jl. Al''Jayri / HM Yamin, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (130, 130, 5, 'Guesthouse JJ Medan', 'guesthouse-jj-medan-130', 4.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3529773-Reviews-Guesthouse_JJ_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. JJ / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (131, 131, 5, 'Yap Hostel', 'yap-hostel-131', 3.3, 4, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8317489-Reviews-Yap_Hostel-Medan_North_Sumatra_Sumatra.html', 'Jl. Yap / Asia No. 15, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (132, 132, 5, 'The Sulthan Darussalam Medan', 'the-sulthan-darussalam-medan-132', 3.0, 6, 'Tidak', 'Theyo2n', 3, '3 Agu 2016', 'Hotel Murah dan Lokasi yang Strategis', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3219711-Reviews-The_Sulthan_Darussalam_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Darussalam No. 14, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (133, 133, 5, 'Wisma HM Jhoni', 'wisma-hm-jhoni-133', 3.5, 2, 'Tidak', 'Lisa Tanoko', 5, '24 Agu 2015', 'Perfect place for long stay', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3592792-Reviews-Wisma_HM_Jhoni-Medan_North_Sumatra_Sumatra.html', 'Jl. HM Jhoni No. 33, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (134, 134, 5, 'OYO 436 Raz Residence', 'oyo-436-raz-residence-134', 3.3, 3, 'Tidak', 'Visitor93', 1, '5 Sep 2022', 'KECEWA', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17392485-Reviews-OYO_436_Raz_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 188, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (135, 135, 5, 'Bambu River Guesthouse Tangkahan', 'bambu-river-guesthouse-tangkahan-135', 3.8, 5, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3331725-Reviews-Bambu_River_Guesthouse_Tangkahan-Medan_North_Sumatra_Sumatra.html', 'Kawasan Tangkahan, Kec. Batang Serangan', 'Batang Serangan', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (136, 136, 5, 'Cherry Garden Hotel', 'cherry-garden-hotel-136', 3.0, 3, 'Tidak', 'nas30041982', 4, '18 Jun 2012', 'hotel murah ramah famillyar', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2437100-Reviews-Cherry_Garden_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Cherry / KH Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (137, 137, 5, 'Cherry Green Hotel', 'cherry-green-hotel-137', 3.0, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1935386-Reviews-Cherry_Green_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Green / Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (138, 138, 5, 'OYO 291 Medan City Syariah', 'oyo-291-medan-city-syariah-138', 3.0, 2, 'Tidak', 'Ismayadi', 3, '11 Jun 2019', 'Hotel murah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17387262-Reviews-OYO_291_Medan_City_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 70, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (139, 139, 5, 'Hotel Medan Metro', 'hotel-medan-metro-139', 3.0, 2, 'Tidak', 'Denny W', 3, '14 Sep 2017', 'Hotel hemat', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d583113-Reviews-Hotel_Medan_Metro-Medan_North_Sumatra_Sumatra.html', 'Jl. HM Yamin No. 22, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (140, 140, 5, 'Swasana Hotel Medan', 'swasana-hotel-medan-140', 3.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34085096-Reviews-Swasana_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Krakatau No. 72, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (141, 141, 5, 'Paddington Homestay', 'paddington-homestay-141', 3.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12580120-Reviews-Paddington_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Paddington / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (142, 142, 5, 'Wisata Hotel', 'wisata-hotel-142', 3.0, 1, 'Tidak', 'nanang b', 3, '13 Apr 2016', 'sangat bagus', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3483457-Reviews-OYO_1752_Hotel_Wisata-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 100, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (143, 143, 5, 'Oase Guesthouse', 'oase-guesthouse-143', 3.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12520784-Reviews-Oase_Guesthouse-Medan_North_Sumatra_Sumatra.html', 'Jl. Oase / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (144, 144, 5, 'Zaza Zizi House Tanjung Sari', 'zaza-zizi-house-tanjung-sari-144', 3.0, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6684176-Reviews-Zaza_Zizi_House_Tanjung_Sari-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi / Tanjung Sari, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (145, 145, 5, 'Royyan Inn-Backpacker Hostel-Kno Kualanamu', 'royyan-inn-backpacker-hostel-kno-kualanamu-145', 3.0, 2, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11994167-Reviews-Royyan_Inn_Backpacker_Hostel_Kno_Kualanamu-Medan_North_Sumatra_Sumatra.html', 'Jl. Akses Bandara Kualanamu, Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (146, 146, 5, 'Soechi International Hotel', 'soechi-international-hotel-146', 3.0, 260, 'Ya', 'Rebecca S', 3, '2 Nov 2018', 'Kurang ramah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d307990-Reviews-Soechi_International_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Cirebon No. 76A, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (147, 147, 5, 'ZEN Rooms K Hotel', 'zen-rooms-k-hotel-147', 3.0, 3, 'Tidak', 'Maruli Sirait', 1, '16 Jun 2019', 'Mengecewakan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12986750-Reviews-ZEN_Rooms_K_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. K / Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (148, 148, 5, 'Hotel Melati', 'hotel-melati-148', 3.0, 5, 'Ya', 'Zulfikar M', 4, '15 Agu 2018', 'Ya lumayan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d10754835-Reviews-Hotel_Melati-Medan_North_Sumatra_Sumatra.html', 'Jl. Melati / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (149, 149, 5, '511 Ideal Stay Hotel', '511-ideal-stay-hotel-149', 2.8, 5, 'Tidak', 'Lim Hendra', 4, '25 Mei 2015', 'Salah satu pilihan terbaik untuk biaya rendah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d4885330-Reviews-511_Ideal_Stay_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Ideal / HM Yamin, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (150, 150, 5, 'Hotel Citi International Palang Merah', 'hotel-citi-international-palang-merah-150', 2.9, 19, 'Ya', 'Dwik Saragih', 3, '5 Agu 2014', 'Budget and Service Serba Pas', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1218327-Reviews-Hotel_Citi_International_Palang_Merah-Medan_North_Sumatra_Sumatra.html', 'Jl. Palang Merah No. 15, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (151, 151, 6, 'Super Oyo 2574 Z Suites Hotel', 'super-oyo-2574-z-suites-hotel-151', 3.0, 13, 'Tidak', 'arieftrum', 3, '26 Des 2016', 'Strategis', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6997897-Reviews-Super_Oyo_2574_Z_Suites_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Z / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (152, 152, 6, 'Alpha Inn', 'alpha-inn-152', 2.8, 38, 'Tidak', '480fandyr', 3, '2 Apr 2017', 'kamarnya kecil', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1952971-Reviews-Alpha_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Gatot Subroto No. 148, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (153, 153, 6, 'The Crew Hotel', 'the-crew-hotel-153', 2.9, 66, 'Ya', 'Sandersonthe', 3, '11 Feb 2018', 'Kamar Tidak Ada Jendela.', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d8814701-Reviews-The_Crew_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Crew / Bandara Kualanamu, Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (154, 154, 6, 'Grand Lubuk Raya Hotel', 'grand-lubuk-raya-hotel-154', 2.9, 7, 'Tidak', '777Hebat', 3, '11 Jun 2014', 'fasilitas kurang', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6619343-Reviews-Grand_Lubuk_Raya_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Lubuk Raya / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (155, 155, 6, 'Hotel Syariah Aceh House Gajah Mada', 'hotel-syariah-aceh-house-gajah-mada-155', 2.5, 2, 'Tidak', 'Dizzman', 3, '4 Okt 2016', 'Hotel Dekat Ucok Durian', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3514789-Reviews-Hotel_Syariah_Aceh_House_Gajah_Mada-Medan_North_Sumatra_Sumatra.html', 'Jl. Gajah Mada No. 35, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (156, 156, 6, 'Siba Island Resort', 'siba-island-resort-156', 2.0, 1, 'Ya', 'Dita A', 2, '13 Apr 2015', 'Biasa', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3783850-Reviews-Siba_Island_Resort-Medan_North_Sumatra_Sumatra.html', 'Pulau Siba, Hamparan Perak, Deli Serdang', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (157, 157, 6, 'The Labana Inn Hotel', 'the-labana-inn-hotel-157', 2.0, 1, 'Tidak', 'differensiasi', 2, '31 Jul 2016', 'Drop of our guest', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3247040-Reviews-The_Labana_Inn_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Labana / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (158, 158, 6, 'OYO 1244 Wisma Syari''ah Aceh House', 'oyo-1244-wisma-syari-ah-aceh-house-158', 2.5, 2, 'Tidak', 'Ecolodge Bukit Lawang', 2, '6 Agu 2018', 'Kurang Perawatan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3514942-Reviews-OYO_1244_Wisma_Syari_ah_Aceh_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 104, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (159, 159, 6, 'OYO 1030 Hotel Bumi Asih', 'oyo-1030-hotel-bumi-asih-159', 2.5, 4, 'Tidak', 'Angga Pratama', 3, '19 Feb 2017', 'Cuma buat tidur semalam okelah..', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2516465-Reviews-OYO_1030_Hotel_Bumi_Asih-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 120, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (160, 160, 6, 'J Hotel Medan', 'j-hotel-medan-160', 2.8, 17, 'Tidak', 'tamandoy', 3, '22 Agu 2018', 'Murah ditengah kota', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d5884769-Reviews-J_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. J / Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (161, 161, 6, 'Hotel Grand Darussalam', 'hotel-grand-darussalam-161', 2.7, 3, 'Tidak', 'Misba H', 3, '11 Des 2016', 'Masih banyak yg perlu diperbaiki', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3369917-Reviews-Hotel_Grand_Darussalam-Medan_North_Sumatra_Sumatra.html', 'Jl. Darussalam No. 30, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (162, 162, 6, 'Hotel Menara Lexus', 'hotel-menara-lexus-162', 2.9, 14, 'Ya', 'Kenjo S', 3, '7 Jul 2018', 'Lumayan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3914136-Reviews-Hotel_Menara_Lexus-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 200, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (163, 163, 6, 'Garuda Plaza Hotel', 'garuda-plaza-hotel-163', 2.9, 170, 'Ya', 'Rebecca S', 3, '23 Nov 2018', 'Ga banget', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1207175-Reviews-Garuda_Plaza_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sisingamangaraja No. 18, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (164, 164, 6, 'Green Elite', 'green-elite-164', 2.0, 1, 'Tidak', 'TiraUllyviaTanita', 2, '10 Mar 2020', 'Tempat yang bagus, tapi...', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19219538-Reviews-Green_Elite-Medan_North_Sumatra_Sumatra.html', 'Jl. Green / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (165, 165, 6, 'SZ Nine One', 'sz-nine-one-165', 2.8, 4, 'Ya', 'Grasella', 3, '26 Mar 2019', 'There is no room key', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d13932028-Reviews-SZ_Nine_One-Medan_North_Sumatra_Sumatra.html', 'Jl. Nine / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (166, 166, 6, 'Graha Kardopa Hotel', 'graha-kardopa-hotel-166', 2.6, 5, 'Tidak', 'johan_a_saleh', 3, '29 Mar 2014', 'Hotel paling bagus di Binjai', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d6279943-Reviews-Graha_Kardopa_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sultan Hasanuddin No. 8, Binjai / Medan', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (167, 167, 6, 'Hotel Dhaksina', 'hotel-dhaksina-167', 2.2, 5, 'Tidak', 'truesin', 2, '23 Jun 2010', 'Cari tempat lain...', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1218328-Reviews-Hotel_Dhaksina-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 15, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (168, 168, 6, 'HL Days Inn', 'hl-days-inn-168', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2511925-Reviews-HL_Days_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Days / HM Yamin, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (169, 169, 6, 'OYO 1947 Woong Rame Resort & Restaurant', 'oyo-1947-woong-rame-resort-restaurant-169', 2.3, 3, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d11776605-Reviews-OYO_1947_Woong_Rame_Resort_Restaurant-Medan_North_Sumatra_Sumatra.html', 'Kawasan Pantai Woong Rame, Serdang Bedagai', NULL, '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (170, 170, 6, 'Maradona Guest House', 'maradona-guest-house-170', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d12684372-Reviews-Maradona_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Maradona / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (171, 171, 6, 'Cherry Red Hotel', 'cherry-red-hotel-171', 1.7, 3, 'Tidak', 'AGUS S', 3, '20 Sep 2015', 'Hotel yang unik', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d2512215-Reviews-Cherry_Red_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Red / KH Wahid Hasyim, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (172, 172, 6, 'OYO Flagship 1348 Hotel Home Anaya', 'oyo-flagship-1348-hotel-home-anaya-172', 2.9, 26, 'Tidak', 'Faisal,SKM', 3, '4 Jan 2020', 'Hotel dengan Pelayanan yang biasa saja', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3360560-Reviews-OYO_Flagship_1348_Hotel_Home_Anaya-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 138, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (173, 173, 6, 'Hotel 61 Medan', 'hotel-61-medan-173', 2.7, 16, 'Tidak', 'Kurniawan N', 3, '13 Jan 2016', 'minimalis dan bising', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3222897-Reviews-Hotel_61_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. H. Adam Malik No. 61, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (174, 174, 6, 'Wisma Syailendra', 'wisma-syailendra-174', 1.0, 1, 'Tidak', 'Mr.travelanyo', 1, '21 Feb 2020', 'TIDAK NYAMAN!!!', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3592784-Reviews-Wisma_Syailendra-Medan_North_Sumatra_Sumatra.html', 'Jl. Syailendra No. 10, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (175, 175, 6, 'OYO 1507 Doriyu Homestay', 'oyo-1507-doriyu-homestay-175', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19602784-Reviews-OYO_1507_Doriyu_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Doriyu / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (176, 176, 6, 'RedDoorz At Ralica Grand Palladium Medan', 'reddoorz-at-ralica-grand-palladium-medan-176', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21301899-Reviews-RedDoorz_At_Ralica_Grand_Palladium_Medan-Medan_North_Sumatra_Sumatra.html', 'Mall Grand Palladium, Jl. Kapten Maulana Lubis, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (177, 177, 6, 'RedDoorz Plus @ Centrium Katamso Medan', 'reddoorz-plus-centrium-katamso-medan-177', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21290504-Reviews-RedDoorz_Plus_Centrium_Katamso_Medan-Medan_North_Sumatra_Sumatra.html', 'Komplek Centrium, Jl. Brigjend Katamso, Kec. Medan Maimun', 'Medan Maimun', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (178, 178, 6, 'OYO 621 Vania Residence', 'oyo-621-vania-residence-178', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d18428715-Reviews-OYO_621_Vania_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Vania / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (179, 179, 6, 'RedDoorz Plus @ Jalan Gatot Subroto Medan', 'reddoorz-plus-jalan-gatot-subroto-medan-179', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21287205-Reviews-RedDoorz_Plus_Jalan_Gatot_Subroto_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Gatot Subroto No. 180, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (180, 180, 6, 'Hotel O Bakti Homestay', 'hotel-o-bakti-homestay-180', 1.0, 1, 'Tidak', 'Explore28675668237', 1, '27 Mei 2023', 'Kesal dengan pelayan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17673281-Reviews-Hotel_O_Bakti_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Bakti No. 15, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (181, 181, 7, 'Saka Hotel Medan', 'saka-hotel-medan-181', 2.6, 15, 'Ya', 'MukhtarHadi', 3, '26 Des 2017', 'Sedikit mengecewakan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d4046701-Reviews-Saka_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Gagak Hitam / Ring Road No. 14, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (182, 182, 7, 'De''Tonga Hotel', 'de-tonga-hotel-182', 1.0, 2, 'Tidak', 'Break Free', 1, '6 Agu 2021', 'mengerikan', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20554530-Reviews-De_Tonga_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Batang Hari No. 40, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (183, 183, 7, 'Hotel O Krakatau Near Universitas Muhammadiya Sumatra Utara ( UMSU )', 'hotel-o-krakatau-near-universitas-muhammadiya-sumatra-utara-umsu-183', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33117051-Reviews-Hotel_O_Krakatau_Near_Universitas_Muhammadiya_Sumatra_Utara_UMSU-Medan_North_Sumatra_.html', 'Jl. Krakatau Near UMSU, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (184, 184, 7, 'Vosstel Guest House', 'vosstel-guest-house-184', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17387297-Reviews-Vosstel_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Vosstel / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (185, 185, 7, 'Hotel O Festive Inn', 'hotel-o-festive-inn-185', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21351598-Reviews-Hotel_O_Festive_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Festive / HM Yamin, Kec. Medan Timur', 'Medan Timur', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (186, 186, 7, 'Hotel O Onasis Syariah Inn', 'hotel-o-onasis-syariah-inn-186', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26443761-Reviews-Hotel_O_Onasis_Syariah_Inn-Medan_North_Sumatra_Sumatra.html', 'Jl. Onasis / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (187, 187, 7, 'RedDoorz near RS Royal Prima Medan 2', 'reddoorz-near-rs-royal-prima-medan-2-187', 1.0, 1, 'Tidak', 'Raja', 1, '1 Agu 2024', 'Penilaian saat saya menginap', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23341598-Reviews-RedDoorz_near_RS_Royal_Prima_Medan_2-Medan_North_Sumatra_Sumatra.html', 'Jl. Ayahanda Near RS Royal Prima, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (188, 188, 7, 'Grand Sirao Hotel', 'grand-sirao-hotel-188', 2.4, 25, 'Tidak', 'tamandoy', 2, '6 Mar 2016', 'Hotel Lama', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d3478270-Reviews-Grand_Sirao_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Sirao / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (189, 189, 7, 'Gurusinga Guesthouse', 'gurusinga-guesthouse-189', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21409729-Reviews-Gurusinga_Guesthouse-Medan_North_Sumatra_Sumatra.html', 'Jl. Gurusinga / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (190, 190, 7, 'Hotel Garuda Citra', 'hotel-garuda-citra-190', 2.2, 11, 'Tidak', 'Andika Bhayangkara', 2, '23 Mar 2019', 'Harus berubah', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1463837-Reviews-Hotel_Garuda_Citra-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 28, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (191, 191, 7, 'Hotel O Ratu Residence', 'hotel-o-ratu-residence-191', 1.0, 1, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21378064-Reviews-Hotel_O_Ratu_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Ratu / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (192, 192, 7, 'RedDoorz @ Helvetia Medan 2', 'reddoorz-helvetia-medan-2-192', 1.0, 1, 'Tidak', 'Inspire08313714549', 1, '11 Feb 2026', 'Hotel tidak nyaman', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21287275-Reviews-RedDoorz_Helvetia_Medan_2-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Muslim / Helvetia, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (193, 193, 7, 'Urbanview Os Residence Medan By RedDoorz', 'urbanview-os-residence-medan-by-reddoorz-193', 1.0, 1, 'Tidak', 'Wira S', 1, '6 Jul 2026', 'Pengalaman mentraumakan menginap di penginapan ini,dengan pelayanan yang sangat buruk', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33101287-Reviews-Urbanview_Os_Residence_Medan_By_RedDoorz-Medan_North_Sumatra_Sumatra.html', 'Jl. Os / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (194, 194, 7, 'OYO 687 Residence Hotel Syariah', 'oyo-687-residence-hotel-syariah-194', 2.5, 62, 'Tidak', 'gmanik2366', 3, '21 Sep 2019', 'Tempat Ngadem', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1959450-Reviews-OYO_687_Residence_Hotel_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 90, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (195, 195, 7, 'Pondok Wisata Angel', 'pondok-wisata-angel-195', 2.2, 69, 'Tidak', 'SimplyVeva', 2, '2 Apr 2014', 'Cari Tempat Lain', 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d1950224-Reviews-Pondok_Wisata_Angel-Medan_North_Sumatra_Sumatra.html', 'Jl. Angel / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (196, 196, 7, 'RedDoorz @ Sekip Medan', 'reddoorz-sekip-medan-196', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23472273-Reviews-RedDoorz_Sekip_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Sekip No. 25, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (197, 197, 7, 'Odua Golden Mansyur Medan', 'odua-golden-mansyur-medan-197', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26528103-Reviews-Odua_Golden_Mansyur_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Dr. Mansyur No. 50, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (198, 198, 7, 'OYO 2940 Papakoel Guest House', 'oyo-2940-papakoel-guest-house-198', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20347086-Reviews-OYO_2940_Papakoel_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Pembangunan / Papakoel, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (199, 199, 7, 'Cordex Hotel Medan', 'cordex-hotel-medan-199', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26355154-Reviews-Cordex_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Cordex / Kapten Muslim, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (200, 200, 7, 'Spot On 93506 Kamboja Homestay Syariah', 'spot-on-93506-kamboja-homestay-syariah-200', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d27739935-Reviews-Spot_On_93506_Kamboja_Homestay_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Kamboja / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (201, 201, 7, 'OYO 1377 Os Residence', 'oyo-1377-os-residence-201', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19459507-Reviews-OYO_1377_Os_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Os / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (202, 202, 7, 'Hotel O Royal Guest House', 'hotel-o-royal-guest-house-202', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d18435154-Reviews-Hotel_O_Royal_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Royal / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (203, 203, 7, 'Sapo Bengkurung Kost', 'sapo-bengkurung-kost-203', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17199167-Reviews-Sapo_Bengkurung_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Bengkurung / Padang Bulan, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (204, 204, 7, 'Doriyu Homestay', 'doriyu-homestay-204', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23798713-Reviews-Doriyu_Homestay-Medan_North_Sumatra_Sumatra.html', 'Jl. Doriyu / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (205, 205, 7, 'Rail Transit Hotel Medan', 'rail-transit-hotel-medan-205', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34400502-Reviews-Rail_Transit_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Stasiun Kereta Api Medan, Jl. Stasiun No. 1, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (206, 206, 7, 'Aresidencia Guest House', 'aresidencia-guest-house-206', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20888688-Reviews-Aresidencia_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Padang No. 44, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (207, 207, 7, 'OYO 90338 Rumah Yasmin Syariah', 'oyo-90338-rumah-yasmin-syariah-207', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23341243-Reviews-OYO_90338_Rumah_Yasmin_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Yasmin / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (208, 208, 7, 'OYO 1009 Hotel Bumi Malaya', 'oyo-1009-hotel-bumi-malaya-208', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d17301574-Reviews-OYO_1009_Hotel_Bumi_Malaya-Medan_North_Sumatra_Sumatra.html', 'Jl. SM Raja No. 110, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (209, 209, 7, 'Leuser Park Batu Kapal Camping Ground', 'leuser-park-batu-kapal-camping-ground-209', NULL, NULL, 'Ya', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d4171773-Reviews-Leuser_Park_Batu_Kapal_Camping_Ground-Medan_North_Sumatra_Sumatra.html', 'Batu Kapal, Tangkahan, Kec. Batang Serangan', 'Batang Serangan', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (210, 210, 7, 'OYO 92341 Oetama Syariah', 'oyo-92341-oetama-syariah-210', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d26855004-Reviews-OYO_92341_Oetama_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Oetama / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (211, 211, 8, 'RedDoorz @ Teratai Homestay Medan Amplas', 'reddoorz-teratai-homestay-medan-amplas-211', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34282688-Reviews-RedDoorz_Teratai_Homestay_Medan_Amplas-Medan_North_Sumatra_Sumatra.html', 'Jl. Teratai / Amplas, Kec. Medan Amplas', 'Medan Amplas', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (212, 212, 8, 'Reddoorz Near Institut Kesehatan Helvetia', 'reddoorz-near-institut-kesehatan-helvetia-212', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21367593-Reviews-Reddoorz_Near_Institut_Kesehatan_Helvetia-Medan_North_Sumatra_Sumatra.html', 'Jl. Kapten Muslim Near Helvetia, Kec. Medan Helvetia', 'Medan Helvetia', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (213, 213, 8, 'Airy Eco Syariah Medan Sunggal Sei Batang Hari 67', 'airy-eco-syariah-medan-sunggal-sei-batang-hari-67-213', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20928035-Reviews-Airy_Eco_Syariah_Medan_Sunggal_Sei_Batang_Hari_67-Medan_North_Sumatra_Sumatra.html', 'Jl. Sei Batang Hari No. 67, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (214, 214, 8, 'Sapadia Syailendra', 'sapadia-syailendra-214', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33402552-Reviews-Sapadia_Syailendra-Medan_North_Sumatra_Sumatra.html', 'Jl. Syailendra No. 12, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (215, 215, 8, 'Oyo 93066 D''niel Homestay Syariah', 'oyo-93066-d-niel-homestay-syariah-215', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d27431940-Reviews-Oyo_93066_D_niel_Homestay_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. D''niel / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (216, 216, 8, 'OYO 91234 Aladinmar 2', 'oyo-91234-aladinmar-2-216', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25214027-Reviews-OYO_91234_Aladinmar_2-Medan_North_Sumatra_Sumatra.html', 'Jl. Aladinmar / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (217, 217, 8, 'Hotel Citi International Sunyatsen', 'hotel-citi-international-sunyatsen-217', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d23264103-Reviews-Hotel_Citi_International_Sunyatsen-Medan_North_Sumatra_Sumatra.html', 'Jl. Sun Yat Sen No. 77, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (218, 218, 8, 'Green Forest Hotel by ZUZU', 'green-forest-hotel-by-zuzu-218', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34017898-Reviews-Green_Forest_Hotel_by_ZUZU-Medan_North_Sumatra_Sumatra.html', 'Jl. Green Forest / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (219, 219, 8, 'OYO 1763 Dinda Residence', 'oyo-1763-dinda-residence-219', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19459291-Reviews-OYO_1763_Dinda_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Dinda / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (220, 220, 8, 'Hotel O Royal Guest House', 'hotel-o-royal-guest-house-220', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34584501-Reviews-Hotel_O_Royal_Guest_House-Medan_North_Sumatra_Sumatra.html', 'Jl. Royal / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (221, 221, 8, 'Airy Syariah Medan Sunggal Setiabudi 57', 'airy-syariah-medan-sunggal-setiabudi-57-221', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20924127-Reviews-Airy_Syariah_Medan_Sunggal_Setiabudi_57-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 57, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (222, 222, 8, 'Oyo 92881 Nany Ayahanda Syariah', 'oyo-92881-nany-ayahanda-syariah-222', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d27433773-Reviews-Oyo_92881_Nany_Ayahanda_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Ayahanda No. 40, Kec. Medan Petisah', 'Medan Petisah', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (223, 223, 8, 'RedDoorz Syariah At Setiabudi Medan 2', 'reddoorz-syariah-at-setiabudi-medan-2-223', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21303398-Reviews-RedDoorz_Syariah_At_Setiabudi_Medan_2-Medan_North_Sumatra_Sumatra.html', 'Jl. Setia Budi No. 88, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (224, 224, 8, 'OYO 1539 Armita Residence', 'oyo-1539-armita-residence-224', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d19602847-Reviews-OYO_1539_Armita_Residence-Medan_North_Sumatra_Sumatra.html', 'Jl. Armita / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (225, 225, 8, 'Hotel O Fermansio Homestay 2', 'hotel-o-fermansio-homestay-2-225', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d28654050-Reviews-Hotel_O_Fermansio_Homestay_2-Medan_North_Sumatra_Sumatra.html', 'Jl. Fermansio / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (226, 226, 8, 'Adam Malik Guesthouse near Regale ICC Medan RedPartner', 'adam-malik-guesthouse-near-regale-icc-medan-redpartner-226', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d33454161-Reviews-Adam_Malik_Guesthouse_near_Regale_ICC_Medan_RedPartner-Medan_North_Sumatra_Sumatra.html', 'Jl. H. Adam Malik Near Regale ICC, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (227, 227, 8, 'Dancenter Hotel Airis', 'dancenter-hotel-airis-227', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34485437-Reviews-Dancenter_Hotel_Airis-Medan_North_Sumatra_Sumatra.html', 'Jl. Airis / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (228, 228, 8, 'Hotel O near Universitas Sumatera Utara formerly Golden Cempaka Guest House', 'hotel-o-near-universitas-sumatera-utara-formerly-golden-cempaka-guest-house-228', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34570034-Reviews-Hotel_O_near_Universitas_Sumatera_Utara_formerly_Golden_Cempaka_Guest_House-Medan_Nor.html', 'Jl. Golden Cempaka Near USU, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (229, 229, 8, 'RedDoorz Plus @ Jalan Adam Malik Medan', 'reddoorz-plus-jalan-adam-malik-medan-229', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20954766-Reviews-RedDoorz_Plus_Jalan_Adam_Malik_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. H. Adam Malik No. 80, Kec. Medan Barat', 'Medan Barat', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (230, 230, 8, 'Airy Medan Selayang Arteri Ring Road 11', 'airy-medan-selayang-arteri-ring-road-11-230', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d20924971-Reviews-Airy_Medan_Selayang_Arteri_Ring_Road_11-Medan_North_Sumatra_Sumatra.html', 'Jl. Ring Road / Gagak Hitam No. 11, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (231, 231, 8, 'KoolKost Syariah @ Jalan Pasar Baru Medan', 'koolkost-syariah-jalan-pasar-baru-medan-231', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d24985996-Reviews-KoolKost_Syariah_Jalan_Pasar_Baru_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Pasar Baru No. 15, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (232, 232, 8, 'Hotel O Palem Mas Garden', 'hotel-o-palem-mas-garden-232', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34491457-Reviews-Hotel_O_Palem_Mas_Garden-Medan_North_Sumatra_Sumatra.html', 'Jl. Palem Mas / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (233, 233, 8, 'Hotel O Skyview Near Universitas Sumatra Utara ( USU ) Formerly Juma Ciger', 'hotel-o-skyview-near-universitas-sumatra-utara-usu-formerly-juma-ciger-233', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34027915-Reviews-Hotel_O_Skyview_Near_Universitas_Sumatra_Utara_USU_Formerly_Juma_Ciger-Medan_North_Su.html', 'Jl. Juma Ciger Near USU, Kec. Medan Baru', 'Medan Baru', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (234, 234, 8, 'Urbanview Hotel Nisura Homestay Medan', 'urbanview-hotel-nisura-homestay-medan-234', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d25383849-Reviews-Urbanview_Hotel_Nisura_Homestay_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Nisura / Dr. Mansyur, Kec. Medan Selayang', 'Medan Selayang', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (235, 235, 8, 'Acirasa Homestay Medan', 'acirasa-homestay-medan-235', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d28657048-Reviews-Acirasa_Homestay_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. Acirasa / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (236, 236, 8, 'Hotel O Ani Nur Homestay Syariah', 'hotel-o-ani-nur-homestay-syariah-236', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d27740327-Reviews-Hotel_O_Ani_Nur_Homestay_Syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. Ani Nur / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (237, 237, 8, 'King''s Palace Hotel Medan', 'king-s-palace-hotel-medan-237', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21353060-Reviews-King_s_Palace_Hotel_Medan-Medan_North_Sumatra_Sumatra.html', 'Jl. King''s Palace / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (238, 238, 8, 'OYO 553 Gentala Hotel', 'oyo-553-gentala-hotel-238', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34432100-Reviews-OYO_553_Gentala_Hotel-Medan_North_Sumatra_Sumatra.html', 'Jl. Gentala / Setiabudi, Kec. Medan Sunggal', 'Medan Sunggal', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (239, 239, 8, 'RedDoorz Near Universitas Medan Area 2', 'reddoorz-near-universitas-medan-area-2-239', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d21288466-Reviews-RedDoorz_Near_Universitas_Medan_Area_2-Medan_North_Sumatra_Sumatra.html', 'Jl. UMA Area No. 2, Kec. Medan Area', 'Medan Area', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);
INSERT INTO public.hotels VALUES (240, 240, 8, 'De Rucci Hotel syariah', 'de-rucci-hotel-syariah-240', NULL, NULL, 'Tidak', NULL, NULL, NULL, NULL, 'https://www.tripadvisor.co.id/Hotel_Review-g297725-d34403323-Reviews-De_Rucci_Hotel_syariah-Medan_North_Sumatra_Sumatra.html', 'Jl. De Rucci / SM Raja, Kec. Medan Kota', 'Medan Kota', '2026-11-08 17:24:21.612642', NULL, NULL, NULL);


--
-- Data for Name: inquiries; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: kuliner; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: reviews; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.users VALUES (1, 'Admin Web', 'admin@gmail.com', '$2y$12$wK0htCs799TCnDsySr5Mm.usdaNBUfJ0Vn3jkRpLUBXCZ1BUC6D0e', 'admin', '2026-11-09 13:42:41.73193', NULL);


--
-- Name: api_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.api_keys_id_seq', 1, false);


--
-- Name: hotels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.hotels_id_seq', 240, true);


--
-- Name: inquiries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inquiries_id_seq', 1, false);


--
-- Name: kuliner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.kuliner_id_seq', 1, false);


--
-- Name: reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reviews_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: api_keys api_keys_key_hash_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys
    ADD CONSTRAINT api_keys_key_hash_key UNIQUE (key_hash);


--
-- Name: api_keys api_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys
    ADD CONSTRAINT api_keys_pkey PRIMARY KEY (id);


--
-- Name: favorites favorites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_pkey PRIMARY KEY (user_id, hotel_id);


--
-- Name: hotels hotels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hotels
    ADD CONSTRAINT hotels_pkey PRIMARY KEY (id);


--
-- Name: hotels hotels_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hotels
    ADD CONSTRAINT hotels_slug_key UNIQUE (slug);


--
-- Name: inquiries inquiries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inquiries
    ADD CONSTRAINT inquiries_pkey PRIMARY KEY (id);


--
-- Name: kuliner kuliner_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.kuliner
    ADD CONSTRAINT kuliner_pkey PRIMARY KEY (id);


--
-- Name: kuliner kuliner_sumber_sumber_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.kuliner
    ADD CONSTRAINT kuliner_sumber_sumber_id_key UNIQUE (sumber, sumber_id);


--
-- Name: reviews reviews_hotel_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_hotel_id_user_id_key UNIQUE (hotel_id, user_id);


--
-- Name: reviews reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_hotels_kecamatan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_hotels_kecamatan ON public.hotels USING btree (kecamatan);


--
-- Name: idx_hotels_rating; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_hotels_rating ON public.hotels USING btree (rating);


--
-- Name: idx_kuliner_kecamatan; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_kuliner_kecamatan ON public.kuliner USING btree (kecamatan);


--
-- Name: favorites favorites_hotel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_hotel_id_fkey FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE;


--
-- Name: favorites favorites_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: inquiries inquiries_hotel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inquiries
    ADD CONSTRAINT inquiries_hotel_id_fkey FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE;


--
-- Name: inquiries inquiries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inquiries
    ADD CONSTRAINT inquiries_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: reviews reviews_hotel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_hotel_id_fkey FOREIGN KEY (hotel_id) REFERENCES public.hotels(id) ON DELETE CASCADE;


--
-- Name: reviews reviews_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--


