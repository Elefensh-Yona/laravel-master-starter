# Manual Test: Application Delivery (MT-03)

**Test Document:** Manual Test Specification for Application HTTP Delivery  
**Status:** NOT RUN BY DESIGN (see test strategy in Project-Roadmap.md)  
**Execution Date:** Not executed  
**Environment:** Local development (Laravel Sail) 

---

## MT-03-001: Create Application in Published Program

**Objective:** Verify that an authenticated user with `application.create` permission can create an Application in a published Program.

**Setup:**
- User authenticated and verified
- User has `application.create` permission
- At least one published Program exists

**Manual Steps:**
1. Navigate to `/applications/create`
2. Select a published Program from the dropdown
3. Select applicant type: INDIVIDUAL
4. Leave reference field empty (optional field)
5. Click "Create Application"

**Expected Result:**
- Browser redirects to `/applications/{id}` (show page)
- Success message displayed: "Application created successfully."
- Application record exists in database with:
  - `status` = `draft`
  - `applicant_type` = `INDIVIDUAL`
  - `primary_owner_id` = current user
  - `current_version_id` points to v1
- Initial ApplicationVersion exists with:
  - `version_number` = `1`
  - `status` = `draft`
  - `content` = `[]` (empty array)

**Verification:**
- Check database: `SELECT * FROM applications WHERE id = ?`
- Check database: `SELECT * FROM application_versions WHERE application_id = ?`
- Verify activity log: `SELECT * FROM activity_logs WHERE event = 'applications.created'`

---

## MT-03-002: Edit Draft Application Content

**Objective:** Verify that an application owner can edit a draft application version.

**Setup:**
- User owns a draft Application
- Application has a draft ApplicationVersion
- User authenticated and verified

**Manual Steps:**
1. Navigate to `/applications/{id}/edit`
2. Update content (simulate form inputs; actual content structure depends on domain schema)
3. Add revision reason (optional)
4. Click "Save Changes"

**Expected Result:**
- Browser redirects to `/applications/{id}` (show page)
- Success message displayed: "Application version updated successfully."
- ApplicationVersion record updated with:
  - `content` contains the new values
  - `version_number` unchanged (still `1`)
  - `status` still `draft`
  - `submitted_at` remains `NULL`
- Activity log records event: `applications.version_updated`

**Verification:**
- Check database: `SELECT content FROM application_versions WHERE id = ?`
- Check activity log for version_updated event

---

## MT-03-003: Submit Draft Application Version

**Objective:** Verify that an application owner can submit a draft version, making it immutable.

**Setup:**
- User owns a draft Application with completed content
- ApplicationVersion has status `draft`
- User authenticated and verified

**Manual Steps:**
1. Navigate to `/applications/{id}` (show page)
2. Review application content
3. Click "Submit Application" button
4. Confirm the submission action in modal or form

**Expected Result:**
- Browser redirects to `/applications/{id}` (show page)
- Success message displayed: "Application submitted successfully."
- Application record updated:
  - `status` = `submitted`
  - `submitted_at` = current timestamp
- ApplicationVersion record updated:
  - `status` = `submitted`
  - `submitted_at` = current timestamp
  - `submitted_by` = current user id
- Activity log records event: `applications.submitted`

**Verification:**
- Check database: `SELECT status, submitted_at FROM applications WHERE id = ?`
- Check database: `SELECT status, submitted_at, submitted_by FROM application_versions WHERE id = ?`
- Verify `submitted_at` is recent (within seconds of submission time)

---

## MT-03-004: Cannot Edit Submitted Application

**Objective:** Verify that a submitted application version cannot be edited (immutability enforcement).

**Setup:**
- User owns an Application with submitted version
- ApplicationVersion has status `submitted`
- User authenticated and verified

**Manual Steps:**
1. Navigate to `/applications/{id}/edit` directly
2. Attempt to modify content

**Expected Result:**
- Page redirect occurs before reaching edit form, OR
- Edit form is not displayed
- Error message shown: "Only draft versions can be edited." (or similar)
- Browser remains on `/applications/{id}` (show page)
- ApplicationVersion content in database is unchanged

**Verification:**
- Check database: submitted version content unchanged
- Verify no new activity log entry for version update

---

## MT-03-005: Create Revision of Submitted Application

**Objective:** Verify that an application owner can create a new revision after submission.

**Setup:**
- User owns an Application with submitted version (v1)
- ApplicationVersion v1 has status `submitted`
- User authenticated and verified

**Manual Steps:**
1. Navigate to `/applications/{id}` (show page)
2. Observe current version displayed as submitted
3. Click "Create Revision" button
4. Confirm revision action

**Expected Result:**
- Browser redirects to `/applications/{id}` (show page)
- Success message displayed: "Revision created successfully."
- New ApplicationVersion created:
  - `version_number` = `2`
  - `status` = `draft`
  - `content` = copy of v1 content
  - `supersedes_version_id` = v1 id
  - `created_by` = current user id
- Application record updated:
  - `current_version_id` now points to v2
