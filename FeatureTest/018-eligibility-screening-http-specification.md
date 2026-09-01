# FeatureTest/018-eligibility-screening-http-specification

**Status:** Specification only. NOT EXECUTED.  
**Purpose:** Document HTTP/Inertia delivery for Eligibility & Screening foundation.  
**Date:** 2026-09-01  
**Scope:** HTTP endpoints, authorization, version traceability, state management

---

## Overview

This specification documents 12 HTTP/Inertia test scenarios for the eligibility validation and screening delivery layer built on the Task 017 foundation.

**Key Testing Principles:**
- These are specifications for future automated testing
- No tests are executed in Task 018
- Tests validate authorization, version traceability, state transitions, and data integrity
- Backend authorization enforcement is central (not frontend hiding)
- Exact Application Version must be preserved in every action

---

## Test Scenarios

### ELIGIBILITY-HTTP-001: Authorized Program Staff Can Access Validation History

**Test ID:** ELIGIBILITY-HTTP-001

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User is active Program Staff member in a published program (capability = `program_staff`, status = `active`)

**Application Context:** Application belongs to the target program, status = `submitted`

**Version Context:** Application has submitted version #1 with status = `submitted`

**Preconditions:**
- User has `eligibility.validate` permission
- User has active program membership with `program_staff` capability
- Program is published
- Application has a submitted version
- At least one validation record exists for the application

**Exact HTTP/Action:**
```
GET /applications/{application}/eligibility-validations
Headers: Authorization: Bearer {token}
```

**Expected Authorization Result:**
- Backend authorizes via `$this->authorize('view', $application)`
- User can view application (owner or has `application.view` permission)
- HTTP 200 returned

**Expected Business Result:**
- Inertia renders `applications/eligibility/Index`
- Page includes validation history ordered by execution time (most recent first)
- Submitted versions listed
- `canValidate` flag set based on permission + program scope

**Expected Database Result:**
- Query: Application, ApplicationVersion (where status='submitted'), ApplicationValidation records
- Relationships loaded: `applicationValidation->program`, `applicationValidation->executor`
- No data modified

**Security Reason:**
- Validates staff can only access eligibility features within their authorized program scope
- Confirms application view permission is required

**Evidence Required:**
- HTTP 200 response
- Response body contains application ID and validation array
- Validation records sorted by executed_at DESC
- canValidate flag accurate for requesting user

**PASS Criteria:**
- Status 200
- Page data returned
- canValidate correctly reflects user permissions and scope
- Validation history accurate

**FAIL Criteria:**
- Status 403 or 401
- Wrong data returned
- canValidate inaccurate

---

### ELIGIBILITY-HTTP-002: Out-of-Scope Program Staff Access is Denied

**Test ID:** ELIGIBILITY-HTTP-002

**Actor:** Program Staff user (assigned to Program A)

**Account:** `qa-program-staff-a@example.com`

**Authentication:** Verified and authenticated

**Program Context:** Target application belongs to Program B; requesting user belongs to Program A

**Application Context:** Application belongs to Program B

**Version Context:** Application has submitted version

**Preconditions:**
- User is active staff in Program A (not Program B)
- User has `eligibility.validate` permission (global)
- Application belongs to Program B

**Exact HTTP/Action:**
```
GET /applications/{application-in-program-b}/eligibility-validations
Headers: Authorization: Bearer {token-of-program-a-staff}
```

**Expected Authorization Result:**
- Backend authorizes via `$this->authorize('view', $application)`
- User's program membership in Program B is checked
- Cross-program scope violation detected

**Expected Business Result:**
- HTTP 403 Forbidden (permission denied)
- No data exposed

**Expected Database Result:**
- No queries executed beyond authorization checks
- No data returned

**Security Reason:**
- Prevents cross-program data leakage
- Ensures program staff from Program A cannot access Program B applications

**Evidence Required:**
- HTTP 403 response
- No application data in response

**PASS Criteria:**
- Status 403
- Cross-program access blocked
- No data exposed

**FAIL Criteria:**
- Status 200 (data leaked)
- Application data returned
- Staff can see cross-program applications

---

### ELIGIBILITY-HTTP-003: Applicant Cannot Access Validation Endpoint

**Test ID:** ELIGIBILITY-HTTP-003

**Actor:** Applicant user (application owner)

