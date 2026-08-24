# Laravel Master Starter

Reusable domain-neutral Laravel 12 boilerplate built on Inertia, Vue, Fortify, Sanctum, and Spatie Permission.

## Stack

- Laravel 12
- PHP 8.5
- Inertia v2 + Vue 3 + TypeScript
- Laravel Fortify for auth
- Sanctum for API tokens
- Spatie Permission for RBAC
- Pest 4 for testing
- Wayfinder for typed frontend routes

## Current Baseline

- Auth flows:
  - login
  - registration
  - password reset
  - email verification
  - two-factor authentication
  - profile and security settings
- Admin shell:
  - sidebar
  - header actions
  - page header/container primitives
  - flash messages
- RBAC:
  - roles
  - permissions
  - role-based sidebar and route access
  - user role assignment
- Admin modules:
  - users CRUD
  - roles CRUD
- Cross-cutting modules:
  - notifications center
  - activity log
  - audit detail page
  - global search
  - media library with upload/download/delete and automatic image thumbnails (requires the PHP GD extension; gracefully skipped otherwise)
  - shared settings registry and admin settings UI
  - dashboard shell with generic metrics
  - export center: CSV, XLSX, XML, print view, and PDF summary
  - generic CSV import engine (`App\Support\Import\CsvImportEngine`) with preview/validation and `import_runs` history
- Localization:
  - per-user locale preference on the profile screen
  - `lang/<code>/messages.php` structure exposed to the frontend via a shared `translations` prop and the `useT()` composable
  - add a language by creating the lang folder and registering it in `App\Support\Locales`
- API baseline:
  - `/api/v1/auth/*`
  - `/api/v1/notifications`
  - `/api/v1/activity-logs`
  - `/api/v1/admin/users`
  - `/api/v1/admin/summary`
  - `/api/v1/media`

## Local Setup

### Requirements

- PHP 8.5+
- Composer
- Node.js 22+
- npm
- SQLite, PostgreSQL, or MySQL

### Install

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

If you use Laravel Herd, update `APP_URL` in `.env` to your Herd domain before browser testing.

### Database

For SQLite:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

For PostgreSQL or MySQL:

1. Update the database values in `.env`
2. Run:

```bash
php artisan migrate --seed
```

### Frontend

For local development:

```bash
npm run dev
```

If you are using Laravel Herd, open the Herd project URL in the browser. You do not need `php artisan serve`.

If you want the multi-process local workflow outside Herd, you can also run:

```bash
composer run dev
```

### Baseline Verification

```bash
php artisan test --compact
npm run types:check
npm run build
```

## Default Roles

After `php artisan migrate --seed`, these system roles exist:

- `Super Admin` — full access with a global authorization bypass
- `Manager` — operational visibility plus user administration
- `Staff` — dashboard and notification access
- `Guest` — authenticated, minimal access; no dashboard or administrative surfaces

## Seeded Account

After `php artisan migrate --seed`, this account is available:

- `admin@example.com` / `password` (Super Admin)

Change the password before any shared or production deployment.

## API Usage

The API uses Sanctum bearer tokens.

Main flow:

1. `POST /api/v1/auth/login`
2. Copy the returned bearer token
3. Call protected endpoints with `Authorization: Bearer <token>`

An importable Postman collection is included at [master-starter-api.postman_collection.json](./master-starter-api.postman_collection.json).

## Import Engine

The reusable CSV import engine validates first, persists second:

```php
use App\Support\Import\CsvImportEngine;
use App\Support\Import\ImportRowError;

$engine = new CsvImportEngine;

$preview = $engine->preview(
    $request->file('file')->get(),
    'users',
    function (array $row, int $rowNumber): ?ImportRowError {
        if (($row['email'] ?? '') === '') {
            return new ImportRowError('Email is required.');
        }

        return null;
    },
    expectedHeader: ['name', 'email'],
);

if ($preview->isCommittable()) {
    $run = $engine->commit($preview, auth()->user(), function (array $row): void {
        // persist one validated row
    });
}
```

Every commit is recorded in `import_runs` with row counts, per-row errors,
and a preview snapshot. UI wiring is intentionally left to each project.

## Operations Notes

### Queue

- Default queue driver: `database`
- For local async queue processing:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Scheduler

There are no required scheduled tasks yet, but production deployments should still register Laravel's scheduler:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

### Mail

- Local default mailer is `log`
- For local inbox testing, switch `.env` to Mailpit or another SMTP sink

Example:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### Deployment Baseline

At minimum, production should run:

- `php artisan migrate --force`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `npm run build`

And supervise:

- PHP/FPM or Octane
- queue worker
- scheduler

### Deployment Targets

- Laravel Cloud:
  - good fit for this starter
  - set production env values, run migrations on deploy, and enable queue plus scheduler support
- Laravel Forge:
  - good fit for this starter
  - configure PHP, queue worker, scheduler, and deployment script with the baseline commands above
- Generic VPS such as GoDaddy:
  - workable only if you control PHP/FPM, cron, queue workers, and deployment steps yourself
  - shared hosting is not a good target for this starter

## CI

GitHub Actions verifies:

- PHP formatting with Pint
- frontend formatting
- ESLint
- TypeScript checks
- production asset build
- Pest test suite

## Project Workflow

- Use one branch per roadmap phase
- Keep `TheRoadmap/BoilerplateTaskList.md` in sync
- Record implementation learning in `TheRoadmap/laravelbasics.md`

## Useful Commands

```bash
composer setup
composer lint
composer lint:check
php artisan test --compact
npm run lint:check
npm run types:check
npm run build
```
