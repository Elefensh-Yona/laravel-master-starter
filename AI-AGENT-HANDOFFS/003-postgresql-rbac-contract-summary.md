# AI Agent Handoff 003: PostgreSQL, RBAC Contract, and Manual Test Preparation

## Interaction ID

`003`

## Task Assigned

Verify the owner-supplied local PostgreSQL configuration, authenticate Laravel against the approved `development` database, apply only the existing Master Starter baseline, verify starter RBAC, and prepare a proposed AILH RBAC/test contract and future manual acceptance plan. Stop before AILH implementation.

## What Was Inspected

- Current `.env`, including database settings and password presence without exposing the password.
- Git ignore behavior and current worktree.
- Docker Desktop and the running `dev-postgres` container.
- Laravel PostgreSQL metadata and migration commands.
- PostgreSQL databases, schemas, tables, migration state, version, and row counts.
- `SystemRole`, `RolePermissionSeeder`, starter policies, middleware aliases, route middleware, `AppServiceProvider`, and Spatie Permission configuration.
- AILH requirements, schema, roadmap, and Phase 0/handoff documents for actors, scope, and unresolved decisions.

## What Was Changed

- No application source files were changed.
- No AILH domain migrations, models, controllers, routes, roles, permissions, or policies were created.
- No starter migrations or starter RBAC code was modified.
- No PostgreSQL data was deleted, reset, truncated, or altered destructively.
- Applied only the existing Master Starter migrations and existing seeders to the confirmed empty `development` database, as explicitly authorized by this interaction.
- Created this required handoff file.

The local ignored `.env` was already correct and was not modified. Its password is not included here, in project documentation, or in any command output.

## `.env` Verification Result

The effective local configuration was verified through Laravel and the file was inspected with the password redacted:

```text
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=development
DB_USERNAME=postgres
DB_PASSWORD=<REDACTED>
```

- PostgreSQL is selected by the effective Laravel environment.
- Host, port, database, and username match the approved project database.
- Password is present locally and was never printed.
- `.env` is ignored by Git.
- `.env.example` was not modified.

## PostgreSQL Connection Result

Laravel authenticated successfully and read PostgreSQL metadata.

- Database: `development`.
- PostgreSQL version: `17.11 (Debian 17.11-1.pgdg13+2)`.
- Host: `127.0.0.1`.
- Port: `5432`.
- Username: `postgres`.
- Laravel connection: `pgsql`.
- `php artisan db:show --database=pgsql --counts --views --no-interaction`: passed before migrations and after migrations.
- Docker container: `dev-postgres`, image `postgres:17`, running.
- Docker published port: `0.0.0.0:5432 -> 5432`.
- pgAdmin4 same-server connectivity had been verified in Interaction 002; this interaction confirmed the same container/endpoint remains running.

## Empty Database Verification Before Migration

Before applying the starter baseline:

- `development` existed and was reachable.
- Non-system schemas present: `information_schema`, `public` (full PostgreSQL inspection also showed `pg_catalog` and `pg_toast`).
- `public` contained no tables.
- `public.migrations` did not exist.
- Laravel `migrate:status --database=pgsql` reported `Migration table not found`.
- `db:show` reported `Tables: 0`.
- No Master Starter tables or AILH domain tables existed.
- No destructive operation was performed.

## Master Starter Baseline Application

Command executed:

```text
php artisan migrate --database=pgsql --seed --graceful --ansi --no-interaction
```

Result: **SUCCESS**.

All 14 existing Master Starter migrations ran in batch 1:

1. `0001_01_01_000000_create_users_table`
2. `0001_01_01_000001_create_cache_table`
3. `0001_01_01_000002_create_jobs_table`
4. `2025_08_14_170933_add_two_factor_columns_to_users_table`
5. `2026_03_16_082039_create_permission_tables`
6. `2026_03_16_195118_add_description_to_roles_table`
7. `2026_03_16_202511_create_personal_access_tokens_table`
8. `2026_03_16_203229_create_activity_logs_table`
9. `2026_03_16_203825_create_notifications_table`
10. `2026_03_18_081015_create_settings_table`
11. `2026_03_18_084542_create_media_table`
12. `2026_03_18_110256_create_import_runs_table`
13. `2026_08_23_210723_add_locale_to_users_table`
14. `2026_08_23_212200_add_thumbnail_to_media_table`