**Account:** `qa-applicant@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User is application primary owner, not program staff

**Application Context:** User owns the application

**Version Context:** Application has submitted version

**Preconditions:**
- User is primary owner of application
- User does NOT have `eligibility.validate` permission
- User does NOT have program staff membership

**Exact HTTP/Action:**
```
GET /applications/{application}/eligibility-validations
Headers: Authorization: Bearer {token}
```

**Expected Authorization Result:**
- Backend authorizes via `$this->authorize('view', $application)` — passes (user is owner)
- User can see application
- However, index renders with `canValidate: false` because user lacks permission
- If user attempts to trigger validation (store), it fails at permission check

**Expected Business Result:**
- GET returns HTTP 200 (user can view application)
- Page rendered with `canValidate: false`
- No validation button/action visible to frontend (UI decision)
- Backend prevents store action

**Expected Database Result:**
- Application and validation query executed
- No permissions changed
- No new records created

**Security Reason:**
- Application ownership does not grant staff screening authority
- Validation is staff-only, not applicant-controlled

**Evidence Required:**
- HTTP 200 for GET
- canValidate false in response
- POST would fail at permission check

**PASS Criteria:**
- GET returns 200
- canValidate is false
- Applicant cannot trigger validation (permission denied on POST)

**FAIL Criteria:**
- canValidate is true for applicant
- Applicant can trigger validation (HTTP 200 on POST)

---

### ELIGIBILITY-HTTP-004: Validation References Exact Submitted Application Version

**Test ID:** ELIGIBILITY-HTTP-004

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User is staff in the application's program

**Application Context:** Application with versions 1 (draft, superseded), 2 (submitted), 3 (draft, current)

**Version Context:** Testing validation of version #2 (submitted), ensuring version #3 is not substituted

**Preconditions:**
- Application has 3 versions
- Version #1: draft, superseded by version #2
- Version #2: submitted, target for validation
- Version #3: draft, current_version_id, not submitted
- User has `eligibility.validate` permission

**Exact HTTP/Action:**
```
POST /applications/{application}/eligibility-validations
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 2
}
```

**Expected Authorization Result:**
- Backend authorizes: application viewable, user has permission + scope

**Expected Business Result:**
- Validation created for version #2 (submitted)
- HTTP 302 redirect to validation show page

**Expected Database Result:**
```sql
SELECT * FROM application_validations 
WHERE application_id = {id} AND application_version_id = 2
```
- Exactly 1 record created
- Fields: program_id, application_id, application_version_id=2, status, result, executed_at, executed_by
- Version #3 (current but draft) NOT used
- Validation linked to version #2, not current version

**Security Reason:**
- Ensures validation is tied to exact submitted version submitted by applicant
- Prevents substitution with newer draft versions
- Maintains version traceability and audit trail

**Evidence Required:**
- Validation record with application_version_id = 2
- No validation exists for version #3
- HTTP 302 redirect to correct validation

**PASS Criteria:**
- Validation created for version #2
- application_version_id in database = 2 (not 3, not current_version_id)
- No substitution occurred

**FAIL Criteria:**
- Validation created for wrong version
- application_version_id ≠ 2
- Current version used instead of submitted version

---

### ELIGIBILITY-HTTP-005: Invalid Application/Version Relationship Rejected

**Test ID:** ELIGIBILITY-HTTP-005

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- User has `eligibility.validate` permission and program staff scope
- Application #1 exists with version #1
- Application #2 exists with version #2
- Attempting to validate Application #1 with Version #2 (doesn't belong to App #1)

**Exact HTTP/Action:**
```
POST /applications/{application-1}/eligibility-validations
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 2  // version from application #2
}
```

**Expected Authorization Result:**
- Authorization passes for Application #1 view

**Expected Business Result:**
- Validation fails with error: "Invalid application version"
- HTTP 302 redirect back with error message
- No validation record created

**Expected Database Result:**
- No new record in application_validations
- No cross-application linking

**Security Reason:**
- Prevents version substitution attacks
- Ensures input validation catches mismatched relationships

**Evidence Required:**
- HTTP 302 redirect
- Error message in flash data
- No validation record created
- Database unchanged

**PASS Criteria:**
- Version validation rejects mismatched application_id
- Error returned
- No data persisted

**FAIL Criteria:**
- Mismatched version accepted
- Validation created with wrong version
- Cross-application linking allowed

---

### ELIGIBILITY-HTTP-006: Validation Result is Auditable (Actor + Timestamp)

**Test ID:** ELIGIBILITY-HTTP-006

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- User is staff with validation permission
- Application has submitted version

**Exact HTTP/Action:**
```
POST /applications/{application}/eligibility-validations
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 1
}
```

**Expected Database Result:**
```sql
SELECT * FROM application_validations 
WHERE application_id = {id}
```
- Record contains:
  - `executed_by` = {user.id} (staff user performing validation)
  - `executed_at` = current timestamp (or frozen for testing)
  - Timestamp is timezone-aware (timestamptz)

**Security Reason:**
- Audit trail requires actor identity
- Timestamp proves when validation occurred
- Immutable record (cannot be modified after creation)

**Evidence Required:**
- Validation record shows correct executed_by user ID
- executed_at timestamp is recent (within test execution time)
- Timestamp is UTC/timezone-aware

**PASS Criteria:**
- executed_by matches requesting staff user
- executed_at is set and valid
- Record is immutable after creation

**FAIL Criteria:**
- executed_by is NULL or incorrect
- executed_at is missing or in wrong timezone
- Record can be modified

---

### SCREENING-HTTP-001: Authorized Program Staff Can Access Screening

**Test ID:** SCREENING-HTTP-001

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User is active staff in the application's program

**Application Context:** Application belongs to program, status = `submitted`

**Version Context:** Application has submitted version

**Preconditions:**
- User has `eligibility.screen` permission
- User has active program membership with `program_staff` capability
- Application has submitted version
- Program is published

**Exact HTTP/Action:**
```
GET /applications/{application}/screenings
Headers: Authorization: Bearer {token}
```

**Expected Authorization Result:**
- Backend authorizes via `$this->authorize('view', $application)`
- User can view application
- HTTP 200 returned

**Expected Business Result:**
- Inertia renders `applications/screening/Index`
- Screening history listed
- `canScreen` flag set to true
- Latest validation (if any) shown for context

**Expected Database Result:**
- Queries: Application, ApplicationVersion, Screening, ApplicationValidation
- Relationships loaded: `screening->screener`, `screening->reopenedBy`, `screening->validation`

**Security Reason:**
- Validates staff can access screening within authorized program scope
- Confirms application view permission required

**Evidence Required:**
- HTTP 200
- Page data returned
- canScreen = true
- Screening records accurate

**PASS Criteria:**
- Status 200
- canScreen correctly reflects staff authorization
- Screening history present

**FAIL Criteria:**
- Status 403
- canScreen false for authorized staff
- Wrong data

---

### SCREENING-HTTP-002: Applicant Cannot Perform Staff Screening

**Test ID:** SCREENING-HTTP-002

**Actor:** Applicant user (application owner)

**Account:** `qa-applicant@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- User is application primary owner
- User does NOT have `eligibility.screen` permission
- User does NOT have program staff membership

