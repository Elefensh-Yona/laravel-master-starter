# AI Agent Handoff 002: PostgreSQL, RBAC, and Test Contract Summary

## Interaction ID

`002`

## Task Assigned

Verify the running PostgreSQL environment, establish the PostgreSQL database baseline, inspect the inherited Master Starter RBAC, prepare a proposed AILH actor/RBAC test contract, define future local test-account requirements and an acceptance sequence, and stop before AILH domain implementation.

## What Was Inspected

- Current user-modified `.env` and `database/database.sqlite`, with secret values redacted.
- Docker Desktop CLI, context, `dev-postgres` container metadata, published ports, network, and logs.
- PostgreSQL server identity, version, databases, schemas, roles, active clients, and public relations.
- Laravel database configuration and direct Laravel PostgreSQL connection behavior.
- Laravel migration status on PostgreSQL, without running migrations.
- Existing `SystemRole`, `RolePermissionSeeder`, policies, middleware aliases, route permission middleware, `AppServiceProvider`, and Spatie Permission configuration.
- AILH requirements, roadmap, schema, and Phase 0 planning documents for actor scope and unresolved decisions.

## What Was Changed

- No application source files, migrations, models, controllers, policies, roles, permissions, routes, configuration, package manifests, or lockfiles were changed.
- No PostgreSQL data, schema, password, role, or configuration was modified.
- Created this required handoff file only.

The existing `.env` remains configured for SQLite. PostgreSQL values were passed only as process-local command overrides for connection testing and were not written to project files.

## PostgreSQL Verification Results

### Docker / exposure

- Docker Desktop server is reachable from WSL2.
- Docker CLI client/server: `29.7.2`.
- Docker Desktop: `4.86.0`.
- Active context: `default`, with Docker Desktop server available.
- Running PostgreSQL container: `dev-postgres`.
- Image: `postgres:17`.
- Container status: running.
- Container healthcheck: none configured/reported.
- Container internal address: `172.18.0.2` on Docker network `postgres_default`.
- Published exposure: `0.0.0.0:5432 -> container:5432`, including IPv6 host binding.
- Verified Laravel/WSL target host and port: `127.0.0.1:5432`.
- PostgreSQL authentication is configured for host connections using `scram-sha-256`, according to the server log.

### Exact database identity

- Database name: `development`.
- PostgreSQL user observed for administrative/container queries: `postgres`.
- PostgreSQL version: `PostgreSQL 17.11 (Debian 17.11-1.pgdg13+2)`.
- Server architecture: x86_64.
- Connection method used for server inspection: `docker exec dev-postgres psql -U postgres -d development` over the container's local Unix socket.
- Connection method intended for Laravel: TCP to `127.0.0.1:5432` using the PostgreSQL driver.
- Password: intentionally omitted from this handoff.

### pgAdmin4 relationship

`pg_stat_activity` showed these active sessions on the same PostgreSQL server:

- `development | postgres | pgAdmin 4 - DB:development | idle`
- `postgres | postgres | pgAdmin 4 - DB:postgres | idle`

This verifies that pgAdmin4 has sessions against the same `dev-postgres` instance, including the `development` database. It does not independently verify which saved credential pgAdmin4 uses.

### Laravel connection result

- Laravel reached `127.0.0.1:5432` and PostgreSQL returned an authentication response.
- Laravel command used a process-local PostgreSQL override for database `development` and user `postgres`.
- Result: **FAILED** with `SQLSTATE[08006] [7] ... password authentication failed for user "postgres"`.
- The same authentication failure occurred for both `php artisan db:show --database=pgsql` and `php artisan migrate:status --database=pgsql`.
- No password was printed or recorded.

### Connection conclusion

The PostgreSQL server and database endpoint are verified, but the approved Laravel credential is not verified. The container's `POSTGRES_PASSWORD` metadata is not sufficient evidence of the current password because the PostgreSQL data directory was pre-existing and the container logs state initialization was skipped. No password reset or role modification was attempted.

## Database Baseline

### Database

- Exact database: `development`.
- PostgreSQL version: `17.11`.
- Verified host/port from WSL: `127.0.0.1:5432`.
- Server connection status: reachable and accepting connections.
- Laravel application connection status: **not authenticated**; verification incomplete pending the approved credential.

### Schemas

