# TerminRadar Architecture

## Environment Decision

The target production environment is IONOS with PHP 8.2+, Apache, MySQL/MariaDB and cron. The current workspace has WAMP PHP 8.4 available at `C:\wamp64\bin\php\php8.4.0\php.exe`, but `php` and `composer` are not available in PATH. Because a network Composer install is not guaranteed here, stage 1 uses a Composer-compatible modular PHP architecture with a checked-in lightweight PSR-4 autoloader.

If Composer becomes available later, the project can run `composer dump-autoload` and replace the checked-in fallback autoloader. The code is kept framework-shaped so a future Laravel migration remains straightforward: controllers, repositories, services, migrations, seeders, commands and views are already separated.

## Proposed Directory Structure

```text
app root
  bin/console                  CLI entrypoint for migrations, seeders and appointment commands
  bootstrap/app.php            Application bootstrap and service wiring
  config/*.php                 App, database and route configuration
  database/migrations/*.php    Versioned PDO migrations
  database/seeders/*.php       Initial specialties, cities, source and admin user
  lang/{uk,de,ru}/*.php        Interface translations
  public/index.php             Apache document root entrypoint
  public/assets/*              Static CSS and JavaScript
  resources/views/**/*.php     Escaped PHP templates
  src/Core/*                   Router, request, response, DB, sessions, CSRF, validation
  src/Controllers/*            Web controllers
  src/Models/*                 Domain model objects
  src/Repositories/*           PDO data access
  src/Services/*               Business services
  src/Providers/*              Appointment source adapter contracts and DTOs
  storage/*                    SQLite, logs, cache, sessions and uploads
  tests/*                      Dependency-free test runner and fixtures
```

## Layers

- `Core`: request lifecycle, routing, config, environment loading, database access, view rendering, localization, sessions, CSRF and validation.
- `Controllers`: thin HTTP layer. Controllers validate input and call repositories/services.
- `Repositories`: all SQL lives here or in migrations. Repositories use prepared PDO statements.
- `Services`: authentication, provider matching, notification dispatching and monitoring logic.
- `Providers`: `AppointmentProviderInterface` allows `DocVisitProviderAdapter`, `GenericHtmlProviderAdapter` and `ManualProviderAdapter` to return normalized slot DTOs.
- `Commands`: CLI handlers exposed through `bin/console`, designed for IONOS cron.

## Database

The schema mirrors the product specification: specialties, practices, doctors, appointment types, appointment sources, appointment slots, users, watches, watch matches, notifications and provider logs. Indexes are added for city, postal code, provider, external IDs, slot start time, active watches, `last_seen_at` and `user_id`.

SQLite is supported for local development and tests. MySQL/MariaDB is the production driver.

## Security

- Passwords use `password_hash()` and `password_verify()`.
- All forms include CSRF tokens.
- Output is escaped by the view helper.
- Sessions use secure cookie settings when HTTPS is active.
- Application secrets remain in `.env`.
- Medical diagnoses and external platform credentials are outside the data model.
- Booking is never automated in MVP; users are sent to official booking pages.

## Localization

Ukrainian is the default locale. German and Russian are available via `lang/de` and `lang/ru`. Locale is stored in session for guests and in the `users.locale` column for registered users.

## Background Checks

IONOS cron runs:

```bash
php /full/path/bin/console schedule:run
```

The scheduler selects due sources and calls `appointments:check-source`. Locking is implemented through the database-backed `source_locks` table so overlapping cron runs do not process the same source concurrently.

## Provider Ethics

Adapters must respect public page availability, robots.txt, Terms of Service, rate limits and official APIs. They must not bypass CAPTCHA, authentication or access restrictions. Empty or failed responses are logged and never trigger mass disappearance of previously known slots without confirmation.
