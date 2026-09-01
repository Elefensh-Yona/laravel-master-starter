# Manual Test: Application UI and Member Management

## Objective

Validate the complete Application UI foundation (list, create, show, edit) and member management functionality through human browser interaction. These tests describe manual verification scenarios that remain `NOT RUN` until explicitly executed.

---

## MT-05-001: Application index page displays list

- **Actor:** Any authenticated user with `application.view` permission or as application owner
- **Preconditions:** 
  - At least one application exists in the database
  - User is either the owner or has `application.view` permission
- **Manual steps:**
  1. Navigate to `/applications`
  2. Observe the page layout
- **Expected UI result:**
  - Page title: "Applications"
  - Description text visible
  - Create Application button visible (if user has `application.create`)
  - Applications table renders with columns: Application, Status, Type (hidden on mobile), Created (hidden on desktop)
  - Status badges show correct colors (blue/info for draft, green/success for submitted)
- **Expected backend result:** `GET /applications` returns filtered list based on user permission
- **Expected database result:** Applications ordered by created_at desc, filtered by owner or permission
- **Security reason:** Index must filter correctly and display only authorized applications
- **Evidence requirement:** Screenshot showing index page with applications
- **PASS criteria:** Table displays applications with correct layout and filtering
- **FAIL criteria:** Unauthorized applications visible OR formatting broken OR buttons missing

---

## MT-05-002: Owner can only see own applications (without application.view)

- **Actor:** Applicant user without `application.view` permission
- **Preconditions:**
  - User owns some applications but not others
  - User does not have `application.view` permission
  - Other applications exist in system
- **Manual steps:**
  1. Log in as applicant user
  2. Navigate to `/applications`
  3. Count applications visible
  4. Verify none are from other owners
- **Expected UI result:**
  - Only user's own applications are listed
  - Applications from other owners are not visible
- **Expected backend result:** Query filters by `primary_owner_id = user.id` when permission is missing
- **Expected database result:** Only applications where `primary_owner_id = actor.id` returned
- **Security reason:** Applicants must not see other applicants' applications without explicit view permission
- **Evidence requirement:** Screenshot showing filtered list; verification of user ownership
- **PASS criteria:** Only user's own applications visible; others hidden
- **FAIL criteria:** Other applications visible OR unauthorized data disclosure

---

## MT-05-003: Create application page loads with program list

- **Actor:** User with `application.create` permission
- **Preconditions:**
  - At least one published program exists
  - User has `application.create` permission
- **Manual steps:**
  1. Navigate to `/applications` (index)
  2. Click "Create application" button
  3. Observe form fields
- **Expected UI result:**
  - Page title: "Create application"
  - Description visible
  - Program dropdown populated with published programs
  - Applicant type select shows options: INDIVIDUAL, TEAM, ORGANIZATION
  - Reference field (optional) is present
  - Submit button labeled "Create draft"
- **Expected backend result:** `GET /applications/create` returns list of published programs
- **Expected database result:** Programs with `status = published` fetched
- **Security reason:** Only published programs should be available for new applications
- **Evidence requirement:** Screenshot of create form with populated program dropdown
- **PASS criteria:** Form renders with all fields and program list
- **FAIL criteria:** Form missing fields OR program list empty (when published programs exist) OR validation missing

---

## MT-05-004: User without permission cannot access create

- **Actor:** User without `application.create` permission
- **Preconditions:**
  - User has no `application.create` permission
  - User attempts to access create page
- **Manual steps:**
  1. Log in as user without `application.create`
  2. Attempt to navigate to `/applications/create`
  3. Observe response
- **Expected UI result:**
  - Forbidden page or redirect to index
  - No form displayed
- **Expected backend result:** Route middleware denies with 403
- **Expected database result:** No mutation
- **Security reason:** Create permission must be enforced by route middleware
- **Evidence requirement:** Verification of 403 or redirect
- **PASS criteria:** Access denied
- **FAIL criteria:** Form displayed OR unauthorized access granted

