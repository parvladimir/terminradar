<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Germany · Ukraine first · GDPR aware</p>
        <h1><?= $e($t('home.title')) ?></h1>
        <p><?= $e($t('home.subtitle')) ?></p>
    </div>
    <form class="search-panel" method="get" action="/">
        <label>
            <?= $e($t('home.specialty')) ?>
            <select name="specialty">
                <option value="">Alle / Усі</option>
                <?php foreach ($specialties as $specialty): ?>
                    <option value="<?= $e($specialty['slug']) ?>"><?= $e($specialty['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <?= $e($t('home.city')) ?>
            <input name="city" placeholder="Marl, Essen, Berlin">
        </label>
        <label>
            <?= $e($t('home.radius')) ?>
            <select name="radius">
                <option value="5">5 km</option>
                <option value="10">10 km</option>
                <option value="25">25 km</option>
                <option value="50">50 km</option>
            </select>
        </label>
        <button class="button" type="submit"><?= $e($t('home.find')) ?></button>
    </form>
</section>

<section class="band">
    <h2><?= $e($t('home.how')) ?></h2>
    <div class="steps">
        <article><strong>1</strong><span>Praxis oder Fachrichtung wählen.</span></article>
        <article><strong>2</strong><span>Zeitraum, Wochentage und Uhrzeit festlegen.</span></article>
        <article><strong>3</strong><span>Telegram, E-Mail oder Web Push aktivieren.</span></article>
    </div>
</section>

<section class="band muted">
    <h2><?= $e($t('home.security')) ?></h2>
    <p>TerminRadar speichert keine Diagnosen und keine Zugangsdaten externer medizinischer Plattformen. Die endgültige Buchung erfolgt auf der offiziellen Praxis- oder Anbieter-Seite.</p>
</section>
