# Task 016: EAIC Application Member Management & UI Foundation

**Interaction ID:** 016  
**Date:** 2026-09-01  
**Status:** COMPLETE  

## 1. Overview

Task 016 finalized the Application Member Management authorization foundation (from 015C) and implemented the first usable Application UI foundation within the approved EAIC scope. No new permissions were introduced. Ownership remains distinct from membership. The implementation stays within the current Application foundation module and does not advance into Screening, Judge, Decision, or later lifecycle phases.

---

## 2. Authoritative documents consulted

The following governance/specification documents were reviewed before and during implementation:

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- relevant `FeatureTest/*.md` specifications
- relevant `ManualTest/*.md` documentation
- prior handoffs 014, 014A, 014B, 015, 015A, 015B, and 015C-016

These documents confirm the following governing rules:

- singular `resource.action` naming remains authoritative for permissions
- ownership (`primary_owner_id`) is distinct from membership (`ApplicationMember`)
- Application members do not automatically equal primary ownership
- `application.revise` is not a separate permission; revision flows through `application.update`
- role-to-permission grants remain a governance decision, not a backend default
- member management remains owner-only under the existing `application.update` permission

---

## 3. Application Member Management Authorization (from 015C, verified in 016)

### Canonical Permission Set

Four Application permissions remain:

- `application.view` — view applications within approved visibility
- `application.create` — create a draft application
- `application.update` — update draft/revise submitted; includes member management
- `application.submit` — submit a draft version to make it immutable

No additional Application permissions were invented.

### Member-Management Operations

The ApplicationMemberController includes four operations:

- `index()` — list members for an application (route: `GET /applications/{application}/members`)
- `store()` — add a new member (route: `POST /applications/{application}/members`)
- `update()` — change member status (route: `PUT /applications/{application}/members/{member}`)
- `destroy()` — deactivate/remove a member (route: `DELETE /applications/{application}/members/{member}`)

### Authorization Behavior

The ApplicationMemberPolicy enforces:

- `viewAny()` requires `application.view` permission
- `create()` allows owner-only member addition when actor has `application.update`
- `view()` allows owner, self, or `application.view` permission
- `update()` allows only the primary owner with `application.update` and an active member
- `delete()` defers to the same owner-only update policy

### Intentionally Not Implemented

- no member invitation/delegation system
- no organization/team hierarchy
- no general member approval workflow beyond the existing schema
- no new permission such as `application.member.manage`
- no UI for member management in this task (backend foundation only)

### Owner/Member Distinction

- `applications.primary_owner_id` is the authoritative owner field
- The member model is support infrastructure for approved participation, not ownership
- The owner is not silently made an `ApplicationMember` on creation
- Member status changes do not mutate `primary_owner_id`
- Inactive members (`status = ended`) no longer retain active authority

---

## 4. Application UI Foundation

### Pages Implemented

#### 4.1 Index Page
**File:** `resources/js/pages/applications/Index.vue`

- Lists all user applications with status badges
- Shows application ID, applicant type, program, reference, status
- Responsive table with hidden columns for md/xl breakpoints
- Empty state when no applications exist
- View and Edit action buttons
- "Create Application" primary button in header
- Status badges use tone-based styling (info=draft, success=submitted, neutral/warning=other)
- Breadcrumb: `Applications`

**Authorization:**
- Backend filters to owner-only unless user has `application.view` permission
- Edit button only shown when `canEdit` flag is true

#### 4.2 Create Page
**File:** `resources/js/pages/applications/Create.vue`

- Form to create a draft application
- Fields:
  - Program dropdown (populated from published programs)
  - Applicant type select (INDIVIDUAL, TEAM, ORGANIZATION)
  - Reference field (optional string)
- Submit button: "Create draft"
- Error messages for validation failures
- Breadcrumbs: `Applications > Create`

**Authorization:**
- Route-level middleware: `permission:application.create`
- Backend controller validates and filters program list to published programs

