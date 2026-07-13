<section class="page">
    <h1><?= $e($t('watch.create')) ?></h1>
    <form method="post" action="/watches" class="form-grid">
        <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
        <input type="hidden" name="practice_id" value="<?= $e($practice['id'] ?? '') ?>">
        <label class="wide"><?= $e($t('watch.name')) ?><input name="name" required value="<?= $e(($practice['name'] ?? '') ? 'Watch: ' . $practice['name'] : '') ?>"></label>
        <label>
            <?= $e($t('home.specialty')) ?>
            <select name="specialty_id">
                <option value=""></option>
                <?php foreach ($specialties as $specialty): ?><option value="<?= $e($specialty['id']) ?>"><?= $e($specialty['name']) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label><?= $e($t('home.city')) ?><input name="city" value="<?= $e($practice['city'] ?? '') ?>"></label>
        <label>PLZ<input name="postal_code" value="<?= $e($practice['postal_code'] ?? '') ?>"></label>
        <label><?= $e($t('watch.frequency')) ?>
            <select name="frequency_minutes">
                <?php foreach ([5,10,15,30,60] as $minutes): ?><option value="<?= $minutes ?>" <?= $minutes === 15 ? 'selected' : '' ?>><?= $minutes ?> min</option><?php endforeach; ?>
            </select>
        </label>
        <label><?= $e($t('watch.earliest')) ?><input type="date" name="earliest_date"></label>
        <label><?= $e($t('watch.latest')) ?><input type="date" name="latest_date"></label>
        <label><?= $e($t('watch.before')) ?><input type="date" name="desired_before_date"></label>
        <label><?= $e($t('watch.expires')) ?><input type="date" name="expires_at"></label>
        <label><?= $e($t('watch.time_from')) ?><input type="time" name="time_from"></label>
        <label><?= $e($t('watch.time_to')) ?><input type="time" name="time_to"></label>
        <label><?= $e($t('catalog.insurance')) ?>
            <select name="insurance_type">
                <option value=""></option>
                <option value="gesetzlich">gesetzlich</option>
                <option value="privat">privat</option>
                <option value="selbstzahler">selbstzahler</option>
            </select>
        </label>
        <div class="wide check-row">
            <label class="check"><input type="checkbox" name="notification_email" value="1" checked> Email</label>
            <label class="check"><input type="checkbox" name="notification_telegram" value="1"> Telegram</label>
            <label class="check"><input type="checkbox" name="notification_web_push" value="1"> Web Push</label>
            <label class="check"><input type="checkbox" name="only_new_patients" value="1"> <?= $e($t('watch.new_patients')) ?></label>
        </div>
        <button class="button wide" type="submit"><?= $e($t('watch.save')) ?></button>
    </form>
</section>
