# EAIC Application Foundation Specification

## APP-FOUND-001

- **Actor:** Applicant with a valid Program context and application ownership privileges.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The user is the designated primary owner for the target Application.
- **Action:** Open the Application record or direct version history.
- **Expected UI:** The Application identity, owner, and current version pointer are visible.
- **Expected server authorization:** ALLOW only for authorized Application ownership or permitted view access.
- **Expected result:** The Application details are displayed to an authorized actor.
- **Security reason:** Application identity and membership boundaries are enforced at the model and policy layer, not by hidden UI state alone.

## APP-FOUND-002

- **Actor:** User outside the target Application's ownership or membership context.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The user has no explicit application authorization.
- **Action:** Request the Application detail or edit route for a record outside their scope.
- **Expected UI:** No edit controls are shown; direct access fails when attempted.
- **Expected server authorization:** DENY.
- **Expected result:** Denied or hidden.
- **Security reason:** Application ownership is the base permission boundary for direct application operations.

## APP-FOUND-003

- **Actor:** Application primary owner.
- **Authentication:** Authenticated and verified.
- **Preconditions:** A valid draft Application exists.
- **Action:** Create a new ApplicationVersion for the Application.
- **Expected UI:** Version creation succeeds with the next sequential version number.
- **Expected server authorization:** ALLOW for the owning Application.
- **Expected result:** Application version record created with `status=draft` and unique `version_number`.
- **Security reason:** Submitted versions are immutable; drafts are the only mutable editing state before submission.

## APP-FOUND-004

- **Actor:** Authorized program or application owner.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The target Application already has an active member for a user.
- **Action:** Attempt to add a second active member record for the same user on the same Application.
- **Expected UI:** The operation fails or validation blocks the duplicate.
- **Expected server authorization:** DENY database constraint violation for duplicate active membership.
- **Expected result:** Duplicate active membership is not persisted.
- **Security reason:** An Application cannot have multiple overlapping active members for the same user.

## APP-FOUND-005

- **Actor:** Application owner or program-scoped actor.
- **Authentication:** Authenticated and verified.
- **Preconditions:** A saved version exists for an Application.
- **Action:** Create a second version with the same `version_number` in the same Application.
- **Expected UI:** Validation or database constraint prevents duplicate version numbering.
- **Expected server authorization:** DENY.
- **Expected result:** Duplicate version number is rejected.
- **Security reason:** Each version must be uniquely numbered within an Application for auditability.

## APP-FOUND-006

- **Actor:** Application and Program staff actors.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Application data is present.
- **Action:** Inspect the model and relationship chain `Program -> Application -> ApplicationMember / ApplicationVersion`.
- **Expected UI:** All relationships are discoverable and consistent with the application lifecycle contract.
- **Expected server authorization:** ALLOW for authorized model access.
- **Expected result:** The relationship graph remains stable across ownership and membership queries.
- **Security reason:** Domain structure is intentionally narrow and scoped to the foundation layer only.

## APP-FOUND-007

- **Actor:** QA reviewer or developer.
- **Authentication:** Local environment actor.
- **Preconditions:** Database migrations for the Application foundation are available.
- **Action:** Run the focused Application foundation feature tests.
- **Expected result:** The Application model/factory/schema behavior remains stable and the focused regression is green.
- **Security reason:** This check provides a narrow, reviewable proof of the new foundation before downstream application workflow work begins.
