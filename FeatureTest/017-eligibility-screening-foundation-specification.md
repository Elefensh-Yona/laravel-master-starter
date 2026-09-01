# FeatureTest/017: Eligibility & Screening Foundation Specification

**Status:** Specification only. Not executed.  
**Based on:** EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md, EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md, decisions.md D-022, D-023

---

## Overview

This specification documents the approved test scenarios for the Task 017 Eligibility & Screening foundation. These tests verify the backend domain contracts for objective program validation and human Program Staff screening.

**Scope:** Foundation layer only. No judge assignment, conflict, evaluation, deliberation, or decision flows.

---

## ELIGIBILITY-001: Application Evaluation Against Program Eligibility Configuration

**Test ID:** ELIGIBILITY-001  
**Actor:** Program Staff with `eligibility.validate` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- User has active Program Staff membership in the target program
- Program has published eligibility rule configuration
- Application exists in the program with submitted version

**Program Context:**
- Program status: `published`
- Program has at least one enabled `ProgramEligibilityRule`

**Application Context:**
- Application status: `submitted`
- Application belongs to the target program
- Application has a current submitted version

**Version Context:**
- `ApplicationVersion` status: `submitted`
- Version has complete content payload
- Version is the current/submitted version of the application

**Action:**
1. Trigger objective validation logic for the application against the program's eligibility rules
2. Create an `ApplicationValidation` record with the result

**Expected Result:**
- Validation executes without error
- No exception is thrown
- A database record is created in `application_validations` table

**Expected Backend Result:**
- `ApplicationValidation` row exists with:
  - `program_id` = target program ID
  - `application_id` = target application ID
  - `application_version_id` = target submitted version ID
  - `status` = one of `'passed'`, `'failed'`, `'error'`
  - `result` = JSON structure with eligibility check results (if applicable)
  - `executed_at` = timestamp of execution
  - `executed_by` = user ID or NULL

**Expected Database Result:**
```sql
SELECT * FROM application_validations 
WHERE application_id = ? AND program_id = ? AND application_version_id = ?
-- returns exactly 1 row per version/program pair
```

**Security Reason:**
Program eligibility must be deterministic and auditable. Objective checks must be recorded as separate artifacts from human screening decisions per D-022.

**Evidence Requirement:**
- Validation record exists in `application_validations` table
- Record timestamps and actor are present
- Result payload is stored as JSON

**PASS Criteria:**
- ✓ Record created
- ✓ Foreign keys correct
- ✓ Status is valid (`passed`/`failed`/`error`)
- ✓ Timestamps are present and reasonable
- ✓ Record is tied to exact application version

**FAIL Criteria:**
- ✗ Exception during validation
- ✗ Record not created
- ✗ Foreign key constraint violation
- ✗ Timestamp is NULL or invalid
- ✗ Version reference is incorrect

---

## ELIGIBILITY-002: Objective Validation References Correct Application Version

**Test ID:** ELIGIBILITY-002  
**Actor:** Program Staff with `eligibility.validate` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Program has published eligibility rules
- Application has multiple versions (draft and submitted)
- Only the submitted version should be validated

**Program Context:**
- Program status: `published`

**Application Context:**
- Application has version 1 (draft)
- Application has version 2 (submitted, current)
- Application status: `submitted`

**Version Context:**
- Draft version: `ApplicationVersion` v1 with status `draft`
- Submitted version: `ApplicationVersion` v2 with status `submitted` (current)

**Action:**
1. Create an `ApplicationValidation` record
2. Specify `application_version_id` = version 2 ID (submitted)
3. Confirm validation does NOT reference or use draft version 1

**Expected Result:**
- Validation targets only the submitted version
- No draft version is included in the validation scope

**Expected Backend Result:**
- `ApplicationValidation` record has `application_version_id` = submitted version 2 ID
- Query for validation records by draft version ID returns empty
- Unique constraint ensures one validation per (version_id, program_id) pair

**Expected Database Result:**
```sql
SELECT COUNT(*) FROM application_validations 
WHERE application_id = ? AND application_version_id = (SELECT id FROM application_versions WHERE application_id = ? AND status = 'draft')
-- returns 0 rows (draft is never validated alone)

SELECT * FROM application_validations 
WHERE application_version_id = (SELECT id FROM application_versions WHERE application_id = ? AND status = 'submitted')
-- returns 1+ rows (current submitted version may have validation record)
```

