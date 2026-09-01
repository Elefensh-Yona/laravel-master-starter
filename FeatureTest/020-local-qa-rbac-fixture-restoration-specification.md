# Task 020: Local QA RBAC Fixture Restoration Specification

**Status:** Specification only. NOT EXECUTED.  
**Scope:** Local PostgreSQL development fixture and RBAC restoration.

## Preconditions

- Migrations have run against local PostgreSQL `development`.
- `DatabaseSeeder` has run before `ManualQaFixtureSeeder`.
- The fixture is evaluated only in a local development/QA environment.

## QA-FIXTURE-001: Five documented accounts exist

- **Actor/account:** Fixture records.
- **Action:** Run `DatabaseSeeder`, then `ManualQaFixtureSeeder`.
- **Expected database/RBAC result:** Exactly one row exists for each documented QA email: Super Admin, Program Staff, Decision Maker, Judge, and Applicant.
- **Security reason:** Reproducible QA identities must not be replaced or duplicated.
- **Evidence requirement:** Per-email grouped counts equal one.
- **PASS:** All five accounts exist exactly once.
- **FAIL:** Any account is absent or duplicated.

## QA-FIXTURE-002: Required QA accounts are email verified

- **Actor/account:** All five documented QA accounts.
- **Action:** Inspect `email_verified_at` after local fixture restoration and the documented Super Admin correction.
- **Expected database/RBAC result:** Every documented QA account has a non-null verification timestamp.
- **Security reason:** Local QA can traverse verified routes without disabling Fortify verification globally.
- **Evidence requirement:** Non-null `email_verified_at` for all five accounts.
- **PASS:** All five timestamps are present.
- **FAIL:** Any QA account remains unverified.

## QA-FIXTURE-003: Super Admin retains Super Admin role

- **Actor/account:** `admin@example.com`.
- **Action:** Inspect assigned roles after `DatabaseSeeder` and local verification correction.
- **Expected database/RBAC result:** Exactly the existing `Super Admin` role is assigned; the password remains valid.
- **Security reason:** The recovery account must retain governed administrative identity without creating another admin.
- **Evidence requirement:** Role relation and password verification check.
- **PASS:** Admin exists, is verified, retains `Super Admin`, and its password remains unchanged.
- **FAIL:** Admin is missing, renamed, reassigned, duplicated, or has an invalid expected password.

## QA-RBAC-001: Canonical Application permissions exist once

- **Actor/account:** Permission registry.
- **Action:** Run normal core/RBAC seeding and group permission rows by name.
- **Expected database/RBAC result:** `application.view`, `application.create`, `application.update`, and `application.submit` each exist exactly once with `web` guard.
- **Security reason:** Route and policy authorization relies on canonical permission records.
- **Evidence requirement:** Per-name count equals one.
- **PASS:** All four canonical permissions exist exactly once.
- **FAIL:** Any is missing or duplicated.

## QA-RBAC-002: Deprecated application.revise permission is absent

- **Actor/account:** Permission registry.
- **Action:** Search for `application.revise`.
- **Expected database/RBAC result:** No matching permission exists.
- **Security reason:** Revision uses `application.update`; a second permission would alter the approved authorization contract.
- **Evidence requirement:** Permission count equals zero.
- **PASS:** Count is zero.
- **FAIL:** Any `application.revise` row exists.

## QA-RBAC-003: Program Staff grants and scope match fixture

- **Actor/account:** `qa-program-staff@example.com`.
- **Action:** Inspect direct permissions and program memberships.
- **Expected database/RBAC result:** The account has only the fixture's documented direct Program, Eligibility, and Rubric permissions and one active `program_staff` membership for `EAIC-2026-01`.
- **Security reason:** Global permission and program scope are separate authorization layers.
- **Evidence requirement:** Direct-permission list and membership/program-code query.
- **PASS:** Grants and one scoped active membership match the fixture.
- **FAIL:** Any required grant/scope is absent or a broad/unintended grant exists.

## QA-RBAC-004: Judge has no unauthorized global permission

- **Actor/account:** `qa-judge@example.com`.
- **Action:** Inspect direct permissions and roles.
- **Expected database/RBAC result:** No direct permissions or roles are assigned by this fixture.
- **Security reason:** A Judge needs future program and application assignment; QA identity must not bypass that boundary.
- **Evidence requirement:** Empty direct-permission and role relations.
- **PASS:** Both relations are empty.
- **FAIL:** A global Staff, Judge, Program, Eligibility, or application grant is present.

## QA-RBAC-005: Decision Maker has no unauthorized global permission

- **Actor/account:** `qa-decision-maker@example.com`.
- **Action:** Inspect direct permissions and roles.
- **Expected database/RBAC result:** No direct permissions or roles are assigned by this fixture.
- **Security reason:** Decision authority remains program-scoped and separately governed.
- **Evidence requirement:** Empty direct-permission and role relations.
- **PASS:** Both relations are empty.
- **FAIL:** An unrelated global authorization grant is present.

## QA-RBAC-006: Applicant has no unauthorized Staff/Judge permission

- **Actor/account:** `qa-applicant@example.com`.
- **Action:** Inspect direct permissions and roles.
- **Expected database/RBAC result:** No direct permissions or roles are assigned by this fixture.
- **Security reason:** Applicant access is ownership/delegation based and must not grant Staff/Judge authority.
- **Evidence requirement:** Empty direct-permission and role relations.
- **PASS:** Both relations are empty.
- **FAIL:** A Staff, Judge, Decision Maker, Program, Eligibility, or administrative grant is present.

## QA-SCOPE-001: Program Staff scope is limited to intended program

- **Actor/account:** `qa-program-staff@example.com`.
- **Action:** Query active ProgramMembership records for the fixture account.
- **Expected database/RBAC result:** One active `program_staff` membership exists for `EAIC-2026-01`; none exists for `EAIC-2026-02`.
- **Security reason:** Cross-program operations require explicit scope.
- **Evidence requirement:** Membership count and joined program codes.
- **PASS:** Scope is only Program A.
- **FAIL:** Program B or duplicate active scope exists.

## QA-FIXTURE-004: Existing fixture is idempotent

- **Actor/account:** All fixture records.
- **Action:** Execute the approved seed chain twice in a disposable local QA database.
- **Expected database/RBAC result:** `firstOrCreate`, `updateOrCreate`, and permission cache handling preserve unique fixture identities.
- **Security reason:** Repeated setup must not create duplicate identities or scope records.
- **Evidence requirement:** Row counts and unique natural keys before/after second execution.
- **PASS:** Second run creates no duplicates.
- **FAIL:** Any user, program, membership, rule, rubric, permission, or role duplicates.

## QA-FIXTURE-005: Fixture rerun preserves fixture identities

- **Actor/account:** QA accounts and Programs `EAIC-2026-01`, `EAIC-2026-02`.
- **Action:** Re-run existing fixture after successful setup.
- **Expected database/RBAC result:** Existing users/programs are selected by email/code and not recreated; no downstream application, validation, or screening records are introduced.
- **Security reason:** Fixture setup must remain additive and limited to its approved domain.
- **Evidence requirement:** Natural-key counts remain one; downstream record counts remain unchanged.
- **PASS:** Fixture identities remain unique and no lifecycle data is added.
- **FAIL:** Duplicate fixture identities or unexpected downstream data exists.