Existing seeders completed successfully:

- `Database\\Seeders\\RolePermissionSeeder`
- `Database\\Seeders\\SettingsSeeder`

## Database State After Starter Baseline

### Schemas

| Schema | Purpose | Ownership/classification | Treatment | AILH use |
|---|---|---|---|---|
| `information_schema` | SQL metadata views | PostgreSQL system schema | Leave unchanged | Not used directly |
| `pg_catalog` | PostgreSQL system catalogs | PostgreSQL system schema | Leave unchanged | Not used directly |
| `pg_toast` | PostgreSQL internal large-value storage | PostgreSQL internal schema | Leave unchanged | Not used directly |
| `public` | Default application schema | Application/default PostgreSQL schema | Leave unchanged | **OWNER DECISION REQUIRED:** confirm AILH continues using `public` |

No additional schema was created.

### MASTER STARTER TABLES

All 20 application tables currently present in `public` are Master Starter tables:

- `activity_logs`
- `cache`
- `cache_locks`
- `failed_jobs`
- `import_runs`
- `job_batches`
- `jobs`
- `media`
- `migrations`
- `model_has_permissions`
- `model_has_roles`
- `notifications`
- `password_reset_tokens`
- `permissions`
- `personal_access_tokens`
- `role_has_permissions`
- `roles`
- `sessions`
- `settings`
- `users`

Post-migration `db:show` reported 20 tables, 14 migration records, and the following relevant counts:

- `users`: 1
- `roles`: 4
- `permissions`: 18
- `role_has_permissions`: 30
- `model_has_roles`: 1
- `activity_logs`: 0
- `settings`: 7
- `notifications`: 0

### AILH DOMAIN TABLES

None exist. No AILH table names were created or found. The PostgreSQL database now contains only the Master Starter baseline and its seeded starter records.

### Migration status

`php artisan migrate:status --database=pgsql --no-interaction` passed after migration. All 14 listed migrations report `Ran` in batch 1.

## Starter RBAC Baseline

### VERIFIED roles

The four existing `web`-guard system roles are:

- `Super Admin`
- `Manager`
- `Staff`
- `Guest`

They are defined in `app/Support/SystemRole.php` and created/updated idempotently by `database/seeders/RolePermissionSeeder.php`.

### VERIFIED permissions

The 18 existing `web`-guard permissions are:

- `activity-logs.view`
- `dashboard.view`
- `exports.view`
- `media.create`
- `media.delete`
- `media.view`
- `notifications.view`
- `roles.create`
- `roles.delete`
- `roles.update`
- `roles.view`
- `search.view`
- `settings.update`
- `settings.view`
- `users.create`
- `users.delete`
- `users.update`
- `users.view`

### VERIFIED role-permission assignments

| Role | Permission count | Assigned permissions |
|---|---:|---|
| `Super Admin` | 18 | All 18 permissions listed above |
| `Manager` | 9 | `activity-logs.view`, `dashboard.view`, `exports.view`, `media.create`, `media.view`, `notifications.view`, `search.view`, `settings.view`, `users.view` |
| `Staff` | 3 | `dashboard.view`, `notifications.view`, `search.view` |
| `Guest` | 0 | None |

Actual role user counts in PostgreSQL:

- `Super Admin`: 1 user
- `Manager`: 0 users
- `Staff`: 0 users
- `Guest`: 0 users

No user identities were recorded.

### VERIFIED enforcement mechanics

- Spatie Permission is configured for the `web` guard.
- `config/permission.php` has teams disabled: `'teams' => false`.
- `bootstrap/app.php` aliases `role`, `permission`, and `role_or_permission` middleware.
- Existing routes use `auth`, `verified`, and `permission:<name>` middleware.
- Existing policies generally delegate to `$user->can('<permission>')`.
- `AppServiceProvider` registers starter policies for `User`, `Media`, `Setting`, and `Role`.
- `AppServiceProvider` defines `Gate::before` to grant all abilities to `Super Admin`.
- API authentication/authorization failures are normalized to JSON in `bootstrap/app.php`.
- Current authorization is global role/permission authorization. There is no program, stage, assignment, or record scope implemented.