**Exact HTTP/Action:**
```
POST /applications/{application}/screenings
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 1,
  "validation_id": null
}
```

**Expected Authorization Result:**
- User fails `can('eligibility.screen')` permission check
- HTTP 403 Forbidden

**Expected Business Result:**
- Screening not created
- HTTP 403 returned

**Expected Database Result:**
- No screening record created
- application_validations, screenings tables unchanged

**Security Reason:**
- Application ownership does not grant screening authority
- Screening is staff-only decision-making action

**Evidence Required:**
- HTTP 403
- No screening record created

**PASS Criteria:**
- Applicant denied (403)
- No screening action possible

**FAIL Criteria:**
- Applicant can create screening
- HTTP 200 or 302 (accepted)

---

### SCREENING-HTTP-003: Judge Cannot Perform Staff Screening Without Explicit Authorization

**Test ID:** SCREENING-HTTP-003

**Actor:** Judge user (Judge role, but not program staff)

**Account:** `qa-judge@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User is in program, but with Judge assignment (not program staff capability)

**Preconditions:**
- User has some role but NOT `eligibility.screen` permission
- User does NOT have program membership with `program_staff` capability
- Application has submitted version

**Exact HTTP/Action:**
```
POST /applications/{application}/screenings
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 1
}
```

**Expected Authorization Result:**
- User fails `can('eligibility.screen')` permission check (permission not granted to Judge role)
- HTTP 403 Forbidden

**Expected Business Result:**
- Screening not created
- HTTP 403 returned

**Expected Database Result:**
- No screening record created

**Security Reason:**
- Judge role is separate from staff screening capability
- Judge can perform evaluations (future phase), not eligibility screening
- Prevents role confusion and unauthorized decisions

**Evidence Required:**
- HTTP 403
- No screening record

**PASS Criteria:**
- Judge denied (403)
- Screening cannot be created by judge

**FAIL Criteria:**
- Judge can create screening
- HTTP 200/302

---

### SCREENING-HTTP-004: Cross-Program Screening Access Denied

**Test ID:** SCREENING-HTTP-004

**Actor:** Program Staff user (assigned to Program A)

**Account:** `qa-program-staff-a@example.com`

**Authentication:** Verified and authenticated

**Program Context:** User belongs to Program A; target application belongs to Program B

**Preconditions:**
- User is staff in Program A only
- User has `eligibility.screen` permission
- Application belongs to Program B

**Exact HTTP/Action:**
```
POST /applications/{application-in-program-b}/screenings
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 1
}
```

**Expected Authorization Result:**
- Backend checks program staff scope for Program B
- Cross-program check fails: user not staff in Program B
- HTTP 403 Forbidden

**Expected Business Result:**
- Screening not created
- HTTP 403 returned

**Expected Database Result:**
- No screening record created
- No cross-program linking

**Security Reason:**
- Program staff from Program A cannot make decisions in Program B
- Enforces program boundary at HTTP layer

**Evidence Required:**
- HTTP 403
- No screening record

**PASS Criteria:**
- Cross-program access blocked
- HTTP 403

**FAIL Criteria:**
- Cross-program screening created
- HTTP 200/302

---

### SCREENING-HTTP-005: Screening References Exact Submitted Application Version

**Test ID:** SCREENING-HTTP-005

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Application Context:** Application with versions 1 (draft), 2 (submitted), 3 (draft, current)

**Version Context:** Screening created for version #2 (submitted)

**Preconditions:**
- Application has 3 versions as described
- User is staff with screening permission

**Exact HTTP/Action:**
```
POST /applications/{application}/screenings
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 2,
  "validation_id": null
}
```

**Expected Database Result:**
```sql
SELECT * FROM screenings 
WHERE application_id = {id} AND application_version_id = 2
```
- Screening created with application_version_id = 2
- Version #3 (current draft) NOT used
- Status = 'in_review' (initial state)
- Exact version preserved

**Security Reason:**
- Ensures screening decision applies to submitted version, not current draft
- Prevents version substitution
- Maintains audit trail accuracy

**Evidence Required:**
- Screening record with application_version_id = 2
- No screening for version #3

**PASS Criteria:**
- Screening references version #2 (submitted)
- Version substitution prevented

**FAIL Criteria:**
- Screening created for version #3
- Wrong version in database

---

### SCREENING-HTTP-006: Authorized Staff Can Transition Screening to Completed

**Test ID:** SCREENING-HTTP-006

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- Screening exists in 'in_review' state
- User is staff with screening permission in program
- Staff member who created screening (or authorized staff member)

**Exact HTTP/Action:**
```
PUT /applications/{application}/screenings/{screening}
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "outcome": "ELIGIBLE",
  "rationale": "Applicant meets all eligibility criteria."
}
```

**Expected Authorization Result:**
- Backend authorizes via `$this->authorize('update', $screening)`
- User has `eligibility.screen` permission + program staff scope
- HTTP 200/302 successful

**Expected Business Result:**
- Screening status transitions: in_review → completed
- outcome set to ELIGIBLE
- rationale stored
- completed_at timestamp recorded
- Application status updated to lowercase outcome ('eligible')
- HTTP 302 redirect to screening show page

**Expected Database Result:**
```sql
SELECT * FROM screenings WHERE id = {screening_id}
```
- status = 'completed'
- outcome = 'ELIGIBLE'
- rationale = submitted text
- completed_at = current timestamp
- screened_by = original screener (unchanged)

```sql
SELECT * FROM applications WHERE id = {application_id}
```
- status = 'eligible' (updated from 'submitted' or 'screening')

**Security Reason:**
- Transitions require authorization and valid state
- Outcome and rationale are auditable decisions
- Timestamp proves when completed

**Evidence Required:**
- HTTP 302
- Screening status = completed
- Outcome set
- Application status updated
- completed_at timestamp present

**PASS Criteria:**
- Screening transitions to completed
- Outcome and rationale stored
- Application status updated
- Audit trail complete

**FAIL Criteria:**
- Screening remains in_review
- Outcome not stored
- Application status unchanged

---

### SCREENING-HTTP-007: Invalid Screening State Transition Rejected

**Test ID:** SCREENING-HTTP-007

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- Screening exists in 'completed' state (already finalized)
- User attempts to update it again
- User has permission

**Exact HTTP/Action:**
```
PUT /applications/{application}/screenings/{screening}
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "outcome": "INELIGIBLE",
  "rationale": "New reason"
}
```

**Expected Business Result:**
- Backend detects screening.status != 'in_review'
- HTTP 302 redirect with error: "Only in-review screenings can be completed"
- Screening not modified

**Expected Database Result:**
- Screening unchanged (status, outcome, rationale, completed_at all preserved)
- Application status unchanged

**Security Reason:**
- Immutability of completed decisions (no silent overwrites)
- Prevents audit trail corruption
- Ensures decisions are not retroactively changed

**Evidence Required:**
- HTTP 302
- Error message in flash
- Screening status remains 'completed'
- completed_at unchanged
- Original outcome preserved

**PASS Criteria:**
- Invalid state transition blocked
- Error returned
- No data modified

**FAIL Criteria:**
- Screening updated despite invalid state
- Decision changed/overwritten

---

### SCREENING-HTTP-008: Direct URL/Identifier Access Requires Authorization

**Test ID:** SCREENING-HTTP-008

**Actor:** Applicant user (not program staff)

**Account:** `qa-applicant@example.com`

**Preconditions:**
- Application belongs to applicant
- Screening record exists for application
- Applicant user does NOT have `eligibility.screen` permission
- URL is known or enumerable

**Exact HTTP/Action:**
```
GET /applications/{application}/screenings/{screening}
Headers: Authorization: Bearer {token}
```

**Expected Authorization Result:**
- Application view authorized (applicant is owner)
- Screening view authorization checked: `$this->authorize('view', $screening)`
- Authorization fails: user lacks `eligibility.screen` permission + scope
- HTTP 403 Forbidden

**Expected Business Result:**
- No screening data returned
- HTTP 403 response
- Applicant cannot directly access screening record via URL

**Expected Database Result:**
- No queries executed after authorization fails
- No data returned

**Security Reason:**
- Direct URL access cannot bypass policy authorization
- Ensures screening is staff-only, not public
- Applicant cannot view screening rationale or outcome (not yet shown in this phase)

**Evidence Required:**
- HTTP 403
- No screening data in response

**PASS Criteria:**
- Direct URL access blocked (403)
- Authorization enforced at backend

**FAIL Criteria:**
- Applicant can view screening (200)
- Data exposed

---

### SCREENING-HTTP-009: Objective Validation and Human Screening Remain Distinct Records

**Test ID:** SCREENING-HTTP-009

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- Application has submitted version
- Validation record exists for version (status = 'passed')
- Screening will be created for same version

**Exact HTTP/Action:**
```
POST /applications/{application}/validations  // validation already exists
POST /applications/{application}/screenings   // separate screening creation
```

**Expected Database Result:**
```sql
SELECT * FROM application_validations WHERE application_id = {id};
SELECT * FROM screenings WHERE application_id = {id};
```
- 1 validation record: status='passed', result={rules}, executed_at set
- 1 screening record: status='in_review', outcome=null initially, completed_at=null

- Two distinct tables, records not merged
- Screening has validation_id FK (reference, not replacement)

**Security Reason:**
- Maintains separation of concerns
- Objective results vs. human judgment are distinct
- Audit trail clarity (which rule passed, what did human decide)

**Evidence Required:**
- 2 separate records in different tables
- Distinct fields and state

**PASS Criteria:**
- Validation and Screening are separate records
- Distinct tables and IDs

**FAIL Criteria:**
- Records merged
- Screening overwrites validation
- Single table for both

---

### SCREENING-HTTP-010: Screening Actor and Timestamp Preserved (Audit Trail)

**Test ID:** SCREENING-HTTP-010

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- Screening created in `POST /screenings`
- Screening completed in `PUT /screenings/{screening}`
- Both operations by same or different staff

**Expected Database Result:**
- On creation:
  ```sql
  SELECT * FROM screenings WHERE id = {screening_id}
  ```
  - screened_by = {staff_user_id} (creator)
  - created_at = initial creation timestamp
  - status = 'in_review'
  - completed_at = NULL

- On completion (PUT):
  - screened_by = unchanged (original creator preserved)
  - created_at = unchanged
  - status = 'completed'
  - completed_at = update timestamp
  - Rationale and outcome stored
  - reopened_at/reopened_by = NULL (normal completion, not reopen)

**Security Reason:**
- Audit trail must show who made each decision
- Timestamps prove sequence of events
- Immutable fields (screened_by, created_at) cannot change
- History preserved for later review

**Evidence Required:**
- screened_by matches creating staff user ID
- created_at is initial timestamp
- completed_at is update timestamp (later than created_at)
- All fields immutable after creation
- Reopened_at/reopened_by still NULL for normal completion

**PASS Criteria:**
- Actor and timestamps correctly recorded
- Audit trail complete
- Immutability preserved

**FAIL Criteria:**
- Timestamps missing or wrong
- screened_by changed
- Audit trail incomplete

---

### SCREENING-HTTP-011: Completed Screening History Not Silently Overwritten

**Test ID:** SCREENING-HTTP-011

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Preconditions:**
- First screening created and completed: outcome='ELIGIBLE', rationale='First decision'
- Staff attempts to create second screening for same version

**Exact HTTP/Action:**
```
POST /applications/{application}/screenings
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "application_version_id": 1,
  "validation_id": null
}
```

**Expected Business Result:**
- Backend checks for existing completed screening: `WHERE application_version_id = {version} AND status = 'completed'`
- Check finds existing completed screening
- HTTP 302 redirect with error: "A completed screening already exists for this version"
- No new screening created

**Expected Database Result:**
- First screening remains unchanged
- outcome = 'ELIGIBLE' (not overwritten)
- No second screening record created
- History preserved

**Security Reason:**
- Prevents silent overwrite of decisions
- Requires explicit reopen/supersession for review/correction
- Maintains audit trail integrity

**Evidence Required:**
- HTTP 302 with error
- Only 1 screening record for version
- First outcome preserved

**PASS Criteria:**
- Duplicate screening rejected
- History preserved
- Error returned

**FAIL Criteria:**
- Second screening created
- First screening overwritten
- History lost

---

### SCREENING-HTTP-012: Screening Complete with INELIGIBLE Outcome

**Test ID:** SCREENING-HTTP-012

**Actor:** Program Staff user

**Account:** `qa-program-staff@example.com`

**Authentication:** Verified and authenticated

**Preconditions:**
- Screening in in_review state
- Validation shows rules failed (optional, staff makes independent judgment)

**Exact HTTP/Action:**
```
PUT /applications/{application}/screenings/{screening}
Headers: Authorization: Bearer {token}
Content-Type: application/json
Body: {
  "outcome": "INELIGIBLE",
  "rationale": "Applicant does not meet required criteria X."
}
```

**Expected Business Result:**
- Screening transitions to completed
- outcome = 'INELIGIBLE'
- Application status updated to 'ineligible'
- HTTP 302 redirect to screening show

**Expected Database Result:**
- screening.outcome = 'INELIGIBLE'
- application.status = 'ineligible'

**Security Reason:**
- Both ELIGIBLE and INELIGIBLE outcomes must be supported
- Application lifecycle reflects screening decision
- Audit trail captures outcome type

**Evidence Required:**
- Screening status = completed
- outcome = INELIGIBLE
- Application status = ineligible
- Rationale stored

**PASS Criteria:**
- INELIGIBLE outcome handled correctly
- Application status transitions properly

**FAIL Criteria:**
- outcome not stored as INELIGIBLE
- Application status wrong

---

## Summary

These 12 HTTP delivery tests ensure:

1. ✅ Authorization: Permission + program scope + record policy
2. ✅ Version Traceability: Exact submitted version preserved
3. ✅ State Management: Valid state transitions only
4. ✅ Audit Trail: Actor + timestamp immutable
5. ✅ Security Boundaries: Cross-program denied, applicant/judge denied where appropriate
6. ✅ Data Integrity: Distinct records, no overwrites, validation/screening separate

**All scenarios are specifications for future automated testing. None are executed in Task 018.**
