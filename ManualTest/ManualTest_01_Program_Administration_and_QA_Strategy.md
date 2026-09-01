# Manual Test 01: Program Administration and QA Strategy

## 1. Purpose of this document

This document has two purposes:

1. Define the permanent manual QA methodology for EAIC.
2. Define the first executable manual test set for the currently implemented Program Administration UI.

This document reflects the actual current source implementation in the repository and does not assume future modules, lifecycle states, or unimplemented authority models.

---

## 2. Current implementation inventory

### 2.1 Authentication state

IMPLEMENTED

- Authentication is enforced by Laravel auth middleware.
- Verified-only access is required for main authenticated application routes.
- The root route sends an unauthenticated user to the login flow.
- Authenticated users without `dashboard.view` permission are redirected to profile editing.
- Program routes are inside `Route::middleware(['auth', 'verified'])`.

PARTIALLY IMPLEMENTED

- The app enforces verification for normal authenticated access, but the verified state is not the same as a full lifecycle-based applicant or staff onboarding flow.

NOT IMPLEMENTED

- Full logout/session maturity coverage beyond the standard starter behavior.
- Multi-step identity verification beyond the existing email verification requirement.

DEFERRED

- Advanced authentication flows beyond the current starter/verification model.

KNOWN ISSUE

- The manual QA workflow must treat email verification as a precondition for the current Program Administration entry points.

### 2.2 Email verification requirement

IMPLEMENTED

- `auth` and `verified` middleware are required on the Program routes.
- Unverified users are redirected to the verification flow when they attempt to access protected routes.
- Local Mailpit SMTP is configured and operational in the current local development environment.

### 2.3 Programs navigation

IMPLEMENTED

- The sidebar navigation includes a Programs item using a shared `managePrograms` ability gate.
- The Programs item is visible only when the front-end ability state shows Program administration access.
- The main route is `/programs`.

PARTIALLY IMPLEMENTED

- The route is visible based on a shared ability gate, but the full UI authorization model is still tied to the current permission foundation rather than a broader role-governance model.

### 2.4 Program index

IMPLEMENTED

- `GET /programs` is implemented in `ProgramController::index()`.
- The index respects Super Admin behavior and program visibility through active membership plus publication rules.
- The page displays a title, status badges, timing metadata, timezone, and actions.
- The empty state is implemented.
- `canCreate` is supplied to the page.

PARTIALLY IMPLEMENTED

- The index shows only visible records and is intentionally scoped by policy/scope checks, but it is not yet a full advanced EAIC program discovery UI.

### 2.5 Program creation

IMPLEMENTED

- `GET /programs/create` exists.
- `POST /programs` exists.
- `SaveProgramRequest` validates required fields and duplicate `code` and `slug`.
- Route middleware requires `permission:program.create`.
- The controller authorizes `create` and creates the Program and an initial `program_staff` membership for the creator.

PARTIALLY IMPLEMENTED

- Creation logic is bootstrap-aware but limited to the single current policy model.

NOT IMPLEMENTED

- No extended workflow onboarding beyond program creation.

### 2.6 Program show

IMPLEMENTED

- `GET /programs/{program}` exists.
- The show page exposes the visible program summary and status.
- Edit and Publish actions are shown only when `canEdit` and `canPublish` are true.

PARTIALLY IMPLEMENTED

- The view is a focused program detail summary, not a complete lifecycle overview panel.

### 2.7 Program edit

IMPLEMENTED

- `GET /programs/{program}/edit` exists.
- `PUT /programs/{program}` exists.
- `ProgramPolicy::update()` restricts edits to users with `program.update` and active `program_staff` membership in the specific Program.
- `Program` status must not be archived.

PARTIALLY IMPLEMENTED

- Editing is limited to the defined program fields; later program lifecycle workflows are not yet in scope.

### 2.8 Program publish

IMPLEMENTED

- `POST /programs/{program}/publish` exists.
- Publish is allowed only when the user has `program.publish`, an active `program_staff` membership in the Program, the status is `draft`, and the opening time is before the closing time.
- The publish action uses a confirmation UI and a success flash message.

PARTIALLY IMPLEMENTED

- Publish is a narrow operational gate only; there is no broader publication workflow yet.

### 2.9 Program permissions

IMPLEMENTED

- Permission names used by the current implementation include:
  - `program.view`
  - `program.create`
  - `program.update`
  - `program.publish`
- These are enforced via route middleware and policy logic.

NOT IMPLEMENTED

- Full EAIC lifecycle permissions beyond the Program administration foundation.

### 2.10 Program policies

IMPLEMENTED

- `ProgramPolicy` implements `viewAny`, `view`, `create`, `update`, and `publish`.
- `viewAny` currently returns `true` for all authenticated users, with the actual record visibility still filtered at the query and program-scope level.
- `view` allows published programs or users with active Program membership scope.
- `update` and `publish` are per-program scoped and require active `program_staff` membership.

PARTIALLY IMPLEMENTED

- The policy model is valid for the current Batch 1 Program scope but does not represent the broader future decision-making governance model.

### 2.11 Program scope

IMPLEMENTED

