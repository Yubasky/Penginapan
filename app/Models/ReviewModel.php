<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['hotel_id', 'user_id', 'rating', 'judul', 'isi', 'media'];
    protected $useTimestamps = true;

    /** Ulasan pengunjung untuk satu hotel (terbaru dulu). */
    public function forHotel(int $hotelId, int $limit = 20): array
    {
        return $this->select('reviews.*, users.nama AS penulis')
            ->join('users', 'users.id = reviews.user_id')
            ->where('reviews.hotel_id', $hotelId)
            ->orderBy('reviews.created_at', 'DESC')
            ->findAll($limit);
    }

    public function ringkasan(int $hotelId): array
    {
        $r = $this->db->table('reviews')
            ->select('COUNT(*) AS jumlah, ROUND(AVG(rating), 1) AS rata')
            ->where('hotel_id', $hotelId)->get()->getRowArray();

        return ['jumlah' => (int) $r['jumlah'], 'rata' => $r['rata'] !== null ? (float) $r['rata'] : null];
    }

    /** Ambil semua foto dan video fasilitas dari seluruh ulasan untuk satu hotel. */
    public function mediaForHotel(int $hotelId): array
    {
        $rows = $this->select('reviews.id, reviews.media, reviews.rating, users.nama AS penulis, reviews.created_at')
            ->join('users', 'users.id = reviews.user_id')
            ->where('reviews.hotel_id', $hotelId)
            ->where('reviews.media IS NOT NULL')
            ->orderBy('reviews.created_at', 'DESC')
            ->findAll();

        $items = [];
        foreach ($rows as $r) {
            $mediaList = json_decode($r['media'] ?? '[]', true) ?: [];
            foreach ($mediaList as $m) {
                if (! empty($m['url'])) {
                    $items[] = [
                        'url'       => $m['url'],
                        'type'      => $m['type'] ?? 'image',
                        'penulis'   => $r['penulis'],
                        'rating'    => $r['rating'],
                        'review_id' => $r['id'],
                        'tgl'       => $r['created_at'],
                    ];
                }
            }
        }
        return $items;
    }
}
