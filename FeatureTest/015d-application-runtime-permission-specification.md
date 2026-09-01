# Application Runtime Permission Specification

## APP-RUNTIME-001

- **Test ID:** APP-RUNTIME-001
- **Title:** Four canonical Application permissions exist in the live registry
- **Preconditions:** Local PostgreSQL development database is available and project seeding has been run using the standard RolePermissionSeeder path
- **Action:** Inspect the permission registry for `application.view`, `application.create`, `application.update`, and `application.submit`
- **Expected result:** All four permission names exist in the `permissions` table and are associated with the `web` guard
- **Security reason:** Route middleware and policy checks depend on these exact names being present before authorization can function correctly

## APP-RUNTIME-002

- **Test ID:** APP-RUNTIME-002
- **Title:** No duplicate Application permissions are created
- **Preconditions:** Permission seeding has run at least once
- **Action:** Count the live permission rows for the canonical Application set and group by name
- **Expected result:** Each canonical permission appears once only; group counts are all `1`
- **Security reason:** Duplicate permission rows create ambiguous authorization decisions and make enforcement unpredictable

## APP-RUNTIME-003

- **Test ID:** APP-RUNTIME-003
- **Title:** `application.revise` does not exist in the live registry
- **Preconditions:** Canonical Application permission set has been materialized
- **Action:** Query the live permission registry for the literal name `application.revise`
- **Expected result:** No row exists for `application.revise`
- **Security reason:** Revision remains handled by `application.update`; the permission catalog must remain minimal and consistent with the approved naming pattern

## APP-RUNTIME-004

- **Test ID:** APP-RUNTIME-004
- **Title:** Existing Starter permissions remain intact
- **Preconditions:** The database contains the standard starter permission set and the runtime seed has been run
- **Action:** Count or inspect core starter permission rows such as `dashboard.view`, `search.view`, `settings.view`, and `users.view`
- **Expected result:** These starter permissions still exist and are not deleted or replaced by the Application runtime setup
- **Security reason:** Runtime materialization must not disturb the platform’s core authorization layer or starter roles

## APP-RUNTIME-005

- **Test ID:** APP-RUNTIME-005
- **Title:** Existing roles and users remain intact
- **Preconditions:** Local development DB has any existing roles/users from the starter environment or QA fixture
- **Action:** Count roles and users after the seed operation and compare with the pre-seed baseline
- **Expected result:** No role or user rows are dropped, reset, or removed by the Application permission seed path
- **Security reason:** The runtime setup must preserve the current development identity and role graph while only materializing the required permissions

---

## Evidence expectations

- Capture the exact permission names as they appear in the database.
- Record the count of each canonical permission and the total count for the four names.
- Record whether `application.revise` is absent.
- Record the role/user counts before and after seeding if available.
- This is a runtime verification specification only; the tests were not executed in this task.
