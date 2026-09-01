# AI Agent Handoff 013C-3A: Program Bootstrap Policy Correction

## 1. Interaction ID

`013C-3A`

## 2. Problem Found

The 013C-2 `ProgramPolicy::create()` method required an active `program_staff` membership in an existing Program. That caused a bootstrap circular dependency: the first Program could not be created because no Program Membership could exist yet.

## 3. Recovery State

- Branch: `main`, tracking `upstream/main`.
- The reviewed 013A, 013B, 013C-1, and 013C-2 Batch 1 work was present as uncommitted work.
- The only correction needed was the Program creation membership prerequisite.
- Pre-existing unrelated tracked edits to `.env.example` and `TheRoadmap/decisions.md`, plus pre-existing untracked Batch 1 artifacts and EAIC documents, were preserved.

## 4. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `AI-AGENT-HANDOFFS/013c2-eaic-batch1-policy-summary.md`
- Current `Program` model and `ProgramPolicy`
- Existing `RolePermissionSeeder`, `AppServiceProvider`, Starter role conventions, and the existing policy scope concern.

## 5. Policy Changes

- Changed `ProgramPolicy::create(User $user)` to return only `$user->can('program.create')`.
- Removed the now-unused `hasActiveProgramStaffScopeAnywhere()` helper from `InteractsWithProgramScope`.
- Did not change Program update or publish logic.

## 6. Exact Bootstrap Authorization Behavior

`program.create` is now the global/bootstrap authorization action.

- It requires the existing `program.create` permission.
- It does not require a Program ID, target Program Membership, or prior Program Staff membership in any Program.
- It does not map inherited Starter `Manager` or `Staff` roles to EAIC authority.
- It does not add an EAIC role, capability, permission, or permission grant.

The first Program can therefore be authorized for an actor who already has the required global permission. Program-scoped authority begins only once the Program and its membership relationship exist.

## 7. Program Update and Publish Behavior

Unchanged from 013C-2:

- `update` requires `program.update`, active `program_staff` membership in the target Program, and a non-archived target Program.
- `publish` requires `program.publish`, active `program_staff` membership in the target Program, `draft` state, and the defensive `opens_at < closes_at` condition.
- A permission alone still cannot update or publish an unrelated Program.

## 8. Test Specifications Created or Updated

Created `FeatureTest/013c3a-program-bootstrap-authorization-specification.md` in the existing required `FeatureTest/` directory.

It records the requested cases, each with test ID, actor, preconditions, action, expected result, and security reason:

- `RBAC-PROGRAM-CREATE-001` through `RBAC-PROGRAM-CREATE-005`
- `RBAC-PROGRAM-UPDATE-001` through `RBAC-PROGRAM-UPDATE-002`
- `RBAC-PROGRAM-PUBLISH-001` through `RBAC-PROGRAM-PUBLISH-002`
- `RBAC-PROGRAM-SCOPE-001`

## 9. Static Verification Performed

- PHP syntax checks passed for `ProgramPolicy` and `InteractsWithProgramScope`.
- Laravel Pint ran on dirty PHP files and formatted the scope concern's class-attribute spacing.
- `git diff --check` passed.
- Inspected the intended policy and FeatureTest specification changes. No migration, schema, package, permission catalog, or `.env` change was made by this interaction.

## 10. Test Execution Status

**NOT RUN BY DESIGN.**

No Pest, focused application, full application, or unrelated test suite was executed, per the current credit-efficient testing policy. The required cases were recorded as specifications only.

## 11. Database Changes

None.

No migration, schema, role table, permission table, permission seed catalog, role grant, or domain data was changed.

## 12. Files Modified

- `app/Policies/ProgramPolicy.php`
- `app/Policies/Concerns/InteractsWithProgramScope.php`
- `FeatureTest/013c3a-program-bootstrap-authorization-specification.md`
- `AI-AGENT-HANDOFFS/013c3a-eaic-program-bootstrap-policy-summary.md`

## 13. Files Intentionally Not Modified

- Handoffs 001–013C-2.
- `TheRoadmap/decisions.md` and all EAIC blueprint/contract/matrix/schema documents.
- Models, factories, migrations, database configuration, role tables, permission tables, the permission seeder, and Starter roles.
- Routes, controllers, requests, middleware, frontend, UI, packages, lockfiles, and `.env`.
- Applications, judging, evaluations, deliberation, decisions, outcomes, notifications, and AI work.

## 14. Known 013A Issue

The database-level `opens_at < closes_at` constraint remains unresolved. No corrective migration was created or modified. Program publication retains its existing defensive policy check.

## 15. Known Risks

- `program.create` is now intentionally global/bootstrap authorization. Exact future EAIC grants of that permission remain a controller-governed RBAC decision; no role grant is implied by this policy.
- The existing Super Admin `Gate::before` remains unchanged and broad; protected-history governance remains later work.
- The FeatureTest artifact is a specification, not executed coverage. Route/controller enforcement is still outside this task.

## 16. Recommended Next Task

Stop for Product & Technical Controller review. The controller should approve the eventual grant path for `program.create` before a Program administration route/controller is introduced, then authorize implementation of that delivery path separately.

## 17. Verified Facts vs Assumptions

**Verified facts:** `ProgramPolicy::create()` now checks only the existing `program.create` permission; no Program Membership query remains on that path; update and publish remain target-Program-scoped; the required FeatureTest specification exists; PHP syntax, Pint, and whitespace checks passed; no database change occurred.

**Assumptions kept explicit:** `program.create` is treated as the required global/bootstrap permission because creation has no target Program and no approved separate bootstrap EAIC role/capability exists. This does not grant the permission to any existing role or actor, and it does not change Program-scoped management rules.
