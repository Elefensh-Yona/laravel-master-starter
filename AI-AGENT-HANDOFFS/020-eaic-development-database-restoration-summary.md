# Task 020: Development Database Restoration Summary

**Interaction ID:** 020  
**Date:** 2026-09-01  
**Status:** COMPLETE  
**Test execution status:** NOT RUN BY DESIGN

## 1. Scope

Diagnose the empty local PostgreSQL `development` database and restore approved local QA/RBAC fixture data using existing seeders only. No application redesign, migration reset, UI work, later lifecycle work, Git history change, or production-authentication change was performed.

## 2. Authoritative Review

Reviewed the EAIC decisions, Blueprint, Governance Contract, RBAC Scope Matrix, Database Lifecycle Specification, Final Schema and Acceptance Contract, Pre-Migration Decision Register, Project Requirements/Roadmap, relevant FeatureTest/ManualTest specifications, and handoffs 013C1, 013C2, 013C3A, 013D4, 015B, 015D, 016C, 016D, 017, 018, and 019.

## 3. Root Cause Evidence Chain

### VERIFIED

1. The active Laravel connection is PostgreSQL (`pgsql`) to database `development` at `127.0.0.1:5432`; the connection succeeds.
2. All starter and EAIC migrations through `create_screenings_table` are recorded as run, all in migration batch 1.
3. Before restoration, users, roles, permissions, Programs, Applications, ApplicationVersions, ApplicationValidations, and Screenings all had count zero.
4. `DatabaseSeeder` is not invoked by `php artisan migrate` unless the caller supplies `--seed`; migrations create schema, not runtime data.
5. `DatabaseSeeder` calls `RolePermissionSeeder` and `SettingsSeeder`, then creates/syncs `admin@example.com`; it does not call `ManualQaFixtureSeeder`.
6. Handoff 016C records a previous successful Manual QA fixture run, but no repository evidence identifies a later destructive command, actor, or timestamp.

### DIAGNOSIS

The current state is an **empty-but-migrated development database initialized without seed data**. This conclusion is supported by the complete batch-1 schema and empty core seeded tables, combined with Laravel's explicit seed behavior.

The available evidence does **not** prove whether a database recreation, data clearing operation, environment replacement, or a fresh migration without `--seed` removed the prior runtime data. No destructive operation is asserted without evidence.

### Prevention Recommendation

**RECOMMENDED - NOT YET APPROVED:** Add a local-development onboarding instruction that explicitly runs the non-destructive sequence `php artisan db:seed --no-interaction` followed by `php artisan db:seed --class=ManualQaFixtureSeeder --no-interaction`, with the documented local-only Super Admin verification correction. Do not automatically include QA fixtures in general or production seeding without controller approval.

## 4. Existing Seeder Relationship

### DatabaseSeeder

- Calls `RolePermissionSeeder` and `SettingsSeeder`.
- Uses `updateOrCreate` for `admin@example.com` and assigns `Super Admin`.
- Does not set admin `email_verified_at` and does not invoke Manual QA fixtures.

### RolePermissionSeeder

- Uses `Permission::findOrCreate` and role `updateOrCreate`; invalidates Spatie's permission cache before and after seeding.
- Creates the four source-defined starter roles: `Super Admin`, `Manager`, `Staff`, and `Guest`.
- Creates 33 source-defined permissions, including canonical Application permissions and Eligibility permissions.
- Does not create EAIC-specific roles or grant EAIC permissions to starter roles.

### ManualQaFixtureSeeder

- Uses `firstOrCreate` for five QA accounts and fixture programs, so it is idempotent by natural key.
- Preserves an existing Super Admin; it does not overwrite the existing admin verification state.
- Creates two Programs: `EAIC-2026-01` and `EAIC-2026-02`.
- Creates one active `program_staff` ProgramMembership for Program A only.
- Creates two ProgramEligibilityRules and two draft Rubrics.
- Assigns the QA Program Staff the documented ten direct Program, Eligibility, and Rubric permissions.
- Assigns no roles or direct permissions to the QA Applicant, Judge, or Decision Maker.

## 5. Restoration Performed

### IMPLEMENTED

Executed the existing non-destructive local sequence:

1. `php artisan db:seed --no-interaction`
2. `php artisan db:seed --class=ManualQaFixtureSeeder --no-interaction`
3. Set `email_verified_at` on the existing `admin@example.com` row only, following the documented Task 016D local QA correction.

The Super Admin email, password, and role were preserved. No extra administrator was created.

