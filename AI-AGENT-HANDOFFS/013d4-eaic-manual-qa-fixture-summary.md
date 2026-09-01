# AI Agent Handoff 013D-4: Controlled Manual-QA Actors and Demo Data

## 1. Interaction ID

`013D-4`

## 2. Recovery / status check

Before the QA fixture work, the repository was checked with `git status --short --branch` and reviewed for existing seeders, factories, models, permissions, policies, and FeatureTest documentation.

Result: no existing QA fixture or QA seeder was already in place. The repository already contained the required EAIC Batch 1 Program models, Program membership model, Program eligibility rule model, Rubric model, and the existing factory architecture. The default Starter `admin@example.com` Super Admin account and Starter role system were preserved as-is.

## 3. Scope constraints respected

This task did not modify:

- the production/default Starter seed behavior
- the existing `admin@example.com` account
- the existing Super Admin password
- the existing Master Starter roles
- the EAIC permission catalog
- migrations
- application workflow
- judging workflow
- evaluation workflow
- deliberation
- decisions
- outcomes
- notifications
- AI

No new EAIC roles were created. No second role system was invented.

## 4. QA actors created

The development-only QA accounts created via the local QA seeder are:

- `qa-program-staff@example.com` — QA Program Staff
- `qa-decision-maker@example.com` — QA Decision Maker
- `qa-judge@example.com` — QA Judge
- `qa-applicant@example.com` — QA Applicant

The existing Super Admin account remains:

- `admin@example.com` — Super Admin

## 5. Password policy and credentials

The local QA password used for the four QA actors is:

- `DevelopmentQa123!`

This password is explicitly marked as development/testing only and is not used for the existing Super Admin account.

The existing Super Admin password was left unchanged.

## 6. Authorization and capability model used

The approved Project architecture was followed:

- the existing Starter role model remained unchanged
- the existing EAIC permission model remained unchanged
- Program Staff capability is represented through the established `program_memberships.capability` literal: `program_staff`

A checked, active Program Staff membership was created for the QA Program Staff user in Program A.

For Decision Maker, Judge, and Applicant, no extra EAIC DB role or capability literal was invented because the repository does not yet provide a safe approved representation for those actor scopes beyond the current Batch 1 foundation. They were created as local QA test users for manual examination only, without inventing a new DB role system.

## 7. Sample Program data created

The QA fixture created two minimal realistic Programs:

- Program A: `EAIC Innovation Challenge 2026`
- Program B: `EAIC Regional Challenge 2026`

Both Programs were created with valid time windows and realistic metadata.

## 8. Program membership data created

Active Program Staff membership created:

- Program A
- user: `qa-program-staff@example.com`
- capability: `program_staff`
- status: `active`

No additional unsupported program-capability membership entries were invented for Decision Maker, Judge, or Applicant.

## 9. RBAC / eligibility / rubric demo data created

The QA fixture created:

- two Program Eligibility Rules
- two Rubrics
- enough Program and Program Membership data to exercise the current Program UI and basic Program-scoped behavior

## 10. Seeder file created

Created a dedicated development QA fixture seeder:

- `database/seeders/ManualQaFixtureSeeder.php`

This seeder is isolated from the default database seeding flow and does not alter the default Starter behavior.

## 11. Verification performed

The fixture was executed against the current local PostgreSQL `development` database using a direct Laravel seeding command and then verified with a read-only database inspection.

Fresh verification output after seeding:

- `users`: `5`
- `roles`: `4`
- `permissions`: `28`
- `programs`: `2`
- `program_memberships`: `1`
- `program_eligibility_rules`: `2`
- `rubrics`: `2`

Observed user records:

- `admin@example.com` — Super Admin
- `qa-program-staff@example.com` — QA Program Staff
- `qa-decision-maker@example.com` — QA Decision Maker
- `qa-judge@example.com` — QA Judge
- `qa-applicant@example.com` — QA Applicant

Observed active Program membership:

- Program 1 / user 2 / capability `program_staff` / status `active`

## 12. Files modified

- `database/seeders/ManualQaFixtureSeeder.php`

## 13. No changes made outside scope

No unrelated code, permission schema, migration, or application workflow was modified.

## 14. Recommended next action

Proceed to Manual QA Checkpoint #1 using the ready local QA accounts and minimal Program dataset, while preserving the default Starter Super Admin account and the existing RBAC architecture.

## 15. Verified facts vs assumptions

### Verified facts

- The repository had no existing QA fixture before this task.
- The default `admin@example.com` Super Admin stays in place and unchanged.
- The QA fixture seeder created the demanded local QA user accounts.
- The QA seeder created two Programs, one active Program Staff membership, two eligibility rules, and two rubrics.
- The database verification after seeding confirmed the counts above.

### Assumptions kept explicit

- Decision Maker, Judge, and Applicant representation beyond basic local QA logins remains intentionally limited because the repository does not yet define a safe approved DB capability model for those actors.
- This task is a controlled local QA fixture only and is not an RBAC redesign or production policy change.
