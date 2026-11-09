<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['hotel_id', 'user_id', 'rating', 'judul', 'isi'];
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
}
