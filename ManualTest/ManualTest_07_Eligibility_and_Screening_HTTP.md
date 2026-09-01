# ManualTest/ManualTest_07_Eligibility_and_Screening_HTTP

**Status:** Specification only. NOT RUN.  
**Purpose:** Document future human QA scenarios for HTTP/Inertia delivery of eligibility & screening.  
**Date Created:** 2026-09-01  
**Scope:** HTTP endpoints, user interface integration, browser behavior, end-to-end workflows

---

## Overview

This manual test plan specifies scenarios for future human QA verification of the HTTP/Inertia delivery layer for eligibility and screening.

**Key Points:**
- These tests are NOT RUN as part of Task 018
- No browser testing has occurred
- No test results are marked PASS or FAIL
- The scenarios provide guidance for future QA when UI forms are implemented

---

## Preconditions for All Scenarios

Before any manual QA can occur:

1. **Local development environment is fully configured:**
   - Laravel dev server running on localhost:8000
   - PostgreSQL `development` database populated
   - Fortify email verification configured
   - Inertia.js + Vue frontend loaded
   - HTTP endpoints for validation/screening functional

2. **QA Accounts exist and are email-verified:**
   - `admin@example.com` (Super Admin)
   - `qa-program-staff@example.com` (Program Staff with eligibility permissions)
   - `qa-program-staff-b@example.com` (Program Staff in different program)
   - `qa-applicant@example.com` (Applicant/Application owner)
   - `qa-judge@example.com` (Judge role, if applicable)

3. **Test Data exists:**
   - At least 2 published programs (Program A, Program B)
   - Program A has eligibility rules configured
   - At least 2 applications in Program A
   - Application 1: submitted version, no validation/screening yet
   - Application 2: submitted version with validation + screening already done
   - Program Staff members assigned correctly to their programs

4. **HTTP Endpoints Available:**
   - Validation endpoints: GET/POST routes functional
   - Screening endpoints: GET/POST/PUT routes functional
   - Inertia rendering properly
   - Redirect/flash message system working

5. **No Destructive Database Operations:**
   - No database resets during manual testing
   - No data deletion except through governed UI actions
   - Testing uses existing seeded data or creates new test records

---

## Manual Test Scenarios (All NOT RUN)

### Manual Test 07.01: Program Staff Can Access Validation History Page

**Objective:** Verify that authorized program staff can view the validation history for an application.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to a submitted application
3. Access the "Eligibility Validations" section or tab
4. Observe the validation history page

**Expected Observations:**
- Page loads without error (HTTP 200)
- Validation history list is displayed
- Each validation shows: execution status, timestamp, executor name
- Validations sorted by most recent first
- "Run Validation" or similar button visible (if user has permission)
- Application info (ID, status) shown for context

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.02: Validation Execution Triggered and Result Displayed

**Objective:** Verify that staff can trigger validation and see results immediately.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an application with submitted version
3. Access Eligibility Validations page
4. Click "Run Validation" button
5. Observe response and result display
6. Navigate back to validation history

**Expected Observations:**
- Button click triggers HTTP POST request
- Validation executes without error
- Result page displays: status (passed/failed), timestamp, rules evaluated
- New validation appears in history on reload
- Validation timestamp is accurate
- Rule results shown (if applicable to UI)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.03: Validation Failure Displays Reason

**Objective:** Verify that validation failures show clear reason/messaging.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an application that fails eligibility
3. Trigger validation (or view existing failed validation)
4. Observe result page

**Expected Observations:**
- Validation status shows as "failed"
- Failure reason is displayed to staff member
- Reason is readable and diagnostic (not cryptic)
- Rule details may show which specific rules failed

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.04: Applicant Cannot Access Validation Endpoint

**Objective:** Verify that applicant users cannot navigate to validation pages.

**Account:** `qa-applicant@example.com`

**Steps:**
1. Log in as Applicant
2. Navigate to own application
3. Attempt to access `/applications/{id}/eligibility-validations` (direct URL)

**Expected Observations:**
- Page either not shown in navigation, or
- Direct URL access results in 403 Forbidden
- Applicant cannot see validation history
- Validation section not visible in application detail page (UI decision for later)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.05: Program Staff Can Access Screening Page

**Objective:** Verify that authorized program staff can navigate to screening.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an application with submitted version
3. Access the "Screening" section or tab
4. Observe the screening page/form

**Expected Observations:**
- Screening page loads (HTTP 200)
- Application info displayed
- Existing screenings listed (if any)
- "Create Screening" or "Start Screening" button visible
- Latest validation info shown for context (if available)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.06: Staff Creates Screening Record (in_review state)