### Verified Restored State

| Record type | Count |
|---|---:|
| Users | 5 |
| Roles | 4 |
| Permissions | 33 |
| Programs | 2 |
| Program memberships | 1 |
| Eligibility rules | 2 |
| Rubrics | 2 |
| Applications | 0 |
| Application versions | 0 |
| Application validations | 0 |
| Screenings | 0 |

All five documented accounts exist exactly once and are email verified. No duplicate QA emails or fixture Program codes were found.

## 6. RBAC Verification

### Roles

- `admin@example.com`: `Super Admin` role retained.
- The QA Program Staff, Decision Maker, Judge, and Applicant have no fixture-assigned roles.
- No EAIC-specific global role was created.

### Direct Permissions and Scope

- QA Program Staff has the existing fixture's ten direct permissions: Program (`view/create/update/publish`), Eligibility (`view/validate/screen`), and Rubric (`view/create/update`).
- QA Program Staff has exactly one active `program_staff` membership for `EAIC-2026-01`, and none for Program B.
- QA Decision Maker, Judge, and Applicant have no direct permissions, preserving their future assignment/ownership boundaries.
- The permission registry has exactly one each of `application.view`, `application.create`, `application.update`, `application.submit`, `eligibility.view`, `eligibility.validate`, and `eligibility.screen`.
- `application.revise` has count zero.

## 7. Safety and Verification

### Safety

- No `migrate:fresh`, `db:wipe`, truncate, database/table drop, deletion, or migration execution occurred.
- No existing unrelated records existed before restoration; none were removed.
- Existing idempotent seeders were used; duplicate checks found none.
- No RBAC architecture, application code, Screening implementation, UI, migrations, historical handoffs, or governance documents were modified.

### Focused Runtime Verification

Performed read-only checks for PostgreSQL identity, migration state, row counts, exact QA identities, email verification, role assignments, direct permissions, permission occurrence counts, Program Staff scope, fixture Program/rule/rubric rows, and duplicate groups.

The first verification query failed only because PostgreSQL does not allow a selected alias in `HAVING`. It was corrected once to `HAVING COUNT(*) > 1` and rerun successfully under the one-retry rule.

No Pest, PHPUnit, or broad automated/browser test was run.

## 8. Documentation Created

- `FeatureTest/020-local-qa-rbac-fixture-restoration-specification.md`: future automated specification; NOT EXECUTED.
- `ManualTest/ManualTest_08_Local_QA_RBAC_Fixture.md`: future browser/manual QA specification; all scenarios NOT RUN.

## 9. Known Issues and Blocks

### BLOCKED

- Eligibility/Screening controllers reference Vue pages that do not currently exist. Restored fixture data does not resolve that delivery gap.

### KNOWN LIMITATION

- Eligibility validation rule evaluation remains an explicit passing placeholder; no generic rule engine was added.

### OWNER DECISION REQUIRED

- Exact persisted role-to-permission grants beyond the existing fixture behavior.
- Exact applicant-visible screening messaging.
- Additional screening result taxonomy.
- Whether every Screening requires a Validation record.
- Downstream lifecycle transitions after screening.

## 10. Recommended Next Task

**RECOMMENDED - NOT YET APPROVED:** Review the restored local fixture state, then approve a narrowly scoped Eligibility/Screening Vue delivery task before manual workflow QA. Do not begin Judge Assignment, Conflict, Evaluation, Deliberation, Decision, Outcome, Notifications, or AI work.

## 11. Verified Facts vs Assumptions

### Verified Facts

- The active PostgreSQL database is `development`, has complete migration history, and was empty before restoration.
- Existing seeders restored the intended five local QA users, core roles/permissions, Programs, scope, eligibility rules, and rubrics non-destructively.
- The Admin retains Super Admin role, expected password validity, and now has local QA verification state.
- No fixture duplication or downstream lifecycle data was created.

### Assumptions Avoided

- No claim about who or what cleared/recreated the prior database data.
- No claim that the restored accounts have passed browser/manual QA.
- No new EAIC role, permission grant, lifecycle state, or Screening behavior was inferred.

## 12. Stop Condition

- [x] Empty database diagnosis documented with evidence boundary.
- [x] Existing governed QA/RBAC fixtures restored safely.
- [x] QA accounts, canonical permissions, and Program Staff scope verified.
- [x] FeatureTest and ManualTest specifications created.
- [x] Handoff 020 created.
- [x] No later lifecycle or UI implementation begun.

Await Product and Technical Controller review.