---

## MT-05-005: Create application with valid data

- **Actor:** User with `application.create` permission
- **Preconditions:**
  - At least one published program exists
  - User has `application.create` permission
- **Manual steps:**
  1. Navigate to create page
  2. Select a program from dropdown
  3. Select applicant type (e.g., "INDIVIDUAL")
  4. Enter reference (optional, e.g., "App-2026-001")
  5. Click "Create draft" button
  6. Observe response
- **Expected UI result:**
  - Success message or redirect to show page
  - New application displays with status "draft"
  - Draft appears in application list
- **Expected backend result:**
  - Application created with `status = draft`
  - First version created with `status = draft`
  - `primary_owner_id` set to actor
  - Activity logged
- **Expected database result:**
  - New row in `applications` table
  - New row in `application_versions` table with `version_number = 1`
  - `applications.current_version_id` points to new version
- **Security reason:** Application creation must be atomic and secure
- **Evidence requirement:** Screenshot of new application in list; database verification
- **PASS criteria:** Application created with correct owner and status
- **FAIL criteria:** Application not created OR owner incorrect OR status wrong

---

## MT-05-006: Create application with invalid program

- **Actor:** User with `application.create` permission
- **Preconditions:**
  - User attempts to create application for unpublished/non-existent program
- **Manual steps:**
  1. Attempt to submit form with invalid program_id (e.g., 99999)
  2. Observe response
- **Expected UI result:**
  - Validation error displayed
  - Error message indicates program not found or not available
- **Expected backend result:** Request validation fails; 422 response
- **Expected database result:** No new application created
- **Security reason:** Only published programs should be available
- **Evidence requirement:** Screenshot of validation error
- **PASS criteria:** Validation error shown; application not created
- **FAIL criteria:** Application created OR error not shown clearly

---

## MT-05-007: Application show page displays details (draft)

- **Actor:** Application primary owner
- **Preconditions:**
  - Draft application exists
  - User is primary owner
- **Manual steps:**
  1. Navigate to `/applications/{application}`
  2. Observe page layout
- **Expected UI result:**
  - Application ID in title and header
  - Reference displayed in description
  - Status badge shows "draft" (blue/info tone)
  - Overview section shows:
    - Program ID
    - Applicant type
    - Reference
    - Primary owner ID
    - Current version info (Version 1 · draft)
  - Lifecycle sidebar shows:
    - Submitted: — (not submitted yet)
    - Created: [date]
  - Action buttons visible:
    - Edit draft (pencil icon)
    - Submit application (send icon)
    - All applications (arrow back)
- **Expected backend result:** `GET /applications/{application}` returns full application with canEdit/canSubmit flags
- **Expected database result:** Read-only query returns application with current version
- **Security reason:** Authorized owner must see their draft application
- **Evidence requirement:** Screenshot of show page
- **PASS criteria:** All details display correctly; buttons present
- **FAIL criteria:** Information missing OR buttons not available for owner

---

## MT-05-008: Application show page displays submitted state

- **Actor:** Application primary owner or user with `application.view`
- **Preconditions:**
  - Submitted application exists
  - User is owner or has view permission
- **Manual steps:**
  1. Navigate to submitted application show page
  2. Observe state
- **Expected UI result:**
  - Status badge shows "submitted" (green/success tone)
  - Overview shows current version as "submitted"
  - Submitted date displayed in lifecycle sidebar
  - Action buttons:
    - Edit draft NOT visible
    - Submit application NOT visible
    - Revise submission visible (for owner)
- **Expected backend result:** Flags indicate `canEdit = false`, `canSubmit = false`, `canRevise = true` (for owner)
- **Expected database result:** Application status is "submitted"; version status is "submitted"
- **Security reason:** Submitted applications are immutable; only revision allowed for owner
- **Evidence requirement:** Screenshot of submitted state
- **PASS criteria:** State displays correctly; revision button only for owner
- **FAIL criteria:** Edit/submit buttons visible OR revision missing