**Security Reason:**
Only submitted, immutable versions may be evaluated. Draft versions are works-in-progress and must not pollute validation history.

**Evidence Requirement:**
- `application_version_id` FK on validation record points to submitted version
- Draft version record exists but is never referenced by validation record
- Version status is `submitted` at time of validation

**PASS Criteria:**
- ✓ Validation links to submitted version only
- ✓ Draft version is never included
- ✓ `application_version_id` FK is correct
- ✓ Version status at validation time is `submitted`

**FAIL Criteria:**
- ✗ Validation links to draft version
- ✗ Draft version is referenced in result/reasoning
- ✗ FK points to wrong version
- ✗ Validation runs against `current_version_id` pointer rather than exact version

---

## ELIGIBILITY-003: Eligibility Rule Relationship is Valid

**Test ID:** ELIGIBILITY-003  
**Actor:** Program Staff with `eligibility.validate` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Program has at least one `ProgramEligibilityRule`
- Rule is enabled (`is_enabled = true`)
- Rule belongs to the same program as the application

**Program Context:**
- Program status: `published`
- Program has `ProgramEligibilityRule` records with:
  - `program_id` = target program
  - `is_enabled` = true
  - `rule_type` in `('boolean', 'range', 'list', ...)`

**Application Context:**
- Application belongs to target program

**Version Context:**
- Application version has submitted status

**Action:**
1. Query `ProgramEligibilityRule` records for the program
2. Confirm all enabled rules have valid configuration (not NULL, valid JSON, etc.)
3. Create an `ApplicationValidation` that references/considers these rules

**Expected Result:**
- Eligible rules are found and loaded
- Rule configuration is valid JSON
- Validation result includes information about which rules passed/failed

**Expected Backend Result:**
- `ProgramEligibilityRule` records exist with correct `program_id`
- `is_enabled = true` for active rules
- `configuration` column contains valid JSON (parseable)
- `ApplicationValidation` record exists and may reference the rule IDs or keys in the result payload

**Expected Database Result:**
```sql
SELECT * FROM program_eligibility_rules 
WHERE program_id = ? AND is_enabled = true
-- returns 1+ rows with valid JSON in configuration column

SELECT * FROM application_validations 
WHERE program_id = ? AND application_id = ?
-- result payload may reference rule keys from the rules table
```

**Security Reason:**
Eligibility rules must be configuration-driven and auditable per program. Validation must reference the exact rules that were active at time of evaluation.

**Evidence Requirement:**
- Rules exist in `program_eligibility_rules` table
- Rules belong to the correct program
- Configuration is valid and deserializable
- Validation result traces back to these rules

**PASS Criteria:**
- ✓ Rules are found for the program
- ✓ Rules are enabled
- ✓ Configuration is valid JSON
- ✓ Validation references or traces to the rules
- ✓ Rule position/key is preserved in result

**FAIL Criteria:**
- ✗ No rules found for program
- ✗ Disabled rules included in validation
- ✗ Configuration is invalid JSON
- ✗ Validation does not reference rules
- ✗ Rule IDs/keys are corrupted or missing

---

## ELIGIBILITY-004: Objective Validation Result is Auditable

**Test ID:** ELIGIBILITY-004  
**Actor:** Program Staff with `eligibility.validate` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Application has submitted version
- Validation record is created

**Program Context:**
- Program is same as application program

**Application Context:**
- Application has submitted status

**Version Context:**
- Submitted version exists

**Action:**
1. Create an `ApplicationValidation` record
2. Populate `executed_at` timestamp and `executed_by` user ID
3. Store the result payload
4. Query the record to retrieve all audit fields

**Expected Result:**
- Timestamp is recorded
- Actor (user who triggered validation) is recorded
- Result snapshot is preserved as immutable

**Expected Backend Result:**
- `ApplicationValidation` record has:
  - `executed_at` = precise timestamp with timezone
  - `executed_by` = user ID (or NULL if system-triggered)
  - `created_at` = record creation timestamp
  - `updated_at` = same as created_at (immutable after creation)
  - `result` = JSON snapshot of validation output

