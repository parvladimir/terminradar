<section class="page">
    <h1><?= $e($t('dashboard.title')) ?></h1>
    <?php if (($watches ?? []) === []): ?><p><?= $e($t('dashboard.empty')) ?></p><?php endif; ?>
    <div class="tool-grid">
        <a class="tool" href="/">Neue Suche</a>
        <a class="tool" href="/watches/create"><?= $e($t('watch.create')) ?></a>
        <a class="tool" href="/datenschutz">Datenschutz</a>
        <?php if (($user['role'] ?? 'user') === 'admin'): ?><a class="tool" href="/admin">Admin</a><?php endif; ?>
    </div>
    <?php if (($watches ?? []) !== []): ?>
        <div class="watch-list">
            <?php foreach ($watches as $watch): ?>
                <article class="watch-card">
                    <div>
                        <h2><?= $e($watch['name']) ?></h2>
                        <p><?= $e($watch['practice_name'] ?? $watch['city'] ?? '') ?> · <?= $e($watch['earliest_date'] ?? '') ?> – <?= $e($watch['latest_date'] ?? '') ?> · <?= $e($watch['time_from'] ?? '') ?> <?= $watch['time_to'] ? '– ' . $e($watch['time_to']) : '' ?></p>
                        <span class="<?= $watch['active'] ? 'status active' : 'status paused' ?>"><?= $e($watch['active'] ? $t('watch.active') : $t('watch.paused')) ?></span>
                    </div>
                    <div class="card-actions">
                        <form method="post" action="/watches/<?= $e($watch['id']) ?>/<?= $watch['active'] ? 'pause' : 'resume' ?>">
                            <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                            <button class="button tiny secondary" type="submit"><?= $e($watch['active'] ? $t('watch.pause') : $t('watch.resume')) ?></button>
                        </form>
                        <form method="post" action="/watches/<?= $e($watch['id']) ?>/delete">
                            <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                            <button class="button tiny danger" type="submit"><?= $e($t('watch.delete')) ?></button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
