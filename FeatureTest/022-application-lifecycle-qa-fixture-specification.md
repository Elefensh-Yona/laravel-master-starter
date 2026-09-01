# Task 022: Application Lifecycle QA Fixture Specification

**Status:** Specification only. NOT EXECUTED.  
**Scope:** Deterministic local development fixtures for Application through Screening QA.

## FIXTURE-001: Draft Application exists

- **Actor:** QA Applicant.
- **Fixture:** `QA-APPLICATION-A-DRAFT`.
- **Preconditions:** ManualQaFixtureSeeder has run against local QA data.
- **Expected data state:** Program A, `INDIVIDUAL`, draft status, owner is QA Applicant, and current Version 1 is draft.
- **Security reason:** Ownership and editable version workflow can be examined without broad grants.
- **Evidence:** Application and current-version query.
- **PASS:** One matching Application and one draft Version 1 exist.
- **FAIL:** Missing, duplicate, wrong owner/program/state, or absent current version.

## FIXTURE-002: Submitted Application exists

- **Actor:** QA Applicant / Program Staff.
- **Fixture:** `QA-APPLICATION-B-SUBMITTED`.
- **Preconditions:** Fixture seeded.
- **Expected data state:** Program A application is submitted and has a submitted timestamp.
- **Security reason:** Eligibility and Screening require an exact submitted version.
- **Evidence:** Application query.
- **PASS:** One submitted Program A fixture exists.
- **FAIL:** Missing, duplicate, or non-submitted state.

## FIXTURE-003: Submitted Application has one submitted version

- **Actor:** QA Program Staff.
- **Fixture:** `QA-APPLICATION-B-SUBMITTED`.
- **Preconditions:** Fixture seeded.
- **Expected data state:** Exactly Version 1 exists, is submitted, has a submitted timestamp/by actor, and is the current version.
- **Security reason:** Assessment must target a specific immutable version.
- **Evidence:** Version count and pointer query.
- **PASS:** One matching submitted Version 1 is present.
- **FAIL:** Missing version, duplicate number, wrong state, or mismatched pointer.

## FIXTURE-004: Applications belong to intended Programs

- **Actor:** QA Program Staff.
- **Fixture:** All three application references.
- **Preconditions:** Fixture seeded.
- **Expected data state:** Applications A/B belong to `EAIC-2026-01`; Application C belongs to `EAIC-2026-02`.
- **Security reason:** Program boundary checks require known target and out-of-scope records.
- **Evidence:** Joined Application/Program query.
- **PASS:** Each reference has its prescribed Program code.
- **FAIL:** Any Program mismatch.

## FIXTURE-005: Applicant ownership is correct

- **Actor:** QA Applicant.
- **Fixture:** All three applications.
- **Preconditions:** Fixture seeded.
- **Expected data state:** QA Applicant is `primary_owner_id` for every fixture; no owner Member row is fabricated.
- **Security reason:** Existing ownership is distinct from Application membership.
- **Evidence:** Owner join and ApplicationMember counts.
- **PASS:** Ownership matches and no member row is created solely for owner identity.
- **FAIL:** Wrong owner or invented owner membership.

## FIXTURE-006: Program Staff has Program A scope only

- **Actor:** QA Program Staff.
- **Fixture:** ProgramMembership.
- **Preconditions:** Existing QA fixture seeded.
- **Expected data state:** One active `program_staff` membership for Program A and none for Program B.
- **Security reason:** Cross-program permission must not become global scope.
- **Evidence:** Membership/Program query.
- **PASS:** Program A-only scope.
- **FAIL:** Program B scope or duplicate active scope.

## FIXTURE-007: Program B Application supports scope checks

- **Actor:** QA Program Staff.
- **Fixture:** `QA-APPLICATION-C-PROGRAM-B-SCOPE`.
- **Preconditions:** Fixture seeded.
- **Expected data state:** One submitted Program B Application with submitted Version 1.
- **Security reason:** A known out-of-scope application is needed for protected-action checks.
- **Evidence:** Application/Version/Program query.
- **PASS:** Record exists with prescribed Program B relationship.
- **FAIL:** Missing, duplicate, or attached to Program A.

## FIXTURE-008: Seeder rerun does not duplicate records

- **Actor:** Local fixture system.
- **Fixture:** All Task 022 fixture records.
- **Preconditions:** Fixture seeded once.
- **Action:** Re-run ManualQaFixtureSeeder in a local disposable QA environment.
- **Expected data state:** Natural keys retain one Application per Program/reference and one Version per Application/version number.
- **Security reason:** Repeated local setup cannot create ambiguous assessment records.
- **Evidence:** Grouped duplicate queries before/after rerun.
- **PASS:** No duplicate fixture Application or Version identities.
- **FAIL:** Any duplicate natural key.

## FIXTURE-009: No Screening is pre-created

- **Actor:** QA Program Staff.
- **Fixture:** Task 022 set.
- **Preconditions:** Fixture seeded.
- **Expected data state:** `application_validations` and `screenings` remain empty.
- **Security reason:** QA must manually trigger the current Validation then Screening flow.
- **Evidence:** Table counts.
- **PASS:** Both counts are zero.
- **FAIL:** Any validation or Screening fixture is pre-created.

## FIXTURE-010: No downstream lifecycle data is created

- **Actor:** Local fixture system.
- **Fixture:** Task 022 set.
- **Preconditions:** Fixture seeded.
- **Expected data state:** No JudgeAssignment, Conflict, Evaluation, Deliberation, Decision, or Outcome rows/tables are created by this task.
- **Security reason:** Later stages are explicitly outside scope.
- **Evidence:** Seeder source and database table/count inspection.
- **PASS:** Seeder creates only approved Program/Application fixture records.
- **FAIL:** Downstream lifecycle data is introduced.
