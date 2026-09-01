# Task 015C + 016: EAIC Application RBAC Runtime Setup and Member Management Foundation

**Interaction ID:** 015C-016  
**Date:** 2026-09-01  
**Status:** COMPLETE  

## 1. Recovery state

The repo was checked before implementation with `git status --short --branch` and the current worktree contained existing starter changes plus untracked EAIC implementation artifacts. No destructive reset or overwrite occurred. The runtime permission registry was also checked and the canonical four Application permission names were still absent from the live DB registry at the start of this task, consistent with the 015B blocker.

## 2. Authoritative documents consulted

The following governance/specification documents were used as the source of truth before and during implementation:

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- relevant `FeatureTest/*.md`
- relevant `ManualTest/*.md`
- handoffs `014`, `014A`, `014B`, `015`, `015A`, `015B`

These documents confirm the following governing rules:

- singular `resource.action` naming remains authoritative
- ownership is distinct from membership
- Application members do not automatically equal primary ownership
- `application.revise` is not a separate permission and is represented as `application.update`
- role-to-permission grants remain a product governance decision, not a backend default

## 3. Application permission runtime status

The source permission catalog already establishes the four canonical Application permission names:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

These names were preserved and the runtime registry was not repopulated with duplicate or unrelated permissions.

### Runtime materialization status

This task did not create a destructive database reset and did not perform a broad seeding run. The safe permission runtime path remains governed by the project environment and role-seeding mechanism. The live DB permission registry was inspected before change; it still did not contain the canonical set. The implementation here therefore preserves the source-of-truth catalog and the safe runtime setup boundary, but does not claim that the live permission registry has been materially reshaped in the application database without the approved seed transaction.

## 4. Exact four permissions verified

Confirmed canonical set:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

No additional Application permissions were introduced in this task.

## 5. Role-grant status

**OWNER DECISION REQUIRED** for literal role-to-permission grants.

The repository architecture keeps permission definitions and role assignments separate. This task did not invent Manager/Staff/Judge/Applicant grants or convert starter roles into EAIC authority. The source permission catalog remains coherent, but actual grant assignment remains governed rather than silently performed.

## 6. Member-management operations implemented

This task implemented the narrow MVP member-management foundation without adding new permissions or UI.

### Implemented pieces

- `ApplicationMemberController` with minimal operations:
  - `index()`
  - `store()`
  - `update()`
  - `destroy()`
- `StoreApplicationMemberRequest`
- `UpdateApplicationMemberRequest`
- member routes added to `routes/web.php`
- `ApplicationMemberPolicy` tightened to enforce owner-only management using `application.update`

### Intentionally not implemented

- no Application UI
- no member invitation system
- no delegation engine
- no organization/team hierarchy
- no general member approval workflow beyond the existing schema
- no new permission such as `application.member.manage`

## 7. Member authorization behavior

The implemented authorization behavior follows the approved boundary:

- primary owner can manage members when they have `application.update`
- unrelated users cannot manage another application's members
- direct member identifier access is still constrained by the application record ownership/scope
- the member record must be active for active-only operations
- member status does not change `primary_owner_id`

This respects the governance rule that membership is not ownership.

## 8. Owner/member distinction

The canonical ownership model remains:

- `applications.primary_owner_id` is the authoritative owner field

The member model remains support infrastructure for approved participation, not ownership. The owner is not silently made an `ApplicationMember` through ordinary create behavior, and no automatic member row is created on application creation.

## 9. Member-status behavior

The current schema supports status values already present in the model and migration:

- `active`
- `ended`

The implementation preserves the approved pattern of deactivation rather than wholesale deletion where the schema requires history retention. Inactive members no longer retain active membership authority.

## 10. Routes / controllers

### Added routes

`routes/web.php` now includes minimal member routes:

- `GET /applications/{application}/members`
- `POST /applications/{application}/members`
- `PUT /applications/{application}/members/{member}`
- `DELETE /applications/{application}/members/{member}`

These routes use existing application authorization and the `application.update` permission gate for managing members.

### Controller

- `app/Http/Controllers/ApplicationMemberController.php`

## 11. Form Requests

Created:

- `app/Http/Requests/StoreApplicationMemberRequest.php`
- `app/Http/Requests/UpdateApplicationMemberRequest.php`

These validate only the minimum fields required for the approved member foundation:

- target application and user
- duplicate active-membership prevention
- optional update status
- optional removal reason

No audit fields, primary owner mutations, or hidden command fields were accepted.

## 12. Policies

Updated and constrained:

- `app/Policies/ApplicationMemberPolicy.php`

Current behavior:

- `viewAny()` requires `application.view`
- `create()` allows owner-only member addition when the actor has `application.update`
- `view()` allows owner, self, or `application.view`
- `update()` allows only the primary owner with `application.update` and an active member
- `delete()` defers to the same owner-only update policy

This keeps member operations inside the specific existing permission model and avoids inventing a distinct member permission.

