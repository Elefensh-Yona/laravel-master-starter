# Laravel Master Starter — Complete Architecture & Feature Inventory

> Authoritative reference for the current, finalized codebase.
> Generated from a fresh investigation of this repository. Every claim below was
> verified against the actual source files unless explicitly marked otherwise.

---

## 1. Executive Summary

This project is the **Laravel Master Starter** (composer package name
`elefensh-yona/laravel-master-starter`): a reusable, **domain-neutral** Laravel 12
boilerplate providing everything a new internal tool or business application needs
on day one — authentication, RBAC, user/role administration, notifications,
audit logging, media library, shared settings, global search, import/export, and a
versioned JSON API baseline — plus a complete Vue 3 admin shell.

**Architectural purpose:** it is a starting point, not a product. It contains no
business domain entities. Future projects clone this repository and add their own
domain modules on top of the reusable core.

**Designed to support:** internal business applications, admin panels, back-office
tools, ERP/CRM-style systems, and any project that needs authenticated multi-role
users, audited actions, file management, and settings-driven configuration.

**Major technologies:** PHP 8.5 / Laravel 12, Inertia v2 + Vue 3 + TypeScript,
Tailwind CSS v4, Vite 7, Fortify (headless auth), Sanctum (API tokens), Spatie
Permission (RBAC), Pest 4 (testing).

**Major reusable capabilities:**

- Complete Fortify authentication flows with 2FA, recovery codes, and password confirmation
- Role-based access control with protected system roles and permission-seeded defaults
- User management CRUD with self-protection rules
- Permission-aware sidebar navigation and shared frontend capability props
- Database notification center with unread badge and preview
- Activity/audit logging with actor + polymorphic subject tracking
- Media library with uploads, downloads, deletion, and optional image thumbnails
- Settings registry with admin UI and Inertia-shared values
- Global search across modules (permission-filtered)
- Export center (CSV/XLSX/XML/print/PDF) and a two-phase CSV import engine
- API v1 baseline with Sanctum bearer tokens, resources, and consistent pagination

**Intentionally NOT included:** pages/CMS, public website, notes, reports,
handbook application, departments/team scoping, project-specific business
entities or integrations, enterprise reporting. See Section 28.

---

## 2. Technology Stack

Versions below were read from `composer.json`, `package.json`, and the local
environment (`php -v` equivalent recorded in AGENTS.md).

### Backend

| Technology | Version | Notes |
|---|---|---|
| PHP | ^8.2 required; **8.5.4 running locally** | CI uses PHP 8.5 |
| Laravel Framework | ^12.0 | Laravel 12 streamlined structure |
| laravel/fortify | ^1.30 | Headless authentication backend |
| laravel/sanctum | ^4.0 | API personal access tokens |
| spatie/laravel-permission | ^7.0 | RBAC (roles + permissions) |
| inertiajs/inertia-laravel | ^2.0 | Inertia server adapter |
| laravel/wayfinder | ^0.1.9 | TypeScript route generation |
| barryvdh/laravel-dompdf | ^3.1 | PDF generation (export center) |
| intervention/image | ^4.3 | Image thumbnails (GD driver) |
| spatie/simple-excel | ^3.10 | XLSX export |
| spatie/array-to-xml | ^3.4 | XML export |
| laravel/tinker | ^2.10.1 | REPL |
| laravel/pail | ^1.2.2 | Local request log viewer (dev) |
| laravel/pint | ^1.24 | PHP code style (dev) |
| laravel/sail | ^1.41 | Present in dev dependencies; **not part of the application itself** |
| pestphp/pest | ^4.4 (+ pest-plugin-laravel ^4.1) | Testing framework |

### Frontend

| Technology | Version | Notes |
|---|---|---|
| Vue | ^3.5.13 | Composition API, `<script setup>` |
| @inertiajs/vue3 | ^2.3.7 | SPA bridge |
| TypeScript | ^5.2.2 (checked with vue-tsc ^2.2.4) | Strict typing |
| Tailwind CSS | ^4.1.11 | Via `@tailwindcss/vite` plugin, no config file |
| Vite | ^7.0.4 | With SSR entry support |
| reka-ui | ^2.6.1 | Headless UI primitives (shadcn-vue "new-york-v4" style, see `components.json`) |
| lucide-vue-next | ^0.468.0 | Icons |
| class-variance-authority / clsx / tailwind-merge | latest majors | Styling utilities |
| vue-input-otp | ^0.3.2 | 2FA OTP input |
| @vueuse/core | ^12.8.2 | Composable utilities |
| ESLint ^9 / Prettier ^3 / typescript-eslint ^8 | dev | Quality gates |

### Databases

The application supports multiple databases through standard Laravel configuration
(`config/database.php`). Both connections are first-class:

- **PRIMARY PRODUCTION-GRADE DATABASE: PostgreSQL**
  - Fully configured `pgsql` connection in `config/database.php`
  - Explicitly handled in application code: the notification search path in
    `app/Http/Controllers/GlobalSearchController.php` has a dedicated pgsql branch
    for database-safe JSON payload searching
  - Enabled by setting `DB_CONNECTION=pgsql` plus host/port/database credentials in `.env`
- **ALTERNATIVE / LIGHTWEIGHT DATABASE: SQLite**
  - The shipped default (`DB_CONNECTION=sqlite` in `.env.example`)
  - Zero-setup: `touch database/database.sqlite && php artisan migrate --seed`
  - Used by the automated test suite (`phpunit.xml`: sqlite `:memory:`)
  - The current local `.env` in this working copy uses SQLite

Both paths are documented in `README.md`. MySQL is also listed as workable there
but PostgreSQL and SQLite are the supported baselines.

Docker is **not** part of the application. There is no docker-compose file in the
repository; local services (PostgreSQL, Mailpit, Redis) are developer workstation
concerns, not application features.

### Infrastructure defaults (from config)

| Concern | Default driver |
|---|---|
| Cache | `database` (`CACHE_STORE`) |
| Queue | `database` (`QUEUE_CONNECTION`) |
| Session | `database` (`SESSION_DRIVER`) |
| Mail | `log` locally (`MAIL_MAILER`) |
| Filesystem | `local` (`FILESYSTEM_DISK`) |
| Broadcast | `log` (unused by features) |
| Redis | Configured in `config/cache.php`, `config/queue.php`, `config/database.php`; **not enabled by default** |

---

## 3. High-Level Architecture

```
Browser
   ↓ HTTPS
Blade root view (resources/views/app.blade.php)  ← only server-rendered page shell
   ↓
Vue 3 + Inertia v2 SPA (resources/js/)
   ├── pages/            route components resolved by Inertia
   ├── layouts/          AppLayout / AuthLayout / settings layout
   ├── components/       app components + ui/ design system
   ├── composables/, types/, navigation/
   ↓ (XHR, partial reloads, Wayfinder-generated typed routes/actions)
Laravel HTTP layer (routes/web.php, routes/settings.php, routes/api.php)
   ↓ middleware stack (bootstrap/app.php): locale → appearance → Inertia share
Controllers
   ↓ FormRequest authorize() + validate()
   ↓ Spatie `permission:` route middleware (defense in depth)
Policies (app/Policies)  ←→  Gate::before Super Admin bypass (AppServiceProvider)
   ↓
Models (app/Models) + Support services (app/Support: ActivityLogger, MediaUploader,
SettingStore, CsvImportEngine, ApiPagination, Locales)
   ↓
PostgreSQL / SQLite · Storage disks (local media files) · Database queue ·
Database notifications · Cache tables
```

### Responsibility of each layer

| Layer | Location | Responsibility |
|---|---|---|
| Routes | `routes/web.php`, `routes/settings.php`, `routes/api.php` | URL → controller mapping; every protected route declares its `permission:` middleware here |
| Controllers | `app/Http/Controllers` (root + `Admin/`, `Settings/`, `Api/V1/`) | Orchestration only: authorize, fetch, render Inertia responses or JSON; no business rules beyond self-protection guards |
| Requests | `app/Http/Requests/**` | Authorization (`authorize()`) + validation rules + custom messages; all input validation lives here |
| Policies | `app/Policies/*Policy.php` | Model-level authorization mapped 1:1 to permission strings via `Gate::policy()` registrations |
| Models | `app/Models/*.php` | Eloquent entities, relationships, casts; intentionally thin (no custom query scopes or business logic) |
| Services / support classes | `app/Support/**` | Reusable domain-neutral engines: `ActivityLogger`, `MediaUploader`, `SettingRegistry`/`SettingStore`, `Import\CsvImportEngine`, `ApiPagination`, `Locales`, `SystemRole` |
| Actions | `app/Actions/Fortify/*` | Fortify contract implementations (`CreateNewUser`, `ResetUserPassword`) |
| Resources | `app/Http/Resources/Api/V1/*` | API JSON transformation |
| Middleware | `app/Http/Middleware/*` + `bootstrap/app.php` registration | Locale per user, appearance cookie sharing, Inertia shared props |
| Notifications | `app/Notifications/SystemMessageNotification.php` | Database-channel system messages |
| Frontend pages | `resources/js/pages/**` | One component per Inertia response |
| Shared props | `HandleInertiaRequests::share()` | auth (user/roles/permissions/can map), settings, flash, locale, translations, notification count/preview, sidebar state |

There are **no** dedicated domain Service/Action directories beyond Fortify actions
and `app/Support`. That is deliberate: the starter keeps logic in controllers +
support helpers until projects introduce real domains.

---

## 4. Directory Structure

