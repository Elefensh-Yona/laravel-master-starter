# AI Project Starter Guide

> **Read this document FIRST if you are an AI coding agent working on a project
> cloned from Laravel Master Starter.**
>
> This is the onboarding/orientation layer. The deep technical reference lives in
> `MASTER-STARTER-ARCHITECTURE.md`. Do not start coding until you have read both.

---

## 1. What This Project Is

This project was created from the **Laravel Master Starter**: a reusable,
**domain-neutral** Laravel 12 / Vue 3 application foundation providing
authentication, RBAC, user administration, notifications, audit logging, media
management, settings, search, import/export, and a versioned JSON API baseline —
plus a complete Vue admin shell.

Two facts define your situation:

1. **Everything in Sections 3–10 already exists and works.** It is inherited
   infrastructure, tested and documented. Your job is to build *on top of* it,
   not around it.
2. **This repository may be a new downstream project.** Project-specific
   requirements (domain entities, workflows, business rules) will be supplied by
   the human developer and added on top of the inherited foundation. If no
   project-specific requirements exist yet, ask for them before implementing.

The starter deliberately contains **no business domain**. If you find yourself
inventing customers, orders, or pages — stop; those decisions belong to the
developer (see Section 14).

---

## 2. Read These Documents First

Read in this order:

| # | Document | Purpose |
|---|---|---|
| 1 | `AI-PROJECT-STARTER.md` (this file) | Orientation: what you inherited, what to reuse, what not to rebuild |
| 2 | `AGENTS.md` | Binding repository working rules: package versions, mandatory skill activation, code style, testing enforcement |
| 3 | `MASTER-STARTER-ARCHITECTURE.md` | Deep technical reference: every system, table, route, policy, verified state |
| 4 | `README.md` | Human-facing setup: install, database options, deployment baseline, seeded account |
| 5 | `TheRoadmap/` documents (`BoilerplateTaskList.md`, `decisions.md`, etc.) | Historical planning/decisions of the starter itself — context, not active requirements |
| 6 | **Project-specific requirements supplied by the developer** | The actual business goal of this downstream project (may not exist yet — request them) |
| 7 | **Database schema supplied by the developer** | Domain tables planned beyond the inherited schema (if any) |
| 8 | **Project roadmap** (e.g. `PROJECT-ROADMAP.md`) | Ordered implementation phases for this project (if it exists) |

Items 6–8 are expected to be created per-project (see Section 21). If they are
missing, that is normal for a fresh clone — ask the developer rather than
guessing requirements.

---

## 3. What You Already Inherited

Inventory of capabilities that are present, working, and tested right now.
"Reuse" means extend/wire these systems; "Do not rebuild" means do not create a
parallel implementation.

| Capability | Where it lives | Reuse | Do NOT rebuild |
|---|---|---|---|
| **Authentication** (login/logout) | Fortify + `app/Providers/FortifyServiceProvider.php` | Fortify routes/actions; add guards/features only via config | Custom login controllers, session handling, auth guards |
| **Registration** | `app/Actions/Fortify/CreateNewUser.php`, `auth/Register.vue` | Extend validation via `app/Concerns/*ValidationRules` | A second signup flow |
| **Password reset** | `app/Actions/Fortify/ResetUserPassword.php`, `auth/ForgotPassword.vue`, `ResetPassword.vue` | Existing flow + `password_reset_tokens` table | Custom token handling |
| **Email verification** | `User implements MustVerifyEmail`, `auth/VerifyEmail.vue` | Built-in notifications + re-verification-on-email-change behavior (already wired in profile & user update) | Manual verification logic |
| **2FA (TOTP)** | `TwoFactorAuthenticatable` trait, `SecurityController`, `TwoFactorSetupModal.vue`, `useTwoFactorAuth.ts` | Existing setup/challenge/recovery flows | Alternative TOTP implementations |
| **Recovery codes** | Same 2FA stack, `TwoFactorRecoveryCodes.vue` | As-is | Regeneration schemes |
| **Password confirmation gate** | `password.confirm` middleware (used by SecurityController) | Apply to new sensitive actions | Ad-hoc "re-enter password" forms |
| **Profile/security screens** | `app/Http/Controllers/Settings/*`, `resources/js/pages/settings/*` | Add fields through existing controllers/requests | New settings pages duplicating profile/security |
| **RBAC** | Spatie Permission v7 + `app/Policies` + `permission:` middleware | Permission strings, policies, seeder pattern | Any second authorization system (Section 5) |
| **Users admin CRUD** | `Admin\UserManagementController`, `admin/Users/*.vue` | Pattern + self-protection rules | Parallel user management |
| **Roles admin CRUD** | `Admin\RoleManagementController`, `admin/Roles/*.vue` | Grouped permission editor UI | Hardcoded role editing |
| **Dashboard shell** | `DashboardController`, `pages/Dashboard.vue`, `StatCard.vue`, `RecentActivityPanel.vue` | StatCard/panel components for project dashboards | A second dashboard framework |
| **Settings registry** | `app/Support/SettingRegistry.php`, `SettingStore.php`, `admin/Settings/Edit.vue` | Add fields/groups to the registry — UI/validation/seeding follow automatically | A second key-value config store or `.env`-driven runtime settings |
| **Notifications center** | `SystemMessageNotification`, `NotificationController`, `NotificationBell.vue`, API endpoints | Send via the notification class; bell/count already shared to frontend | Custom notification tables/polling |
| **Activity/audit log** | `ActivityLogger::record()`, `ActivityLog` model, `activity-logs/*` pages + API | One call per mutation with a dotted event name | spatie/laravel-activitylog or custom audit tables |
| **Media library** | `Media` model, `MediaUploader`, `MediaManagementController`, `admin/Media/Index.vue`, `GET /api/v1/media` | Upload/download/delete/thumbnails; attach via `attachable` morphs | Per-feature upload pipelines |
| **Global search** | `GlobalSearchController` + `searchLike()` macro | Add a permission-gated group for new modules | External search engines for admin-scale needs |
| **Import/export** | `App\Support\Import\CsvImportEngine`, `ExportCenterController` (CSV/XLSX/XML/print/PDF) | Engine for new imports; export-center pattern for new exports | One-off CSV parsers, ad-hoc download endpoints without auditing |
| **API v1 baseline** | `routes/api.php`, `Api\V1\*` controllers/resources, `ApiPagination` | Follow conventions for new endpoints (Section 9) | Unversioned or unauthenticated JSON routes |
| **Frontend UI system** | `resources/js/components/ui/**` (shadcn-vue/reka-ui), `components/admin/**` kit | Compose primitives; use ResourceTable/FormSection/ConfirmActionDialog | Copying raw HTML widgets, adding another component library |
| **i18n foundation** | `App\Support\Locales`, `HandleLocale` middleware, `lang/<code>/messages.php`, `useT()` composable | Register a locale + add translation files | Hardcoded strings where translations exist; a second i18n layer |
| **Testing foundation** | Pest 4 suite (129 tests), factories, `skipUnlessFortifyFeature()` helper | Extend patterns per module | PHPUnit-style classes, deleting suites |
| **CI/quality tooling** | GitHub Actions (`lint.yml`, `tests.yml`), Pint, ESLint, Prettier, vue-tsc, `composer ci:check` | Keep gates green | Disabling checks to pass |