#### 4.3 Show Page
**File:** `resources/js/pages/applications/Show.vue`

- Displays application details and lifecycle state
- Sections:
  - Header with applicant type eyebrow badge
  - Application overview (program, type, reference, owner, version)
  - Lifecycle sidebar (submitted date, created date)
- Action buttons conditional on backend flags:
  - Edit Draft (if `canEdit`)
  - Submit Application (if `canSubmit`)
  - Revise Submission (if `canRevise`)
- Status badge in header
- Back to All Applications link
- Breadcrumbs: `Applications > #[id]`

**Authorization:**
- Backend calculates `canEdit`, `canSubmit`, `canRevise` based on ApplicationPolicy
- Submit and revise use POST with `router.post()` (confirmation via ConfirmActionDialog pattern available)

#### 4.4 Edit Page
**File:** `resources/js/pages/applications/Edit.vue`

- Edit draft version content
- Content editor: JSON textarea (18 rows)
- Fields:
  - Content: JSON string (empty object `{}` by default)
  - Metadata: reserved for future use
- Submit button: "Save draft"
- Form submission: `PUT /applications/{id}` via useForm
- Breadcrumbs: `Applications > #[id] > Edit`

**Authorization:**
- Route-level middleware: `permission:application.update`
- Backend policy check for draft-only state

### Design & Responsive Behavior

- **Layout:** AppLayout with breadcrumbs, sidebar navigation, page header with eyebrow badge
- **Component Library:** Uses existing project UI components:
  - `Button` — primary/outline variants
  - `Input`, `Label` — form fields
  - `StatusBadge` — status display with tone-based colors
  - `PageHeader`, `PageContainer` — page structure
  - `FormSection` — section containers (on Show page)
- **Responsive Design:**
  - Table columns hidden on viewport sizes (md=medium, xl=extra-large)
  - Stack layout on mobile
  - Form fields use grid for alignment on larger screens
- **Tailwind CSS v4:** All styling uses project's Tailwind configuration
- **Icons:** Lucide Vue Next icons (Plus, Eye, SquarePen, Send, Undo2, etc.)

### Authorization-aware UI Behavior

- **Index:** Action buttons and nav item only visible to users with appropriate permissions
- **Create:** Form only appears after passing route middleware
- **Show:** 
  - Edit button only shown if `canEdit` is true (owner + draft status)
  - Submit button only shown if `canSubmit` is true (owner + draft status + permission)
  - Revise button only shown if `canRevise` is true (owner + submitted status + permission)
- **Navigation:** Applications item in sidebar only visible to users with `application.view` permission

### Version/Submission/Revision UI

- **Show Page Displays:**
  - Current version number
  - Current version status (draft/submitted)
  - Submitted date (when applicable)
  - Creation date
- **Version Lifecycle:**
  - Draft → Submit (immutable transition, requires permission + ownership + draft state)
  - Submitted → Revise (creates new draft version, requires permission + ownership + submitted state)
  - Revise action visible only when current version is submitted

---

## 5. Type Definitions

**File:** `resources/js/types/application.ts`

```typescript
export type ManagedApplication = {
    id: number;
    programId: number;
    primaryOwnerId: number;
    applicantType: 'INDIVIDUAL' | 'TEAM' | 'ORGANIZATION' | string;
    status: string;
    reference: string | null;
    submittedAt: string | null;
    createdAt: string;
    canEdit?: boolean;
    canSubmit?: boolean;
    canRevise?: boolean;
};

export type ManagedApplicationVersion = {
    id: number;
    applicationId: number;
    versionNumber: number;
    status: string;
    submittedAt: string | null;
};

export type ApplicationProgramOption = {
    id: number;
    name: string;
    code: string;
};
```

These types are exported from `resources/js/types/index.ts` and available throughout the frontend.

---

## 6. Navigation Integration

**File:** `resources/js/navigation/app.ts` (modified)

Added:

