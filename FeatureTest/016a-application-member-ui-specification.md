# Application Member UI Feature Specification

## Overview

This specification documents the Application Member Management UI component behavior and authorization-aware visibility within the Application Show page. These tests verify the frontend display layer and form interaction without requiring execution unless a blocker is encountered.

**Status:** `NOT RUN BY DESIGN`

---

## UI-MEMBER-001

- **Test ID:** UI-MEMBER-001
- **Title:** Authorized owner can view application members list
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission on target application
- **Preconditions:** 
  - Application exists
  - User is the `primary_owner_id` of the application
  - One or more active members exist
- **Program context:** Active program (or any published program)
- **Application context:** Owner is the primary owner; member list is not empty
- **Action:** 
  1. Navigate to `/applications/{application}`
  2. Scroll to or locate the "Application members" section
- **Expected UI result:** 
  - Members section renders with clear heading
  - Member list displays each active member with name, email, status badge, and joined date
  - Owner badge appears next to the primary owner's name if they are listed as a member
  - Status badges display with correct tone (success for active, warning for ended)
  - Empty state message does not appear when members exist
- **Expected backend result:** Controller returns `canManageMembers = true` and members array populated
- **Expected database result:** Read-only query returns active members ordered by `joined_at`
- **Security reason:** Member list visibility must be limited to authorized actors and should display accurate member information
- **Evidence requirement:** 
  - Screenshot of members section rendering
  - Verification that member names/emails match database records
  - Confirmation that non-owner members do not appear with owner badge
- **PASS criteria:** Members section is visible, member data is correct and correctly formatted
- **FAIL criteria:** Section not visible OR member data missing/incorrect OR unauthorized user sees management controls

---

## UI-MEMBER-002

- **Test ID:** UI-MEMBER-002
- **Title:** Unauthorized actor cannot see member-management controls
- **Actor:** Unrelated user or user without `application.update` permission
- **Account:** User with `application.view` permission only or no Application permissions
- **Preconditions:** 
  - Application exists
  - User is not the `primary_owner_id`
  - User does not have `application.update` permission
- **Program context:** Published program
- **Application context:** Application is visible to user (e.g., submitted status OR explicitly shared)
- **Action:** 
  1. Navigate to `/applications/{application}`
  2. Locate the members section
  3. Attempt to interact with member management controls
- **Expected UI result:** 
  - Members section renders (read-only) if user can view application
  - Add Member button is NOT visible
  - Member form is NOT rendered
  - Status selects and Remove buttons are NOT visible
  - Member names and status are visible but read-only
- **Expected backend result:** Controller returns `canManageMembers = false` in props
- **Expected database result:** Read-only query if user has view permission; otherwise section hidden
- **Security reason:** Member management must be owner-only; unauthorized UI elements must not be presented
- **Evidence requirement:** 
  - Screenshot showing members list without management controls
  - Verification that form elements are not in DOM or are disabled
  - Confirmation that browser inspection does not reveal hidden controls
- **PASS criteria:** No management UI visible or enabled for unauthorized actor
- **FAIL criteria:** Add Member button visible OR form inputs enabled OR status selects present

---

## UI-MEMBER-003

- **Test ID:** UI-MEMBER-003
- **Title:** Authorized owner can open Add Member UI
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission
- **Preconditions:** 
  - Application exists
  - User is primary owner
  - Add Member form exists but is initially collapsed or focused on select field
- **Program context:** Active program
- **Application context:** Owner is primary owner
- **Action:** 
  1. Navigate to `/applications/{application}`
  2. Locate the "Add member" button
  3. Click it
- **Expected UI result:** 
  - User select dropdown displays all available users with name and email
  - Form is ready for selection
  - Submit button ("Add member") is present and disabled until user is selected
- **Expected backend result:** Controller passes full user list in `memberUsers` prop (all users)
- **Expected database result:** No mutation; read-only list fetch
- **Security reason:** Owner must be able to select from available users without exposing the form to unauthorized actors
- **Evidence requirement:** 
  - Screenshot showing user dropdown populated with available options
  - Verification that form elements are interactive