---

## 4. Authentication You Already Have

Stack: **Laravel Fortify v1.30+** (headless backend, `web` guard) with Inertia
Vue views. Nothing about auth needs rebuilding.

Enabled features (`config/fortify.php`): registration, password reset, email
verification, two-factor authentication (with `confirm` + `confirmPassword`
options).

Where to inspect before touching anything auth-related:

| Concern | Files |
|---|---|
| Feature flags, username field, home path, limiters wiring | `config/fortify.php` |
| View/action/rate-limiter registration (login 5/min per email+IP, 2FA 5/min) | `app/Providers/FortifyServiceProvider.php` |
| User creation & password reset contracts | `app/Actions/Fortify/CreateNewUser.php`, `app/Actions/Fortify/ResetUserPassword.php` |
| Shared validation rule bundles | `app/Concerns/PasswordValidationRules.php`, `app/Concerns/ProfileValidationRules.php` |
| Profile update / account self-deletion | `app/Http/Controllers/Settings/ProfileController.php` |
| Password change (throttled 6/min) + 2FA management | `app/Http/Controllers/Settings/SecurityController.php` |
| Production password policy (min 12, mixed case, numbers, symbols, uncompromised) | `app/Providers/AppServiceProvider.php` (`Password::defaults()`) |
| Post-login routing | `config/fortify.php` (`home => '/'`) → permission-aware redirect in `routes/web.php` (Guest → profile edit; `dashboard.view` holders → dashboard); regression-tested by `tests/Feature/Auth/PostLoginRedirectTest.php` |
| Auth pages | `resources/js/pages/auth/*` (Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword, TwoFactorChallenge) |
| 2FA frontend | `resources/js/composables/useTwoFactorAuth.ts`, `components/TwoFactorSetupModal.vue`, `components/TwoFactorRecoveryCodes.vue` |

Rules: email changes clear `email_verified_at` (re-verification required) — keep
this behavior when extending profile/user updates. Account deletion logs out,
invalidates the session, and regenerates the CSRF token — preserve that sequence.

---

## 5. Authorization / RBAC You Already Have

Powered by **spatie/laravel-permission v7**, single `web` guard,
**teams disabled** (`config/permission.php`: `'teams' => false`).

### Roles (exactly four, defined in `app/Support/SystemRole.php`)

| Role | Meaning |
|---|---|
| **Super Admin** | Full-access recovery role; bypasses every gate via `Gate::before` |
| **Manager** | Operational visibility + user administration (9 permissions) |
| **Staff** | Standard internal user (dashboard.view, search.view, notifications.view) |
| **Guest** | Authenticated with **zero permissions**; lands on profile screen, gets 403 everywhere else |

### How authorization works (defense in depth)

1. **Route middleware** — `->middleware('permission:x.y')` on protected routes
   (`routes/web.php`, `routes/api.php`; aliases registered in `bootstrap/app.php`).
2. **FormRequest `authorize()`** — every write request re-checks its permission.
3. **Controller `$this->authorize()`** — policy checks inside admin controllers.
4. **Policies** — `app/Policies/{User,Role,Media,Setting}Policy.php`; each method
   maps to exactly one permission string; registered via `Gate::policy()` in
   `AppServiceProvider`.
5. **Super Admin bypass** — `Gate::before(...)` in `AppServiceProvider`
   returns `true` for Super Admin on every ability.
6. **Frontend capability props** — shared `auth.can` map +
   `permission` fields in `resources/js/navigation/app.ts`. These are UX only;
   never treat them as enforcement.

