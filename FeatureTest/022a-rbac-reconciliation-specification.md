# Task 022A: RBAC Reconciliation Specification

**Status:** Specification only. NOT EXECUTED.  
**Decision outcome:** C - Architecture mismatch documented; no policy/route redesign performed.

## RBAC-RECON-001: Program Staff has minimum Application viewing authority

- **Actor/account:** `qa-program-staff@example.com`.
- **Preconditions:** QA fixture has run; actor has `application.view`, `eligibility.view`, `eligibility.validate`, `eligibility.screen`, and active Program A `program_staff` membership.
- **Exact action:** Request Program A Application context and Eligibility/Screening index routes.
- **Expected result:** Route permission middleware permits the application-context prerequisite.
- **Security reason:** Program Staff must be able to reach context needed for approved Eligibility/Screening work.
- **Evidence requirement:** Direct permission query and successful middleware passage.
- **PASS:** Exact single `application.view` direct grant exists for the fixture actor.
- **FAIL:** Required view permission is missing or broad extra Application action grants exist.

## RBAC-RECON-002: Application viewing does not bypass Program scope

- **Actor/account:** QA Program Staff.
- **Preconditions:** Program A-only active staff membership; Program B submitted fixture Application exists.
- **Exact action:** Request Program B Application and Eligibility/Screening context.
- **Expected result:** **Architecture acceptance expectation:** deny because Program B scope is absent.
- **Security reason:** `application.view` must not grant cross-program Staff visibility.
- **Evidence requirement:** ApplicationPolicy decision and HTTP response.
- **PASS:** Program B read/action is denied.
- **FAIL:** Submitted-state visibility bypasses Program Staff scope.

## RBAC-RECON-003: Eligibility validation requires permission and scope

- **Actor/account:** QA Program Staff and an out-of-scope actor.
- **Preconditions:** Submitted fixtures in Program A and Program B.
- **Exact action:** POST Eligibility validation for each program.
- **Expected result:** Program A succeeds only with `eligibility.validate` plus active Program A staff scope; Program B is denied.
- **Security reason:** Objective validation remains program-scoped and cannot become a final human decision.
- **Evidence requirement:** Route/policy/controller outcome and data count.
- **PASS:** Both permission and scope are required.
- **FAIL:** Permission alone allows Program B validation.

## RBAC-RECON-004: Screening requires permission and scope

- **Actor/account:** QA Program Staff and an out-of-scope actor.
- **Preconditions:** Submitted fixtures in Program A and Program B.
- **Exact action:** POST/PUT Screening actions.
- **Expected result:** Program A actions require `eligibility.screen` and active Program A staff scope; Program B actions are denied.
- **Security reason:** Human eligibility outcomes are consequential and program-bound.
- **Evidence requirement:** Controller/policy outcome and Screening count.
- **PASS:** Scope and permission both constrain mutations.
- **FAIL:** Out-of-scope actor can create or complete Screening.

## RBAC-RECON-005: Applicant lacks Staff Screening authority

- **Actor/account:** `qa-applicant@example.com`.
- **Preconditions:** Fixture seeded.
- **Exact action:** Attempt Screening action route.
- **Expected result:** Denied; no Staff direct grants/role exist.
- **Security reason:** Applicant ownership is not Staff authority.
- **Evidence requirement:** Direct-permission query, HTTP result, record count.
- **PASS:** No Screening mutation succeeds.
- **FAIL:** Applicant can screen.

## RBAC-RECON-006: Judge lacks Staff Screening authority

- **Actor/account:** `qa-judge@example.com`.
- **Preconditions:** Fixture seeded; no approved Judge assignment implementation exists.
- **Exact action:** Attempt Screening action route.
- **Expected result:** Denied; no direct grants/role exist.
- **Security reason:** Judge and Program Staff are separate authorities.
- **Evidence requirement:** Direct-permission query and HTTP result.
- **PASS:** No Staff screening authority.
- **FAIL:** Judge can screen without explicit approval.

## RBAC-RECON-007: Decision Maker lacks unauthorized global permission

- **Actor/account:** `qa-decision-maker@example.com`.
- **Preconditions:** Fixture seeded.
- **Exact action:** Inspect roles/direct permissions and attempt Staff action.
- **Expected result:** No fixture-assigned global permissions or Staff authority.
- **Security reason:** Decision Maker authority is separate and program-scoped.
- **Evidence requirement:** Role/permission query and action response.
- **PASS:** Empty direct grants and denied Staff action.
- **FAIL:** Global Staff access is present.

## RBAC-RECON-008: Factories remain generic

- **Actor/account:** Factory definitions.
- **Preconditions:** Current source inspection.
- **Exact action:** Inspect UserFactory, ProgramMembershipFactory, and Application factories.
- **Expected result:** Factories create reusable random model data; QA-specific grants/references stay in ManualQaFixtureSeeder.
- **Security reason:** Test factories must not encode local QA identity/authorization assumptions.
- **Evidence requirement:** Source review.
- **PASS:** No QA-specific role, permission, Program, or fixed identity added to generic factories.
- **FAIL:** Generic factories are polluted with Task 022A QA grants.