| Schema | Owner | Classification | Treatment | AILH expectation |
|---|---|---|---|---|
| `information_schema` | `postgres` | PostgreSQL system metadata | Leave unchanged | Not used for AILH tables |
| `pg_catalog` | `postgres` | PostgreSQL system catalog | Leave unchanged | Not used for AILH tables |
| `pg_toast` | `postgres` | PostgreSQL internal large-value storage | Leave unchanged | Not used directly by AILH |
| `public` | `pg_database_owner` | Default application schema | Leave unchanged; no additional schema is justified by current documentation | **OWNER DECISION REQUIRED:** confirm AILH uses `public` as the default application schema |

No additional schema was created.

### Tables

The `development` database currently has no relations in `public` and no application tables.

**MASTER STARTER TABLES:** None currently present in PostgreSQL `development`. The starter tables exist in the repository migrations and were previously applied to the local ignored SQLite baseline, not this PostgreSQL database.

**AILH DOMAIN TABLES:** None.

Relevant database checks:

- `information_schema.tables` returned no non-system tables.
- `pg_tables` for `public` returned no rows.
- `to_regclass('public.migrations')` returned null/no migration table.
- The database is empty of both Master Starter and AILH domain tables.

### Migration state

- PostgreSQL migration status could not be read through Laravel because authentication failed.
- Direct database inspection proves the `public.migrations` table does not exist, so no Laravel migrations have been applied to `development`.
- No migration command was run against PostgreSQL.
- No destructive command, reset, drop, truncate, or schema change was performed.

### PostgreSQL-specific verification

Verified:

- PostgreSQL 17 server is running.
- TCP port 5432 is published and reachable.
- SCRAM host authentication is active.
- PHP has `pdo_pgsql` and `pgsql` drivers.
- Direct PostgreSQL queries work from inside the container.
- Empty `development` database and `public` schema are confirmed.

Not yet verified:

- Laravel's authenticated PostgreSQL connection.
- Laravel migrations on PostgreSQL.
- PostgreSQL behavior of the application schema, constraints, JSON fields, indexes, transactions, or queries.

## RBAC Baseline

### Existing roles

The starter seeds exactly four global `web`-guard roles:

- `Super Admin`
- `Manager`
- `Staff`
- `Guest`

They are defined in `app/Support/SystemRole.php` and created/updated idempotently by `database/seeders/RolePermissionSeeder.php`.

### Existing permissions

The starter seeds these 18 global `web`-guard permissions:

- `dashboard.view`
- `search.view`
- `exports.view`
- `settings.view`
- `settings.update`
- `media.view`
- `media.create`
- `media.delete`
- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `roles.view`
- `roles.create`
- `roles.update`
- `roles.delete`
- `notifications.view`
- `activity-logs.view`

### Seeding and assignment

`RolePermissionSeeder`:

1. Clears Spatie's cached permissions.
2. Creates permissions with `Permission::findOrCreate(..., 'web')`.
3. Creates or updates each default role by name and `web` guard.
4. Calls `syncPermissions()` with the role's permission list.
5. Clears the permission cache again.

The current role-permission assignment is:

| Role | Assigned starter permissions |
|---|---|
| Super Admin | All 18 permissions |
| Manager | `dashboard.view`, `search.view`, `exports.view`, `settings.view`, `media.view`, `media.create`, `users.view`, `notifications.view`, `activity-logs.view` |
| Staff | `dashboard.view`, `search.view`, `notifications.view` |
| Guest | None |

### Middleware and policies

- Spatie middleware aliases are registered in `bootstrap/app.php`: `role`, `permission`, and `role_or_permission`.
- Existing web/API routes use `auth`, `verified`, and `permission:<name>` middleware as appropriate.
- Existing policy methods generally delegate to `$user->can('<permission>')`.
- `AppServiceProvider` explicitly registers starter policies for `User`, `Media`, `Setting`, and `Role`.
- `Gate::before` grants every ability to a user with the `Super Admin` role.
- `config/permission.php` has teams disabled (`'teams' => false`).
- Existing authorization is global role/permission authorization, with no AILH program, stage, assignment, or record scope implemented.
- API unauthorized/unauthenticated errors are normalized to JSON in `bootstrap/app.php`.

### Can current RBAC support AILH program-scoped permissions without modification?

**No, not by itself.** The inherited package and policy conventions are reusable, but the current implementation has global roles, global permissions, teams disabled, and no program-membership/assignment scope model. AILH program-scoped authorization will require an approved extension using policies and domain relationships, and possibly additional scope data. The exact design must not be silently chosen.

## Proposed AILH Actor / RBAC Test Contract

