<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="wrap authbox" style="max-width:440px;margin:3rem auto">
    <div style="text-align:center;margin-bottom:1.8rem">
        <span class="brand__mark" style="display:inline-block;margin-bottom:.6rem;font-size:1.8rem;width:48px;height:48px;line-height:48px">S</span>
        <h1 style="font-size:1.8rem;margin:0 0 .3rem">Masuk Panel Admin</h1>
        <p class="muted small">Akses khusus pengelola Singgah Medan</p>
    </div>

    <?php if (! empty($currentUser)): ?>
        <div class="flash flash--err" style="margin-bottom:1.2rem;font-size:.85rem">
            Saat ini Anda masuk sebagai <strong><?= esc($currentUser['nama']) ?></strong> (bukan admin). Masuk dengan akun admin di bawah untuk membuka panel.
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= site_url('admin') ?>">
        <?= token_field() ?>
        <label>Email atau Username Admin
            <input type="text" name="email" required autofocus value="<?= old('email') ?>" placeholder="admin">
        </label>
        <label>Password Admin
            <input type="password" name="password" required placeholder="••••••••">
        </label>
        <button class="btn btn--block btn--gold" type="submit" style="margin-top:.6rem">Masuk ke Panel Admin</button>
    </form>

    <p class="muted small" style="text-align:center;margin-top:1.8rem">
        <a class="link" href="<?= site_url('/') ?>">← Kembali ke Beranda</a>
    </p>
</div>
<?= $this->endSection() ?>

