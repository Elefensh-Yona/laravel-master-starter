# Task 022D: Screening State and Schema Reconciliation

**Status:** Specification only. NOT EXECUTED.  
**Scope:** Valid `in_review -> completed` Screening lifecycle and authorization boundary.

## SCREENING-SCHEMA-001: New Screening starts in review

- **Actor/account:** QA Program Staff.
- **Preconditions:** Submitted Program A ApplicationVersion and active Program A staff scope.
- **Action:** Create a Screening.
- **Expected:** Record has `status=in_review`.
- **Security reason:** Human decision cannot be implied at creation.
- **Evidence:** Database row and browser detail.
- **PASS:** Create succeeds without HTTP 500 and state is in_review.
- **FAIL:** Completion data is required or state differs.

## SCREENING-SCHEMA-002: Completion fields may be null in review

- **Actor/account:** QA Program Staff.
- **Preconditions:** Newly created in_review Screening.
- **Action:** Inspect outcome, rationale, completed_at.
- **Expected:** All are null.
- **Security reason:** Incomplete review must not carry fabricated final data.
- **Evidence:** Database row.
- **PASS:** Nullable completion fields are empty until completion.
- **FAIL:** Schema rejects record or values are fabricated.

## SCREENING-SCHEMA-003: Completed Screening requires outcome

- **Actor/account:** QA Program Staff.
- **Preconditions:** In-review Screening.
- **Action:** Attempt completion without outcome.
- **Expected:** Controller validation rejects request.
- **Security reason:** Final human decision needs approved outcome.
- **Evidence:** Validation response and unchanged record.
- **PASS:** No completed transition.
- **FAIL:** Completed record has no outcome.

## SCREENING-SCHEMA-004: Completed Screening requires rationale

- **Actor/account:** QA Program Staff.
- **Preconditions:** In-review Screening.
- **Action:** Attempt completion without rationale.
- **Expected:** Controller validation rejects request.
- **Security reason:** Consequential decision requires recorded reason.
- **Evidence:** Validation response and unchanged record.
- **PASS:** No completed transition.
- **FAIL:** Completed record has empty rationale.

## SCREENING-SCHEMA-005: Completed Screening requires completion timestamp

- **Actor/account:** QA Program Staff.
- **Preconditions:** In-review Screening.
- **Action:** Complete with approved outcome/rationale.
- **Expected:** Controller sets completed_at atomically.
- **Security reason:** Completion must be auditable.
- **Evidence:** Database row.
- **PASS:** Non-null completed_at on completed record.
- **FAIL:** Completed state lacks timestamp.

## SCREENING-SCHEMA-006: Completed Screening cannot revert

- **Actor/account:** QA Program Staff.
- **Preconditions:** Completed Screening.
- **Action:** Attempt normal completion/update route again.
- **Expected:** Existing in_review guard rejects it.
- **Security reason:** Human decision history is immutable.
- **Evidence:** Response and before/after row.
- **PASS:** No reversion or mutation.
- **FAIL:** Status/outcome/rationale/timestamp changes.

## SCREENING-SCHEMA-007: Exact version remains immutable

- **Actor/account:** QA Program Staff.
- **Preconditions:** Screening tied to submitted Version 1.
- **Action:** Complete and inspect record.
- **Expected:** application_version_id remains original Version 1.
- **Security reason:** Screening must trace to exact submission.
- **Evidence:** Foreign key and UI detail.
- **PASS:** Version reference is unchanged.
- **FAIL:** Any version substitution.

## SCREENING-SCHEMA-008: Scoped Staff completes authorized Screening

- **Actor/account:** QA Program Staff.
- **Preconditions:** Program A scope and eligibility.screen permission.
- **Action:** Complete Program A in-review Screening.
- **Expected:** ELIGIBLE/INELIGIBLE plus rationale completes record and updates Application status transactionally.
- **Security reason:** Human authority is permission and scope controlled.
- **Evidence:** Browser and database rows.
- **PASS:** Valid transition succeeds.
- **FAIL:** Authorized completion fails or partial state remains.

## SCREENING-SCHEMA-009: Cross-program Screening is denied

- **Actor/account:** QA Program Staff.
- **Preconditions:** Program A-only scope; Program B Application C.
- **Action:** Direct Program B Screening URL/action.
- **Expected:** 403.
- **Security reason:** Permission cannot replace Program scope.
- **Evidence:** HTTP response and count.
- **PASS:** No Program B record/data/action.
- **FAIL:** Cross-program screening is accessible.

## SCREENING-SCHEMA-010: Applicant and Judge stay denied

- **Actor/account:** QA Applicant and QA Judge.
- **Preconditions:** No eligibility.screen grant or approved assignment.
- **Action:** Direct Screening URL/action.
- **Expected:** 403.
- **Security reason:** Neither actor has Program Staff screening authority.
- **Evidence:** HTTP response and count.
- **PASS:** Both are denied.
- **FAIL:** Either creates or completes Screening.

## SCREENING-SCHEMA-011: Application status update is atomic

- **Actor/account:** QA Program Staff.
- **Preconditions:** In-review Screening.
- **Action:** Complete with ELIGIBLE.
- **Expected:** Screening is completed and Application status becomes eligible in one transaction.
- **Security reason:** Workflow state cannot partially commit.
- **Evidence:** Post-request records.
- **PASS:** Both records reflect completion.
- **FAIL:** Only one record changes.

## SCREENING-SCHEMA-012: Completed history is preserved

- **Actor/account:** QA Program Staff.
- **Preconditions:** Completed Screening.
- **Action:** Reload detail/history.
- **Expected:** Outcome, rationale, actor, timestamp, and version remain displayed read-only.
- **Security reason:** Auditable human decision history is protected.
- **Evidence:** Browser detail and database row.
- **PASS:** Immutable result persists.
- **FAIL:** Completion form or mutable result is exposed.