- **PASS criteria:** User dropdown opens and displays users with name/email
- **FAIL criteria:** Dropdown empty OR form not interactive OR submit button not available

---

## UI-MEMBER-004

- **Test ID:** UI-MEMBER-004
- **Title:** Valid member can be added successfully
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission
- **Preconditions:** 
  - Application exists
  - Target user exists and is not already an active member
  - Owner has access to Add Member form
- **Program context:** Active program
- **Application context:** Owner is primary owner
- **Action:** 
  1. Open Add Member form
  2. Select an available user from dropdown
  3. Click "Add member" button
  4. Observe response
- **Expected UI result:** 
  - Success message or confirmation appears
  - Member list refreshes (if kept in viewport) or shows the new member
  - Form resets for next entry or closes
  - No validation errors displayed
- **Expected backend result:** 
  - `POST /applications/{application}/members` succeeds with 201/200
  - Redirect or success response with flash message
- **Expected database result:** 
  - New `application_members` row created
  - `status = active`, `approved_by = actor ID`, `joined_at = now()`
- **Security reason:** Owner-controlled member addition must work correctly when all preconditions are met
- **Evidence requirement:** 
  - Screenshot of success state or new member appearing in list
  - Verification of database insert
- **PASS criteria:** New member appears in list with correct status/date after submission
- **FAIL criteria:** Error message displayed OR member not added to list OR validation fails

---

## UI-MEMBER-005

- **Test ID:** UI-MEMBER-005
- **Title:** Duplicate active membership is rejected
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission
- **Preconditions:** 
  - Application exists
  - Target user is already an active member of this application
  - Owner attempts to add the same user again
- **Program context:** Active program
- **Application context:** Owner is primary owner; duplicate already exists in DB
- **Action:** 
  1. Open Add Member form
  2. Select a user that is already an active member
  3. Click "Add member"
- **Expected UI result:** 
  - Validation error message appears
  - Member list does not show duplicate
  - User can correct the selection and try again
- **Expected backend result:** 
  - Request denied by `StoreApplicationMemberRequest` validation
  - Validation error returned to frontend with clear message
- **Expected database result:** 
  - No new row created
  - Existing active member row unchanged
- **Security reason:** Duplicate active memberships create ambiguous authority and must be prevented
- **Evidence requirement:** 
  - Screenshot of validation error message
  - Verification that duplicate was prevented
- **PASS criteria:** Validation error shown; duplicate not created
- **FAIL criteria:** Duplicate created OR error not shown clearly

---

## UI-MEMBER-006

- **Test ID:** UI-MEMBER-006
- **Title:** Unauthorized direct member creation is denied
- **Actor:** Unrelated user or user without `application.update`
- **Account:** User with no ownership or update permission
- **Preconditions:** 
  - Application exists
  - User is not primary owner and has no `application.update` permission
  - User knows the application ID and target user ID
- **Program context:** Published program
- **Application context:** Application visible to user
- **Action:** 
  1. Attempt to craft and submit member creation request to `/applications/{application}/members`
  2. Include valid `user_id` in request body
- **Expected UI result:** 
  - Forbidden/access denied response
  - No member UI visible to craft the request
- **Expected backend result:** 
  - Policy check denies `create` on ApplicationMember
  - `403 Forbidden` response
- **Expected database result:** 
  - No new member row created
- **Security reason:** Authorization must be verified on backend regardless of frontend visibility
- **Evidence requirement:** 
  - Verification of 403 response or equivalent denial
  - Confirmation that policy gate prevented the operation
- **PASS criteria:** Request denied with 403 or equivalent
- **FAIL criteria:** Member created OR request succeeded

---

## UI-MEMBER-007

- **Test ID:** UI-MEMBER-007
- **Title:** Authorized owner can change member status
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission
- **Preconditions:** 
  - Application exists
  - Active member exists
  - Owner has access to member status controls
