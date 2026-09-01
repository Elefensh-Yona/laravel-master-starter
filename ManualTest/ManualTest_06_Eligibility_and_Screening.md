# ManualTest/ManualTest_06_Eligibility_and_Screening

**Status:** Specification only. NOT RUN.  
**Purpose:** Document future human QA scenarios for the Eligibility & Screening foundation.  
**Date Created:** 2026-09-01  
**Scope:** Backend foundation only; no downstream judge/evaluation/decision flows

---

## Overview

This manual test plan specifies scenarios for future human QA verification of the eligibility and screening foundation. The scenarios are defined now but not executed in this phase.

**Key Points:**
- These tests are NOT RUN as part of Task 017A
- No browser testing has occurred
- No test results are marked PASS or FAIL
- The scenarios provide guidance for future QA when UI and integration are available

---

## Preconditions for All Scenarios

Before any manual QA can occur:

1. **Local development environment is configured:**
   - Laravel dev server running on localhost:8000
   - PostgreSQL `development` database populated
   - Fortify email verification configured
   - Inertia.js + Vue frontend loaded

2. **QA Accounts exist and are email-verified:**
   - `admin@example.com` (Super Admin)
   - `qa-program-staff@example.com` (Program Staff with screening permissions)
   - `qa-applicant@example.com` (Applicant)
   - `qa-judge@example.com` (Judge, if applicable to future phases)

3. **Test Data exists:**
   - At least one published program with eligibility rules configured
   - At least one application in the program with submitted version
   - Application status transitions are possible (draft → submitted → screening)

4. **No Destructive Database Operations:**
   - No database resets during manual testing
   - No data deletion except through governed UI actions
   - Testing uses existing seeded data or creates new test records

---

## Manual Test Scenarios (All NOT RUN)

### Manual Test 06.01: Eligibility Rule Configuration Loads Correctly
**Status:** NOT RUN

### Manual Test 06.02: Published vs Draft Eligibility Rules Visibility
**Status:** NOT RUN

### Manual Test 06.03: Validation Result is Created and Stored
**Status:** NOT RUN

### Manual Test 06.04: Validation Result References Correct Application Version
**Status:** NOT RUN

### Manual Test 06.05: Validation Failure Captures Reason
**Status:** NOT RUN

### Manual Test 06.06: Authorized Staff Can Access Screening Interface
**Status:** NOT RUN

### Manual Test 06.07: Applicant Cannot Access Screening Interface
**Status:** NOT RUN

### Manual Test 06.08: Cross-Program Staff Access is Denied
**Status:** NOT RUN

### Manual Test 06.09: Screening Outcome Can Be Recorded
**Status:** NOT RUN

### Manual Test 06.10: Screening Requires Rationale
**Status:** NOT RUN

### Manual Test 06.11: Invalid Screening Outcome is Rejected
**Status:** NOT RUN

### Manual Test 06.12: Screening State Transitions Are Audited
**Status:** NOT RUN

### Manual Test 06.13: Direct Status Manipulation Cannot Bypass Workflow
**Status:** NOT RUN

### Manual Test 06.14: Screening Remains Linked to Exact Version
**Status:** NOT RUN

### Manual Test 06.15: Applicant Cannot View Screening Results (Before Decision)
**Status:** NOT RUN

### Manual Test 06.16: Judge Cannot Screen Unless Explicitly Authorized
**Status:** NOT RUN

### Manual Test 06.17: Screening History is Not Silently Overwritten
**Status:** NOT RUN

### Manual Test 06.18: Screening Timestamp Accuracy
**Status:** NOT RUN

### Manual Test 06.19: Direct Screening URL Access Requires Authorization
**Status:** NOT RUN

### Manual Test 06.20: Screening UI Updates Application Status
**Status:** NOT RUN

### Manual Test 06.21: Validation Error is Captured
**Status:** NOT RUN

### Manual Test 06.22: UI Elements Shown/Hidden Based on Authorization
**Status:** NOT RUN

---

## Detailed Test Specifications

**Full test specifications with steps, preconditions, and expected observations are documented in:**

→ [FeatureTest/017-eligibility-screening-foundation-specification.md](../FeatureTest/017-eligibility-screening-foundation-specification.md)

The manual test scenarios parallel the FeatureTest specification but focus on user-visible behavior and integration testing when the UI layer is implemented.

---

## Known Limitations

The following cannot be tested until additional components are implemented:

1. **No HTTP API yet:** These scenarios assume REST/Inertia endpoints exist to access screening functionality.
2. **No Frontend UI yet:** Scenarios reference future UI forms and sections that don't exist yet.
3. **No Event/Notification System yet:** Scenarios cannot verify notifications are sent to applicants or judges.
4. **No Downstream Workflow yet:** Judge assignment, evaluation, decision flows are future phases.

---

## Testing Readiness Checklist

Before manual QA can be performed:

- [ ] Development environment fully configured
- [ ] QA accounts created and verified
- [ ] Test data loaded (programs, applications, versions)
- [ ] Frontend UI components implemented for screening
- [ ] HTTP endpoints or Inertia routes created
- [ ] Authorization layer integrated
- [ ] Error handling and feedback messages implemented
- [ ] Database constraints and validation in place
- [ ] Audit logging (activity_logs table) functional
- [ ] Test environment reset procedure documented

---

## Conclusion

These 22 manual test scenarios provide comprehensive coverage of the eligibility and screening foundation from a user-centric perspective.

**All scenarios are marked NOT RUN for Task 017A.**

When UI and integration work are complete in future tasks, these scenarios will serve as the basis for human QA verification.

See [FeatureTest/017-eligibility-screening-foundation-specification.md](../FeatureTest/017-eligibility-screening-foundation-specification.md) for full detailed specifications of each test including:
- Test ID and objectives
- Actor and account information
- Complete preconditions
- Step-by-step actions
- Expected backend/database results
- Security reasoning
- Pass/fail criteria
