<section class="auth-page">
    <h1><?= $e($t('nav.login')) ?></h1>
    <form method="post" class="form-card">
        <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
        <label><?= $e($t('auth.email')) ?><input name="email" type="email" required></label>
        <label><?= $e($t('auth.password')) ?><input name="password" type="password" required></label>
        <button class="button" type="submit"><?= $e($t('auth.login')) ?></button>
    </form>
</section>
