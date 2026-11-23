<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox">
    <h1>Daftar</h1>
    <form class="form" method="post" action="<?= site_url('daftar') ?>">
        <?= token_field() ?>
        <label>Nama Lengkap<input type="text" name="nama" required minlength="2" maxlength="100" autocomplete="name" value="<?= esc(old('nama')) ?>"></label>
        <label>Email<input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= esc(old('email')) ?>"></label>
        <label>Nomor HP / WhatsApp<input type="tel" name="no_hp" required inputmode="tel" maxlength="20" placeholder="081234567890" autocomplete="tel" value="<?= esc(old('no_hp')) ?>"></label>
        <label>Jenis Kelamin <span class="muted" style="font-weight:400">(opsional)</span>
            <select name="jenis_kelamin">
                <option value="">— Pilih —</option>
                <?php foreach (['Laki-laki', 'Perempuan'] as $jk): ?>
                    <option value="<?= $jk ?>" <?= old('jenis_kelamin') === $jk ? 'selected' : '' ?>><?= $jk ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tanggal Lahir <span class="muted" style="font-weight:400">(opsional)</span>
            <input type="date" name="tanggal_lahir" max="<?= date('Y-m-d') ?>" autocomplete="bday" value="<?= esc(old('tanggal_lahir')) ?>">
        </label>
        <label>Kota Asal <span class="muted" style="font-weight:400">(opsional)</span><input type="text" name="kota_asal" maxlength="100" placeholder="Contoh: Medan" autocomplete="address-level2" value="<?= esc(old('kota_asal')) ?>"></label>
        <label>Password
            <div class="pwbox" style="position:relative; display:flex; align-items:center">
                <input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Min. 8 karakter, huruf &amp; angka" style="padding-right:40px; margin:0; width:100%">
                <span role="button" tabindex="0" aria-label="Tampilkan/sembunyikan password" onclick="togglePw(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();togglePw(this)}" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#666; display:flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </span>
            </div>
        </label>
        <label>Ulangi password
            <div class="pwbox" style="position:relative; display:flex; align-items:center">
                <input type="password" name="password2" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Ketik ulang password" style="padding-right:40px; margin:0; width:100%">
                <span role="button" tabindex="0" aria-label="Tampilkan/sembunyikan password" onclick="togglePw(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();togglePw(this)}" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#666; display:flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </span>
            </div>
        </label>
        <label style="flex-direction:row; align-items:flex-start; gap:.6rem; font-weight:400">
            <input type="checkbox" name="setuju" value="1" required style="width:auto; margin-top:.2rem" <?= old('setuju') ? 'checked' : '' ?>>
            <span>Saya menyetujui syarat &amp; ketentuan serta kebijakan privasi Singgah Medan.</span>
        </label>
        <button class="btn btn--block" type="submit">Buat akun</button>
    </form>
    <p class="muted">Sudah punya akun? <a class="link" href="<?= site_url('masuk') ?>">Masuk</a></p>
</div>

<script>
    const IKON_TUTUP = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    const IKON_BUKA = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';

    function togglePw(el) {
        const i = el.previousElementSibling;
        const buka = i.type === 'password';
        i.type = buka ? 'text' : 'password';
        el.innerHTML = buka ? IKON_BUKA : IKON_TUTUP;
    }
</script>
<?= $this->endSection() ?>
