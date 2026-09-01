# Task 021: Eligibility and Screening UI Specification

**Status:** Specification only. NOT EXECUTED.  
**Scope:** Inertia/Vue UI for existing Eligibility Validation and Human Screening routes.

## Common Preconditions

- A submitted ApplicationVersion exists for an application in Program A.
- QA Program Staff has `eligibility.validate`/`eligibility.screen` and active Program A `program_staff` scope.
- A cross-program actor and unprivileged Applicant/Judge QA actors exist.

## UI-ELIGIBILITY-001: Staff opens validation index

- **Actor/account:** QA Program Staff.
- **Action:** Open an application Eligibility Validation index.
- **Expected UI:** History, selected submitted-version context, and Run validation control render.
- **Expected backend/security:** Existing route/policy authorizes only the eligible scoped actor.
- **Expected business/data:** No record changes on GET.
- **Security reason:** UI reflects backend authority, not a new permission system.
- **Evidence:** Screenshot and request status.
- **PASS:** Page renders with the application/program/version context.
- **FAIL:** Page errors, hides valid staff action, or changes data.

## UI-ELIGIBILITY-002: Unauthorized actor cannot validate

- **Actor/account:** Applicant or Judge.
- **Action:** Open the page and attempt the POST URL directly.
- **Expected UI:** No Run validation control.
- **Expected backend/security:** POST is denied by existing middleware/policy/scope.
- **Expected business/data:** No validation record created.
- **Security reason:** Frontend hiding is supplemental only.
- **Evidence:** Screenshot, HTTP outcome, and record count.
- **PASS:** Control is absent and request cannot mutate data.
- **FAIL:** Unauthorized validation can be created.

## UI-ELIGIBILITY-003: Exact version is displayed

- **Actor/account:** QA Program Staff.
- **Action:** Open index and detail for a validation tied to a submitted version.
- **Expected UI:** Version number, submission timestamp where supplied, and application identity appear.
- **Expected backend/security:** Route preserves the explicit `application_version_id`.
- **Expected business/data:** No substitution with a newer current version.
- **Security reason:** Decisions must trace to an exact immutable submission.
- **Evidence:** Page screenshot and database foreign key.
- **PASS:** UI version matches validation record.
- **FAIL:** UI implies or displays a different current version.

## UI-ELIGIBILITY-004: Status and result are visually correct

- **Actor/account:** QA Program Staff.
- **Action:** View passed, failed, and error validation fixtures.
- **Expected UI:** Existing status badge tones, supplied result payload, and failure reason appear without fabricated results.
- **Expected backend/security:** Immutable stored result is displayed.
- **Expected business/data:** No mutation.
- **Security reason:** Objective validation must not be represented as final human screening.
- **Evidence:** Screenshots for each supported status.
- **PASS:** Each status is distinguishable and objective language remains explicit.
- **FAIL:** Status is misleading, hidden, or represented as a final decision.

## UI-ELIGIBILITY-005: Validation errors are clear

- **Actor/account:** QA Program Staff.
- **Action:** Submit a non-submitted or unrelated version through the existing endpoint.
- **Expected UI:** Backend error feedback is displayed without internal exception detail.
- **Expected backend/security:** Existing version checks reject the request.
- **Expected business/data:** No validation record created.
- **Security reason:** Invalid references cannot bypass traceability.
- **Evidence:** Error message and database count.
- **PASS:** Clear user-safe feedback and no mutation.
- **FAIL:** Sensitive error disclosure or a record is created.

## UI-SCREENING-001: Staff opens screening index

- **Actor/account:** QA Program Staff.
- **Action:** Open an application Screening index.
- **Expected UI:** History, latest validation context where present, and Start screening action render.
- **Expected backend/security:** Scoped Program Staff remains authorized.
- **Expected business/data:** GET does not mutate data.
- **Security reason:** Human screening is Program Staff-scoped.
- **Evidence:** Screenshot and HTTP outcome.
- **PASS:** Page renders with distinct human-screening language.
- **FAIL:** Page errors or exposes staff action incorrectly.

## UI-SCREENING-002: Unauthorized actor cannot begin screening

- **Actor/account:** Applicant or Judge.
- **Action:** Open/directly POST the screening endpoint.
- **Expected UI:** Start action absent.
- **Expected backend/security:** POST denied.
- **Expected business/data:** No Screening created.
- **Security reason:** Screening cannot be performed by unassigned actors.
- **Evidence:** Screenshot, HTTP outcome, and count.
- **PASS:** No unauthorized control or mutation.
- **FAIL:** Actor starts screening.

## UI-SCREENING-003: Screening identifies exact version

