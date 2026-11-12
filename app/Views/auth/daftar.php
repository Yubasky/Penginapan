<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox">
    <h1>Daftar</h1>
    <form class="form" method="post" action="<?= site_url('daftar') ?>">
        <?= token_field() ?>
        <label>Nama<input type="text" name="nama" required maxlength="100" value="<?= old('nama') ?>"></label>
        <label>Email<input type="email" name="email" required value="<?= old('email') ?>"></label>
        <label>Password (min. 8 karakter)<input type="password" name="password" required minlength="8"></label>
        <label>Ulangi password<input type="password" name="password2" required minlength="8"></label>
        <button class="btn btn--block" type="submit">Buat akun</button>
    </form>
    <p class="muted">Sudah punya akun? <a class="link" href="<?= site_url('masuk') ?>">Masuk</a></p>
</div>
<?= $this->endSection() ?>