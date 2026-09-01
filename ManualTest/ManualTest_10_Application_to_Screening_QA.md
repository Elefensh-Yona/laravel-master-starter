# Manual Test 10: Application to Screening QA

**Status:** NOT RUN  
**Scope:** Future browser QA using the governed Task 022 local fixture set.

## Agent Smoke Verification (Task 022C)

This section records limited agent browser smoke evidence only. It does not mark the formal human QA scenarios below as run or passed.

- Observed PASS: Super Admin login, Program Staff login, Program A Application B read access, Eligibility index, Version 1 objective validation, Screening index, Applicant owned Application access, Applicant Eligibility denial, Program B Application/Eligibility/Screening denial, and 320px no-overflow measurements for Application/Eligibility pages.
- Observed FAIL: Starting Screening returned HTTP 500 because the schema requires `completed_at` and `rationale` for an `in_review` create action.
- BLOCKED: Screening completion and completed-screening UI cannot be observed until the creation-schema mismatch is resolved.
- Full formal human QA status remains **NOT RUN**.

## Preconditions

- The existing Manual QA fixture has created the five local QA accounts.
- `QA-APPLICATION-A-DRAFT`, `QA-APPLICATION-B-SUBMITTED`, and `QA-APPLICATION-C-PROGRAM-B-SCOPE` exist.
- This plan does not authorize creation of Judge, Evaluation, Decision, Notification, or AI data.

| ID | Future browser check | Expected result | Status |
|---|---|---|---|
| 10.01 | Sign in as Super Admin | Login passes local verification and administrative identity is retained | NOT RUN |
| 10.02 | Sign in as QA Applicant | Local verification passes; owned Applications are visible according to existing authorization | NOT RUN |
| 10.03 | Sign in as QA Program Staff | Local verification passes; Program A-only authority remains bounded | NOT RUN |
| 10.04 | Open Application A | Draft status and editable Version 1 are displayed consistently | NOT RUN |
| 10.05 | Open Application B | Submitted status and exact submitted Version 1 are visible | NOT RUN |
| 10.06 | Inspect Application B version details | Version number, status, and submitted timestamp identify the assessed version | NOT RUN |
| 10.07 | Open Eligibility page for Application B | Objective validation page renders with no pre-created result | NOT RUN |
| 10.08 | Trigger validation as authorized Program Staff | A result is created only by the existing backend action; no client-side result is fabricated | NOT RUN |
| 10.09 | Inspect validation result | Version traceability and objective-not-final-decision wording are clear | NOT RUN |
| 10.10 | Open Screening page for Application B | Human screening page renders with no pre-created Screening | NOT RUN |
| 10.11 | Start Screening as authorized Program Staff | Selected exact submitted version is used | NOT RUN |
| 10.12 | Complete ELIGIBLE Screening | Only the supported outcome and required rationale are accepted | NOT RUN |
| 10.13 | Complete INELIGIBLE Screening in a separate approved scenario | Only the supported outcome and required rationale are accepted | NOT RUN |
| 10.14 | Attempt Staff action as Applicant | Staff validation/screening controls and direct actions are denied | NOT RUN |
| 10.15 | Attempt Program B Staff action using Application C | Program A scope does not authorize Program B operation | NOT RUN |
| 10.16 | Attempt direct protected URLs | Backend authorization remains authoritative | NOT RUN |
| 10.17 | Inspect Application draft/version/action consistency | Record the existing QA finding without silently changing unrelated UI | NOT RUN |
| 10.18 | Inspect mobile, tablet, desktop layout | Version information, forms, tables, and shell remain usable without horizontal overflow | NOT RUN |

## Evidence Rules

- Record actor, application reference, Program, version, URL, response, timestamp, and screenshot for each actual run.
- Do not mark a scenario PASS because a control is hidden; confirm backend enforcement where relevant.
- Do not claim automated validation is a final human decision or that later lifecycle stages exist.