| Directory | Purpose | Belongs there | Does NOT belong there |
|---|---|---|---|
| `app/Actions/Fortify/` | Fortify contract implementations | user creation, password reset logic | general business actions |
| `app/Concerns/` | Reusable traits | validation rule bundles (`PasswordValidationRules`, `ProfileValidationRules`) | models |
| `app/Http/Controllers/` | Web controllers; sub-namespaces `Admin\`, `Settings\`, `Api\V1\` | request handling | validation rules (use Requests), persistence logic beyond simple Eloquent calls |
| `app/Http/Middleware/` | HTTP middleware | `HandleInertiaRequests`, `HandleLocale`, `HandleAppearance` | policies |
| `app/Http/Requests/` | Form Requests mirroring controller namespaces | authorization + validation | rendering |
| `app/Http/Resources/Api/V1/` | API transformers | JSON shapes | web page props |
| `app/Models/` | Eloquent models | `User`, `ActivityLog`, `Media`, `Setting`, `ImportRun` | query-heavy report logic |
| `app/Notifications/` | Notification classes | database-channel messages | mail templates per project feature (add as needed) |
| `app/Policies/` | Model policies | one policy per managed model | route-level checks (use middleware) |
| `app/Providers/` | Service providers | `AppServiceProvider` (Gate, macros, defaults, event listeners), `FortifyServiceProvider` (Fortify views/actions/rate limiters) | feature bootstrap that belongs in packages |
| `app/Support/` | Domain-neutral reusable engines | see Section 21/29 | anything project-specific |
| `bootstrap/app.php` | Application bootstrap: routing, middleware aliasing/appending, exception rendering (JSON errors for `api/*`) | middleware + exception config | route definitions |
| `config/` | Framework + package config (`fortify.php`, `permission.php`, `sanctum.php`, etc.) | environment-driven configuration | hardcoded secrets |
| `database/migrations/` | Schema history | one migration per schema change | seed data (use seeders) |
| `database/seeders/` | `RolePermissionSeeder`, `SettingsSeeder`, `DatabaseSeeder` | baseline data | bulk fake content |
| `database/factories/` | `UserFactory`, `ActivityLogFactory`, `MediaFactory`, `SettingFactory`, `ImportRunFactory` | test model factories | production seeding |
| `lang/en/messages.php` | Translation lines exposed to the frontend via the `translations` prop | UI strings | backend exception messages |
| `resources/js/pages/` | Inertia page components | one file per server-rendered route | shared widgets (use components) |
| `resources/js/components/ui/` | Design-system primitives (shadcn-vue) | generated/stable primitives | feature-specific markup |
| `resources/js/components/`, `components/admin/` | App-level composed components | tables, dialogs, headers, cards | raw primitives |
| `resources/js/routes/`, `resources/js/actions/` | **Wayfinder-generated** TypeScript route/controller functions | generated output only — never hand-edit | hand-written fetch wrappers |
| `resources/js/types/` | Shared TypeScript contracts (`index.ts`, `auth.ts`, `admin.ts`, `navigation.ts`, `settings.ts`, `ui.ts`) | prop/page types | runtime code |
| `routes/` | `web.php`, `settings.php`, `api.php`, `console.php` | route definitions with permission middleware | controller logic |
| `tests/Feature/**`, `tests/Unit/` | Pest suites grouped by module | behavior coverage | slow browser suites (none today) |
| `TheRoadmap/` | Project workflow docs: roadmap, task list, decisions, learning notes, integration guidance | planning + process records | application code |
| Root docs | `README.md` (usage), `AGENTS.md` (AI conventions, Boost guidelines), `master-starter-api.postman_collection.json` (importable API collection), `AlphaFix*.md`, `remainTask.md` | historical fix logs / remaining tasks | secrets |

---

## 5. Authentication & Account Security

Authentication is provided entirely by **Laravel Fortify** (headless backend) with
Inertia views registered in `app/Providers/FortifyServiceProvider.php`.

### Features enabled (`config/fortify.php`)

```php
Features::registration(),
Features::resetPasswords(),
Features::emailVerification(),
Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
```

### Flows and where they live

| Flow | Implementation |
|---|---|
| Registration | Fortify `CreatesNewUsers` → `app/Actions/Fortify/CreateNewUser.php` (uses `ProfileValidationRules` + `PasswordValidationRules`); view `resources/js/pages/auth/Register.vue` |
| Login | Fortify controllers; view `auth/Login.vue`; rate limiter `login` = 5/min keyed by email+IP (`FortifyServiceProvider::configureRateLimiting`) |
| Logout | Fortify; session invalidation handled by framework |
| Password reset | `ForgotPassword.vue`, `ResetPassword.vue`; reset logic in `app/Actions/Fortify/ResetUserPassword.php`; tokens in `password_reset_tokens` table |
| Email verification | `VerifyEmail.vue`; `User` implements `MustVerifyEmail`; verification notification tests in `tests/Feature/Auth/VerificationNotificationTest.php` |
| Two-factor (TOTP) | `TwoFactorAuthenticatable` trait on `User`; setup modal + recovery codes components (`TwoFactorSetupModal.vue`, `TwoFactorRecoveryCodes.vue`, composable `useTwoFactorAuth.ts`); challenge view `auth/TwoFactorChallenge.vue`; OTP input via `vue-input-otp`; rate limiter `two-factor` = 5/min per pending login session |
| Recovery codes | Generated/displayed through the security settings flow (`TwoFactorRecoveryCodes.vue`) |
| Password confirmation | `auth/ConfirmPassword.vue`; enforced on `SecurityController@edit` when the `confirmPassword` 2FA option is enabled |
| Profile management | `app/Http/Controllers/Settings/ProfileController.php` + `settings/Profile.vue`; name/email/locale updates; email change resets `email_verified_at` (re-verification required) |
| Password change | `Settings\SecurityController@update` via `PasswordUpdateRequest`, throttled `throttle:6,1`, logged to activity log |
| Account deletion | `ProfileController@destroy` via `ProfileDeleteRequest`: logs activity, logs out, deletes user, invalidates session, regenerates token; guarded by `auth` + `verified` |
| Post-login routing | `config/fortify.php` sets `home => '/'`; named route `home` (in `routes/web.php`) redirects: guests → login; users **with** `dashboard.view` → dashboard; users **without** → profile edit screen. Protected by `tests/Feature/Auth/PostLoginRedirectTest.php` |
| Per-user locale | `locale` column on users; applied by `App\Http\Middleware\HandleLocale`; registry in `app/Support/Locales.php` (currently `en`) |

### Security-relevant defaults (`app/Providers/AppServiceProvider.php`)

- `Password::defaults()` (production only): minimum 12 chars, mixed case, letters,
  numbers, symbols, uncompromised (HaveIBeenPwned check).
- `DB::prohibitDestructiveCommands(app()->isProduction())` — destructive DB
  commands are blocked in production.
- `Date::use(CarbonImmutable::class)` — immutable dates app-wide.

### Authentication-related middleware

Registered in `bootstrap/app.php`: web group appends `HandleLocale`,
`HandleAppearance`, `HandleInertiaRequests`, `AddLinkHeadersForPreloadedAssets`.
Spatie middleware aliases `role`, `permission`, `role_or_permission` are declared
here. Cookies `appearance` and `sidebar_state` are excluded from encryption.

---

## 6. RBAC & Authorization

Powered by **spatie/laravel-permission v7** (single `web` guard;
**teams feature disabled**: `'teams' => false` in `config/permission.php`).

### Roles

Defined as constants in `app/Support/SystemRole.php` and seeded by
`database/seeders/RolePermissionSeeder.php`:

| Role | Purpose | Permissions granted at seed |
|---|---|---|
| **Super Admin** | Full-access recovery role managing the entire boilerplate | All 18 permissions — but effectively irrelevant because of the `Gate::before` bypass |
| **Manager** | Operational visibility plus user administration | dashboard.view, search.view, exports.view, settings.view, media.view, media.create, users.view, notifications.view, activity-logs.view |
| **Staff** | Standard internal user | dashboard.view, search.view, notifications.view |
| **Guest** | Authenticated account with minimal access | **zero permissions** (empty array) |

Roles carry a `description` column (added by migration
`2026_03_16_195118_add_description_to_roles_table`).

### Permission catalog

18 permissions, seeded with `Permission::findOrCreate($name, 'web')`:

```
dashboard.view      search.view         exports.view
settings.view       settings.update
media.view          media.create        media.delete
users.view          users.create        users.update    users.delete
roles.view          roles.create        roles.update    roles.delete
notifications.view  activity-logs.view
```

- **Naming convention:** `<resource>.<action>` — lowercase resource noun, dot,
  verb. New modules must follow it.
- **Storage:** Spatie tables `permissions`, `roles`, pivots `role_has_permissions`,
  `model_has_permissions`, `model_has_roles` (see Section 18).
- **Role → permission assignment:** `syncPermissions()` in the seeder and in
  `RoleManagementController@store/updatePermissions`.
- **Direct user permissions:** technically supported by Spatie
  (`model_has_permissions`) but **not used anywhere** in this starter — roles only.
- **Caching:** Spatie's `PermissionRegistrar` cache; the seeder calls
  `forgetCachedPermissions()` before and after seeding. Default cache store applies.
- **Seeding:** idempotent (`updateOrCreate`/`findOrCreate`); safe to re-run.

### Authorization layers (applied in order of typical enforcement)

1. **Route middleware** — `->middleware('permission:xxx')` on nearly every route in
   `routes/web.php` and all protected `routes/api.php` endpoints (aliases registered
   in `bootstrap/app.php`).
2. **FormRequest `authorize()`** — each admin/settings Request re-checks its
   permission (e.g. `StoreUserRequest::authorize()` returns `$user->can('users.create')`).
3. **Controller `$this->authorize()`** — policy checks inside every admin controller action.
4. **Policies** — `app/Policies/UserPolicy.php`, `RolePolicy.php`, `MediaPolicy.php`,
   `SettingPolicy.php`; each method delegates to exactly one permission string.
   Registrations via `Gate::policy()` in `AppServiceProvider`.
5. **Gate::before** — Super Admin bypass (below).
6. **Frontend capability checks** — shared `auth.can` prop map + per-item
   `permission` fields in `resources/js/navigation/app.ts`; sidebar items, buttons,
   and quick links hide themselves without permission. **Frontend checks are UX
   only; the backend always enforces.**

Convention: **permissions over roles**. Route/policy code never hardcodes role
names except for the protected system-role behaviors described next.

### Super Admin protections

- **Global bypass:** `Gate::before(fn (User $user, string $ability) => $user->hasRole(SystemRole::SUPER_ADMIN) ? true : null)` in `AppServiceProvider.php:84`. Super Admin passes every ability without needing the permission rows.
- **Rename/delete protection:** `RoleManagementController` refuses renaming or
  deleting any role whose name is in `SystemRole::names()` ("System role names are
  fixed…", "System roles cannot be deleted.").
- **Self-demotion protection:** `UserManagementController@updateRoles` blocks a
  Super Admin from removing their own Super Admin role ("You cannot remove your own
  Super Admin role from this screen."). Deleting yourself is also blocked.
- **Permission protection:** `RoleManagementController@updatePermissions` rejects
  edits targeting the Super Admin role ("managed automatically").
- Unit-tested in `tests/Unit/SystemRoleTest.php`; behavior covered in
  `tests/Feature/Admin/RoleManagementTest.php`, `UserCrudTest.php`, and
  `tests/Feature/RoleAccessTest.php`.

### Guest

- Seeded with an **explicitly empty permission set** — Guest has zero application
  permissions.
- Guests can authenticate and reach only their profile/security/appearance screens
  and Fortify flows. The `home` route sends them to `profile.edit` instead of the
  dashboard; every dashboard/admin surface returns 403.
- Intended use: accounts that exist (e.g. awaiting assignment) but must not see
  operational surfaces.

### Departments / Teams

- **Departments are NOT part of this master starter.**
- **Spatie Teams is NOT enabled** (`'teams' => false` in `config/permission.php`;
  no team foreign keys exist in migrations).
- Future projects may introduce department/team scoping when required; enabling
  teams later requires a migration plan for the Spatie pivot tables.

---

## 7. User Management

Backend:

- Controller: `app/Http/Controllers/Admin/UserManagementController.php`
  (`index`, `create`, `store`, `edit`, `update`, `updateRoles`, `destroy`)
- Requests: `StoreUserRequest` (name/email unique/password `Password::defaults()`/
  roles array validated against `roles.name`), `UpdateUserRequest`,
  `UpdateUserRolesRequest` (custom messages; `roles` must always be present)
- Policy: `UserPolicy` → `users.*` permissions
- Behaviors:
  - Creating a user notifies them via `SystemMessageNotification` ("Your account was created")
  - Changing email clears `email_verified_at` so it must be re-verified
  - Optional password reset on edit (blank = unchanged)
  - Role changes notify the affected user ("Your access changed") and are activity-logged
  - Self-protections: cannot delete yourself; cannot remove your own Super Admin role
  - Every mutation writes an `ActivityLogger` record (`users.created`,
    `users.updated`, `users.roles-updated`, `users.deleted`) with IP address

Frontend:

- Pages: `resources/js/pages/admin/Users/{Index,Create,Edit}.vue`
- Components: `admin/UserRoleCard.vue`, `admin/ResourceTable.vue`,
  `admin/ResourceToolbar.vue`, `admin/FormSection.vue`,
  `admin/ConfirmActionDialog.vue`, pagination + skeletons

Tests: `tests/Feature/Admin/UserCrudTest.php`, `tests/Feature/Admin/UserManagementTest.php`.

---

## 8. Dashboard

- Route: `GET /dashboard` (`DashboardController`, invokable), middleware
  `['auth', 'verified', 'permission:dashboard.view']`.
- Page: `resources/js/pages/Dashboard.vue` using `AppLayout`.
- **Generic reusable design:** four count metrics (active users, roles, media files,
  activity events) rendered through `admin/StatCard.vue` with tone colors, plus a
  recent-activity list (`admin/RecentActivityPanel.vue`, last 6 events).
- Quick links section is **permission-aware**: links to Exports, Settings, Media,
  Users, Roles, Activity logs are filtered client-side by the shared `auth.can` flags.
- Breadcrumbs via the shared `Breadcrumbs.vue` pattern.
- All data comes from core starter tables — nothing project-specific.
- Clearly project-specific dashboards should be built as new pages/controllers in a
  downstream project, reusing `StatCard` + `RecentActivityPanel` + quick-link patterns.

Tests: `tests/Feature/DashboardTest.php` (access control per role included in
`RoleAccessTest.php`).

---

## 9. Settings System

Architecture (all under `app/Support/`):

- **`SettingRegistry.php`** — static definition of setting groups and fields.
  Groups: `application` (display name, tagline, support email) and `organization`
  (name, legal name, email, phone). Each field declares key/label/description/type
  (`text|email|textarea`)/default/placeholder/rows.
- **`SettingStore.php`** — reads stored values merged over registry defaults
  (`values()`), builds the admin form shape (`groupsWithValues()`), persists
  validated keys (`sync()` via `updateOrCreate`), and produces the Inertia-shared
  subset (`shared()` returning camelCase keys like `appDisplayName`).
- Storage: `settings` table (`group` indexed, `key` unique, nullable text `value`).
- Seeder: `database/seeders/SettingsSeeder.php` inserts all defaults (idempotent).
- Validation: `app/Http/Requests/Admin/UpdateSettingsRequest.php` generates rules
  from the registry per field type and normalizes empty strings to null.
- Controllers: `Admin\SettingsManagementController` — `edit` requires
  `settings.view`, `update` requires `settings.update` (both route middleware and
  `SettingPolicy`); updates are activity-logged (`settings.platform-updated`).
- Frontend: `resources/js/pages/admin/Settings/Edit.vue` renders groups/fields from
  the `settingGroups` prop.
- **Caching:** none — values are read per request from the DB and shared into
  Inertia props on every visit (`HandleInertiaRequests::share`).
- Extending: add fields/groups to `SettingRegistry` (and translations if needed);
  validation, storage, seeding, and the admin UI pick them up automatically.
  `shared()` controls what reaches every page.

Tests: `tests/Feature/Admin/SettingsManagementTest.php`.

---

## 10. Notifications

- Channel: **database only** (`notifications` table, Laravel's default schema).
- Class: `app/Notifications/SystemMessageNotification.php` — constructor takes
  `title`, `message`, optional `actionUrl`, `actionLabel`, `level` (default `info`);
  queued via `Queueable` trait but delivery channel is database (so queue usage is
  incidental unless configured).
- Backend: `app/Http/Controllers/NotificationController.php`
  - `index` — paginated (10/page), filter `read=unread|read`, stats (unreadCount,
    totalCount), normalized item shape (id/title/message/actionUrl/actionLabel/
    level/readAt/createdAt)
  - `read` — marks one read (owner-scoped via `$user->notifications()->findOrFail`),
    activity-logged
  - `read-all` — bulk mark-as-read, activity-logged
- Shared props (`HandleInertiaRequests`): live `notificationCount` (unread) and
  `notificationPreview` (5 most recent) power the header bell without extra requests.
- Frontend: `NotificationBell.vue` (dropdown preview in the header),
  `pages/notifications/Index.vue` (list, filters, mark-read actions).
- API mirror: `/api/v1/notifications*` (Section 15).
- Tests: `tests/Feature/Feature/Notifications/NotificationCenterTest.php`,
  `tests/Feature/Feature/Api/NotificationApiTest.php`.

Extending: create additional `Notification` classes choosing channels as needed and
keep the `data` payload keys aligned with the shared presentation shape.

---

## 11. Activity / Audit Logging

- Architecture: lightweight custom logger (no spatie/laravel-activitylog dependency).
- Storage: `activity_logs` table — `actor_id` (nullable FK → users, nullOnDelete),
  `event` string, `description`, `subject_type`/`subject_id` (nullable morphs),
  `properties` JSON, `ip_address`(45), `created_at` (defaults to now; no updated_at).
- Model: `app/Models/ActivityLog.php` — `timestamps = false`,
  `properties` array cast, `created_at` immutable datetime cast, `actor()` belongsTo,
  `subject()` morphTo.
- Writer: `app/Support/ActivityLogger::record(actor, event, description, subject,
  properties, request)` — single static entry point used everywhere.
- Automatic events: `Login` and `Logout` listeners in `AppServiceProvider` record
  `auth.login` / `auth.logout`.
- Instrumented mutations include: users created/updated/deleted, roles CRUD,
  role permission sync, platform settings update, media upload/delete, notification
  read/read-all, profile/password changes, account deletion, export downloads
  (`exports.users-csv|xlsx|xml`, `exports.summary-print|pdf`), API token issue/revoke.
- Web UI: `ActivityLogController@index` (paginated 15/page, free-text search across
  description+event via the `searchLike` macro, distinct `event` filter dropdown) and
  `@show` detail page including properties. Permission: `activity-logs.view`.
- Frontend: `pages/activity-logs/Index.vue`, `pages/activity-logs/Show.vue`.
- API: `/api/v1/activity-logs`, `/api/v1/activity-logs/{id}`.
- Tests: `tests/Feature/Feature/ActivityLogs/ActivityLogIndexTest.php`,
  `tests/Feature/Feature/Api/ActivityLogApiTest.php`; login/logout logging exercised
  indirectly by auth tests.

Adding future activities: call `ActivityLogger::record(...)` inside the project's
own controllers/jobs with a dotted `event` name (e.g. `invoices.created`); no other
wiring needed — index filters and search pick up new event strings automatically.

---

## 12. Media & File Management

- Model: `app/Models/Media.php` — fields: `uploaded_by` (FK cascade),
  nullable morphs `attachable` (attach media to any model later), `collection`
  (default `library`), `disk` (default `local`), `directory`, `path` (unique),
  `thumbnail_path`, `original_name`, `stored_name`, `extension`, `mime_type`,
  `size`, JSON `metadata`. Relations: `uploadedBy()`, `attachable()`.
- Engine: `app/Support/MediaUploader.php`
  - `store(UploadedFile, User, collection='library', disk='local')` — slugs the
    collection, stores under `media/<collection>/`, records metadata
  - `generateThumbnail(Media)` — for image mimes (excluding SVG) when the **PHP GD
    extension is present**: scales longest edge to 400px, saves JPEG quality 80
    alongside the original; silently skips otherwise (record simply has no thumbnail)
  - `deleteFiles(Media)` — removes original and thumbnail
- Controller: `app/Http/Controllers/Admin/MediaManagementController.php` — index
  (search across original_name/collection/mime_type, eager-loaded uploader,
  10/page), store, download (streams original filename), destroy. Each mutation
  activity-logged.
- Validation: `StoreMediaRequest` — required file ≤ 20 MB (`max:20480`), optional
  collection string ≤ 100 chars, custom size message.
- Policy: `MediaPolicy` — view/create/delete via `media.*` permissions; update/
  restore/forceDelete hard-coded `false` (immutable uploads).
- Frontend: `pages/admin/Media/Index.vue` with `admin/MediaUploadField.vue`,
  thumbnails when available, download/delete actions.
- API: `GET /api/v1/media` (paginated via `MediaResource`) — read-only listing.
- Generic vs project-specific: the uploader/model/policy/API are fully generic;
  attachment to specific models happens via `attachable` morphs in downstream projects.
- Tests: `tests/Feature/Admin/MediaManagementTest.php`,
  `tests/Feature/Admin/MediaThumbnailTest.php` (skips gracefully without GD — the
  2 skipped tests in the current suite),
  `tests/Feature/Feature/Api/MediaApiTest.php`.

---

## 13. Search

- Controller: `app/Http/Controllers/GlobalSearchController.php` (invokable),
  route `GET /search` with `permission:search.view`, request `SearchIndexRequest`.
- Behavior: trims `q`; when non-empty, builds grouped results across modules,
  each group gated by the caller's permission:
  - Users (`users.view`) — name/email match, top 5, link to edit screen, roles meta
  - Roles (`roles.view`) — name/description, top 5
  - Own notifications (`notifications.view`) — database-safe JSON payload search
    with an explicit **pgsql-specific branch** (driver check) for case-insensitive
    matching
  - Activity logs (`activity-logs.view`) — event/description, newest first
- Empty groups are dropped; each result carries id/title/description/href/meta.
- Pagination: fixed limit 5 per group (no paging — designed as a quick-jump surface).
- Frontend: `pages/search/Index.vue` renders grouped results with deep links.
- Reusable helper: the `searchLike(array $columns, string $term)` builder macro
  (Eloquent + Query Builder) registered in `AppServiceProvider` — case-insensitive
  LIKE across columns; used by users/roles/media/activity indexes too.
- Extending: add a private `searchX()` method + permission gate + group entry in
  `buildResults()`.

Tests: `tests/Feature/Feature/GlobalSearchTest.php`,
`tests/Feature/SearchLikeMacroTest.php`.

---

## 14. Import / Export

### Import (generic engine)

- Classes: `app/Support/Import/CsvImportEngine.php`, `ImportPreview.php`,
  `ImportRowError.php`.
- **Two-phase design:**
  1. `preview($csv, $resource, $rowHandler, ?array $expectedHeader, $previewLimit=25, $fileName)` — parses header (exact-match option incl. order), associates rows with header, invokes a row handler returning `ImportRowError|null` for soft failures (throwing = hard failure), collects valid rows + row-numbered errors. Produces immutable `ImportPreview` (`isCommittable()` false on header mismatch/empty file/no valid rows).
  2. `commit($preview, $user, $persister)` — executes the persister per valid row and records an `ImportRun` with status `completed`/`completed_with_errors`, counts, error summary, and a preview snapshot (25 rows).
- History storage: `import_runs` table (model `app/Models/ImportRun.php`, factory
  included).
- Usage example lives in `README.md`. **UI wiring is intentionally left to each
  project** — there is no import screen in the starter.
- Tests: `tests/Feature/Support/CsvImportEngineTest.php`.

### Export center (implemented)

- Controller: `app/Http/Controllers/ExportCenterController.php`; page
  `pages/exports/Index.vue`; print view `pages/exports/PrintSummary.vue`; Blade PDF
  template `resources/views/exports/summary.blade.php`.
- Capabilities (all current):
  - **CSV** users snapshot — streamed (`streamDownload`), columns Name/Email/Roles/
    Email Verified At/Created At
  - **XLSX** users snapshot — `spatie/simple-excel` writer
  - **XML** users feed — `spatie/array-to-xml`
  - **Print summary** — Inertia page styled for printing (counts + recent users/events)
  - **PDF summary** — `barryvdh/laravel-dompdf`
- Authorization: index/print/PDF require `exports.view`; user data exports require
  `exports.view` **and** `users.view`.
- Auditing: every export/download is activity-logged.
- Not implemented: scheduled/recurring exports, per-row transformation pipelines,
  JSON export format. XLSX/XML/PDF **are** implemented (common misconception to avoid).

Tests: `tests/Feature/Feature/ExportFoundationTest.php`,
`tests/Feature/Feature/MultiFormatExportTest.php`.

---

## 15. API / Sanctum

Foundation:

- **Laravel Sanctum v4** personal access tokens; guard `web` (`config/sanctum.php`);
  stateful domains from `SANCTUM_STATEFUL_DOMAINS`.
- Versioned prefix: `routes/api.php` groups everything under **`/api/v1`**.
- Exception rendering (`bootstrap/app.php`): `api/*` requests get JSON
  `{message}` responses — 401 for `AuthenticationException`, 403 for
  `AccessDeniedHttpException` and Spatie `UnauthorizedException`.

Endpoints (complete list — nothing else exists):

| Method | URI | Auth | Permission | Purpose |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | guest | — | Validate credentials (`LoginApiRequest`, includes `device_name`), issue bearer token, return `AuthUserResource`; activity-logged |
| GET | `/api/v1/auth/me` | sanctum | — | Current user (`CurrentUserController`) |
| POST | `/api/v1/auth/logout` | sanctum | — | Revoke current token; activity-logged |
| GET | `/api/v1/notifications` | sanctum | notifications.view | Paginated list |
| GET | `/api/v1/notifications/{notification}` | sanctum | notifications.view | Show (owner-scoped) |
| PUT | `/api/v1/notifications/{notification}/read` | sanctum | notifications.view | Mark read |
| POST | `/api/v1/notifications/read-all` | sanctum | notifications.view | Mark all read |
| GET | `/api/v1/activity-logs` | sanctum | activity-logs.view | Paginated list |
| GET | `/api/v1/activity-logs/{activityLog}` | sanctum | activity-logs.view | Show |
| GET | `/api/v1/admin/summary` | sanctum | users.view | Counts, recent users, role breakdown (`AdminSummaryResource`) |
| GET | `/api/v1/admin/users` | sanctum | users.view | Paginated, `?search=` filter (`AdminUserResource`) |
| GET | `/api/v1/media` | sanctum | media.view | Paginated media listing (`MediaResource`) |

- Controllers: `app/Http/Controllers/Api/V1/*`; Requests:
  `LoginApiRequest`; Resources: `app/Http/Resources/Api/V1/*` (`AuthUserResource`,
  `AdminUserResource`, `AdminSummaryResource`, `NotificationResource`,
  `ActivityLogResource`, `MediaResource`).
- **Pagination convention:** `app/Support/ApiPagination::response()` — envelope of
  `data` (resource collection), `links` (first/last/prev/next), and `meta.pagination`
  (current_page, last_page, per_page, total, from, to) plus custom meta.
- Rate limiting: no dedicated API limiter is registered; endpoints inherit the
  framework's throttling defaults only. (Web login/2FA limiters do not apply here.)
- An importable Postman collection covering all endpoints ships at
  `master-starter-api.postman_collection.json`.
- Tests: `tests/Feature/Api/AuthApiTest.php`, `AdminUsersApiTest.php`,
  `MediaApiTest.php`, plus `tests/Feature/Feature/Api/*` for notifications,
  activity logs, and summary.

Extending: follow the same pattern — controller in `Api/V1`, FormRequest for
input, Resource for output, `permission:` middleware on the route,
`ApiPagination::response()` for lists.

---

## 16. Frontend Architecture

- **Vue 3** (`<script setup lang="ts">` everywhere) + **Inertia v2**
  (`createInertiaApp` in `resources/js/app.ts`, page resolution via
  `import.meta.glob('./pages/**/*.vue')`, progress indicator).