---

## MT-05-009: Unauthorized user cannot edit another application

- **Actor:** User without authority over the target application
- **Preconditions:**
  - Application exists
  - User is not owner and has no `application.update`
- **Manual steps:**
  1. Attempt to navigate to `/applications/{application}/edit`
  2. Observe response
- **Expected UI result:**
  - Forbidden page or redirect
  - No edit form displayed
- **Expected backend result:** Policy denies update; 403 response
- **Expected database result:** No mutation
- **Security reason:** Only owner with permission can edit
- **Evidence requirement:** Verification of 403 or denial
- **PASS criteria:** Access denied
- **FAIL criteria:** Edit page displayed OR unauthorized access granted

---

## MT-05-010: Owner can edit draft application

- **Actor:** Application primary owner
- **Preconditions:**
  - Draft application exists
  - User is primary owner
  - Application has a draft version
- **Manual steps:**
  1. Navigate to `/applications/{application}`
  2. Click "Edit draft" button
  3. Observe edit page
- **Expected UI result:**
  - Page title: "Edit application"
  - Content editor: textarea with 18 rows
  - Placeholder shows empty object or previous content
  - "Save draft" button present
  - Breadcrumbs show: Applications > #[id] > Edit
- **Expected backend result:** `GET /applications/{application}/edit` returns current version content
- **Expected database result:** Read-only query returns draft version
- **Security reason:** Only owner can edit draft; submitted versions are read-only
- **Evidence requirement:** Screenshot of edit form
- **PASS criteria:** Edit form displays with current content
- **FAIL criteria:** Form missing OR previous content not shown

---

## MT-05-011: Owner can save draft changes

- **Actor:** Application primary owner
- **Preconditions:**
  - Draft application exists
  - User is owner
  - Edit form is open
- **Manual steps:**
  1. Modify content in textarea (e.g., add JSON fields)
  2. Click "Save draft" button
  3. Observe response
- **Expected UI result:**
  - Success message or redirect to show page
  - Changes persist when returning to edit page
- **Expected backend result:**
  - `PUT /applications/{application}` succeeds
  - Version content updated
  - Activity logged
- **Expected database result:**
  - `application_versions.content` updated
  - No new version created (still version 1)
  - No status change
- **Security reason:** Draft editing must update content safely
- **Evidence requirement:** Screenshot of save confirmation; re-load verification
- **PASS criteria:** Changes saved and persist
- **FAIL criteria:** Changes not saved OR error shown

---

## MT-05-012: Owner cannot edit submitted application

- **Actor:** Application primary owner
- **Preconditions:**
  - Submitted application exists
  - User is owner
  - Application has submitted version
- **Manual steps:**
  1. Navigate to `/applications/{application}`
  2. Attempt to click "Edit draft" button (should not be visible)
  3. If visible, click it and attempt to edit
- **Expected UI result:**
  - Edit button should NOT be visible on submitted application
  - If edit URL is accessed directly, error message appears
- **Expected backend result:** Policy denies update for submitted status
- **Expected database result:** No mutation
- **Security reason:** Submitted applications are immutable; editing blocked
- **Evidence requirement:** Verification that edit not allowed
- **PASS criteria:** Edit action blocked for submitted application
- **FAIL criteria:** Edit allowed OR form displays

---

## MT-05-013: Owner can submit draft application

- **Actor:** Application primary owner
- **Preconditions:**
  - Draft application exists
  - User is owner
  - User has `application.submit` permission
  - Application has draft version with content
- **Manual steps:**
  1. Navigate to application show page
  2. Click "Submit application" button
  3. Confirm any dialog if present
  4. Observe response
- **Expected UI result:**
  - Success message or page reload
  - Status changes to "submitted" (green badge)
  - Submit button disappears
  - Revise button becomes available
  - Submitted date appears in lifecycle
