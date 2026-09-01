# Task 022C: Browser Smoke Acceptance Specification

**Environment:** Local Laravel server at `http://127.0.0.1:8000`  
**Status:** Limited agent smoke results recorded below; this is not a full regression or formal human QA suite.

## SMOKE-001: Super Admin login

- **Actor/account:** Super Admin.
- **Preconditions:** Local QA fixture and verified admin state.
- **Exact action:** Sign in through the browser login form.
- **Expected UI/authorization:** Authenticated shell; no verification barrier.
- **Actual result:** **PASS by observed browser evidence.** Dashboard rendered with Super Admin access label.
- **Evidence:** Browser URL `/dashboard`; authenticated application shell snapshot.
- **Notes:** No administrative workflow was exercised.

## SMOKE-002: Program Staff login

- **Actor/account:** QA Program Staff.
- **Preconditions:** Active Program A `program_staff` membership and fixture permissions.
- **Exact action:** Sign in through browser login form.
- **Expected UI/authorization:** Authenticated shell without email-verification barrier.
- **Actual result:** **PASS by observed browser evidence.** Profile shell rendered for QA Program Staff.
- **Evidence:** Browser URL `/settings/profile`; authenticated user label/email snapshot.

## SMOKE-003: Program A Application access

- **Actor/account:** QA Program Staff.
- **Preconditions:** Program A Application B submitted fixture.
- **Exact action:** Open `/applications/5`.
- **Expected UI/authorization:** In-scope application view, Program, submitted state, Version 1.
- **Actual result:** **PASS by observed browser evidence.** Application B rendered as Program 37, submitted, Version 1.
- **Evidence:** Browser snapshot and mobile screenshot.
- **Notes:** Existing Application Show UI has no Eligibility/Screening navigation controls; direct registered routes were used.

## SMOKE-004: Eligibility access

- **Actor/account:** QA Program Staff.
- **Preconditions:** Application B submitted Version 1.
- **Exact action:** Open `/applications/5/eligibility-validations`.
- **Expected UI/authorization:** Objective validation context, Version 1 selector, history state, Run validation control, human-decision distinction.
- **Actual result:** **PASS by observed browser evidence.** Page rendered all required context and the no-history state.
- **Evidence:** Browser snapshot at the registered URL.

## SMOKE-005: Objective validation

- **Actor/account:** QA Program Staff.
- **Preconditions:** Eligibility index loaded with Version 1 selected.
- **Exact action:** Confirm Run validation.
- **Expected UI/authorization/result:** Version 1 validation records approved status and shows actor/timestamp/result.
- **Actual result:** **PASS by observed browser evidence.** Created Validation #4 with `passed`, Version 1, QA Program Staff, timestamp, and supplied rule result.
- **Evidence:** Browser URL `/applications/5/eligibility-validations/4` and success alert.
- **Notes:** `passed` reflects the known placeholder eligibility evaluator; no rule-engine change was made.

## SMOKE-006: Screening access

- **Actor/account:** QA Program Staff.
- **Preconditions:** Application B and Validation #4.
- **Exact action:** Open `/applications/5/screenings`.
- **Expected UI/authorization:** Human screening context, Version 1, latest validation, Start screening control.
- **Actual result:** **PASS by observed browser evidence.** Page rendered no-screening state, Version 1 selector, latest passed validation, and Start screening control.
- **Evidence:** Browser snapshot at registered URL.

## SMOKE-007: Start Screening

- **Actor/account:** QA Program Staff.
- **Preconditions:** Screening index loaded with Version 1 selected.
- **Exact action:** Confirm Start screening.
- **Expected UI/authorization/result:** Create `in_review` Screening for Version 1.
- **Actual result:** **FAIL by observed browser evidence.** Request returned HTTP 500.
- **Evidence:** Browser console/network error and Laravel log.
- **Notes:** PostgreSQL rejected the insert because `screenings.completed_at` and `screenings.rationale` are non-null while the approved `in_review` creation action supplies neither. No corrective change was made because a schema change or placeholder completion data is outside this smoke task.

## SMOKE-008: Complete Screening

- **Actor/account:** QA Program Staff.
- **Preconditions:** An `in_review` Screening must exist.
- **Exact action:** Complete with `ELIGIBLE` and rationale.
- **Expected UI/authorization/result:** Completed immutable screening and current application-status update.
- **Actual result:** **BLOCKED.** SMOKE-007 cannot create the prerequisite `in_review` record.
- **Evidence:** Observed SMOKE-007 HTTP 500.

## SMOKE-009: Applicant boundaries

- **Actor/account:** QA Applicant.
- **Preconditions:** Applicant owns Application B.
- **Exact action:** Log in, open `/applications/5`, then directly open Eligibility URL.
- **Expected UI/authorization:** Own Application allowed; Staff Eligibility actions denied.
- **Actual result:** **PASS by observed browser evidence.** Own Application B rendered; Eligibility URL returned 403 and no Staff control rendered.
- **Evidence:** Application page snapshot and 403 browser page.

## SMOKE-010: Program B cross-program denial

- **Actor/account:** QA Program Staff.
- **Preconditions:** Program A-only staff scope; Program B Application C.
- **Exact action:** Open Application index and direct `/applications/6`.
- **Expected UI/authorization:** Application C omitted from index and direct access denied without data disclosure.
- **Actual result:** **PASS by observed browser evidence.** Index showed Program A Applications only; `/applications/6` returned 403.
- **Evidence:** Browser index snapshot and 403 page.

## SMOKE-011: Program B Eligibility/Screening denial

- **Actor/account:** QA Program Staff.
- **Preconditions:** Same as SMOKE-010.
- **Exact action:** Directly open `/applications/6/eligibility-validations` and `/applications/6/screenings`.
- **Expected UI/authorization:** Deny without Program B data.
- **Actual result:** **PASS by observed browser evidence.** Both URLs returned 403.
- **Evidence:** Browser 403 pages; no record content rendered.

## SMOKE-012: Responsive and overflow

- **Actor/account:** QA Program Staff.
- **Preconditions:** Rendered Application B and Validation #4 pages.
- **Exact action:** Reload each at 320px viewport width and measure root scroll width.
- **Expected UI:** No document-level horizontal overflow.
- **Actual result:** **PASS by observed browser evidence.** Both pages measured `scrollWidth=305`, `clientWidth=305`, `horizontalOverflow=false`.
- **Evidence:** Playwright measurements and mobile screenshots.
- **Notes:** Screening detail could not be inspected because no Screening record could be created.