- **SSR supported:** `resources/js/ssr.ts` entry, `npm run build:ssr`,
  `composer run dev:ssr` script; built SSR bundle lives in `bootstrap/ssr/`.
- **TypeScript strictness** checked with `vue-tsc --noEmit` (`npm run types:check`).
- **Tailwind CSS v4** imported once in `resources/css/app.css` via
  `@tailwindcss/vite`; `tw-animate-css` for animations; `prettier-plugin-tailwindcss`
  sorts classes. No `tailwind.config.js` (v4 CSS-first config).
- **Vite 7** (`vite.config.ts`): laravel-vite-plugin (refresh, SSR input),
  tailwindcss, vue plugins, and **Wayfinder** plugin generating
  `resources/js/routes/**` + `resources/js/actions/**` (with `.form()` variants).
  Regenerate with `php artisan wayfinder:generate --with-form`.
  **Never hand-edit these generated directories.**

Key folders:

| Folder | Contents |
|---|---|
| `resources/js/pages/` | Inertia pages: `Dashboard.vue`, `auth/*` (7), `admin/Users/*`, `admin/Roles/*`, `admin/Settings/Edit.vue`, `admin/Media/Index.vue`, `activity-logs/*`, `notifications/Index.vue`, `exports/*`, `search/Index.vue`, `settings/*` |
| `resources/js/layouts/` | `AppLayout.vue` (sidebar shell), `AuthLayout.vue` (+ `auth/AuthCardLayout/AuthSimpleLayout/AuthSplitLayout`), `settings/Layout.vue`, `app/AppHeaderLayout.vue` |
| `resources/js/components/` | App components: `AppShell/AppSidebar/AppHeader/NavMain/NavUser/NavFooter`, `Breadcrumbs`, `PageHeader/PageContainer/PageErrorState`, `FlashMessages`, `AlertError`, `EmptyState`, `LoadingState`, `Heading`, `InputError`, `PasswordInput`, `TextLink`, `UserInfo`, `DeleteUser`, `AppearanceTabs`, `PlaceholderPattern`, `NotificationBell`, `TwoFactorSetupModal`, `TwoFactorRecoveryCodes`, `UserMenuContent` |
| `resources/js/components/admin/` | Admin building blocks: `ResourceTable` (+Skeleton), `ResourceToolbar`, `ResourcePagination`, `StatCard`, `RecentActivityPanel`, `FormSection` (+Skeleton), `ConfirmActionDialog`, `UserRoleCard`, `RolePermissionCard`, `StatusBadge`, `ActionIconLink`, `MediaUploadField` |
| `resources/js/components/ui/` | shadcn-vue design system (reka-ui based): alert, avatar, badge, breadcrumb, button, card, checkbox, collapsible, dialog, dropdown-menu, input, input-otp, label, navigation-menu, select, separator, sheet, sidebar, skeleton, spinner, tooltip |
| `resources/js/composables/` | `useAppearance` (dark/light/system + `initializeTheme`), `useT` (translations from shared prop), `useInitials`, `useCurrentUrl`, `useTwoFactorAuth` |
| `resources/js/types/` | Barrel `index.ts` re-exporting `auth.ts` (`User`, `Auth`, `SharedFlash`), `admin.ts` (`ManagedUser/ManagedRole/ManagedNotification/ManagedSetting*/ManagedMedia/PaginatedResource<T>` …), `navigation.ts`, `settings.ts`, `ui.ts` + env shims |
| `resources/js/navigation/app.ts` | Declarative sidebar definition: `NavGroup[]` items with icon + required `permission` string; rendered by `AppSidebar` filtered against shared `auth.can` |

