# Task 015A: Application Delivery Reconciliation Summary

**Interaction ID:** 015A  
**Status:** CLARIFICATION ONLY  
**Date:** 2026-09-01  
**Verification Policy:** No Pest, no broad regression, no DB mutation, no code changes beyond this summary handoff  
**Test Execution Status:** NOT RUN BY DESIGN

---

## 1. Application permission status

### Verified current state

The actual permission registry was inspected in the live application database and the permission names below are currently absent.

**Permission status:**

- `application.create` — NOT EXISTS
- `application.update` — NOT EXISTS
- `application.submit` — NOT EXISTS
- `application.view` — NOT EXISTS

### Evidence inspected

- `database/seeders/RolePermissionSeeder.php` defines the seeded permission list and does not include any of the four application permissions.
- `php artisan tinker --execute ...` against the live permission registry returned:
  - `EXISTING: []`
- No role or user assignment found for any of those names in the live role/user permission graph.

### Where defined

The names are referenced in code, but not defined in the authoritative seeded catalog.

- `routes/web.php` uses:
  - `permission:application.create`
  - `permission:application.update`
- `app/Policies/ApplicationPolicy.php` checks:
  - `$user->can('application.create')`
  - `$user->can('application.update')`
  - `$user->can('application.view')`
- `app/Policies/ApplicationMemberPolicy.php` checks:
  - `$user->can('application.view')`
  - `$user->can('application.update')`
- `app/Policies/ApplicationVersionPolicy.php` checks similar `application.*` capabilities

### Seeded / granted status

- Seeded: NO
- Granted to any current role: NO
- Granted to any current user: NO

### Blocker status

Yes — this is a blocker for route-level permission enforcement and any permission-based access control that assumes the application permission names exist.

---

## 2. Application owner / member model status

### Current creation implementation

The current Application creation path in `app/Http/Controllers/ApplicationController.php` does the following in `store()`:

- creates an `Application` row
- sets `primary_owner_id` to the authenticated actor ID
- sets `status` to `draft`
- creates a first `ApplicationVersion` row with `version_number = 1` and `status = 'draft'`
- sets `current_version_id` to that version

### Automatic member row creation

**Result:** No `ApplicationMember` row is created automatically during Application creation.

### ApplicationMemberPolicy behavior

`app/Policies/ApplicationMemberPolicy.php` currently does:

- `viewAny()` -> `true`
- `view()` -> allows if member belongs to owner or member user or `$user->can('application.view')`
- `update()` -> requires `$user->can('application.update')` and the member's application primary owner is the user

### Owner authorization dependency on membership

The current implementation does not require the owner to also be an `ApplicationMember` record.

- The Application owner is determined by `primary_owner_id`.
- The policy checks are owner-based for `ApplicationPolicy` and member-based for `ApplicationMemberPolicy`, but the Application create flow does not automatically add the owner as a member.

### Safe support for owner-as-member later

This is possible if the application later adds the owner as an `ApplicationMember` row, because the policy logic already permits either the owner or member path in the member policy view logic.

However, the current design is not yet internally self-consistent with a member-centric model because the application owner is not automatically materialized as a member. The current implementation is therefore best classified as:

**STATUS: NEEDS CORRECTION**

### Recommendation only

Recommendation: keep `primary_owner_id` as the canonical ownership source, and add a deliberate, explicit owner-member creation step only if business policy requires the owner to also be represented in the membership table. Do not assume the member model is the authoritative owner model.

---

## 3. Submission authorization status

### Exact behavior of `POST /applications/{application}/submit`

From `app/Http/Controllers/ApplicationController.php`:

- Route is declared in `routes/web.php` as:
  - `POST /applications/{application}/submit`
- There is **no route middleware permission** on that route.
- The route is in the authenticated + verified group only.
- Controller method:
  - `submit(SubmitApplicationVersionRequest $request, Application $application)`
- It immediately calls:
  - `$this->authorize('submit', $application);`
- `ApplicationPolicy::submit()` currently does:
  - `return $application->primary_owner_id === $user->getKey();`

### Authorization summary

- authentication: yes, via `['auth', 'verified']` group
- route permission middleware: none
- policy: `ApplicationPolicy::submit()`
- ownership check: yes, `primary_owner_id === user id`
- other checks: draft-state guard in controller at runtime (`if ($currentVersion?->status !== 'draft') ...`)

### `application.submit` status

`application.submit` is **NOT EXISTS** in the live permission system.

This means the submit route is effectively owner-only through policy logic, not permission middleware.

---

## 4. Route / policy consistency review

### Current routes and policy behavior

| Action | Route | Permission middleware | Policy behavior | Status |
|---|---|---:|---|---|
| create | `GET/POST /applications/create` and `POST /applications` | `permission:application.create` | `ApplicationPolicy::create()` checks `$user->can('application.create')` | MISMATCH: permission absent |
| view | `GET /applications/{application}` | none | `ApplicationPolicy::view()` checks owner, submitted status, or `$user->can('application.view')` | MISMATCH: permission absent |
| update | `GET /applications/{application}/edit` and `PUT /applications/{application}` | `permission:application.update` | `ApplicationPolicy::update()` checks `$user->can('application.update')` plus ownership | MISMATCH: permission absent |
| submit | `POST /applications/{application}/submit` | none | `ApplicationPolicy::submit()` checks owner only | NETWORK-ALIGNED but permission absent |
| revise | `POST /applications/{application}/revise` | `permission:application.update` | `ApplicationPolicy::update()` is used via authorization call | MISMATCH: permission absent |

