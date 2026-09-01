# Task 022B: Application Read Policy Reconciliation Summary

**Interaction ID:** 022B  
**Date:** 2026-09-01  
**Status:** COMPLETE  
**Test execution status:** NOT RUN BY DESIGN

## 1. Recovery State and Authority

Started from Handoff 022A. Reviewed the required EAIC governance/contract/roadmap documents, relevant FeatureTest/ManualTest specifications, and handoffs 020, 021, 022, and 022A. The existing QA fixture supplies Program A/B Applications and Program A-only QA Program Staff scope.

## 2. Original Conflict

After Task 022A added the required existing `application.view` fixture permission, `ApplicationPolicy::view()` still allowed any submitted Application. Consequently Program A-scoped Staff could read submitted Program B Application C. This contradicted the governing RBAC matrix, which limits Program Staff record scope to authorized programs and explicitly denies cross-program Staff access.

## 3. Actor Visibility Matrix and Final Policy Decision

| Actor/path | Final behavior |
|---|---|
| Super Admin | Allowed through existing Gate before behavior |
| Application owner | Allowed for own Application without Staff scope |
| Non-owner Program Staff | Requires `application.view` and active membership in target Program |
| Applicant non-owner | Denied without another approved visibility path |
| Judge/Decision Maker | Denied unless future approved assignment/scope behavior is implemented |

The submitted-status blanket allow was removed. Submitted state is no longer an Application visibility bypass.

## 4. Exact Code Change

### ApplicationPolicy

- Reused the existing `InteractsWithProgramScope` trait.
- Preserved direct owner access.
- For all non-owners, requires `application.view` plus active membership in the Application's Program.
- Preserved Super Admin access via existing Gate before callback.

### ApplicationController index

- Scoped non-Super-Admin list results to owned Applications plus Applications in active memberships when the actor has `application.view`.
- Preserved global list visibility for existing Super Admin behavior.
- This is the adjacent query enforcement needed so index data matches the same read-policy boundary; authorization rules remain centralized in policy for individual records.

No route, migration, model, role, permission catalog, ProgramMembership architecture, UI, Screening logic, or Eligibility logic changed.

## 5. Program Staff, Owner, Super Admin, and Other Actor Results

- Program A Staff + Program A Application B: ALLOW.
- Program A Staff + Program B Application C: DENY.
- QA Applicant owner + owned Application A/B: ALLOW.
- Super Admin + Program B Application C: ALLOW.
- QA Applicant/Judge/Decision Maker received no new permission or role.

## 6. Focused Security Verification

Performed PHP syntax checks and live Gate/query verification using existing deterministic fixtures:

```text
QA Program Staff -> Program A Application B: ALLOW
QA Program Staff -> Program B Application C: DENY
QA Program Staff scoped index: QA-APPLICATION-B-SUBMITTED only
QA Applicant -> owned Applications: ALLOW
Super Admin -> Program B Application C: ALLOW
```

`git diff --check` passed. No broad test suite was executed.

## 7. Documentation

- Created `FeatureTest/022b-application-read-policy-reconciliation.md`; NOT EXECUTED.
- Created `ManualTest/ManualTest_12_Application_Read_Authorization.md`; all scenarios NOT RUN.

## 8. Database Changes

None. No migrations, seeders, schema changes, resets, or fixture changes were performed.

## 9. Files

### Created

- `FeatureTest/022b-application-read-policy-reconciliation.md`
- `ManualTest/ManualTest_12_Application_Read_Authorization.md`
- `AI-AGENT-HANDOFFS/022b-eaic-application-read-policy-reconciliation-summary.md`

### Modified

- `app/Policies/ApplicationPolicy.php`
- `app/Http/Controllers/ApplicationController.php`

### Intentionally not modified

- Application UI, Eligibility/Screening UI/business logic, routes, generic factories, QA fixture, migrations, RBAC permission/role catalog, Program architecture, and later lifecycle areas.

## 10. OWNER DECISION REQUIRED, Known Issues, and Risks

### OWNER DECISION REQUIRED

- Existing screening messaging, result taxonomy, validation-linkage, downstream lifecycle, and persisted role-grant decisions remain unchanged.

### Known Issue

- `QA FINDING - Application draft/version/action-state consistency` remains unrelated and unchanged.

### Known Risk

- Browser/manual QA has not yet been performed. Future Judge/Decision Maker Application visibility requires explicit assignment/scope policy when those stages are implemented.

## 11. Recommended Next Task

**RECOMMENDED - NOT YET APPROVED:** Perform limited browser smoke verification using the existing QA fixtures for Program A Staff Application/Eligibility/Screening access and Program B denial. Do not begin later lifecycle work.

## 12. Verified Facts vs Assumptions

### Verified Facts

- Governing RBAC requires Program Staff Application visibility to remain Program-scoped.
- Program A Staff has the required existing `application.view` grant and Program A membership.
- Focused live verification proves Program A allow, Program B deny, owner allow, Super Admin allow, and scoped index results.
- No new RBAC primitive or database data was created.

### Assumptions Avoided

- No broader Judge/Decision Maker visibility was inferred.
- No claim of browser/manual or broad automated test success.
- No screening/lifecycle business rule was changed.

## 13. Stop Condition

- [x] Application read authorization reconciled.
- [x] Cross-program Program Staff read access denied.
- [x] Owner and Super Admin visibility preserved.
- [x] FeatureTest and ManualTest_12 documented.
- [x] Focused verification completed.
- [x] Handoff 022B created.

Await Product and Technical Controller review.