```typescript
{
    title: 'Applications',
    href: applicationsIndex(),
    icon: BriefcaseBusiness,
    permission: 'application.view',
}
```

- Placed in the Management section (same as Programs)
- Gated by `application.view` permission
- Icon: BriefcaseBusiness from Lucide Vue Next

---

## 7. Frontend TypeScript Compilation

**Verification:** `npm run types:check`

- **Result:** ✅ Passed with no errors
- **Command:** `cd /home/guangut/projects/laravel/ai-innovation-lifecycle-hub && npm run types:check`
- **Output:** No TypeScript errors or warnings
- **Performed:** 2026-09-01 after UI implementation

This confirms the new Application UI pages and types compile correctly with the existing codebase.

---

## 8. FeatureTest Specifications

**File:** `FeatureTest/015c-016-application-rbac-member-management-specification.md`

Covers 12 test scenarios:

- **APP-RUNTIME-RBAC-001:** Four Application permissions exist in the live registry
- **APP-RUNTIME-RBAC-002:** No duplicate permissions
- **APP-MEMBER-001:** Authorized owner can add a member
- **APP-MEMBER-002:** Unauthorized user cannot add members to another application
- **APP-MEMBER-003:** Duplicate active member is rejected
- **APP-MEMBER-004:** Authorized owner can update a member attribute
- **APP-MEMBER-005:** Unauthorized user cannot update another application's member
- **APP-MEMBER-006:** Authorized actor can remove/deactivate a member
- **APP-MEMBER-007:** Inactive member loses active authority
- **APP-MEMBER-008:** Primary owner cannot be silently changed through member management
- **APP-MEMBER-009:** Direct ApplicationMember identifier access cannot bypass application scope
- **APP-MEMBER-010:** Cross-program manipulation is denied

Each test includes:
- Actor and account context
- Application/program context
- Expected backend result
- Expected database result
- Security reason
- Evidence requirement

---

## 9. ManualTest Documentation

### ManualTest_03_Application_Delivery.md

**Status:** NOT RUN BY DESIGN

Covers:
- MT-03-001: Create Application in published program
- MT-03-002: Edit draft application content
- MT-03-003: Submit draft application version

Each scenario includes:
- Objective
- Setup preconditions
- Manual browser steps
- Expected UI/backend/database results
- Verification queries

### ManualTest_04_Application_Member_Management.md

**Status:** NOT RUN BY DESIGN

Covers:
- MT-04-001: View application members
- MT-04-002: Add a valid member
- MT-04-003: Duplicate active member rejected
- MT-04-004: Unauthorized user cannot add member
- MT-04-005: Update member status
- MT-04-006: Remove a member
- MT-04-007: Inactive member loses authority
- MT-04-008: Primary owner boundary
- MT-04-009: Direct URL protection

---

## 10. Test Execution Status

**Status:** `NOT RUN BY DESIGN`

Rationale:
- Lightweight frontend TypeScript compilation was the only focused verification performed
- No automated Pest/PHPUnit test execution
- No browser manual QA performed
- Follows the project's established credit-efficient testing policy:
  - Test specifications are documented in `FeatureTest/` and `ManualTest/`
  - Automated test execution is deferred unless a focused test is required to diagnose a blocker
- Browser tools were available but manual QA was not claimed/performed

---

## 11. Database Changes

**No database changes in this task.**

- All database structures (Application, ApplicationMember, ApplicationVersion, etc.) were created in earlier tasks
- No migrations created or run in Task 016
- No seeding or fixture modifications
- The existing schema from 013-015C tasks is sufficient for the UI foundation

---

## 12. Files Created

New files created in Task 016:

1. `resources/js/types/application.ts` — TypeScript types for Application UI data structures
2. `resources/js/pages/applications/Index.vue` — Application list page
3. `resources/js/pages/applications/Create.vue` — Create draft application page
4. `resources/js/pages/applications/Show.vue` — View application details and status page
5. `resources/js/pages/applications/Edit.vue` — Edit draft version content page

---

## 13. Files Modified