## AILH RBAC Decision Matrix

The following matrix is **PROPOSED** for controller review. It is not an approved implementation mapping. No AILH roles or permissions were created.

| Actor | Identity | Expected access | Expected actions | Restrictions | Proposed scope | Trust-critical behavior |
|---|---|---|---|---|---|---|
| Super Admin / System Administrator | Platform-level system administration and recovery actor | Global starter administration; AILH access only within approved governance | Manage approved system configuration and users; perform explicitly approved operational recovery | Must not bypass audit, immutable history, conflict governance, or human decision authority without explicit policy | Global, always audited | Every override audited; no silent edits to frozen rubric, finalized evaluation, submitted snapshot, or decision |
| Program Staff / Program Administrator | Internal operator responsible for one or more programs | Assigned program, stages, applications, screening, assignments, conflicts, and approved operational data | Configure/publish approved program; screen; assign judges; manage approved conflict workflow; convene deliberation | No unrelated program access; no silent immutable-record edits; no judge-private score disclosure outside policy | Proposed program-scoped, with stage/record checks | Assigned-program positive access; unrelated-program denial; consequential mutations audited |
| Judge / Evaluator | Human evaluator reviewing assigned applications | Assigned application/evidence, approved rubric, own evaluation, and approved deliberation material | Declare conflict; inspect assigned evidence; draft/finalize own evaluation; participate in approved deliberation | No unassigned application; no private access to another judge's evaluation; no rubric mutation; blocked when conflicted | Proposed assignment-scoped; possibly stage-scoped for events | Assignment isolation, score confidentiality, conflict blocking, finalization protection |
| Applicant / Innovator | Individual or team participant | Public permitted programs; own profile/team; own draft/submissions/status/notifications; approved decision communication | Discover programs; create/edit draft; submit approved revision; receive notifications; perform approved applicant actions | No other applicant records; no internal notes, private scores, conflict details, or deliberation material | Proposed record ownership scoped to individual/team | Cross-applicant denial; deadline/revision integrity; notification visibility |
| Decision Maker | Human authority for final outcomes, if distinct | Approved program applications and deliberation materials | Record and finalize human selection/rejection/waitlist decisions with rationale | Cannot delegate consequential authority to AI; cannot mutate evidence/evaluations silently; cannot decide outside scope | **OWNER DECISION REQUIRED:** program-scoped capability or separate role | Decision is separate from score arithmetic; actor, rationale, timestamp, and audit are mandatory |

### Matrix labels

- **VERIFIED:** current starter has global roles, global permissions, web guard, policy delegation, middleware aliases, teams disabled, and Super Admin global bypass.
- **PROPOSED:** actor descriptions, scope patterns, permission families, and future test behavior above.
- **OWNER DECISION REQUIRED:** any choice that changes product behavior, scope, governance, privacy, or data integrity.

## RBAC Owner Decisions Required

1. **OWNER DECISION REQUIRED:** Is Decision Maker a separate role, a capability on Program Staff, or a program membership assignment?
2. **OWNER DECISION REQUIRED:** May one user hold multiple AILH actor roles simultaneously?
3. **OWNER DECISION REQUIRED:** May one user participate in multiple programs, and can the actor vary by program?
4. **OWNER DECISION REQUIRED:** What creates program scope: explicit program membership, role assignment, staff assignment, or another approved relation?
5. **OWNER DECISION REQUIRED:** What creates stage scope, if stages have distinct operators or visibility?
6. **OWNER DECISION REQUIRED:** What creates judge assignment scope, and what is the precedence for program-, stage-, and application-level assignments?
7. **OWNER DECISION REQUIRED:** What creates applicant ownership for individual and team applications, including team-lead/delegate authority?
8. **OWNER DECISION REQUIRED:** Should inherited `Manager` and `Staff` remain infrastructure roles, be supplemented with AILH roles, or be reused under an approved mapping?
9. **OWNER DECISION REQUIRED:** Should authorization combine roles, permissions, program memberships, judge assignments, and record-level policies? Recommendation: yes, with policies enforcing record scope, but approval is required.
10. **OWNER DECISION REQUIRED:** Should Super Admin retain the current global bypass for AILH records?
11. **OWNER DECISION REQUIRED:** Which immutable/governance operations remain protected even for Super Admin: frozen rubric edits, finalized score edits, reopening, conflict clearance, decision reversal, or others?
12. **OWNER DECISION REQUIRED:** What exact MVP permission names should be used? Proposed names include `programs.view`, `programs.create`, `programs.update`, `programs.publish`, `applications.view`, `applications.create`, `applications.update`, `applications.submit`, `applications.screen`, `applications.assign`, `conflicts.declare`, `conflicts.resolve`, `evaluations.view-own`, `evaluations.create`, `evaluations.finalize`, `evaluations.reopen`, `deliberations.view`, `deliberations.manage`, `decisions.view`, `decisions.create`, and `decisions.finalize`.
13. **OWNER DECISION REQUIRED:** Which permissions require record-level policy checks rather than a simple permission middleware check? Recommendation: all application, evidence, assignment, conflict, evaluation, deliberation, and decision operations require policy checks in addition to permission checks.