The following is **PROPOSED** for test planning only. It is not an approved role mapping and no roles or permissions were created.

### Proposed actor matrix

| Actor family | Identity | Expected access | Expected actions | Expected restrictions | Scope | Trust-critical tests |
|---|---|---|---|---|---|---|
| Super Admin / system administrator | Platform recovery and system administration actor | Global starter administration; AILH access only according to approved policy | Manage approved system configuration, users, permissions, and operational recovery | Must not bypass audit or human-decision governance; should not silently alter immutable workflow history | Global, with explicit audit trail | Global access is audited; cannot silently edit finalized evaluations, frozen rubrics, submissions, or decisions |
| Program Staff / Program Administrator | Internal operator responsible for assigned programs | Assigned program, stages, applications, screening, assignments, conflicts, and operational records | Configure/publish approved program; screen; assign judges; manage conflict workflow; convene deliberation; perform allowed operational actions | Cannot access unrelated programs or alter immutable records outside approved reopen process | Proposed program-scoped, with record/stage limits | Staff can manage assigned program; unrelated program denied; every consequential mutation audited |
| Judge / Evaluator | Person reviewing assigned applications | Assigned application/evidence and approved rubric; own evaluations; approved deliberation material | Declare conflict; review assigned evidence; draft/finalize own evaluation; participate in approved deliberation | Cannot view unassigned applications; cannot see other judges' private evaluations before approved disclosure; cannot change rubric or finalized scores; conflicted judge is blocked as approved | Proposed assignment-scoped for applications; possibly stage-scoped for events | Assignment isolation; private-score isolation; conflict blocking; finalization protection |
| Applicant / Innovator | Individual or team participant | Own profile, own team, own drafts/submissions, status, permitted public programs, notifications, and approved decision communication | Discover permitted programs; create/edit draft; submit approved revision; receive notifications; perform allowed applicant actions | Cannot see other applicants' records, judges' private scores, staff notes, conflict details, or internal deliberation | Proposed record-scoped to own applicant/team records | Cross-applicant denial; deadline lock; revision rules; notification visibility |
| Decision Maker | Human authority, if distinct from Program Staff | Applications and deliberation material for authorized program(s) | Record and finalize human selection/rejection/waitlist decisions with rationale | Cannot delegate final authority to AI; cannot change immutable evidence/evaluations silently; scope must be explicit | OWNER DECISION REQUIRED: program-scoped or staff role capability | Human decision required; decision is separate from score arithmetic; decision and rationale audited |

### OWNER DECISION REQUIRED: role mapping

The requirements name staff, judges, applicants, mentors, partners, administrators, and super administrators, but do not approve whether `Program Staff`, `Program Administrator`, and `Decision Maker` are separate roles, permissions, capabilities, or program memberships. The controller must approve:

- Whether Decision Maker is distinct from Program Staff.
- Whether inherited `Manager`/`Staff` roles are reused, renamed, or supplemented.
- Whether a user can hold multiple actor roles across different programs.
- Whether program membership, stage membership, judge assignment, or record ownership is the source of scope.
- Whether Super Admin's existing global bypass applies to domain records, and which immutable/governance operations remain explicitly blocked even for Super Admin.

### Proposed minimum domain permission families

These are **PROPOSED** names for contract review, not seeded permissions:

- `programs.view`, `programs.create`, `programs.update`, `programs.publish`
- `applications.view`, `applications.create`, `applications.update`, `applications.submit`
- `applications.screen`, `applications.assign`
- `conflicts.declare`, `conflicts.resolve`
- `evaluations.view-own`, `evaluations.create`, `evaluations.finalize`, `evaluations.reopen`
- `deliberations.view`, `deliberations.manage`
- `decisions.view`, `decisions.create`, `decisions.finalize`

**OWNER DECISION REQUIRED:** approve permission names, role mapping, route/policy enforcement, and scope semantics before RBAC implementation. The current starter's global permission names are not enough for this matrix.

## Proposed Local Test Accounts

These are safe placeholder identities for future factories/seeders or local fixtures. They are not created and no credentials are approved.

