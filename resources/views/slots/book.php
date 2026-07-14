<section class="page narrow">
    <?php
    $rawPayload = [];
    if (!empty($slot['raw_payload'])) {
        $decoded = json_decode((string) $slot['raw_payload'], true);
        $rawPayload = is_array($decoded) ? $decoded : [];
    }
    $label = static function (string $key, string $fallback) use ($t): string {
        $translated = $t($key);
        return $translated === $key ? $fallback : $translated;
    };
    ?>
    <a href="/practices/<?= $e($slot['practice_id']) ?>" class="back-link">&larr; <?= $e($t('catalog.back')) ?></a>
    <div class="booking-panel">
        <p class="eyebrow"><?= $e($slot['provider']) ?> &middot; <?= $e($slot['city']) ?> <?= $e($slot['postal_code']) ?></p>
        <h1><?= $e($t('booking.title')) ?></h1>
        <dl class="slot-summary">
            <div><dt><?= $e($t('booking.practice')) ?></dt><dd><?= $e($slot['practice_name']) ?></dd></div>
            <div><dt><?= $e($t('booking.date_time')) ?></dt><dd><?= $e($slot['starts_at']) ?></dd></div>
            <div><dt><?= $e($label('booking.status', 'Статус')) ?></dt><dd><?= $e($slot['status']) ?></dd></div>
            <?php if (!empty($slot['source_label'])): ?><div><dt><?= $e($label('booking.source_label', 'Метка источника')) ?></dt><dd><?= $e($slot['source_label']) ?></dd></div><?php endif; ?>
            <div><dt><?= $e($label('booking.first_seen', 'Впервые найден')) ?></dt><dd><?= $e($slot['first_seen_at']) ?></dd></div>
            <div><dt><?= $e($label('booking.last_seen', 'Последний раз подтвержден')) ?></dt><dd><?= $e($slot['last_seen_at']) ?></dd></div>
            <div><dt><?= $e($label('booking.source', 'Источник')) ?></dt><dd><a href="<?= $e($slot['source_url']) ?>" target="_blank" rel="noopener"><?= $e($slot['source_url']) ?></a></dd></div>
            <?php if (!empty($slot['external_slot_id'])): ?><div><dt><?= $e($label('booking.external_id', 'ID у источника')) ?></dt><dd><?= $e($slot['external_slot_id']) ?></dd></div><?php endif; ?>
        </dl>
        <?php if ($rawPayload !== []): ?>
            <details class="raw-details">
                <summary><?= $e($label('booking.raw_details', 'Технические детали источника')) ?></summary>
                <pre><?= $e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
            </details>
        <?php endif; ?>
        <p class="muted-text"><?= $e($t('booking.notice')) ?></p>
        <a class="button primary-strong" href="<?= $e($slot['booking_url'] ?: $slot['practice_booking_url'] ?: $slot['source_url']) ?>" target="_blank" rel="noopener"><?= $e($t('booking.official')) ?></a>
    </div>
</section>