- **Program context:** Active program
- **Application context:** Owner is primary owner; target member is active
- **Action:** 
  1. Locate member row in the table
  2. Change member status from "Active" to "Ended" using the status select
  3. Observe response
- **Expected UI result:** 
  - Status dropdown changes to reflect new value
  - Member status badge updates (e.g., from success/green to warning/yellow)
  - Success feedback displayed or page refreshes
- **Expected backend result:** 
  - `PUT /applications/{application}/members/{member}` succeeds
  - Status updated to `ended`
  - `ended_at` and `ended_by` set
- **Expected database result:** 
  - Member row updated with `status = ended`, `ended_at = now()`, `ended_by = actor ID`
  - `primary_owner_id` unchanged
- **Security reason:** Owner-controlled member status changes must work correctly for normal workflows
- **Evidence requirement:** 
  - Screenshot of status change in UI
  - Verification of database update
- **PASS criteria:** Status changes correctly in UI and database without affecting ownership
- **FAIL criteria:** Status not updated OR primary_owner_id mutated OR error shown

---

## UI-MEMBER-008

- **Test ID:** UI-MEMBER-008
- **Title:** Unauthorized actor cannot modify another application's member
- **Actor:** User without authority over target application
- **Account:** User with no ownership or update permission
- **Preconditions:** 
  - Two applications exist
  - User owns Application A but not Application B
  - Application B has members
- **Program context:** Published program
- **Application context:** User is owner of different application
- **Action:** 
  1. Attempt to craft and submit member update/delete request for Application B's member
  2. Send `PUT /applications/B/{member}` or `DELETE /applications/B/{member}`
- **Expected UI result:** 
  - Access denied or forbidden response
  - No member UI visible for Application B to unauthorized user
- **Expected backend result:** 
  - Policy check denies `update` on ApplicationMember
  - `403 Forbidden` response
- **Expected database result:** 
  - No member row modified
- **Security reason:** Cross-application access attempts must be blocked by policy
- **Evidence requirement:** 
  - Verification of 403 response
  - Confirmation that backend policy enforced the boundary
- **PASS criteria:** Request denied; member unchanged
- **FAIL criteria:** Member modified OR request succeeded

---

## UI-MEMBER-009

- **Test ID:** UI-MEMBER-009
- **Title:** Inactive member loses active authority
- **Actor:** Member with `status = ended`
- **Account:** User who was a member but is now inactive
- **Preconditions:** 
  - Application exists
  - Member exists with `status = ended`
  - Application has active members or requires member-level operations
- **Program context:** Active program
- **Application context:** Application exists; target member is inactive
- **Action:** 
  1. Log in as the ended member
  2. Navigate to the application
  3. Attempt any member-only or owner-only action
- **Expected UI result:** 
  - Access denied or read-only display
  - No management controls visible
  - Application may be visible if owned by user or has submitted status
- **Expected backend result:** 
  - Policy check denies access based on active membership
  - Appropriate authorization response
- **Expected database result:** 
  - No new operations performed
  - Member row remains with `status = ended`
- **Security reason:** Inactive members must not retain authority after being ended
- **Evidence requirement:** 
  - Verification that ended member cannot perform restricted operations
  - Confirmation that policy checks respect inactive status
- **PASS criteria:** Ended member denied access to member-only operations
- **FAIL criteria:** Ended member can still perform restricted operations

---

## UI-MEMBER-010

- **Test ID:** UI-MEMBER-010
- **Title:** Primary owner is not changed by member-management actions
- **Actor:** Application primary owner
- **Account:** User with `application.update` permission
- **Preconditions:** 
  - Application exists
  - Primary owner is set to specific user ID
  - Owner performs member add/remove/update actions
- **Program context:** Active program
- **Application context:** Owner is primary owner
- **Action:** 
  1. Add multiple members
  2. Update member statuses
  3. Remove/end members
  4. Inspect application record
- **Expected UI result:** 
  - Member operations succeed
  - No unexpected redirects or errors
- **Expected backend result:** 
  - All member operations succeed
  - No `primary_owner_id` mutation in ApplicationController
