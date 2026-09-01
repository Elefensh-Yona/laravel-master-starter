# Task 015B: Application Permission Alignment Summary

**Interaction ID:** 015B  
**Status:** COMPLETE  
**Date:** 2026-09-01  
**Problem being corrected:** The Application delivery path referenced four permissions that were not present in the managed permission catalog, creating a genuine authorization blocker. This task aligned the Application permission model with the approved singular `resource.action` convention and the existing permission architecture without introducing UI, members management, or downstream domain work.

---

## 1. Permission catalog before / after

### Before

The live permission registry was inspected and returned:

- `EXISTING: []`

for the target set:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

This was confirmed by inspection of the current seeded permission list in `database/seeders/RolePermissionSeeder.php` and route/policy references in `routes/web.php`, `app/Policies/ApplicationPolicy.php`, `app/Policies/ApplicationMemberPolicy.php`, and `app/Policies/ApplicationVersionPolicy.php`.

### After

The permission catalog source was updated to include the four canonical Application permissions in the existing seeded catalog:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

The app code now references a coherent permission set, and the update was scoped strictly to the Application permissions required by the architecture. The project does not currently contain approved EAIC role grants for these permissions, so no broad role assignment was added.

---

## 2. Permission definitions added

The existing permission catalog in `database/seeders/RolePermissionSeeder.php` was updated to include only the four requested application permissions, inserted alongside the existing program and eligibility permissions.

Added definitions:

- `application.view`
- `application.create`
- `application.update`
- `application.submit`

Explicit rule followed:

- `application.revise` was not introduced as a separate permission.
- Revision continues to use the existing `application.update` action.

---

## 3. Route alignment

Updated only the necessary Application routes in `routes/web.php`:

- create → `permission:application.create`
- update/edit → `permission:application.update`
- revise → `permission:application.update`
- submit → `permission:application.submit`

This keeps the route-level permission pattern aligned with the approved model while preserving policy authorization and ownership checks.

---

## 4. Policy alignment

Updated `app/Policies/ApplicationPolicy.php` to use the authoritative names consistently.

### Current policy behavior

- `view()`
  - still allows owner and approved visibility, while retaining permission-based access when applicable
- `create()`
  - checks `application.create`
- `update()`
  - checks `application.update` and the existing ownership/scope logic
- `submit()`
  - checks `application.submit` and ownership, plus the draft-state guard

Important note:

- The owner is still the canonical owner via `primary_owner_id`.
- No automatic ApplicationMember creation was done in this task.

---

## 5. Submission authorization

The Application submission route is now aligned with the approved permission architecture:

- authentication: yes, via `auth` + `verified`
- route permission: `application.submit`
- policy: `ApplicationPolicy::submit()`
- ownership: required (`primary_owner_id === user id`)
- draft-state validation: required in the controller before state change

This preserves the required lifecycle distinction:

- update is for draft editing and revision capability
- submit is a real transition to an immutable submitted state

No submitted-version mutation is allowed.

---

## 6. Role-grant status

This task did not invent or broadly assign EAIC roles.

The project currently follows the existing starter role architecture and the approved permission architecture, which keeps roles/capabilities separate from permissions.

### Current grant status

- permission definitions created in the source catalog: yes
- permission existence in the catalog: yes, at code-level seeding source
- assignment to Manager/Staff: not silently performed
- new EAIC database roles created: no

**OWNER DECISION REQUIRED** for any precise role-to-permission grant decisions beyond the current repository’s existing architecture.

---

## 7. Ownership model status

This task preserved the current ownership model and did not change it.

- Canonical owner remains `primary_owner_id`
- No automatic owner insertion into `application_member` was added
- No involvement in Application member management was introduced

This is intentionally kept separate from the permission alignment task.

---

## 8. FeatureTest specifications

The following tests were documented in `FeatureTest/015b-application-permission-catalog-specification.md` and intentionally were not executed:

- APP-RBAC-001: catalog contains four canonical permissions
- APP-RBAC-002: no duplicate Application permission is created
- APP-RBAC-003: `application.create` protects creation
- APP-RBAC-004: `application.view` supports permission-based viewing where applicable
- APP-RBAC-005: `application.update` protects draft editing/revision
- APP-RBAC-006: `application.submit` protects submission
- APP-RBAC-007: `application.revise` permission does not exist
- APP-RBAC-008: permission alone does not bypass ownership/program scope

