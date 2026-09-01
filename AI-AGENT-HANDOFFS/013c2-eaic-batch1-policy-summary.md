# AI Agent Handoff 013C-2: EAIC Batch 1 Policies and Focused Authorization Tests

## 1. Interaction ID

`013C-2`

## 2. Recovery State Found

- Branch: `main`, tracking `upstream/main`.
- The verified 013A migrations, 013B models/factories/tests, and 013C-1 permission foundation were present as uncommitted work.
- No partial 013C-2 policies or policy tests existed.
- Pre-existing unrelated tracked edits to `.env.example` and `TheRoadmap/decisions.md` were found and left unchanged. Pre-existing untracked EAIC documents, historical handoffs, and Batch 1 artifacts were also preserved.

## 3. Task Requested

Implement only Laravel authorization policies and focused Pest policy tests for the Batch 1 `Program`, `ProgramMembership`, `ProgramEligibilityRule`, and `Rubric` domains. Do not add schema, EAIC roles, routes, controllers, UI, or downstream workflows.

## 4. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- Handoffs `013a-eaic-batch1-migrations-summary.md`, `013b-eaic-batch1-models-tests-summary.md`, and `013c1-eaic-rbac-permission-foundation-summary.md`
- Current Batch 1 models, factories, migrations, permission seeder, Starter policies, `AppServiceProvider`, and relevant existing focused tests.

## 5. Policies Created or Modified

Created:

- `app/Policies/Concerns/InteractsWithProgramScope.php`
- `app/Policies/ProgramPolicy.php`
- `app/Policies/ProgramMembershipPolicy.php`
- `app/Policies/ProgramEligibilityRulePolicy.php`
- `app/Policies/RubricPolicy.php`

Modified:

- `app/Providers/AppServiceProvider.php` registers the four new model policies. The existing Starter `Gate::before` behavior was not changed.

## 6. Policy Methods Implemented

| Domain | Methods | Record and state boundary |
|---|---|---|
| Program | `view`, `create`, `update`, `publish` | Published Programs are visible; non-public view requires permission plus active Program scope. Update denies archived Programs. Publish requires an active Program Staff membership, a draft Program, and `opens_at < closes_at`. |
| Program Membership | `update` | Requires Program update permission, active Program Staff scope, and an active target membership. Ended/suspended historical membership rows cannot be updated through this ordinary policy. |
| Program Eligibility Rule | `view`, `validate`, `screen` | Published/enabled rule visibility is allowed by the currently represented public state; internal access requires permission and active Program scope. Validation is limited to enabled rules. Screening requires active Program Staff capability. |
| Rubric | `view`, `create`, `update` | Create receives the target Program so scope can be checked. Update requires a draft rubric. A frozen (or any non-draft) rubric cannot be updated. |

No speculative membership, application, assignment, evaluation, or workflow methods were added.

## 7. Permission Checks Used

- `program.view`, `program.create`, `program.update`, `program.publish`
- `eligibility.view`, `eligibility.validate`, `eligibility.screen`
- `rubric.view`, `rubric.create`, `rubric.update`

Every non-public scoped action requires both the named permission and the policy's active Program Membership scope check. The direct policy test `a program permission without required scope is denied` proves permission alone does not allow an unrelated Program update.

## 8. Program Membership and Capability Behavior

- Active scope is queried from `program_memberships` using the target Program, user, and `status = active`.
- Program Staff actions additionally require the already-established `program_staff` capability used by the existing Batch 1 factory.
- No second EAIC role system, Spatie Teams configuration, or EAIC role grant was introduced.
- Program creation has no target Program to scope. It requires `program.create` plus an existing active `program_staff` membership in at least one Program, which establishes explicit EAIC administration capability without treating a Starter role as EAIC authority.
- An ended membership cannot satisfy scope, and ordinary membership update denies non-active historical rows.

## 9. Super Admin Behavior

The existing Starter `Gate::before` Super Admin bypass remains unchanged as instructed. No EAIC policy claims that Super Admin may silently rewrite governed history. This task's ordinary protected-row guards are ready for later governance-specific enforcement, but the inherited global bypass remains a known later boundary.

## 10. Manager and Staff Behavior