### RBAC recommendation

**PROPOSED:** keep inherited starter permissions/roles intact as infrastructure and add AILH authorization through domain permissions plus policies and scope relationships after owner approval. Do not enable Spatie teams solely to represent program scope without a documented fit; the application is currently configured with teams disabled and the requirements do not establish teams as the scope mechanism.

## Proposed Local Manual Test Accounts

These are **PROPOSED test identities only**. They were not created, seeded, assigned roles, or given credentials.

| Identity | Actor/role | Purpose | Expected permissions | Expected visibility | Forbidden actions |
|---|---|---|---|---|---|
| `admin@example.test` | Super Admin / System Administrator | Verify global starter access and governance boundaries | Existing 18 starter permissions; approved AILH administration only | Global approved records | Silent immutable edits; un-audited consequential decisions |
| `staff@example.test` | Program Staff / Program Administrator | Operate assigned program | Approved program, screening, assignment, conflict, deliberation permissions | Assigned program/stage/application records | Unrelated program; private judge scores; silent finalized-record changes |
| `decision-maker@example.test` | Decision Maker, if distinct | Record human final outcomes | Approved decision view/create/finalize within assigned program | Approved decision inputs and deliberation material | AI final decision; unauthorized program decision; score mutation |
| `judge1@example.test` | Judge / Evaluator | Evaluate assigned application A | Approved assignment-scoped view, conflict, evaluation permissions | Assigned application A, own evaluation, approved deliberation material | Application B/unassigned records; judge2 private evaluation; rubric changes |
| `judge2@example.test` | Judge / Evaluator | Verify independent judge isolation | Same approved judge permissions | Separate assigned application or controlled shared fixture | Judge1 private evaluation; unassigned applications |
| `applicant1@example.test` | Applicant / Innovator | Create/submit application A | Approved own-record application permissions | Public programs and own records | Applicant2 record; staff/judge internal records |
| `applicant2@example.test` | Applicant / Innovator | Verify cross-applicant isolation | Approved own-record application permissions | Public programs and own records | Applicant1 record; staff/judge internal records |

Credentials must be generated by approved local test fixtures or entered locally by the owner. No passwords, tokens, or real personal data belong in documentation or commits.

## Proposed Future Manual Acceptance Test

This is **PROPOSED** and must be executed only after approved AILH migrations, RBAC, policies, workflow, and UI/API surfaces exist.

### POSITIVE TESTS: what the actor CAN do

1. Each approved actor logs in with its local test account.
2. Super Admin reaches approved global administration and starter dashboard surfaces.
3. Program Staff reaches the dashboard and assigned program workspace.
4. Decision Maker reaches approved decision workspace, if distinct.
5. Judge reaches only the approved evaluation workspace.
6. Applicant reaches permitted public program discovery.
7. Applicant reaches the authenticated application flow when policy requires authentication.
8. Staff creates and configures a program with approved stages/rules.
9. Staff publishes the configured program through the approved transition.
10. Applicant creates an individual/team application according to the approved participation model.
11. Applicant saves a draft and sees the draft in the correct owner scope.
12. Applicant submits before the deadline.
13. Staff screens the application and records an auditable result.
14. Staff assigns Judge 1 under the approved assignment scope.
15. Judge 1 declares or checks conflict status.
16. Authorized staff resolves a conflict according to approved authority.
17. Staff activates/freezes the approved rubric version.
18. Judge 1 drafts an independent evaluation against the approved rubric.
19. Judge 1 finalizes the evaluation.
20. Authorized staff enters deliberation after approved entry conditions.
21. Decision Maker or authorized staff records a human decision with rationale.
22. Applicant receives the approved notification after the decision persists.
23. Applicant sees only the approved notification and decision communication.
24. Authorized users can inspect the permitted audit history.

