<?php

namespace App\Controllers\Api;

use App\Models\HotelModel;
use CodeIgniter\HTTP\ResponseInterface;

class Hotels extends ApiBase
{
    public static function format(array $h): array
    {
        return [
            'id'              => (int) $h['id'],
            'nama'            => $h['nama'],
            'slug'            => $h['slug'],
            'kota'            => 'Medan',
            'kecamatan'       => $h['kecamatan'],
            'alamat'          => $h['alamat'],
            'latitude'        => isset($h['latitude']) && $h['latitude'] !== null ? (float) $h['latitude'] : null,
            'longitude'       => isset($h['longitude']) && $h['longitude'] !== null ? (float) $h['longitude'] : null,
            'rating'          => $h['rating'] !== null ? (float) $h['rating'] : null,
            'jumlah_ulasan'   => $h['jumlah_ulasan'] !== null ? (int) $h['jumlah_ulasan'] : null,
            'sarapan'         => $h['sarapan'] ?? null,
            'foto'            => $h['foto'] ?? null,
            'url'             => site_url('penginapan/' . ($h['slug'] ?? $h['id'])),
            'tripadvisor_url' => $h['link_tripadvisor'] ?? null,
            'updated_at'      => $h['updated_at'] ?? null,
            'ulasan_unggulan' => ! empty($h['reviewer']) ? [
                'reviewer' => $h['reviewer'],
                'rating'   => isset($h['rating_ulasan']) && $h['rating_ulasan'] !== null ? (int) $h['rating_ulasan'] : null,
                'tanggal'  => $h['tanggal_ulasan'] ?? null,
                'judul'    => $h['judul_ulasan'] ?? null,
            ] : null,
        ];
    }

