<?php $this->layout('admin/layout_guest', ['title' => 'Masuk']); ?>

<div class="card">
    <div class="card-header text-center">
        <h1 class="card-title text-xl">Selamat datang kembali</h1>
        <p class="card-description">Masukkan email dan password untuk masuk ke dashboard</p>
    </div>

    <div class="card-content">
        <form method="post" action="<?= e(route('admin.login.attempt')) ?>" class="grid gap-4">
            <?= csrf_field() ?>

            <div class="grid gap-2">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="username"
                       placeholder="nama@perusahaan.com"
                       value="<?= e(flash('old_email')) ?>"
                       class="input">
            </div>

            <div class="grid gap-2">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="input">
            </div>

            <button type="submit" class="btn btn-primary mt-2 w-full">Masuk</button>
        </form>
    </div>
</div>