- The policy layer uses `InteractsWithProgramScope`.
- Scope is checked through active `program_memberships` records keyed to the current user and program.
- The current Program Staff capability is represented as `program_staff`.

PARTIALLY IMPLEMENTED

- The current scope model is intentionally narrow and does not yet cover the full future EAIC lifecycle or role/capability matrix.

### 2.12 Program Staff capability

IMPLEMENTED

- The QA fixture creates an active `program_staff` membership for `qa-program-staff@example.com` on Program A.
- `ProgramPolicy::update()` and `ProgramPolicy::publish()` enforce the active capability requirement.

PARTIALLY IMPLEMENTED

- This is a current working minimal model, not the full future governance model.

### 2.13 Bootstrap creation

IMPLEMENTED

- The first Program can be created by a user with `program.create` permission.
- The creation flow creates the Program and the initial creator as `program_staff` for that Program.

PARTIALLY IMPLEMENTED

- This is a bootstrap-safe exception and not a general role-assignment subsystem.

### 2.14 Validation

IMPLEMENTED

- `SaveProgramRequest` validates:
  - required name
  - required code
  - required slug
  - unique `code` and `slug`
  - `timezone` validity
  - `opens_at` / `closes_at` presence and ordering (`after:opens_at`)
  - optional description, metadata

PARTIALLY IMPLEMENTED

- Current validation is sufficient for the current program administration scope but not the full lifecycle validation model.

### 2.15 Empty state

IMPLEMENTED

- the index has a clear empty state when no visible programs exist.

### 2.16 Success and error behavior

IMPLEMENTED

- success flash messages are returned to the user after create, update, and publish.
- validation errors show clearly when invalid input is submitted.
- direct unauthorized attempts are blocked by middleware/policy.

### 2.17 Direct URL protection

IMPLEMENTED

- direct requests to protected Program routes fail when the user does not hold the necessary permission and/or scope.

### 2.18 Cross-program protection

IMPLEMENTED

- Program Staff with scope to Program A are not automatically authorized for Program B.
- The policy checks target program membership and professionalism scope.

### 2.19 Responsive behavior

IMPLEMENTED

- The Program pages are built with responsive layout patterns and standard app shell structure.

KNOWN ISSUE

- Browser QA previously identified and corrected a sidebar horizontal overflow issue, which is documented as a resolved live UI finding only when verified in the browser.

### 2.20 Current QA fixture actors

IMPLEMENTED

- `admin@example.com` — Super Admin
- `qa-program-staff@example.com` — QA Program Staff
- `qa-decision-maker@example.com` — QA Decision Maker
- `qa-judge@example.com` — QA Judge
- `qa-applicant@example.com` — QA Applicant

PARTIALLY IMPLEMENTED

- The QA fixture creates these accounts in local development, but not all of them have a broader EAIC lifecycle role model yet.

### 2.21 Current QA fixture Programs

IMPLEMENTED

- Program A: `EAIC Innovation Challenge 2026`
- Program B: `EAIC Regional Challenge 2026`
- Program A includes an active `program_staff` membership for the QA Program Staff account.

PARTIALLY IMPLEMENTED

- The Programs exist as minimal realistic development fixtures for current Program Administration QA but do not represent a complete future EAIC lifecycle dataset.

---

## 3. Permanent Manual QA methodology

### 3.1 Standard ManualTest document structure

Every future `ManualTest/` document should follow the same structure:

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

Status must be one of:

- NOT RUN
- PASS
- FAIL
- BLOCKED
- NOT APPLICABLE

### 3.2 Rule for execution

- The Product & Technical Controller executes the tests manually in the browser.
- AI agents must not claim PASS based only on source inspection.
- A manual test becomes PASS only after actual human execution and evidence.
- A manual test becomes FAIL when actual behavior differs from expected behavior.
- A test can be BLOCKED when the required feature, data, or account does not yet exist.

### 3.3 Evidence policy

Manual QA evidence should normally include:

- screenshot when visual behavior matters
- URL when route behavior matters
- visible error or success message
- actor/account used
- relevant record identifier
- concise actual-result summary

For security tests, evidence should include the direct URL or request attempt and the observed denial behavior where safe.

Do not require screenshots for every trivial click when they add no value.

---

## 4. First manual test scope: Program Administration

The current manual QA set is limited to the Program Administration UI currently implemented in the repository.

This set does not claim coverage of life-cycle workflows, judging, evaluation, outcomes, or full EAIC governance. Those are explicitly future work.

### 4.1 Authentication and entry

#### MT-01-001 — Super Admin can reach authenticated application

- Priority: Critical
- Feature/module: Authentication / entry
- Actor: Super Admin
- Account: `admin@example.com`
- Preconditions: User exists and is verified.
- Test data: Default application Super Admin account.
- Steps:
  1. Open the app login page.
  2. Sign in as the Super Admin.
  3. Confirm the app loads the authenticated dashboard or redirect destination.
- Expected UI result: Authenticated home/dashboard is shown.
- Expected backend/security result: Access is allowed for the verified Super Admin account.
- Expected database/business result: User session persists and no security denial occurs.
- Evidence required: URL, visible dashboard, account used.
- PASS criteria: User reaches the app without a verification or access denial.
- FAIL criteria: Redirect loop, login failure, or prevented access.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: Current app expects valid verified user auth.
- Status: NOT RUN