**Objective:** Verify that staff can create a new screening record.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an application (no completed screening yet)
3. Access Screening page
4. Click "Create Screening" or similar button
5. Select submitted version (if dropdown appears)
6. Confirm creation
7. Observe result

**Expected Observations:**
- Screening record created successfully
- Page shows screening in "in_review" state
- Form to enter outcome/rationale appears (or separate step)
- Screening timestamp shows creation time
- Screener name shows (staff member who created it)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.07: Staff Completes Screening with ELIGIBLE Outcome

**Objective:** Verify that staff can enter eligibility outcome and rationale.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an in-review screening
3. Fill in form:
   - Outcome: Select "ELIGIBLE"
   - Rationale: Enter text explaining decision
4. Click "Complete Screening" or "Save"
5. Observe result and application status update

**Expected Observations:**
- Form submits successfully
- Screening status changes from "in_review" to "completed"
- Outcome recorded as "ELIGIBLE"
- Rationale text preserved
- Application status updates to "eligible"
- Timestamp reflects completion time
- Success message shown

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.08: Staff Completes Screening with INELIGIBLE Outcome

**Objective:** Verify that both ELIGIBLE and INELIGIBLE outcomes are supported.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Navigate to an in-review screening
3. Fill in form:
   - Outcome: Select "INELIGIBLE"
   - Rationale: Enter text explaining decision
4. Click "Complete Screening"
5. Observe result

**Expected Observations:**
- Form accepts INELIGIBLE outcome
- Screening status = "completed"
- outcome = "INELIGIBLE"
- Application status updates to "ineligible"
- Rationale stored and displayed

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.09: Validation Result Version Traceability

**Objective:** Verify that validation results reference the correct application version.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Create an application with multiple submitted versions
3. Run validation on version 1
4. Create new revision, submit as version 2
5. Run validation on version 2
6. View validation history

**Expected Observations:**
- Both validations listed in history
- Each validation shows its associated version number
- Version 1 validation references version 1
- Version 2 validation references version 2
- No version substitution or mixing

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.10: Screening Result Version Traceability

**Objective:** Verify that screening decisions reference exact submitted version.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Application has versions 1 (draft), 2 (submitted), 3 (draft, current)
3. Create and complete screening for version 2
4. View screening detail

**Expected Observations:**
- Screening shows "Version 2" or similar
- screening.application_version_id = 2
- Current draft version (3) not substituted
- Version traceability accurate in database

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.11: Applicant Denied Direct Access to Screening URL

**Objective:** Verify that applicants cannot access screening via direct URL.

**Account:** `qa-applicant@example.com`

**Steps:**
1. Log in as Applicant
2. Obtain or guess screening ID
3. Attempt to navigate to `/applications/{app}/screenings/{screening}`

**Expected Observations:**
- HTTP 403 Forbidden or redirect to unauthorized page
- Screening data not visible
- No authentication/permission bypass via URL

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.12: Cross-Program Staff Cannot Access Other Program's Applications

**Objective:** Verify that staff from Program A cannot validate/screen Program B applications.

**Account:** `qa-program-staff-b@example.com` (staff of Program B)

**Steps:**
1. Log in as Program Staff B
2. Know or obtain an application ID from Program A
3. Attempt to navigate to `/applications/{program-a-app}/eligibility-validations`

**Expected Observations:**
- HTTP 403 Forbidden or appropriate error
- Application data not visible
- Cross-program access blocked
- Staff cannot enumerate or access out-of-scope applications

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.13: Validation History Ordered Correctly

**Objective:** Verify that validation history is sorted by most recent execution first.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Run multiple validations for same application (different versions)
3. Access validation history page
4. Observe sort order

**Expected Observations:**
- Validations listed in descending order by executed_at
- Most recent validation at top
- Timestamps accurate and in correct order
- Pagination (if applicable) works

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.14: Screening History Preserves Rationale

**Objective:** Verify that screening decision details are preserved and accessible.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Complete a screening with outcome and rationale
3. Navigate to screening detail page
4. View saved screening

**Expected Observations:**
- Outcome displayed
- Rationale text shown in full
- No truncation or data loss
- Formatting preserved (if markdown, etc.)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.15: Completed Screening Cannot Be Re-completed

**Objective:** Verify that completed screenings are immutable.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. View a completed screening
3. Attempt to access edit/update form
4. Attempt to submit changes via direct request

**Expected Observations:**
- Edit form not shown (or disabled)
- Direct PUT request fails with error: "Only in-review screenings can be completed"
- Screening remains unchanged
- Original outcome preserved

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.16: Duplicate Screening Prevention

**Objective:** Verify that a second screening cannot be created for an already-completed version.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. View application with completed screening
3. Attempt to create a new screening for the same version
4. Observe error

