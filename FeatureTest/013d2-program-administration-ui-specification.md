# EAIC Program Administration UI Specification

## UI-AUTH-001

- **Actor:** Program Staff with Program viewing authority or bootstrap administration authority.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The actor satisfies the server-side visibility policy for at least one Program.
- **UI action/request:** Open the application navigation and visit the Programs workspace.
- **Expected UI:** Program Administration navigation and permitted Program actions are visible.
- **Expected server authorization:** ALLOW only policy-permitted Program records/actions.
- **Expected result:** Visible.
- **Security reason:** Navigation follows shared policy-aware abilities; server policy remains authoritative for data and actions.

## UI-AUTH-002

- **Actor:** Applicant without Program administration authority.
- **Authentication:** Authenticated and verified.
- **Preconditions:** No `program.view` or `program.create` authority.
- **UI action/request:** Inspect navigation and a visible Program record.
- **Expected UI:** Program Administration navigation/actions are hidden.
- **Expected server authorization:** DENY protected create/edit/publish requests.
- **Expected result:** Hidden.
- **Security reason:** Applicant identity does not imply Program administration authority.

## UI-AUTH-003

- **Actor:** Program Staff with active `program_staff` membership and `program.update` in the target Program.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Target Program is not archived.
- **UI action/request:** Open Program index or detail page.
- **Expected UI:** Edit action is visible for the target Program.
- **Expected server authorization:** ALLOW `GET /programs/{program}/edit` and `PUT /programs/{program}`.
- **Expected result:** Visible.
- **Security reason:** The controller exposes `canEdit` only after the existing update policy succeeds for the target record.

## UI-AUTH-004

- **Actor:** Program Staff with authority only in Program A.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Program B is outside the actor's active membership scope.
- **UI action/request:** View Program B through any allowed discovery path.
- **Expected UI:** Edit action is hidden for Program B.
- **Expected server authorization:** DENY direct edit/update requests for Program B.
- **Expected result:** Hidden.
- **Security reason:** Per-record UI flags reflect target Program policy; a route identifier cannot establish scope.

## UI-AUTH-005

- **Actor:** Bootstrap-capable administrator with `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Valid Program form data; no existing Program Membership is required.
- **UI action/request:** Open Create Program, complete the form, and submit.
- **Expected UI:** Validation succeeds, success feedback appears, and the new Program detail page is displayed.
- **Expected server authorization:** ALLOW `POST /programs`.
- **Expected result:** Successful response and Program created.
- **Security reason:** Creation is a global bootstrap action; successful creation establishes the explicit initial Program Staff membership afterward.

## UI-AUTH-006

- **Actor:** Unauthorized actor without `program.create`.
- **Authentication:** Authenticated and verified.
- **Preconditions:** The Create action is not visible.
- **UI action/request:** Submit a direct `POST /programs` request with valid fields.
- **Expected UI:** No Create action is shown; direct request receives an authorization failure.
- **Expected server authorization:** DENY.
- **Expected result:** DENY.
- **Security reason:** Hidden UI is supplementary; route middleware and the create policy enforce the boundary.

## UI-AUTH-007

- **Actor:** Program Staff with `program.publish` and active `program_staff` membership in the target Program.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Program is `draft` and its opening instant is before its closing instant.
- **UI action/request:** Select Publish and confirm the dialog.
- **Expected UI:** Publish action is visible, confirmation is required, and success feedback/status follows publication.
- **Expected server authorization:** ALLOW `POST /programs/{program}/publish`.
- **Expected result:** ALLOW.
- **Security reason:** The UI derives `canPublish` from the existing policy, which retains scope and state checks.

## UI-AUTH-008

- **Actor:** User without `program.publish` or active target Program Staff scope.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Target Program is a draft.
- **UI action/request:** Inspect target Program and submit a direct publish request.
- **Expected UI:** Publish action is hidden.
- **Expected server authorization:** DENY direct publication.
- **Expected result:** DENY.
- **Security reason:** Permission alone cannot bypass target Program scope, and UI hiding is not relied upon for security.

## UI-AUTH-009

- **Actor:** Authorized bootstrap creator or target-scoped Program Staff.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Form input has `opens_at >= closes_at`.
- **UI action/request:** Submit Create or Edit Program form with invalid window ordering.
- **Expected UI:** Clear field-level date error is displayed and all unrelated entered values remain available for correction.
- **Expected server authorization:** DENY validation; publication remains denied by its defensive policy check.
- **Expected result:** DENY with useful validation/error feedback.
- **Security reason:** The Form Request enforces ordering before storage while the known database-level constraint remains unresolved.

## UI-AUTH-010

- **Actor:** Program Staff scoped only to Program A.
- **Authentication:** Authenticated and verified.
- **Preconditions:** Program B is non-public/out of scope.
- **UI action/request:** Navigate directly to `/programs/{program-b}`, `/programs/{program-b}/edit`, or the publish endpoint.
- **Expected UI:** No Program B administration action is exposed; forbidden/error response is shown for a denied route.
- **Expected server authorization:** DENY.
- **Expected result:** DENY.
- **Security reason:** Direct URLs resolve records but do not bypass model policy scope checks.

## UI-AUTH-011

- **Actor:** Authenticated and verified viewer with no Programs currently visible.
- **Authentication:** Authenticated and verified.
- **Preconditions:** No published Programs and no in-scope Program memberships.
- **UI action/request:** Open the Programs index.
- **Expected UI:** Useful empty state explains which Programs will appear.
- **Expected server authorization:** ALLOW index discovery without disclosing unauthorized records.
- **Expected result:** Useful empty-state UI.
- **Security reason:** An empty collection must not be confused with permission to discover hidden Programs.

## UI-AUTH-012

- **Actor:** Authorized Program creator or target-scoped editor.
- **Authentication:** Authenticated and verified.
- **Preconditions:** One or more invalid form fields; other supplied fields are valid.
- **UI action/request:** Submit the Program form.
- **Expected UI:** Field-level errors are clearly displayed without losing unrelated valid input.
- **Expected server authorization:** Validation response is returned only after route and policy authorization pass.
- **Expected result:** Errors displayed clearly without losing unrelated valid input.
- **Security reason:** Server-side validation remains authoritative while Inertia form state preserves safe user input for correction.