- **Expected backend result:**
  - `POST /applications/{application}/submit` succeeds
  - Version status changed to `submitted`
  - Application status changed to `submitted`
  - `submitted_at` timestamps set
  - Activity logged
- **Expected database result:**
  - `applications.status = submitted`, `submitted_at = now()`
  - `application_versions.status = submitted`, `submitted_at = now()`
- **Security reason:** Submission transitions draft to immutable state
- **Evidence requirement:** Screenshot of submitted state; database verification
- **PASS criteria:** Application submitted; status updated; buttons adjusted
- **FAIL criteria:** Status not changed OR button state incorrect

---

## MT-05-014: Unauthorized user cannot submit another application

- **Actor:** User without authority
- **Preconditions:**
  - Application exists
  - User is not owner and/or lacks `application.submit`
- **Manual steps:**
  1. Attempt to craft POST request to `/applications/{application}/submit`
  2. Observe response
- **Expected UI result:**
  - No submit button visible for unauthorized user
- **Expected backend result:** Policy denies; 403 response
- **Expected database result:** No status change
- **Security reason:** Submit permission must be checked
- **Evidence requirement:** Verification of denial
- **PASS criteria:** Request denied
- **FAIL criteria:** Application submitted

---

## MT-05-015: Owner can revise submitted application

- **Actor:** Application primary owner
- **Preconditions:**
  - Submitted application exists
  - User is owner
  - User has `application.update` permission
- **Manual steps:**
  1. Navigate to submitted application show page
  2. Click "Revise submission" button
  3. Confirm any dialog
  4. Observe response
- **Expected UI result:**
  - Page reloads or redirects to edit page
  - New version is draft (version 2)
  - Edit form opens with content from previous version
  - Status now shows "draft" (blue badge)
- **Expected backend result:**
  - `POST /applications/{application}/revise` succeeds
  - New version created with incremented version_number
  - New version status is `draft`
  - Previous version remains `submitted`
  - Current application status returns to `draft`
- **Expected database result:**
  - New row in `application_versions` with `version_number = 2`
  - New version has `status = draft`
  - `supersedes_version_id` points to previous version (version 1)
  - `applications.status = draft`
  - `applications.current_version_id` points to new version
- **Security reason:** Revision creates new draft without destroying submission history
- **Evidence requirement:** Screenshot of new draft version; database verification
- **PASS criteria:** New draft version created; version number incremented
- **FAIL criteria:** Version not created OR version_number not incremented

---

## MT-05-016: Application members section displays (owner)

- **Actor:** Application primary owner
- **Preconditions:**
  - Application exists
  - Application has active members (1+ records)
  - User is owner
- **Manual steps:**
  1. Navigate to `/applications/{application}`
  2. Scroll to members section
- **Expected UI result:**
  - "Application members" heading visible
  - Member list table renders with columns:
    - Member (name, owner badge if applicable, email)
    - Status (badge: success for active, warning for ended)
    - Joined (date, hidden on mobile)
    - Actions (status dropdown, remove button)
  - Add member button visible
  - Each active member displays with correct data
- **Expected backend result:** Members array passed to template; `canManageMembers = true`
- **Expected database result:** Members query returns active members ordered by joined_at
- **Security reason:** Owner must see complete member list for management
- **Evidence requirement:** Screenshot of members section
- **PASS criteria:** Member list displays with correct data and formatting
- **FAIL criteria:** Members not shown OR data incorrect

---

## MT-05-017: Members section is read-only (unauthorized)

- **Actor:** User with `application.view` only
- **Preconditions:**
  - Application has members
  - User can view application
  - User is not owner
  - User does not have `application.update`
- **Manual steps:**
  1. Navigate to submitted/published application
  2. Observe members section
