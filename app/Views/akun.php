<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Profil &amp; Akun</p>
    <h1><?= esc($user['nama']) ?></h1>
    <p><?= esc($user['email']) ?> · <span class="badge" style="text-transform:capitalize"><?= esc($user['role'] ?? 'user') ?></span></p>
</div></section>

<div class="wrap" style="padding:2rem 0 4rem">
    <div class="panel" style="max-width:720px;margin-bottom:3rem">
        <h2>Edit Data Profil</h2>
        <p class="muted small" style="margin-top:-.3rem;margin-bottom:1.2rem">Perbarui nama lengkap, email, atau ganti kata sandi akunmu.</p>
        
        <form class="form form--grid" method="post" action="<?= site_url('akun/update') ?>">
            <?= token_field() ?>
            <div>
                <label>Nama Lengkap
                    <input type="text" name="nama" required value="<?= esc(old('nama', $user['nama'])) ?>">
                </label>
            </div>
            <div>
                <label>Alamat Email
                    <input type="email" name="email" required value="<?= esc(old('email', $user['email'])) ?>">
                </label>
            </div>
            <div>
                <label>Password Baru <span class="muted font-normal">(opsional)</span>
                    <input type="password" name="password_baru" placeholder="Minimal 6 karakter" autocomplete="new-password">
                </label>
            </div>
            <div>
                <label>Konfirmasi Password Baru
                    <input type="password" name="password_konfirmasi" placeholder="Ulangi password baru" autocomplete="new-password">
                </label>
            </div>
            <div class="full" style="margin-top:.5rem">
                <button class="btn btn--gold" type="submit">Simpan Perubahan Profil</button>
            </div>
        </form>
    </div>

    <h2>Hotel favorit (<?= count($favorit) ?>)</h2>
    <?php if ($favorit): ?><div class="grid"><?php foreach ($favorit as $h) { echo hotel_card($h); } ?></div>
    <?php else: ?><p class="muted">Belum ada favorit. Klik ♡ pada kartu hotel untuk menyimpannya.</p><?php endif; ?>

    <h2 style="margin-top:2.5rem">Ulasan saya (<?= count($ulasan) ?>)</h2>
    <?php foreach ($ulasan as $r): ?>
        <div class="rev">
            <div class="rev__top">
                <a class="link" href="<?= site_url('penginapan/' . $r['hotel_slug']) ?>#ulasan"><strong><?= esc($r['hotel_nama']) ?></strong></a>
                <span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span>
                <span class="muted small"><?= tgl_id($r['created_at']) ?></span>
            </div>
            <?php if (! empty($r['judul'])): ?><p class="rev__t"><?= esc($r['judul']) ?></p><?php endif; ?>
            <p><?= nl2br(esc($r['isi'])) ?></p>
            <?php 
            $uMedia = ! empty($r['media']) ? (json_decode($r['media'], true) ?: []) : [];
            if ($uMedia): ?>
                <div class="rev__media">
                    <?php foreach ($uMedia as $um): ?>
                        <?php if (($um['type'] ?? '') === 'video'): ?>
                            <div class="rev__video-box"><video src="<?= base_url(esc($um['url'])) ?>" controls preload="metadata"></video></div>
                        <?php else: ?>
                            <a href="<?= base_url(esc($um['url'])) ?>" target="_blank" rel="noopener" class="rev__thumb" title="Lihat foto fasilitas"><img src="<?= base_url(esc($um['url'])) ?>" alt="Fasilitas" loading="lazy"></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (! $ulasan): ?><p class="muted">Belum ada ulasan.</p><?php endif; ?>

    <h2 style="margin-top:2.5rem">Pesan ke hotel (<?= count($pesan) ?>)</h2>
    <?php foreach ($pesan as $p): ?>
        <div class="rev">
            <div class="rev__top">
                <a class="link" href="<?= site_url('penginapan/' . $p['hotel_slug']) ?>"><strong><?= esc($p['hotel_nama']) ?></strong></a>
                <span class="chip chip--st st-<?= esc($p['status'], 'attr') ?>" style="text-transform:capitalize"><?= esc($p['status']) ?></span>
                <span class="muted small"><?= tgl_id($p['created_at']) ?></span>
            </div>
            <p><?= esc($p['pesan'] ?: '(tanpa pesan)') ?></p>
            <?php if (! empty($p['balasan_admin'])): ?>
                <div style="background:#f0fdf4;border-left:3px solid #16a34a;padding:.6rem .9rem;border-radius:6px;margin-top:.6rem">
                    <strong style="color:#166534;font-size:.85rem;display:block">Balasan dari Pengelola / Admin:</strong>
                    <p style="margin:.2rem 0 0;color:#15803d;font-size:.9rem"><?= nl2br(esc($p['balasan_admin'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (! $pesan): ?><p class="muted">Belum ada pesan.</p><?php endif; ?>
</div>
<?= $this->endSection() ?>