- **Expected database result:** 
  - `applications.primary_owner_id` unchanged after all member operations
  - Member rows only are created/updated
- **Security reason:** Ownership must be immutable through member-management operations; only explicit ownership changes can alter primary ownership
- **Evidence requirement:** 
  - Verification that `primary_owner_id` remains constant
  - Query output before and after member operations
- **PASS criteria:** Primary owner unchanged after member operations
- **FAIL criteria:** Primary owner changed OR unexpected mutations occur

---

## UI-MEMBER-011

- **Test ID:** UI-MEMBER-011
- **Title:** Direct member URL cannot bypass application authorization
- **Actor:** Unrelated user
- **Account:** User without ownership or permission for target application
- **Preconditions:** 
  - Application exists with members
  - User is not owner and does not have `application.update`
  - User knows or guesses member ID
- **Program context:** Published program
- **Application context:** Application exists
- **Action:** 
  1. Attempt to navigate directly to `/applications/{application}/members`
  2. Attempt `PUT /applications/{application}/members/{member}`
  3. Attempt `DELETE /applications/{application}/members/{member}`
- **Expected UI result:** 
  - Access denied or redirect to application show page
  - No member management interface displayed
- **Expected backend result:** 
  - `ApplicationMemberPolicy` checks gate all operations
  - Unauthorized requests denied with appropriate response
- **Expected database result:** 
  - No mutations occur
- **Security reason:** Direct URL access must not bypass the authorization model
- **Evidence requirement:** 
  - Verification of 403 responses or redirects
  - Confirmation that policy enforcement is not bypassed
- **PASS criteria:** Direct access denied; no unauthorized operations
- **FAIL criteria:** Member operations allowed OR authorization bypassed

---

## UI-MEMBER-012

- **Test ID:** UI-MEMBER-012
- **Title:** Application pages remain free of unintended horizontal overflow
- **Actor:** Any user viewing Application pages
- **Account:** Any authenticated account
- **Preconditions:** 
  - Application exists
  - User can view the application
  - Member list may be empty or populated
- **Program context:** Any published program
- **Application context:** Any application
- **Action:** 
  1. Open Application Show page
  2. Inspect member list section if present
  3. Resize browser to mobile (320px), tablet (768px), and desktop (1024px+) widths
  4. Check for horizontal scrollbars or layout overflow
  5. Check sidebar for overflow
- **Expected UI result:** 
  - Member table uses responsive columns
  - Hidden columns on small viewports (md: hidden, xl: hidden classes)
  - Card/section boundaries contain content
  - No horizontal scrollbar appears at any viewport
  - Member form fields stack properly on mobile
  - Sidebar remains within viewport bounds
- **Expected backend result:** N/A
- **Expected database result:** N/A
- **Security reason:** UI usability; responsive design is part of the application quality standard
- **Evidence requirement:** 
  - Screenshots at mobile, tablet, desktop viewports
  - Verification of no horizontal overflow
  - Confirmation that responsive classes work as expected
- **PASS criteria:** No horizontal overflow at any tested viewport
- **FAIL criteria:** Horizontal scrollbar present OR content overflow OR layout broken

---

## Specification Summary

This document specifies 12 UI-layer tests for Application Member Management.

- Tests UI-MEMBER-001 through UI-MEMBER-012 verify member list visibility, authorization-aware UI, form interaction, and responsive behavior.
- All tests are marked `NOT RUN BY DESIGN` per project credit-efficient testing policy.
- Automated execution is not required unless a blocking issue requires focused diagnosis.
- These tests serve as acceptance criteria for the member management UI implementation.

---

## Notes

- Owner badge display is conditional: the owner appears as a member ONLY if an actual ApplicationMember row exists with that user ID.
- Member filtering in the Add Member dropdown already filters out active members using computed property.
- All member operations preserve application audit trail through `approved_by`, `ended_by`, and `end_reason` fields where present.
- Responsive behavior uses Tailwind CSS breakpoints: md (768px), xl (1024px).