#### MT-01-002 — Unverified account is redirected to email verification

- Priority: Critical
- Feature/module: Authentication / entry
- Actor: Unverified user
- Account: any unverified local user
- Preconditions: Account exists but `email_verified_at` is null.
- Test data: Unverified user credentials.
- Steps:
  1. Attempt to sign in with the unverified account.
  2. Attempt to access a protected app route after login.
- Expected UI result: User is redirected to verification-related flow.
- Expected backend/security result: Route access is denied until verification is complete.
- Expected database/business result: No authenticated access to protected app content before verification.
- Evidence required: Redirect target, verification prompt, account used.
- PASS criteria: Verification requirement is enforced.
- FAIL criteria: Protected route loads without verification.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This is required by the current route middleware model.
- Status: NOT RUN

#### MT-01-003 — Verified authorized user can reach Program Administration

- Priority: Critical
- Feature/module: Authentication / entry
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Verified account with active Program Staff scope.
- Test data: Program A QA membership.
- Steps:
  1. Sign in as the QA Program Staff account.
  2. Navigate to the Programs workspace.
- Expected UI result: Program Administration pages are reachable.
- Expected backend/security result: Route access is allowed by active auth and verification.
- Expected database/business result: Program membership and permission scope are recognized.
- Evidence required: URL, page title, account used.
- PASS criteria: Program index or Program detail loads successfully.
- FAIL criteria: Denied access, incorrect redirect, or missing page.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This is the current baseline Program-scoped access path.
- Status: NOT RUN

### 4.2 Program navigation

#### MT-01-010 — Authorized actor sees Programs navigation

- Priority: High
- Feature/module: Program navigation
- Actor: Program Staff / Super Admin
- Account: `qa-program-staff@example.com` or `admin@example.com`
- Preconditions: Active auth and verified user state.
- Test data: Program visibility allowed.
- Steps:
  1. Sign in as an authorized actor.
  2. Inspect the left sidebar.
- Expected UI result: Programs navigation item is visible.
- Expected backend/security result: UI visibility matches current permitted access.
- Expected database/business result: No hidden or unauthorized record leakage is required to display the nav item.
- Evidence required: Sidebar screenshot or visible nav item; account used.
- PASS criteria: Programs nav appears for the authorized actor.
- FAIL criteria: Missing nav item or visible nav to unauthorized user.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: Current navigation is rendered using the shared ability gate.
- Status: NOT RUN

#### MT-01-011 — Unauthorized actor does not receive Program Administration navigation

- Priority: High
- Feature/module: Program navigation
- Actor: Decision Maker / Judge / Applicant
- Account: `qa-decision-maker@example.com`, `qa-judge@example.com`, `qa-applicant@example.com`
- Preconditions: Verified accounts without special Program Staff rights.
- Test data: Default QA actor data.
- Steps:
  1. Sign in as the actor.
  2. Inspect the sidebar.
- Expected UI result: Programs navigation is not shown for the unauthorized actor.
- Expected backend/security result: No program administration page access is granted without the proper permission model.
- Expected database/business result: No extra membership is implied.
- Evidence required: Sidebar view and account used.
- PASS criteria: Programs item remains hidden.
- FAIL criteria: Item visible to an unauthorized actor.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: The current repo does not yet define broader lifecycle authority for these actors.
- Status: NOT RUN

#### MT-01-012 — Programs navigation does not cause horizontal overflow in the sidebar

- Priority: High
- Feature/module: Program navigation / shell quality
- Actor: Program Staff / Super Admin
- Account: `qa-program-staff@example.com` or `admin@example.com`
- Preconditions: Authenticated verified user.
- Test data: Normal desktop viewport.
- Steps:
  1. Open the app and inspect the sidebar with the Programs item visible.
  2. Observe side-to-side overflow at standard desktop width.
- Expected UI result: No unintended horizontal scrollbar appears.
- Expected backend/security result: No route or permission change is involved.
- Expected database/business result: None.
- Evidence required: Screenshot or browser evidence of sidebar width/overflow state.
- PASS criteria: Sidebar remains within its intended width.
- FAIL criteria: Horizontal scrollbar or clipped content is observed.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This was previously identified and corrected in the browser; it must be manually verified, not assumed.
- Status: NOT RUN

### 4.3 Program index

#### MT-01-020 — Open Program list

- Priority: Critical
- Feature/module: Program index
- Actor: Program Staff / Super Admin
- Account: `qa-program-staff@example.com` or `admin@example.com`
- Preconditions: User is authenticated and verified.
- Test data: Program A and Program B dataset.
- Steps:
  1. Open the Programs page.
  2. Inspect the title, list rows, status badge, actions, and empty-state behavior.
- Expected UI result: The page loads successfully and shows the visible Program records with the expected metadata.
- Expected backend/security result: Only authorized visible records are exposed.
- Expected database/business result: Record visibility matches active membership and publication rules.
- Evidence required: Page title, screenshot, visible records.
- PASS criteria: Page loads and records are rendered correctly.
- FAIL criteria: Blank page, missing status, missing actions, or unauthorized records.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This is the baseline Program index acceptance test.
- Status: NOT RUN

