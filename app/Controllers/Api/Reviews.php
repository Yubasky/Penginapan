<?php

namespace App\Controllers\Api;

use App\Models\HotelModel;
use App\Models\ReviewModel;
use CodeIgniter\HTTP\ResponseInterface;

class Reviews extends ApiBase
{
    private static function format(array $r): array
    {
        return [
            'id'         => (int) $r['id'],
            'hotel_id'   => (int) $r['hotel_id'],
            'hotel'      => $r['hotel'] ?? null,
            'user_id'    => (int) $r['user_id'],
            'penulis'    => $r['penulis'] ?? 'Pengunjung',
            'rating'     => (int) $r['rating'],
            'judul'      => $r['judul'],
            'isi'        => $r['isi'],
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
        ];
    }

    /**
     * GET /api/v1/reviews?hotel_id=7
     * Menampilkan daftar ulasan hotel tertentu atau semua ulasan terbaru.
     */
    public function index(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $hotelId = (int) $this->request->getGet('hotel_id');
        $m       = new ReviewModel();

        if ($hotelId > 0) {
            $rows = $m->select('reviews.id, reviews.hotel_id, reviews.user_id, reviews.rating, reviews.judul, reviews.isi, reviews.created_at, reviews.updated_at, users.nama AS penulis')
                ->join('users', 'users.id = reviews.user_id')
                ->where('reviews.hotel_id', $hotelId)
                ->orderBy('reviews.created_at', 'DESC')
                ->findAll(50);
        } else {
            $rows = $m->select('reviews.id, reviews.hotel_id, hotels.nama AS hotel, reviews.user_id, reviews.rating, reviews.judul, reviews.isi, reviews.created_at, reviews.updated_at, users.nama AS penulis')
                ->join('hotels', 'hotels.id = reviews.hotel_id')
                ->join('users', 'users.id = reviews.user_id')
                ->orderBy('reviews.created_at', 'DESC')
                ->findAll(50);
        }

        return $this->json([
            'status' => 'ok',
            'total'  => count($rows),
            'data'   => array_map([self::class, 'format'], $rows),
        ]);
    }

