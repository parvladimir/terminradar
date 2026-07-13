<section class="auth-page">
    <h1><?= $e($t('nav.register')) ?></h1>
    <form method="post" class="form-card">
        <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
        <label><?= $e($t('auth.name')) ?><input name="name" required minlength="2"></label>
        <label><?= $e($t('auth.email')) ?><input name="email" type="email" required></label>
        <label><?= $e($t('auth.password')) ?><input name="password" type="password" required minlength="10"></label>
        <label class="check"><input type="checkbox" name="privacy" value="1" required> <?= $e($t('auth.privacy')) ?></label>
        <button class="button" type="submit"><?= $e($t('auth.create')) ?></button>
    </form>
</section>
