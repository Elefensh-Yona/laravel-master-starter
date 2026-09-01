# Manual Test 11: RBAC Eligibility and Screening Access

**Status:** NOT RUN  
**Scope:** Future human verification after resolution of the documented Program Staff cross-program read-policy conflict.

| ID | Future browser check | Expected result | Status |
|---|---|---|---|
| 11.01 | Program Staff opens Program A Application B | Application context is accessible through the minimum approved view grant | NOT RUN |
| 11.02 | Program Staff opens Program A Eligibility | Objective validation page is accessible; action requires existing backend authority | NOT RUN |
| 11.03 | Program Staff opens Program A Screening | Human screening page is accessible; controls reflect backend capability | NOT RUN |
| 11.04 | Applicant attempts Staff action/direct URL | Staff validation/screening action is denied | NOT RUN |
| 11.05 | Judge attempts Staff action/direct URL | Staff validation/screening action is denied | NOT RUN |
| 11.06 | Program Staff attempts Program B Application C direct URL | Expected governance result is denial; record any cross-program read-policy defect | NOT RUN |
| 11.07 | Program Staff attempts Program B Eligibility/Screening action | Action is denied because Program B staff scope is absent | NOT RUN |
| 11.08 | Compare navigation/action visibility | UI flags supplement, but do not replace, backend authorization | NOT RUN |

## Evidence Rules

- Record account, target Program/Application, URL, response status, timestamp, and screenshot.
- Do not declare cross-program access secure until the Application read-policy conflict is resolved and observed.
- Do not use this plan to infer Judge Assignment or later lifecycle functionality.