**Expected Database Result:**
```sql
SELECT id, executed_at, executed_by, status, result, created_at, updated_at
FROM application_validations 
WHERE id = ?
-- All timestamps present, no NULL values in required fields
-- executed_by IS NOT NULL (actor recorded)
-- created_at = updated_at (no mutation)
```

**Security Reason:**
Validation results must be fully auditable. The exact time, actor, and result snapshot are evidence for compliance and dispute resolution.

**Evidence Requirement:**
- Timestamp is precise (not just date, includes time and timezone)
- User ID is recorded (not anonymous)
- Result payload is captured in JSON
- No updates to the record after creation

**PASS Criteria:**
- ✓ `executed_at` is present and reasonable
- ✓ `executed_by` is NOT NULL and valid user ID
- ✓ `created_at` and `updated_at` are identical (immutable)
- ✓ `result` JSON is valid and complete
- ✓ All audit fields populated

**FAIL Criteria:**
- ✗ `executed_at` is NULL or invalid
- ✗ `executed_by` is NULL (actor not recorded)
- ✗ `created_at` differs from `updated_at` (record was mutated)
- ✗ `result` is NULL or invalid JSON
- ✗ Timestamp is before or after reasonable bounds

---

## SCREENING-001: Authorized Program Staff Can Perform Screening Action

**Test ID:** SCREENING-001  
**Actor:** Program Staff with `eligibility.screen` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- User has active Program Staff membership in the target program
- User has `eligibility.screen` permission
- Application is in `screening` status
- Application has submitted version
- An `ApplicationValidation` record exists (may be optional, but preferred)

**Program Context:**
- Program status: `published`
- User is active member of this program with `capability = 'program_staff'`
- Program scope includes the application

**Application Context:**
- Application status: `submitted` or `screening` (ready for screening)
- Application belongs to the user's program

**Version Context:**
- Current submitted version exists and is linked to the application

**Action:**
1. As Program Staff, create a `Screening` record
2. Set `status = 'in_review'` initially
3. Add `screened_by = <staff user ID>`
4. Set `outcome = 'ELIGIBLE'` or `'INELIGIBLE'`
5. Add `rationale = <reason text>`
6. Transition to `status = 'completed'`

**Expected Result:**
- Screening record is created without error
- Record is visible to the staff member
- Record is persisted in database

**Expected Backend Result:**
- `Screening` row exists with:
  - `program_id` = target program ID
  - `application_id` = target application ID
  - `application_version_id` = submitted version ID
  - `screened_by` = staff user ID
  - `status` = `'completed'`
  - `outcome` = `'ELIGIBLE'` or `'INELIGIBLE'`
  - `rationale` = text explanation
  - `completed_at` = timestamp

**Expected Database Result:**
```sql
SELECT * FROM screenings 
WHERE application_id = ? AND program_id = ? AND status = 'completed'
-- returns exactly 1 row (one completed screening per application version)

SELECT * FROM screenings 
WHERE screened_by = ? AND program_id = ? AND status = 'completed'
-- returns 1+ rows (audit trail of staff actions)
```

**Security Reason:**
Program Staff must be authorized to record eligibility screening decisions per D-023. Human screening remains under explicit authorization and governance.

**Evidence Requirement:**
- `Screening` record exists in database
- `screened_by` field matches the staff user
- `rationale` is populated (required field)
- `outcome` is valid (`ELIGIBLE` or `INELIGIBLE`)
- Record is timestamped

**PASS Criteria:**
- ✓ Record created successfully
- ✓ All required fields populated
- ✓ `screened_by` is correct user
- ✓ `outcome` is valid enum value
- ✓ `status` transitions to `completed`
- ✓ `rationale` is not empty

**FAIL Criteria:**
- ✗ Authorization check fails (user lacks scope or permission)
- ✗ Record not created
- ✗ Required fields left NULL
- ✗ `outcome` is invalid
- ✗ Status remains `in_review` (incomplete)
- ✗ `rationale` is empty or NULL

---

## SCREENING-002: Applicant Cannot Perform Staff Screening

**Test ID:** SCREENING-002  
**Actor:** Applicant  
**Account:** `qa-applicant@example.com`  
**Preconditions:**
- User is an applicant (has `applicant` capability or no Program Staff membership)
- Application belongs to this applicant
- Application is in `screening` status

