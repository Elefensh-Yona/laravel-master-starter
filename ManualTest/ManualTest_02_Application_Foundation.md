# Manual Test 02: Application Foundation

## 1. Purpose

This document defines the first manual QA plan for the EAIC Application foundation only.

This is intentionally limited to:

- `applications`
- `application_members`
- `application_versions`

It does not cover downstream screening, assignment, evaluation, deliberation, decision, outcome, or AI workflow modules.

---

## 2. Current implementation status

IMPLEMENTED

- Application aggregate model exists.
- Application member model exists.
- Application version model exists.
- Application factories exist.
- Application migrations exist.
- Application policies are registered in the provider.
- Focused Application foundation feature tests are present.

PARTIALLY IMPLEMENTED

- The data model exists for the foundation layer only.
- No downstream application lifecycle actions are implemented yet.

NOT IMPLEMENTED

- Application screening and evaluation.
- Assignment flows.
- Deliberation and decision flows.
- Outcome and notification modules.

STATUS

- NOT RUN BY DESIGN for live browser execution.
- This manual QA plan is a permanent future-execution artifact only.

---

## 3. Test matrix

### MT-02-001: Application record creation

- **Priority:** High
- **Feature/module:** Application foundation
- **Actor:** Authorized applicant or seeded admin account
- **Account:** current local QA account
- **Preconditions:** A valid Program exists and the user is the primary owner
- **Exact action steps:**
  1. Create an Application record for a valid active Program.
  2. Confirm the reference and status field values.
- **Expected UI result:** The record displays the Application identity and default draft state.
- **Expected backend/security result:** Record creation succeeds under the correct Application ownership or program-scoped actor model.
- **Expected database/business result:** One Application row exists for the Program and owner.
- **Evidence required:** screenshot or record identifier
- **PASS criteria:** The Application is created and stored in draft state.
- **FAIL criteria:** Missing record, invalid Program association, or inconsistent ownership.
- **Actual result:** NOT RUN
- **Status:** NOT RUN

### MT-02-002: Application membership uniqueness

- **Priority:** High
- **Feature/module:** Application membership
- **Actor:** Application owner or authorized actor
- **Account:** current QA account
- **Preconditions:** One Application exists and a member is active.
- **Exact action steps:**
  1. Attempt to add the same user as a second active member on the same Application.
- **Expected UI result:** Duplicate active membership is rejected.
- **Expected backend/security result:** Duplicate active membership is prevented by schema or validation.
- **Expected database/business result:** Only one active membership row remains for that user.
- **Evidence required:** record count and screenshot if applicable
- **PASS criteria:** Duplicate active member is blocked.
- **FAIL criteria:** Duplicate active membership is persisted.
- **Actual result:** NOT RUN
- **Status:** NOT RUN

### MT-02-003: Application version uniqueness

- **Priority:** High
- **Feature/module:** Application versioning
- **Actor:** Application owner
- **Account:** current QA account
- **Preconditions:** A draft Application exists.
- **Exact action steps:**
  1. Create a first ApplicationVersion.
  2. Attempt to create a second ApplicationVersion with the same `version_number` and `application_id`.
- **Expected UI result:** Duplicate version is rejected.
- **Expected backend/security result:** Unique `(application_id, version_number)` constraint prevents a duplicate version.
- **Expected database/business result:** Only one version row per application/version number remains.
- **Evidence required:** record list and validation or error message
- **PASS criteria:** duplicate version number is denied
- **FAIL criteria:** two rows with same version_number persist
- **Actual result:** NOT RUN
- **Status:** NOT RUN

### MT-02-004: Current version pointer integrity

- **Priority:** Medium
- **Feature/module:** Application version pointer
- **Actor:** Authorized owner
- **Account:** current QA account
- **Preconditions:** At least two ApplicationVersion rows exist for an Application.
- **Exact action steps:**
  1. Set the current application version pointer to a valid version.
  2. Confirm the relationship resolves to the intended record.
- **Expected UI result:** Correct version summary and metadata are visible.
- **Expected backend/security result:** Only valid version pointers are accepted.
- **Expected database/business result:** `current_version_id` references a valid record.
- **Evidence required:** reference ID and UI snapshot
- **PASS criteria:** pointer resolves correctly
- **FAIL criteria:** pointer references invalid or stale version
- **Actual result:** NOT RUN
- **Status:** NOT RUN

### MT-02-005: Cross-Program isolation

- **Priority:** High
- **Feature/module:** Application relationships
- **Actor:** Authorized owner or staff
- **Account:** designated Program A / Program B QA account
- **Preconditions:** An Application exists in Program A and another exists in Program B.
- **Exact action steps:**
  1. Attempt to access the Application from Program B within Program A context.
- **Expected UI result:** No cross-program access is displayed without authorization.
- **Expected backend/security result:** Access is denied.
- **Expected database/business result:** No accidental cross-assignment between Programs.
- **Evidence required:** response or page state
- **PASS criteria:** cross-program access is blocked
- **FAIL criteria:** the Application from Program B appears in Program A context
- **Actual result:** NOT RUN
- **Status:** NOT RUN

---

## 4. Execution policy

- These tests are intentionally not run by the AI agent during the Application foundation increment.
- The Product & Technical Controller or designated human QA reviewer is the required executor.
- Results must be recorded directly in the final column of this document before closing the QA checkpoint.

## 5. Evidence requirements

All manual QA results must record:

- date/time
- user account
- program record IDs
- application record IDs
- application version IDs when relevant
- screenshot or record evidence
- direct URL for route-based checks
- final PASS/FAIL/BLOCKED status

## 6. Stop condition

This manual QA file is created and marked `NOT RUN BY DESIGN` as requested.

This interaction stops here for the controlled Application foundation phase.
