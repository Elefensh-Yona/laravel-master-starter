# Manual Test 09: Eligibility and Screening UI

**Status:** NOT RUN  
**Scope:** Future browser QA for Task 021. This document is not evidence of completed manual testing.

## Preconditions

- Local QA fixture accounts and Program A scope exist.
- A submitted ApplicationVersion, validation examples, and Screening examples exist through approved setup.
- Do not create downstream lifecycle data solely to execute these scenarios.

## Scenarios

| ID | Future browser check | Expected result | Status |
|---|---|---|---|
| 09.01 | Open Eligibility index as scoped Program Staff | History/empty state, exact submitted-version selector, and validation control render | NOT RUN |
| 09.02 | Open validation detail | Objective-validation wording, status, result, failure information, exact version, and audit data render | NOT RUN |
| 09.03 | Confirm and run validation | New validation is requested for selected submitted version only; backend feedback is visible | NOT RUN |
| 09.04 | Inspect passed/failed/error fixtures | Existing status badges distinguish all supplied validation statuses | NOT RUN |
| 09.05 | Open Screening index as scoped Program Staff | Human-screening language, history/empty state, validation context, and start action render | NOT RUN |
| 09.06 | Start Screening | Exact submitted version is selected/confirmed before record creation | NOT RUN |
| 09.07 | Open in-review Screening | Completion controls offer only ELIGIBLE/INELIGIBLE and required rationale | NOT RUN |
| 09.08 | Complete ELIGIBLE Screening | Outcome/rationale submit and final result is shown | NOT RUN |
| 09.09 | Complete INELIGIBLE Screening | Outcome/rationale submit and final result is shown | NOT RUN |
| 09.10 | Open completed Screening | Immutable outcome/rationale display with no completion form | NOT RUN |
| 09.11 | Inspect version traceability | Application ID, version number, and submitted timestamp where provided match the assessed record | NOT RUN |
| 09.12 | Inspect linked validation context | Linked validation is contextual and not represented as the human decision | NOT RUN |
| 09.13 | Sign in as Applicant | No Staff validation/screening controls; direct requests remain protected | NOT RUN |
| 09.14 | Sign in as Judge | No Staff screening controls or unauthorized direct access | NOT RUN |
| 09.15 | Try cross-program URL/action | Restricted action is denied; no data changes | NOT RUN |
| 09.16 | Trigger invalid version/transition response | Safe, useful error feedback; no exception details | NOT RUN |
| 09.17 | Inspect no-validation/no-screening state | Empty states are readable and do not promise unsupported actions | NOT RUN |
| 09.18 | Navigate Application -> Eligibility -> Screening | Breadcrumbs and back actions remain consistent with the existing shell | NOT RUN |
| 09.19 | Inspect 320px/mobile/tablet/desktop | Cards stack; tables remain usable; rationale wraps; no document/sidebar overflow | NOT RUN |

## Evidence Rules

- Record actor, Program/Application/Version context, URL, timestamp, HTTP outcome, and screenshot for each executed scenario.
- Do not mark PASS based on frontend control visibility alone; backend authorization remains authoritative.
- Do not use these tests to imply Judge Assignment, Conflict, Evaluation, Decision, Notification, or AI functionality exists.