Conventions:

- Forms use Inertia `useForm`; destructive actions wrapped in `ConfirmActionDialog`.
- Tables: `ResourceTable` + `ResourceToolbar` (search box) + `ResourcePagination`
  consuming Laravel paginator shapes (`PaginatedResource<T>`).
- Flash feedback via shared `flash.success/error` rendered by `FlashMessages`.
- Dark/light mode: appearance cookie (`appearance`) shared by `HandleAppearance`
  middleware, toggled by `AppearanceTabs`, initialized by `initializeTheme()`.
- Sidebar collapse persisted in the unencrypted `sidebar_state` cookie and shared
  back as `sidebarOpen`.
- Accessibility: semantic landmarks/headings, labeled inputs via `Label`, keyboard-
  navigable headless primitives (reka-ui), focus-visible rings, skeleton loaders
  during loads.
- Breadcrumbs: each page defines `BreadcrumbItem[]` consumed by `Breadcrumbs.vue`
  inside `PageHeader`.
- i18n: `useT()` reads the shared `translations` prop (`__('messages')` from
  `lang/<locale>/messages.php`); locale switcher on profile screen backed by
  `Locales` registry.

---

## 17. UI / Design System

Generated via **shadcn-vue** (`components.json`, style `new-york-v4`, base color
neutral, CSS variables, lucide icons) on top of **reka-ui** headless primitives.
Primitives live in `resources/js/components/ui/<primitive>/`:

