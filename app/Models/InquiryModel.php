<?php

namespace App\Models;

use CodeIgniter\Model;

class InquiryModel extends Model
{
    protected $table         = 'inquiries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['hotel_id', 'user_id', 'nama', 'email', 'telepon', 'tgl_checkin', 'tgl_checkout', 'jumlah_tamu', 'pesan', 'status', 'balasan_admin', 'dibalas_at', 'jawaban'];
    protected $useTimestamps = true;
}
