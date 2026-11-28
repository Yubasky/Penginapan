<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h1>Ringkasan</h1>
<div class="adm__cards">
    <a href="<?= site_url('admin/hotel') ?>"><strong><?= $c['hotel'] ?></strong><span>Hotel</span><small><?= $c['geo'] ?> sudah punya koordinat</small></a>
    <a href="<?= site_url('admin/ulasan') ?>"><strong><?= $c['ulasan'] ?></strong><span>Ulasan pengunjung</span></a>
    <a href="<?= site_url('admin/pengguna') ?>"><strong><?= $c['pengguna'] ?></strong><span>Pengguna</span></a>
    <a href="<?= site_url('admin/apikey') ?>"><strong><?= $c['apikey'] ?></strong><span>Kunci API aktif</span><small>kelola token mitra</small></a>
    <a href="<?= site_url('admin/kuliner') ?>"><strong><?= $c['kuliner'] ?></strong><span>Data kuliner</span><small>sinkron: <?= $syncTerakhir ? tgl_id($syncTerakhir) : 'belum pernah' ?></small></a>
</div>

<div style="display:flex;gap:.8rem;margin:1.8rem 0;flex-wrap:wrap">
    <a class="btn btn--outline" href="<?= site_url('admin/laporan/hotel') ?>" target="_blank" style="display:inline-flex;align-items:center;gap:.4rem">
        🖨️ Cetak Laporan Rekap Hotel
    </a>
</div>

<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.8rem">
        <h2 style="margin:0">Ulasan terkini</h2>
        <a class="link" href="<?= site_url('admin/ulasan') ?>">Lihat semua ulasan →</a>
    </div>
    <?php if (! empty($ulasanTerbaru)): ?>
        <div class="tblwrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pengulas</th>
                        <th>Hotel</th>
                        <th>Rating</th>
                        <th>Ulasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ulasanTerbaru as $u): ?>
                        <?php $ringkasan = trim((string) ($u['judul'] ?: $u['isi'])); ?>
                        <tr>
                            <td class="small"><?= tgl_id($u['created_at']) ?></td>
                            <td><strong><?= esc($u['penulis']) ?></strong></td>
                            <td><a class="link" href="<?= site_url('penginapan/' . $u['hotel_slug']) ?>#ulasan" target="_blank"><?= esc($u['hotel_nama']) ?></a></td>
                            <td><span class="stars"><?= str_repeat('★', (int) $u['rating']) ?></span></td>
                            <td class="small"><?= esc(mb_strimwidth($ringkasan, 0, 90, '…')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="muted">Belum ada ulasan pengunjung.</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