- **Buttons** — `button/Button.vue` (cva variants)
- **Inputs** — `input/Input.vue`, `label/Label.vue`, `checkbox/Checkbox.vue`,
  `select/*` (14 files), `input-otp/*` (TOTP entry)
- **Dialogs/sheets** — `dialog/*` (12 incl. scroll content), `sheet/*` (mobile nav)
- **Tables/data display** — no primitive table; composition via app-level
  `admin/ResourceTable.vue` + `badge/Badge.vue`, `admin/StatusBadge.vue`,
  `avatar/*`, `card/*` (7)
- **Dropdowns/menus** — `dropdown-menu/*` (17), `navigation-menu/*` (8)
- **Navigation/chrome** — `sidebar/*` (26 files: provider, menu, groups, rail,
  trigger, skeleton…), `breadcrumb/*` (7), `separator/Separator.vue`,
  `collapsible/*`
- **Feedback** — `alert/*` (3), `skeleton/Skeleton.vue`, `spinner/Spinner.vue`,
  tooltip (5)

App-level states: `EmptyState.vue` (used with tables/search), `LoadingState.vue`,
`FormSectionSkeleton.vue`, `ResourceTableSkeleton.vue`, `PageErrorState.vue`.

Rule of thumb: primitives stay untouched; compose them inside
`components/` or `components/admin/`.

---

## 18. Database Architecture

Default connection `sqlite` out of the box; `pgsql` fully configured (Section 2).
Migrations verified runnable end-to-end (`php artisan migrate:status` — all Ran in
a freshly migrated database; see Section 31 for the current local DB note).

### Core