### Consistency conclusion

The code is internally consistent in terms of intent and structure, but it is not consistent with the live authorization catalog because the referenced permissions do not exist.

**Result:** Permission/policy mismatch is confirmed.

---

## 5. Verification claim: what was actually verified

### Verified with evidence

1. **Route registration**
   - Verified with `php artisan route:list --name=applications`
   - Output showed 8 application routes registered.

2. **Permission registry inspection**
   - Verified with `php artisan tinker --execute ...`
   - Output: `EXISTING: []`

3. **File-level inspection**
   - Verified the route definitions in `routes/web.php`
   - Verified policy logic in `app/Policies/ApplicationPolicy.php`
   - Verified member policy in `app/Policies/ApplicationMemberPolicy.php`
   - Verified seeding list in `database/seeders/RolePermissionSeeder.php`
   - Verified controller logic in `app/Http/Controllers/ApplicationController.php`

4. **Formatting check**
   - `vendor/bin/pint --dirty --format agent ...` executed earlier in the session and returned pass status.

5. **Autoloader check**
   - `php -r "require 'vendor/autoload.php'; require 'bootstrap/app.php'; ..."` succeeded.

### Not verified

The following either were not executed or cannot be confirmed from the existing session evidence:

- Pest test execution: **RESULT NOT VERIFIED**
- full application tests: **RESULT NOT VERIFIED**
- browser HTTP tests: **RESULT NOT VERIFIED**
- end-to-end application workflow tests against a live app session: **RESULT NOT VERIFIED**
- live database state of actual application records beyond the permission registry: **RESULT NOT VERIFIED** unless directly inspected from the DB

### Actual executed status

- static/structural inspection: yes
- route registration: yes
- permission registry query: yes
- formatting: yes
- automated tests: no
- browser/manual HTTP tests: no

---

## 6. Handoff date metadata observation

Handoff 015 contains the historical metadata:

- `Date: 2026-02-15 (simulated)`

This is present in the file as inspected, and it is inconsistent with the current project timeline in this session (current date 2026-09-01).

**Conclusion:** the Handoff 015 date metadata appears historically inconsistent with the current project timeline and should be treated as a metadata discrepancy requiring review.

---

## 7. Blockers

1. `application.create`, `application.update`, `application.submit`, and `application.view` are not currently defined in the managed permission system.
2. Route-level authorization on create/update/revise depends on missing permissions.
3. The permission mismatch prevents reliable authorization enforcement at the route layer.
4. Handoff 015 includes a date metadata value that appears inconsistent with the current project timeline.

---

## 8. Recommendations

1. Do not assume these application permission names exist until they are explicitly seeded and granted in the permission catalog.
2. Treat the missing permissions as a formal blocker before proceeding with any route-level authorization enforcement.
3. Keep `primary_owner_id` as the canonical ownership source unless intentional product policy requires owner-member equivalence.
4. If owner-member representation is required, create it deliberately rather than assuming it is already present.
5. Reconcile the Handoff 015 date metadata before any timeline-anchored handoff or release documentation uses it.

---

## 9. Files inspected

- `routes/web.php`
- `app/Http/Controllers/ApplicationController.php`
- `app/Policies/ApplicationPolicy.php`
- `app/Policies/ApplicationMemberPolicy.php`
- `app/Policies/ApplicationVersionPolicy.php`
- `database/seeders/RolePermissionSeeder.php`
- `app/Providers/AppServiceProvider.php`
- `AI-AGENT-HANDOFFS/015-application-delivery-implementation.md`

---

## 10. No changes made

No existing application code, models, policies, routes, migrations, controllers, permissions, or Handoff 015 files were modified for this clarification task.

This handoff file is the only artifact created for the reconciliation exercise.

---

## 11. Verified Facts vs Assumptions

### Verified facts

- The application permission names are absent in the live permission registry.
- No current role or user is granted those permissions.
- `routes/web.php` references application permission names for create/update/revise.
- `ApplicationPolicy::submit()` authorizes based on owner only.
- `ApplicationController::store()` creates the Application and initial version without creating an `ApplicationMember` row.
- Handoff 015 includes the date string `2026-02-15`.

### Assumptions / not claimed as fact

- It is not proven that the intended business rule is to add an `application.submit` permission.
- It is not proven that the owner should automatically become an `ApplicationMember`.
- It is not proven that the Handoff 015 date was intentional versus a placeholder.
- It is not proven that any browser or end-to-end HTTP behavior is correct beyond route registration and static code inspection.

---

## Final assessment

**Current design status:** NEEDS CORRECTION  
**Reason:** application permissions do not exist in the live authorization catalog, and create/update/revise route protections rely on names that are not seeded or assigned.

**Blocker:** yes — permission catalog mismatch.  
**Next review required:** Product & Technical Controller review before proceeding to the next implementation task.
