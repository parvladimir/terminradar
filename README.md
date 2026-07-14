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

Tests use `storage/testing.sqlite` so they do not reset the local development database.

## Appointment checks

```powershell
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console appointments:check
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console appointments:check-source 1
```

`appointments:check` processes enabled sources that are due. `appointments:check-source 1` runs the seeded DocVisit source directly and stores normalized slots in `appointment_slots`.

## How monitoring is visible locally

1. Create a Watch from a Praxis page or `/watches/create`.
2. Run:

```powershell
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console appointments:check-source 1
```

3. Open `/dashboard`.

The dashboard shows:

- active Watches;
- notification channel status;
- matches from `watch_matches`;
- notification records from `notifications`.

Telegram and Web Push need a transport connection before real external delivery. Until then, TerminRadar still creates in-app notification records so the matching result is visible.

Current seeded appointments come from the public DocVisit calendar URL stored in `appointment_sources` for the test Praxis. They are fetched live by `appointments:check-source 1` and then stored in `appointment_slots`.

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

## Local database

For WAMP/phpMyAdmin, use MySQL:

```powershell
& 'C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe' -uroot -e "CREATE DATABASE IF NOT EXISTS terminradar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console migrate
& 'C:\wamp64\bin\php\php8.4.0\php.exe' bin/console db:seed
```

The database will appear in phpMyAdmin as `terminradar`.