| Account | Proposed role/actor | Purpose | Expected permissions | Expected visibility | Forbidden actions |
|---|---|---|---|---|---|
| `admin@example.test` | Super Admin / system administrator | Verify global administrative baseline and governance boundaries | Existing all-starter permissions; proposed domain administration only after approval | Global records according to approved Super Admin policy | Silent immutable-record edits; un-audited consequential decisions |
| `staff@example.test` | Program Staff / Program Administrator | Configure and operate an assigned program | Proposed program/application/screening/assignment/conflict/deliberation permissions within assigned scope | Assigned program records and approved evidence | Unrelated program access; judge-private scores; silent finalized-record changes |
| `decision-maker@example.test` | Decision Maker, if distinct | Record human selection decisions | Proposed decision view/create/finalize within authorized program scope | Approved deliberation material and decision inputs | AI final selection; unauthorized program decisions; score mutation |
| `judge1@example.test` | Judge / Evaluator | Evaluate assigned application A | Proposed assignment-scoped application/evaluation/conflict permissions | Assigned application A, own evaluation, approved deliberation view | Application B; judge2's private score; rubric changes; conflicted evaluation |
| `judge2@example.test` | Judge / Evaluator | Verify judge isolation and independent evaluation | Same proposed judge permissions | Assigned application B or a separate assignment fixture | Unassigned applications; judge1's private evaluation |
| `applicant1@example.test` | Applicant / Innovator | Create and submit application A | Proposed own-record application permissions | Public permitted programs and own records only | Applicant2's application; internal notes/scores/conflicts |
| `applicant2@example.test` | Applicant / Innovator | Cross-record isolation test for application B | Proposed own-record application permissions | Public permitted programs and own records only | Applicant1's application and staff/judge records |

Use non-secret test credentials supplied by the test harness or environment. Do not commit passwords, tokens, or real personal data.

## Proposed First AILH Acceptance-Test Sequence

This sequence is **PROPOSED** for execution only after approved domain migrations, policies, workflow services, and UI/API surfaces exist. It was not implemented in this interaction.

1. Authenticate each approved actor fixture.
2. Verify dashboard and route access for each actor.
3. Verify public program visibility and anonymous/authenticated boundaries.
4. Create and configure a program as authorized staff.
5. Publish the program only through the approved transition.
6. Verify the applicant can access the intended application flow.
7. Create an individual or team application according to the approved participation rule.
8. Save a draft and verify draft visibility is record-scoped.
9. Submit a versioned application before the deadline.
10. Verify deadline locking and approved revision/resubmission behavior.
11. Verify staff screening behavior and audit record.
12. Assign judge 1 to the approved application scope.
13. Verify judge 1 can access the assignment and judge 2 cannot access it unless separately assigned.
14. Declare and resolve/check conflict of interest using the approved categories and authority.
15. Verify a blocking conflict prevents the approved assignment/evaluation/deliberation actions.
16. Activate/freeze the approved rubric version.
17. Draft an independent evaluation against that rubric.
18. Verify judges cannot view another judge's private evaluation before the approved disclosure point.
19. Finalize the evaluation.
20. Verify finalized scores cannot be silently modified and any reopen path is authorized and audited.
21. Enter deliberation only when approved entry conditions are satisfied.
22. Record a separate human decision with rationale.
23. Verify AI cannot create or finalize the consequential decision.
24. Verify the applicant receives the approved notification after persistence.
25. Verify the applicant can see only the approved decision communication.
26. Verify activity/audit history contains actor, action, target, timestamp, and relevant before/after or rationale data.
27. Attempt each unauthorized action and verify the correct web/API denial behavior.
28. Repeat the critical persistence and authorization tests against PostgreSQL after Laravel authentication is fixed; retain SQLite compatibility tests.

## Tests / Checks Performed

### Successful

- Docker client/server verified: Docker `29.7.2`, Docker Desktop `4.86.0`.
- `dev-postgres` container found running with PostgreSQL 17 image and port 5432 published.
- Direct PostgreSQL query verified database `development`, user `postgres`, and version `17.11`.
- Direct PostgreSQL query verified all four schemas and no public relations.
- `pg_stat_activity` verified pgAdmin4 sessions on `development` and `postgres` on the same server.
- PHP PDO drivers include `pgsql` and `sqlite`.
- Current `.env` inspected with secret values redacted; it remains SQLite-based.
- RBAC source and AILH actor/open-decision documentation inspected.
- No destructive operation was run.

### Failed / incomplete

- `DB_CONNECTION=pgsql ... php artisan db:show --database=pgsql --no-interaction`: failed with PostgreSQL password authentication failure for `postgres`.
- `DB_CONNECTION=pgsql ... php artisan migrate:status --database=pgsql --no-interaction`: failed with the same authentication failure.
- PostgreSQL migration status through Laravel is therefore incomplete, although direct inspection shows no `public.migrations` table.