Files modified in Task 016:

1. `resources/js/types/index.ts` — added export for application types
2. `resources/js/navigation/app.ts` — added Applications nav item with permission gate

Files modified in earlier tasks (015C) that are part of the Application foundation:

- `routes/web.php` — added member management routes (verified in git status)
- `app/Providers/AppServiceProvider.php` — policies already registered (from earlier phases)
- (Plus many untracked files from Batch 1 and earlier tasks, preserved without modification)

---

## 14. Files Intentionally Not Modified

- No changes to Application or ApplicationMember models (completed in earlier tasks)
- No changes to ApplicationController (completed in earlier tasks)
- No changes to ApplicationMemberController (completed in 015C)
- No changes to Application or ApplicationMember policies beyond 015C
- No changes to permission seeder (canonical source maintained)
- No modifications to historical handoffs (014, 015A, 015B, 015C-016 preserved)
- No Pest test implementations created or run
- No changes to authentication/authorization middleware beyond existing patterns
- No changes to database.php, config files, or Laravel bootstrap

---

## 15. OWNER DECISION REQUIRED

The following decisions remain pending product governance approval:

### 15.1 Role-to-Permission Grants

Which role(s) should receive each Application permission?

- `application.view` — Program Staff? All authenticated users? Scoped to program membership?
- `application.create` — Applicant role? Specific approved user types?
- `application.update` — Owner only? Approved delegates?
- `application.submit` — Owner only? Restricted to specific applicant types?

The repository architecture keeps permission definitions and role assignments separate. This task did not invent Manager/Staff/Judge/Applicant grants. The source permission catalog remains coherent, but actual grant assignment remains a governance decision.

### 15.2 Application Content Schema

The current implementation uses open JSON for draft content:

```json
{
  "summary": "...",
  "category": "..."
}
```

A domain-specific decision is required on:

- What fields/structure should applications capture?
- Should there be a form builder component instead of JSON textarea?
- Should content validation be schema-based?

### 15.3 Member Management UI Scope

Member management backend is complete, but UI is not yet implemented:

- Should member management remain within the Show page or a dedicated Members page?
- Should member invitations/bulk actions be supported?
- Should approval workflows exist beyond owner-only management?

---

## 16. Known Issues

**None identified at this stage.**

- TypeScript compilation successful
- No runtime errors observed during type-check
- No backend-frontend contract mismatches detected
- All imports and route references verified

---

## 17. Known Risks

1. **No browser manual testing performed:**
   - UI layout, responsiveness, and user experience have not been validated in a live browser
   - Form submission flows not verified end-to-end
   - Navigation paths not tested for broken links

2. **No automated E2E or integration tests:**
   - Pest feature tests not executed (by design)
   - Application creation/submission workflow not verified end-to-end
   - Permission enforcement not validated against live database

3. **Member management UI not yet implemented:**
   - Backend foundation is complete, but no UI for adding/removing/managing members
   - Member list endpoint exists but no Show page section to display it

4. **Application content structure still open:**
   - JSON textarea editor is temporary; no domain-driven form builder
   - Content validation is minimal (only array type check)

5. **No role-to-permission grants materialized:**
   - Permissions exist in code, but no live database grants to any role
   - Without role assignments, features are access-denied to all users

---

## 18. Recommended Next Task

### 18.1 Immediate (Quick Win)

- **Implement Member Management UI within Show page:**
  - Display list of active members with status/added date
  - Add member button and form (owner-only)
  - Remove/deactivate member actions (owner-only)
  - ~200 lines of Vue + component reuse

### 18.2 Short Term

- **Application Content Form Builder:**
  - Replace JSON textarea with domain-driven form fields
  - Add content validation schema
  - Implement field-level error messages
  - ~300-400 lines of Vue components

### 18.3 Medium Term

- **Role-to-Permission Assignments:**
  - Coordinate with product governance on role strategy
  - Create seed/fixture to assign permissions to roles
  - Add QA fixtures for role-based testing

