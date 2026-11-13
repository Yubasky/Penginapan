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
            $rows = db_connect()->table('favorites')->select('hotel_id')->where('user_id', (int) $u['id'])->get()->getResultArray();
            $ids  = array_map('intval', array_column($rows, 'hotel_id'));
        }
        return $ids;
    }
}

if (! function_exists('bandingkan_ids')) {
    function bandingkan_ids(): array
    {
        return array_map('intval', (array) (session('bandingkan') ?? []));
    }
}

if (! function_exists('hotel_card')) {
    function hotel_card(array $h): string
    {
        $fav = in_array((int) $h['id'], favorit_ids(), true);
        $cmp = in_array((int) $h['id'], bandingkan_ids(), true);
        ob_start(); ?>
        <article class="card">
            <a class="card__cover <?= ! empty($h['foto']) ? 'card__cover--has-img' : '' ?>" href="<?= site_url('penginapan/' . $h['slug']) ?>" style="--h: <?= hue_hotel($h) ?>" aria-label="<?= esc($h['nama'], 'attr') ?>">
                <?php if (! empty($h['foto'])): ?>
                    <img src="<?= base_url(esc($h['foto'])) ?>" alt="<?= esc($h['nama']) ?>" class="card__photo" loading="lazy">
                <?php else: ?>
                    <span class="card__mono"><?= esc(inisial_hotel($h['nama'])) ?></span>
                <?php endif; ?>
                <?php if ($h['sarapan'] !== 'Tidak'): ?>
                    <span class="chip chip--gold"><?= $h['sarapan'] === 'All-in' ? 'All-in' : 'Sarapan' ?></span>
                <?php endif; ?>
            </a>
            <form class="fav" method="post" action="<?= site_url('favorit/' . $h['id']) ?>">
                <?= token_field() ?>
                <button class="fav__btn <?= $fav ? 'is-on' : '' ?>" type="submit" title="<?= $fav ? 'Hapus dari favorit' : 'Simpan ke favorit' ?>" aria-label="Favorit"><?= $fav ? '♥' : '♡' ?></button>
            </form>
            <div class="card__body">
                <?php if (! empty($h['kecamatan'])): ?><p class="eyebrow"><?= esc($h['kecamatan']) ?></p><?php endif; ?>
                <h3><a href="<?= site_url('penginapan/' . $h['slug']) ?>"><?= esc($h['nama']) ?></a></h3>
                <p class="card__addr"><?= esc($h['alamat']) ?></p>
                <div class="card__meta">
                    <?= rating_label($h['rating']) ?>
                    <?php if ($h['jumlah_ulasan'] !== null): ?>
                        <span class="muted"><?= number_format((int) $h['jumlah_ulasan'], 0, ',', '.') ?> ulasan</span>
                    <?php endif; ?>
                    <a class="cmp <?= $cmp ? 'is-on' : '' ?>" href="<?= site_url('bandingkan/toggle/' . $h['id']) ?>"><?= $cmp ? '✓ Dipilih' : '⇄ Bandingkan' ?></a>
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
        ob_start(); ?>
        <article class="food__item">
            <?php if (! empty($k['foto'])): ?><img src="<?= esc($k['foto'], 'attr') ?>" alt="" loading="lazy"><?php endif; ?>
            <div>
                <h3><?php if (! empty($k['url'])): ?><a href="<?= esc($k['url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc($k['nama']) ?></a><?php else: ?><?= esc($k['nama']) ?><?php endif; ?></h3>
                <p class="muted"><?= esc(trim(($k['kategori'] ?? '') . (! empty($k['harga']) ? ' · ' . $k['harga'] : ''), ' ·')) ?></p>
                <?php if (! empty($k['alamat'])): ?><p class="small"><?= esc($k['alamat']) ?></p><?php endif; ?>
                <?php if (isset($k['rating']) && $k['rating'] !== null && $k['rating'] !== ''): ?><p><?= rating_label($k['rating']) ?></p><?php endif; ?>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }
}