#### MT-01-021 — Program Staff sees authorized Program records

- Priority: High
- Feature/module: Program index
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Active Program Staff membership exists for Program A.
- Test data: Program A and Program B fixture records.
- Steps:
  1. Sign in as Program Staff.
  2. Open the Programs index.
- Expected UI result: The user sees the Program records currently permitted in scope.
- Expected backend/security result: Authorized program records are visible; unauthorized ones remain hidden.
- Expected database/business result: Only active Program Staff membership context is used for visibility.
- Evidence required: Page screenshot and visible record names.
- PASS criteria: Program A visibility is correct and Program B remains outside scope.
- FAIL criteria: Out-of-scope Program appears or in-scope Program disappears.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: The current scope is intentionally narrow and tied to Program membership.
- Status: NOT RUN

#### MT-01-022 — Program Staff does not receive unauthorized Program administration access to an out-of-scope Program

- Priority: High
- Feature/module: Program index / security
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program Staff scope is limited to Program A.
- Test data: Program B data only.
- Steps:
  1. Sign in as Program Staff.
  2. Attempt to access Program B in a visible or direct route flow.
- Expected UI result: Program B administration actions are not shown and access is denied if attempted.
- Expected backend/security result: Route access fails due to membership scope checks.
- Expected database/business result: No program_staff scope is granted on Program B.
- Evidence required: URL, denial state, or hidden action state.
- PASS criteria: Program B is not accessible or modifiable in scope.
- FAIL criteria: Program B is visible or modifiable without permission.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-023 — Super Admin visibility matches current implemented Super Admin behavior

- Priority: High
- Feature/module: Program index / admin visibility
- Actor: Super Admin
- Account: `admin@example.com`
- Preconditions: User has Super Admin role.
- Test data: Current default test dataset.
- Steps:
  1. Sign in as Super Admin.
  2. Open the Programs page.
- Expected UI result: Super Admin sees the Programs workspace according to current implementation.
- Expected backend/security result: The route allows the Super Admin access model already defined in the repo.
- Expected database/business result: No unsupported future governance model is assumed.
- Evidence required: Page title and visible programs.
- PASS criteria: Super Admin access matches current implemented behavior.
- FAIL criteria: Super Admin access differs from current implementation without a documented change.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This test must reflect the actual implementation, not a future decision model.
- Status: NOT RUN

### 4.4 Program create

#### MT-01-030 — Authorized bootstrap-capable actor can open Create Program

- Priority: Critical
- Feature/module: Program create
- Actor: Authorized Program creator
- Account: `admin@example.com` or a user with `program.create`
- Preconditions: User has `program.create` permission.
- Test data: Valid bootstrap authority.
- Steps:
  1. Sign in as the authorized user.
  2. Open the Programs index.
  3. Select Create program.
- Expected UI result: Create form opens.
- Expected backend/security result: Create route is allowed.
- Expected database/business result: No record is created before form submission.
- Evidence required: URL and form screen.
- PASS criteria: Form opens successfully.
- FAIL criteria: Missing action or denied route.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-031 — Unauthorized actor cannot see Create Program

- Priority: Critical
- Feature/module: Program create / authorization
- Actor: Unauthorized actor
- Account: `qa-applicant@example.com` or any lacking `program.create`
- Preconditions: The actor is authenticated and verified.
- Test data: A user without create permission.
- Steps:
  1. Sign in as the actor.
  2. Open the Programs page.
- Expected UI result: No Create program action is visible.
- Expected backend/security result: Direct route attempts are denied.
- Evidence required: Visible nav/button state and direct route attempt observation.
- PASS criteria: Hidden UI and denied route access.
- FAIL criteria: Visible create control or route accepts unauthorized create action.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-032 — Valid Program can be created

- Priority: Critical
- Feature/module: Program create
- Actor: Authorized bootstrap-capable actor
- Account: `admin@example.com`
- Preconditions: Verified user with `program.create` permission.
- Test data:
  - Name: `Manual QA Test Program`
  - Code: `MQA-001`
  - Slug: `manual-qa-test-program`
  - Timezone: `Africa/Addis_Ababa`
  - opens_at < closes_at
- Steps:
  1. Open Create Program.
  2. Complete the valid fields.
  3. Submit the form.
- Expected UI result: Success message appears and the Program detail page displays.
- Expected backend/security result: Program is created and stored successfully.
- Expected database/business result: New Program record is created; the creator receives an active `program_staff` membership.
- Evidence required: Success message, resulting URL, created record identifier.
- PASS criteria: The created program is live and visible on the detail page.
- FAIL criteria: Validation error, page not created, or missing membership persistence.
- Actual result: NOT RUN
- Evidence reference: blank
- Notes: This is a key confirmation of the current bootstrap flow.
- Status: NOT RUN

#### MT-01-033 — Invalid required fields produce clear validation errors