- **Expected UI result:**
  - Member list visible (read-only)
  - Names, emails, and statuses displayed
  - Add member button NOT visible
  - Status dropdown/select NOT visible
  - Remove buttons NOT visible
  - Form section NOT rendered
- **Expected backend result:** Members visible; `canManageMembers = false`
- **Expected database result:** Read-only query
- **Security reason:** Unauthorized users must not see management controls
- **Evidence requirement:** Screenshot showing read-only display
- **PASS criteria:** Members visible but no management UI
- **FAIL criteria:** Management controls visible OR form present

---

## MT-05-018: Owner can open Add Member form

- **Actor:** Application primary owner
- **Preconditions:**
  - Application exists
  - User is owner
  - Members section is present
- **Manual steps:**
  1. Navigate to application show page
  2. Locate "Add member" button (in members section header)
  3. Click button
  4. Observe form
- **Expected UI result:**
  - User select dropdown displays all available users
  - Each option shows "Name — email"
  - Submit button ("Add member") present and disabled until selection
  - Form is ready for input
- **Expected backend result:** Controller passes full user list in `memberUsers` prop
- **Expected database result:** No mutation
- **Security reason:** Owner must access member selection
- **Evidence requirement:** Screenshot of open form
- **PASS criteria:** Form opens with user dropdown populated
- **FAIL criteria:** Dropdown empty (when users exist) OR form not functional

---

## MT-05-019: Owner adds valid member

- **Actor:** Application primary owner
- **Preconditions:**
  - Application exists
  - Owner can access Add Member form
  - Target user exists and is not already active member
- **Manual steps:**
  1. Open Add Member form
  2. Select a user from dropdown
  3. Click "Add member" button
  4. Observe response
- **Expected UI result:**
  - Success feedback or page refresh
  - New member appears in table with status "Active"
  - Form resets or closes
- **Expected backend result:**
  - `POST /applications/{application}/members` succeeds
  - Member created with status = active
- **Expected database result:**
  - New `application_members` row
  - `status = active`, `joined_at = now()`, `approved_by = actor`
- **Security reason:** Valid member creation must work correctly
- **Evidence requirement:** Screenshot of new member in list; database verification
- **PASS criteria:** Member added and listed correctly
- **FAIL criteria:** Member not added OR validation fails

---

## MT-05-020: Duplicate active member rejected

- **Actor:** Application primary owner
- **Preconditions:**
  - Application exists
  - Target user is already active member
  - Owner attempts to add again
- **Manual steps:**
  1. Open Add Member form
  2. Select a user already in active members
  3. Click "Add member"
  4. Observe response
- **Expected UI result:**
  - Validation error shown
  - Member list does not show duplicate
  - User can select different member and retry
- **Expected backend result:** Validation fails; error returned
- **Expected database result:** No duplicate created
- **Security reason:** Duplicate memberships must be prevented
- **Evidence requirement:** Screenshot of error
- **PASS criteria:** Duplicate rejected with clear message
- **FAIL criteria:** Duplicate created OR error not shown

---

## MT-05-021: Owner changes member status to ended

- **Actor:** Application primary owner
- **Preconditions:**
  - Application exists
  - Active member exists
  - Owner has access to status control
- **Manual steps:**
  1. Locate member row
  2. Change status select from "Active" to "Ended"
  3. Observe response
- **Expected UI result:**
  - Status badge updates to "Ended" (warning/yellow)
  - Page refreshes or updates
  - Member remains in list but with ended status
- **Expected backend result:**
  - `PUT /applications/{application}/members/{member}` succeeds
  - Member status changed to `ended`
  - `ended_at` and `ended_by` set
- **Expected database result:**
  - Member row updated: `status = ended`, `ended_at = now()`, `ended_by = actor`
  - No ownership mutation
- **Security reason:** Deactivating members must work correctly
- **Evidence requirement:** Screenshot of status change; database verification
- **PASS criteria:** Member status changed to ended
- **FAIL criteria:** Status not changed OR primary_owner_id altered