**Program Context:**
- Program is published
- User is NOT a Program Staff member (or has only `applicant` capability)
- User is NOT assigned as a Judge

**Application Context:**
- Application is owned by the applicant
- Application status: `screening`

**Version Context:**
- Submitted version exists

**Action:**
1. As applicant, attempt to create a `Screening` record
2. Attempt to set `status` and `outcome`
3. Attempt to call screening policy/authorization

**Expected Result:**
- Action is rejected before database write
- Exception is raised or policy denies access
- No record is created

**Expected Backend Result:**
- `ApplicationPolicy::screen()` or `ScreeningPolicy::create()` returns `false`
- Policy check fails due to:
  - User lacking `eligibility.screen` permission, OR
  - User lacking `program_staff` capability in the program, OR
  - Both
- No `Screening` record is inserted

**Expected Database Result:**
```sql
SELECT COUNT(*) FROM screenings 
WHERE application_id = ? AND screened_by = ? AND status = 'completed'
-- returns 0 (applicant never created a screening record)
```

**Security Reason:**
Screening is a staff-only action. Applicants must not be able to influence their own eligibility determination per D-023 and the broader authorization matrix.

**Evidence Requirement:**
- Authorization check enforces the boundary
- Policy method returns `false` or throws exception
- Database has no record created by the applicant
- Activity log records the denied attempt

**PASS Criteria:**
- ✓ Authorization check fails
- ✓ Policy denies access
- ✓ No database record created
- ✓ Exception or error response returned
- ✓ Audit trail records the attempted action

**FAIL Criteria:**
- ✗ Record is created (authorization bypass)
- ✗ Applicant successfully updates screening
- ✗ No policy check performed
- ✗ No exception raised on denial
- ✗ Database record exists with applicant as screener

---

## SCREENING-003: Judge Cannot Perform Staff Screening Unless Explicitly Authorized

**Test ID:** SCREENING-003  
**Actor:** Judge (user with Judge role/capability)  
**Account:** `qa-judge@example.com`  
**Preconditions:**
- User has active Judge membership in the target program
- User does NOT have `eligibility.screen` permission
- User does NOT have active Program Staff membership
- Application is in `screening` status

**Program Context:**
- Program is published
- User is NOT in Program Staff scope
- User is assigned as Judge (future step, but assumed for this test)

**Application Context:**
- Application status: `screening`

**Version Context:**
- Submitted version exists

**Action:**
1. As Judge, attempt to create a `Screening` record
2. Attempt to call screening authorization/policy

**Expected Result:**
- Action is rejected
- Policy denies access
- No record is created

**Expected Backend Result:**
- `ScreeningPolicy::update()` returns `false`
- Policy check fails because:
  - User lacks `eligibility.screen` permission, OR
  - User lacks active Program Staff membership
- No `Screening` record created

**Expected Database Result:**
```sql
SELECT COUNT(*) FROM screenings 
WHERE application_id = ? AND screened_by = ?
-- returns 0 if judge was not explicitly authorized to screen
```

**Security Reason:**
Screening is an explicit Program Staff function. Judge role is separate per the architecture. A Judge may not screen an application unless they also hold active Program Staff membership with screening capability for that program.

**Evidence Requirement:**
- Policy denies access based on lacking staff scope
- Role separation is enforced (Judge ≠ Staff without explicit grant)
- No database record created by judge without staff authorization

**PASS Criteria:**
- ✓ Authorization denied
- ✓ Policy returns `false`
- ✓ No record created
- ✓ Role separation is enforced
- ✓ Audit records denial

**FAIL Criteria:**
- ✗ Judge successfully creates screening record
- ✗ Policy returns `true` for Judge without staff scope
- ✗ Database record created by unauthorized Judge
- ✗ Role separation is bypassed

---

## SCREENING-004: Cross-Program Program Staff Access is Denied

**Test ID:** SCREENING-004  
**Actor:** Program Staff in Program A  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- User has active Program Staff membership in Program A (not Program B)
- User has `eligibility.screen` permission globally
- Application belongs to Program B
- Application is in `screening` status

**Program Context:**
- User's membership: Program A, capability `program_staff`, status `active`
- Application's program: Program B
- User has no membership in Program B

**Application Context:**
- Application status: `screening`
- Application `program_id` = Program B ID

**Version Context:**
- Submitted version exists