- Priority: High
- Feature/module: Program create / validation
- Actor: Authorized creator
- Account: `admin@example.com`
- Preconditions: User is on the Create Program form.
- Test data: Missing required fields.
- Steps:
  1. Leave required fields empty or invalid.
  2. Submit the form.
- Expected UI result: Clear validation message appears at the relevant fields.
- Expected backend/security result: Submission is rejected before persistence.
- Expected database/business result: No Program is created.
- Evidence required: Validation screenshot or visible error text.
- PASS criteria: Clear input errors are shown and no record is created.
- FAIL criteria: No message, undefined error, or database mutation occurs.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-034 — Invalid date ordering: opens_at >= closes_at must be rejected

- Priority: Critical
- Feature/module: Program create / validation
- Actor: Authorized creator
- Account: `admin@example.com`
- Preconditions: Create Program form is open.
- Test data: `opens_at` equal or later than `closes_at`.
- Steps:
  1. Enter invalid dates.
  2. Submit.
- Expected UI result: Validation message clearly rejects the invalid range.
- Expected backend/security result: The request is rejected by validation.
- Expected database/business result: No new Program record is persisted.
- Evidence required: Error message and request result.
- PASS criteria: Invalid date ordering is rejected.
- FAIL criteria: Record is created or the validation message is unclear.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-035 — Valid timezone is accepted

- Priority: Medium
- Feature/module: Program create / validation
- Actor: Authorized creator
- Account: `admin@example.com`
- Preconditions: User can open the Create Program form.
- Test data: Valid timezone such as `Africa/Addis_Ababa`.
- Steps:
  1. Submit a valid Program with a valid timezone.
- Expected UI result: Create succeeds and the timezone is displayed.
- Expected backend/security result: The request passes validation and stores the timezone.
- Expected database/business result: Program record includes the selected timezone.
- Evidence required: Success page and stored record value.
- PASS criteria: Valid timezone is accepted.
- FAIL criteria: Validation rejects a valid timezone.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-036 — Duplicate Program code is rejected

- Priority: High
- Feature/module: Program create / validation
- Actor: Authorized creator
- Account: `admin@example.com`
- Preconditions: A Program with an existing code exists.
- Test data: Duplicate `code`.
- Steps:
  1. Attempt to create a second Program with the same code.
- Expected UI result: Validation error appears for the duplicate code.
- Expected backend/security result: Request fails and no duplicate record is inserted.
- Expected database/business result: One Program record remains for the code.
- Evidence required: Validation error message and record count.
- PASS criteria: Duplicate code is rejected.
- FAIL criteria: Duplicate record is inserted.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-037 — Duplicate Program slug is rejected

- Priority: High
- Feature/module: Program create / validation
- Actor: Authorized creator
- Account: `admin@example.com`
- Preconditions: A Program with the same slug exists.
- Test data: Duplicate `slug`.
- Steps:
  1. Submit a Program using an existing slug.
- Expected UI result: Validation error displayed.
- Expected backend/security result: No duplicate insert occurs.
- Expected database/business result: Existing slug remains unique.
- Evidence required: Validation screenshot and persisted record check.
- PASS criteria: Duplicate slug is rejected.
- FAIL criteria: Duplicated slug is accepted.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-038 — Direct unauthorized POST/create attempt is denied

- Priority: Critical
- Feature/module: Program create / security
- Actor: Unauthorized actor
- Account: `qa-applicant@example.com`
- Preconditions: No `program.create` permission.
- Test data: Valid Program payload.
- Steps:
  1. Attempt direct `POST /programs` with valid data.
- Expected UI result: Denial or redirect to an unauthorized state.
- Expected backend/security result: Route-level authorization fails.
- Expected database/business result: No new Program record is stored.
- Evidence required: Direct URL or request result and observed denial message.
- PASS criteria: Unauthorized create attempt is denied.
- FAIL criteria: Record is created or request passes.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

### 4.5 Program show

#### MT-01-040 — Authorized actor can open Program details

- Priority: High
- Feature/module: Program show
- Actor: Program Staff / Super Admin
- Account: `qa-program-staff@example.com` or `admin@example.com`
- Preconditions: User can access the target Program.
- Test data: Existing Program A or B.
- Steps:
  1. Open the Program detail page.
- Expected UI result: Program metadata and status are displayed.
- Expected backend/security result: Policy allows the user to view the record.
- Expected database/business result: Program detail corresponds to the correct stored record.
- Evidence required: Page title, URL, visible program metadata.
- PASS criteria: Program detail loads correctly.
- FAIL criteria: Access is denied or details mismatch data.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-041 — Out-of-scope actor cannot access protected Program directly

- Priority: Critical
- Feature/module: Program show / security
- Actor: Program Staff outside scope
- Account: `qa-program-staff@example.com`
- Preconditions: Membership is only valid for Program A.
- Test data: Program B record.
- Steps:
  1. Attempt to access Program B directly through a URL.
- Expected UI result: Access denied or route blocked.
- Expected backend/security result: Request fails because the user lacks valid scope.
- Expected database/business result: No record exposure beyond policy-approved visibility.
- Evidence required: URL and denial result.
- PASS criteria: Direct access is denied.
- FAIL criteria: Record is loaded without scope.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-042 — Displayed Program information matches supported backend data

