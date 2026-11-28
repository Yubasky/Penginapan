<?php

if (! function_exists('hue_hotel')) {
    function hue_hotel(array $h): int
    {
        return (crc32($h['nama']) % 40) + 155; // rentang hijau-toska
    }
}

if (! function_exists('inisial_hotel')) {
    function inisial_hotel(string $nama): string
    {
        $nama  = preg_replace('/^(hotel|the)\s+/i', '', trim($nama));
        $kata  = preg_split('/\s+/', $nama);
        $huruf = mb_substr($kata[0] ?? '?', 0, 1) . mb_substr($kata[1] ?? '', 0, 1);

        return mb_strtoupper($huruf);
    }
}

if (! function_exists('rating_label')) {
    function rating_label($rating): string
    {
        if ($rating === null || $rating === '') {
            return '<span class="rate rate--none">Belum ada rating</span>';
        }
        return '<span class="rate"><i>★</i> ' . number_format((float) $rating, 1) . '</span>';
    }
}

if (! function_exists('hitung_jarak')) {
    /** Hitung jarak antara 2 koordinat bumi (dalam kilometer) menggunakan rumus Haversine. */
    function hitung_jarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371; // radius bumi dalam km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt(max(0.0, min(1.0, $a))), sqrt(max(0.0, min(1.0, 1 - $a))));
        return round($r * $c, 2);
    }
}

if (! function_exists('format_jarak')) {
    /** Format jarak ramah pengguna: "450 m" atau "2.4 km". */
    function format_jarak($km): string
    {
        if ($km === null || $km === '') {
            return '';
        }
        $d = (float) $km;
        if ($d < 1.0) {
            return round($d * 1000) . ' m';
        }
        return number_format($d, 1, ',', '.') . ' km';
    }
}

if (! function_exists('peta_url')) {
    function peta_url(array $h): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($h['nama'] . ', ' . $h['alamat'] . ', Medan');
    }
}

if (! function_exists('peta_embed_url')) {
    function peta_embed_url(array $h): string
    {
        return 'https://www.google.com/maps?q=' . rawurlencode($h['nama'] . ', ' . $h['alamat'] . ', Medan') . '&output=embed';
    }
}

if (! function_exists('token_field')) {
    /** Input tersembunyi anti-CSRF; dicek oleh AppBase::tokenOk(). */
    function token_field(): string
    {
        $s = session();
        if (! $s->get('_token')) {
            $s->set('_token', bin2hex(random_bytes(16)));
        }
        return '<input type="hidden" name="_token" value="' . esc((string) $s->get('_token'), 'attr') . '">';
    }
}

if (! function_exists('tgl_id')) {
    function tgl_id(?string $ts): string
    {
        if (! $ts) {
            return '-';
        }
        $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t   = strtotime($ts);

        return date('j', $t) . ' ' . $bln[(int) date('n', $t)] . ' ' . date('Y', $t);
    }
}

if (! function_exists('favorit_ids')) {
    function favorit_ids(): array
    {
        static $ids = null;
        if ($ids !== null) {
            return $ids;
        }
        $ids = [];
        $u   = session('user');
        if ($u) {
            $sessKey = 'user_fav_ids_' . $u['id'];
            if (session()->has($sessKey)) {
                $ids = (array) session($sessKey);
            } else {
                $rows = db_connect()->table('favorites')->select('hotel_id')->where('user_id', (int) $u['id'])->get()->getResultArray();
                $ids  = array_map('intval', array_column($rows, 'hotel_id'));
                session()->set($sessKey, $ids);
            }
        }
        return $ids;
    }
}

if (! function_exists('foto_url')) {
    /** Helper URL foto: mendukung Base64 data URI (cloud Supabase), HTTP(S), atau path lokal. */
    function foto_url(?string $src): string
    {
        if (empty($src)) {
            return '';
        }
        if (str_starts_with($src, 'data:') || str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }
        return base_url($src);
    }
}

