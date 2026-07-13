<section class="page narrow">
    <a href="/practices/<?= $e($slot['practice_id']) ?>" class="back-link">← <?= $e($t('catalog.back')) ?></a>
    <div class="booking-panel">
        <p class="eyebrow"><?= $e($slot['provider']) ?> · <?= $e($slot['city']) ?> <?= $e($slot['postal_code']) ?></p>
        <h1><?= $e($t('booking.title')) ?></h1>
        <dl class="slot-summary">
            <div><dt><?= $e($t('booking.practice')) ?></dt><dd><?= $e($slot['practice_name']) ?></dd></div>
            <div><dt><?= $e($t('booking.date_time')) ?></dt><dd><?= $e($slot['starts_at']) ?></dd></div>
        </dl>
        <p class="muted-text"><?= $e($t('booking.notice')) ?></p>
        <a class="button primary-strong" href="<?= $e($slot['booking_url'] ?: $slot['practice_booking_url'] ?: $slot['source_url']) ?>" target="_blank" rel="noopener"><?= $e($t('booking.official')) ?></a>
    </div>
</section>
