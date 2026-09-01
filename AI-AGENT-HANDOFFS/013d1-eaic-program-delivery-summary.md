# AI Agent Handoff 013D-1: EAIC Program Administration Delivery Path

## 1. Interaction ID

`013D-1`

## 2. Recovery State

- Branch: `main`, tracking `upstream/main`.
- The approved 013A through 013C-3A Batch 1 work was present as uncommitted work.
- No partial Program administration controller, Program Form Request, Program routes, or 013D-1 FeatureTest specification existed.
- Pre-existing unrelated tracked edits to `.env.example` and `TheRoadmap/decisions.md`, and pre-existing untracked Batch 1 artifacts/EAIC documents, were preserved.

## 3. Task Requested

Implement only the first secured EAIC Program administration delivery path: Program validation, controller, routes, connection to existing authorization, and FeatureTest specifications. No UI or downstream EAIC workflow was requested.

## 4. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- Handoffs `013c3a-eaic-program-bootstrap-policy-summary.md` and `013c2-eaic-batch1-policy-summary.md`
- Current Batch 1 Program model, Program membership model, Program policy, permission seeder, routes, controllers, Form Requests, and Starter authorization conventions.

## 5. Form Request Created or Modified

Created `app/Http/Requests/SaveProgramRequest.php`.

It validates only current Program schema-backed input:

- required name, code, slug, timezone, opening date, and closing date;
- unique code and slug, with the route-bound Program ignored on update;
- IANA timezone validation;
- `closes_at` after `opens_at`;
- optional description and JSON object metadata.

It intentionally returns `true` from `authorize()`: route permission middleware and controller policy calls own authorization, so record-level policy logic is not duplicated in the Form Request. Status, publication timestamps, creator, and membership fields are not client-assignable.

## 6. Controller Created or Modified

Created `app/Http/Controllers/ProgramController.php` with JSON-only endpoints:

- `index`
- `create`
- `store`
- `show`
- `edit`
- `update`
- `publish`

No Inertia page, Vue component, or other UI asset was created. The controller returns a deliberately conservative Program summary using only existing Program attributes.

`store` creates the Program as `draft` and atomically creates the creator's active `program_staff` Program Membership using the existing model/schema. This establishes explicit target Program scope after bootstrap creation; it is not a prerequisite for creation. Creation, update, and publish reuse the existing activity logger.

## 7. Routes Created or Modified

Modified `routes/web.php` to add seven authenticated-and-verified web routes:

| Route | Name | Middleware / policy action |
|---|---|---|
| `GET /programs` | `programs.index` | authentication, verification, `viewAny` plus visibility-scoped query |
| `GET /programs/create` | `programs.create` | `permission:program.create`, `create` policy |
| `POST /programs` | `programs.store` | `permission:program.create`, `create` policy |
| `GET /programs/{program}` | `programs.show` | `view` policy |
| `GET /programs/{program}/edit` | `programs.edit` | `permission:program.update`, `update` policy |
| `PUT /programs/{program}` | `programs.update` | `permission:program.update`, `update` policy |
| `POST /programs/{program}/publish` | `programs.publish` | `permission:program.publish`, `publish` policy |

## 8. Policy and Permission Integration

- Added `ProgramPolicy::viewAny()` for the authenticated discovery/index route.
- Route middleware establishes named action permission where an action is protected.
- Controllers call `authorize()` for every operation, so route middleware cannot substitute for record-level policy checks.
- `index` returns published Programs to non-Super Admin actors and additionally returns non-public Programs only when the actor has `program.view` plus an active membership in that Program.
- Existing Starter Super Admin behavior is preserved: the inherited global Gate bypass sees all Programs through the index; no change was made to `Gate::before`.
- `show` remains policy-protected, so authentication alone never exposes a non-public Program.

## 9. Program Create Behavior

- Creation requires the corrected global/bootstrap `program.create` authorization and does not require any existing Program Membership.
- No EAIC role, capability type, permission, permission grant, or seeder entry was added.
- On successful creation, the newly created Program receives one active `program_staff` membership for the creator, allowing normal scoped management of that Program afterward.
- The existing permission seeder still grants no Batch 1 EAIC permissions to inherited Starter roles. The existing Super Admin Gate bypass remains the only seeded bootstrap-capable path; later test setup may directly grant `program.create` without inventing a role.

## 10. Program Update Behavior

- Update route middleware requires `program.update`.
- Controller authorization invokes `ProgramPolicy::update`.
- The policy requires `program.update`, active `program_staff` membership in the target Program, and a non-archived Program.
- Client input cannot change status/publication/audit fields.

## 11. Program Publish Behavior