Each spec records actor, preconditions, action, expected result, and security reason.

---

## 9. ManualTest updates

`ManualTest/ManualTest_03_Application_Delivery.md` was updated to include explicit manual checks for:

- applicant with submission authority
- owner without submission authority
- unauthorized direct submission
- draft editing
- revision
- permission boundaries

All manual scenarios remain marked:

- `NOT RUN`

---

## 10. Test execution status

**Test execution status: NOT RUN BY DESIGN**

This task followed the no-Pest/no-regression instruction and did not run broad application tests. Only lightweight static inspection and targeted route verification were used.

---

## 11. Lightweight verification

Allowed lightweight verification was performed:

- `git status --short --branch` recovery check
- read-only inspection of permission definitions and route/policy references
- route listing via `php artisan route:list --name=applications`
- `git diff --check` on the touched files
- static inspection of the permission catalog and auth code

### Verified observations

- The route list still includes the Application routes.
- The permission catalog source now includes the four canonical permissions.
- The submission route is aligned to `application.submit`.
- No broad tests were run, per instruction.

### Result not claimed

The Application authorization model was not end-to-end executed against live browser or seeded user roles in this task. That would require a runtime test phase beyond the clarification / alignment work allowed here.

---

## 12. Database changes

### Source-level database catalog change

The permission seed list in `database/seeders/RolePermissionSeeder.php` was updated.

### Not performed

- no migration
- no schema mutation
- no seeding execution
- no live database write
- no permission assignment to roles or users

The actual permission registry was not mutated during this task beyond changing the source seeder content for the next approved seed run.

---

## 13. Files created

- `FeatureTest/015b-application-permission-catalog-specification.md`
- `AI-AGENT-HANDOFFS/015b-application-permission-alignment-summary.md`

---

## 14. Files modified

- `database/seeders/RolePermissionSeeder.php`
- `app/Policies/ApplicationPolicy.php`
- `routes/web.php`
- `ManualTest/ManualTest_03_Application_Delivery.md`

---

## 15. Known risks

- Permission assignments to actual system roles are not yet approved by the current decision record.
- Permission-only checks are not sufficient without ownership and program scope policy enforcement.
- UI and ApplicationMember management remain separate tasks and were intentionally not included here.
- The runtime permission registry must be refreshed by the project’s normal seed process before testing a real role assignment path.

---

## 16. OWNER DECISION REQUIRED items

1. Which existing role(s), if any, should receive `application.view`, `application.create`, `application.update`, and `application.submit` after the catalog is approved?
2. Should the application owner automatically also appear as an ApplicationMember for future role/authorization enforcement, or should ownership remain wholly `primary_owner_id`-based?
3. Should submission authority remain a distinct permission in the baseline EAIC catalog, or should it be rolled into a broader role capability after product approval?

---

## 17. Recommended next task

Recommended next task: a short approval pass for the Application permission catalog and role grant path, then a controlled runtime seeding/role assignment review before the next Application delivery phase.

This is the minimal next governance step, because the permission source is now coherent but grant decisions remain external to the current repository architecture.

---

## 18. Verified Facts vs Assumptions

### Verified facts

- The Application permission names were absent from the live permission registry and from the seeded catalog before this task.
- The app route and policy code referenced those names.
- The source permission catalog has been updated to include the four canonical permissions.
- `application.revise` was not added as a new permission.
- `application.submit` is now required in the route and policy path.
- The route list still contains the Application routes after the alignment update.

### Assumptions

- The current project’s role model will later determine which actors receive the Application permissions.
- The product owner will decide whether an Application owner should also be backed by an `ApplicationMember` row.
- Future runtime seeding and role assignment are subject to the project’s governance approval process.

---

## Final status

**Permission catalog alignment:** complete at source-code level for the canonical four names  
**Runtime seeding status:** not executed in this task  
**Role grant approval status:** OWNER DECISION REQUIRED  
**Stop condition:** reached; no further Application UI or member-management work was performed.
