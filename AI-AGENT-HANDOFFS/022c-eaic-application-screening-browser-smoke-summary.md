# Task 022C: Application to Screening Browser Smoke Summary

**Interaction ID:** 022C  
**Date:** 2026-09-01  
**Environment:** Local Laravel server, `http://127.0.0.1:8000`  
**Automated test execution:** NOT RUN BY DESIGN

## 1. Authority and Recovery

Reviewed the required EAIC governance/contract/roadmap documents, relevant FeatureTest/ManualTest specifications, and handoffs 020, 021, 022, 022A, and 022B. Used only existing local QA fixtures: Program A/B, Application B (ID 5, Program A, submitted Version 1), Application C (ID 6, Program B, submitted Version 1), and established QA accounts.

The local server was initially unavailable and was started with `php artisan serve --host=127.0.0.1 --port=8000`. The broader dev stack was not started because a prior Pail error is unrelated to this smoke path.

## 2. Actual Browser Results

| Smoke check | Outcome | Observed result |
|---|---|---|
| Super Admin login | PASS | Dashboard shell rendered; no verification barrier |
| Program Staff login | PASS | Authenticated profile shell rendered |
| Program A Application B | PASS | Submitted state, Program 37, reference, and Version 1 displayed |
| Eligibility index | PASS | Objective-validation distinction, selected Version 1, empty history, and action rendered |
| Objective validation | PASS | Validation #4 created as `passed`; actor, timestamp, Version 1, and result displayed |
| Screening index | PASS | Human-screening distinction, Version 1, latest validation, and start action rendered |
| Start Screening | FAIL | HTTP 500, described below |
| Complete Screening | BLOCKED | No `in_review` record can be created |
| Applicant own Application | PASS | Application B rendered |
| Applicant Eligibility URL | PASS / EXPECTED DENY | 403 returned; no Staff screen rendered |
| Program B Application C direct URL | PASS / EXPECTED DENY | 403 returned; no Application C data rendered |
| Program B Eligibility/Screening URLs | PASS / EXPECTED DENY | Both returned 403 |
| 320px Application/Eligibility overflow | PASS | Root width equal to viewport; no document-level overflow |

## 3. Critical Defect Found

**Observed failure:** Starting Screening for Application B Version 1 returns HTTP 500.

**Evidence:** PostgreSQL error `SQLSTATE[23502]`: null value in `screenings.completed_at` violates the non-null constraint. The log also shows `rationale` would be null. `ScreeningController::store()` correctly creates an approved `in_review` state without completion timestamp/rationale, but the existing migration declares both columns non-null.

**Classification:** Pre-existing schema/controller state-model mismatch discovered by smoke verification.

**Correction:** None. The only coherent correction requires schema contract review/migration so `in_review` records can exist without completion-only fields. Supplying fabricated completion data or changing schema during this smoke-only task would violate scope and history semantics. Per the one-retry rule, no code correction or repeat create attempt was made.

## 4. Authorization Evidence

- Program A scoped Staff can read Program A Application B and access Eligibility/Screening index pages.
- Program A scoped Staff is denied Program B Application C both from scoped index visibility and direct Application URL.
- Direct Program B Eligibility and Screening URLs are denied with 403.
- Applicant can read owned Application B but is denied the direct Eligibility URL through existing permission middleware.

This is observed browser evidence, not an assertion based only on source code.

## 5. UI, Version, and Responsive Evidence

- Application B rendered as submitted with exact Version 1.
- Eligibility index explicitly stated objective validation is not final human screening.
- Validation detail rendered Validation #4, Version 1, passed status, supplied rule result, QA Program Staff actor, and timestamp.
- Screening index rendered the latest validation as supporting context and kept human screening distinct.
- At 320px, Application and Validation pages both reported no document-level horizontal overflow. Browser screenshots were captured for those pages.
- Existing Application Show page did not expose Eligibility/Screening navigation links; direct registered routes were used. No UI change was made.

## 6. Documentation

- Created `FeatureTest/022c-browser-smoke-acceptance-specification.md` with actual limited smoke outcomes.
- Updated `ManualTest/ManualTest_10_Application_to_Screening_QA.md` with a separate Agent Smoke Verification section. Formal human cases remain NOT RUN.

## 7. Verification and Database Changes

- Browser smoke verification was authorized and performed.
- No Pest, PHPUnit, full regression, or browser regression suite was run.
- No database reset, migration, schema change, seeder run, or destructive operation was performed in this task.
- One ApplicationValidation record was created through the observed authorized validation action. No Screening record was created because its transaction failed.

## 8. Files

### Created

- `FeatureTest/022c-browser-smoke-acceptance-specification.md`
- `AI-AGENT-HANDOFFS/022c-eaic-application-screening-browser-smoke-summary.md`

### Modified

- `ManualTest/ManualTest_10_Application_to_Screening_QA.md`

### Intentionally not modified

- Application/Eligibility/Screening implementation, policies, routes, migrations, schema, RBAC, fixtures, generic factories, Program/UI shell, and later lifecycle areas.

## 9. Known Limitations and Decisions

### Known Limitations

- Eligibility rule evaluation remains the existing passing placeholder.
- Screening create/completion path is blocked by schema/controller mismatch.
- No completed Screening UI could be observed.

### OWNER DECISION REQUIRED

- Resolve the persistent schema contract for completion-only Screening fields in an `in_review` state before implementation correction.
- Existing applicant-visible messaging, additional result taxonomy, validation-screening linkage, downstream lifecycle, and persisted role-grant decisions remain unresolved.

## 10. Recommended Next Task

**RECOMMENDED - NOT YET APPROVED:** Reconcile the Screening schema with the approved `in_review -> completed` state model through a narrowly scoped migration/controller acceptance task, then rerun only Screening start/completion browser smoke checks.

## 11. Verified Facts vs Assumptions

### Verified Facts

- Local server, QA logins, Program A read path, Eligibility validation, Applicant boundary, Program B boundary, and mobile no-overflow checks were observed in the browser.
- Program B direct Application/Eligibility/Screening access returned 403 for Program A-only Staff.
- Screening creation failed with the exact PostgreSQL non-null error.

### Assumptions Avoided

- No claim that Screening can be completed or completed UI works.
- No claim that the placeholder validation evaluator constitutes real eligibility evaluation.
- No claim of formal human QA or broad automated test success.

## 12. Stop Condition

- [x] Limited smoke verification completed.
- [x] Actual PASS/FAIL/BLOCKED results documented.
- [x] FeatureTest specification created.
- [x] ManualTest updated without marking formal QA passed.
- [x] Handoff 022C created.
- [x] No unrelated feature work begun.

Await Product and Technical Controller review.
