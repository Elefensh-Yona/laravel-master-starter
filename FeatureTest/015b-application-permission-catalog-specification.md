# Application Permission Catalog Specification

## APP-RBAC-001

- **Test ID:** APP-RBAC-001
- **Title:** Application permission catalog contains the four canonical permissions
- **Actor:** System-level admin/seed verification actor
- **Preconditions:** Permission registry is loaded from database seeding
- **Action:** Inspect the seeded permission catalog for `application.view`, `application.create`, `application.update`, `application.submit`
- **Expected result:** All four canonical permissions exist exactly once in the catalog with guard `web`
- **Security reason:** The permission model must be internally coherent before route or policy enforcement depends on it

## APP-RBAC-002

- **Test ID:** APP-RBAC-002
- **Title:** No duplicate Application permission is created
- **Actor:** System-level admin/seed verification actor
- **Preconditions:** Permission registry has been seeded at least once
- **Action:** Check duplicate names for the canonical Application permission set
- **Expected result:** Each permission name appears once only; there are no duplicate entries or duplicate guard rows
- **Security reason:** Duplicate permission rows would create unpredictable authorization results

## APP-RBAC-003

- **Test ID:** APP-RBAC-003
- **Title:** `application.create` protects creation
- **Actor:** Authenticated user without `application.create`
- **Preconditions:** User has authenticated session but lacks `application.create`
- **Action:** Attempt to open or submit `POST /applications`
- **Expected result:** Request denied by permission gate or policy; no Application created
- **Security reason:** Creation is a distinct action and must be protected separately from viewing or updating

## APP-RBAC-004

- **Test ID:** APP-RBAC-004
- **Title:** `application.view` supports permission-based viewing where applicable
- **Actor:** Authenticated user with `application.view` permission and an approved program or owned record
- **Preconditions:** The actor is authorized within policy scope and the record is in an allowed visibility state
- **Action:** Query application show details
- **Expected result:** Allowed when the visibility/policy rules permit; denied when out of scope or not owned
- **Security reason:** Viewing is distinct from creation and update and must remain policy-guarded

## APP-RBAC-005

- **Test ID:** APP-RBAC-005
- **Title:** `application.update` protects draft editing and revision workflows
- **Actor:** Authenticated user with `application.update` permission and valid ownership/scope
- **Preconditions:** Application is in draft state and current user is the primary owner or otherwise approved
- **Action:** Update draft content or revise a draft revision flow
- **Expected result:** Authorization succeeds only when `application.update` is present and ownership/scope checks permit it
- **Security reason:** Draft editing and revision control are distinct from submission and must use the update permission

## APP-RBAC-006

- **Test ID:** APP-RBAC-006
- **Title:** `application.submit` protects submission
- **Actor:** Authenticated owner or approved actor
- **Preconditions:** Application exists, current version is draft, actor is the primary owner
- **Action:** Submit the draft via `POST /applications/{application}/submit`
- **Expected result:** Allowed only with both `application.submit` and ownership/scope checks; rejected if missing permission or not in draft state
- **Security reason:** Submission is a consequential transition to immutable historical state and must not be collapsed into update permission

## APP-RBAC-007

- **Test ID:** APP-RBAC-007
- **Title:** `application.revise` permission does not exist
- **Actor:** System admin or auth reviewer
- **Preconditions:** Canonical permission registry has been seeded
- **Action:** Check for the literal permission name `application.revise`
- **Expected result:** No permission row exists; revision uses `application.update`
- **Security reason:** The permission model must stay minimal and consistent with the approved singular `resource.action` naming pattern

## APP-RBAC-008

- **Test ID:** APP-RBAC-008
- **Title:** Permission alone does not bypass ownership/program scope
- **Actor:** User with a valid permission but no ownership or program scope
- **Preconditions:** Permission exists, but the user is not the owner and not in the permitted scope
- **Action:** Attempt direct access to another user’s application or submission action
- **Expected result:** Denied by policy and scope checks even if `application.*` permission is present
- **Security reason:** Authorization is layered; record scope and ownership remain enforceable regardless of a broad capability grant