**Action:**
1. As staff from Program A, attempt to view `Screening` records for Program B application
2. Attempt to create a screening record for Program B

**Expected Result:**
- View access is denied
- Create access is denied
- No record is visible or modifiable

**Expected Backend Result:**
- `ScreeningPolicy::view()` returns `false` (no active membership in Program B)
- `ScreeningPolicy::update()` returns `false`
- Query by program_id and user membership returns no results

**Expected Database Result:**
```sql
SELECT * FROM screenings 
WHERE application_id = ? AND program_id = ?
WHERE screened_by = <staff user from Program A>
-- returns 0 rows (staff is not authorized for Program B)

SELECT COUNT(*) FROM program_memberships 
WHERE user_id = ? AND program_id = ?  -- Program B ID
-- returns 0 (no membership in Program B)
```

**Security Reason:**
Program scope is a mandatory authorization boundary. Staff from one program must not access, view, or influence applications in other programs per the layered authorization model.

**Evidence Requirement:**
- Policy check examines `InteractsWithProgramScope` trait
- Active membership in the *target* program is required
- Membership in a different program does not grant access

**PASS Criteria:**
- ✓ Policy denies access
- ✓ `InteractsWithProgramScope` check fails
- ✓ No record is visible to out-of-scope staff
- ✓ Create/update attempt is blocked
- ✓ Audit trail records denied access

**FAIL Criteria:**
- ✗ Out-of-scope staff can view program B screening
- ✗ Out-of-scope staff can modify screening
- ✗ Policy does not check program scope
- ✗ Membership in one program grants access to all

---

## SCREENING-005: Screening References Exact Application Version

**Test ID:** SCREENING-005  
**Actor:** Program Staff with `eligibility.screen` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Application has multiple submitted versions (v1 submitted earlier, v2 submitted later)
- Application's `current_version_id` now points to v2
- Screening record must reference the exact version being screened

**Program Context:**
- Program status: `published`

**Application Context:**
- Application status: `submitted` (or `screening`)
- Application has version 1 submitted at T1
- Application has version 2 submitted at T2 (current)

**Version Context:**
- `ApplicationVersion` v1: status `submitted`, submitted_at = T1
- `ApplicationVersion` v2: status `submitted`, submitted_at = T2 (current)

**Action:**
1. Create a `Screening` record referencing version 1 (earlier submission)
2. Later, create a different `Screening` record referencing version 2 (later submission)
3. Confirm each screening record is tied to its exact version

**Expected Result:**
- Two distinct `Screening` records exist
- Each references its exact version_id
- Histories remain separate

**Expected Backend Result:**
- `Screening` row 1: `application_version_id` = version 1 ID
- `Screening` row 2: `application_version_id` = version 2 ID
- Unique constraint allows multiple screening records per application (one per version)

**Expected Database Result:**
```sql
SELECT COUNT(*) FROM screenings 
WHERE application_id = ? AND application_version_id = <v1 id>
-- returns 1 (or 0..1 depending on whether v1 was screened)

SELECT COUNT(*) FROM screenings 
WHERE application_id = ? AND application_version_id = <v2 id>
-- returns 1 (or 0..1 depending on whether v2 was screened)

SELECT COUNT(*) FROM screenings 
WHERE application_id = ?
-- may return 0, 1, or 2 depending on revision history
```

**Security Reason:**
Revisions create new versions. Screening history must track which version was screened at which time. Evaluations later reference the exact version, so screening must too.

**Evidence Requirement:**
- `application_version_id` on each screening record is correct
- Multiple screening records can exist for one application (one per version)
- Screening of an earlier version does not overwrite screening of a later version

**PASS Criteria:**
- ✓ Screening v1 has `application_version_id` = v1 ID
- ✓ Screening v2 has `application_version_id` = v2 ID
- ✓ Both records coexist in database
- ✓ No cross-version contamination
- ✓ Version reference is immutable

**FAIL Criteria:**
- ✗ Screening references wrong version
- ✗ Version ID is NULL or corrupted
- ✗ Multiple screenings for same version (not history, just duplication)
- ✗ Screening of v2 overwrites v1's screening record

---

## SCREENING-006: Historical Screening Information is Not Silently Overwritten

