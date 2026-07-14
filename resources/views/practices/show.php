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
            <p><?= $e($practice['phone'] ?? '') ?></p>
        </section>
        <section>
            <h2><?= $e($t('catalog.sources')) ?></h2>
            <?php foreach ($practice['sources'] as $source): ?>
                <p><strong><?= $e($source['provider']) ?></strong> · <?= $e($source['enabled'] ? 'enabled' : 'disabled') ?> · <?= $e($source['check_interval_minutes']) ?> min</p>
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