- Priority: Medium
- Feature/module: Program show
- Actor: Authorized user
- Account: `admin@example.com`
- Preconditions: Record exists in DB.
- Test data: Program A or B record.
- Steps:
  1. Open the detail page and compare the visible fields with the stored values.
- Expected UI result: Program code, slug, description, timezone, and dates align to the backend record.
- Expected backend/security result: Only supported fields are exposed.
- Expected database/business result: No hidden internal-only values are visible.
- Evidence required: Screenshot and record check.
- PASS criteria: Visible detail matches backend data.
- FAIL criteria: Data mismatch or unsupported fields appear.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-043 — Unsupported/internal fields are not exposed

- Priority: Medium
- Feature/module: Program show / data exposure
- Actor: Authorized user
- Account: `admin@example.com`
- Preconditions: Program is visible and record exists.
- Test data: Program record that contains supported user-facing fields.
- Steps:
  1. Inspect the Program detail screen.
- Expected UI result: Only supported user-facing fields are displayed.
- Expected backend/security result: Internal-only parameters are not exposed through the response.
- Expected database/business result: No unsupported sensitive values appear.
- Evidence required: Screenshot of the detail page.
- PASS criteria: UI exposes only supported fields.
- FAIL criteria: Internal-only values are visible.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

### 4.6 Program edit

#### MT-01-050 — Authorized Program Staff sees Edit action for an in-scope Program

- Priority: High
- Feature/module: Program edit
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program A membership exists and is active.
- Test data: Program A.
- Steps:
  1. Open the Program detail page for Program A.
- Expected UI result: Edit action is visible.
- Expected backend/security result: `update` is permitted for the target Program.
- Evidence required: Visible Edit button and page context.
- PASS criteria: Edit appears for in-scope Program.
- FAIL criteria: Edit is absent or denied unexpectedly.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-051 — Program Staff outside the Program scope does not see Edit action

- Priority: High
- Feature/module: Program edit / scope
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: User is only in scope for Program A.
- Test data: Program B.
- Steps:
  1. Open Program B or inspect its actions.
- Expected UI result: Edit action is hidden.
- Expected backend/security result: `update` is denied.
- Evidence required: Action visibility and URL attempt.
- PASS criteria: Edit action is hidden and not usable.
- FAIL criteria: Edit action is visible for out-of-scope Program.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-052 — Authorized Program Staff can edit valid fields

- Priority: Critical
- Feature/module: Program edit
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program A is in scope and editable.
- Test data: Valid changed values for a Program record.
- Steps:
  1. Open Edit form.
  2. Update valid values.
  3. Submit the change.
- Expected UI result: Updated Program detail appears and success message is visible.
- Expected backend/security result: Update is allowed and persisted.
- Expected database/business result: Program record is updated in the correct row.
- Evidence required: Success message, final detail values, record identifier.
- PASS criteria: Valid edits are accepted and persisted.
- FAIL criteria: Validation or policy blocks valid changes.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-053 — Unauthorized direct edit request is denied

- Priority: Critical
- Feature/module: Program edit / security
- Actor: Unauthorized actor
- Account: `qa-applicant@example.com`
- Preconditions: No `program.update` scope to the target Program.
- Test data: Program A or B record ID.
- Steps:
  1. Submit a direct `PUT` request to the Program edit route.
- Expected UI result: Request fails or redirect to unauthorized state.
- Expected backend/security result: Authorization fails.
- Expected database/business result: No update occurs to the target Program.
- Evidence required: Request result and denial observation.
- PASS criteria: Direct unauthorized edit request is denied.
- FAIL criteria: The record is changed.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-054 — Archived/non-editable Program cannot be edited when policy disallows it

- Priority: Medium
- Feature/module: Program edit / status gating
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program status is archived or otherwise not editable under policy.
- Test data: Archived Program record.
- Steps:
  1. Attempt to open the edit route or action.
- Expected UI result: Edit is hidden or inaccessible.
- Expected backend/security result: Policy denies update.
- Expected database/business result: No modification occurs.
- Evidence required: Route result and status state.
- PASS criteria: Archived Program cannot be edited when policy disallows it.
- FAIL criteria: Edit route is accessible despite it being disallowed.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

### 4.7 Program publish

#### MT-01-060 — Authorized Program Staff sees Publish action for a valid draft Program

- Priority: Critical
- Feature/module: Program publish
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program A is a draft and in scope.
- Test data: Draft Program A.
- Steps:
  1. Open the Program detail page.
- Expected UI result: Publish action is visible.
- Expected backend/security result: `publish` policy succeeds for the target Program.
- Evidence required: Visible Publish button.
- PASS criteria: Publish is visible for a valid draft Program.
- FAIL criteria: Publish button absent or enabled unlawfully.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-061 — Publish requires confirmation

- Priority: High
- Feature/module: Program publish
- Actor: Authorized Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Draft Program in scope.
- Test data: Program A draft.
- Steps:
  1. Click Publish.