**Test ID:** SCREENING-006  
**Actor:** Program Staff with `eligibility.screen` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Application has a completed screening record (outcome `ELIGIBLE`, rationale recorded)
- Staff wishes to correct or revise the screening
- Revision must use a new/successor record, not mutation

**Program Context:**
- Program status: `published`

**Application Context:**
- Application status: `eligible` (result of first screening)
- Application has a completed screening record

**Version Context:**
- Application version is submitted

**Action:**
1. Retrieve the existing completed screening record (read-only)
2. Attempt to update the rationale or outcome on the existing record (should fail)
3. Create a new screening record with `reopened_at` and `reopened_by` fields to supersede

**Expected Result:**
- Original record remains unchanged
- New record is created with reopen metadata
- Both records remain in history

**Expected Backend Result:**
- Update of existing completed screening is blocked (immutability)
- New `Screening` row is inserted with:
  - `reopened_at` = current timestamp
  - `reopened_by` = staff user ID
  - `reopen_reason` = text explaining the change
  - Previous record's ID may be referenced (if schema includes predecessor link)

**Expected Database Result:**
```sql
SELECT * FROM screenings 
WHERE application_id = ? AND application_version_id = ?
ORDER BY completed_at DESC
-- returns 2+ rows (original and any reopenings)

-- Original record unchanged:
SELECT * FROM screenings WHERE id = <original_id>
-- outcome, rationale, completed_at all match original values
-- updated_at may equal created_at (no mutation) OR may differ (immutable history marker)
```

**Security Reason:**
Completed screening results are immutable governance artifacts. Corrections must create historical audit trail, not silent overwrites, per the lifecycle specification.

**Evidence Requirement:**
- Original completed record is not mutated
- New reopening record is created instead
- Timestamps and actors of both records are preserved
- Reopen reason is recorded

**PASS Criteria:**
- ✓ Original record is read-only / update fails
- ✓ New record with reopen metadata is created
- ✓ Both records exist in database
- ✓ `reopened_by` and `reopened_at` are populated
- ✓ Reopen reason is documented
- ✓ No silently overwritten values

**FAIL Criteria:**
- ✗ Existing record is updated in place (outcome/rationale changed)
- ✗ No new record created
- ✗ Old record is deleted (history lost)
- ✗ `reopened_at` or `reopened_by` is missing
- ✗ Only one screening record remains (earlier one lost)

---

## SCREENING-007: Screening Actor and Timestamp Are Preserved

**Test ID:** SCREENING-007  
**Actor:** Program Staff with `eligibility.screen` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- User is authenticated
- Application is ready for screening

**Program Context:**
- Program published

**Application Context:**
- Application in `screening` status

**Version Context:**
- Submitted version exists

**Action:**
1. As named staff user, create a screening record
2. Populate `screened_by` with the current user ID
3. Record `completed_at` timestamp at time of completion

**Expected Result:**
- Actor is recorded (not anonymous)
- Timestamp is precise

**Expected Backend Result:**
- `Screening` row has:
  - `screened_by` = user ID of the staff member (not NULL, not 0, not generic)
  - `completed_at` = precise timestamp with timezone info

**Expected Database Result:**
```sql
SELECT screened_by, completed_at FROM screenings WHERE id = ?
-- screened_by is NOT NULL and is valid user ID
-- completed_at is NOT NULL and is ISO 8601 / RFC 3339 timestamp with timezone
-- completed_at is recent (within seconds of query time)
```

**Security Reason:**
Full audit trail requires identifying the person responsible for each decision. Timestamps enable chronological reconstruction of events.

**Evidence Requirement:**
- User ID is recorded in `screened_by` column
- Timestamp is precise (not just date, includes time component)
- Both fields are required (NOT NULL)

**PASS Criteria:**
- ✓ `screened_by` is NOT NULL
- ✓ `screened_by` is valid user ID
- ✓ `completed_at` is NOT NULL
- ✓ `completed_at` includes time and timezone
- ✓ Timestamp is reasonable (not in future, not ancient)

**FAIL Criteria:**
- ✗ `screened_by` is NULL (actor unknown)
- ✗ `screened_by` is 0 or generic ID (not specific user)
- ✗ `completed_at` is NULL (no timestamp)
- ✗ Timestamp is invalid or in future

---

## SCREENING-008: Invalid Screening State Transition is Rejected

