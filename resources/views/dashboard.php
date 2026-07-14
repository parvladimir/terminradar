<section class="page">
    <h1><?= $e($t('dashboard.title')) ?></h1>
    <div class="info-strip">
        <strong><?= $e($t('dashboard.flow_title')) ?></strong>
        <span><?= $e($t('dashboard.flow_text')) ?></span>
    </div>
    <?php if (($watches ?? []) === []): ?><p><?= $e($t('dashboard.empty')) ?></p><?php endif; ?>
    <div class="tool-grid">
        <a class="tool" href="/">Neue Suche</a>
        <a class="tool" href="/watches/create"><?= $e($t('watch.create')) ?></a>
        <a class="tool" href="/datenschutz">Datenschutz</a>
        <?php if (($user['role'] ?? 'user') === 'admin'): ?><a class="tool" href="/admin">Admin</a><?php endif; ?>
    </div>

    <section class="dashboard-section">
        <h2><?= $e($t('notifications.channels')) ?></h2>
        <div class="channel-grid">
            <article>
                <strong>Email</strong>
                <span class="<?= $user['email_notifications_enabled'] ? 'status active' : 'status paused' ?>"><?= $e($user['email_notifications_enabled'] ? $t('notifications.enabled') : $t('notifications.disabled')) ?></span>
                <form method="post" action="/notifications/test/email">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.test')) ?></button>
                </form>
            </article>
            <article>
                <strong>Telegram</strong>
                <span class="<?= !empty($user['telegram_chat_id']) ? 'status active' : 'status paused' ?>"><?= $e(!empty($user['telegram_chat_id']) ? $t('notifications.connected') : $t('notifications.not_connected')) ?></span>
                <?php if (!empty($user['telegram_link_code'])): ?><p class="muted-text"><?= $e($t('notifications.code')) ?>: <strong><?= $e($user['telegram_link_code']) ?></strong></p><?php endif; ?>
                <p class="muted-text"><?= $e($t('notifications.telegram_help')) ?></p>
                <form method="post" action="/telegram/link-code">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.get_code')) ?></button>
                </form>
                <form method="post" action="/telegram/confirm-local" class="inline-confirm">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <input name="code" placeholder="<?= $e($t('notifications.code')) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.confirm_local')) ?></button>
                </form>
                <form method="post" action="/notifications/test/telegram">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.test')) ?></button>
                </form>
            </article>
            <article>
                <strong>Web Push</strong>
                <span class="<?= $user['web_push_enabled'] ? 'status active' : 'status paused' ?>"><?= $e($user['web_push_enabled'] ? $t('notifications.enabled') : $t('notifications.not_connected')) ?></span>
                <form method="post" action="/push/enable-local">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.enable_local')) ?></button>
                </form>
                <form method="post" action="/notifications/test/web_push">
                    <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
                    <button class="button tiny secondary" type="submit"><?= $e($t('notifications.test')) ?></button>
                </form>
            </article>
        </div>
    </section>

    <?php if (($watches ?? []) !== []): ?>
        <section class="dashboard-section">
        <h2><?= $e($t('watch.list')) ?></h2>
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
        </section>
    <?php endif; ?>

    <section class="dashboard-section">
        <h2><?= $e($t('notifications.matches')) ?></h2>
        <?php if (($matches ?? []) === []): ?><p class="muted-text"><?= $e($t('notifications.no_matches')) ?></p><?php endif; ?>
        <?php foreach (($matches ?? []) as $match): ?>
            <article class="event-row">
                <div>
                    <strong><?= $e($match['practice_name']) ?></strong>
                    <p><?= $e($match['watch_name']) ?> · <?= $e($match['starts_at']) ?></p>
                </div>
                <a class="button tiny" href="/slots/<?= $e($match['slot_id']) ?>/book"><?= $e($t('catalog.booking')) ?></a>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="dashboard-section">
        <h2><?= $e($t('notifications.log')) ?></h2>
        <?php if (($notifications ?? []) === []): ?><p class="muted-text"><?= $e($t('notifications.no_notifications')) ?></p><?php endif; ?>
        <?php foreach (($notifications ?? []) as $notification): ?>
            <article class="event-row">
                <div>
                    <strong><?= $e($notification['channel']) ?> · <?= $e($notification['status']) ?></strong>
                    <p><?= $e($notification['subject']) ?></p>
                    <?php if (!empty($notification['error_message'])): ?><p class="error-text"><?= $e($notification['error_message']) ?></p><?php endif; ?>
                </div>
                <?php if (!empty($notification['appointment_slot_id'])): ?><a href="/slots/<?= $e($notification['appointment_slot_id']) ?>/book"><?= $e($t('catalog.booking')) ?></a><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
</section>
