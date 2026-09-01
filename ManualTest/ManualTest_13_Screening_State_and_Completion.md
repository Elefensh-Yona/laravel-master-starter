# Manual Test 13: Screening State and Completion

**Formal Human Manual QA Status:** NOT RUN

## Agent Browser Smoke (Task 022D)

- **PASS:** QA Program Staff created Screening #5 for Program A Application B Version 1. It rendered as `in review` with outcome, rationale, and completion timestamp absent.
- **PASS:** QA Program Staff completed Screening #5 with `ELIGIBLE` and a rationale. The browser showed completed status, outcome, rationale, actor, timestamp, and no completion form; Application B rendered with status `eligible`.
- **PASS / EXPECTED DENY:** Program A-only Staff received 403 for Program B Application C Screening.
- **PASS / EXPECTED DENY:** QA Applicant received 403 for Program A Screening.

These are limited agent browser observations. They do not mark the formal cases below as passed.

## Formal Human QA Cases

| ID | Test | Expected result | Status |
|---|---|---|---|
| 13.01 | Start Screening as scoped Staff | Version-specific record starts in_review | NOT RUN |
| 13.02 | Inspect in-review display | Outcome/rationale/completion timestamp are empty; completion form appears | NOT RUN |
| 13.03 | Complete with ELIGIBLE | Completed status, outcome, rationale, timestamp, actor, version persist | NOT RUN |
| 13.04 | Complete with INELIGIBLE | Only approved outcome is accepted and application state follows implementation | NOT RUN |
| 13.05 | Submit missing rationale/outcome | Safe validation feedback; no state transition | NOT RUN |
| 13.06 | Reload completed Screening | Read-only result, no normal completion form/reversion | NOT RUN |
| 13.07 | Inspect exact submitted version | Version number/timestamp remain the assessed version | NOT RUN |
| 13.08 | Applicant direct Screening access | Denied; no Staff control/action | NOT RUN |
| 13.09 | Program A Staff Program B Screening access | Denied; no Program B disclosure | NOT RUN |
| 13.10 | Attempt invalid lifecycle transition | Completed record remains unchanged | NOT RUN |
| 13.11 | Inspect error handling | No internal exception information disclosed | NOT RUN |

## Evidence Rules

- Record actor, Program/Application/Version, URL, outcome, timestamp, HTTP response, and screenshot.
- Do not infer authorization from hidden controls only; verify direct routes.
- Do not claim eligibility placeholder output represents a real rules engine.
