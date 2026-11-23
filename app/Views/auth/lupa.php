<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox">
    <h1>Lupa Sandi</h1>
    <p class="muted">Masukkan email dan nomor HP yang kamu pakai saat mendaftar, lalu buat password baru.</p>
    <form class="form" method="post" action="<?= site_url('lupa-sandi') ?>">
        <?= token_field() ?>
        <label>Email<input type="email" name="email" required autofocus maxlength="190" autocomplete="email" value="<?= esc(old('email')) ?>"></label>
        <label>Nomor HP / WhatsApp<input type="tel" name="no_hp" required inputmode="tel" maxlength="20" placeholder="081234567890" autocomplete="tel" value="<?= esc(old('no_hp')) ?>"></label>
        <label>Password baru
            <div style="position:relative; display:flex; align-items:center">
                <input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Min. 8 karakter, huruf &amp; angka" style="padding-right:40px; margin:0; width:100%">
                <span role="button" tabindex="0" aria-label="Tampilkan/sembunyikan password" onclick="togglePw(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();togglePw(this)}" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#666; display:flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </span>
            </div>
        </label>
        <label>Ulangi password baru
            <div style="position:relative; display:flex; align-items:center">
                <input type="password" name="password2" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Ketik ulang password baru" style="padding-right:40px; margin:0; width:100%">
                <span role="button" tabindex="0" aria-label="Tampilkan/sembunyikan password" onclick="togglePw(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();togglePw(this)}" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#666; display:flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </span>
            </div>
        </label>
        <button class="btn btn--block" type="submit">Update password</button>
    </form>
    <p class="muted"><a class="link" href="<?= site_url('masuk') ?>">&larr; Kembali ke Masuk</a></p>
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
