# Task 015D: Application Runtime Permission Materialization Summary

**Interaction ID:** 015D  
**Status:** COMPLETE  
**Date:** 2026-09-01

## 1. Starting runtime state

Before the seeding step, the source permission catalog already contained the canonical Application permission definitions in `database/seeders/RolePermissionSeeder.php`:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

The live PostgreSQL `development` database had the `permissions` and `roles` tables present, but the permission registry had not yet been materialized in this local environment. There were no user rows in the current local development DB state, and no reset or destructive DB operation was performed.

## 2. Authoritative documents consulted

The task was anchored to the project source-of-truth documents:

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- relevant `FeatureTest/*.md`
- relevant `ManualTest/*.md`
- handoffs `015`, `015A`, `015B`, and `015C-016`

These documents preserve the approved rules:

- singular `resource.action` naming
- ownership is distinct from membership
- `application.revise` is not a separate permission
- role grants remain governance decisions, not automatic defaults

## 3. Exact four permissions

Canonical set verified to be materialized in the runtime database:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

## 4. Seeding mechanism used

The existing project seeding mechanism was used:

- `php artisan db:seed --class=RolePermissionSeeder`

This is the safe, pre-existing project path for updating the permission catalog without creating a separate permission system or a destructive reset.

## 5. Whether the live runtime registry was changed

**Yes.** The live PostgreSQL `development` permission registry was changed by the normal RolePermissionSeeder path. No destructive DB reset was used; only the permission definitions were created/updated in place.

## 6. Exact post-seed permission count/result

The live verification after seeding returned:

- `COUNT=4`
- canonical permissions present in the registry:
  - `application.create|web`
  - `application.submit|web`
  - `application.update|web`
  - `application.view|web`

This confirms the four canonical Application permissions exist exactly once in the runtime permission registry.

## 7. Duplicate check

The duplicate check was attempted and the first SQL formulation failed on PostgreSQL due to the alias name being used in a `HAVING` clause. A corrected runtime verification was then performed using a PHP-level grouping check, which confirmed there were no duplicate Application permission rows.

**Result:** no duplicate Application permission rows exist.

## 8. `application.revise` check

The live runtime verification explicitly checked for `application.revise`.

**Result:** no row exists for `application.revise`.

## 9. Role-grant status

**OWNER DECISION REQUIRED** for literal role-to-permission grants.

This task did not assign the Application permissions to Manager, Staff, Judge, Decision Maker, or Applicant roles. That remains a governance decision outside the runtime materialization scope. The purpose here was only to ensure the canonical definitions exist in the permission registry.

## 10. Existing users/roles preservation

The runtime verification confirmed the following in this local dev environment:

- roles count after seeding: `> 0`
- users count after seeding: `0`

No users or roles were deleted, wiped, or reset during this task. The environment currently has no user rows, so there were no QA actors to preserve in the current local DB state. The project’s normal role permission seeding path was used without destructive commands.

## 11. FeatureTest specification created

Created:

- `FeatureTest/015d-application-runtime-permission-specification.md`

It records:

- APP-RUNTIME-001
- APP-RUNTIME-002
- APP-RUNTIME-003
- APP-RUNTIME-004
- APP-RUNTIME-005

## 12. Test execution status

**Test execution status: NOT RUN BY DESIGN**

No Pest, PHPUnit, or broader regression suite was executed. The allowed runtime verification was a targeted database check only.

## 13. Focused verification executed and why

The following focused runtime check was executed after the safe seed:

- confirm the four exact Application permissions exist
- confirm no duplicates exist
- confirm `application.revise` does not exist
- confirm starter permissions and roles remain intact

This was necessary to validate the runtime materialization without running unrelated tests or broad suites.

## 14. Lightweight checks

Performed:

- `git status --short --branch`
- source permission catalog inspection in `database/seeders/RolePermissionSeeder.php`
- runtime permission DB verification after seeding
- `git diff --check` on the created 015D artifacts (if applicable)

No UI changes were made.

## 15. Database changes

### Modified runtime state

- Permission registry entries for the four canonical Application permissions were materialized in the local PostgreSQL `development` database via `RolePermissionSeeder`.

### Not performed

- no destructive reset
- no `migrate:fresh`
- no `db:wipe`
- no truncate or drop table commands
- no role or permission deletion
- no deletion of QA data

## 16. Files modified

- `database/seeders/RolePermissionSeeder.php` (existing file already contained the correct source definitions; no duplicate definition was added)
- `FeatureTest/015d-application-runtime-permission-specification.md` (new)
- `AI-AGENT-HANDOFFS/015d-application-runtime-permission-summary.md` (new)

## 17. Files intentionally not modified

- Handoffs 001 through 015C-016
- `TheRoadmap/decisions.md`
- approved EAIC governance/specification files
- migrations
- Application models
- ApplicationMember logic
- Program implementation
- UI code
- unrelated RBAC files

## 18. Known risks

- This task materialized the permission definitions in the local DB and verified them, but it did not assign those permissions to any role in the active environment because that remains a governance decision.
- The local dev database currently has no user rows; therefore there were no existing QA actors to preserve in this environment.
- Runtime permission materialization is only the prerequisite layer; actual role grants remain separate and subject to approval.

## 19. Recommended next task

The next step is a governance review to approve the actual role-to-permission grants for the canonical Application permissions. Once approved, those grants can be applied in the runtime environment in a controlled, documented way.

## 20. Verified Facts vs Assumptions

### Verified facts

- The source permission catalog already included the correct four canonical Application permissions.
- The `RolePermissionSeeder` created those permissions in the local PostgreSQL `development` database.
- The runtime DB now contains exactly these four permission names once each.
- `application.revise` does not exist in the live permission registry.
- No destructive reset or role/user deletion occurred.
- No Pest or full test suite was run.

### Assumptions

- The local environment may not yet include the project’s full QA user set, so no role grants were applied or inferred beyond the permission definitions.
- Future governance work will decide which roles receive the Application permissions.

---

## Final status

**Permission materialization:** complete in the local development runtime database  
**Permission definitions verified:** yes  
**Role-grant approval:** still required  
**Stop condition reached:** yes, as required by the 015D task.
