<?php

namespace App\Controllers\Api;

use App\Models\HotelModel;
use App\Models\ReviewModel;
use CodeIgniter\HTTP\ResponseInterface;

class Reviews extends ApiBase
{
    /**
     * GET /api/reviews?hotel_id=7
     * Menampilkan daftar ulasan hotel tertentu atau semua ulasan terbaru.
     */
    public function index(): ResponseInterface
    {
        $hotelId = (int) $this->request->getGet('hotel_id');
        $m       = new ReviewModel();

        if ($hotelId > 0) {
            $rows = $m->forHotel($hotelId, 50);
        } else {
            $rows = $m->select('reviews.id, reviews.hotel_id, hotels.nama AS hotel, reviews.user_id, reviews.rating, reviews.judul, reviews.isi, reviews.created_at, users.nama AS penulis')
                ->join('hotels', 'hotels.id = reviews.hotel_id')
                ->join('users', 'users.id = reviews.user_id')
                ->orderBy('reviews.created_at', 'DESC')
                ->findAll(50);
        }

        return $this->json([
            'status' => 'ok',
            'total'  => count($rows),
            'data'   => array_map(static fn ($r) => [
                'id'         => (int) $r['id'],
                'hotel_id'   => (int) $r['hotel_id'],
                'hotel'      => $r['hotel'] ?? null,
                'user_id'    => (int) $r['user_id'],
                'penulis'    => $r['penulis'] ?? 'Pengunjung',
                'rating'     => (int) $r['rating'],
                'judul'      => $r['judul'],
                'isi'        => $r['isi'],
                'created_at' => $r['created_at'],
            ], $rows),
        ]);
    }

    /**
     * POST /api/reviews
     * Menerima ulasan baru dari aplikasi client (misal: Web Kuliner).
     * Body JSON: {"hotel_id": 7, "rating": 5, "judul": "...", "isi": "...", "user_id": 1}
     */
    public function create(): ResponseInterface
    {
        $json = $this->request->getJSON(true) ?: [];
        $hotelId = (int) ($json['hotel_id'] ?? 0);
        $rating  = (int) ($json['rating'] ?? 0);
        $judul   = trim((string) ($json['judul'] ?? ''));
        $isi     = trim((string) ($json['isi'] ?? ''));
        $userId  = (int) ($json['user_id'] ?? 2); // default ke user terdaftar bila tidak dikirim

        if ($hotelId <= 0 || ! (new HotelModel())->find($hotelId)) {
            return $this->json(['status' => 'error', 'message' => 'hotel_id tidak valid atau hotel tidak ditemukan.'], 422);
        }

        if ($rating < 1 || $rating > 5) {
            return $this->json(['status' => 'error', 'message' => 'rating harus berupa angka antara 1 sampai 5.'], 422);
        }

        if (mb_strlen($isi) < 5) {
            return $this->json(['status' => 'error', 'message' => 'isi ulasan minimal 5 karakter.'], 422);
        }

        // Pastikan user_id valid di tabel users, jika tidak fallback ke user 2 (atau 1)
        $userAda = db_connect()->table('users')->where('id', $userId)->countAllResults() > 0;
        if (! $userAda) {
            $userId = 2;
        }

        $reviewModel = new ReviewModel();
        $insertData = [
            'hotel_id' => $hotelId,
            'user_id'  => $userId,
            'rating'   => $rating,
            'judul'    => $judul !== '' ? $judul : null,
            'isi'      => $isi,
        ];

        $reviewId = $reviewModel->insert($insertData);
        if (! $reviewId) {
            return $this->json(['status' => 'error', 'message' => 'Gagal menyimpan ulasan.'], 500);
        }

        // Sinkronkan rating dan jumlah ulasan hotel secara dinamis
        (new HotelModel())->sinkronRating($hotelId);

        $saved = $reviewModel->find($reviewId);

        return $this->json([
            'status'  => 'ok',
            'message' => 'Ulasan berhasil ditambahkan.',
            'data'    => [
                'id'         => (int) $saved['id'],
                'hotel_id'   => (int) $saved['hotel_id'],
                'user_id'    => (int) $saved['user_id'],
                'rating'     => (int) $saved['rating'],
                'judul'      => $saved['judul'],
                'isi'        => $saved['isi'],
                'created_at' => $saved['created_at'],
            ],
        ], 201);
    }

    /**
     * DELETE /api/reviews/{id}
     * Menghapus ulasan berdasarkan ID ulasan.
     */
    public function delete(int $id): ResponseInterface
    {
        $m = new ReviewModel();
        $review = $m->find($id);

        if (! $review) {
            return $this->json(['status' => 'error', 'message' => 'Ulasan tidak ditemukan.'], 404);
        }

        $hotelId = (int) $review['hotel_id'];
        $m->delete($id);

        // Hitung ulang rating hotel
        (new HotelModel())->sinkronRating($hotelId);

        return $this->json([
            'status'  => 'ok',
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }
}

