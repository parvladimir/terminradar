<section class="page detail">
    <?php
    $slotGroups = [];
    $weekdayNames = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        7 => 'Воскресенье',
    ];
    foreach ($practice['slots'] as $slot) {
        $date = new DateTimeImmutable((string) $slot['starts_at']);
        $key = $date->format('Y-m-d');
        if (!isset($slotGroups[$key])) {
            $slotGroups[$key] = [
                'label' => $weekdayNames[(int) $date->format('N')] . ', ' . $date->format('d.m.Y'),
                'slots' => [],
            ];
        }
        $slotGroups[$key]['slots'][] = $slot + ['time_label' => $date->format('H:i')];
    }
    ?>
    <a href="/practices" class="back-link">&larr; <?= $e($t('catalog.back')) ?></a>
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
        <section class="slots-section">
            <div class="slots-heading">
                <div>
                    <h2><?= $e($t('catalog.slots')) ?></h2>
                    <p class="muted-text"><?= $e($t('catalog.source_note')) ?></p>
                </div>
                <span class="slot-count"><?= count($practice['slots']) ?> терминов</span>
            </div>
            <?php if ($practice['slots'] === []): ?><p class="muted-text"><?= $e($t('catalog.no_slots')) ?></p><?php endif; ?>
            <div class="slot-days">
                <?php foreach ($slotGroups as $group): ?>
                    <article class="slot-day">
                        <div class="slot-day-head">
                            <h3><?= $e($group['label']) ?></h3>
                            <span><?= count($group['slots']) ?> слотов</span>
                        </div>
                        <div class="slot-time-grid">
                            <?php foreach ($group['slots'] as $slot): ?>
                                <a class="slot-time" href="<?= $e($slot['booking_url'] ?: '/slots/' . $slot['id'] . '/book') ?>" target="_blank" rel="noopener" title="<?= $e($slot['source_label'] ?? '') ?>">
                                    <strong><?= $e($slot['time_label']) ?></strong>
                                    <span>DocVisit</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
