<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
    <h1 style="margin:0">Pesan &amp; Tanya Hotel</h1>
    <a class="btn btn--outline" href="<?= site_url('admin/laporan/pesan') ?>" target="_blank" style="display:inline-flex;align-items:center;gap:.4rem">
        🖨️ Cetak Rekap Pesan
    </a>
</div>

<p class="tabs">
    <?php foreach (['' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $v => $l): ?>
        <a class="<?= $status === $v ? 'is-on' : '' ?>" href="<?= site_url('admin/pesan' . ($v ? '?status=' . $v : '')) ?>"><?= $l ?></a>
    <?php endforeach; ?>
</p>

<?php foreach ($items as $p): ?>
    <div class="panel msg" style="margin-bottom:1.2rem">
        <div class="msg__top">
            <div>
                <strong><?= esc($p['nama']) ?></strong> · 
                <a class="link" href="mailto:<?= esc($p['email'], 'attr') ?>"><?= esc($p['email']) ?></a>
                <?= $p['telepon'] ? ' · ' . esc($p['telepon']) : '' ?>
                <div class="small">
                    Tujuan: <a class="link" href="<?= site_url('penginapan/' . $p['hotel_slug']) ?>" target="_blank"><strong><?= esc($p['hotel_nama']) ?></strong></a> · <?= tgl_id($p['created_at']) ?>
                </div>
            </div>
            <span class="chip chip--st st-<?= esc($p['status'], 'attr') ?>" style="text-transform:capitalize"><?= esc($p['status']) ?></span>
        </div>

        <p class="small" style="margin:.5rem 0;color:var(--muted)">
            <?= $p['tgl_checkin'] ? '📅 Check-in ' . tgl_id($p['tgl_checkin']) : '' ?> 
            <?= $p['tgl_checkout'] ? '· Check-out ' . tgl_id($p['tgl_checkout']) : '' ?> 
            <?= $p['jumlah_tamu'] ? '· 👥 ' . (int) $p['jumlah_tamu'] . ' tamu' : '' ?>
        </p>

        <div style="background:#f9f8f4;padding:.8rem 1rem;border-radius:8px;margin:.6rem 0">
            <strong class="small" style="color:var(--muted)">Pertanyaan / Pesan Tamu:</strong>
            <p style="margin:.2rem 0 0"><?= nl2br(esc($p['pesan'] ?: '(tanpa pesan)')) ?></p>
        </div>

        <?php 
        $allAns = json_decode($p['jawaban'] ?? '[]', true) ?: [];
        if ($allAns): ?>
            <div style="margin:.6rem 0;padding:.6rem .8rem;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
                <strong class="small" style="color:#166534">💬 Riwayat Jawaban / Diskusi Q&amp;A (<?= count($allAns) ?>):</strong>
                <ul style="margin:.3rem 0 0;padding-left:1.2rem;font-size:.85rem;color:#14532d">
                    <?php foreach ($allAns as $ans): ?>
                        <li style="margin-bottom:.2rem"><strong><?= esc($ans['nama']) ?> (<?= esc($ans['role'] ?? 'user') ?>):</strong> <?= esc($ans['isi']) ?> <span class="muted" style="font-size:.78rem">(<?= tgl_id($ans['created_at'] ?? null) ?>)</span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Balasan & Update Status -->
        <form method="post" action="<?= site_url('admin/pesan/' . $p['id'] . '/status') ?>" style="margin-top:.8rem;border-top:1px dashed var(--line);padding-top:.8rem">
            <?= token_field() ?>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-start">
                <div style="flex:1;min-width:240px">
                    <label class="small" style="font-weight:600;display:block;margin-bottom:.3rem">
                        Balasan Pengelola / Catatan Admin:
                        <?php if (! empty($p['dibalas_at'])): ?>
                            <span class="muted font-normal">(terakhir dibalas: <?= tgl_id($p['dibalas_at']) ?>)</span>
                        <?php endif; ?>
                    </label>
                    <textarea name="balasan_admin" rows="2" style="width:100%;font-size:.9rem" placeholder="Tulis balasan untuk tamu ini (mis: Kamar tersedia, silakan konfirmasi ke WA...)"><?= esc($p['balasan_admin'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem;min-width:140px">
                    <label class="small" style="font-weight:600">Ubah Status:</label>
                    <select name="status" style="padding:.5rem;font-size:.9rem">
                        <?php foreach (['baru', 'diproses', 'selesai'] as $s): ?>
                            <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?> style="text-transform:capitalize"><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn--gold" type="submit" style="font-size:.85rem;padding:.5rem 1rem">Simpan &amp; Balas</button>
                </div>
            </div>
        </form>
    </div>
<?php endforeach; ?>

<?php if (! $items): ?>
    <p class="muted">Tidak ada pesan.</p>
<?php endif; ?>

<?= paginasi($page, $pages) ?>
<?= $this->endSection() ?>