### Permission catalog (18, seeded idempotently by `database/seeders/RolePermissionSeeder.php`)

```
dashboard.view      search.view         exports.view
settings.view       settings.update
media.view          media.create        media.delete
users.view          users.create        users.update    users.delete
roles.view          roles.create        roles.update    roles.delete
notifications.view  activity-logs.view
```

**Naming convention:** `<resource>.<action>` (lowercase noun, dot, verb).
Direct user permissions (`model_has_permissions`) are technically supported but
**unused** — roles only.

### Protections you must preserve

- System roles cannot be renamed/deleted; Super Admin's permissions cannot be
  edited from the UI (`RoleManagementController`).
- Users cannot delete themselves nor remove their own Super Admin role
  (`UserManagementController`).

### Explicit prohibitions

- ❌ **DO NOT create a second authorization system** (no parallel ACL tables, no
  custom middleware replacing `permission:`).
- ❌ **DO NOT bypass existing policies** (no skipping `$this->authorize()`,
  no route-level shortcuts around FormRequest authorization).
- ❌ **DO NOT assign direct user permissions** unless a future project decision
  explicitly requires them — roles only.
- ❌ **DO NOT hardcode role-name literals** — import `App\Support\SystemRole`.
- ✅ To add a feature: add `resource.action` permissions to the seeder, gate the
  route, authorize the request/controller, add a policy method, and expose a
  shared `auth.can` flag if the UI needs it.

---

## 6. Reusable Backend Infrastructure

### Settings
- **WHERE:** `app/Support/SettingRegistry.php` (definitions),
  `app/Support/SettingStore.php` (read/sync/shared), `Admin\SettingsManagementController`,
  `settings` table, `SettingsSeeder`.
- **HOW TO EXTEND:** add fields/groups to the registry; validation
  (`UpdateSettingsRequest`) and admin UI pick them up automatically; control what
  reaches every page via `SettingStore::shared()`.
- **REUSE:** for any runtime-configurable value (branding, contact info, toggles).
- **NOT TO REBUILD:** a second settings table, `.env` reads at runtime, cached
  config singletons for editable values.

### Notifications
- **WHERE:** `app/Notifications/SystemMessageNotification.php`
  (`title`, `message`, `actionUrl`, `actionLabel`, `level`), database channel;
  controller `NotificationController`; API under `/api/v1/notifications*`;
  unread count + 5-item preview shared to Inertia.
- **HOW TO EXTEND:** instantiate the class anywhere users must be informed; keep
  the `data` payload keys aligned with the presentation shape.
- **REUSE:** for all user-facing messages.
- **NOT TO REBUILD:** custom notification tables or polling endpoints.

### Activity / audit logging
- **WHERE:** `app/Support/ActivityLogger::record(actor, event, description,
  subject, properties, request)` → `activity_logs` table; automatic
  `auth.login`/`auth.logout` listeners in `AppServiceProvider`.
- **HOW TO EXTEND:** one static call per mutation with a dotted event name
  (`projects.created`). Index filters/search adapt automatically.
- **REUSE:** for every state-changing action in your domain.
- **NOT TO REBUILD:** bespoke audit columns/tables per entity.

### Media
- **WHERE:** `app/Models/Media.php` (+ nullable `attachable` morphs),
  `app/Support/MediaUploader.php` (store ≤20 MB files, GD thumbnails ≤400px,
  safe deletion), `Admin\MediaManagementController`, `GET /api/v1/media`.
- **HOW TO EXTEND:** attach media to domain models via `attachable` morphs;
  reuse `MediaUploadField.vue` in forms.
- **REUSE:** for any file-handling need.
- **NOT TO REBUILD:** per-entity upload storage or thumbnail logic.

### Search
- **WHERE:** `app/Http/Controllers/GlobalSearchController.php`;
  `searchLike()` builder macro registered in `AppServiceProvider` (portable
  across SQLite and PostgreSQL).
- **HOW TO EXTEND:** add a private `searchX()` group gated by its permission in
  `buildResults()`.
- **REUSE:** quick-jump admin search across modules.
- **NOT TO REBUILD:** a search engine for admin scale; unindexed LIKE scans over
  huge domain tables without measuring first.

### Import / Export
- **WHERE:** `app/Support/Import/{CsvImportEngine,ImportPreview,ImportRowError}.php`
  (two-phase preview→commit, records `import_runs`);
  `ExportCenterController` (users CSV/XLSX/XML, workspace print + PDF via dompdf);
  pages `resources/js/pages/exports/*`.
- **HOW TO EXTEND:** implement a row-handler + persister for domain imports;
  add audited export methods for domain data.
- **REUSE:** the engine contract (validate-first, persist-second, history row).
- **NOT TO REBUILD:** one-off parsers that skip preview/history; unaudited downloads.

### API
- See Section 9. **NOT TO REBUILD:** unversioned JSON endpoints outside `api/v1`.

### Shared services/conventions
- **WHERE:** everything in `app/Support/` (`ApiPagination`, `Locales`,
  `SystemRole`), `app/Concerns/` (validation bundles), `app/Http/Resources/Api/V1/`.
- **HOW TO EXTEND:** follow the registry/store split and dotted-event naming.
- **REUSE:** before writing any new helper, check whether one exists here.

---

## 7. Reusable Frontend Infrastructure

Stack: Vue 3 (`<script setup lang="ts">`) + Inertia v2 + TypeScript (vue-tsc)
+ Tailwind CSS v4 + Vite 7 (SSR-capable) + Wayfinder-generated typed routes.

