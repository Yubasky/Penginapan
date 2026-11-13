<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Perbandingan</p>
    <h1>Bandingkan penginapan</h1>
    <p>Pilih sampai 3 hotel dengan tombol “⇄ Bandingkan” pada kartu hotel.</p>
</div></section>
<div class="wrap prose prose--wide">
<?php if (! $hotels): ?>
    <div class="empty"><h3>Belum ada hotel dipilih</h3><p>Buka daftar penginapan lalu klik “⇄ Bandingkan” pada hotel yang kamu mau.</p><a class="btn" href="<?= site_url('penginapan') ?>">Ke daftar penginapan</a></div>
<?php else:
    $bestR = max(array_map(fn ($x) => (float) ($x['rating'] ?? 0), $hotels));
    $bestU = max(array_map(fn ($x) => (int) ($x['jumlah_ulasan'] ?? 0), $hotels)); ?>
    <div class="tblwrap"><table class="tbl cmp-tbl">
        <tr><th></th><?php foreach ($hotels as $h): ?>
            <td><div class="cmp__head" style="--h: <?= hue_hotel($h) ?>"><span><?= esc(inisial_hotel($h['nama'])) ?></span></div>
                <a class="cmp__name" href="<?= site_url('penginapan/' . $h['slug']) ?>"><?= esc($h['nama']) ?></a>
            </td>
        <?php endforeach; ?></tr>
        <tr><th>Rating Tripadvisor</th><?php foreach ($hotels as $h): ?><td class="<?= $h['rating'] !== null && (float) $h['rating'] === $bestR ? 'best' : '' ?>"><?= rating_label($h['rating']) ?></td><?php endforeach; ?></tr>
        <tr><th>Jumlah ulasan</th><?php foreach ($hotels as $h): ?><td class="<?= (int) $h['jumlah_ulasan'] === $bestU && $bestU > 0 ? 'best' : '' ?>"><?= $h['jumlah_ulasan'] !== null ? number_format((int) $h['jumlah_ulasan'], 0, ',', '.') : '-' ?></td><?php endforeach; ?></tr>
        <tr><th>Rating pengunjung situs</th><?php foreach ($hotels as $h): $a = $avg[(int) $h['id']] ?? null; ?><td><?= $a ? number_format((float) $a['rata'], 1) . ' ★ (' . (int) $a['n'] . ')' : '<span class="muted">Belum ada</span>' ?></td><?php endforeach; ?></tr>
        <tr class="cmp-action-row"><th>Aksi</th><?php foreach ($hotels as $h): ?><td><a class="btn btn--sm btn--ghost btn--del-cmp" href="<?= site_url('bandingkan/toggle/' . $h['id']) ?>" title="Hapus dari perbandingan">✕ Hapus</a></td><?php endforeach; ?></tr>
        <tr><th>Sarapan</th><?php foreach ($hotels as $h): ?><td><?= $h['sarapan'] === 'Tidak' ? 'Tidak' : ($h['sarapan'] === 'All-in' ? 'All-inclusive' : 'Termasuk') ?></td><?php endforeach; ?></tr>
        <tr><th>Kecamatan</th><?php foreach ($hotels as $h): ?><td><?= esc($h['kecamatan'] ?? '-') ?></td><?php endforeach; ?></tr>
        <tr><th>Alamat</th><?php foreach ($hotels as $h): ?><td><?= esc($h['alamat']) ?></td><?php endforeach; ?></tr>
        <tr><th>Peta</th><?php foreach ($hotels as $h): ?><td><a class="link" target="_blank" rel="noopener" href="<?= esc(peta_url($h), 'attr') ?>">Google Maps</a></td><?php endforeach; ?></tr>
    </table></div>
    <p style="margin-top:1rem"><a class="link" href="<?= site_url('bandingkan/kosongkan') ?>">Kosongkan perbandingan</a></p>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
