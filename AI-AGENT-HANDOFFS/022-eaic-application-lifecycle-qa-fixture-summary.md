# Task 022: Application Lifecycle QA Fixture Summary

**Interaction ID:** 022  
**Date:** 2026-09-01  
**Status:** COMPLETE  
**Test execution status:** NOT RUN BY DESIGN

## 1. Recovery State and Authority

Started from Handoff 021. Reviewed the required EAIC governance/contract/roadmap documents, relevant FeatureTest and ManualTest specifications, and handoffs 016C, 016D, 017, 017A, 018, 019, 020, and 021 before changes.

The live database was inspected first. Program A/B, their rules/rubrics, QA accounts, and the Program A staff membership existed. Application lifecycle records did not exist. An initial broad diagnostic reported absent Programs due to a query defect; a targeted joined query confirmed they exist. No fixture assumption was retained without that verification.

## 2. Existing Fixture Strategy

`ManualQaFixtureSeeder` was the single existing local development fixture mechanism. It already creates QA accounts, Program A/B, one Program A `program_staff` membership, eligibility rules, and rubrics idempotently. It does not create Application records.

## 3. Fixture Implementation Strategy

Extended `ManualQaFixtureSeeder` rather than adding a competing fixture system.

- Uses `firstOrCreate` by `(program_id, reference)` for applications and `(application_id, version_number)` for versions.
- Uses fixed submitted timestamp `2026-08-15 12:00:00 UTC` for deterministic submitted fixtures.
- Sets an Application current-version pointer only when it is null, avoiding overwrite of a pre-existing fixture record's pointer.
- Uses existing Application/ApplicationVersion models and supported JSON content only.

## 4. Applications and Versions Created

| Fixture | Program | Owner | State | Version |
|---|---|---|---|---|
| `QA-APPLICATION-A-DRAFT` | `EAIC-2026-01` | QA Applicant | draft | Version 1 draft |
| `QA-APPLICATION-B-SUBMITTED` | `EAIC-2026-01` | QA Applicant | submitted | Version 1 submitted at fixed UTC timestamp |
| `QA-APPLICATION-C-PROGRAM-B-SCOPE` | `EAIC-2026-02` | QA Applicant | submitted | Version 1 submitted at fixed UTC timestamp |

Each Application has deterministic summary-only content in its Version JSON. No unsupported content fields or reference system was introduced.

## 5. Ownership, Members, Scope, and Eligibility

- QA Applicant is the primary owner of all three fixtures.
- No ApplicationMember rows were created. Ownership and membership remain distinct in the existing model, and no additional member is necessary for these scenarios.
- QA Program Staff remains scoped to Program A only. No Program B membership or RBAC grant was added.
- Existing Program eligibility rules are used unchanged. No dynamic rule engine or executable expression was added.

## 6. Validation and Screening Initial State

ApplicationValidation count remains zero. Screening count remains zero. This preserves the intended manual sequence: use submitted Application B to trigger existing validation, then human screening. No Judge Assignment, Conflict, Evaluation, Deliberation, Decision, Outcome, Notification, or AI data was created.

## 7. QA Actors and RBAC Impact

Only established QA identities were used: Super Admin, Program Staff, Applicant, Judge, and Decision Maker. No new role, capability, permission, or membership was created or modified.

**Known verification boundary:** Current Program Staff direct fixture grants do not include `application.view`, although Eligibility/Screening GET routes have `permission:application.view` middleware. This Task 022 fixture does not alter that RBAC contract. It may block Program Staff browser access to those UI routes and must be reviewed separately.

## 8. Database Changes and Safety

### Database additions

- 3 Applications.
- 3 ApplicationVersions.
- Current-version pointers for those Application fixtures.

### Safety preserved

- No migration, `migrate:fresh`, `db:wipe`, truncate, drop, delete, or destructive reset was run.
- Existing users, roles, permissions, Programs, memberships, rules, and rubrics were preserved.
- Existing submitted versions were not overwritten.

## 9. Focused Database Verification

Confirmed through read-only queries:

- All five QA accounts are unique.
- Program A staff scope exists and is Program A-only.
- Each of the three deterministic fixtures exists once with its expected Program/owner/status.
- Current Version 1 records match their draft/submitted fixture state.
- ApplicationMember, ApplicationValidation, and Screening counts are zero.
- No duplicate application references or `(application_id, version_number)` combinations were found.
- No future lifecycle tables/data were created by this seeder.

The first broad verification command had a PHP parsing failure and was corrected once. A later targeted query initially referenced a non-existent `application_versions.number` column, then corrected once to the actual `version_number` field. The final focused checks succeeded under the one-retry rule.

## 10. Documentation

- Created `FeatureTest/022-application-lifecycle-qa-fixture-specification.md`; NOT EXECUTED.
- Created `ManualTest/ManualTest_10_Application_to_Screening_QA.md`; all scenarios NOT RUN.

## 11. Files

### Created

- `FeatureTest/022-application-lifecycle-qa-fixture-specification.md`
- `ManualTest/ManualTest_10_Application_to_Screening_QA.md`
- `AI-AGENT-HANDOFFS/022-eaic-application-lifecycle-qa-fixture-summary.md`

### Modified

- `database/seeders/ManualQaFixtureSeeder.php`

### Intentionally not modified

- Existing controllers, policies, models, routes, UI, migrations, RBAC architecture, Program fixtures, and historical handoffs/governance documents.

## 12. Known Issues, Decisions, Risks

### Known Issues

- `QA FINDING - Application draft/version/action-state consistency` is preserved and not changed.
- Program Staff may be blocked from Eligibility/Screening GET pages by missing `application.view` direct permission; Task 022 does not resolve it.

### OWNER DECISION REQUIRED

- Applicant-visible screening messaging.
- Additional Screening result taxonomy.
- Whether each Screening must reference a Validation.
- Downstream lifecycle transitions.
- Persisted role-to-permission grants beyond the existing approved fixture behavior.

### Known Risks

- No browser QA has been performed; UI behavior, route authorization, and responsive rendering require later observed verification.
- Eligibility logic remains the existing passing placeholder.

## 13. Recommended Next Task

**RECOMMENDED - NOT YET APPROVED:** Review the Program Staff `application.view` permission/middleware mismatch before limited browser verification of Application B's Eligibility/Screening UI. Do not begin Judge Assignment or later lifecycle stages.

## 14. Root Fixture Assumptions and Verified Facts

### Verified Facts

- Existing Program A/B fixture data and QA actors were present before this task.
- Models support deterministic references, JSON version content, draft/submitted state, timestamps, and current-version pointers.
- Three Application fixtures and three Version 1 records were added without duplicates.
- No ApplicationMember, Validation, Screening, or downstream records were pre-created.

### Assumptions Avoided

- No owner Member record was inferred.
- No application permission, Program B Staff scope, result status, Screening state, or later lifecycle record was invented.
- No claim that browser QA, Eligibility, or Screening has passed.

## 15. Stop Condition

- [x] Minimum governed Application lifecycle fixtures safely present.
- [x] FeatureTest specification created.
- [x] ManualTest_10 created.
- [x] Focused database verification completed.
- [x] Handoff 022 created.

Await Product and Technical Controller review.
