# Manual Test 12: Application Read Authorization

**Status:** NOT RUN  
**Scope:** Future human QA for Task 022B's Application read policy boundary.

| ID | Browser verification | Expected result | Status |
|---|---|---|---|
| 12.01 | Super Admin opens Program B Application C | System-level Application access remains available | NOT RUN |
| 12.02 | Program Staff opens Program A Application B | In-scope Application context is available | NOT RUN |
| 12.03 | Program Staff opens Program B Application C | Access is denied; no Program B content is disclosed | NOT RUN |
| 12.04 | Program Staff uses Application C direct URL | Direct identifier does not bypass scope | NOT RUN |
| 12.05 | QA Applicant opens owned fixture Applications | Ownership visibility remains available | NOT RUN |
| 12.06 | Actor without application.view attempts non-owned Application | Route/policy denies access unless another approved path applies | NOT RUN |
| 12.07 | Program Staff opens Program A Eligibility/Screening context | In-scope read access supports separately authorized workflow actions | NOT RUN |
| 12.08 | Program Staff uses Program B Eligibility/Screening direct URLs | Program B context/action is denied | NOT RUN |
| 12.09 | Judge and Decision Maker attempt non-owned Application URLs | No unintended Application-wide visibility | NOT RUN |
| 12.10 | Inspect Application index as Program Staff | Only Program A scoped and owned Applications are listed | NOT RUN |

## Evidence Rules

- Capture account, URL, Program/Application reference, HTTP response, timestamp, and screenshot.
- Do not mark a result PASS based solely on hidden navigation; direct-route enforcement is required.
- Do not infer later lifecycle authority from Application read access.