**Test ID:** SCREENING-008  
**Actor:** Program Staff with `eligibility.screen` permission  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Screening record in `completed` status already exists
- Attempt to transition to an invalid state

**Program Context:**
- Program published

**Application Context:**
- Application status matches a screened state

**Version Context:**
- Submitted version exists

**Action:**
1. Retrieve a completed screening record
2. Attempt to set `status` to an invalid value (e.g., `'rejected'`, `'archived'`, `'pending'`)
3. Attempt to set `outcome` to an invalid value (e.g., `'WAITLISTED'`, `NULL` on completed record)

**Expected Result:**
- Database constraint rejects invalid enum values
- Application-level validation rejects the transition
- Record is not updated

**Expected Backend Result:**
- Check constraint on `screenings.status` prevents invalid values
- Check constraint on `screenings.outcome` enforces `'ELIGIBLE'` or `'INELIGIBLE'` (or NULL for in_review)
- Application model casts/validates enum before save

**Expected Database Result:**
```sql
-- Attempt to insert invalid status
INSERT INTO screenings (status, ...) VALUES ('invalid_status', ...)
-- ERROR: new row for relation "screenings" violates check constraint "screenings_status_check"

-- Attempt to insert invalid outcome
INSERT INTO screenings (outcome, ...) VALUES ('WAITLISTED', ...)
-- ERROR: new row for relation "screenings" violates check constraint "screenings_outcome_check"
```

**Security Reason:**
State machine integrity must be enforced at the database level. Invalid state values are data corruption and must be prevented.

**Evidence Requirement:**
- Database constraints exist and are active
- Application validation layer rejects invalid values
- No record is created or updated with invalid state

**PASS Criteria:**
- ✓ Database constraint exists and is active
- ✓ Invalid state is rejected before insert/update
- ✓ Meaningful error message returned
- ✓ Application validation also rejects invalid state
- ✓ Record is not modified

**FAIL Criteria:**
- ✗ Invalid state value is accepted
- ✗ No database constraint exists
- ✗ Record is created with invalid state
- ✗ No application-level validation

---

## SCREENING-009: Direct Identifier/URL Access Cannot Bypass Screening Authorization

**Test ID:** SCREENING-009  
**Actor:** User without `eligibility.screen` permission  
**Account:** applicant or out-of-scope staff  
**Preconditions:**
- Screening record exists in database
- Unauthorized user knows or guesses the screening record ID
- User attempts direct database/URL access without policy check

**Program Context:**
- Unauthorized user is not in the screening's program scope

**Application Context:**
- Application belongs to a program user is not scoped to

**Version Context:**
- Submitted version exists

**Action:**
1. Unauthorized user attempts to query `Screening` by ID
2. If REST API exists, attempt direct /api/screenings/{id} request
3. If policy is used, attempt to retrieve without passing policy gate

**Expected Result:**
- Direct access is denied
- Policy check is enforced
- Record is not returned

**Expected Backend Result:**
- `Screening` model uses policy gates via `Gate::authorize()` or policy method calls
- Query by ID alone does not bypass authorization
- Authorization check examines user's scope and permission

**Expected Database Result:**
```sql
-- Database contains the record:
SELECT * FROM screenings WHERE id = ?
-- returns 1 row (record exists)

-- But authorization prevents access:
// Pseudo-code: if (!auth()->user()->can('view', screening)) { return 403; }
```

**Security Reason:**
Direct ID/URL manipulation is a common authorization bypass. Policy gates must wrap all record access, not rely on route middleware alone.

**Evidence Requirement:**
- `Screening` model has protected access methods
- Policy is checked on every access (view, update, delete)
- ID alone does not grant access

**PASS Criteria:**
- ✓ Policy check is enforced
- ✓ Direct ID access is denied
- ✓ 403/Unauthorized error returned
- ✓ No record data leaked
- ✓ Audit trail records denied access

**FAIL Criteria:**
- ✗ Record is returned without policy check
- ✗ ID-only access bypasses authorization
- ✗ Record data is exposed to unauthorized user
- ✗ No policy gate on view/retrieve

---

## SCREENING-010: Objective Eligibility Validation and Human Screening Remain Separate Records

**Test ID:** SCREENING-010  
**Actor:** Program Staff with both `eligibility.validate` and `eligibility.screen` permissions  
**Account:** `qa-program-staff@example.com`  
**Preconditions:**
- Application has submitted version
- Both `ApplicationValidation` and `Screening` concepts are present

