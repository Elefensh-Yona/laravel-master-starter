# AI Agent Handoff 014: EAIC Manual Test Plan 01 Summary

## 1. Interaction ID

`014`

## 2. Task requested

Create the permanent manual QA specification for the current Program Administration implementation and the first ManualTest file for EAIC.

This task is documentation only.

No implementation work, no database changes, no feature fixes, and no automated test execution were performed.

## 3. Current Program feature inventory

### IMPLEMENTED

- Authentication with verified-user access requirements
- Program route protection via `auth` and `verified` middleware
- `GET /programs` program index
- `GET /programs/create` and `POST /programs` create flow
- `GET /programs/{program}` program detail view
- `GET /programs/{program}/edit` and `PUT /programs/{program}` edit flow
- `POST /programs/{program}/publish` publish flow
- `ProgramPolicy` with view/create/update/publish checks
- `SaveProgramRequest` validation rules
- Program-scope checks using active `program_memberships`
- `program_staff` membership capability model for current Program administration
- Shared sidebar Programs navigation using the current ability gate
- Empty-state UI on the Programs index
- QA fixture users and Program records for local manual QA
- Local Mailpit email verification environment for current manual QA setup

### PARTIALLY IMPLEMENTED

- Program access visibility uses the current permission/scope model but not the full future EAIC governance model.
- Publish/edit state is limited to the current Program administration scope.
- The current sidebar layout was corrected in-browser and must still be manually verified as part of QA.

### NOT IMPLEMENTED

- Full EAIC lifecycle workflow beyond Program administration
- Judge, Decision Maker, and Applicant capabilities beyond current fixture-only local QA actors
- Full advanced program governance or lifecycle states
- Full future RBAC model beyond the current permission/scope foundation

### DEFERRED

- Broader EAIC lifecycle domain features and advanced governance flows
- Future role/permission model enhancements beyond the current implementation

### KNOWN ISSUE

- The sidebar overflow issue was identified, corrected in the browser, and must still be manually re-validated as a QA outcome; it is not assumed to be permanently resolved without evidence.
- Starter branding remains a separate issue and is not part of this manual test plan.

## 4. ManualTest file created

Created:

- `ManualTest/ManualTest_01_Program_Administration_and_QA_Strategy.md`

## 5. Tests defined

The file includes the requested Program Administration manual test set covering:

- Authentication / entry
- Program navigation
- Program index
- Program create
- Program show
- Program edit
- Program publish
- Cross-program security
- Actor coverage matrix
- UI quality checks
- Broader QA roadmap
- Execution policy and blank evidence log

The required test IDs are included, including:

- `MT-01-001` through `MT-01-003`
- `MT-01-010` through `MT-01-012`
- `MT-01-020` through `MT-01-023`
- `MT-01-030` through `MT-01-038`
- `MT-01-040` through `MT-01-043`
- `MT-01-050` through `MT-01-054`
- `MT-01-060` through `MT-01-066`
- `MT-01-070` through `MT-01-072`

## 6. Actor matrix

The actor matrix in the manual QA plan reflects the current local QA accounts:

- `admin@example.com` — Super Admin
- `qa-program-staff@example.com` — Program Staff
- `qa-decision-maker@example.com` — Decision Maker
- `qa-judge@example.com` — Judge
- `qa-applicant@example.com` — Applicant

The document clearly states that the Decision Maker/Judge/Applicant lifecycle capabilities are not currently assumed to exist beyond limited QA account coverage and denial expectations.

## 7. QA methodology

The permanent methodology section includes the required standard structure for future ManualTest files:

- Test ID
- Test title
- Priority
- Feature/module
- Actor
- Account
- Preconditions
- Test data
- Exact action steps
- Expected UI result
- Expected backend/security result
- Expected database/business result where observable
- Evidence required
- PASS criteria
- FAIL criteria
- Actual result
- Evidence reference
- Notes
- Status

It also states the status values:

- NOT RUN
- PASS
- FAIL
- BLOCKED
- NOT APPLICABLE

## 8. Evidence requirements

The document defines the evidence expectations for manual QA:

- screenshot when visual behavior matters
- URL when route behavior matters
- visible success or failure message
- actor/account used
- relevant record identifier
- concise actual-result summary
- direct URL/denial evidence for security checks

## 9. Future QA coverage roadmap

The broader roadmap includes the required future manual QA domains:

- Authentication
- RBAC
- CRUD
- Search
- Pagination
- Import/export
- Print
- Assignment
- Roles & permissions
- Activity/audit
- Dashboard
- Notifications
- Lifecycle
- Data integrity
- Security
- EAIC lifecycle workflows

This is documented as a roadmap only and not a claim that those features exist.

## 10. Test execution status

Status: NOT RUN BY DESIGN

Reason:

- the task was solely documentation/specification creation
- no manual browser execution was requested or performed by the AI agent
- the Product & Technical Controller is the required human execution actor

## 11. Files created

- `ManualTest/ManualTest_01_Program_Administration_and_QA_Strategy.md`
- `AI-AGENT-HANDOFFS/014-eaic-manual-test-plan-01-summary.md`

## 12. Files modified

None.

This task did not modify application code, policies, routes, migrations, database content, or frontend implementation.

## 13. Database changes

None.

No migrations were created or run.
No data modifications were performed for this task.

## 14. Known current limitations

- Current Program Administration is intentionally narrow and built around the present Batch 1 Program foundation.
- Full EAIC lifecycle implementation is still pending.
- Decision Maker/Judge/Applicant lifecycle roles are not yet assumed to exist.
- The current QA fixture is a local development fixture, not a production governance model.
- The sidebar overflow issue must be manually re-checked and not assumed resolved without browser evidence.

## 15. Recommended next task

HUMAN MANUAL QA CHECKPOINT #1

The Product & Technical Controller should now execute the manual Program tests in the live browser and record actual results in the blank execution table.

## 16. Verified Facts vs Assumptions

### Verified facts

- The Program Administration UI exists in the current repo.
- The Program routes, policy checks, and validation are implemented.
- The QA fixture exists and includes the current local QA accounts and Programs.
- Local Mailpit verification has been established in the environment.
- The sidebar overflow issue was observed and corrected in the live browser, but it must still be manually verified as part of QA.
- Manual test execution has not yet occurred.

### Assumptions kept explicit

- Future EAIC lifecycle features are not assumed to exist.
- The current Program model is not treated as full enterprise governance.
- No future lifecycle or role system is claimed by this documentation.

## 17. Stop condition

The required ManualTest specification has been created and the required handoff summary exists.

This interaction stops here as requested.