The inherited Starter `Manager` and `Staff` roles were not changed and are not checked by these policies. EAIC Program Staff authority is established only through an active `program_staff` Program Membership together with the relevant direct/role-derived permission.

## 11. Focused Tests Created

Created `tests/Feature/BatchOnePolicyTest.php` with 21 focused direct policy tests:

- Program: in-scope view, cross-program update denial, permission-without-scope denial, authorized/unauthorized create, authorized/unauthorized publish.
- Program Membership: authorized active update, wrong Program denial, inactive source membership denial, historical target membership denial.
- Eligibility: scoped Staff view/validate/screen, Applicant-with-permission denial, and out-of-scope screen denial.
- Rubric: scoped view/create/update, cross-program update denial, and frozen update denial.

## 12. Test Results

`php artisan test --compact tests/Feature/BatchOnePolicyTest.php`

Result after implementation: **21 passed (21 assertions)**.

Result after Pint: **21 passed (21 assertions)**.

## 13. One-Retry Results

No test or verification failure occurred. No retry was required.

## 14. Verification Performed

- PHP syntax checks passed for every new policy/concern and the focused test file.
- Focused Pest policy suite passed before and after formatting.
- `vendor/bin/pint --dirty --format agent` passed.
- `git diff --check` passed.

## 15. Database Changes

None in 013C-2.

No migrations, schema, roles, permission rows, role-permission grants, or domain records were changed. Tests used the existing in-memory SQLite test configuration only.

## 16. Files Modified

- `app/Providers/AppServiceProvider.php`
- `app/Policies/Concerns/InteractsWithProgramScope.php`
- `app/Policies/ProgramPolicy.php`
- `app/Policies/ProgramMembershipPolicy.php`
- `app/Policies/ProgramEligibilityRulePolicy.php`
- `app/Policies/RubricPolicy.php`
- `tests/Feature/BatchOnePolicyTest.php`
- `AI-AGENT-HANDOFFS/013c2-eaic-batch1-policy-summary.md`

## 17. Files Intentionally Not Modified

- Handoffs 001–013B and 013C-1.
- `TheRoadmap/decisions.md` and all EAIC governance/contract documents.
- Existing Batch 1 migrations, models, factories, and permission seeder.
- Existing Starter roles, permissions, role grants, `Gate::before`, routes, controllers, requests, middleware, frontend, UI, packages, and lockfiles.
- Application, ownership, Judge, evaluation, deliberation, decision, outcome, notification, and AI work.

## 18. Known 013A Issue Status

The `opens_at < closes_at` database-level constraint remains unresolved exactly as reported in 013A. No migration was created or changed. The Program publish policy defensively requires that ordering for the publish action, but it does not replace the missing database constraint.

## 19. Known Risks

- Capability literals were not formally enumerated by the schema contract; this policy uses the existing Batch 1 factory's `program_staff` capability as the concrete Program Staff capability.
- Exact public Program fields and eligibility-rule transparency tiers remain controller decisions. The implemented public branches only reflect the currently represented `published` Program and enabled-rule states; no public route was added.
- Membership history audit events and governed Super Admin overrides are not implemented; policy guards are not a full historical-governance system.
- Rubric versions/criteria do not yet exist. The policy treats only the existing draft rubric identity as mutable and denies a represented `frozen` state.
- No routes/controllers currently invoke these policies; policy registration and direct policy coverage are complete only for the requested foundation.

## 20. Recommended Next Task

Stop for Product & Technical Controller review. The next task should be explicitly authorized and should not assume UI or route enforcement is complete. It may define the first EAIC program administration delivery path and its controller/request/route enforcement, or resolve remaining lifecycle/governance decisions first.

## 21. Verified Facts vs Assumptions

**Verified facts:** all four Batch 1 models now have registered Laravel policies; the policy methods listed above exist; the ten existing Batch 1 permissions are the only EAIC permissions referenced; the focused suite passes with 21 tests and 21 assertions; Pint and whitespace verification pass; no database change occurred.

**Assumptions kept explicit:** `program_staff` is the current literal Program Staff capability because it is used by the pre-existing factory; a Program administrator must already hold that active capability somewhere before creating another Program; `published` is the currently represented public Program state; only draft rubrics are mutable until version/freeze behavior is implemented. None of these assumptions creates an EAIC role, permission grant, route, UI, or downstream workflow.