### NEGATIVE / SECURITY TESTS: what the actor MUST NOT do

25. Applicant 1 cannot view Applicant 2's application, draft, submission, evidence, or status details.
26. Applicant 2 cannot view Applicant 1's records.
27. Applicant cannot submit after deadline locking.
28. Applicant cannot alter an immutable submitted snapshot except through the approved revision path.
29. Staff cannot access or mutate an unrelated program.
30. Judge 1 cannot access an unassigned application.
31. Judge 2 cannot access Judge 1's assigned application unless separately and explicitly assigned.
32. Judge 1 cannot see Judge 2's private evaluation before the approved disclosure point.
33. A blocking conflict prevents the conflicted judge from evaluating and participating where policy requires.
34. A judge cannot change the active/frozen rubric or another actor's criteria.
35. A finalized evaluation cannot be silently modified.
36. Reopening a finalized evaluation requires the approved authority, reason, audit event, and history.
37. AI cannot create, finalize, reverse, or override a consequential human decision.
38. An unauthorized web request returns the approved denial behavior.
39. An unauthorized API request returns the approved JSON denial behavior.
40. A cross-program request is denied when the actor lacks program scope.
41. A user cannot access another judge's private evaluation through direct URL/API manipulation.
42. A user cannot bypass policy checks by calling a route that has the broad permission but lacks record scope.
43. A decision cannot be created without the approved human authority and rationale.
44. Consequential mutations always create the required audit event and actor/timestamp history.
45. Notifications are not emitted as a substitute for a failed or rolled-back decision transaction.

### Manual evidence to capture

- Actor account used, without recording password.
- Program/application fixture identifiers.
- Visible page/result and HTTP status for each case.
- Expected versus actual access outcome.
- Audit event identifier or visible audit row.
- Notification identifier/visible notification.
- For failures: exact step, evidence, suspected cause, severity, and whether task- or project-level.

## Tests / Checks Performed

### Successful

- `.env` effective PostgreSQL configuration verified without exposing password.
- `.env` confirmed ignored by Git.
- Docker Desktop/PostgreSQL container confirmed running.
- Laravel `db:show --database=pgsql --counts --views --no-interaction` passed before and after migration.
- Direct PostgreSQL identity/version and table queries passed.
- Existing Master Starter migrations applied successfully: 14/14.
- Existing seeders completed successfully.
- `php artisan migrate:status --database=pgsql --no-interaction` passed with all 14 migrations marked `Ran`.
- PostgreSQL database reports 20 starter tables and no AILH domain tables.
- Starter roles/permissions and actual role-permission counts verified through Laravel queries.
- Existing Pest suite: `127 passed`, `2 skipped`, `768 assertions`.
- `npm run types:check`: passed.
- `npm run format:check`: passed.
- `npm run build`: passed.
- No tracked implementation diff was observed.

### Failed results

No persistent test failure occurred in this interaction.

The previous Interaction 002 Laravel PostgreSQL authentication failure is resolved by the owner-supplied local `.env` password and is not a current failure.

## Known Blockers

### PostgreSQL application credential is now verified

No PostgreSQL connectivity blocker remains for this environment. Laravel authenticated and queried the approved `development` database successfully.

### Domain implementation remains intentionally blocked by owner decisions

No AILH migrations or domain code may begin until the controller approves the unresolved actor scope, public access, participation, workflow, conflict, rubric/evaluation, privacy, and decision-authority rules listed below. This is a controlled sequencing gate, not an infrastructure failure.

## Risks