- **Actor/account:** QA Program Staff.
- **Action:** Start and view a Screening for a submitted version.
- **Expected UI:** Version number, application identity, and submitted timestamp where supplied are visible.
- **Expected backend/security:** Explicit version foreign key remains authoritative.
- **Expected business/data:** Matching version is stored and rendered.
- **Security reason:** Human outcome must remain version-traceable.
- **Evidence:** Screenshot and foreign-key inspection.
- **PASS:** Rendered version matches stored version.
- **FAIL:** Any silent substitution occurs.

## UI-SCREENING-004: In-review screening has completion controls

- **Actor/account:** QA Program Staff.
- **Action:** Open an `in_review` Screening.
- **Expected UI:** Only `ELIGIBLE`/`INELIGIBLE` choices and required rationale field appear.
- **Expected backend/security:** Existing update route accepts only authorized in-review transition.
- **Expected business/data:** Valid submission completes the record atomically.
- **Security reason:** Outcome taxonomy and transitions are constrained.
- **Evidence:** Screenshot and request/result state.
- **PASS:** Staff can complete with rationale and supported outcome only.
- **FAIL:** Extra statuses/outcomes appear or completion controls are missing.

## UI-SCREENING-005: Completed screening is immutable

- **Actor/account:** QA Program Staff.
- **Action:** Open completed Screening.
- **Expected UI:** Outcome/rationale display; completion form absent.
- **Expected backend/security:** Existing state guard rejects a second completion.
- **Expected business/data:** Record remains unchanged.
- **Security reason:** Completed human decision history is protected.
- **Evidence:** Screenshot and attempted PUT result.
- **PASS:** Immutable presentation and backend enforcement agree.
- **FAIL:** Completed data is editable.

## UI-SCREENING-006: Applicant receives no Staff controls

- **Actor/account:** QA Applicant.
- **Action:** Attempt index/show/direct URLs where application visibility permits.
- **Expected UI:** No start or completion controls.
- **Expected backend/security:** Existing policy/middleware prevents staff action.
- **Expected business/data:** No mutation.
- **Security reason:** Applicant authority is ownership/delegation based.
- **Evidence:** Screenshot and request result.
- **PASS:** Staff controls remain absent.
- **FAIL:** Applicant can begin or complete Screening.

## UI-SCREENING-007: Cross-program actions remain restricted

- **Actor/account:** A Program Staff actor outside target program scope.
- **Action:** Attempt target index/show/store/update URLs.
- **Expected UI:** Restricted controls are absent when a page is visible.
- **Expected backend/security:** Existing scope checks deny restricted records/actions.
- **Expected business/data:** No record changes.
- **Security reason:** Global permissions cannot replace Program scope.
- **Evidence:** HTTP outcomes and counts.
- **PASS:** No target-program action exposed or accepted.
- **FAIL:** Out-of-scope action succeeds.

## UI-SCREENING-008: Invalid transition feedback is useful

- **Actor/account:** QA Program Staff.
- **Action:** Attempt to complete an already completed Screening.
- **Expected UI:** Safe backend error feedback; no internal exception text.
- **Expected backend/security:** Existing `in_review` guard rejects transition.
- **Expected business/data:** Existing outcome/rationale persist unchanged.
- **Security reason:** State transitions cannot be replayed.
- **Evidence:** Response feedback and before/after record.
- **PASS:** Clear feedback and unchanged record.
- **FAIL:** State/data mutates or details leak.

## UI-SCREENING-009: Validation and Screening remain distinct

- **Actor/account:** QA Program Staff.
- **Action:** Compare Eligibility and Screening index/detail pages.
- **Expected UI:** Validation is labeled objective; Screening is labeled human/final eligibility outcome.
- **Expected backend/security:** Existing distinct routes and policies are used.
- **Expected business/data:** No mutation.
- **Security reason:** Automated checks cannot be represented as human authority.
- **Evidence:** Screenshots.
- **PASS:** Labels and actions are unambiguous.
- **FAIL:** UI implies validation is final human decision.

## UI-SCREENING-010: No horizontal overflow

- **Actor/account:** Any authorized fixture account.
- **Action:** Inspect all four pages at 320px, tablet, and desktop widths with long rationale/result content.
- **Expected UI:** Controls wrap, tables scroll within their container, and the shell/sidebar stays within viewport bounds.
- **Expected backend/security:** No effect.
- **Expected business/data:** No effect.
- **Security reason:** Responsive rendering must not hide security-relevant context or controls.
- **Evidence:** Viewport screenshots and overflow inspection.
- **PASS:** No document-level horizontal overflow.
- **FAIL:** Viewport or sidebar overflows.
