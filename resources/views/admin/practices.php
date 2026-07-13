<section class="page">
    <h1><?= $e($t('admin.practices')) ?></h1>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
        <label><?= $e($t('admin.practice_name')) ?><input name="name" required></label>
        <label>
            <?= $e($t('home.specialty')) ?>
            <select name="specialty_id">
                <option value=""></option>
                <?php foreach ($specialties as $specialty): ?><option value="<?= $e($specialty['id']) ?>"><?= $e($specialty['name']) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>PLZ<input name="postal_code" required></label>
        <label><?= $e($t('home.city')) ?><input name="city" required></label>
        <label>Street<input name="street"></label>
        <label>No.<input name="house_number"></label>
        <label>Phone<input name="phone"></label>
        <label>Email<input name="email" type="email"></label>
        <label>Website URL<input name="website_url" type="url"></label>
        <label>Booking URL<input name="booking_url" type="url"></label>
        <label>Provider
            <select name="provider">
                <option value="manual">manual</option>
                <option value="docvisit">docvisit</option>
                <option value="generic_html">generic_html</option>
            </select>
        </label>
        <label>Source URL<input name="source_url" type="url"></label>
        <label>External ID<input name="external_calendar_id"></label>
        <label>Interval minutes<input name="check_interval_minutes" type="number" min="5" step="5" value="15"></label>
        <label>Insurance<input name="insurance_types" value="gesetzlich, privat, selbstzahler"></label>
        <label>Languages<input name="languages" value="de"></label>
        <label class="check"><input type="checkbox" name="is_verified" value="1"> Verified</label>
        <label class="check"><input type="checkbox" name="source_enabled" value="1"> Enable source after validation</label>
        <label class="check"><input type="checkbox" name="is_test_data" value="1"> Test data</label>
        <label class="wide">Description<textarea name="description" rows="3"></textarea></label>
        <button class="button wide" type="submit"><?= $e($t('admin.create_practice')) ?></button>
    </form>

    <h2><?= $e($t('catalog.title')) ?></h2>
    <div class="practice-list compact">
        <?php foreach ($practices as $practice): ?>
            <article class="practice-card">
                <div><strong><?= $e($practice['name']) ?></strong><p><?= $e($practice['postal_code']) ?> <?= $e($practice['city']) ?></p></div>
                <a class="button small" href="/practices/<?= $e($practice['id']) ?>"><?= $e($t('catalog.open')) ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