if (! function_exists('ambil_notifikasi_user')) {
    /** Ambil notifikasi untuk user (jawaban atas pertanyaan yang diajukan oleh user). */
    function ambil_notifikasi_user(?array $user = null): array
    {
        if (! $user) {
            $user = session('user');
        }
        if (! $user) {
            return ['items' => [], 'unread' => 0];
        }

        $userId = (int) $user['id'];

        // Cache notifikasi di session selama 25 detik agar tidak query berulang ke Supabase di setiap klik halaman
        $cacheKey = 'notif_cache_' . $userId;
        $cachedTime = (int) session($cacheKey . '_time');
        if (time() - $cachedTime < 25 && session()->has($cacheKey)) {
            return session($cacheKey);
        }

        $db = \Config\Database::connect();

        // Cari semua pertanyaan yang diajukan oleh user ini
        $inquiries = $db->table('inquiries')
            ->select('inquiries.id, inquiries.pesan, inquiries.jawaban, inquiries.balasan_admin, inquiries.dibalas_at, inquiries.updated_at, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
            ->join('hotels', 'hotels.id = inquiries.hotel_id')
            ->where('inquiries.user_id', $userId)
            ->orderBy('inquiries.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        // Waktu terakhir notifikasi dibaca
        $userRow = $db->table('users')->select('notif_dibaca_at')->where('id', $userId)->get()->getRowArray();
        $lastRead = session('notif_read_at') ?: ($userRow['notif_dibaca_at'] ?? '2000-01-01 00:00:00');
        $lastReadTs = strtotime($lastRead ?: '2000-01-01 00:00:00');

        $notifications = [];
        $unreadCount = 0;

        foreach ($inquiries as $inq) {
            $jawabanList = json_decode($inq['jawaban'] ?? '[]', true) ?: [];

            // Tambahkan juga balasan_admin jika belum masuk dalam jawabanList
            if (! empty($inq['balasan_admin'])) {
                $already = false;
                foreach ($jawabanList as $jl) {
                    if (($jl['isi'] ?? '') === $inq['balasan_admin']) {
                        $already = true;
                        break;
                    }
                }
                if (! $already) {
                    $jawabanList[] = [
                        'id'         => 'admin_legacy',
                        'user_id'    => null,
                        'nama'       => 'Pengelola Hotel / Admin',
                        'role'       => 'admin',
                        'isi'        => $inq['balasan_admin'],
                        'created_at' => $inq['dibalas_at'] ?? $inq['updated_at'],
                    ];
                }
            }

            foreach ($jawabanList as $ans) {
                // Jawaban dari orang lain (bukan user itu sendiri)
                $ansUid = isset($ans['user_id']) ? (int) $ans['user_id'] : 0;
                if ($ansUid !== $userId) {
                    $time = $ans['created_at'] ?? $inq['updated_at'];
                    $isUnread = strtotime($time) > $lastReadTs;
                    if ($isUnread) {
                        $unreadCount++;
                    }

                    $notifications[] = [
                        'inquiry_id' => $inq['id'],
                        'hotel_nama' => $inq['hotel_nama'],
                        'hotel_slug' => $inq['hotel_slug'],
                        'penjawab'   => $ans['nama'] ?? 'Pengguna',
                        'role'       => $ans['role'] ?? 'user',
                        'isi'        => $ans['isi'] ?? '',
                        'pertanyaan' => $inq['pesan'] ?? '',
                        'waktu'      => $time,
                        'is_unread'  => $isUnread,
                        'is_new_q'   => false,
                    ];
                }
            }
        }

        // Jika user adalah admin, ambil juga pertanyaan baru yang diajukan oleh pengguna lain
        if (($user['role'] ?? '') === 'admin') {
            $recentQuestions = $db->table('inquiries')
                ->select('inquiries.id, inquiries.nama, inquiries.pesan, inquiries.created_at, inquiries.updated_at, hotels.nama AS hotel_nama, hotels.slug AS hotel_slug')
                ->join('hotels', 'hotels.id = inquiries.hotel_id')
                ->where('inquiries.user_id !=', $userId)
                ->orderBy('inquiries.created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            foreach ($recentQuestions as $rq) {
                $time = $rq['created_at'] ?? $rq['updated_at'];
                $isUnread = strtotime($time) > $lastReadTs;
                if ($isUnread) {
                    $unreadCount++;
                }
                $notifications[] = [
                    'inquiry_id' => $rq['id'],
                    'hotel_nama' => $rq['hotel_nama'],
                    'hotel_slug' => $rq['hotel_slug'],
                    'penjawab'   => $rq['nama'] ?: 'Pengunjung',
                    'role'       => 'user',
                    'isi'        => $rq['pesan'] ?: '(Pertanyaan baru)',
                    'pertanyaan' => $rq['pesan'] ?? '',
                    'waktu'      => $time,
                    'is_unread'  => $isUnread,
                    'is_new_q'   => true,
                ];
            }
        }

        usort($notifications, static function ($a, $b) {
            return strtotime($b['waktu']) <=> strtotime($a['waktu']);
        });

        $result = [
            'items'  => array_slice($notifications, 0, 10),
            'unread' => $unreadCount,
        ];

        session()->set($cacheKey, $result);
        session()->set($cacheKey . '_time', time());

        return $result;
    }
}

if (! function_exists('bandingkan_ids')) {
    function bandingkan_ids(): array
    {
        return [];
    }
}

if (! function_exists('hotel_card')) {
    function hotel_card(array $h): string
    {
        $fav = in_array((int) $h['id'], favorit_ids(), true);
        $jarak = isset($h['jarak']) && $h['jarak'] !== null && $h['jarak'] !== '' ? (float) $h['jarak'] : null;
        ob_start(); ?>
        <article class="card" data-hotel-id="<?= (int) $h['id'] ?>" data-lat="<?= $h['latitude'] ?? '' ?>" data-lng="<?= $h['longitude'] ?? '' ?>">
            <a class="card__cover <?= ! empty($h['foto']) ? 'card__cover--has-img' : '' ?>" href="<?= site_url('penginapan/' . $h['slug']) ?>" style="--h: <?= hue_hotel($h) ?>" aria-label="<?= esc($h['nama'], 'attr') ?>">
                <?php if (! empty($h['foto'])): ?>
                    <img src="<?= foto_url($h['foto']) ?>" alt="<?= esc($h['nama']) ?>" class="card__photo" loading="lazy">
                <?php else: ?>
                    <span class="card__mono"><?= esc(inisial_hotel($h['nama'])) ?></span>
                <?php endif; ?>
                <?php if ($h['sarapan'] !== 'Tidak'): ?>
                    <span class="chip chip--gold"><?= $h['sarapan'] === 'All-in' ? 'All-in' : 'Sarapan' ?></span>
                <?php endif; ?>
                <?php if ($jarak !== null): ?>
                    <span class="chip chip--gps" title="Jarak dari lokasi Anda">📍 <?= format_jarak($jarak) ?></span>
                <?php else: ?>
                    <span class="chip chip--gps" style="display:none" title="Jarak dari lokasi Anda"></span>
                <?php endif; ?>
            </a>
            <form class="fav" method="post" action="<?= site_url('favorit/' . $h['id']) ?>">
                <?= token_field() ?>
                <button class="fav__btn <?= $fav ? 'is-on' : '' ?>" type="submit" title="<?= $fav ? 'Hapus dari favorit' : 'Simpan ke favorit' ?>" aria-label="Favorit"><?= $fav ? '♥' : '♡' ?></button>
            </form>
            <div class="card__body">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
                    <?php if (! empty($h['kecamatan'])): ?><p class="eyebrow" style="margin:0"><?= esc($h['kecamatan']) ?></p><?php endif; ?>
                    <span class="card__jarak-label" style="<?= $jarak !== null ? '' : 'display:none;' ?>font-size:.76rem;font-weight:700;color:#0284c7;background:#f0f9ff;border:1px solid #bae6fd;padding:1px 7px;border-radius:99px">
                        📍 <span class="val"><?= $jarak !== null ? format_jarak($jarak) : '' ?></span>
                    </span>
                </div>
                <h3><a href="<?= site_url('penginapan/' . $h['slug']) ?>"><?= esc($h['nama']) ?></a></h3>
                <p class="card__addr"><?= esc($h['alamat']) ?></p>
                <div class="card__meta">
                    <?= rating_label($h['rating']) ?>
                    <?php if ($h['jumlah_ulasan'] !== null): ?>
                        <span class="muted"><?= number_format((int) $h['jumlah_ulasan'], 0, ',', '.') ?> ulasan</span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }
}

if (! function_exists('paginasi')) {
    /** Tautan halaman yang mempertahankan filter di query string. */
    function paginasi(int $page, int $pages): string
    {
        if ($pages <= 1) {
            return '';
        }
        $get  = service('request')->getGet();
        $base = current_url();
        $url  = static fn (int $p): string => $base . '?' . http_build_query(array_merge($get, ['page' => $p]));
        $nums = array_unique(array_filter(
            [1, $page - 2, $page - 1, $page, $page + 1, $page + 2, $pages],
            static fn ($p) => $p >= 1 && $p <= $pages
        ));
        sort($nums);

        $html = '<nav class="pager" aria-label="Halaman">';
        if ($page > 1) {
            $html .= '<a href="' . esc($url($page - 1), 'attr') . '">‹ Sebelumnya</a>';
        }
        $prev = 0;
        foreach ($nums as $p) {
            if ($p - $prev > 1) {
                $html .= '<span class="pager__gap">…</span>';
            }
            $html .= $p === $page
                ? '<span class="is-current">' . $p . '</span>'
                : '<a href="' . esc($url($p), 'attr') . '">' . $p . '</a>';
            $prev = $p;
        }
        if ($page < $pages) {
            $html .= '<a href="' . esc($url($page + 1), 'attr') . '">Berikutnya ›</a>';
        }

        return $html . '</nav>';
    }
}

if (! function_exists('kuliner_card')) {
    function kuliner_card(array $k): string
    {
        $jarak     = isset($k['jarak']) && $k['jarak'] !== null && $k['jarak'] !== '' ? (float) $k['jarak'] : null;
        $detailUrl = ! empty($k['id']) ? site_url('kuliner/' . rawurlencode((string) $k['id'])) : ($k['url'] ?? '');
        $external  = empty($k['id']) && ! empty($k['url']);
        ob_start(); ?>
        <article class="food__item" data-lat="<?= $k['latitude'] ?? '' ?>" data-lng="<?= $k['longitude'] ?? '' ?>">
            <?php if (! empty($k['foto'])): ?>
                <?php if ($detailUrl !== ''): ?><a href="<?= esc($detailUrl, 'attr') ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><img src="<?= esc($k['foto'], 'attr') ?>" alt="<?= esc($k['nama'], 'attr') ?>" loading="lazy"></a><?php else: ?><img src="<?= esc($k['foto'], 'attr') ?>" alt="" loading="lazy"><?php endif; ?>
            <?php endif; ?>
            <div>
                <h3><?php if ($detailUrl !== ''): ?><a href="<?= esc($detailUrl, 'attr') ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><?= esc($k['nama']) ?></a><?php else: ?><?= esc($k['nama']) ?><?php endif; ?></h3>
                <p class="muted"><?= esc(trim(($k['kategori'] ?? '') . (! empty($k['harga']) ? ' · ' . $k['harga'] : ''), ' ·')) ?></p>
                <?php if (! empty($k['alamat'])): ?><p class="small"><?= esc($k['alamat']) ?></p><?php endif; ?>
                <div style="display:flex;align-items:center;gap:.6rem;margin-top:.4rem;flex-wrap:wrap">
                    <?php if (isset($k['rating']) && $k['rating'] !== null && $k['rating'] !== ''): ?><p style="margin:0"><?= rating_label($k['rating']) ?></p><?php endif; ?>
                    <span class="chip-jarak" style="<?= $jarak !== null ? 'display:inline-flex;' : 'display:none;' ?>align-items:center;gap:3px;font-size:.78rem;font-weight:700;color:#0284c7;background:#e0f2fe;padding:2px 8px;border-radius:99px" title="Jarak terdekat">
                        📍 <span class="val"><?= $jarak !== null ? format_jarak($jarak) : '' ?></span>
                    </span>
                </div>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }
}
