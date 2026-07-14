# TerminRadar Deployment on IONOS

## Requirements

- IONOS hosting plan with PHP 8.2 or newer.
- MySQL or MariaDB database.
- Apache with `.htaccess` and rewrite support.
- Cron Jobs.
- HTTPS certificate enabled for the domain.

## Document Root

Set the domain document root to:

```text
/path/to/terminradar/public
```

If IONOS temporarily points to the project root, the root `.htaccess` forwards traffic to `public/index.php`, but production should use `public`.

## Database

Create a MySQL database in IONOS and put the values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=your-ionos-db-host
DB_PORT=3306
DB_DATABASE_MYSQL=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Local WAMP development uses:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE_MYSQL=terminradar
DB_USERNAME=root
DB_PASSWORD=
```

## Environment

Copy `.env.example` to `.env` and set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.de
APP_KEY=generate-a-long-random-secret
ADMIN_EMAIL=admin@example.de
ADMIN_PASSWORD=change-this-before-first-login
MAIL_FROM_ADDRESS=no-reply@your-domain.de
TELEGRAM_BOT_TOKEN=
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

Never commit `.env`.

## Install

Upload the project files, then run:

```bash
php bin/console migrate
php bin/console db:seed
```

The admin seeder ensures the admin user from `.env` exists and resets that admin password to the `.env` value.

## Permissions

Make these directories writable by PHP:

```text
storage/
storage/logs/
storage/cache/
storage/sessions/
storage/uploads/
```

## Cron

Run the scheduler every minute if IONOS allows it:

```bash
php /full/path/to/terminradar/bin/console schedule:run
```

If one-minute cron is unavailable, run every five minutes. Source-level intervals are still respected by TerminRadar.

For a specific source during diagnostics:

```bash
php /full/path/to/terminradar/bin/console appointments:check-source 1
```

## Email

Configure SMTP credentials in `.env` before enabling real email delivery. Do not log SMTP passwords.

## Telegram

Create a Telegram bot with BotFather, put the token in `TELEGRAM_BOT_TOKEN`, and complete the webhook or polling integration in the notification transport stage.

## Web Push

Generate VAPID keys and put them in `.env`. Browser permission must only be requested after an explicit user action.

## Legal Pages

Before production, replace placeholder operator details on:

- `/impressum`
- `/datenschutz`
- `/terms`
- `/cookies`
- `/haftung`

TerminRadar must clearly state that it is not a doctor, does not guarantee slot availability, does not provide emergency care, and that emergencies use `112` while ärztlicher Bereitschaftsdienst uses `116117`.

## API v1

The mobile-ready API is under:

```text
/api/v1/
```

Authentication uses bearer tokens returned by:

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
```

Protected requests send:

```http
Authorization: Bearer <token>
```
