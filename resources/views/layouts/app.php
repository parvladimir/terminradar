<!doctype html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TerminRadar</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/">TerminRadar</a>
    <nav class="nav">
        <a href="/dashboard"><?= $e($t('nav.dashboard')) ?></a>
        <a href="/login"><?= $e($t('nav.login')) ?></a>
        <a class="button small" href="/register"><?= $e($t('nav.register')) ?></a>
        <form method="post" action="/locale" class="locale-form">
            <input type="hidden" name="_token" value="<?= $e($csrf()) ?>">
            <input type="hidden" name="redirect" value="<?= $e($_SERVER['REQUEST_URI'] ?? '/') ?>">
            <select name="locale" onchange="this.form.submit()" aria-label="Language">
                <?php foreach (['uk' => 'UA', 'de' => 'DE', 'ru' => 'RU'] as $code => $label): ?>
                    <option value="<?= $e($code) ?>" <?= $locale === $code ? 'selected' : '' ?>><?= $e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </nav>
</header>

<?php if ($flashSuccess): ?><div class="flash success"><?= $e($flashSuccess) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="flash error"><?= $e($flashError) ?></div><?php endif; ?>

<main>
    <?= $content ?>
</main>

<footer class="footer">
    <a href="/impressum">Impressum</a>
    <a href="/datenschutz">Datenschutzerklärung</a>
    <a href="/terms">Nutzungsbedingungen</a>
    <a href="/cookies">Cookie-Einstellungen</a>
    <a href="/haftung">Haftungsausschluss</a>
</footer>
<script src="/assets/app.js" defer></script>
</body>
</html>
