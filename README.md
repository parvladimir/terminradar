# TerminRadar

TerminRadar is a PHP 8.2+ web application for finding earlier medical appointments in Germany.

The current implementation starts with a Composer-compatible modular PHP architecture because Composer is not available in this workspace PATH. It is built for IONOS-style Apache/PHP hosting without a long-running Node.js process.

## Local commands

```powershell
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console migrate
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console db:seed
& 'C:\wamp64\bin\php\php8.4.0\php.exe' tests/run.php
& 'C:\wamp64\bin\php\php8.4.0\php.exe' -S 127.0.0.1:8000 -t public
```

Production should point Apache document root to `public/`.

For WAMP virtual hosts, set:

```apache
DocumentRoot "C:/wamp64/www/terminradar/public"
<Directory "C:/wamp64/www/terminradar/public">
    AllowOverride All
    Require local
</Directory>
```

The repository root also contains a fallback `index.php` and `.htaccess` so a local host that temporarily points to the project root does not expose the source tree.