    /**
     * POST /api/v1/reviews
     * Menerima ulasan baru dari aplikasi client.
     */
    public function create(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $json    = $this->request->getJSON(true) ?: [];
        $hotelId = (int) ($json['hotel_id'] ?? 0);
        $rating  = (int) ($json['rating'] ?? 0);
        $judul   = trim((string) ($json['judul'] ?? ''));
        $isi     = trim((string) ($json['isi'] ?? ''));

        if (($this->authActor['type'] ?? '') === 'user' && ! empty($this->authActor['id'])) {
            $userId = (int) $this->authActor['id'];
        } else {
            $userId  = (int) ($json['user_id'] ?? 2);
            $userAda = db_connect()->table('users')->where('id', $userId)->countAllResults() > 0;
            if (! $userAda) {
                $userId = 2;
            }
        }

        if ($hotelId <= 0 || ! (new HotelModel())->find($hotelId)) {
            return $this->json(['status' => 'error', 'message' => 'hotel_id tidak valid atau hotel tidak ditemukan.'], 422);
        }
        if ($rating < 1 || $rating > 5) {
            return $this->json(['status' => 'error', 'message' => 'rating harus berupa angka antara 1 sampai 5.'], 422);
        }
        if (mb_strlen($isi) < 5) {
            return $this->json(['status' => 'error', 'message' => 'isi ulasan minimal 5 karakter.'], 422);
        }

        $db          = db_connect();
        $reviewModel = new ReviewModel();
        $insertData  = [
            'hotel_id' => $hotelId,
            'user_id'  => $userId,
            'rating'   => $rating,
            'judul'    => $judul !== '' ? $judul : null,
            'isi'      => $isi,
        ];

        $db->transBegin();
        try {
            $reviewId = $reviewModel->insert($insertData);
            if (! $reviewId || $db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menyimpan ulasan.'], 500);
            }

            (new HotelModel())->sinkronRating($hotelId);

            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menyinkronkan rating hotel.'], 500);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Reviews::create gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server saat menyimpan ulasan.'], 500);
        }

        return $this->json([
            'status'  => 'ok',
            'message' => 'Ulasan berhasil ditambahkan.',
            'data'    => self::format($reviewModel->find($reviewId)),
        ], 201);
    }

    /**
     * PUT/PATCH /api/v1/reviews/{id}
     * Memperbarui ulasan (hanya pemilik ulasan lewat JWT atau admin; API Key mitra tidak diizinkan).
     */
    public function update(int $id): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $m      = new ReviewModel();
        $review = $m->find($id);

        if (! $review) {
            return $this->json(['status' => 'error', 'message' => 'Ulasan tidak ditemukan.'], 404);
        }

        if ($r = $this->cekHakUbah($review, 'mengubah')) {
            return $r;
        }

        $json = $this->request->getJSON(true) ?: [];

        if (isset($json['updated_at']) && $json['updated_at'] !== '' && ($review['updated_at'] ?? null) !== null) {
            if (trim((string) $json['updated_at']) !== trim((string) $review['updated_at'])) {
                return $this->json([
                    'status'  => 'error',
                    'message' => 'Konflik pembaruan (409): ulasan telah diubah oleh sesi lain. Muat ulang data terbaru.',
                    'data'    => self::format($review),
                ], 409);
            }
        }

        $upd = [];
        if (array_key_exists('rating', $json)) {
            $rating = (int) $json['rating'];
            if ($rating < 1 || $rating > 5) {
                return $this->json(['status' => 'error', 'message' => 'rating harus berupa angka antara 1 sampai 5.'], 422);
            }
            $upd['rating'] = $rating;
        }
        if (array_key_exists('judul', $json)) {
            $judul        = trim((string) $json['judul']);
            $upd['judul'] = $judul !== '' ? $judul : null;
        }
        if (array_key_exists('isi', $json)) {
            $isi = trim((string) $json['isi']);
            if (mb_strlen($isi) < 5) {
                return $this->json(['status' => 'error', 'message' => 'isi ulasan minimal 5 karakter.'], 422);
            }
            $upd['isi'] = $isi;
        }

        if ($upd === []) {
            return $this->json(['status' => 'error', 'message' => 'Tidak ada field yang diperbarui.'], 422);
        }

        $hotelId = (int) $review['hotel_id'];
        $db      = db_connect();
        $db->transBegin();

        try {
            $m->update($id, $upd);
            (new HotelModel())->sinkronRating($hotelId);

            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal memperbarui ulasan.'], 500);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Reviews::update gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server saat memperbarui ulasan.'], 500);
        }

        return $this->json([
            'status'  => 'ok',
            'message' => 'Ulasan berhasil diperbarui.',
            'data'    => self::format($m->find($id)),
        ]);
    }

    /**
     * DELETE /api/v1/reviews/{id}
     * Menghapus ulasan berdasarkan ID ulasan (hanya pemilik ulasan lewat JWT atau admin; API Key mitra tidak diizinkan).
     */
    public function delete(int $id): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $m      = new ReviewModel();
        $review = $m->find($id);

        if (! $review) {
            return $this->json(['status' => 'error', 'message' => 'Ulasan tidak ditemukan.'], 404);
        }

        if ($r = $this->cekHakUbah($review, 'menghapus')) {
            return $r;
        }

        $hotelId = (int) $review['hotel_id'];
        $db      = db_connect();
        $db->transBegin();

        try {
            $m->delete($id);
            (new HotelModel())->sinkronRating($hotelId);

            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menghapus ulasan.'], 500);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Reviews::delete gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server saat menghapus ulasan.'], 500);
        }

        return $this->json([
            'status'  => 'ok',
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }

    private function cekHakUbah(array $review, string $aksi): ?ResponseInterface
    {
        if (! $this->tidakBolehUbahMilik($review)) {
            return null;
        }

        $msg = ($this->authActor['type'] ?? '') === 'partner'
            ? "Akses ditolak (403): API Key mitra tidak berhak {$aksi} ulasan. Gunakan login JWT pemilik ulasan atau admin."
            : "Akses ditolak (403): Anda tidak berhak {$aksi} ulasan pengguna lain.";

        return $this->json(['status' => 'error', 'message' => $msg], 403);
    }

    private function tidakBolehUbahMilik(array $review): bool
    {
        $type = $this->authActor['type'] ?? '';
        $role = $this->authActor['role'] ?? '';

        if ($type === 'user' && $role === 'admin') {
            return false;
        }

        if ($type !== 'user' || empty($this->authActor['id'])) {
            return true;
        }

        return (int) ($review['user_id'] ?? 0) !== (int) $this->authActor['id'];
    }
}