| Asset | Location | Use it for |
|---|---|---|
| Layouts | `resources/js/layouts/` — `AppLayout` (sidebar shell), `AuthLayout` variants, `settings/Layout.vue` | Every new page nests in the appropriate layout |
| Sidebar navigation | `resources/js/navigation/app.ts` (typed `NavGroup[]` with `permission` keys) filtered by `AppSidebar.vue` | Adding project nav items with permission gating |
| Design-system primitives | `resources/js/components/ui/**` — button, input, select, checkbox, dialog, sheet, dropdown-menu, card, badge, avatar, breadcrumb, sidebar, skeleton, spinner, tooltip, alert, label, separator, collapsible, navigation-menu, input-otp | All interactive elements; compose, don't restyle ad hoc |
| Admin building blocks | `resources/js/components/admin/` — `ResourceTable(+Skeleton)`, `ResourceToolbar`, `ResourcePagination`, `FormSection(+Skeleton)`, `ConfirmActionDialog`, `StatCard`, `RecentActivityPanel`, `StatusBadge`, `UserRoleCard`, `RolePermissionCard`, `MediaUploadField`, `ActionIconLink` | List pages, forms, destructive confirmations, dashboards |
| App components | `PageHeader`, `PageContainer`, `Breadcrumbs`, `FlashMessages`, `EmptyState`, `LoadingState`, `PageErrorState`, `Heading`, `InputError`, `PasswordInput`, `TextLink`, `NotificationBell`, `UserInfo`, `AppearanceTabs` | Page scaffolding and feedback states |
| Forms | Inertia `useForm` + Wayfinder `.form()` action variants | All mutations; errors render through shared InputError patterns |
| Tables/lists | `ResourceTable` consuming `PaginatedResource<T>` shapes from Laravel paginators | Any index page |
| Notifications UI | `NotificationBell.vue` (header dropdown fed by shared props), `pages/notifications/Index.vue` | Already global — nothing to wire |
| Settings UI | `pages/admin/Settings/Edit.vue` renders whatever the registry defines | Extending settings requires no new UI work |
| Media UI | `pages/admin/Media/Index.vue` + `MediaUploadField.vue` | File pickers in domain forms |
| Search UI | `pages/search/Index.vue` | Renders grouped results automatically once backend groups exist |
| Dashboard shell | `pages/Dashboard.vue` + `StatCard` + `RecentActivityPanel` | Template for project-specific dashboards |
| Shared props/types | `HandleInertiaRequests::share()` (auth.user/roles/permissions/can, settings, flash, locale, translations, notificationCount/Preview, sidebarOpen); types in `resources/js/types/` | Read capabilities/settings from `usePage()`; extend TS types for new domains |
| Composables | `useT` (translations), `useAppearance`, `useInitials`, `useCurrentUrl`, `useTwoFactorAuth` | Before writing a new composable, check here |
| Generated routes | `resources/js/routes/**`, `resources/js/actions/**` — **Wayfinder output, never hand-edit**; regenerate with `php artisan wayfinder:generate --with-form` after changing routes/controllers | All internal links/submissions |

**Rule: reuse the existing component/design system before creating any new
component.** A new component is justified only when no primitive/kit composition
achieves the result.

---

## 8. Database Foundation

Default connection SQLite (zero-config; used by tests via `:memory:`);
PostgreSQL fully configured and explicitly handled in code (see
`MASTER-STARTER-ARCHITECTURE.md` §2). Choose per project in `.env`.

### CORE INFRASTRUCTURE (inherited — do not modify unnecessarily)

