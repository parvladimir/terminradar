<section class="page">
    <h1><?= $e($t('dashboard.title')) ?></h1>
    <p><?= $e($t('dashboard.empty')) ?></p>
    <div class="tool-grid">
        <a class="tool" href="/">Neue Suche</a>
        <a class="tool" href="/datenschutz">Datenschutz</a>
        <?php if (($user['role'] ?? 'user') === 'admin'): ?><a class="tool" href="/admin">Admin</a><?php endif; ?>
    </div>
</section>