    /** GET /api/v1/hotels?q=&kecamatan=&sarapan=ya&rating_min=4&sort=rating|ulasan|nama&page=1&limit=20 */
    public function index(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $m = new HotelModel();
        [$page, $limit] = $this->paging();

        $m->filtered($this->filterDari());
        $total = $m->countAllResults(false);
        $rows  = $m->findAll($limit, ($page - 1) * $limit);

        return $this->json([
            'status' => 'ok',
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => (int) ceil($total / $limit)],
            'data'   => array_map([self::class, 'format'], $rows),
        ]);
    }

    public function show(int $id): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }
        $h = (new HotelModel())->find($id);
        if (! $h) {
            return $this->json(['status' => 'error', 'message' => 'Hotel tidak ditemukan'], 404);
        }

        return $this->json(['status' => 'ok', 'data' => self::format($h)]);
    }

    /** POST /api/v1/hotels — khusus role admin. */
    public function create(): ResponseInterface
    {
        if ($r = $this->butuhRole(['admin'])) {
            return $r;
        }

        $json = $this->request->getJSON(true);
        $in   = is_array($json) ? $json : [];

        $nama      = trim((string) ($in['nama'] ?? ''));
        $kecamatan = trim((string) ($in['kecamatan'] ?? ''));
        $sarapan   = trim((string) ($in['sarapan'] ?? 'Tidak'));
        $alamat    = trim((string) ($in['alamat'] ?? ''));
        $lat       = $in['latitude'] ?? null;
        $lng       = $in['longitude'] ?? null;
        $foto      = trim((string) ($in['foto'] ?? ''));

        if (mb_strlen($nama) < 3) {
            return $this->json(['status' => 'error', 'message' => 'nama hotel minimal 3 karakter.'], 422);
        }
        if ($kecamatan === '') {
            return $this->json(['status' => 'error', 'message' => 'kecamatan wajib diisi.'], 422);
        }
        if (! in_array($sarapan, ['Tidak', 'Ya', 'All-in'], true)) {
            return $this->json(['status' => 'error', 'message' => 'sarapan harus bernilai Tidak, Ya, atau All-in.'], 422);
        }
        if ($lat !== null && (! is_numeric($lat) || (float) $lat < -90 || (float) $lat > 90)) {
            return $this->json(['status' => 'error', 'message' => 'latitude harus angka antara -90 dan 90.'], 422);
        }
        if ($lng !== null && (! is_numeric($lng) || (float) $lng < -180 || (float) $lng > 180)) {
            return $this->json(['status' => 'error', 'message' => 'longitude harus angka antara -180 dan 180.'], 422);
        }

        $m   = new HotelModel();
        $db  = db_connect();
        $max = (int) ($db->table('hotels')->selectMax('peringkat')->get()->getRowArray()['peringkat'] ?? 0);
        $slugBase = url_title($nama, '-', true) ?: 'hotel';
        $slug     = $slugBase . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

        $data = [
            'peringkat'     => isset($in['peringkat']) && is_numeric($in['peringkat']) ? (int) $in['peringkat'] : ($max + 1),
            'nama'          => $nama,
            'slug'          => $slug,
            'kecamatan'     => $kecamatan,
            'alamat'        => $alamat !== '' ? $alamat : null,
            'sarapan'       => $sarapan,
            'latitude'      => $lat !== null ? (float) $lat : null,
            'longitude'     => $lng !== null ? (float) $lng : null,
            'foto'          => ($foto !== '' && preg_match('#^https?://#i', $foto)) ? $foto : null,
            'jumlah_ulasan' => 0,
        ];

        $db->transBegin();
        try {
            $id = $m->insert($data);
            if (! $id || $db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menyimpan data hotel.'], 500);
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Hotels::create gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan server saat membuat hotel.'], 500);
        }

        $m->hapusCache();
        $saved = $m->find($id);

        return $this->json([
            'status'  => 'ok',
            'message' => 'Hotel berhasil ditambahkan.',
            'data'    => self::format($saved),
        ], 201);
    }

    /** PUT/PATCH /api/v1/hotels/{id} — khusus role admin (mendukung optimistic locking updated_at). */
    public function update(int $id): ResponseInterface
    {
        if ($r = $this->butuhRole(['admin'])) {
            return $r;
        }

        $m = new HotelModel();
        $h = $m->find($id);
        if (! $h) {
            return $this->json(['status' => 'error', 'message' => 'Hotel tidak ditemukan.'], 404);
        }

        $json = $this->request->getJSON(true);
        $in   = is_array($json) ? $json : [];

        // Optimistic locking (K7): cegah menimpa data jika versi sudah berubah
        if (isset($in['updated_at']) && $in['updated_at'] !== '' && ($h['updated_at'] ?? null) !== null) {
            if (trim((string) $in['updated_at']) !== trim((string) $h['updated_at'])) {
                return $this->json([
                    'status'  => 'error',
                    'message' => 'Konflik pembaruan (409): data hotel telah diubah oleh pengguna lain. Muat ulang data terbaru.',
                    'data'    => self::format($h),
                ], 409);
            }
        }

        $upd = [];
        if (array_key_exists('nama', $in)) {
            $nama = trim((string) $in['nama']);
            if (mb_strlen($nama) < 3) {
                return $this->json(['status' => 'error', 'message' => 'nama hotel minimal 3 karakter.'], 422);
            }
            $upd['nama'] = $nama;
        }
        if (array_key_exists('kecamatan', $in)) {
            $kec = trim((string) $in['kecamatan']);
            if ($kec === '') {
                return $this->json(['status' => 'error', 'message' => 'kecamatan tidak boleh kosong.'], 422);
            }
            $upd['kecamatan'] = $kec;
        }
        if (array_key_exists('alamat', $in)) {
            $upd['alamat'] = trim((string) $in['alamat']) ?: null;
        }
        if (array_key_exists('sarapan', $in)) {
            $sarapan = trim((string) $in['sarapan']);
            if (! in_array($sarapan, ['Tidak', 'Ya', 'All-in'], true)) {
                return $this->json(['status' => 'error', 'message' => 'sarapan harus bernilai Tidak, Ya, atau All-in.'], 422);
            }
            $upd['sarapan'] = $sarapan;
        }
        if (array_key_exists('latitude', $in)) {
            if ($in['latitude'] !== null && (! is_numeric($in['latitude']) || (float) $in['latitude'] < -90 || (float) $in['latitude'] > 90)) {
                return $this->json(['status' => 'error', 'message' => 'latitude harus angka antara -90 dan 90.'], 422);
            }
            $upd['latitude'] = $in['latitude'] !== null ? (float) $in['latitude'] : null;
        }
        if (array_key_exists('longitude', $in)) {
            if ($in['longitude'] !== null && (! is_numeric($in['longitude']) || (float) $in['longitude'] < -180 || (float) $in['longitude'] > 180)) {
                return $this->json(['status' => 'error', 'message' => 'longitude harus angka antara -180 dan 180.'], 422);
            }
            $upd['longitude'] = $in['longitude'] !== null ? (float) $in['longitude'] : null;
        }

        if ($upd === []) {
            return $this->json(['status' => 'error', 'message' => 'Tidak ada field valid yang dikirim untuk diperbarui.'], 422);
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $m->update($id, $upd);
            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal memperbarui hotel.'], 500);
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Hotels::update gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan server saat memperbarui hotel.'], 500);
        }

        $m->hapusCache();

        return $this->json([
            'status'  => 'ok',
            'message' => 'Data hotel berhasil diperbarui.',
            'data'    => self::format($m->find($id)),
        ]);
    }

    /** DELETE /api/v1/hotels/{id} — khusus role admin. */
    public function delete(int $id): ResponseInterface
    {
        if ($r = $this->butuhRole(['admin'])) {
            return $r;
        }

        $m = new HotelModel();
        if (! $m->find($id)) {
            return $this->json(['status' => 'error', 'message' => 'Hotel tidak ditemukan.'], 404);
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $m->delete($id);
            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->json(['status' => 'error', 'message' => 'Gagal menghapus hotel.'], 500);
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'API Hotels::delete gagal: {msg}', ['msg' => $e->getMessage()]);

            return $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan server saat menghapus hotel.'], 500);
        }

        $m->hapusCache();

        return $this->json([
            'status'  => 'ok',
            'message' => 'Hotel dan relasinya berhasil dihapus.',
        ]);
    }

    /** GET /api/v1/kecamatan — kunci gabung dengan data kuliner. */
    public function kecamatan(): ResponseInterface
    {
        if ($r = $this->butuhKey()) {
            return $r;
        }

        return $this->json(['status' => 'ok', 'data' => (new HotelModel())->daftarKecamatan()]);
    }
}