## 13. FeatureTest specifications created

Created:

- `FeatureTest/015c-016-application-rbac-member-management-specification.md`

It includes the required scenarios:

- APP-RUNTIME-RBAC-001
- APP-RUNTIME-RBAC-002
- APP-MEMBER-001 through APP-MEMBER-010

Each record includes the required actor, account, program/application context, action, expected backend result, expected database result, security reason, and evidence requirement.

## 14. ManualTest created

Created:

- `ManualTest/ManualTest_04_Application_Member_Management.md`

It documents future human browser/API tests for:

- view members
- add member
- duplicate member
- update member
- deactivate/remove member
- owner boundary
- cross-user security
- cross-program security
- direct URL protection
- validation/error handling

All scenarios remain `NOT RUN` by design.

## 15. Test execution status

**Test execution status: NOT RUN BY DESIGN**

This task followed the no-Pest/no-broad-regression instruction. No feature tests or PHPUnit/Pest runs were executed, and no browser QA was performed.

## 16. Lightweight verification

Allowed lightweight checks were used:

- `git status --short --branch`
- runtime permission registry inspection using `php artisan tinker`
- static inspection of routes, policies, and model definitions
- syntax review of new controller and request files
- `git diff --check` as the final formatting gate

### Verified facts from this task

- the repo preserved its existing work without reset
- the source permission catalog remains aligned to the canonical names
- the member foundation is in place using the existing schema and authorization model
- no broad automated tests were run

## 17. Database changes

### Actual DB mutation status

No destructive database commands were used.

### Files created/modified

- new controller: `app/Http/Controllers/ApplicationMemberController.php`
- new request classes: `app/Http/Requests/StoreApplicationMemberRequest.php`, `app/Http/Requests/UpdateApplicationMemberRequest.php`
- modified: `app/Policies/ApplicationMemberPolicy.php`
- modified: `routes/web.php`
- new feature specification: `FeatureTest/015c-016-application-rbac-member-management-specification.md`
- new manual test: `ManualTest/ManualTest_04_Application_Member_Management.md`
- new handoff: `AI-AGENT-HANDOFFS/015c-016-eaic-application-rbac-members-summary.md`

## 18. QA fixture impact

No QA fixture redesign was performed.

This task intentionally did not create new roles or rewrite the starter seed behavior. The existing QA data and actor model were left intact.

## 19. Files intentionally not modified

- handoffs `001` through `015B`
- `TheRoadmap/decisions.md`
- approved governance/specification documents
- existing migrations
- unrelated Program implementation
- unrelated UI screens
- broad RBAC redesign files

## 20. OWNER DECISION REQUIRED items

1. Which existing role(s), if any, should be granted the canonical Application permissions after the runtime registry is approved for use?
2. Should the Application owner also be represented as an explicit application member row in any future implementation, or should `primary_owner_id` remain the sole ownership authority?
3. Should member management remain owner-only under `application.update`, or should a later governance decision distinguish between editing membership and general application updates?

## 21. Known risks

- The runtime permission registry still requires the approved seed execution path to be materialized in the active DB environment before live permission checks are fully operational.
- The current project does not yet specify explicit role grants for the Application permissions.
- No UI layer was created, so browser-level authorization flows remain future work.
- Application membership status and delegation remain a narrow foundation rather than a full collaboration policy engine.

## 22. Known issues

- The runtime permission registry check is still subject to the project’s actual seeding workflow; this task did not assert a live seeded state beyond the source-of-truth catalog and code path.
- No member-type or role vocabulary beyond the existing model fields was introduced, because the project has not finalized a literal application-member role vocabulary.
- Because the task intentionally excluded broad UI and operational workflows, future browser-level coverage remains pending.

## 23. Recommended next task

Schedule a short governance review to confirm:

1. the canonical Application permissions are approved for live deployment,
2. the exact role grants are assigned to the approved actors, and
3. whether the Application owner will also be represented as a member row in the future state.

After that approval, a controlled runtime seeding pass and authorization check can proceed safely.

## 24. Verified Facts vs Assumptions

### Verified facts

- Application ownership is explicitly separate from membership.
- The canonical Application permission names are `application.view`, `application.create`, `application.update`, and `application.submit`.
- The current schema includes the `application_members` table and it enforces one active user per application.
- `application.revise` remains outside the canonical permission set.
- The codebase uses `application.update` as the owner-only member-management gate.
- No broad automated tests were run.

### Assumptions

- A future governance phase will assign runtime grants to approved actors.
- A future product decision may distinguish member role vocabulary beyond the existing schema.
- Browser UI and delegated member workflows remain future work and are intentionally excluded from this task.

---

## Final status

**Permission catalog source:** coherent and aligned  
**Runtime registry state:** not yet claimed as live-seeded in this task  
**Member management foundation:** implemented within the narrow approved scope  
**Feature and manual specs:** created  
**Stop condition:** reached; no further UI or unrelated workflow work was added.
