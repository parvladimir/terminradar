<section class="page">
    <h1><?= $e($t('catalog.title')) ?></h1>
    <form class="filters" method="get" action="/practices">
        <label>
            <?= $e($t('home.specialty')) ?>
            <select name="specialty">
                <option value=""><?= $e($t('catalog.any_specialty')) ?></option>
                <?php foreach ($specialties as $specialty): ?>
                    <option value="<?= $e($specialty['slug']) ?>" <?= ($filters['specialty'] ?? '') === $specialty['slug'] ? 'selected' : '' ?>><?= $e($specialty['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= $e($t('home.city')) ?><input name="city" value="<?= $e($filters['city'] ?? '') ?>" placeholder="Marl"></label>
        <label><?= $e($t('catalog.keyword')) ?><input name="q" value="<?= $e($filters['q'] ?? '') ?>" placeholder="Praxis"></label>
        <label>
            <?= $e($t('catalog.insurance')) ?>
            <select name="insurance">
                <option value=""></option>
                <?php foreach (['gesetzlich', 'privat', 'selbstzahler'] as $type): ?>
                    <option value="<?= $e($type) ?>" <?= ($filters['insurance'] ?? '') === $type ? 'selected' : '' ?>><?= $e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= $e($t('catalog.language')) ?><input name="language" value="<?= $e($filters['language'] ?? '') ?>" placeholder="de, uk, ru"></label>
        <button class="button" type="submit"><?= $e($t('home.find')) ?></button>
    </form>

    <div class="practice-list">
        <?php if ($practices === []): ?>
            <p class="muted-text"><?= $e($t('catalog.empty')) ?></p>
        <?php endif; ?>
        <?php foreach ($practices as $practice): ?>
            <article class="practice-card">
                <div>
                    <p class="eyebrow"><?= $e($practice['city'] ?? '') ?> <?= $e($practice['postal_code'] ?? '') ?></p>
                    <h2><a href="/practices/<?= $e($practice['id']) ?>"><?= $e($practice['name']) ?></a></h2>
                    <p><?= $e($practice['specialties'] ?: $t('catalog.no_specialty')) ?></p>
                    <p class="muted-text"><?= $e($practice['description'] ?? '') ?></p>
                </div>
                <div class="card-actions">
                    <span><?= $e($t('catalog.sources')) ?>: <?= $e($practice['source_count'] ?? 0) ?></span>
                    <a class="button small" href="/practices/<?= $e($practice['id']) ?>"><?= $e($t('catalog.open')) ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