| Table | Purpose | Key fields/constraints |
|---|---|---|
| `users` | Accounts | `name`, `email` unique, nullable `email_verified_at`, hashed `password`, `remember_token`, timestamps, `locale` (added 2026_08_23); 2FA columns (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` — Fortify migration) |
| `password_reset_tokens` | Reset tokens | PK `email`, `token`, `created_at` |
| `sessions` | DB sessions (default driver) | PK `id`, nullable indexed `user_id`, `ip_address`, `user_agent`, `payload`, indexed `last_activity` |
| `personal_access_tokens` | Sanctum tokens | standard Sanctum schema (`tokenable` morphs, indexed `token`, `expires_at`) |

### Authorization (Spatie Permission v7, guard `web`, teams disabled)

| Table | Purpose |
|---|---|
| `permissions` | Catalog rows (18 seeded) |
| `roles` | Role rows + `description` column (starter-added migration) |
| `role_has_permissions` | role ↔ permission pivot |
| `model_has_roles` | user ↔ role pivot (morph map; **no team FK**) |
| `model_has_permissions` | direct user permissions (unused, supported) |

### Application infrastructure

| Table | Purpose | Notes |
|---|---|---|
| `cache` / `cache_locks` | Default cache store | key/value/expiration |
| `jobs`, `job_batches`, `failed_jobs` | Default database queue | standard Laravel 12 schema |
| `notifications` | Database notifications | morphTo notifiable, UUID pk, `data` JSON, `read_at` |
| `activity_logs` | Audit trail | nullable `actor_id` FK nullOnDelete, `event`, `description`, nullable morphs `subject`, JSON `properties`, `ip_address`, `created_at` default now |
| `settings` | Key/value settings | `group` indexed, `key` unique, nullable text `value`, timestamps |
| `media` | File records | `uploaded_by` FK cascade, nullable morphs `attachable`, `collection` default `library`, `disk` default `local`, `path` unique, `thumbnail_path` (added 2026_08_23), JSON `metadata`, size bigint |
| `import_runs` | CSV import history | nullable `user_id` FK nullOnDelete, `resource`, `status`, `file_name`, three unsigned counters, JSON `summary` + `preview_rows`, nullable `completed_at` |

Constraints worth knowing: media paths unique per row (one physical file per
record); activity actors survive user deletion as NULL (audit history preserved);
media rows cascade-delete with their uploader.

---

## 19. Models & Relationships

| Model | Path | Purpose | Relationships | Traits | Notable methods/facts |
|---|---|---|---|---|---|
| `User` | `app/Models/User.php` | Authenticatable account | Spatie `HasRoles` provides `roles()`, `permissions()`, `hasRole()`, `hasPermissionTo()`, `getAllPermissions()`, `getRoleNames()`; `notifications()` (Notifiable); `tokens()` (HasApiTokens) | `HasApiTokens`, `HasFactory`, `HasRoles`, `Notifiable`, `TwoFactorAuthenticatable`; implements `MustVerifyEmail` | `effectiveLocale(): string` falls back to default locale; hidden fields include 2FA secret/recovery codes; `password` hashed cast |
| `ActivityLog` | `app/Models/ActivityLog.php` | Audit entry | `actor(): BelongsTo(User)`, `subject(): MorphTo` | `HasFactory` | `timestamps = false`; `properties` array cast; immutable `created_at` |
| `Media` | `app/Models/Media.php` | Stored file record | `uploadedBy(): BelongsTo(User)`, `attachable(): MorphTo` | `HasFactory` | `metadata` array cast; uniqueness on `path` (migration level) |
| `Setting` | `app/Models/Setting.php` | One stored setting value | — (keyed lookup only) | `HasFactory` | fillable group/key/value; logic lives in `SettingStore` |
| `ImportRun` | `app/Models/ImportRun.php` | CSV import history | `user(): BelongsTo` | `HasFactory` | `summary`, `preview_rows` array casts; status strings `completed`/`completed_with_errors` |

Relationship overview:

```
User ─┬─< model_has_roles >─ Role ──< role_has_permissions >─ Permission
      ├─< notifications            (morph: notifiable)
      ├─< personal_access_tokens   (morph: tokenable)
      ├─< activity_logs            (actor_id, nullable)
      ├─< media                    (uploaded_by, cascade delete)
      └─< import_runs              (user_id, nullable)

ActivityLog ─── subject() ───► any model (nullable morphTo)
Media ──────── attachable() ─► any model (nullable morphTo)
```

Authorization implications: only `User`, `Media`, `Setting`, and Spatie `Role`
have policies; `ActivityLog`/`ImportRun` are guarded purely by route
`permission:` middleware.

---

## 20. Routes

Verified via `php artisan route:list --except-vendor` (52 routes total).

### Web (`routes/web.php`)

- `GET /` → named `home`: auth-aware redirect (login / dashboard / profile).
- Dashboard: `GET /dashboard` — `auth`, `verified`, `permission:dashboard.view`.
- Authenticated group (`auth`, `verified`), each route additionally carrying its
  `permission:` middleware:
  - Search: `GET /search` (`search.view`)
  - Exports: `GET /exports`, `/exports/users.csv|.xlsx|.xml` (also `users.view`),
    `/exports/summary/print`, `/exports/summary.pdf` (`exports.view[+users.view]`)
  - Users admin: index/create/store/edit/update/roles-update/destroy under
    `/admin/users*` with `users.*` permissions
  - Roles admin: index/create/store/edit/update/permissions-update/destroy under
    `/admin/roles*` with `roles.*` permissions
  - Settings admin: `GET|PUT /admin/settings` (`settings.view` / `settings.update`)
  - Media: `GET|POST /admin/media`, `GET /admin/media/{media}/download`,
    `DELETE /admin/media/{media}` with `media.*` permissions
  - Notifications: index, `{notification}/read`, `read-all` (`notifications.view`)
  - Activity logs: index + show (`activity-logs.view`)

### Settings/authenticated profile (`routes/settings.php`)

- `settings` redirect → `/settings/profile`
- `GET|PATCH /settings/profile` (auth), `DELETE /settings/profile` (auth+verified)
- `GET /settings/security`; `PUT /settings/password` (throttled 6/min)
- `GET /settings/appearance` (direct `Route::inertia`)

### Fortify routes (registered by the package)

login/logout, register, password reset request+reset, email verification
notice/verify/resend, password confirm, two-factor challenge + enable/disable/secret
— all under the `web` middleware group with views wired in `FortifyServiceProvider`.

### Health

`GET /up` (health route configured in `bootstrap/app.php`).

### API (`routes/api.php`) — see Section 15 table.

Conventions: authorization is declared **at the route level** with Spatie
`permission:` middleware AND enforced again in FormRequest `authorize()` and
controller policies. Named routes are used everywhere (`route()` helper and
Wayfinder-generated functions on the frontend).

---

## 21. Controllers / Requests / Policies / Services

Module-by-module inventory:

| Module | Controller(s) | Requests | Policy | Service/Support | Resource(s) |
|---|---|---|---|---|---|
| Dashboard | `DashboardController` (invokable) | — | — (route permission) | — | — |
| Users admin | `Admin\UserManagementController` | `StoreUserRequest`, `UpdateUserRequest`, `UpdateUserRolesRequest` | `UserPolicy` | `ActivityLogger`, `SystemMessageNotification` | — |
| Roles admin | `Admin\RoleManagementController` | `StoreRoleRequest`, `UpdateRoleRequest` (permissions), `UpdateRoleDetailsRequest` | `RolePolicy` | `SystemRole` (protection lists), permission label/group builders | — |
| Settings admin | `Admin\SettingsManagementController` | `UpdateSettingsRequest` (registry-driven rules) | `SettingPolicy` | `SettingRegistry`, `SettingStore` | — |
| Media admin | `Admin\MediaManagementController` | `StoreMediaRequest` (≤20 MB) | `MediaPolicy` | `MediaUploader` | `Api\V1\MediaResource` |
| Notifications | `NotificationController`; `Api\V1\NotificationApiController` | — (owner-scoped lookups) | — (route permission + owner scoping) | `SystemMessageNotification` | `Api\V1\NotificationResource` |
| Activity logs | `ActivityLogController`; `Api\V1\ActivityLogApiController` | — | — (route permission) | `ActivityLogger` (writer) | `Api\V1\ActivityLogResource` |
| Search | `GlobalSearchController` (invokable) | `SearchIndexRequest` | — (per-module permission gating) | `searchLike` macro | — |
| Export center | `ExportCenterController` | — | — (route permissions) | dompdf `Pdf`, SimpleExcelWriter, ArrayToXml | — |
| Profile/security | `Settings\ProfileController`, `Settings\SecurityController` (HasMiddleware for password.confirm) | `ProfileUpdateRequest`, `ProfileDeleteRequest`, `PasswordUpdateRequest`, `TwoFactorAuthenticationRequest` | — | Fortify features API | — |
| Auth (web) | Fortify package + `Actions\Fortify\{CreateNewUser,ResetUserPassword}` | concerns: `PasswordValidationRules`, `ProfileValidationRules` | — | Fortify rate limiters | — |
| API auth/tokens | `Api\V1\AuthTokenController`, `CurrentUserController` | `LoginApiRequest` | — | Sanctum tokens | `AuthUserResource` |
| API admin | `Api\V1\AdminUserController`, `AdminSummaryController` | — | — (route permission) | `ApiPagination` | `AdminUserResource`, `AdminSummaryResource` |

How they work together: the route grants coarse entry (`permission:` middleware);
the Request authorizes + validates; the controller double-checks via policy
(`$this->authorize`), performs Eloquent operations, emits side effects
(`ActivityLogger`, notifications), and renders either an Inertia page (array props)
or a paginated JSON envelope.

---

## 22. Testing Architecture

- Framework: **Pest v4** with PHPUnit 12 underlying; `RefreshDatabase` bound to all
  Feature tests via `tests/Pest.php`.
- Environment (`phpunit.xml`): sqlite `:memory:`, array cache/session/mail/queue
  drivers, `BCRYPT_ROUNDS=4` for speed.
- Layout:
  - `tests/Unit/SystemRoleTest.php` — system-role constant integrity
  - `tests/Feature/Auth/*` — authentication, registration, password reset,
    email verification (+notification), password confirmation, 2FA challenge,
    post-login redirect
  - `tests/Feature/Admin/*` — user/role CRUD + management, settings, media
    management + thumbnails, policy conventions
  - `tests/Feature/Settings/*` — profile update, security/password
  - `tests/Feature/Api/*` + `tests/Feature/Feature/Api/*` — Sanctum auth flow,
    admin users, media, notifications, activity logs, admin summary
  - `tests/Feature/Feature/*` — dashboard, exports foundation, multi-format
    exports, global search, notification center, activity-log index
  - `tests/Feature/Support/CsvImportEngineTest.php` — engine preview/commit rules
  - `tests/Feature/RoleAccessTest.php` — cross-role access matrix
  - `LocalizationTest.php`, `HomeRedirectTest.php`, `SearchLikeMacroTest.php`,
    `ExampleTest.php`
- Helpers: `TestCase::skipUnlessFortifyFeature()` skips tests when a Fortify
  feature is disabled; GD-dependent thumbnail tests skip with a message when GD is
  absent.
- **Verified current run (this session): 127 passed, 2 skipped (GD thumbnail
  cases), 768 assertions, ~12s.**
- Protected behaviors of note: Super Admin bypass, system-role immutability,
  self-demotion/self-deletion prevention, Guest zero-access, permission-gated
  routes/API, owner-scoped notification access, import header-mismatch handling,
  export format correctness.

Frontend has no JS unit suite; quality is protected by ESLint + vue-tsc + build.

---

## 23. Code Quality & CI

Local commands:

| Check | Command |
|---|---|
| PHP style (fix) | `composer lint` / `vendor/bin/pint --dirty` |
| PHP style (check) | `composer lint:check` |
| Frontend lint | `npm run lint:check` (ESLint 9 flat config, typescript-eslint, vue, import plugins) |
| Formatting | `npm run format:check` (Prettier 3 + tailwind class sorting; scope `resources/`) |
| Types | `npm run types:check` (vue-tsc) |
| Build | `npm run build` / `npm run build:ssr` |
| Full gate | `composer ci:check` (lint:check → format:check → types:check → build → `artisan test --compact`) |

Config files: `pint.json`, `eslint.config.js`, `.prettierrc`, `.prettierignore`,
`tsconfig.json`, `.editorconfig`.

CI (GitHub Actions, `.github/workflows/`):

- `lint.yml` — job `quality` on push/PR to develop/main/master: PHP 8.5 + Node 22;
  Pint check, Prettier check, ESLint, vue-tsc type check, production asset build.
- `tests.yml` — job `ci`: installs deps, copies `.env.example`, generates key,
  runs `php artisan wayfinder:generate --with-form` (so generated TS exists before
  type/build steps), `npm run build`, then the full Pest suite.

No PHP static analysis (PHPStan/Larastan/Psalm) is configured — not confirmed
present in the repository.

Expected gate before merging: all five `composer ci:check` stages green locally
and in CI.

---

## 24. Queues / Cache / Mail / Storage

Application defaults (all database-backed — no external services required):

| Concern | Driver | Tables/config |
|---|---|---|
| Queue | `database` | `jobs`, `job_batches`, `failed_jobs`; local processing via `php artisan queue:listen --tries=1 --timeout=0` (part of `composer run dev`) |
| Cache | `database` | `cache`, `cache_locks`; also powers Spatie permission cache |
| Session | `database` | `sessions` table; 120-min lifetime |
| Mail | `log` locally | swap to SMTP/Mailpit in `.env` (example in README); used by Fortify verification/reset mails |
| Storage | `local` disk (`storage/app/private`) | media files under `media/<collection>/`; public disk defined but unused by features |
| Broadcast | `log` | no real-time features |

Redis: connection configuration exists (`REDIS_CLIENT=phpredis`, host/port) in
`config/database.php`, `config/cache.php`, `config/queue.php` but **nothing uses
Redis by default** — switching is a `.env` change (`CACHE_STORE=redis`,
`QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`).

Scheduler: `routes/console.php` contains only the default inspire command; no
scheduled tasks are required. Production deployments should still register
`schedule:run` (README deployment baseline).

Developer workstation services (Herd, Mailpit, PostgreSQL server, Redis) are
environment conveniences, not application components.

---

## 25. Environment Configuration

Safe placeholders only — never commit real values. Reference: `.env.example`.

```dotenv
APP_NAME="Laravel Master Starter"
APP_ENV=local                 # local | staging | production
APP_KEY=                      # php artisan key:generate
APP_DEBUG=true                # false in production
APP_URL=http://localhost      # Herd: https://<project>.test

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

# --- Database -----------------------------------------------------------
# SQLite (shipped default):
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite
#
# PostgreSQL:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=laravel
# DB_USERNAME=<user>
# DB_PASSWORD=<password>

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database          # redis available
QUEUE_CONNECTION=database     # redis available
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=log               # smtp + MAIL_HOST/PORT for Mailpit
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

VITE_APP_NAME="${APP_NAME}"
```

Notes: AWS S3 variables exist for the s3 filesystem disk (unused by features).
Secrets belong only in `.env`/secret managers — configuration files read them via
`env()` at load time; application code must use `config()`.

---

## 26. Developer Workflow

Actual project commands (from `composer.json` scripts and `README.md`):

```bash
# 1. Install
composer install
cp .env.example .env
php artisan key:generate
npm install            # or npm ci

# 2. Database (SQLite path)
touch database/database.sqlite
php artisan migrate --seed

#    ...or configure PostgreSQL in .env first, then migrate --seed.
#    One-shot alternative: composer setup

# 3. Develop
npm run dev            # Vite dev server (pair with Herd or php artisan serve)
composer run dev       # concurrently: serve + queue listener + pail + vite
composer run dev:ssr   # SSR variant

# 4. Verify
php artisan test --compact
composer lint:check    # Pint
npm run lint:check     # ESLint
npm run format:check   # Prettier
npm run types:check    # vue-tsc
npm run build

# 5. Regenerate frontend routes after changing routes/controllers
php artisan wayfinder:generate --with-form

# Full gate
composer ci:check
```

Seeded account: `admin@example.com` / `password` (Super Admin) — change before any
shared deployment.

Branch/documentation workflow (from README): one branch per roadmap phase; keep
`TheRoadmap/BoilerplateTaskList.md` synced; record learnings in
`TheRoadmap/laravelbasics.md`.

---

## 27. AI Agent Guidance

1. **Read `AGENTS.md` first** — it carries the Laravel Boost guidelines binding
   this repo: package versions, mandatory skills activation (pest-testing,
   inertia-vue-development, tailwindcss-development, wayfinder-development,
   fortify-development), PHP style rules, testing enforcement, and the rule that
   documentation files may only be created when requested.
2. **Architectural conventions**
   - Follow existing structure; no new top-level folders without approval.
   - Never hand-edit `resources/js/routes/**` or `resources/js/actions/**` — they
     are Wayfinder-generated; rerun `php artisan wayfinder:generate --with-form`.
   - Validation belongs in Form Requests; authorization in policies + route
     middleware; keep controllers thin.
   - Use explicit return types, constructor promotion, curly braces, PHPDoc arrays.
3. **Authorization rules**
   - New surfaces need a `<resource>.<action>` permission added to
     `RolePermissionSeeder` (catalog + role mappings), route `permission:`
     middleware, a policy method, and a shared `auth.can` entry if the sidebar/UI
     needs it.
   - Never weaken Super Admin bypass, system-role protection, or self-protection
     logic (`SystemRole`, `Gate::before`, `UserManagementController` guards).
   - Guest stays zero-permission by design.
4. **Testing requirements** — every behavioral change ships with/updates a Pest
   test; run the narrowest filter first (`php artisan test --compact --filter=...`),
   then the full suite before finishing. Do not delete tests without approval.
5. **Database safety** — migrations only forward; modifying a column means restating
   all prior attributes; destructive `db:wipe`/`migrate:fresh` commands are blocked
   in production by `DB::prohibitDestructiveCommands`. Prefer factories over ad-hoc
   model building in tests.
6. **Domain neutrality** — this is the master starter. Do not add business-domain
   entities, integrations, dashboards-for-a-specific-industry, or opinionated
   features here. Build those in derived projects.
7. **Dependencies** — do not install new packages without approval
   (`AGENTS.md`: "Do not change the application's dependencies without approval").
8. **Docs** — update `MASTER-STARTER-ARCHITECTURE.md` and `README.md` when the
   architecture changes materially; keep secrets and machine-specific paths out of
   all documentation.

---

## 28. Intentional Non-Features

Absence of the following is **by design** in the master starter. Do not "restore"
them here; build them in downstream projects:

- Pages/CMS and any public-facing website (the app assumes authenticated use)
- Notes module
- Reports module / enterprise reporting (only the generic dashboard + workspace
  summary exports exist)
- Handbook application
- Departments / department scoping
- Spatie Teams (multi-tenancy) — disabled in `config/permission.php`
- Any project-specific business entity (invoices, clients, orders, HR records…)
- Project-specific integrations (payment providers, ERPs, messaging platforms…)
- Scheduled/recurring exports; JSON export format
- Import UI screens (engine + history table exist; presentation left to projects)
- OCR / PDF *parsing* (PDF **generation** for the summary does exist via dompdf)
- Real-time/broadcast features, social login, API rate-limiting tiers
- Docker orchestration files

If a future requirement matches something above, extend the derived project — not
this boilerplate — unless the capability is genuinely generic infrastructure.

---

## 29. Extension Guide

### Adding a new domain module (e.g. "projects")

1. **Model + migration** — `php artisan make:model Project -mf` (factory required;
   consider a seeder if baseline data is useful).
2. **Migration** — follow naming; include indexes/foreign keys; remember SQLite +
   Postgres compatibility (avoid driver-only column types).
3. **Permissions** — add `projects.view|create|update|delete` to
   `RolePermissionSeeder` catalog and decide which seeded roles receive them
   (seeder is idempotent; run `php artisan db:seed --class=RolePermissionSeeder`
   after deploy).
4. **Policy** — `php artisan make:policy ProjectPolicy --model=Project`; map each
   ability to its permission string; register via `Gate::policy()` in
   `AppServiceProvider`.
5. **Controller + Requests** — `Admin\ProjectManagementController` (or a new
   namespace) with `StoreProjectRequest`/`UpdateProjectRequest` mirroring existing
   Request conventions (authorize + rules + messages).
6. **Routes** — add to the authenticated group in `routes/web.php` with
   `permission:` middleware and names (`projects.index` …).
7. **Frontend** — regenerate Wayfinder (`php artisan wayfinder:generate
   --with-form`); add pages under `resources/js/pages/admin/Projects/`; reuse
   `ResourceTable`/`FormSection`/`ConfirmActionDialog`; define TS types in
   `resources/js/types/`; add navigation entry in `navigation/app.ts` with its
   `permission` field; add breadcrumbs.
8. **API (optional)** — controller in `Api\V1`, Resource class, routes under
   `/api/v1` with permission middleware, `ApiPagination::response()` for lists,
   Postman collection update.
9. **Search (optional)** — private `searchProjects()` in `GlobalSearchController`
   gated by `projects.view`.
10. **Exports (optional)** — new methods on `ExportCenterController` + entries on
    the export index page, activity-logged like the existing ones.
11. **Tests** — feature tests for CRUD + policy + role matrix; API tests if
    applicable; factory-based data.
12. **Audit** — call `ActivityLogger::record()` on mutations with dotted event
    names (`projects.created` …).

### Reuse, don't rebuild

| Need | Reuse |
|---|---|
| Login/registration/reset/2FA | Fortify configuration (already complete) |
| Access control | `SystemRole` + seeder + policy/middleware patterns |
| Configuration values | `SettingRegistry`/`SettingStore` (extend, don't fork) |
| File handling | `MediaUploader` + `Media.attachable` morphs |
| User-facing messages | `SystemMessageNotification` (database channel) |
| Action history | `ActivityLogger::record()` |
| Quick find | `GlobalSearchController` group + `searchLike` macro |
| Lists/tables/forms/dialogs | `admin/*` + `ui/*` component libraries |
| API listing | `ApiPagination` + `Resources/Api/V1` patterns |
| Testing | Pest setup, factories, `skipUnlessFortifyFeature` helper |

---

## 30. Security Architecture

| Layer | Mechanism |
|---|---|
| Authentication | Fortify sessions (web guard), bcrypt cost 12 (`BCRYPT_ROUNDS`), login throttle 5/min per email+IP, 2FA TOTP w/ confirmation + password-confirm window, recovery codes |
| Authorization | Defense in depth: route `permission:` middleware → FormRequest `authorize()` → controller `$this->authorize()` → policies; `Gate::before` Super Admin bypass; frontend checks are cosmetic only |
| CSRF | `ValidateCsrfToken` in the web group (Sanctum's stateful pipeline); cookie encryption except `appearance`/`sidebar_state` |
| API auth | Sanctum personal access tokens (bearer); plain-text token shown once; revocation on logout; `auth:sanctum` on every protected endpoint; JSON 401/403 exception shaping for `api/*` |
| Password policy | Production default: ≥12 chars, mixed case, letters, numbers, symbols, compromised-password check; `confirmed` on all set flows; hashes never serialized (`$hidden`) |
| 2FA secrets | Encrypted at rest by Fortify; hidden from serialization |
| Mass assignment | Explicit `$fillable` on all models; controllers set fields deliberately; `$hidden` covers sensitive attributes |
| Validation | Every write path goes through a FormRequest with typed rules + custom messages; settings validation is registry-driven |
| Rate limiting | `login` (5/min/email+IP), `two-factor` (5/min/session), `throttle:6,1` on password change; no custom API limiter configured |
| Owner scoping | Notification reads resolve through `$user->notifications()->findOrFail()` — no cross-user access |
| Destructive safety | `DB::prohibitDestructiveCommands()` in production; delete-self and self-demotion blocked; system roles immutable |
| Upload safety | Stored with hashed names under a fixed directory, size-capped (20 MB), served via authorized download route rather than public URLs; thumbnails skip SVG |
| Secrets/.env handling | `.env` gitignored; `.env.example` holds placeholders only; config accessed via `config()` in code; CI never receives secrets; documentation must contain placeholders only |

---

## 31. Current Verified State

Everything below was executed/inspected in this working session:

| Check | Result |
|---|---|
| Test suite (`php artisan test --compact`) | ✅ **127 passed, 2 skipped, 768 assertions** (~12s). Skips = GD-thumbnail cases (GD extension absent locally) |
| Migration status | All 13 migrations **Ran** on a fresh migrate; **note:** the current local SQLite dev DB shows 2 pending migrations (`2026_08_23_210723_add_locale_to_users_table`, `2026_08_23_212200_add_thumbnail_to_media_table`) until `php artisan migrate` is run |
| Seeders | Verified by reading + fresh-migrate seeding path: roles/permissions/settings/admin user |
| Pint (`vendor/bin/pint --test --parallel`) | ✅ pass |
| ESLint (`npm run lint:check`) | ✅ pass |
| Prettier (`npm run format:check`) | ✅ pass |
| TypeScript (`npm run types:check`) | ✅ pass |
| Vite build (`npm run build`) | ✅ succeeds (production assets emitted) |
| Route inventory | 52 non-vendor routes verified via `route:list` |
| PostgreSQL verification | Code-level verification only: `pgsql` connection configured; pgsql-specific search branch present. No live PostgreSQL instance was queried in this session |
| SQLite verification | Active: local `.env` + test runs execute on SQLite |
| RBAC | Roles/permission catalog verified in seeder + `SystemRole`; Gate::before verified in provider; access covered by `RoleAccessTest` |
| Git | Branch `main`; HEAD `0688a7e` "Complete template gap closure…" |

---

## 32. Known Limitations / Future Considerations

Deferred by design (not a wishlist):

- **Department/team scoping** — Spatie teams disabled; enabling later requires
  pivot-table migrations and policy updates.
- **Advanced import formats** — engine is CSV-only; XLSX/JSON/XML ingestion would
  need adapters around `CsvImportEngine`'s contract.
- **Import UI** — engine + `import_runs` exist; screens intentionally absent.
- **API rate limiting tiers** — no named `api` limiter; add before exposing publicly.
- **Settings caching** — settings are queried per request; add a cache layer if a
  project scales traffic.
- **No PHP static analysis** (PHPStan/Psalm) in the toolchain.
- **No JS unit/browser tests** — frontend correctness rests on types/lint/build.
- **Scheduled tasks & recurring exports** — scheduler registered-but-empty.
- **Search depth** — global search caps at 5 hits/group, no full-text index;
  fine for admin scale, revisit for large datasets.
- **Single locale shipped** — `en` only; the localization plumbing (registry,
  middleware, `useT`) is ready for more.

---

## 33. Important Files Reference

| Area | Important Files | Purpose |
|------|-----------------|---------|
| Bootstrap | `bootstrap/app.php`, `bootstrap/providers.php` | Routing wiring, middleware aliases/append, API exception shaping |
| Providers | `app/Providers/AppServiceProvider.php`, `app/Providers/FortifyServiceProvider.php` | Gate/policy registration, Super Admin bypass, macros, security defaults, auth events; Fortify views/actions/rate limiters |
| RBAC core | `app/Support/SystemRole.php`, `database/seeders/RolePermissionSeeder.php`, `config/permission.php` | Role constants, permission catalog + role mapping, teams off |
| Auth config | `config/fortify.php`, `app/Actions/Fortify/*`, `app/Concerns/*ValidationRules.php` | Feature flags, home path, registration/reset logic, password rules |
| Authorization | `app/Policies/*.php` | Permission-string mapping per model |
| Shared props | `app/Http/Middleware/HandleInertiaRequests.php` | auth.can, settings, flash, locale, notifications |
| User mgmt | `app/Http/Controllers/Admin/UserManagementController.php`, `app/Http/Requests/Admin/*User*.php` | CRUD + protections |
| Roles mgmt | `app/Http/Controllers/Admin/RoleManagementController.php` | CRUD + system-role guards + permission grouping |
| Settings | `app/Support/SettingRegistry.php`, `app/Support/SettingStore.php`, `app/Http/Controllers/Admin/SettingsManagementController.php` | Registry/store/controller trio |
| Notifications | `app/Notifications/SystemMessageNotification.php`, `app/Http/Controllers/NotificationController.php` | Message shape + center |
| Audit | `app/Support/ActivityLogger.php`, `app/Models/ActivityLog.php`, `app/Http/Controllers/ActivityLogController.php` | Write + read paths |
| Media | `app/Support/MediaUploader.php`, `app/Models/Media.php`, `app/Http/Controllers/Admin/MediaManagementController.php` | Upload/thumbnail/delete lifecycle |
| Search | `app/Http/Controllers/GlobalSearchController.php`, `app/Providers/AppServiceProvider.php` (macro) | Grouped permission-aware search |
| Import/export | `app/Support/Import/*`, `app/Http/Controllers/ExportCenterController.php`, `resources/views/exports/summary.blade.php` | Engines + endpoints |
| API | `routes/api.php`, `app/Http/Controllers/Api/V1/*`, `app/Http/Resources/Api/V1/*`, `app/Support/ApiPagination.php` | v1 surface |
| Frontend shell | `resources/js/app.ts`, `resources/js/layouts/AppLayout.vue`, `resources/js/navigation/app.ts`, `resources/js/types/index.ts` | Entry, layout, nav definition, shared types |
| Design system | `resources/js/components/ui/**`, `components.json`, `resources/css/app.css` | shadcn-vue primitives + Tailwind v4 entry |
| Tests | `tests/Pest.php`, `tests/TestCase.php`, `tests/Feature/RoleAccessTest.php`, `tests/Feature/Admin/*` | Conventions + critical behavior coverage |
| Quality/CI | `composer.json` (scripts), `.github/workflows/lint.yml`, `.github/workflows/tests.yml`, `pint.json`, `eslint.config.js`, `.prettierrc`, `tsconfig.json` | Gates |
| Ops/docs | `README.md`, `AGENTS.md`, `.env.example`, `master-starter-api.postman_collection.json`, `TheRoadmap/` | Setup, AI rules, placeholders, API collection, process docs |

---

## 34. Final Architecture Summary

- **Philosophy:** a hardened, domain-neutral core; generic capabilities live in
  `app/Support`, project logic gets layered on top without touching the core.
  Everything is permission-gated and audit-logged by default.
- **Reusable core:** Fortify auth (incl. 2FA), Spatie RBAC with protected system
  roles, users/roles administration, notification center, activity trail, media
  library, settings registry, global search, export center + CSV import engine,
  API v1 scaffold, and a full Vue admin shell with a shadcn-vue design system.
- **Security model:** session auth + TOTP for people, Sanctum tokens for machines;
  layered authorization (middleware → request → policy → Gate bypass); strict
  password policy; production command guards; secrets confined to `.env`.
- **Database model:** seven application-owned tables (users + auth satellites,
  Spatie trio + pivots, notifications, activity_logs, settings, media,
  import_runs) plus Laravel infrastructure tables (cache, jobs, sessions).
  PostgreSQL for production-grade deployments, SQLite for zero-config starts/tests.
- **Frontend model:** Inertia v2 SPA — server-rendered props drive typed Vue
  pages; Wayfinder keeps route calls type-safe; Tailwind v4 + reka-ui deliver the
  visual system; shared `auth.can` props make the UI mirror backend permissions.
- **API model:** versioned `/api/v1`, Sanctum bearer flow, small resource set,
  uniform pagination envelope, JSON error normalization — a pattern to copy, not
  just consume.
- **Extension philosophy:** clone → rename → seed → add your domain module
  following the Section 29 checklist; reuse auth/RBAC/settings/media/notifications/
  audit/search/UI wholesale; keep business specifics out of the master starter.

---

*Document scope: commit `0688a7e` (branch `main`). Regenerate this inventory after
material architectural changes.*

---

## Appendix A — Evolution from the Business Starter Kit

The old **Business Starter Kit** (pre-cleanup `main`, commit `4d697cd`) was a
platform shell with business examples layered on top: Pages/CMS, Notes, Reports,
Handbook, a public website, five roles, and 27 permissions. The cleanup phases
(recorded in `AlphaFix1.md`–`AlphaFix6.md` at the repo root) transformed it into
the domain-neutral **Laravel Master Starter** described by this document. This
appendix records exactly what was removed, added, and changed so future agents
understand why current code looks the way it does.

### A.1 Removed modules (intentional deletions, 44+ files)

| Old module | What existed before | Removed in |
|---|---|---|
| **Pages / CMS** | `Page` model, `PageStatus` enum + workflow state machine (`WorkflowTransitionRegistry`), `PagePolicy`, page CRUD/import controllers + requests, public slug catch-all (`PublicPageController`), soft deletes + restore flow, 3 pages migrations, `PageFactory`, 4 `admin/Pages/*.vue`, `public/Pages/Show.vue` | Phase 2 (`AlphaFix1.md`) |
| **Notes** | `Note` model/policy/controller/request, `NoteableRegistry` whitelist, `NotePresenter`, `notes` migration, `NoteFactory`, `NotesPanel.vue`, note tests | Phase 2 |
| **Reports** | `ReportsController`, filterable pages report + filtered CSV export (`reports.view`), `reports/Index.vue` | Phase 2 |
| **Handbook** | `HandbookController`, `HandbookLibrary` markdown renderer serving `TheRoadmap/` docs in-app, `handbook/Index.vue`, guest-accessible handbook route | Phase 2 |
| **Public website** | `Welcome.vue` landing page, `PublicLayout.vue`, settings-driven marketing copy | Phase 2 |
| **Demo data** | Six demo accounts, `ActivityLogSeeder`, dashboard widgets test | Phase 2 |
| **Machine-specific AI config** | `.codex/config.toml`, `.gemini/settings.json` (contained `/Users/yonassayfu/Herd/distro-app` paths), duplicate `GEMINI.md` | Phase 4 (`AlphaFix4.md`) |

Corresponding permission groups dropped: all `pages.*`, all `notes.*`,
`reports.view`, `handbook.view` (**27 → 18 permissions**).

### A.2 Added capabilities (not present in the old kit)

| Capability | Detail | Added in |
|---|---|---|
| **Generic CSV import engine** | `App\Support\Import\{CsvImportEngine,ImportPreview,ImportRowError}` — two-phase preview→commit, header contract, row-level errors, `import_runs` history. Replaces the old Pages-specific importer with domain-neutral infrastructure | Phase 6 (`AlphaFix6.md`, T3) |
| **Media API endpoint** | `GET /api/v1/media` (`MediaApiController`) — paginated listing with search/collection filters; previously `MediaResource` existed with no consuming route | Phase 6 (T1) |
| **Image thumbnails** | `thumbnail_path` column, `MediaUploader::generateThumbnail()` (Intervention Image v4/GD, ≤400px JPEG), graceful degradation without GD | Phase 6 (T4) |
| **XLSX export** | `GET /exports/users.xlsx` via `spatie/simple-excel` | Phase 6 (T5) |
| **PDF summary export** | `GET /exports/summary.pdf` via `barryvdh/laravel-dompdf` + shared Blade template | Phase 6 (T6) |
| **XML export** | `GET /exports/users.xml` via `spatie/array-to-xml` | Phase 6 (T7) |
| **i18n scaffolding** | `locale` users column, `App\Support\Locales` registry, `HandleLocale` middleware, shared `translations`/`availableLocales` props, `useT()` composable, `lang/en/messages.php`, profile language selector | Phase 6 (T8) |
| **Portable search macro** | `searchLike()` builder macro replacing pgsql-only `ilike` usage that silently broke SQLite across 7 controllers | Phase 6 (bonus fix) |
| **Unit test suite restored** | `tests/Unit/SystemRoleTest.php` (suite had been deleted during cleanup, aborting `php artisan test`) | Phase 6 (bonus fix) |
| **Postman collection** | Importable v1 API collection (renamed to `master-starter-api.postman_collection.json`) | Formalized Phase 4 |

New packages installed (Phase 6 only): `intervention/image ^4.3`,
`spatie/simple-excel ^3.10`, `barryvdh/laravel-dompdf ^3.1`,
`spatie/array-to-xml ^3.4`. Phases 2–5 removed/installed **zero** packages.

### A.3 Changed items (old → new)

| Area | Business Starter Kit | Laravel Master Starter |
|---|---|---|
| Roles | Admin, Manager, Member, ReadOnly, External (hardcoded strings) | Super Admin, Manager, Staff, Guest — centralized in `app/Support/SystemRole.php`; Guest has zero permissions |
| Permission count | 27 | 18 |
| Gate bypass | `hasRole('Admin')` literal | `$user->hasRole(SystemRole::SUPER_ADMIN)` constant |
| Post-login routing | Fortify `home => '/dashboard'` | `home => '/'` + permission-aware root dispatcher (Guest → profile, capable → dashboard); covered by `PostLoginRedirectTest` |
| Dashboard | Business widgets + report highlights | Generic metrics (users/roles/media/activity) + permission-aware quick links |
| Root URL | Public landing page (`Welcome.vue`) | Auth-aware redirect: guests → login |
| Navigation | Core/Insight/Management incl. Pages, Reports, Handbook | Reduced to generic modules only (Dashboard, Notifications, Activity logs, Exports, Settings, Media, Users, Roles) |
| Identity/branding | Mixed: `yonassayfu/starter-core`, `distro-app`, `Starter Core` | Unified `laravel-master-starter` / "Laravel Master Starter" across composer, package, `.env.example`, README, Postman, settings defaults, app logo |
| Settings registry | Application/Organization/Public groups (12 fields, site copy for CMS) | Application/Organization groups only (7 fields, domain-neutral defaults) |
| Search scope | Users, roles, notifications, activity logs, pages | Users, roles, notifications, activity logs |
| Repo identity | `yonasayfu/business-starter-kit` | `elefensh-yona/laravel-master-starter` (GitHub migrated Phase 5; PR #1 merged cleanup into `main` as `90f54e8`) |
| Test suite | ~99 tests incl. module tests for removed features | 129 tests (127 pass, 2 GD-skip), all covering current modules |

### A.4 Preserved unchanged through the transition

Fortify auth stack (all flows incl. 2FA), Sanctum API v1 pattern,
`ApiPagination` envelope + versioned resources, notification center +
`SystemMessageNotification`, activity-log system, media system (incl. unused
`attachable` morph — still prepared-but-unused), settings registry/store split,
export center foundation, global search shell, full shadcn-vue `ui/` library, all
layouts/auth pages, SSR pipeline, CI workflows, `import_runs` table/factory,
`TheRoadmap/` planning archive.

### A.5 Phase history reference

| Phase | File | Outcome |
|---|---|---|
| Phase 2 — Boilerplate cleanup | `AlphaFix1.md` | 44 files deleted (Pages/CMS, Notes, Reports, Handbook, public site, demo data); roles normalized; 27→18 permissions; routes pruned |
| Phase 3 — Verification (+ PostgreSQL report) | `AlphaFix2.md`, `AlphaFix3.md` | Full suite green on fresh SQLite; first-class PostgreSQL verification on isolated DB `boilerplate_pg_verify`; dual-database strategy proven |
| Phase 4 — Freeze & rebrand | `AlphaFix4.md` | Machine-specific AI configs removed; "Laravel Master Starter" branding sweep; freeze commit `0c81577` |
| Phase 5 — GitHub migration | `AlphaFix5.md` | Remote repointed to `Elefensh-Yona/laravel-master-starter`; PR #1 merged (`90f54e8`) |
| Phase 6 — Template gap closure | `AlphaFix6.md` | T1–T10: Media API, import engine, i18n, thumbnails, XLSX/PDF/XML exports, SSR validation, `searchLike` macro; tests 99→129 |

> Note on naming: the repository *folder* is still named `business-starter-kit`
> locally (historical artifact of the clone location). The application itself is
> the Laravel Master Starter everywhere it matters (composer name, package name,
> APP_NAME, README, docs). Renaming the folder is cosmetic and optional.
