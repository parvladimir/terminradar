<section class="page detail">
    <a href="/practices" class="back-link">← <?= $e($t('catalog.back')) ?></a>
    <div class="detail-header">
        <div>
            <p class="eyebrow"><?= $e($practice['city'] ?? '') ?> <?= $e($practice['postal_code'] ?? '') ?></p>
            <h1><?= $e($practice['name']) ?></h1>
            <p><?= $e($practice['description'] ?? '') ?></p>
        </div>
        <div class="detail-actions">
            <a class="button secondary" href="/watches/create?practice_id=<?= $e($practice['id']) ?>"><?= $e($t('watch.track')) ?></a>
            <?php if (!empty($practice['booking_url'])): ?><a class="button" href="<?= $e($practice['booking_url']) ?>" rel="noopener" target="_blank"><?= $e($t('catalog.booking')) ?></a><?php endif; ?>
            <?php if (!empty($practice['website_url'])): ?><a class="button secondary" href="<?= $e($practice['website_url']) ?>" rel="noopener" target="_blank"><?= $e($t('catalog.website')) ?></a><?php endif; ?>
        </div>
    </div>

    <div class="detail-grid">
        <section>
            <h2><?= $e($t('catalog.specialties')) ?></h2>
            <div class="chips">
                <?php foreach ($practice['specialties_list'] as $specialty): ?><span><?= $e($specialty['name']) ?></span><?php endforeach; ?>
                <?php if ($practice['specialties_list'] === []): ?><span><?= $e($t('catalog.no_specialty')) ?></span><?php endif; ?>
            </div>
        </section>
        <section>
            <h2><?= $e($t('catalog.contact')) ?></h2>
            <p><?= $e(trim(($practice['street'] ?? '') . ' ' . ($practice['house_number'] ?? ''))) ?></p>
            <p><?= $e(trim(($practice['postal_code'] ?? '') . ' ' . ($practice['city'] ?? ''))) ?></p>
            <?php if (!empty($practice['phone'])): ?><p><?= $e($t('catalog.phone')) ?>: <?= $e($practice['phone']) ?></p><?php endif; ?>
            <?php if (!empty($practice['email'])): ?><p><?= $e($t('catalog.email')) ?>: <a href="mailto:<?= $e($practice['email']) ?>"><?= $e($practice['email']) ?></a></p><?php endif; ?>
            <?php if ((int) ($practice['wheelchair_accessible'] ?? 0) === 1): ?><p><?= $e($t('catalog.accessible')) ?></p><?php endif; ?>
        </section>
        <section>
            <h2><?= $e($t('catalog.sources')) ?></h2>
            <?php foreach ($practice['sources'] as $source): ?>
                <article class="source-card">
                    <div class="source-head">
                        <strong><?= $e($source['provider']) ?></strong>
                        <span class="<?= $source['enabled'] ? 'status active' : 'status paused' ?>"><?= $e($source['enabled'] ? $t('catalog.source_enabled') : $t('catalog.source_disabled')) ?></span>
                    </div>
                    <p><a href="<?= $e($source['source_url']) ?>" target="_blank" rel="noopener"><?= $e($source['source_url']) ?></a></p>
                    <p><?= $e($t('catalog.check_interval')) ?>: <?= $e($source['check_interval_minutes']) ?> min</p>
                    <p><?= $e($t('catalog.last_success')) ?>: <?= $e($source['last_success_at'] ?: '-') ?></p>
                    <p><?= $e($t('catalog.last_error')) ?>: <?= $e($source['last_error_message'] ?: '-') ?></p>
                    <p><?= $e($t('catalog.saved_slots')) ?>: <?= $e($source['slot_count'] ?? 0) ?></p>
                    <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
                        <form method="post" action="/admin/sources/<?= $e($source['id']) ?>/check">
                            <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                            <button class="button tiny" type="submit"><?= $e($t('catalog.check_now')) ?></button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
        <section>
            <h2><?= $e($t('catalog.slots')) ?></h2>
            <p class="muted-text"><?= $e($t('catalog.source_note')) ?></p>
            <?php if ($practice['slots'] === []): ?><p class="muted-text"><?= $e($t('catalog.no_slots')) ?></p><?php endif; ?>
            <?php foreach ($practice['slots'] as $slot): ?>
                <div class="slot-row">
                    <span><?= $e($slot['starts_at']) ?></span>
                    <a class="button tiny" href="/slots/<?= $e($slot['id']) ?>/book"><?= $e($t('catalog.booking')) ?></a>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</section>