## Blockers

### Task-level blocker: Laravel PostgreSQL credential

The endpoint and database are verified, but the approved credential for Laravel is unknown/not accepted. The current container environment declares an initialization password, but the data directory predates the current container startup and PostgreSQL explicitly skipped initialization. Treating that value as the current password would be an unsafe assumption.

**Recommended next action:** owner/technical lead supplies the approved non-secret configuration method or enters the current password directly into the local ignored `.env` outside this handoff, then reruns Laravel `db:show` and `migrate:status` using `development`. Do not record the password.

### Project-level status

This is not a domain-architecture blocker because no AILH schema or code work is being attempted. It blocks claiming that PostgreSQL is fully ready for AILH development and blocks PostgreSQL migration/query verification.

## Risks

- PostgreSQL is empty, so applying the existing starter migrations later will create the baseline there; this has not been done in this interaction.
- The `development` database may be shared with pgAdmin or other local work despite currently being empty; owner should confirm ownership before any future migration command.
- The existing Super Admin global bypass may be too broad for immutable workflow/governance operations; this requires an explicit domain authorization decision.
- Program-scoped authorization cannot be proven with the current global starter roles and teams-disabled configuration.
- The proposed permissions and actor mapping are not approved and must not be seeded from this handoff without controller review.
- pgAdmin session presence proves same-server connectivity but not the saved credential or user intent.

## OWNER DECISION REQUIRED

1. Confirm the approved Laravel PostgreSQL credential/configuration method for `development` without recording secrets.
2. Confirm the `development` database is approved for this project and may receive the existing Master Starter migrations in a later, separately authorized interaction.
3. Confirm AILH should use the existing PostgreSQL `public` schema rather than another schema.
4. Approve whether Decision Maker is a distinct role/capability from Program Staff.
5. Approve global versus program/stage/assignment/record scope and the mechanism that carries that scope.
6. Approve whether Super Admin's global bypass includes AILH domain records and which immutable/governance operations remain blocked.
7. Approve the AILH permission catalog and actor mapping before creating roles or permissions.
8. Approve public program/application access and individual/team participation policy before acceptance tests are implemented.
9. Approve judge score visibility, conflict blocking, rubric freeze, evaluation finalization/reopening, and decision authority rules before workflow coding.

## Files Changed

- `AI-AGENT-HANDOFFS/002-postgresql-rbac-test-contract-summary.md` created.
- No application files changed.
- No migrations created or modified.
- No database schema/data changed.
- No package/config/lockfile changed.
- `.env` and `database/database.sqlite` were inspected but not modified in this interaction.

## Git State

- Branch: `main`.
- Upstream relation: `main...upstream/main`.
- Latest observed commit: `dc03e5e (HEAD -> main, upstream/main, upstream/HEAD) Add AI project starter onboarding guide`.
- Existing untracked planning/project documents remain present and untouched.
- Existing handoff `001-baseline-environment-summary.md` remains present and untouched.
- New untracked handoff: `AI-AGENT-HANDOFFS/002-postgresql-rbac-test-contract-summary.md`.
- No tracked application, migration, configuration, package, or lockfile diff was observed.

## Recommended Next Action

Stop this interaction for Product & Technical Controller review.

First resolve the approved Laravel PostgreSQL credential and confirm that `development` is the intended project database. Then run a dedicated non-destructive Laravel PostgreSQL verification interaction. After that result is accepted, the next implementation task should be the smallest approved AILH contract artifact: finalize the actor/scope permission matrix and trust-critical state-transition/test matrix before creating domain migrations.

Do not create AILH migrations, models, controllers, roles, permissions, or vertical-slice code from this handoff alone.

## Verified Facts vs Assumptions

**Verified:** Docker Desktop is reachable; `dev-postgres` is running; PostgreSQL 17.11 is listening on host port 5432; database `development` exists; user `postgres` exists; pgAdmin4 has sessions on the same server; all four PostgreSQL schemas exist; `public` has no tables; `public.migrations` is absent; Laravel's TCP connection reaches the server but fails authentication; current starter RBAC is global and teams are disabled; proposed AILH actors and permissions were not implemented.

**Not assumed:** the current password; that pgAdmin's saved credential is approved for Laravel; that `development` is safe to migrate without owner confirmation; that public schema is the final AILH schema; that Decision Maker is a separate role; that program scope can be represented by current global roles; or that any proposed AILH permission/actor mapping is approved.