- Expected UI result: A confirmation dialog appears before state change.
- Expected backend/security result: No mutation occurs until the confirmation is accepted.
- Expected database/business result: Status remains unchanged until confirmation.
- Evidence required: Confirmation dialog screenshot.
- PASS criteria: Confirmation step is enforced.
- FAIL criteria: Publish occurs without confirmation.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-062 — Valid draft Program can be published

- Priority: Critical
- Feature/module: Program publish
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program A is a valid draft and in scope.
- Test data: Valid Program A.
- Steps:
  1. Proceed through the confirmation.
- Expected UI result: Success message appears and status transitions to published.
- Expected backend/security result: `publish` policy allows the state change.
- Expected database/business result: `status` becomes `published`; `published_at` is set.
- Evidence required: Success message, resulting detail view, record status.
- PASS criteria: Draft is successfully published.
- FAIL criteria: Status does not change or publish fails.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-063 — Published Program no longer exposes inappropriate publish action

- Priority: Medium
- Feature/module: Program publish / state handling
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Program is already published.
- Test data: Published Program A.
- Steps:
  1. Open the Program detail page.
- Expected UI result: Publish action is no longer shown for a published program.
- Expected backend/security result: Publish route remains disallowed by policy because status is not `draft`.
- Expected database/business result: Published status remains stable.
- Evidence required: Visible action state.
- PASS criteria: Publish disappears after publication.
- FAIL criteria: Publish remains available for a published Program.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-064 — Unauthorized actor cannot publish

- Priority: Critical
- Feature/module: Program publish / security
- Actor: Unauthorized user
- Account: `qa-applicant@example.com`
- Preconditions: No valid `program.publish` permission or scope.
- Test data: Draft Program record.
- Steps:
  1. Attempt direct publish request.
- Expected UI result: No action is available or access is denied.
- Expected backend/security result: Request is rejected by policy.
- Expected database/business result: Program remains draft.
- Evidence required: Direct route attempt and denial result.
- PASS criteria: Unauthorized actor cannot publish.
- FAIL criteria: Publish succeeds anyway.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-065 — Out-of-scope Program Staff cannot publish another Program

- Priority: Critical
- Feature/module: Program publish / scope enforcement
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Scope exists only for Program A.
- Test data: Program B.
- Steps:
  1. Attempt direct publish to Program B.
- Expected UI result: No publish action or denial response.
- Expected backend/security result: `hasActiveProgramStaffScope` fails for Program B.
- Expected database/business result: No status change occurs for Program B.
- Evidence required: URL and denial result.
- PASS criteria: Out-of-scope publish request is denied.
- FAIL criteria: Program B is published by out-of-scope staff.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-066 — Invalid date window cannot be published

- Priority: High
- Feature/module: Program publish / validation state
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Target Program is draft but its window is invalid.
- Test data: `opens_at >= closes_at` on a draft Program.
- Steps:
  1. Attempt to publish the invalid Program.
- Expected UI result: Publish is not available or the action fails.
- Expected backend/security result: Policy denies publication because the date window is invalid.
- Expected database/business result: Program remains draft or not published.
- Evidence required: UI state and back-end denial observation.
- PASS criteria: Invalid date window blocks publish.
- FAIL criteria: Invalid draft is published.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

### 4.8 Cross-program security

#### MT-01-070 — Program Staff assigned only to Program A attempts to access Program B

- Priority: Critical
- Feature/module: Cross-program security
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Active scope only for Program A.
- Test data: Program B record.
- Steps:
  1. Attempt to open Program B or manipulate it through the UI or direct URL.
- Expected UI result: Access denial or privacy boundary remains enforced.
- Expected backend/security result: Policy denies access without matching scope.
- Expected database/business result: Program B stays outside the actor’s operational scope.
- Evidence required: URL and denial behavior.
- PASS criteria: Cross-program access is denied.
- FAIL criteria: Program B can be reached or changed.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-071 — Program Staff attempts Program B edit through direct URL

- Priority: Critical
- Feature/module: Cross-program security
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Active scope only for Program A.
- Test data: Program B edit route.
- Steps:
  1. Attempt `GET /programs/{program-b}/edit` or the update route.
- Expected UI result: The route is denied or inaccessible.
- Expected backend/security result: `update` policy rejects direct access.
- Expected database/business result: Program B is unchanged.
- Evidence required: Direct URL attempt and denial response.
- PASS criteria: Direct edit to Program B is denied.
- FAIL criteria: Program B is edited unexpectedly.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

#### MT-01-072 — Program Staff attempts Program B publish through direct URL

- Priority: Critical
- Feature/module: Cross-program security
- Actor: Program Staff
- Account: `qa-program-staff@example.com`
- Preconditions: Active scope only for Program A.
- Test data: Program B publish route.
- Steps:
  1. Submit a direct `POST /programs/{program-b}/publish` request.
- Expected UI result: Denied response or blocked route.
- Expected backend/security result: `publish` policy rejects the request.
- Expected database/business result: Program B remains unchanged and unpublished if it is still draft.
- Evidence required: URL request attempt and observed denial.
- PASS criteria: Direct cross-program publish is denied.
- FAIL criteria: Publication occurs contrary to scope.
- Actual result: NOT RUN
- Evidence reference: blank
- Status: NOT RUN

