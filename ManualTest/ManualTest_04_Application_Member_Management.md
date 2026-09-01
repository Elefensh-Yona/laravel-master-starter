# Manual Test: Application Member Management

## Objective

Validate the narrow Application Member Management foundation without implementing full UI or delegated member workflows.

Every scenario below is `NOT RUN` until a human executes it in the browser or API client.

---

## MT-04-001: View application members

- **Actor:** Application primary owner
- **Preconditions:** Application exists and the user is the primary owner
- **Action:** Open the application member list route
- **Expected UI result:** Owner sees the list of current members and status values
- **Expected backend result:** `GET /applications/{application}/members` succeeds
- **Expected database result:** No mutation; read-only access
- **Security reason:** Listing members is distinct from modifying them and should follow application visibility + owner checks

## MT-04-002: Add a valid member

- **Actor:** Application primary owner
- **Preconditions:** Application exists; target user exists; target user is not already an active member of the application
- **Action:** Submit the member-create form
- **Expected UI result:** Success banner appears and the new member is listed
- **Expected backend result:** Member record created with `status = active`, `approved_by` set to actor, `joined_at` set
- **Expected database result:** One active row for `(application_id, user_id)`
- **Security reason:** Owner-controlled member addition is the minimal approved foundation without inventing a new permission

## MT-04-003: Duplicate active member rejected

- **Actor:** Application primary owner
- **Preconditions:** The target user is already an active member of the same Application
- **Action:** Submit the member-create form again
- **Expected UI result:** Validation error or denial message
- **Expected backend result:** Request denied by validation or database uniqueness guard
- **Expected database result:** No duplicate active membership created
- **Security reason:** Prevents ambiguous active authority for the same user/application relationship

## MT-04-004: Unauthorized user cannot add a member

- **Actor:** Unrelated user
- **Preconditions:** User is authenticated and has no ownership or authorization for the target Application
- **Action:** Call the member-create endpoint or route directly
- **Expected UI result:** Forbidden or redirect to access denial
- **Expected backend result:** `403` or policy denial
- **Expected database result:** No new member row
- **Security reason:** Permission alone must not bypass Application ownership/scope

## MT-04-005: Update a member status

- **Actor:** Application primary owner
- **Preconditions:** Existing active member exists
- **Action:** Mark member as `ended` or set removal status
- **Expected UI result:** Member status reflects the change
- **Expected backend result:** Member row updated and `ended_at`/`ended_by` set
- **Expected database result:** Status changes from `active` to `ended` without altering `primary_owner_id`
- **Security reason:** Owner controls member lifetime without altering ownership

## MT-04-006: Remove a member

- **Actor:** Application primary owner
- **Preconditions:** Existing active member exists; owner has permission to manage members
- **Action:** Delete or deactivate the member
- **Expected UI result:** Member disappears from active list or is marked ended
- **Expected backend result:** Member removed/deactivated through the owner-controlled policy gate
- **Expected database result:** `status = ended`, `ended_at` set, `ended_by` set
- **Security reason:** Inactive/removed members must not retain active management authority

## MT-04-007: Inactive member loses active authority

- **Actor:** Inactive/removed member
- **Preconditions:** Member record exists with `status = ended`
- **Action:** Attempt an active-only operation that would normally require membership authority
- **Expected UI result:** Access denied
- **Expected backend result:** Policy denies operation
- **Expected database result:** No active membership authority remains
- **Security reason:** Active-only access must be restricted to active members only

## MT-04-008: Primary owner boundary

- **Actor:** Application member who is not the primary owner
- **Preconditions:** User is a valid application member but not the owner
- **Action:** Attempt to edit the application owner or manage members in a way that would alter ownership
- **Expected UI result:** Access denied
- **Expected backend result:** Policy denial and no owner mutation
- **Expected database result:** `primary_owner_id` remains unchanged
- **Security reason:** Membership does not equal primary ownership

## MT-04-009: Direct URL protection

- **Actor:** Unrelated authenticated user
- **Preconditions:** User knows or guesses a member id and Application id for another Application
- **Action:** Call the member edit/destroy route directly
- **Expected UI result:** Forbidden; no privileged page
- **Expected backend result:** Denied by policy or route-level checks
- **Expected database result:** No member mutation
- **Security reason:** Direct record access must not bypass application scope and ownership checks

## MT-04-010: Cross-program security

- **Actor:** User with access to another program but not the target application program
- **Preconditions:** User is active in a different program context
- **Action:** Attempt to manipulate the member list for an application in another program
- **Expected UI result:** Denied or hidden
- **Expected backend result:** Policy denies cross-program manipulation
- **Expected database result:** No record mutation outside the allowed application scope
- **Security reason:** Program scope remains mandatory even when a user has some Application permissions

---

## Validation notes

- All future Application UI for this foundation remains intentionally out of scope.
- No new member permission is introduced.
- No owner-member auto-assignment is created.
- This document remains `NOT RUN` until a human validates it in a real environment.
