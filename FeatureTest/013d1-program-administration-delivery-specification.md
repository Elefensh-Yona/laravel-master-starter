# EAIC Program Administration Delivery Specification

## PROGRAM-HTTP-001

- **Actor:** Authorized Program viewer.
- **Authentication:** Authenticated and verified.
- **Preconditions:** A published Program is available, or the actor has `program.view` and active membership in a Program.
- **Request:** `GET /programs`.
- **Expected HTTP result:** `200 OK`.
- **Expected authorization result:** ALLOW only the Programs visible through publication or the actor's permitted scope.
- **Security reason:** The index must not disclose non-public Programs outside the actor's authorized Program Membership scope.

## PROGRAM-HTTP-002

- **Actor:** Authenticated actor without the required Program administration permission.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The actor lacks `program.create`, `program.update`, and `program.publish`.
- **Request:** `GET /programs/create`.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Protected administration endpoints require their singular action permission and cannot be entered through direct URLs.

## PROGRAM-HTTP-003

- **Actor:** Bootstrap-capable administrator with `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** No Program or Program Membership exists.
- **Request:** `GET /programs/create`, then `POST /programs` with a valid Program payload.
- **Expected HTTP result:** `200 OK`, then `201 Created`.
- **Expected authorization result:** ALLOW.
- **Security reason:** Creation is a global bootstrap action; no target Program Membership exists or is required. Successful storage creates the initial explicit `program_staff` membership for the creator.

## PROGRAM-HTTP-004

- **Actor:** Applicant without `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The actor has no approved bootstrap administrative capability.
- **Request:** `POST /programs` with an otherwise valid payload.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Applicant authority does not imply Program administration or bootstrap creation.

## PROGRAM-HTTP-005

- **Actor:** Judge without `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The actor may have Judge scope but no approved bootstrap administrative capability.
- **Request:** `POST /programs` with an otherwise valid payload.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Judge authority is assignment/program scoped and does not grant Program creation.

## PROGRAM-HTTP-006

- **Actor:** Decision Maker without `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The actor has no approved bootstrap administrative capability.
- **Request:** `POST /programs` with an otherwise valid payload.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Decision authority is distinct from Program administration and does not imply bootstrap creation.

## PROGRAM-HTTP-007

- **Actor:** Program Staff with `program.update` and active `program_staff` membership in the target Program.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The target Program is not archived; the payload has valid unique fields and `opens_at < closes_at`.
- **Request:** `PUT /programs/{program}` for the actor's Program.
- **Expected HTTP result:** `200 OK`.
- **Expected authorization result:** ALLOW.
- **Security reason:** Permission, active target scope, record policy, and validation all pass.

## PROGRAM-HTTP-008

- **Actor:** Program Staff with `program.update` and active `program_staff` membership in Program A.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The requested Program B is outside the actor's membership scope.
- **Request:** `PUT /programs/{program-b}` with an otherwise valid payload.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Route permission middleware is not sufficient; the Program policy denies cross-Program record access.

## PROGRAM-HTTP-009

- **Actor:** Program Staff with `program.publish` and active `program_staff` membership in the target Program.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The target Program is `draft` and `opens_at < closes_at`.
- **Request:** `POST /programs/{program}/publish`.
- **Expected HTTP result:** `200 OK`.
- **Expected authorization result:** ALLOW.
- **Security reason:** The required permission, target scope, draft state, and defensive lifecycle-date condition pass.

## PROGRAM-HTTP-010

- **Actor:** User without `program.publish` or active `program_staff` scope in the target Program.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The target Program is a valid draft.
- **Request:** `POST /programs/{program}/publish`.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** A direct publish request cannot bypass the permission and record-level Program scope requirements.

## PROGRAM-HTTP-011

- **Actor:** Bootstrap-capable administrator or in-scope Program Staff.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Submitted payload has `opens_at >= closes_at`.
- **Request:** `POST /programs` or `PUT /programs/{program}` with that invalid payload.
- **Expected HTTP result:** `302 Redirect` with web validation errors, or `422 Unprocessable Content` for a JSON request.
- **Expected authorization result:** DENY validation.
- **Security reason:** The Form Request rejects invalid lifecycle ordering; publication also retains a defensive policy condition while the database-level check remains unresolved.

## PROGRAM-HTTP-012

- **Actor:** Authenticated actor with neither `program.view` plus active target membership nor Super Admin authority.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Target Program is non-public (`draft`, `closed`, or `archived`).
- **Request:** `GET /programs/{program}`.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Authentication alone does not disclose a non-public Program.

## PROGRAM-HTTP-013

- **Actor:** Program Staff with action permission but active membership only in Program A.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Program B is outside scope and is addressed by a valid direct URL identifier.
- **Request:** `GET /programs/{program-b}/edit`, `PUT /programs/{program-b}`, or `POST /programs/{program-b}/publish`.
- **Expected HTTP result:** `403 Forbidden`.
- **Expected authorization result:** DENY.
- **Security reason:** Valid route-model binding does not establish authorization; the target Program policy enforces record scope after identifier resolution.