**Program Context:**
- Program published with eligibility rules

**Application Context:**
- Application in `submitted` or `screening` status

**Version Context:**
- Submitted version exists

**Action:**
1. Create an `ApplicationValidation` record (objective check)
2. Create a `Screening` record (human decision)
3. Query both tables
4. Confirm they are separate records with separate schemas and purposes

**Expected Result:**
- `ApplicationValidation` table has validation-specific columns
- `Screening` table has screening-specific columns
- No column duplication between them
- Both can coexist for the same application version

**Expected Backend Result:**
- `application_validations` table exists with columns: `status` (passed/failed/error), `result`, `executed_at`, `executed_by`, `failure_reason`
- `screenings` table exists with columns: `status` (in_review/completed), `outcome` (ELIGIBLE/INELIGIBLE), `screened_by`, `completed_at`, `rationale`
- No single table combining both concepts

**Expected Database Result:**
```sql
-- Both tables exist:
SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'
-- includes 'application_validations' and 'screenings'

-- Both records can coexist:
SELECT COUNT(*) FROM application_validations WHERE application_id = ? AND application_version_id = ?
-- may return 1
SELECT COUNT(*) FROM screenings WHERE application_id = ? AND application_version_id = ?
-- may return 1
-- Both returning 1 is valid; they are independent concepts
```

**Security Reason:**
Per D-023, objective validation and human screening are separate concepts. Validation is never a final decision; screening is human-controlled. Keeping them as separate records prevents confusion and ensures no automation can substitute for human judgment.

**Evidence Requirement:**
- Two distinct tables with appropriate columns
- No "validation" column on screenings table that could bypass human decision
- No "screening" column on validations table
- Foreign key relationship optional but not required (independence)

**PASS Criteria:**
- ✓ Both tables exist
- ✓ Tables have distinct schema
- ✓ Both records can coexist for same version
- ✓ No conceptual overlap/confusion
- ✓ Validation does not predetermine screening

**FAIL Criteria:**
- ✗ Single combined table for both concepts
- ✗ Validation result automatically sets screening outcome
- ✗ Only one record type exists
- ✗ Tables share column namespaces confusingly

---

## Summary of Test Scenarios

| ID | Category | Actor | Permission | Expected | Security Focus |
|----|----------|-------|-----------|----------|-----------------|
| ELIGIBILITY-001 | Foundation | Staff | `eligibility.validate` | Record created | Deterministic validation |
| ELIGIBILITY-002 | Version Integrity | Staff | `eligibility.validate` | Correct version linked | Immutable versions only |
| ELIGIBILITY-003 | Configuration | Staff | `eligibility.validate` | Rules valid | Rule-driven validation |
| ELIGIBILITY-004 | Audit | Staff | `eligibility.validate` | Timestamp + actor | Full audit trail |
| SCREENING-001 | Foundation | Staff | `eligibility.screen` | Record created | Authorized action |
| SCREENING-002 | Authorization | Applicant | (none) | Denied | Role separation |
| SCREENING-003 | Authorization | Judge | (none) | Denied | Judge ≠ Staff |
| SCREENING-004 | Scope | Staff A | `eligibility.screen` | Denied for Program B | Program boundary |
| SCREENING-005 | Version Integrity | Staff | `eligibility.screen` | Correct version | Exact version link |
| SCREENING-006 | Immutability | Staff | `eligibility.screen` | No silent overwrite | History preservation |
| SCREENING-007 | Audit | Staff | `eligibility.screen` | Actor + timestamp | Accountability |
| SCREENING-008 | Constraints | Staff | `eligibility.screen` | Invalid rejected | Data integrity |
| SCREENING-009 | Authorization | Unauthorized | (none) | Denied at view | Policy enforcement |
| SCREENING-010 | Separation | Staff | both | Two tables | Decision independence |

---

## Testing Policy

These scenarios are **specifications only**. They document the approved contract but are not executed as automated tests in this task.

Future test execution will follow the project policy:
- FeatureTest specifications are approved first
- Focused automated tests are written only when genuinely necessary to diagnose a blocker
- Broad regression testing is deferred to appropriate phases

No tests are run for Task 017A.