**Expected Observations:**
- Create form shows error (if pre-checked) or
- Server returns error: "A completed screening already exists for this version"
- New screening not created
- Original screening preserved

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.17: Authorization Checks Are Backend-Enforced

**Objective:** Verify that authorization is not just frontend hiding but enforced at HTTP layer.

**Account:** `qa-applicant@example.com`

**Steps:**
1. Log in as Applicant
2. Open browser developer tools (Network tab)
3. Capture URL of screening or validation endpoint
4. Manually craft POST/PUT request to validation/screening endpoint
5. Send request with Authorization header

**Expected Observations:**
- HTTP 403 Forbidden response (not 401)
- No data modification occurs
- Backend validation prevents unauthorized action
- Error logged (if available)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.18: Application Status Reflects Screening Outcome

**Objective:** Verify that application status is updated based on screening decision.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Application status = "submitted"
3. Complete screening with outcome "ELIGIBLE"
4. Reload or check application detail

**Expected Observations:**
- Application status transitions to "eligible"
- Status change visible in application summary
- Status reflected in database (query confirmation)

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.19: Ineligible Status Update

**Objective:** Verify INELIGIBLE outcome updates application status correctly.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Application status = "submitted"
3. Complete screening with outcome "INELIGIBLE"
4. Check application status

**Expected Observations:**
- Application status = "ineligible"
- Status change audited/logged
- Screening preserved with original decision

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.20: Validation Result JSON Storage

**Objective:** Verify that validation result details are stored and retrievable.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Run validation
3. Inspect validation record detail page or database

**Expected Observations:**
- Result field contains JSON object
- Rule evaluation results included
- Data accessible and parseable (not corrupted)
- Result preserved for audit

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.21: Error Handling on Invalid Input

**Objective:** Verify that form validation catches invalid input.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Access screening form
3. Submit with missing rationale (required field)
4. Submit with invalid outcome (if dropdown, should not be possible)
5. Submit with version ID that doesn't match application

**Expected Observations:**
- Form validation prevents submission (if frontend enabled)
- Server-side validation catches error (HTTP 422 or redirect with error)
- Helpful error message shown
- No data persisted
- State unchanged

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

### Manual Test 07.22: Audit Trail Timestamps Are Accurate

**Objective:** Verify that all actions are timestamped correctly.

**Account:** `qa-program-staff@example.com`

**Steps:**
1. Log in as Program Staff
2. Note current time (reference)
3. Run validation
4. Complete screening
5. Query database or view detail pages
6. Compare timestamps to reference time

**Expected Observations:**
- Validation executed_at ≈ action time
- Screening created_at ≈ action time
- Screening completed_at ≈ completion time
- Timestamps in UTC or consistent timezone
- Times are accurate and sequential

**Actual Observation:**
NOT RUN

**Result:**
NOT RUN

---

## Testing Readiness Checklist

Before manual QA can be performed:

- [ ] Laravel dev server running (localhost:8000)
- [ ] PostgreSQL database populated with test data
- [ ] QA accounts created and verified
- [ ] HTTP endpoints tested and working
- [ ] Inertia routes rendering pages correctly
- [ ] Authorization middleware functioning
- [ ] Permissions and roles configured
- [ ] Program staff memberships created
- [ ] Test applications with multiple versions
- [ ] Email/notifications working (if applicable)
- [ ] Activity logging capturing events
- [ ] Database queries and views working
- [ ] Error handling and flash messages functional
- [ ] Form validation (frontend + backend) working
- [ ] Redirect/routing working correctly
- [ ] Session management working
- [ ] Browser dev tools accessible for manual testing

---

## Known Limitations

The following cannot be tested until additional components are implemented:

1. **No Frontend UI Yet:** These scenarios reference future Vue form components and UI sections.
2. **No Page Navigation Links:** Links to screening/validation pages don't exist until UI is built.
3. **No Real Eligibility Rules Engine:** Validation currently accepts all rules; real rule evaluation logic is future.
4. **No Notifications Yet:** Applicants/judges won't be notified of screening outcomes in this phase.
5. **No Judge Assignment Yet:** Judge role and assignment workflow are future phases.
6. **No Appeals Process:** Rescreen/appeal workflow is future; completed screenings are final in this phase.

---

## Conclusion

These 22 manual test scenarios provide comprehensive coverage of the HTTP/Inertia delivery layer for eligibility and screening from a user-interaction perspective.

**All scenarios are marked NOT RUN for Task 018.**

When UI and form components are implemented in future tasks, these scenarios will serve as the basis for human QA verification of the complete eligibility and screening workflow.

See [FeatureTest/018-eligibility-screening-http-specification.md](../FeatureTest/018-eligibility-screening-http-specification.md) for full detailed automated test specifications.
