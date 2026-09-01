# Application Runtime RBAC and Member Management Feature Specification

## APP-RUNTIME-RBAC-001

- **Test ID:** APP-RUNTIME-RBAC-001
- **Title:** Four Application permissions exist in the live permission registry
- **Actor:** System-level admin / dev verification actor
- **Account:** Runtime permission registry account
- **Preconditions:** Existing database is seeded with the standard project seeder
- **Program context:** N/A
- **Application context:** N/A
- **Action:** Inspect the runtime permission registry for `application.view`, `application.create`, `application.update`, and `application.submit`
- **Expected UI result:** N/A
- **Expected backend result:** All four permission names are present exactly once in the `permissions` table for the `web` guard
- **Expected database result:** `Permission::whereIn('name', [...])->count() === 4`
- **Security reason:** Route and policy enforcement require the permission names to exist in the live registry before enforcement can function as designed
- **Evidence requirement:** Print the permission rows or ORM query output showing the canonical set and guard

## APP-RUNTIME-RBAC-002

- **Test ID:** APP-RUNTIME-RBAC-002
- **Title:** No duplicate Application permissions are created
- **Actor:** System-level admin / dev verification actor
- **Account:** Runtime permission registry account
- **Preconditions:** Seed has run at least once
- **Program context:** N/A
- **Application context:** N/A
- **Action:** Query the live table for duplicate canonical Application permissions
- **Expected UI result:** N/A
- **Expected backend result:** One row per canonical permission with no duplicate names
- **Expected database result:** `count(distinct name) === count(name)` for the canonical set
- **Security reason:** Duplicate permission rows create unpredictable authorization outcomes and violate the controlled catalog
- **Evidence requirement:** ORM query output showing one record per permission name

## APP-MEMBER-001

- **Test ID:** APP-MEMBER-001
- **Title:** Authorized owner can add an approved member
- **Actor:** Application primary owner
- **Account:** Owner account on the target application
- **Preconditions:** Application exists; target user exists; target user is not already an active member of the same Application; owner has `application.update`
- **Program context:** Target program is active and relevant to the application
- **Application context:** Owner is the `primary_owner_id` for the application
- **Action:** Create a new `ApplicationMember` by calling the member-create route or direct policy check
- **Expected UI result:** Success confirmation appears
- **Expected backend result:** Allowed by policy because owner has `application.update` and owns the application
- **Expected database result:** New `application_members` row with `status = active`, `approved_by = actor`, `joined_at` set
- **Security reason:** Minimal approved member-management foundation requires explicit owner authorization without inventing a new permission
- **Evidence requirement:** Member row exists and no `primary_owner_id` mutation occurred

## APP-MEMBER-002

- **Test ID:** APP-MEMBER-002
- **Title:** Unauthorized user cannot add a member to another Application
- **Actor:** Unrelated authenticated user
- **Account:** User with no ownership or authorization to the target application
- **Preconditions:** Another Application exists; actor does not own it and lacks `application.update` for it
- **Program context:** Different program or unrelated application context
- **Application context:** Target application belongs to another owner
- **Action:** Submit member-create request for another application
- **Expected UI result:** Forbidden or access denial
- **Expected backend result:** Policy denies access with `403` or redirect to denial
- **Expected database result:** No new application member row inserted
- **Security reason:** Permission alone must not allow cross-Application manipulation
- **Evidence requirement:** Zero new member rows for the target application

## APP-MEMBER-003

- **Test ID:** APP-MEMBER-003
- **Title:** Duplicate active member is rejected
- **Actor:** Application primary owner
- **Account:** Owner account for application
- **Preconditions:** Target user already has an active member row for the same application
- **Program context:** The one in scope for the application
- **Application context:** Existing active membership row exists
- **Action:** Attempt to add the same active member a second time
- **Expected UI result:** Validation error or duplicate-member message
- **Expected backend result:** Request denied by validation or database uniqueness guard
- **Expected database result:** No second active membership row exists
- **Security reason:** Only one active membership per application/user is allowed
- **Evidence requirement:** Count of active rows for the `(application_id, user_id)` pair remains 1

## APP-MEMBER-004

- **Test ID:** APP-MEMBER-004
- **Title:** Authorized owner can update an allowed member attribute
- **Actor:** Application primary owner
- **Account:** Owner account for application
- **Preconditions:** Target member exists and is active
- **Program context:** Relevant program context
- **Application context:** Target application belongs to actor
- **Action:** Update member status or deactivate a member
- **Expected UI result:** Success or status change displayed
- **Expected backend result:** Allowed by member policy when actor is owner and has `application.update`
- **Expected database result:** Member row status changes or `ended_at`/`ended_by` fields are set
- **Security reason:** Owner-managed member status changes are an approved control without changing ownership
- **Evidence requirement:** Field-level DB update reflects the allowed change

