# TerminRadar Implementation Plan

## Stage 1: Foundation

- Create architecture docs and deployment assumptions.
- Build PHP 8.2+ modular app skeleton.
- Add config, environment loading, routing, sessions, CSRF and localization.
- Add database migrations for the full MVP schema.
- Add repositories and models for users, specialties, practices and watches.
- Add registration, login, logout and protected dashboard.
- Add base UI with Ukrainian default locale plus German and Russian translations.
- Add seeders for specialties, German federal states, target cities, Urologie Marl source and admin user from environment.
- Add dependency-free tests for bootstrap, translations, migrations, seed data and auth hashing.

## Stage 2: Catalog and Admin

- Add Praxis and doctor catalog pages with filters.
- Add practice detail cards with appointment types and known slots.
- Add protected admin dashboard sections for users, doctors, practices, specialties, appointment types and sources.
- Add CSV import for practices and source validation preview.

## Stage 3: Providers and Monitoring

- Implement `AppointmentProviderInterface`.
- Implement `DocVisitProviderAdapter` for public DocVisit list pages.
- Implement `GenericHtmlProviderAdapter` and `ManualProviderAdapter`.
- Add appointment discovery, slot persistence, provider logs and source locks.
- Add safeguards for empty responses, temporary errors and mass disappearance confirmation.

## Stage 4: Watches and Notifications

- Add watch creation wizard.
- Implement watch matching for date, time, weekday, specialty, practice, doctor, insurance and new-patient filters.
- Implement Telegram linking with verification codes.
- Add email notifications.
- Add Web Push subscriptions and service worker flow after explicit user action.
- Add match history and notification deduplication.

## Stage 5: API, GDPR, Tests and IONOS

- Add `/api/v1` endpoints with stable JSON resources.
- Add token auth compatible with future mobile clients.
- Add account export and deletion.
- Add Impressum, Datenschutzerklärung, Nutzungsbedingungen, Cookie-Einstellungen and Haftungsausschluss pages.
- Expand unit and feature tests.
- Create `DEPLOYMENT_IONOS.md` with database, cron, queue, email, Telegram and Web Push setup.

## Commit Strategy

Use small commits by stage: docs, framework skeleton, schema, auth/localization/UI, seeders/tests, provider monitoring, watches, notifications and API.
