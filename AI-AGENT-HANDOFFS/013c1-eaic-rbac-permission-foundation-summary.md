# AI Agent Handoff 013C-1: EAIC RBAC Permission Foundation Summary

## 1. Interaction ID

`013C-1`

## 2. Task Requested

Implement only the EAIC MVP permission/capability foundation for Programs, Program Memberships, Program Eligibility Rules, and Rubrics. Reuse the existing Spatie permission infrastructure, do not create EAIC roles, policies, controllers, routes, UI, or downstream permissions, and provide focused tests.

## 3. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- Handoffs 001 through 013B, including Batch 1 scope in Handoff 013A and model/test boundary in Handoff 013B.
- Existing `config/permission.php`, `database/seeders/RolePermissionSeeder.php`, `app/Support/SystemRole.php`, existing role tests, and PostgreSQL permission state.

## 4. Permission Catalog Implemented

The existing `RolePermissionSeeder` now creates these exact `web`-guard EAIC Batch 1 permission records:

- `program.view`
- `program.create`
- `program.update`
- `program.publish`
- `eligibility.view`
- `eligibility.validate`
- `eligibility.screen`
- `rubric.view`
- `rubric.create`
- `rubric.update`

No permissions for applications, application members/versions, assignments, conflicts, evaluations, deliberation, decisions, outcomes, notifications, AI, or deferred modules were added.

## 5. Role and Capability Mapping Implemented

- No EAIC role was created.
- No existing role received an EAIC Batch 1 permission grant.
- Inherited `Manager` and `Staff` roles retain their starter-only permission assignments and do not silently become EAIC authorities.
- Program-specific EAIC capability remains represented by the already approved `program_memberships.capability` field/model relationship.
- Super Admin's existing global Gate behavior was not changed; protected EAIC history/governance enforcement remains a later policy task.
- Decision Maker, Judge, and Applicant receive no Batch 1 domain role grant in this task.

## 6. Starter RBAC Reuse

The implementation reuses:

- Spatie `permissions`, `roles`, and pivot tables.
- The existing `RolePermissionSeeder` and `Permission::findOrCreate(..., 'web')` idempotent pattern.
- Existing `SystemRole` constants and starter role synchronization.
- Existing permission cache invalidation through `PermissionRegistrar`.

No duplicate authorization table, role system, or RBAC configuration was added.

## 7. Files Created

- `tests/Feature/BatchOnePermissionFoundationTest.php`
- `AI-AGENT-HANDOFFS/013c1-eaic-rbac-permission-foundation-summary.md`

## 8. Files Modified

- `database/seeders/RolePermissionSeeder.php`

## 9. Files Intentionally Not Modified

- Handoffs 001–013B.
- `TheRoadmap/decisions.md` and EAIC contracts/specifications.
- Existing Starter roles, existing Starter permission definitions, `SystemRole`, and RBAC configuration.
- Migrations, models, factories, policies, controllers, routes, UI, application/judging/evaluation/deliberation/decision/outcome code, packages, lockfiles, and `.env`.

## 10. Tests Created

`tests/Feature/BatchOnePermissionFoundationTest.php` covers:

1. Exact canonical Batch 1 permission names and `web` guard.
2. Idempotent seeding without duplicate records.
3. Preserved starter role set and no implicit Batch 1 EAIC grant to `Manager` or `Staff`.
4. Existing starter role permissions remain functional.
5. No future EAIC permissions in the Batch 1 foundation.

## 11. Test Results

- Native focused command: `php artisan test --compact tests/Feature/BatchOnePermissionFoundationTest.php`
- Result after correction: **4 passed, 8 assertions**.
- `vendor/bin/pint --dirty --format agent`: passed.
- Focused test rerun after formatting: passed.
- Editor diagnostics for seeder and focused test: no errors.
- `git diff --check`: passed.

## 12. Verification Performed

- Verified source catalog contains exactly the 10 canonical Batch 1 names.
- Verified no future EAIC permission is present in the source seeder.
- Verified the existing four Starter roles remain `Guest`, `Manager`, `Staff`, and `Super Admin`.
- Verified PostgreSQL `development` after direct seed: Batch 1 permissions `10`, total permissions `28`, future permission sample count `0`, Manager Batch 1 grants `0`, Staff Batch 1 grants `0`.
- Verified no migration count change: 18 files/records remain.
- Verified no policies, controllers, routes, UI, or downstream EAIC artifacts were created.
- Verified historical Handoffs 001–013B remain unchanged.

## 13. PostgreSQL and SQLite Impact

### PostgreSQL

- The approved `development` database received the 10 EAIC Batch 1 permission records through the existing idempotent `RolePermissionSeeder`.
- No schema, migration, role, or role-permission pivot change occurred.
- Existing starter role assignments remain unchanged.

### SQLite

- The focused Pest test uses the existing in-memory SQLite test configuration and seeds the same permission foundation for each test.
- Four focused tests and eight assertions pass.

## 14. Failure and One-Retry Result

Two local verification issues occurred and were resolved within the one-retry policy:

1. The generic test-runner integration did not discover the new Pest file with an absolute path. The one retry using the native Artisan focused test command passed.
2. The first execution-subagent PostgreSQL seed attempt reported completion, but read-only verification showed `batch=0`, `total=18`. A single direct Artisan seed command against PostgreSQL `development` was run and verified `batch=10`, `total=28`.

Neither issue left a persistent application or database failure.

## 15. Database Changes

- No migration or database schema changed.
- PostgreSQL `development` received only the 10 approved Batch 1 EAIC permission records via the existing seeder.
- No EAIC roles, role grants, domain data, or downstream permissions were inserted.
- No destructive database command was run.

## 16. Known Risks

- Permission records now exist, but no EAIC role grants, policies, route middleware, or record-level authorization exists yet. The records alone must not be treated as access control.
- The existing Super Admin `Gate::before` remains broad until a later policy task implements protected-history governance boundaries.
- Literal role-to-permission grants remain intentionally deferred; Program Membership capability is not yet enforced by policy.
- The `opens_at < closes_at` database-level check remains unresolved from Handoff 013A.

## 17. Recommended Next Task

Stop for Product & Technical Controller review.

The next authorized task should be 013C-2: implement only Batch 1 policies and focused policy tests for Programs, Program Memberships, Program Eligibility Rules, and Rubrics. It should use the seeded permission foundation plus active Program Membership capability and should not add routes, UI, applications, judging, evaluation, or other later-domain workflows.

## 18. Verified Facts vs Assumptions

**Verified:** the 10 exact Batch 1 EAIC permissions are in the existing seeder and PostgreSQL `development`; the database has 28 total permissions and no sampled future EAIC permissions; Manager and Staff received zero Batch 1 EAIC grants; focused SQLite tests pass with 4 tests/8 assertions; no schema/migration or downstream implementation changed.

**Assumptions avoided:** no EAIC role was invented, no inherited role was repurposed, no literal EAIC role-to-permission grant was assumed, no policy/record scope enforcement was claimed, and no downstream permission or workflow was implemented.
