# Task 022A: RBAC Reconciliation Summary

**Interaction ID:** 022A  
**Date:** 2026-09-01  
**Status:** COMPLETE - conflict documented, no architecture redesign  
**Decision outcome:** C - Architecture mismatch

## 1. Recovery State

Started from Handoff 022. The existing local fixture contains QA Program Staff with Program A-only active `program_staff` membership, Program A/B fixture Applications, and no Validation or Screening records. Git had prior Task 019-022 changes; no historical change was reverted.

## 2. Authority Consulted

Reviewed the EAIC decisions, Blueprint, Governance Contract, RBAC Scope Matrix, Database Lifecycle/Final Schema/Pre-Migration records, Project Requirements/Roadmap, relevant FeatureTest/ManualTest specifications, and handoffs 013C1, 013C2, 013C3A, 015B, 015D, 020, 021, and 022.

## 3. Mismatch Investigated

The QA Program Staff fixture had Eligibility and Program grants but lacked `application.view`. The Eligibility/Screening GET routes require `permission:application.view`, preventing the actor from reaching application context needed for the workflow.

The authoritative RBAC matrix lists `application.view` as conditionally allowed for Program Staff, subject to Program membership and record policy. It also requires Program Staff scope for Eligibility validation and human screening. Therefore the missing QA fixture grant is real and the smallest grant is `application.view` only.

## 4. Permission and Scope Analysis

- `application.view`: required by the Application-related GET middleware; added to QA Program Staff fixture only.
- `eligibility.validate`: remains required for objective validation actions.
- `eligibility.screen`: remains required for human screening actions.
- Active Program A `program_staff` membership remains separately required by Validation/Screening controllers and policies.
- No Application create/update/submit grant, Program B membership, new permission, or new role was added.

## 5. Policy Analysis and Architecture Conflict

The narrow fixture omission was corrected, but live verification found a deeper conflict:

- `ApplicationPolicy::view()` returns true for **any submitted application**, independently of actor Program membership.
- QA Program Staff now has `application.view` and Program A-only scope.
- The policy returns true for both Program A Application B and Program B Application C because both are submitted.

This conflicts with the authoritative matrix: Program Staff record scope is applications within authorized programs, and cross-program Staff access is DENY.

**Outcome C:** Current Application read policy/middleware behavior is inconsistent with governing Program Staff scope for submitted cross-program Applications. Per Task 022A instructions, no immediate policy/route redesign was performed.

## 6. Factory Analysis

**Factory change not required.** UserFactory, ProgramMembershipFactory, and Application factories intentionally create generic/random reusable model instances. They do not and should not encode Task 022A QA roles, permissions, or Program scope. Deterministic QA grants remain in ManualQaFixtureSeeder.

## 7. Exact Change Made

Modified `database/seeders/ManualQaFixtureSeeder.php` only:

- Added existing canonical `application.view` to QA Program Staff's direct permissions.

Ran the existing idempotent ManualQaFixtureSeeder to materialize that grant. No other user, role, capability, permission registry entry, membership, Application, or lifecycle record was changed.

## 8. Runtime Verification

- QA Program Staff has `application.view`, `eligibility.view`, `eligibility.validate`, `eligibility.screen`, existing Program/Rubric grants, and active Program A `program_staff` membership.
- `application.view` exists exactly once; `application.revise` remains absent.
- QA Applicant, Judge, and Decision Maker have no direct permissions or roles from the fixture.
- Eligibility/Screening routes remain registered.
- ApplicationPolicy returns true for both fixture submitted Applications, including Program B out-of-scope record: confirmed conflict.
- Validation and Screening counts remain zero.

## 9. Browser Smoke Check

Not performed. The Task 022A architecture-level cross-program read conflict makes a successful browser check insufficient evidence of compliant access control. No browser outcome is claimed.

## 10. Documentation

- Created `FeatureTest/022a-rbac-reconciliation-specification.md`; NOT EXECUTED.
- Created `ManualTest/ManualTest_11_RBAC_Eligibility_Screening_Access.md`; all scenarios NOT RUN.

## 11. Verification and Database Changes

**Test execution status:** NOT RUN BY DESIGN. No Pest, PHPUnit, or broad tests ran.

Focused verification performed: source, factories, route middleware, policies, live permission/capability/membership queries, ApplicationPolicy decisions, route listing, PHP lint, fixture seed, and `git diff --check`.

**Database change:** The existing QA Program Staff record received the one existing direct `application.view` permission through the governed idempotent fixture. No destructive operation occurred.

## 12. Files

### Created

- `FeatureTest/022a-rbac-reconciliation-specification.md`
- `ManualTest/ManualTest_11_RBAC_Eligibility_Screening_Access.md`
- `AI-AGENT-HANDOFFS/022a-eaic-rbac-reconciliation-summary.md`

### Modified

- `database/seeders/ManualQaFixtureSeeder.php`

### Intentionally not modified

- Generic factories, Application/Validation/Screening policies, controllers, routes, models, migrations, role catalog, Program scope architecture, UI, and historical documentation.

## 13. Owner Decisions, Issues, and Risks

### OWNER DECISION REQUIRED

- Resolve the ApplicationPolicy cross-program submitted-record visibility conflict for Program Staff: policy/middleware must satisfy both Application visibility and Program scope governance before it is changed.
- Existing unresolved Screening messaging, taxonomy, validation-linkage, downstream lifecycle, and persisted role-grant decisions remain unchanged.

### Known Issue

- Program Staff can currently read submitted Program B application context after receiving the required `application.view` grant, despite lacking Program B scope.

### Known Risk

- Eligibility/Screening UI may display out-of-scope submitted Application context until the architecture conflict is resolved. Existing mutation paths still separately enforce Program Staff scope.

## 14. Recommended Next Task

**RECOMMENDED - NOT YET APPROVED:** Product and Technical Controller should approve the smallest coherent Application read-policy reconciliation for Program Staff scope, with focused acceptance specifications, before browser smoke QA. Do not proceed to Judge Assignment or later lifecycle work.

## 15. Verified Facts vs Assumptions

### Verified Facts

- Governance conditionally permits Program Staff `application.view` within Program/record scope.
- The fixture omitted that permission; it is now granted only to QA Program Staff.
- Eligibility/Screening actions still require their own permission plus active Program Staff scope.
- Current ApplicationPolicy allows any submitted record, including Program B for Program A-only Staff.
- Factories are generic and unchanged.

### Assumptions Avoided

- No claim that adding `application.view` alone satisfies cross-program security.
- No claim that browser access has been verified or that the workflow is fully compliant.
- No new role, permission, state, or lifecycle behavior was inferred.

## 16. Stop Condition

- [x] RBAC layers reconciled against governance and live state.
- [x] Minimum fixture permission omission corrected.
- [x] Architecture mismatch identified and documented.
- [x] FeatureTest, ManualTest_11, and Handoff 022A created.
- [x] No unrelated lifecycle implementation started.

Await Product and Technical Controller review.
