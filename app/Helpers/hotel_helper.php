<?php

if (! function_exists('hue_hotel')) {
    function hue_hotel(array $h): int
    {
        return (crc32($h["nama"]) % 40) + 155; // rentang hijau-toska
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

if (! function_exists('hotel_card')) {
    function hotel_card(array $h): string
    {
        ob_start(); ?>
        <article class="card">
            <a class="card__cover" href="<?= site_url('penginapan/' . $h['slug']) ?>" style="--h: <?= hue_hotel($h) ?>" aria-label="<?= esc($h['nama'], 'attr') ?>">
                <span class="card__mono"><?= esc(inisial_hotel($h['nama'])) ?></span>
                <?php if ($h['sarapan'] !== 'Tidak'): ?>
                    <span class="chip chip--gold"><?= $h['sarapan'] === 'All-in' ? 'All-in' : 'Sarapan' ?></span>
                <?php endif; ?>
            </a>
            <div class="card__body">
                <?php if (! empty($h['kecamatan'])): ?><p class="eyebrow"><?= esc($h['kecamatan']) ?></p><?php endif; ?>
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