- `development` now contains the Master Starter baseline and one seeded Super Admin account. It is not empty anymore; future migration commands must account for the 14 applied migrations.
- The PostgreSQL container has no reported Docker healthcheck; application-level Laravel checks are the meaningful verification evidence.
- The current Super Admin `Gate::before` is global and may be too broad for AILH immutable/governance operations.
- Current roles and permissions are global and teams are disabled; they cannot prove program-scoped authorization without an approved domain scope extension.
- The proposed role names and permission names are not approved and must not be seeded from this handoff alone.
- PostgreSQL-specific behavior beyond the starter migration/query baseline still needs coverage when AILH schema and workflows are implemented.
- The project identifier remains `AI Innovation Lifecycle Hub (AILH)`; no rename was performed.

## OWNER DECISION REQUIRED

### Database/schema

1. Confirm `development` remains the approved project development database after starter baseline application.
2. Confirm AILH uses the existing `public` schema; no alternative schema is currently documented or needed.
3. Confirm whether PostgreSQL verification should be repeated against a separately provisioned database before any domain migration work.

### RBAC and governance

4. Decide whether Decision Maker is distinct from Program Staff.
5. Decide whether users may hold multiple actor roles and participate in multiple programs.
6. Decide the source and precedence of program, stage, assignment, and applicant record scope.
7. Decide whether inherited `Manager`/`Staff` roles remain infrastructure roles or receive an approved AILH mapping.
8. Decide whether authorization combines roles, permissions, memberships, assignments, and record-level policies.
9. Decide whether Super Admin keeps the global bypass for AILH records.
10. Define immutable/governance operations protected even for Super Admin.
11. Approve exact MVP domain permission names and which actions require record policies.
12. Approve public versus authenticated program/application boundaries and individual/team/organization participation.
13. Approve judge score visibility, conflict categories/blocking, rubric freezing, evaluation finalization/reopening, and human decision authority.

No password, API key, token, or other secret is required to answer these decisions and none is included in this handoff.

## Files Changed

Created:

- `AI-AGENT-HANDOFFS/003-postgresql-rbac-contract-summary.md`

Not changed:

- Application source.
- AILH migrations/models/controllers/routes/policies.
- Starter migrations/RBAC.
- `.env` and `.env.example`.
- Package manifests and lockfiles.
- Existing handoffs and planning documents.

Database changes:

- Existing Master Starter migrations and existing seeders were applied to PostgreSQL `development` as authorized.
- No AILH tables or AILH data were created.
- No destructive database operation occurred.

## Git State

- Branch: `main`.
- Upstream relation: `main...upstream/main`.
- Latest observed commit: `dc03e5e (HEAD -> main, upstream/main, upstream/HEAD) Add AI project starter onboarding guide`.
- Existing untracked planning documents remain present and untouched.
- Existing handoffs `001` and `002` remain present and untouched.
- New untracked handoff: `AI-AGENT-HANDOFFS/003-postgresql-rbac-contract-summary.md`.
- `.env`, `database/database.sqlite`, `vendor/`, `node_modules/`, and `public/build/` remain ignored.
- No tracked application, migration, configuration, package, or lockfile diff was observed.

## Recommended Next Action

Stop this interaction for Product & Technical Controller review.

The infrastructure baseline is ready for the next controlled task. Before creating AILH roles, permissions, policies, or domain migrations, the controller should approve the RBAC decision matrix and the trust-critical workflow/data decisions. The next implementation interaction should then produce the smallest approved contract artifact or schema change, not the full AILH migration set.

Do not rename AILH. Do not create AILH migrations, models, controllers, routes, roles, permissions, or vertical-slice code from this handoff alone.

## Verified Facts vs Assumptions

**Verified:** local Laravel resolves PostgreSQL; credentials authenticate; `development` is PostgreSQL 17.11 at `127.0.0.1:5432`; Docker container `dev-postgres` is running; pgAdmin4 had same-server sessions; starter migrations 1-14 are applied; public contains exactly 20 starter tables; no AILH domain tables exist; four starter roles and 18 permissions are seeded with the assignments listed; current policies/middleware/Gate behavior is global; baseline tests and frontend checks pass.

**Proposed only:** AILH actor mapping, domain permission names, scope relationships, future test identities, and manual acceptance cases.

**Not assumed:** any password value; a new schema; a final Decision Maker role; multiple-role/multiple-program behavior; Super Admin treatment of immutable AILH records; public application policy; organization support; conflict or score disclosure rules; rubric/evaluation reopen rules; or AI authority.