## APP-MEMBER-005

- **Test ID:** APP-MEMBER-005
- **Title:** Unauthorized user cannot update another Application's member
- **Actor:** Unrelated authenticated user
- **Account:** User without ownership or member-management authority
- **Preconditions:** Member exists on another application
- **Program context:** Different scope from actor
- **Application context:** Target application does not belong to actor
- **Action:** Attempt to update or deactivate the member record
- **Expected UI result:** Denied
- **Expected backend result:** Policy denies `update`/`delete` action
- **Expected database result:** Member row remains unchanged
- **Security reason:** Application member records are scoped to the owning application and primary owner
- **Evidence requirement:** No mutation on the member row

## APP-MEMBER-006

- **Test ID:** APP-MEMBER-006
- **Title:** Authorized actor can remove/deactivate a member where policy allows it
- **Actor:** Application primary owner
- **Account:** Owner account with `application.update`
- **Preconditions:** Target member is active and valid for removal
- **Program context:** Application in target program
- **Application context:** Member belongs to the actor's application
- **Action:** Remove/deactivate the member
- **Expected UI result:** Member is no longer active
- **Expected backend result:** Allowed by owner policy and `application.update`
- **Expected database result:** `status = ended`, `ended_at` and `ended_by` set, no owner mutation
- **Security reason:** Inactive or removed members should not retain active management authority
- **Evidence requirement:** Member becomes inactive and cannot be used for active-only actions

## APP-MEMBER-007

- **Test ID:** APP-MEMBER-007
- **Title:** Inactive/removed member no longer has active membership authority
- **Actor:** A removed or ended application member
- **Account:** Member user whose status is now ended
- **Preconditions:** Member row exists with `status = ended`
- **Program context:** Same application program
- **Application context:** Application still exists
- **Action:** Attempt an active-only operation that depends on application membership
- **Expected UI result:** Denied or hidden
- **Expected backend result:** Access denied because `status` must be `active`
- **Expected database result:** No active membership authority retained
- **Security reason:** Removed members must not continue to carry active access
- **Evidence requirement:** The member cannot pass active-only policy checks

## APP-MEMBER-008

- **Test ID:** APP-MEMBER-008
- **Title:** Primary owner cannot be silently changed through ordinary member management
- **Actor:** Application primary owner or owner-like manager
- **Account:** Owner account
- **Preconditions:** Application has a primary owner and active members
- **Program context:** Program context is consistent with application ownership
- **Application context:** Target application has `primary_owner_id`
- **Action:** Attempt a member management action that would mutate the owner identity
- **Expected UI result:** Not allowed
- **Expected backend result:** Ownership remains unchanged; ordinary member management cannot replace primary ownership
- **Expected database result:** `primary_owner_id` remains exactly the same
- **Security reason:** Ownership transfer is distinct from membership and must not be silently changed by member management
- **Evidence requirement:** DB check confirms `primary_owner_id` unchanged after the operation

## APP-MEMBER-009

- **Test ID:** APP-MEMBER-009
- **Title:** Direct ApplicationMember identifier access cannot bypass application scope
- **Actor:** Unrelated authenticated user
- **Account:** User with no access to the target application
- **Preconditions:** Member record exists on another application
- **Program context:** Unrelated program or application context
- **Application context:** Member belongs to a different application
- **Action:** Directly request the member object by ID or URL
- **Expected UI result:** Denied
- **Expected backend result:** Resource authorization fails even if the member ID is known
- **Expected database result:** No mutation or disclosure outside the allowed application scope
- **Security reason:** Record-level policy must enforce application ownership/scope at every access point
- **Evidence requirement:** Request to member route returns denial and writes nothing

## APP-MEMBER-010

- **Test ID:** APP-MEMBER-010
- **Title:** Cross-program Application member manipulation is denied
- **Actor:** User with rights in a different program
- **Account:** User active in some other program
- **Preconditions:** Another program application exists
- **Program context:** Actor has program access elsewhere but not to the target Application's program
- **Application context:** Target Application belongs to a different program
- **Action:** Attempt member manipulation on the other program's Application
- **Expected UI result:** Denied or not visible
- **Expected backend result:** Policy denies access due to scope mismatch
- **Expected database result:** No member row adjusted
- **Security reason:** Program and application scope must remain enforced even when users have other valid access
- **Evidence requirement:** Target Application remains unchanged

---

## Evidence conventions

- Each scenario must capture actor account, auth state, application ID, program ID, relevant member ID, and verification query/result.
- Where the system is intentionally not yet implemented in UI, the evidence is a backend policy or DB assertion rather than a browser screenshot.
- No automated execution is performed here.
