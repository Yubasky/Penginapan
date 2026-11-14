<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="pagehead"><div class="wrap">
    <p class="eyebrow eyebrow--gold">Profil &amp; Akun</p>
    <h1><?= esc($user['nama']) ?></h1>
    <p><?= esc($user['email']) ?> · <span class="badge" style="text-transform:capitalize"><?= esc($user['role'] ?? 'user') ?></span></p>
</div></section>

<div class="wrap" style="padding:2rem 0 4rem;display:flex;flex-direction:column;gap:2.2rem">
    <!-- Layer 1: Edit Data Profil -->
    <div class="panel" style="max-width:760px">
        <h2 style="margin-top:0">👤 Edit Data Profil</h2>
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

    <!-- Layer 2: Hotel Favorit -->
    <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1.2rem">
            <h2 style="margin:0">❤️ Hotel Favorit</h2>
            <span class="chip" style="font-weight:700"><?= count($favorit) ?> Tersimpan</span>
        </div>
        <?php if ($favorit): ?>
            <div class="grid"><?php foreach ($favorit as $h) { echo hotel_card($h); } ?></div>
        <?php else: ?>
            <div style="text-align:center;padding:2.5rem 1rem;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1">
                <p style="font-size:1.1rem;color:#475569;margin:0">Belum ada hotel yang disimpan di favorit.</p>
                <p class="muted small" style="margin:.4rem 0 1.2rem">Klik tombol hati (♡) pada kartu hotel mana saja untuk menyimpannya ke daftar ini.</p>
                <a class="btn btn--sm" href="<?= site_url('penginapan') ?>">Jelajahi Penginapan</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Layer 3: Ulasan Saya -->
    <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1.2rem">
            <h2 style="margin:0">⭐ Ulasan Penginapan Saya</h2>
            <span class="chip" style="font-weight:700"><?= count($ulasan) ?> Ulasan</span>
        </div>
        <?php if ($ulasan): ?>
            <div style="display:flex;flex-direction:column;gap:1.2rem">
                <?php foreach ($ulasan as $r): ?>
                    <div class="rev" style="background:#fcfcfc;border:1px solid #e2e8f0;border-radius:12px;padding:1.2rem">
                        <div class="rev__top">
                            <a class="link" href="<?= site_url('penginapan/' . $r['hotel_slug']) ?>#ulasan"><strong style="font-size:1.05rem"><?= esc($r['hotel_nama']) ?></strong></a>
                            <span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span>
                            <span class="muted small"><?= tgl_id($r['created_at']) ?></span>
                        </div>
                        <?php if (! empty($r['judul'])): ?><p class="rev__t" style="font-weight:600;margin-top:.4rem"><?= esc($r['judul']) ?></p><?php endif; ?>
                        <p style="margin-top:.3rem"><?= nl2br(esc($r['isi'])) ?></p>
                        <?php 
                        $uMedia = ! empty($r['media']) ? (json_decode($r['media'], true) ?: []) : [];
                        if ($uMedia): ?>
                            <div class="rev__media" style="margin-top:.8rem">
                                <?php foreach ($uMedia as $um): ?>
                                    <?php if (($um['type'] ?? '') === 'video'): ?>
                                        <div class="rev__video-box"><video src="<?= foto_url($um['url']) ?>" controls preload="metadata"></video></div>
                                    <?php else: ?>
                                        <a href="<?= foto_url($um['url']) ?>" target="_blank" rel="noopener" class="rev__thumb" title="Lihat foto fasilitas"><img src="<?= foto_url($um['url']) ?>" alt="Fasilitas" loading="lazy"></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:2.5rem 1rem;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1">
                <p style="font-size:1.1rem;color:#475569;margin:0">Belum ada ulasan yang kamu tulis.</p>
                <p class="muted small" style="margin:.4rem 0 1.2rem">Bagikan pengalaman menginapmu dan unggah foto fasilitas hotel untuk membantu wisatawan lain.</p>
                <a class="btn btn--sm btn--ghost" href="<?= site_url('penginapan') ?>">Cari Hotel &amp; Beri Ulasan</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Layer 4: Pertanyaan & Pesan Saya -->
    <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1.2rem">
            <h2 style="margin:0">💬 Pertanyaan &amp; Tanya Jawab Saya</h2>
            <span class="chip" style="font-weight:700"><?= count($pesan) ?> Pertanyaan</span>
        </div>
        <?php if ($pesan): ?>
            <div style="display:flex;flex-direction:column;gap:1.2rem">
                <?php foreach ($pesan as $p): ?>
                    <?php 
                    $pJawaban = json_decode($p['jawaban'] ?? '[]', true) ?: [];
                    if (! empty($p['balasan_admin'])) {
                        $exists = false;
                        foreach ($pJawaban as $pj) {
                            if (($pj['isi'] ?? '') === $p['balasan_admin']) {
                                $exists = true;
                                break;
                            }
                        }
                        if (! $exists) {
                            array_unshift($pJawaban, [
                                'nama' => 'Pengelola Hotel / Admin',
                                'role' => 'admin',
                                'isi'  => $p['balasan_admin'],
                            ]);
                        }
                    }
                    ?>
                    <div class="rev" style="background:#fcfcfc;border:1px solid #e2e8f0;border-radius:12px;padding:1.2rem">
                        <div class="rev__top" style="flex-wrap:wrap;gap:.5rem">
                            <a class="link" href="<?= site_url('penginapan/' . $p['hotel_slug']) ?>#tanya"><strong style="font-size:1.05rem"><?= esc($p['hotel_nama']) ?></strong></a>
                            <span class="chip chip--st st-<?= esc($p['status'], 'attr') ?>" style="text-transform:capitalize"><?= esc($p['status']) ?></span>
                            <span class="muted small"><?= tgl_id($p['created_at']) ?></span>
                            <div style="display:inline-flex;gap:.3rem;margin-left:auto">
                                <button type="button" class="btn btn--xs btn--ghost" onclick="toggleEditAkunInq(<?= $p['id'] ?>)" title="Edit Pertanyaan">✏️ Edit</button>
                                <form method="post" action="<?= site_url('tanya/' . $p['id'] . '/hapus') ?>" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')">
                                    <?= token_field() ?>
                                    <button type="submit" class="btn btn--xs btn--ghost" style="color:#dc2626;border-color:#fca5a5" title="Hapus Pertanyaan">🗑️ Hapus</button>
                                </form>
                            </div>
                        </div>
                        <p id="akun-inq-text-<?= $p['id'] ?>" style="margin-top:.4rem"><strong>Pertanyaan:</strong> <?= esc($p['pesan'] ?: '(tanpa pesan)') ?></p>

                        <form id="edit-akun-inq-<?= $p['id'] ?>" method="post" action="<?= site_url('tanya/' . $p['id'] . '/edit') ?>" style="display:none;margin-top:.6rem;background:#ffffff;padding:.8rem;border-radius:8px;border:1px solid #cbd5e1">
                            <?= token_field() ?>
                            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;color:var(--teal)">Edit Pertanyaan:</label>
                            <textarea name="pertanyaan" rows="2" required minlength="5" maxlength="1000" style="width:100%;font-size:.9rem;padding:.5rem;border-radius:6px;border:1px solid #cbd5e1"><?= esc($p['pesan']) ?></textarea>
                            <div style="display:flex;justify-content:flex-end;gap:.4rem;margin-top:.4rem">
                                <button type="button" class="btn btn--xs btn--ghost" onclick="toggleEditAkunInq(<?= $p['id'] ?>)">Batal</button>
                                <button type="submit" class="btn btn--xs">Simpan Perubahan</button>
                            </div>
                        </form>
                        
                        <?php if ($pJawaban): ?>
                            <div style="margin-top:.8rem;border-left:3px solid #16a34a;padding-left:.9rem;display:flex;flex-direction:column;gap:.5rem">
                                <?php foreach ($pJawaban as $ans): ?>
                                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:.6rem .8rem;border-radius:6px">
                                        <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.2rem">
                                            <strong style="color:#166534;font-size:.85rem"><?= esc($ans['nama']) ?></strong>
                                            <?php if (($ans['role'] ?? '') === 'admin'): ?>
                                                <span class="badge" style="background:#22c55e;color:#fff;font-size:.68rem">Admin</span>
                                            <?php else: ?>
                                                <span class="badge" style="background:#64748b;color:#fff;font-size:.68rem">Pengunjung</span>
                                            <?php endif; ?>
                                        </div>
                                        <p style="margin:0;color:#15803d;font-size:.9rem"><?= nl2br(esc($ans['isi'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="muted small" style="margin:.5rem 0 0;font-style:italic">⏳ Belum ada jawaban. Pengelola hotel atau sesama pengunjung akan menjawab pertanyaanmu di halaman hotel.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:2.5rem 1rem;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1">
                <p style="font-size:1.1rem;color:#475569;margin:0">Belum ada pertanyaan yang diajukan.</p>
                <p class="muted small" style="margin:.4rem 0 1.2rem">Punya pertanyaan seputar fasilitas atau kamar? Tanyakan langsung di halaman detail penginapan.</p>
                <a class="btn btn--sm btn--ghost" href="<?= site_url('penginapan') ?>">Lihat Penginapan</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleEditAkunInq(id) {
    var form = document.getElementById('edit-akun-inq-' + id);
    var text = document.getElementById('akun-inq-text-' + id);
    if (!form) return;
    var isHidden = form.style.display === 'none' || form.style.display === '';
    form.style.display = isHidden ? 'block' : 'none';
    if (text) text.style.display = isHidden ? 'none' : 'block';
}
</script>
<?= $this->endSection() ?>
