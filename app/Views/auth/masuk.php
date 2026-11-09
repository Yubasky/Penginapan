<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox">
    <h1>Masuk</h1>
    <form class="form" method="post" action="<?= site_url('masuk') ?>">
        <?= token_field() ?>
        <label>Email atau Username<input type="text" name="email" required autofocus value="<?= old('email') ?>"></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="btn btn--block" type="submit">Masuk</button>
    </form>
    <p class="muted">Belum punya akun? <a class="link" href="<?= site_url('daftar') ?>">Daftar</a></p>
</div>
<?= $this->endSection() ?>