- Previous version v1 remains:
  - `status` = `submitted` (unchanged)
  - `content` unchanged
  - `submitted_at` unchanged
- Activity log records event: `applications.revision_created`

**Verification:**
- Check database: `SELECT version_number, status FROM application_versions WHERE application_id = ? ORDER BY version_number`
- Verify v1 is unchanged: `SELECT submitted_at FROM application_versions WHERE version_number = 1`
- Verify v2 created: `SELECT version_number, content FROM application_versions WHERE version_number = 2`
- Verify supersedes: `SELECT supersedes_version_id FROM application_versions WHERE version_number = 2` should equal v1 id

---

## MT-03-006: Authorization Denied for Non-Owner

**Objective:** Verify that a non-owner cannot edit another user's application.

**Setup:**
- User A owns Application in Program
- User B authenticated and verified, but not owner
- User B has no `application.update` permission

**Manual Steps:**
1. Log in as User B (different from application owner)
2. Attempt direct navigation to `/applications/{application_id}/edit` or show page for User A's draft application
3. Attempt to modify content via direct URL or form submission

**Expected Result:**
- On navigation to edit: 403 Forbidden response
- On navigation to show (draft): 403 Forbidden response
- Error message or 403 page displayed
- No modification occurs in database

**Verification:**
- Verify 403 response code received
- Check database: application and version unchanged
- Verify no activity log for unauthorized update attempt (or log shows authorization failure)

---

## MT-03-007: Application Index with Proper Scope

**Objective:** Verify that application index respects user permissions and scope.

**Setup:**
- Multiple applications exist in database
- Some owned by current user, some by others
- User authenticated and verified

**Manual Steps:**
1. Navigate to `/applications` (index page)
2. Observe list of applications

**Expected Result:**
- Index page loads successfully
- User sees their own applications (where primary_owner_id = current user)
- User does NOT see other users' draft applications (unless user has `application.view` permission)
- Submitted applications may be visible to authorized staff (depends on permission)

**Verification:**
- Verify displayed applications match policy logic:
  - Own applications: always shown
  - Others' draft: hidden unless `application.view` permission
  - Submitted: visible if policy allows

---

## MT-03-008: Applicant with Submission Authority Can Submit

**Objective:** Verify that a permitted applicant can submit their own application using the canonical permission `application.submit`.

**Setup:**
- Authenticated applicant user
- User has `application.submit` permission and owns a draft application
- Application current version is in `draft` state

**Manual Steps:**
1. Log in as the applicant owner
2. Navigate to `/applications/{id}`
3. Click the submit button and confirm the action

**Expected Result:**
- Request succeeds only when both the permission and ownership check pass
- Application status changes from `draft` to `submitted`
- Version status changes from `draft` to `submitted`
- No submitted version may be mutated afterward

**Verification:**
- Check database records for application and version changes
- Confirm `submitted_at` is populated

---

## MT-03-009: Owner Without Submission Authority Is Denied

**Objective:** Verify that ownership alone does not bypass the required `application.submit` permission.

**Setup:**
- Authenticated owner user with draft application
- User lacks `application.submit` permission

**Manual Steps:**
1. Attempt direct submission via `/applications/{id}/submit`
2. Observe result

**Expected Result:**
- Request denied with permission failure or policy denial
- Application remains in draft state
- Version remains draft

**Verification:**
- Confirm no `submitted_at` value was populated
- Confirm state remained `draft`

---

## MT-03-010: Unauthorized Direct Submission Attempt Is Blocked

**Objective:** Verify that direct URL submission attempts do not bypass ownership or permission checks.

**Setup:**
- Another authenticated user with no ownership
- User may or may not have unrelated permissions

**Manual Steps:**
1. Attempt direct POST to `/applications/{application_id}/submit`
2. Use a different actor or direct URL bypass path

**Expected Result:**
- Request is rejected
- No database mutation to the target application or current version

**Verification:**
- Check target application status remains unchanged
- Confirm no `submitted_at` update occurred

---

## Test Execution Summary

| Scenario | Status | Notes |
|----------|--------|-------|
| MT-03-001 | NOT RUN | See design policy in ROADMAP |
| MT-03-002 | NOT RUN | See design policy in ROADMAP |
| MT-03-003 | NOT RUN | See design policy in ROADMAP |
| MT-03-004 | NOT RUN | See design policy in ROADMAP |
| MT-03-005 | NOT RUN | See design policy in ROADMAP |
| MT-03-006 | NOT RUN | See design policy in ROADMAP |
| MT-03-007 | NOT RUN | See design policy in ROADMAP |
| MT-03-008 | NOT RUN | See design policy in ROADMAP |
| MT-03-009 | NOT RUN | See design policy in ROADMAP |
| MT-03-010 | NOT RUN | See design policy in ROADMAP |

**Test Strategy:** Per Project-Roadmap.md, manual tests are documented for team reference and CI/CD integration phases but are not executed during incremental development. Automated feature test specifications (FeatureTest/) provide the executable acceptance criteria.