---

## MT-05-022: Owner removes member

- **Actor:** Application primary owner
- **Preconditions:**
  - Active member exists
  - Owner has remove button available
- **Manual steps:**
  1. Locate member row
  2. Click "Remove" button
  3. Confirm any dialog
  4. Observe response
- **Expected UI result:**
  - Member removed from list or marked as ended
  - Success feedback shown
- **Expected backend result:**
  - `DELETE /applications/{application}/members/{member}` succeeds
  - Member deactivated (status = ended)
- **Expected database result:**
  - Member `status = ended`, `ended_at = now()`, `ended_by = actor`
- **Security reason:** Member removal must work
- **Evidence requirement:** Screenshot of removal; verification
- **PASS criteria:** Member deactivated
- **FAIL criteria:** Member not removed OR error shown

---

## MT-05-023: Application UI has no horizontal overflow (mobile)

- **Actor:** Any user viewing applications
- **Preconditions:**
  - Application exists (any status)
  - Browser at mobile width (320px)
- **Manual steps:**
  1. Resize browser to 320px width
  2. Navigate to application index
  3. Navigate to show page
  4. Scroll page and observe sidebar
  5. Check for horizontal scrollbar
- **Expected UI result:**
  - No horizontal scrollbar appears
  - Content stacks vertically
  - Sidebar within bounds
  - Member table columns hidden appropriately
- **Expected backend result:** N/A
- **Expected database result:** N/A
- **Security reason:** Responsive design is quality standard
- **Evidence requirement:** Screenshot at mobile width
- **PASS criteria:** No horizontal overflow
- **FAIL criteria:** Scrollbar present OR content overflow

---

## MT-05-024: Application UI responsive (tablet)

- **Actor:** Any user viewing applications
- **Preconditions:**
  - Application exists
  - Browser at tablet width (768px)
- **Manual steps:**
  1. Resize to 768px
  2. Navigate application pages
  3. Check member table layout
- **Expected UI result:**
  - Grid layout adjusts appropriately
  - Member table shows: Member, Status, Actions (Joined hidden)
  - No overflow
- **Expected backend result:** N/A
- **Expected database result:** N/A
- **Evidence requirement:** Screenshot at tablet width
- **PASS criteria:** Layout responsive and usable
- **FAIL criteria:** Overflow OR layout broken

---

## MT-05-025: Application UI responsive (desktop)

- **Actor:** Any user viewing applications
- **Preconditions:**
  - Application exists
  - Browser at desktop width (1024px+)
- **Manual steps:**
  1. Resize to 1024px or larger
  2. Navigate application pages
  3. Check full layout
- **Expected UI result:**
  - All table columns visible: Member, Status, Joined, Actions
  - Full grid layout displayed
  - No overflow
  - Sidebar visible and properly positioned
- **Expected backend result:** N/A
- **Expected database result:** N/A
- **Evidence requirement:** Screenshot at desktop width
- **PASS criteria:** Full layout displays correctly
- **FAIL criteria:** Overflow OR layout issues

---

## Manual Test Summary

This document specifies 25 manual test scenarios for the complete Application UI and member management foundation.

- Tests MT-05-001 through MT-05-025 cover application lifecycle (index, create, show, edit, submit, revise), member management (list, add, status change, remove), authorization boundaries, and responsive behavior.
- All tests are `NOT RUN BY DESIGN` per project credit-efficient testing policy.
- These tests remain documentation and acceptance criteria until explicitly executed by human QA.

---

## Notes

- "Draft" status is blue/info tone in UI
- "Submitted" status is green/success tone
- "Ended" member status is warning/yellow tone
- Member filtering prevents adding duplicate active members through UI
- Responsive breakpoints: md (768px), xl (1024px)
- Members section only renders if user can manage OR members exist
- Owner badge appears only when member.userId === application.primaryOwnerId