| Table(s) | Purpose |
|---|---|
| `users` | Accounts (email unique, hashed password, verification timestamp, `locale`, Fortify 2FA columns) |
| `password_reset_tokens`, `sessions`, `personal_access_tokens` | Reset tokens, DB sessions, Sanctum API tokens |
| `permissions`, `roles`, `role_has_permissions`, `model_has_roles`, `model_has_permissions` | Spatie RBAC (roles carry a `description`; teams disabled) |
| `notifications` | Database-channel notifications |
| `activity_logs` | Audit trail (nullable actor FK nullOnDelete, polymorphic subject, JSON properties, IP) |
| `settings` | Registry-backed key/value store (unique key, indexed group) |
| `media` | Uploaded files (uploader FK cascade, `attachable` morphs, unique path, `thumbnail_path`) |
| `import_runs` | CSV import history (status, counters, JSON summary/preview) |
| `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Default cache + queue infrastructure |

Seeders (`RolePermissionSeeder`, `SettingsSeeder`, `DatabaseSeeder`) are
idempotent and create roles/permissions/settings plus one bootstrap Super Admin
(`admin@example.com` / `password` — change immediately in a real deployment).
Factories exist for all five app models.

### PROJECT-SPECIFIC DOMAIN DATA (what future projects add)

Domain tables belong to the downstream project: new migrations, new models, new
factories. Rules:

- Add **new** migrations; do not rewrite core infrastructure migrations.
- Foreign-key domain records to `users` where appropriate; mirror core
  conventions (nullable FKs with explicit delete behavior; morphs for
  attachable/auditable relationships).
- Keep both SQLite and PostgreSQL compatibility (no driver-only column types;
  note the `searchLike` macro exists because pgsql-only SQL broke SQLite).
- Never run destructive commands against development data — production blocks
  them automatically via `DB::prohibitDestructiveCommands`.

---

## 9. API Foundation

- **Sanctum v4** personal access tokens; guard `web`; bearer-token flow:
  `POST /api/v1/auth/login` (requires `device_name`) → token →
  `Authorization: Bearer <token>` → `POST /api/v1/auth/logout` revokes.
- **Versioning:** everything lives under `/api/v1` (`routes/api.php`).
- **Existing endpoints** (complete list): auth login/me/logout; notifications
  index/show/mark-read/read-all; activity-logs index/show; `admin/summary`;
  `admin/users` (searchable); `media` index. Each protected endpoint carries
  `auth:sanctum` **and** a `permission:` middleware.
- **Conventions to follow when adding project endpoints:**
  - Controller in `app/Http/Controllers/Api/V1/`.
  - Input via FormRequest in `app/Http/Requests/Api/V1/`.
  - Output via a Resource in `app/Http/Resources/Api/V1/`.
  - Lists return the standard envelope from `app/Support/ApiPagination::response()`
    (`data`, `links.first/last/prev/next`, `meta.pagination.*`).
  - Gate with `permission:` middleware exactly like web routes.
  - Exception shaping is already configured in `bootstrap/app.php` (401/403 JSON
    for `api/*`) — do not add competing handlers.
- An importable Postman collection (`master-starter-api.postman_collection.json`)
  documents the current surface; extend it for new endpoints.

---

## 10. Testing Foundation

- **Framework:** Pest 4 (PHPUnit 12 underneath). `RefreshDatabase` applies to all
  Feature tests (`tests/Pest.php`). Test DB: SQLite `:memory:` with array drivers
  (`phpunit.xml`).
- **Current verified state:** 129 tests — 127 passed, 2 skipped (GD-dependent
  thumbnail cases), 768 assertions (recorded in
  `MASTER-STARTER-ARCHITECTURE.md` §31).
- **Suites:**
  - `tests/Feature/Auth/*` — login, registration, reset, verification,
    password confirmation, 2FA challenge, post-login redirect
  - `tests/Feature/Admin/*` — user/role CRUD, settings, media (+thumbnails),
    policy conventions (`PolicyConventionTest`)
  - `tests/Feature/Settings/*` — profile, security/password
  - `tests/Feature/Api/*` and `tests/Feature/Feature/Api/*` — Sanctum flow,
    admin users, media, notifications, activity logs, summary
  - `tests/Feature/Feature/*` — dashboard, exports, multi-format export,
    global search, notification center, activity-log index, role access matrix
  - `tests/Feature/Support/CsvImportEngineTest.php` — engine contract
  - `tests/Unit/SystemRoleTest.php` — system-role constants
- **Helpers:** model factories for all models; `TestCase::skipUnlessFortifyFeature()`
  for feature-flagged tests.
- **Expectations:**
  - Every meaningful new feature ships with appropriate tests (CRUD, policy,
    role-matrix coverage; API tests for new endpoints).
  - Run narrowly while developing: `php artisan test --compact --filter=...`;
    full suite before finishing: `php artisan test --compact`.
  - **Never delete or weaken existing tests to make the suite pass.** Update an
    assertion only when behavior legitimately changed, and say so explicitly.

---

## 11. What Was Intentionally Removed

These existed in the old Business Starter Kit and were **deliberately deleted**
during finalization (history: `AlphaFix1.md`–`AlphaFix6.md`; rationale in
`MASTER-STARTER-ARCHITECTURE.md` §28 and Appendix A):

- Pages/CMS (including workflow states, soft-delete/restore, slug routing)
- Public website / landing page
- Notes (polymorphic commenting)
- Reports module
- Handbook application (docs-in-app renderer)
- Project-specific business entities (none shipped with the starter by design)
- Departments / department scoping
- Spatie Teams (multi-tenancy — `'teams' => false`)
- Project-specific integrations

**Their absence is intentional.** Do NOT recreate them automatically, do NOT
"restore" them from old branches/tags, and do NOT treat their absence as a gap.
If a real downstream project genuinely needs one of these concepts, design it as
a project-specific requirement (requirements doc → schema → plan → approval) —
not as resurrected starter code.

---

## 12. What Is Domain-Neutral vs Project-Specific

### Master Starter / Reusable (already present)

Authentication · RBAC · Users/Roles administration · Settings registry ·
Notifications · Activity/audit logging · Media library · Global search ·
CSV import engine · Export center · API v1 scaffold · UI/design system ·
i18n foundation · Testing infrastructure · CI/quality gates

### Future Project / Domain-Specific (illustrative examples — **NONE exist today**)

Customers · Orders · Inventory · HR records · Finance/Ledgers · Projects ·
Contracts · Appointments · Tickets · Billing · Products · Warehouses …

> These examples describe *categories* of things future projects might build.
> They are **not** part of this repository. Do not scaffold any of them without
> developer-supplied requirements.

Keep domain logic in domain namespaces (controllers/models/pages for the domain)
and leave generic infrastructure (`app/Support`, core tables, core UI kit)
untouched except through documented extension points.

---

## 13. How a New AI Agent Should Start a Project

Follow this workflow. **Do not start coding just because you have repo access.**

| Step | Action |
|---|---|
| 1 | Read `AI-PROJECT-STARTER.md` (this file) |
| 2 | Read `AGENTS.md` (binding rules: versions, skills, style, testing) |
| 3 | Read `MASTER-STARTER-ARCHITECTURE.md` (deep reference) |
| 4 | Inspect current repository state: routes, models, seeders, tests, config — verify claims against code |
| 5 | Read the developer's project requirements (`PROJECT-REQUIREMENTS.md` or briefing). If absent → request them (Section 14) |
| 6 | Read the database schema (`DATABASE-SCHEMA.md` or supplied schema). If absent → propose one for approval |
| 7 | Read the project roadmap (`PROJECT-ROADMAP.md`) if it exists |
| 8 | Map each requirement onto inherited capabilities: what can be reused as-is vs extended |
| 9 | Identify genuinely new domain functionality (models, tables, permissions, screens) |
| 10 | Produce an implementation plan: migrations → permissions → policies → routes → requests/controllers → Wayfinder regen → pages/nav → tests |
| 11 | **Confirm architecture with the developer before large changes** (new tables, new permissions, new packages, auth/RBAC modifications) |
| 12 | Implement incrementally — smallest coherent slices, one concern at a time |
| 13 | Test every change (write/update Pest tests alongside the change) |
| 14 | Run full quality gates before declaring done: `composer ci:check` (Pint → Prettier → ESLint → vue-tsc → build → full test suite) |

---

## 14. What the Human Developer Should Provide

Checklist for kicking off a real project. Anything missing = ask, don't assume:

- [ ] **Project name** (and display name for the settings registry)
- [ ] **Project purpose** (one paragraph: what the product is and who uses it)
- [ ] **Business requirements** (features, priorities, out-of-scope list)
- [ ] **User types / roles** (map onto or extend Super Admin/Manager/Staff/Guest)
- [ ] **Permission requirements** (new `resource.action` entries needed?)
- [ ] **Domain entities** (the nouns of the business: entities, attributes, relations)
- [ ] **Database schema** (`DATABASE-SCHEMA.md`, ERD, or migration notes)
- [ ] **Workflows** (state machines, approvals, lifecycles)
- [ ] **Business rules** (validation constraints, computed values, edge cases)
- [ ] **UI requirements** (screens, list/form layouts, branding, dark-mode expectations)
- [ ] **API requirements** (consumers, new endpoints, auth scopes)
- [ ] **Import/export requirements** (formats, volumes, error tolerance)
- [ ] **Integrations** (third-party services, webhooks, credentials strategy)
- [ ] **Notifications** (which events notify whom, channels, levels/actions)
- [ ] **Reporting** (metrics, filters, export formats)
- [ ] **Security requirements** (extra password policy, audit depth, rate limits, 2FA mandates)
- [ ] **Deployment requirements** (target platform, environment, scheduler/queue needs)
- [ ] **Roadmap** (phased delivery order — see `PROJECT-ROADMAP.md`)
- [ ] **Known constraints** (budget, hosting limits, browser support, compliance)

---

## 15. AI Agent Rules

1. **Inspect before modifying.** Verify current behavior from source; never trust
   summaries (including this one) over the code.
2. **Reuse before rebuilding.** Check Sections 3–9 before creating anything.
3. **Do not duplicate existing systems.** One settings store, one audit trail,
   one notification pipeline, one authorization system.
4. **Do not introduce packages without justification and developer approval**
   (`AGENTS.md`: dependencies require approval).
5. **Follow existing naming conventions** — `resource.action` permissions,
   dotted activity events, controller/request/resource namespaces, TS type names.
6. **Follow the authorization architecture** — route middleware + FormRequest
   authorize + policy; never a shortcut.
7. **Preserve security boundaries** — Super Admin bypass, system-role locks,
   self-protection rules, CSRF/session teardown sequences.
8. **Preserve existing tests.** No deletions or weakened assertions without
   explicit approval and stated justification.
9. **Add regression tests** for every fixed bug and new behavior.
10. **Do not modify master-starter functionality unnecessarily.** Core edits need
    a reason recorded in the task/PR description.
11. **Keep domain logic separate from generic infrastructure** — new namespaces,
    not edits inside `app/Support` core classes.
12. **Never expose secrets** — no `.env` contents, keys, tokens, or machine paths
    in code, docs, commits, or chat output.
13. **Never modify production data during testing.** Tests run on SQLite
    `:memory:`; keep it that way.
14. **Do not run destructive database commands** (`migrate:fresh`, `db:wipe`,
    rollbacks on shared data) without explicit approval.
15. **Never claim something was tested if it was not.** Report exactly which
    commands ran and their results.
16. **Regenerate Wayfinder artifacts** after route/controller changes
    (`php artisan wayfinder:generate --with-form`); never hand-edit generated files.
17. **Run `vendor/bin/pint --dirty` after touching PHP files** and the frontend
    equivalents after touching JS/TS/Vue.

---

## 16. Important Files AI Should Know

| Area | File/Directory | Why It Matters |
|------|----------------|----------------|
| Onboarding | `AI-PROJECT-STARTER.md` | This orientation contract |
| Working rules | `AGENTS.md` | Binding agent rules, versions, skills |
| Deep reference | `MASTER-STARTER-ARCHITECTURE.md` | Verified inventory of every system/table/route |
| Setup | `README.md` | Install, databases, deployment baseline, seeded account |
| Bootstrap | `bootstrap/app.php` | Middleware aliases, web-group additions, API exception shaping |
| Providers | `app/Providers/AppServiceProvider.php` | Policies, Gate::before, macros, password policy, auth event listeners |
| Auth | `config/fortify.php`, `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/` | Feature flags, views, actions, limiters |
| RBAC | `app/Support/SystemRole.php`, `database/seeders/RolePermissionSeeder.php`, `app/Policies/`, `config/permission.php` | Roles, permission catalog + seeding, policy mapping, teams off |
| Users | `app/Http/Controllers/Admin/UserManagementController.php`, `app/Models/User.php` | CRUD + protections; identity model traits/casts |
| Settings | `app/Support/SettingRegistry.php`, `app/Support/SettingStore.php` | Extension point for configurable values |
| Notifications | `app/Notifications/SystemMessageNotification.php` | Message shape for all user-facing notices |
| Audit | `app/Support/ActivityLogger.php`, `app/Models/ActivityLog.php` | The single audit entry point |
| Media | `app/Support/MediaUploader.php`, `app/Models/Media.php` | Upload/thumb/delete lifecycle + attachment morph |
| Search | `app/Http/Controllers/GlobalSearchController.php` | Grouped search extension point |
| Import/Export | `app/Support/Import/`, `app/Http/Controllers/ExportCenterController.php` | Engine contract + export patterns |
| API | `routes/api.php`, `app/Http/Controllers/Api/V1/`, `app/Http/Resources/Api/V1/`, `app/Support/ApiPagination.php` | Conventions for new endpoints |
| Shared props | `app/Http/Middleware/HandleInertiaRequests.php` | Everything the frontend receives globally |
| Navigation | `resources/js/navigation/app.ts` | Permission-gated sidebar definition |
| UI kit | `resources/js/components/ui/`, `resources/js/components/admin/` | Primitives + admin building blocks |
| Types | `resources/js/types/` | Shared TS contracts to extend per domain |
| Routes | `routes/web.php`, `routes/settings.php`, `routes/api.php` | Route + permission declaration patterns |
| Tests | `tests/Pest.php`, `tests/TestCase.php`, `tests/Feature/`, `tests/Unit/` | Conventions, helpers, coverage map |
| Quality/CI | `composer.json` (scripts), `.github/workflows/` | Gates: `composer ci:check`, workflows |
| Config | `config/database.php`, `config/sanctum.php`, `.env.example` | DB selection, Sanctum, safe env template |

---

## 17. Common Mistakes to Avoid

- **Rebuilding authentication** (custom login controllers, own session logic)
  instead of using/extending Fortify.
- **Creating a second permission system** (ACL tables, role enums in code,
  hardcoded `if ($user->hasRole(...))` scattered in controllers).
- **Assigning direct user permissions** when roles suffice.
- **Adding departments/team scoping** to the starter (Spatie Teams is off on purpose).
- **Restoring removed CMS/Notes/Reports/Handbook modules** from old history.
- **Creating duplicate UI components** that already exist in `components/ui` or
  `components/admin`.
- **Adding packages** for problems solved by inherited infrastructure
  (e.g., installing laravel-activitylog when `ActivityLogger` exists).
- **Bypassing policies/FormRequests** "temporarily".
- **Hardcoding role names** as string literals instead of `SystemRole::*`.
- **Putting domain entities into generic infrastructure** (editing `app/Support`
  core classes instead of adding domain namespaces).
- **Changing core database infrastructure** (rewriting users/roles/migrations)
  instead of adding new migrations.
- **Deleting or weakening existing tests** to get green.
- **Using destructive migrations against development data** (`migrate:fresh`,
  `db:wipe`) without approval.
- **Exposing `.env` credentials** or real secrets in code/docs/commits.
- **Hand-editing Wayfinder-generated files** (`resources/js/routes/**`,
  `resources/js/actions/**`).
- **Writing PostgreSQL-only SQL** (e.g., `ilike`, `::jsonb` casts without a
  driver branch) that breaks SQLite parity.
- **Claiming untested work as tested.**

---

## 18. Definition of "Ready to Code"

Before implementing, you must be able to answer ALL of these. If any answer is
"I don't know yet," go investigate or ask — do not guess:

- What does the starter already provide for this requirement? (Sections 3–10)
- What part of the requirement is genuinely new?
- Which existing systems will be reused, and how exactly?
- What new database tables/columns are required? (migration plan)
- What new permissions are required? (seeder additions + role mapping)
- What new policies/policy methods are required?
- What new routes are required? (web and/or API, with permission middleware)
- What new UI is required? (pages, nav entries, components — reused vs new)
- What tests are required? (feature/API/unit, role-matrix coverage)
- What existing functionality could my change affect? (shared props, seeders,
  search groups, exports, CI gates)

Only then write code — incrementally, tested at each step.

---

## 19. Project Initialization Checklist

Practical checklist after cloning the master starter into a new project
(commands shown are supported by this repository):

- [ ] **Rename the application** — composer/package name, `APP_NAME`,
      `VITE_APP_NAME`, settings registry defaults
      (`SettingRegistry`: display name/tagline/organization), README title
- [ ] **Configure `.env`** from `.env.example`; generate `APP_KEY`
- [ ] **Choose the database** — PostgreSQL (recommended for real deployments:
      set `DB_CONNECTION=pgsql` + host/port/db/user) or SQLite (zero-config
      default: `touch database/database.sqlite`)
- [ ] **Install dependencies** — `composer install`, `npm install`
- [ ] **Migrate + seed** — `php artisan migrate --seed` (creates roles,
      permissions, settings, bootstrap Super Admin)
- [ ] **Change the seeded password** for `admin@example.com` before any
      shared/hosted use
- [ ] **Verify authentication** — log in, register a throwaway user, exercise
      password reset and 2FA setup
- [ ] **Verify RBAC** — confirm sidebar/route behavior differs per role
      (Super Admin sees all; Guest lands on profile)
- [ ] **Verify tests** — `php artisan test --compact`
- [ ] **Verify quality gates** — `npm run types:check && npm run build`
      (regenerate Wayfinder first if routes changed)
- [ ] **Read developer requirements** and write `PROJECT-REQUIREMENTS.md`
- [ ] **Create the project roadmap** (`PROJECT-ROADMAP.md`)
- [ ] **Create the database schema plan** (`DATABASE-SCHEMA.md`)
- [ ] **Produce the implementation plan** and get developer approval before
      large changes (Section 13, steps 10–11)

---

## 20. Relationship to MASTER-STARTER-ARCHITECTURE.md

Use all four top-level documents for different purposes:

| Document | Role |
|---|---|
| `AI-PROJECT-STARTER.md` (this file) | **Onboarding / orientation / starting contract** — read first; tells you what exists, what to reuse, what never to rebuild |
| `MASTER-STARTER-ARCHITECTURE.md` | **Deep technical reference** — exhaustive verified inventory (stack, tables, routes, policies, tests, security, evolution history); consult for details while designing/implementing |
| `README.md` | **Human-facing setup/reference** — install steps, database options, operations notes, deployment targets |
| `AGENTS.md` | **Repository-level AI/developer working rules** — binding conventions, versions, skills activation, testing enforcement |

When this guide and the architecture document appear to disagree, the **source
code wins**, then the architecture document, then this guide — and report the
discrepancy so documentation can be corrected.

---

## 21. Project-Specific Documentation That Should Be Created

Each downstream project should grow its own documentation set (owned by the
project, **not** added back into the master starter):

- `PROJECT-REQUIREMENTS.md` — business goals, features, roles, rules, constraints
- `PROJECT-ROADMAP.md` — phased implementation plan
- `DATABASE-SCHEMA.md` — domain tables beyond the inherited core (diagrams +
  migration notes)
- Optionally: `API-NOTES.md`, `DECISIONS.md` (architecture decision records),
  integration guides, deployment runbook

Until these exist, requirements live only in the developer's head — extract them
using the checklist in Section 14 before building.

---

## 22. AI Project Handoff Template

Copy/paste the block below into any AI agent when starting a new project built
from this starter. Fill the bracketed placeholders first.

```text
You are working on a project created from Laravel Master Starter — a reusable,
domain-neutral Laravel 12 / Vue 3 / Inertia foundation with complete
authentication (Fortify incl. 2FA), RBAC (Spatie Permission: Super Admin /
Manager / Staff / Guest), user & role administration, settings registry,
notification center, activity/audit logging, media library, global search,
CSV import engine, CSV/XLSX/XML/print/PDF export center, a versioned Sanctum
API (/api/v1), and a full shadcn-vue based admin UI.

BEFORE WRITING ANY CODE:

1. READ, IN THIS ORDER:
   - AI-PROJECT-STARTER.md            (orientation & rules)
   - AGENTS.md                        (binding repository working rules)
   - MASTER-STARTER-ARCHITECTURE.md   (deep technical reference)
   - PROJECT-REQUIREMENTS.md          (this project's requirements)
   - DATABASE-SCHEMA.md               (this project's domain schema)
   - PROJECT-ROADMAP.md               (this project's phased plan)
   If PROJECT-REQUIREMENTS.md, DATABASE-SCHEMA.md, or PROJECT-ROADMAP.md do not
   exist yet, STOP after reading the first three and ask me for the missing
   information instead of inventing requirements.

2. INSPECT BEFORE CODING:
   Verify the actual repository state (routes/, app/, database/, resources/,
   tests/, config/) — do not rely on summaries, including your own memory.

3. REUSE EXISTING INFRASTRUCTURE:
   Authentication, RBAC, settings, notifications, activity logging, media,
   search, import/export, the API conventions, and the UI component kit are
   already implemented and tested. Extend them through their documented
   extension points; never build parallel replacements.

4. IDENTIFY GAPS:
   Classify every requirement as (a) satisfied by existing infrastructure,
   (b) an extension of it, or (c) genuinely new domain functionality.

5. PROPOSE ARCHITECTURE:
   For anything new, present: migrations, permissions (resource.action),
   policies, routes, requests/controllers/resources, frontend pages/nav,
   and required tests — mapped to the existing patterns.

6. PROPOSE AN IMPLEMENTATION PLAN:
   Small, ordered, verifiable increments aligned with the roadmap.

7. WAIT FOR MY APPROVAL before large implementation work — especially before
   new database tables, new permissions, new packages, or any change to
   authentication, RBAC, or other master-starter core.

CONSTRAINTS:
   - Do not modify master-starter core functionality unnecessarily.
   - Do not recreate removed modules (Pages/CMS, public website, Notes,
     Reports, Handbook) or add departments/Spatie Teams unless I explicitly
     request them as project features.
   - Preserve all existing tests; add tests for every change.
   - Never expose secrets; never run destructive database commands without
     approval; never claim work is tested unless you ran the tests.

PROJECT CONTEXT:
   - Project name: [NAME]
   - Purpose: [ONE PARAGRAPH]
   - First milestone: [GOAL]
```

---

*Scope note: this guide reflects the repository at commit `66c29e6` (branch
`main`). When the master starter evolves materially, regenerate or update this
onboarding layer together with `MASTER-STARTER-ARCHITECTURE.md`.*