### 18.4 Later Phases (Out of Scope)

- Screening module (judge assignment, evaluations)
- Decision-making workflow
- Appeal and revision at governance level

---

## 19. Verified Facts vs Assumptions

### Verified Facts

✅ **Git Status:**
- Repository state checked with `git status --short --branch`
- No destructive reset occurred
- Task 015C member foundation files preserved
- Task 016 UI files created as untracked

✅ **TypeScript Compilation:**
- Command executed: `npm run types:check`
- Result: Zero errors, clean build
- Date: 2026-09-01

✅ **Routes and Permissions:**
- Member routes verified in `routes/web.php`: 4 routes for member operations
- Application routes verified: create, store, show, edit, update, submit, revise
- Permission middleware checked: routes gate with `permission:application.*`

✅ **Navigation Integration:**
- Applications item added to `resources/js/navigation/app.ts`
- Gated with `permission: 'application.view'`
- Placed in Management section

✅ **Type System:**
- ManagedApplication, ManagedApplicationVersion, ApplicationProgramOption types created
- Exported from resources/js/types/index.ts
- Used in all four UI pages with no unresolved references

✅ **Authorization Model:**
- Ownership remains distinct from membership (verified in policy review)
- Four canonical permissions maintained (not duplicated or replaced)
- No new permissions invented

### Assumptions Not Verified

⚠️ **Browser UI rendering:**
- Assumed Tailwind classes render correctly (not tested in browser)
- Assumed responsive breakpoints work as expected (no viewport testing)
- Assumed form submission flows work (not tested end-to-end)

⚠️ **Backend contract compliance:**
- Assumed ApplicationController returns correct prop shapes for UI (not verified end-to-end)
- Assumed flash messages display correctly (not tested)
- Assumed error handling works as expected (not tested)

⚠️ **Database operations:**
- Assumed existing Application/ApplicationMember schema is correct (assumed from earlier tasks)
- Assumed permission registry is seeded (not verified against live DB in this task)

---

## 20. Repository State Summary

### Before Task 016

- Batch 1 foundation complete (programs, application core, initial version lifecycle)
- Permission catalog in source
- Member management backend implemented (from 015C)
- No Application UI pages

### After Task 016

- Complete Application CRUD UI for create, list, view, edit, submit, revise
- TypeScript types and navigation integration
- FeatureTest and ManualTest specifications for member and UI workflows
- Ready for manual QA and role-to-permission assignments

### Not Changed in Task 016

- Database schema
- Application models or relationships
- Permission catalog or naming
- Pest test suite
- Historical documentation or handoffs

---

## 21. Handoff Checklist

- ✅ Implementation completed for both member authorization and UI foundation
- ✅ Authoritative documents reviewed
- ✅ No destructive database operations
- ✅ No new permissions invented
- ✅ Ownership/membership distinction preserved
- ✅ TypeScript compilation verified
- ✅ FeatureTest specifications created
- ✅ ManualTest documentation created
- ✅ Test execution status: `NOT RUN BY DESIGN`
- ✅ Files created/modified accurately reported
- ✅ Known issues and risks documented
- ✅ OWNER DECISION REQUIRED items identified
- ✅ Recommended next task provided
- ✅ Verified facts vs assumptions clearly marked

---

## 22. Summary

Task 016 successfully completed the Application Member Management authorization foundation and delivered the first usable Application UI. The implementation:

- **Preserves** the canonical four Application permissions without invention
- **Maintains** ownership as distinct from membership
- **Implements** owner-only member management under existing `application.update` permission
- **Provides** core UI pages (list, create, show, edit) for application lifecycle
- **Integrates** with existing project patterns (Tailwind, components, navigation)
- **Follows** the credit-efficient testing policy (specs documented, no broad test execution)
- **Stays within** the current Application module without advancing into Screening or later phases

The next phase is ready to receive role-to-permission grants and can proceed to member management UI or content form builder improvements based on product priorities.
