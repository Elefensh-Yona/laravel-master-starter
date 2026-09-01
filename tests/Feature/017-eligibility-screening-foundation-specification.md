# Task 017: Eligibility & Screening Foundation Specification

**Interaction ID:** 017  
**Status:** COMPLETE (foundation only)  
**Scope boundary:** This is the minimal backend foundation for objective validation and human staff screening only.

---

## 1. Objective

Implement the approved EAIC foundation for:

- objective program validation result storage
- human Program Staff screening outcome storage
- per-program authorization and scope checks
- auditability of validation and screening records
- relationship to Program and Application / Application Version

This task intentionally does not implement:

- judge assignment
- conflict
- evaluations
- deliberation
- decisions
- outcomes
- notifications
- UI

---

## 2. Approved Contract

### Application Validation

- model: `ApplicationValidation`
- table: `application_validations`
- purpose: automated, objective validation output only
- authoritative statuses: `passed`, `failed`, `error`
- unique current record per `(application_version_id, program_id)`
- includes `program_id`, `application_id`, `application_version_id`, `status`, `result`, `executed_at`, `executed_by`, `failure_reason`

### Screening

- model: `Screening`
- table: `screenings`
- purpose: human Program Staff screening of the exact submitted application version
- status values: `in_review`, `completed`
- outcome values: `ELIGIBLE`, `INELIGIBLE`
- includes `validation_id` linkage
- preserves rationale and reopen history
- one completed screening per application version

---

## 3. Access Rules

The authorization boundary follows the existing EAIC pattern:

- permission `eligibility.validate` for automated validation access
- permission `eligibility.screen` for human screening access
- only active Program Staff capability in the relevant program authorizes the action
- out-of-scope actors remain denied
- Super Admin keeps broad override through the app-wide `Gate::before` rule

---

## 4. Validation Scenarios

### Positive cases

- program-scoped staff with `eligibility.validate` can view/update validation results in their own program
- program-scoped staff with `eligibility.screen` can view/update screening records in their own program
- records connect to the correct `Program`, `Application`, and `ApplicationVersion`

### Negative cases

- applicant with screening permission is denied
- staff lacking program membership is denied
- out-of-scope program staff cannot access another program’s validation or screening records

---

## 5. Implementation Notes

This foundation is intentionally narrow and aligned with existing project conventions:

- model + factory + migration + policy pattern used throughout the Batch 1 work
- no generic rules engine introduced
- no extra role vocabulary introduced
- no downstream lifecycle modules implemented

---

## 6. Owner Decision Required

The repository still leaves the following as product decisions outside this task:

- exact applicant-facing screening visibility rules
- any additional screening outcome codes beyond the approved `ELIGIBLE`/`INELIGIBLE`
- whether screening should always tie to a validation record or can be performed without one in specific edge cases
- later lifecycle actions after screening (assignment, evaluation, conflict, decision)

This task intentionally stops at the foundation boundary.
