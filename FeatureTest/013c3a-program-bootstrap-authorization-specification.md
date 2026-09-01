# EAIC Program Bootstrap Authorization Specification

## RBAC-PROGRAM-CREATE-001

- **Actor:** Authenticated bootstrap-capable administrator with `program.create`.
- **Preconditions:** No Program or Program Membership exists.
- **Action:** Authorize Program creation through `ProgramPolicy::create`.
- **Expected result:** ALLOW.
- **Security reason:** Program creation is a global/bootstrap action with no target Program to scope. The `program.create` permission is the required authorization and no pre-existing Program Membership can be required.

## RBAC-PROGRAM-CREATE-002

- **Actor:** Authenticated bootstrap-capable administrator with `program.create` and no Program Staff membership.
- **Preconditions:** No Program exists; therefore no membership can exist.
- **Action:** Authorize creation of the first Program through `ProgramPolicy::create`.
- **Expected result:** ALLOW.
- **Security reason:** Membership in another Program is not a prerequisite for the global bootstrap action. Program scope begins only after a Program exists.

## RBAC-PROGRAM-CREATE-003

- **Actor:** Applicant without `program.create`.
- **Preconditions:** The actor has no approved global/bootstrap administrative authorization.
- **Action:** Attempt Program creation.
- **Expected result:** DENY.
- **Security reason:** Applicant identity or prospective Program visibility does not grant the singular administrative creation permission.

## RBAC-PROGRAM-CREATE-004

- **Actor:** Judge without `program.create`.
- **Preconditions:** The actor may have Program/Judge scope but has no approved global/bootstrap administrative authorization.
- **Action:** Attempt Program creation.
- **Expected result:** DENY.
- **Security reason:** Judge scope and assignment-related authority are not Program administration authority.

## RBAC-PROGRAM-CREATE-005

- **Actor:** Decision Maker without `program.create`.
- **Preconditions:** The actor may have decision authority but has no approved global/bootstrap administrative authorization.
- **Action:** Attempt Program creation.
- **Expected result:** DENY.
- **Security reason:** Decision Maker authority is distinct from Program administration and does not imply global creation authority.

## RBAC-PROGRAM-UPDATE-001

- **Actor:** Program Staff with `program.update` and an active `program_staff` membership in the target Program.
- **Preconditions:** Target Program is not archived.
- **Action:** Authorize update of that Program.
- **Expected result:** ALLOW.
- **Security reason:** The actor satisfies the permission, active membership/capability, target Program scope, and current record-state requirements.

## RBAC-PROGRAM-UPDATE-002

- **Actor:** Program Staff with `program.update` and an active `program_staff` membership in Program A.
- **Preconditions:** Target is unrelated Program B.
- **Action:** Authorize update of Program B.
- **Expected result:** DENY.
- **Security reason:** The action permission does not bypass target Program membership scope.

## RBAC-PROGRAM-PUBLISH-001

- **Actor:** Program Staff with `program.publish` and an active `program_staff` membership in the target Program.
- **Preconditions:** Target Program is `draft` and `opens_at < closes_at`.
- **Action:** Authorize publication of the target Program.
- **Expected result:** ALLOW.
- **Security reason:** The actor has the required permission and target scope, and the represented state/window policy permits the transition.

## RBAC-PROGRAM-PUBLISH-002

- **Actor:** Authenticated user without active `program_staff` membership in the target Program.
- **Preconditions:** Target Program is a valid draft; the actor may hold `program.publish` but lacks target scope.
- **Action:** Authorize publication of the target Program.
- **Expected result:** DENY.
- **Security reason:** Publication is Program-scoped. Permission alone cannot publish an unrelated Program.

## RBAC-PROGRAM-SCOPE-001

- **Actor:** Authenticated user with `program.update` but no active `program_staff` membership in the target Program.
- **Preconditions:** Target Program is not archived.
- **Action:** Directly invoke `ProgramPolicy::update` for the target Program.
- **Expected result:** DENY.
- **Security reason:** Direct policy access cannot bypass the active target Program Membership/capability requirement.
