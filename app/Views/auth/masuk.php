<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox">
    <h1>Masuk</h1>
    <form class="form" method="post" action="<?= site_url('masuk') ?>">
        <?= token_field() ?>
        <label>Email atau Username<input type="text" name="email" required autofocus value="<?= old('email') ?>"></label>
        <label>Password
            <div style="position:relative; display:flex; align-items:center">
                <input type="password" name="password" required style="padding-right:40px; margin:0; width:100%">
                <span onclick="togglePw(this)" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#666; display:flex">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </span>
            </div>
        </label>
        <button class="btn btn--block" type="submit">Masuk</button>
    </form>
    <p class="muted">Belum punya akun? <a class="link" href="<?= site_url('daftar') ?>">Daftar</a></p>
</div>

<script>
    function togglePw(el) {
        const i = el.previousElementSibling;
        const buka = i.type === 'password';
        i.type = buka ? 'text' : 'password';
        el.innerHTML = buka ?
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>' :
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    }
</script>
<?= $this->endSection() ?>