---

## 5. Actor coverage matrix

| Actor | Current QA Account | What can currently be meaningfully tested |
|---|---|---|
| Super Admin | `admin@example.com` | Program administration according to the current Super Admin behavior |
| Program Staff | `qa-program-staff@example.com` | Program A scoped administration, including create/edit/publish based on the current implementation |
| Decision Maker | `qa-decision-maker@example.com` | Current lack of Program Staff authority and denied Program administration |
| Judge | `qa-judge@example.com` | Current lack of Program Staff authority and denied Program administration |
| Applicant | `qa-applicant@example.com` | Current lack of Program Staff authority and denied Program administration |

IMPORTANT

Do not claim Decision Maker, Judge, or Applicant EAIC lifecycle capabilities exist yet.

The current implementation does not yet provide a full, approved lifecycle authority model for those actors beyond basic QA account presence and denial expectations.

---

## 6. UI quality checks

These should be included as manual check steps in the execution workflow for every relevant page.

- Layout
- Spacing
- Readability
- Status badges
- Action placement
- Validation messages
- Empty state
- Success feedback
- Error feedback
- Responsive behavior
- Sidebar overflow
- Page horizontal overflow
- Visual consistency
- AdminLTE-inspired moderate color direction
- No excessive visual clutter

Manual QA should treat these as observable UI quality checks, not as backend requirements.

---

## 7. Broader QA coverage roadmap

The following future ManualTest documents should eventually cover the following areas.

### 7.1 Authentication

- login
- logout
- verification
- password reset
- session behavior

### 7.2 RBAC

- roles
- permissions
- capability
- scope
- direct URL authorization
- privilege escalation

### 7.3 CRUD

- create
- read
- update
- delete where permitted

### 7.4 Search

- search
- filters
- combined filters
- no-result states

### 7.5 Pagination

- page navigation
- page size
- boundaries
- empty result

### 7.6 Import / export

- CSV
- Excel
- validation
- duplicate handling
- authorization
- failure handling

### 7.7 Print

- print layout
- correct fields
- pagination
- confidentiality

### 7.8 Assignment

- assign
- reassign
- unassign
- scope
- conflict restrictions

### 7.9 Roles and permissions

- grant
- revoke
- role changes
- effective access

### 7.10 Activity and audit

- actor
- timestamp
- action
- target
- audit history
- immutability

### 7.11 Dashboard

- role-specific content
- counts
- widgets
- empty states
- authorization

### 7.12 Notifications

- in-app
- email
- authorization
- confidentiality
- failure/retry

### 7.13 Lifecycle

- state transitions
- invalid transitions
- revision
- locking
- reopening
- finalization

### 7.14 Data integrity

- required fields
- uniqueness
- relationships
- concurrency

### 7.15 Security

- IDOR
- cross-user
- cross-program
- privilege escalation
- hidden UI versus backend denial

### 7.16 EAIC lifecycle

- applications
- screening
- Judge assignment
- conflicts
- rubrics
- evaluation
- deliberation
- decisions
- outcomes
- transparency
- AI boundaries

This roadmap is a future planning reference only and is not a claim that these features are currently implemented.

---

## 8. Manual QA execution policy

The Product & Technical Controller will execute these tests manually.

AI agents must not claim PASS based only on source inspection.

A manual test becomes PASS only after actual human execution and evidence.

A manual test becomes FAIL when actual behavior differs from expected behavior.

A test can be BLOCKED when the required feature, data, or account does not yet exist.

---

## 9. Blank execution log

Use this section for future execution records.

| Date | Tester | Test ID | Result | Evidence | Defect reference | Notes |
|---|---|---|---|---|---|---|
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |

---

## 10. Current QA status at time of this document

At the time this document is created:

- UI exists.
- Backend Program authorization exists.
- QA fixture exists.
- Mailpit/local email verification exists.
- Browser QA has identified and corrected a sidebar horizontal overflow issue.
- Full manual Program test execution has not yet been completed.

These facts are recorded accurately.

---

## 11. Test execution policy

Do not execute Pest tests.

Do not execute a large automated suite.

This task is a manual-test specification task.

Lightweight static inspection is enough for documentation creation.

---

## 12. Mandatory result recording rule

All future manual tests must record:

- actual result
- evidence reference
- tester identity
- date
- pass/fail/block status
- defect reference when applicable

No current test is marked as PASS unless human execution has occurred.

---

## 13. Known current limitations

- The current Program Administration model is intentionally narrow.
- The broader EAIC lifecycle is not yet implemented.
- Decision Maker/Judge/Applicant lifecycle capabilities are not assumed.
- The current QA fixture reflects the current Batch 1 Program administration foundation, not a future full governance model.
- Program navigation and shell quality must still be manually verified in the browser.

---

## 14. Summary

This manual QA plan documents the current Program Administration reality of the repository, not an ideal future state.

It is intentionally scoped to existing implementation behavior, account data, policy enforcement, and user-visible UI quality expectations.

The Product & Technical Controller will execute these tests manually and record actual evidence before any test is marked as PASS.