- Publish uses a dedicated `POST /programs/{program}/publish` action rather than ordinary update.
- Route middleware requires `program.publish`; controller authorization invokes the `publish` policy.
- The existing policy retains target Program Staff scope, draft-state, and defensive `opens_at < closes_at` checks.
- Successful publishing only sets `status` to `published` and `published_at` to the current time; no broader lifecycle engine was added.

## 12. Program Visibility and Index Behavior

- All delivery routes remain authenticated and verified, consistent with the current Starter authenticated-first surface.
- The index is a minimal ordered list, not a dashboard/filtering feature.
- Published Programs are discoverable through the current represented visibility state.
- Non-public Programs are limited to `program.view` plus active Program Membership, except for the preserved Starter Super Admin behavior.
- The JSON Program summary excludes metadata, creator/audit references, closure/archive fields, and all unsupported public-field assumptions.

## 13. FeatureTest Specifications Created or Updated

Created `FeatureTest/013d1-program-administration-delivery-specification.md` with all required HTTP specifications:

- `PROGRAM-HTTP-001` through `PROGRAM-HTTP-013`.

Each record includes actor, authentication, preconditions, request, expected HTTP result, expected authorization result, and security reason.

## 14. Test Execution Status

**NOT RUN BY DESIGN.**

No Pest, focused feature, full application, or unrelated test suite was executed under the task's credit-efficient testing policy. The FeatureTest file is specification-only coverage for later execution.

## 15. Static Verification

Passed:

- PHP syntax checks for Program controller, Form Request, Program policy, and `routes/web.php`.
- Laravel Pint on dirty PHP files.
- `php artisan route:list --name=programs -vv`: verified all seven routes and their `auth`, `verified`, action-permission middleware where applicable, and route-model binding middleware.
- `git diff --check`.

No static failure/retry occurred.

## 16. Database Changes

None during this interaction.

No migration, schema, seed, role table, permission table, permission catalog, or data command was run. Runtime Program storage is the delivery behavior implemented in the controller; it was not executed in this task.

## 17. Files Modified

- `app/Http/Controllers/ProgramController.php`
- `app/Http/Requests/SaveProgramRequest.php`
- `app/Policies/ProgramPolicy.php`
- `routes/web.php`
- `FeatureTest/013d1-program-administration-delivery-specification.md`
- `AI-AGENT-HANDOFFS/013d1-eaic-program-delivery-summary.md`

## 18. Files Intentionally Not Modified

- Handoffs 001–013C-3A.
- `TheRoadmap/decisions.md` and all EAIC blueprint/contract/matrix/schema/register documents.
- Existing Batch 1 migrations, models, factories, permission seeder, role configuration, and `Gate::before`.
- API routes/controllers, frontend components, Inertia pages, layouts, navigation, dashboards, CSS, themes, and visual assets.
- Eligibility/rubric UI, applications, judging, evaluation, deliberation, decisions, outcomes, notifications, and AI work.
- Packages, lockfiles, and `.env`.

## 19. Known 013A Issue Status

The database-level `opens_at < closes_at` constraint remains unresolved. No migration was created or altered. The Form Request and existing publish policy both defensively enforce ordering, but neither replaces the required database-level constraint.

## 20. Known Risks

- Exact anonymous/public Program discovery and public-field tiers remain a controller decision. This delivery path is deliberately authenticated-first and exposes only a conservative existing-field summary.
- The global `program.create` permission has no new role grant. Non-Super Admin bootstrap actors require an explicit future approved grant; none was invented.
- Program Staff membership is automatically established for the bootstrap creator, but there is no membership administration endpoint in this task.
- HTTP tests are specified but not executed; route behavior, policy integration, validation responses, and transaction behavior require later automated execution.
- Existing Super Admin Gate bypass remains broad; protected-history governance is still later work.

## 21. Recommended Next Task

Stop for Product & Technical Controller review. The next authorized task can add the first Program administration UI against this secured JSON delivery path, following the stated practical AdminLTE-inspired EAIC design direction, or first execute the recorded HTTP specification suite when testing authorization is approved.

## 22. Verified Facts vs Assumptions

**Verified facts:** the Program controller, Form Request, seven named routes, and FeatureTest specification exist; protected mutation routes have the named Spatie permission middleware and controller policy authorization; create has no membership prerequisite; update/publish retain target Program policy checks; static checks and route registration inspection passed; no database command or test execution occurred.

**Assumptions kept explicit:** published status is the current represented discovery state; all current delivery endpoints stay in the authenticated/verified web surface until public-route policy is explicitly approved; a successful bootstrap creator should receive an explicit existing `program_staff` membership so the created Program remains operable. No new EAIC authority model, role grant, UI, or downstream workflow was inferred.
