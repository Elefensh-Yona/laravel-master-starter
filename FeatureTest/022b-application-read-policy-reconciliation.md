# Task 022B: Application Read Policy Reconciliation

**Status:** Specification only. NOT EXECUTED.  
**Scope:** Application read authorization for Program Staff, owners, and Super Admin.

## RBAC-READ-001: Program Staff views in-scope Program A Application

- **Actor/account:** QA Program Staff.
- **Program/Application:** Program A / `QA-APPLICATION-B-SUBMITTED`.
- **Permissions/scope:** `application.view`; active Program A `program_staff` membership.
- **Preconditions:** Fixture seeded; submitted Application B exists.
- **Access attempt:** Application show/index route and policy check.
- **Expected result:** Allow.
- **Security reason:** Staff must access authorized program context for workflow operations.
- **Evidence:** Gate decision and scoped index result.
- **PASS:** Application B is accessible and listed.
- **FAIL:** In-scope Staff is denied.

## RBAC-READ-002: Program Staff cannot view Program B submitted Application

- **Actor/account:** QA Program Staff.
- **Program/Application:** Program B / `QA-APPLICATION-C-PROGRAM-B-SCOPE`.
- **Permissions/scope:** `application.view`; no Program B membership.
- **Preconditions:** Fixture seeded.
- **Access attempt:** Application policy/show route.
- **Expected result:** Deny.
- **Security reason:** Submitted state cannot bypass Program Staff scope.
- **Evidence:** Gate decision and HTTP forbidden response.
- **PASS:** Program B record is denied.
- **FAIL:** Submitted status permits cross-program access.

## RBAC-READ-003: Direct identifier cannot bypass scope

- **Actor/account:** QA Program Staff.
- **Program/Application:** Program B / Application C.
- **Permissions/scope:** `application.view`; no Program B membership.
- **Preconditions:** Known Application C identifier.
- **Access attempt:** Direct GET to Application C and related Eligibility/Screening GET URLs.
- **Expected result:** Deny at Application policy after route middleware.
- **Security reason:** Identifier knowledge must not disclose cross-program records.
- **Evidence:** HTTP 403 and absence of record payload.
- **PASS:** All direct accesses are rejected.
- **FAIL:** Any Program B context/data is rendered.

## RBAC-READ-004: Owner visibility is preserved

- **Actor/account:** QA Applicant.
- **Program/Application:** Owned Program A/B fixtures.
- **Permissions/scope:** No `application.view` required for ownership exception.
- **Preconditions:** Primary owner relationship exists.
- **Access attempt:** Application policy/show route.
- **Expected result:** Allow for owned records.
- **Security reason:** Ownership is an approved visibility path distinct from Staff scope.
- **Evidence:** Gate decisions.
- **PASS:** Owner reads owned Applications.
- **FAIL:** Policy requires staff membership for owner access.

## RBAC-READ-005: application.view is required for non-owner scoped reads

- **Actor/account:** Non-owner active Program member without `application.view`.
- **Program/Application:** Target Program A Application.
- **Permissions/scope:** Active membership only.
- **Preconditions:** Fixture or isolated test identity.
- **Access attempt:** Application policy/show route.
- **Expected result:** Deny.
- **Security reason:** Membership alone must not create broad Application visibility.
- **Evidence:** Permission and Gate result.
- **PASS:** Missing permission denies access.
- **FAIL:** Membership alone allows view.

## RBAC-READ-006: Eligibility/Screening actions stay separately protected

- **Actor/account:** QA Program Staff.
- **Program/Application:** Program A Application B and Program B Application C.
- **Permissions/scope:** `application.view`, `eligibility.validate`, `eligibility.screen`, Program A staff scope only.
- **Preconditions:** Submitted fixtures.
- **Access attempt:** Validation/Screening POST actions.
- **Expected result:** Program A requires the specific action permission and scope; Program B is denied.
- **Security reason:** Read authority does not replace consequential action authority.
- **Evidence:** Controller/policy outcome and record counts.
- **PASS:** Only permitted scoped action succeeds.
- **FAIL:** application.view enables out-of-scope mutation.

## RBAC-READ-007: No new permission or role is needed

- **Actor/account:** Permission/role registry.
- **Program/Application:** N/A.
- **Permissions/scope:** Existing `application.view` and ProgramMembership.
- **Preconditions:** Reconciliation change applied.
- **Access attempt:** Inspect role and permission records.
- **Expected result:** No new permission or role is created.
- **Security reason:** Correctness comes from existing layered authorization.
- **Evidence:** Registry count/diff.
- **PASS:** Existing catalog only.
- **FAIL:** New RBAC primitive exists.

## RBAC-READ-008: Super Admin retains system-level visibility

- **Actor/account:** `admin@example.com`.
- **Program/Application:** Program B / Application C.
- **Permissions/scope:** Existing Super Admin role and Gate before callback.
- **Preconditions:** Super Admin fixture exists.
- **Access attempt:** Gate/Application route.
- **Expected result:** Allow.
- **Security reason:** Approved system administration behavior is preserved.
- **Evidence:** Gate decision.
- **PASS:** Super Admin reads record.
- **FAIL:** Scoped policy breaks Gate before behavior.

## RBAC-READ-009: Other QA actors gain no unintended read access

- **Actor/account:** QA Applicant, Judge, Decision Maker.
- **Program/Application:** Non-owned Program fixtures.
- **Permissions/scope:** No fixture direct `application.view` grants or roles.
- **Preconditions:** Fixture seeded.
- **Access attempt:** Application policy/show route.
- **Expected result:** Applicant retains only ownership visibility; Judge/Decision Maker are denied unless future approved assignment/scope exists.
- **Security reason:** Actor existence does not grant Application-wide visibility.
- **Evidence:** Gate decisions and permission relations.
- **PASS:** No unintended non-owner visibility.
- **FAIL:** Any actor reads a record without approved path.
