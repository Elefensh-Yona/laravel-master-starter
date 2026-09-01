# CODEX Phase 0 Implementation Plan

**Project:** Ethiopian AI Center (EAIC)  
**Assessment date:** 2026-08-28  
**Assessment scope:** Read-only repository and documentation assessment; no application code, migrations, configuration, packages, or commits changed.

## Executive Decision

The repository is **not ready for broad EAIC domain coding today**. The Laravel Master Starter baseline is structurally suitable, but the EAIC product, workflow, authorization, privacy, and logical schema documents remain drafts and do not yet define an implementation contract.

The smallest safe next step is a Phase 0 approval pass that converts the existing product direction into a deterministic, trust-preserving vertical-slice contract. Domain migrations and workflow code should begin only after the P0 decisions and acceptance tests below are approved.

## Evidence Legend

- **EXISTS:** verified in the current source tree or repository metadata.
- **DOCUMENTED BUT NOT IMPLEMENTED:** described by project documents, but not found in application code.
- **UNKNOWN:** not verifiable from the current checkout or requires an explicit owner decision.
- Documentation is treated as draft unless this report explicitly identifies it as operational guidance. Source code is the authority for current implementation facts.

## 1. Current Repository State

### Verified facts

| Area | Status | Evidence |
|---|---|---|
| Framework | EXISTS: Laravel 12 is declared; exact installed framework version is UNKNOWN because `vendor/` is absent | `composer.json` requires `laravel/framework: ^12.0`; `artisan` cannot boot without `vendor/autoload.php` |
| PHP | EXISTS: PHP 8.5.4 CLI | `php -v` |
| Frontend | EXISTS: Vue 3, Inertia 2, Vite 7, TypeScript, Tailwind CSS 4, Wayfinder, Reka UI, Lucide | `package.json`, `vite.config.ts`, `resources/js/` |
| Authentication | EXISTS: Fortify flows, email verification, password reset, 2FA, Sanctum token baseline | `config/fortify.php`, `app/Actions/Fortify/`, auth tests, API controllers |
| Authorization | EXISTS: Spatie Permission, four seeded system roles, permission middleware, policies, Super Admin gate bypass | `app/Support/SystemRole.php`, `database/seeders/RolePermissionSeeder.php`, `bootstrap/app.php`, policies |
| Database declarations | EXISTS: SQLite is the shipped/default test configuration; PostgreSQL is documented/configurable | `.env.example`, `phpunit.xml`, `config/database.php` |
| Active local configuration | UNKNOWN: `.env` is not present in the checkout, so active DB credentials/driver cannot be verified | `.env.example` exists; no `.env` observed |
| Test framework | EXISTS: Pest 4 with Laravel plugin and PHPUnit 12 declarations | `composer.json`, `tests/Pest.php`, `phpunit.xml` |
| Current tests | EXISTS: 39 PHP test files, primarily starter auth/admin/API/support coverage | `tests/` inventory |
| Test execution | BLOCKED locally: dependencies are not installed | `vendor/` absent; `php artisan test` cannot boot |
| Starter models | EXISTS: 5 models: `User`, `ActivityLog`, `ImportRun`, `Media`, `Setting` | `app/Models/` |
| Starter migrations | EXISTS: 14 migration files for auth, jobs/cache/session, permissions, notifications, activity logs, media, settings, imports, tokens, and user extensions | `database/migrations/` |
| Starter web surface | EXISTS: dashboard, users, roles, settings, media, notifications, activity logs, search, exports, auth/profile | `routes/web.php`, `routes/settings.php` |
| Starter API surface | EXISTS: `/api/v1` auth, current user, notifications, activity logs, admin summary/users, media | `routes/api.php` |
| EAIC domain implementation | DOCUMENTED BUT NOT IMPLEMENTED: no domain models, migrations, controllers, requests, policies, pages, navigation, or domain tests | Source inventory and domain-term search |
| AI provider implementation | DOCUMENTED BUT NOT IMPLEMENTED | No EAIC AI services, provider contracts, jobs, or AI routes found |
| Git state | EXISTS: branch `main`, aligned with `upstream/main`; latest commit is `dc03e5e` | `git status --short --branch`, `git log -1` |
| Worktree | EXISTS: six project/benchmark/schema/roadmap files are untracked; no tracked application changes were made during this assessment | `git status --short` |
| Dependency reproducibility | UNKNOWN until dependencies are installed; package lockfiles exist | `composer.lock`, `package-lock.json`; `vendor/` and `node_modules/` absent |

### Important existing conventions

- Laravel 12's streamlined bootstrap configures middleware in `bootstrap/app.php`; there is no `app/Http/Kernel.php` convention here.
- Inertia pages live under `resources/js/pages`; Laravel named routes are exposed to frontend code through Wayfinder.
- Policies and permission middleware are used together; route authorization is not intended to be UI-only.
- `ActivityLogger` is the existing audit extension point; `SystemMessageNotification` is the existing database notification pattern.
- `Media` is a polymorphically attachable starter model and should be reused for domain evidence where its access model is sufficient.
- Tests use Pest feature tests and factories. `phpunit.xml` forces SQLite in-memory tests, independent of the production/development database choice.

## 2. Document Status

| Document | Purpose | Current status | Authority | Implementation-contract readiness |
|---|---|---|---|---|
| `AI-PROJECT-STARTER.md` | Agent onboarding, reuse rules, starter boundaries, definition of ready-to-code | Operational onboarding guide | Strong operational guidance; source wins for facts | Ready for starter onboarding; not an EAIC domain contract |
| `MASTER-STARTER-ARCHITECTURE.md` | Inventory of inherited Laravel starter architecture and extension conventions | Starter architecture reference | Intended reference, but some claims are historical and need refresh | Ready for reuse with source verification; not a domain contract |
| `PROJECT-REQUIREMENTS.md` | Product vision, actors, lifecycle, module requirements, governance, initial boundary | Draft v0.1 | Product proposal pending owner approval | Not ready; explicit approval gate remains |
| `DATABASE-SCHEMA.md` | Logical EAIC entities, relationships, constraints, migration order, open questions | Draft v0.1 | Schema proposal pending product/architecture approval | Not migration-ready; document itself says so |
| `PROJECT-ROADMAP.md` | Proposed phases, dependencies, tests, first slice, approval gate | Draft v0.1 | Sequencing proposal pending decision closure | Directionally usable; not task-authoritative |
| `README.md` | Human setup, starter operations, roles, API, CI, deployment baseline | Starter README | Human-facing operational reference | Ready for starter use; not an EAIC release guide |
| `AGENTS.md` | Laravel Boost coding, testing, security, framework, formatting, and skill rules | Repository instruction set | Binding implementation instructions | Ready and applicable; its documented Fortify skill name should be reconciled with the available skill name before auth work |
| `CODEx-BENCHMARK-REPORT.md` | Independent benchmark of product/architecture readiness and risks | Advisory benchmark | Non-authoritative review input | Useful for risk prioritization; never a requirements or schema authority |

### Documentation findings

- The requirements, schema, and roadmap consistently identify themselves as drafts and include approval gates.
- The starter architecture claims a verified baseline, but it contains historical-state language that should be refreshed. In particular, it refers to 13 migrations while the checkout contains 14 migration files; it also describes database migration status without proving the current checkout's database state.
- The benchmark's 7/10 assessment is consistent with the source and drafts on major risks, but its claims must be verified rather than adopted automatically.
- None of the eight documents is sufficient alone as a complete implementation contract for the EAIC trust path.

## 3. Implementation Blockers

### P0: blocks broad domain implementation

| ID | Issue | Evidence | Affected document/area | Recommended decision | Approval |
|---|---|---|---|---|---|
| P0-01 | Authoritative workflow/state machine is undefined | Requirements says stages are configurable; schema mostly lists status fields; benchmark warns that free-form statuses are unsafe | Requirements, schema, roadmap; all lifecycle modules | Approve explicit states, allowed transitions, actor, permission, validation, audit event, notification, and reversibility for program, application, screening, assignment, conflict, evaluation, deliberation, and decision | Product owner + technical lead |
| P0-02 | Application revision/submission model is open | Schema asks true immutable snapshots versus lighter revision history | Schema; application forms, audit, reporting | Use immutable submitted snapshots with a clearly defined draft/revision model, or explicitly approve the lighter alternative before migration design | Product owner + technical lead |
| P0-03 | Fixed fields versus configurable application questions is open | Schema explicitly identifies this as an open decision | Schema; application schema, validation, UI, reporting | Choose the smallest v1 model and define question/answer versioning if configurable questions are approved | Product owner |
| P0-04 | Judge assignment scope and precedence are undefined | Schema permits program/stage/application scope; benchmark asks how conflicts are resolved | Schema; policies, queries, judge UX | Approve one assignment scope model, duplicate rules, precedence, reassignment, and access behavior | Product owner + technical lead |
| P0-05 | Conflict-of-interest definition and blocking behavior are incomplete | Requirements requires first-class conflict handling; benchmark identifies indirect affiliation loopholes | Requirements, schema; assignment/evaluation/deliberation | Define conflict categories, declaration timing, who resolves them, whether unresolved conflicts block assignment/evaluation/deliberation, and audit requirements | Product owner + governance/legal owner |
| P0-06 | Rubric version freeze and criterion immutability are not executable rules | Requirements/roadmap require versioning/freezing; schema does not define enforcement sufficiently | Schema, roadmap; rubric/evaluation migrations and services | Freeze the active rubric version at the agreed point; prohibit silent criterion mutation and define superseding versions | Product owner + technical lead |
| P0-07 | Evaluation finalization and reopening rules are undefined | Benchmark asks for final actor/time/history and score mutation rules | Schema, roadmap; evaluation authorization/audit | Define draft/final states, finalization authority, immutable score behavior, exceptional reopen process, and history | Product owner + governance/legal owner |
| P0-08 | Human decision record and authority are undefined | Requirements separates human authority from AI; benchmark asks who creates the decision record | Requirements, schema, roadmap; deliberation/decision | Define decision authority, decision types, required rationale, supersession/reversal, notifications, and audit event | Product owner + governance/legal owner |
| P0-09 | Evidence privacy/access classification is missing | Benchmark identifies public, staff, judge, applicant, and confidential visibility as unresolved | Schema, requirements; media policies/API/AI inputs | Approve classification and access rules for submissions, media, scores, notes, and AI-derived material | Product owner + privacy/legal owner |
| P0-10 | AI data boundary, retention, provenance, and review semantics are undefined | Requirements requires advisory AI and explainability; schema lacks a complete retention/provenance contract | Requirements, schema, roadmap; AI services/storage/queues | Defer AI implementation until the deterministic trust path exists; approve provider abstraction, allowed inputs, retention, source links, human review, failure, cost, and deletion rules | Product owner + privacy/legal owner + technical lead |
| P0-11 | Domain permission catalog and actor-to-role mapping are not approved | Starter has global roles/permissions only; EAIC actors and program scope are proposed | Requirements, architecture, roadmap; policies/routes/UI/API | Produce an action/actor/record-scope matrix and approve whether program-scoped access needs memberships/assignments beyond global roles | Product owner + technical lead |
| P0-12 | Organization/startup participation policy is open | Requirements/schema list applicant, team, and organization concepts without a settled v1 rule | Requirements, schema; identity/application models | Decide whether v1 supports individuals only, teams, organizations, or a constrained combination | Product owner |

### P1: blocks a specific upcoming phase

- Public program visibility, anonymous access, and whether authentication is required to start an application; blocks the public announcement/application phase.
- Team membership, lead authority, delegation, and submission-on-behalf rules; blocks team applications.
- Applicant visibility into scores, judge identity, decision rationale, withdrawals, appeals, reopening, and reversal; blocks applicant-facing lifecycle UX.
- Notification triggers, timing, and delivery guarantees for submission, assignment, conflict, finalization, and decision; blocks communications acceptance tests.
- Expected volume for applications, media, judges, reports, and AI calls; informs queue, indexing, pagination, and storage decisions.
- Legal/privacy/data residency requirements and retention periods; blocks production data handling and AI release.
- Whether event/calendar support needs recurrence; blocks event phase.
- Incubation, mentorship, resource, partner, and alumni rules; each blocks its respective later phase.

### P2: resolve during implementation after the trust path

- Advanced reporting and analytics shape.
- Full calendar/workshop/LMS behavior.
- Resource capacity and overlap rules.
- Partner ecosystem depth.
- Alumni longitudinal data depth.
- Broader API consumer requirements.
- AI assistant UX after governance and deterministic workflows are accepted.

### P3: defer or keep outside the initial release

- Autonomous judging, automatic winner selection, autonomous consequential decisions.
- Full CMS, generic social network, ERP/accounting, generic CRM, blockchain, marketplace, and unrestricted agentic automation.

## 4. Architecture Readiness

### Boundary

The starter/domain boundary is clear: retain authentication, RBAC, audit, notifications, media, settings, search, import/export, API, Vue/Inertia, and testing infrastructure; add EAIC domain modules on top. The domain module boundary is not yet operationally defined in code because no domain exists.

### Access and authorization

The current app is authenticated-first: the root route sends guests to login, and no public program routes exist. Requirements call for public program/announcement behavior, so a deliberate public-versus-authenticated route policy is required. Existing Spatie global roles and permission middleware are a sound base, but program-scoped staff/judge/mentor access and record-level visibility need an approved model and policies.

### AI provider abstraction

DOCUMENTED BUT NOT IMPLEMENTED. No provider contract, adapter, prompt/version record, queue job, source citation model, retention policy, or human review workflow exists. AI must remain outside the first deterministic slice until governance decisions are approved.

### Audit architecture

EXISTS for starter activity events through `ActivityLogger` and activity-log UI/API. EAIC needs an approved event vocabulary and append-only/history rules for state transitions, submissions, conflict resolution, rubric freeze, evaluation finalization, reopen, deliberation, and decisions. Activity logging alone must not be assumed to replace immutable domain records.

### Storage/media

EXISTS as a polymorphic `Media` model with upload/download controls and tests. Domain evidence access classification, version attachment, retention, and AI-input permissions are not defined.

### API strategy

EXISTS as a Sanctum-authenticated `/api/v1` baseline with controllers/resources and permission middleware. Domain endpoint naming, pagination/filter contracts, public endpoints, idempotency, and versioning rules remain to be defined per vertical slice.

### Queue/job strategy

EXISTS as a database queue default and queue-aware notification baseline. No domain jobs exist. Queue use, retries, idempotency, transaction boundaries, and failure audit behavior must be decided when notifications/imports/AI are introduced.

### Testing strategy

EXISTS as Pest feature tests with SQLite in-memory configuration and factories. The strategy is appropriate, but domain acceptance tests must be written from the approved workflow matrix before migrations and controllers. PostgreSQL compatibility still needs a separate verification path because automated tests default to SQLite.

**Architecture verdict:** starter architecture ready for reuse; EAIC architecture **P0 blocked** until workflow authority, scoped authorization, privacy, audit history, and AI boundaries are approved.

## 5. Database Readiness

`DATABASE-SCHEMA.md` is a valuable conceptual inventory but is **not migration-ready**. It explicitly labels itself draft and says migrations require approval first.

### What is defined sufficiently to guide design

- The broad entity inventory and relationships for programs, stages, participants, teams, applications, submissions, screening, rubrics, judges, conflicts, AI evidence, deliberation, decisions, incubation, mentorship, milestones, events, resources, partners, communications, and alumni.
- A proposed migration/dependency order.
- General PostgreSQL and SQLite compatibility intent.
- Candidate indexes, uniqueness, polymorphic relationships, and JSON use.

### What must be resolved before the first migration

1. Exact v1 table set: reduce the proposed broad schema to the approved vertical slice.
2. Every column's type, nullability, default, cast, and allowed values.
3. Foreign-key target and delete behavior for every relationship, including archive versus cascade versus restrict.
4. Primary-key strategy and identifier exposure for domain records.
5. State model and transition/history tables, rather than relying on unconstrained status strings.
6. Submission/revision immutability and which snapshot is authoritative.
7. Assignment scope, uniqueness, duplicate prevention, reassignment, and precedence.
8. Conflict declaration categories, resolution fields, blocking derivation, and history.
9. Rubric version identity, activation/freeze rules, criterion immutability, and evaluation linkage.
10. Evaluation draft/final/reopen representation, finalization actor/time, and score history.
11. Decision append-only/supersession/reversal rules and rationale requirements.
12. Evidence classification, ownership, visibility, retention, and media deletion behavior.
13. Audit/event history required beyond generic `activity_logs`.
14. PostgreSQL/SQLite-compatible constraints, JSON behavior, date/time semantics, and test coverage.
15. Seed data required for deterministic tests without embedding business decisions accidentally.
16. Concurrency/idempotency rules for submit, assign, finalize, and record-decision operations.

### Recommended first migration batch

Only after approval: programs, program stages, participant/team identity as approved, applications, draft/submission history, media association/access classification, screening, rubric/criteria versions, judge profiles/assignments, conflict declarations, evaluations/scores, and selection decisions. Defer incubation, mentorship, milestones, resources, partners, alumni, broad events, and AI tables.

## 6. Minimum Authoritative Workflow Model

The following is the minimum artifact that must be approved before workflow implementation. It is a required model, not an assertion that the current documents have already approved these exact values.

For each aggregate, approve a transition table with: current state, action, actor, permission, precondition, next state, transaction boundary, audit event, notification, and reversibility.

### Required aggregates

- **Program:** draft, configured, published, closed, archived; define when stages/rules/rubric become locked.
- **Application:** draft, submitted, under screening, eligible/ineligible, assigned, under evaluation, evaluation complete, in deliberation, decided, withdrawn/rejected/waitlisted/selected; define deadline locking and permitted resubmission.
- **Submission/revision:** draft edits may change; submitted snapshots are immutable; define superseding revision and authoritative snapshot.
- **Screening:** draft/in review/complete with eligibility reason, actor, timestamp, and immutable result history.
- **Judge assignment:** proposed/active/declined/removed/completed, with scope, assignment actor, and access boundaries.
- **Conflict:** not declared/declared/under review/cleared/blocked/waived only if explicitly approved; unresolved or blocked behavior must be deterministic.
- **Evaluation:** draft/final/reopened only through an authorized action; finalized scores cannot be silently mutated.
- **Rubric:** draft/active/frozen/retired with immutable version and criteria linkage.
- **Deliberation:** eligible-entry conditions, participant visibility, notes/history, and close/finalization behavior.
- **Selection decision:** a separate human-authorized record with rationale, decision type, actor/time, notification, audit event, and supersession/reversal rules.

### Non-negotiable integrity tests

- A user cannot access another applicant's records without approved scope.
- A submission cannot be accepted after a locked deadline.
- A judge cannot access an unassigned application.
- An unresolved blocking conflict prevents evaluation and deliberation access.
- Judges cannot see other judges' independent scores before the approved disclosure point.
- An active/frozen rubric cannot change evaluation semantics silently.
- A finalized evaluation cannot be silently edited.
- A decision is not calculated automatically from scores and remains a human-authorized record.
- Notifications occur after the relevant committed mutation.
- Consequential mutations produce the approved audit event and actor/time data.

## 7. Recommended MVP / Vertical Slice

The documentation supports the following smallest useful end-to-end slice as a direction, subject to the P0 approvals above:

**Program → application → screening → judge assignment → conflict check → evaluation → deliberation → human decision → notification/audit**

### Included

1. Authorized staff creates/configures and publishes a program with stages and approved application rules.
2. Applicant creates an individual or approved team application.
3. Applicant saves a draft and submits a versioned snapshot before the deadline.
4. Staff records screening.
5. Staff assigns a judge under the approved scope rules.
6. Conflict is declared/resolved and enforced before evaluation.
7. Judge evaluates independently against a frozen rubric.
8. Judge finalizes the evaluation under approved rules.
9. Authorized staff records a separate decision after deliberation.
10. Applicant receives a database notification and the lifecycle is audit-traceable.

### Deliberately excluded

Incubation operations, mentorship, milestones, resources/workspaces, partners/vendors, alumni, full events/calendar, LMS/training depth, advanced analytics, public API breadth, and applicant/staff/mentor AI assistants. Autonomous judging and automatic winner selection remain prohibited.

### Dependencies

P0 workflow/state decisions; application/revision decision; assignment/conflict rules; rubric/evaluation rules; decision authority; permission matrix; evidence privacy; approved first migration set; baseline dependency installation and verification.

### Acceptance criteria

The slice passes the integrity tests in Section 6, has policy and route coverage for every actor, records audit events for consequential actions, sends notifications after committed decisions, works with SQLite tests, and has a documented PostgreSQL verification run before release.

## 8. Trackable Task Backlog

Status meanings: **READY** means the Phase 0 activity can be performed now; **BLOCKED** means implementation depends on unresolved approval; **DEFERRED** means intentionally outside Phase 0/MVP.

| ID | Task | Objective | Dependency | Files/documents likely affected | Expected deliverable | Verification/test | Priority | Status |
|---|---|---|---|---|---|---|---|---|
| T-001 | Record baseline and authority | Establish verified source/document facts and approval hierarchy | Current checkout | This report, starter architecture | Signed baseline and authority note | Git/source inventory reviewed | P0 | READY |
| T-002 | Approve product boundary | Confirm actors, v1 scope, public access, individual/team/org participation | T-001 | Requirements, roadmap | Approved v1 boundary and exclusions | Owner approval recorded | P0 | READY |
| T-003 | Approve workflow transition matrices | Make lifecycle behavior executable and deterministic | T-002 | Requirements, schema, roadmap | State/action/actor/permission matrix | Review every required aggregate | P0 | READY |
| T-004 | Approve application versioning | Define drafts, submissions, revisions, locking, and authoritative snapshot | T-002 | Requirements, schema | Versioning decision record | Example submit/resubmit scenarios pass review | P0 | READY |
| T-005 | Approve judging integrity rules | Define assignment, conflict, rubric freeze, evaluation finalization/reopen | T-003 | Requirements, schema, roadmap | Assignment/conflict/rubric/evaluation rules | Integrity scenarios pass review | P0 | READY |
| T-006 | Approve decision/governance model | Define human authority, rationale, reversal, privacy, retention, AI boundary | T-003, T-005 | Requirements, schema, roadmap | Decision and governance policy | Consequential-action review approved | P0 | READY |
| T-007 | Approve authorization matrix | Map actors to global/program/record scope and actions | T-002, T-003 | Architecture, requirements | Permission/policy matrix | Every MVP action has one authorized actor and denial case | P0 | READY |
| T-008 | Refresh starter architecture facts | Reconcile historical claims such as migration count and runtime verification | T-001 | Starter architecture, README | Corrected starter reference | Source inventory and clean verification commands documented | P1 | READY |
| T-009 | Reduce logical schema to MVP | Convert approved decisions into migration-ready columns/constraints | T-003 through T-007 | Database schema | Approved v1 schema with FK/index/delete/state details | Schema review checklist complete | P0 | BLOCKED |
| T-010 | Write MVP acceptance test specification | Translate approved rules into Pest scenarios before implementation | T-003 through T-007 | Requirements, roadmap, tests plan | Test matrix and fixtures plan | Each P0 integrity rule has a test | P0 | BLOCKED |
| T-011 | Verify starter dependencies and baseline | Install only through approved existing lockfiles and run baseline checks | Owner approval for environment setup | README, composer/package lockfiles | Reproducible baseline record | `php artisan test --compact`, route/migration checks, frontend checks | P1 | READY |
| T-012 | Implement approved MVP migrations/models | Build only the reduced schema | T-009, T-010, T-011 | `app/Models`, `database/migrations`, factories | Tested domain persistence | Focused Pest tests on SQLite; PostgreSQL verification | P0 | BLOCKED |
| T-013 | Implement MVP workflow services/policies | Enforce transitions and authorization server-side | T-009, T-010, T-012 | Actions, controllers, requests, policies, support | Deterministic trust path | Focused feature tests and audit assertions | P0 | BLOCKED |
| T-014 | Implement MVP Inertia/API surfaces | Expose approved workflow without bypassing policies | T-013; Wayfinder route approval | Routes, resources, pages, navigation | Usable staff/applicant/judge slice | Feature, type, lint, and build checks | P0 | BLOCKED |
| T-015 | Add AI governance design only | Specify provider contract and provenance without enabling consequential automation | T-006, stable MVP | Requirements, roadmap, architecture | Approved AI design ADR | Review input/retention/source/review/failure rules | P1 | DEFERRED |
| T-016 | Implement later lifecycle modules | Add incubation, mentorship, milestones, resources, events, partners, alumni | MVP acceptance and separate phase approvals | Domain source/docs | Incremental modules | Per-phase acceptance tests | P2 | DEFERRED |

## 9. Phase 0 Checklist

| ID | Exact action | Completion condition | Evidence required | Next dependency |
|---|---|---|---|---|
| P0-01 | Confirm source code is the current implementation authority | Owner acknowledges source/document distinction | Recorded approval note | P0-02 |
| P0-02 | Mark each project document authoritative, draft, or advisory | All eight documents have an approved status | Updated status register | P0-03 |
| P0-03 | Reconcile starter architecture facts | Migration count, runtime state, and historical claims match source | Source comparison and approved corrections | P0-04 |
| P0-04 | Approve v1 actors and participation scope | Individual/team/org policy is explicit | Signed product decision | P0-05 |
| P0-05 | Approve public and authenticated access | Anonymous discovery, application start, and authenticated actions are explicit | Route/access matrix | P0-06 |
| P0-06 | Approve MVP inclusion/exclusion boundary | Vertical slice and exclusions are accepted | Owner approval | P0-07 |
| P0-07 | Approve lifecycle state machines | All required aggregates have transitions and actors | Transition tables | P0-08 |
| P0-08 | Approve submission/revision semantics | Immutable snapshot and resubmission rules are explicit | Scenario examples and decision record | P0-09 |
| P0-09 | Approve assignment/conflict rules | Scope, categories, blocking, resolution, and history are explicit | Assignment/conflict matrix | P0-10 |
| P0-10 | Approve rubric/evaluation integrity | Freeze, finalization, reopen, and score history are explicit | Rubric/evaluation decision record | P0-11 |
| P0-11 | Approve human decision authority | Decision record, rationale, reversal, notification, and audit are explicit | Governance approval | P0-12 |
| P0-12 | Approve privacy/evidence policy | Visibility, retention, deletion, and AI input boundaries are explicit | Privacy/access matrix | P0-13 |
| P0-13 | Approve domain authorization matrix | Every MVP action has actor, permission, record scope, and denial behavior | Policy matrix | P0-14 |
| P0-14 | Reduce and approve the first schema | Columns, constraints, FKs, indexes, delete rules, and states are complete | Migration-ready schema review | P0-15 |
| P0-15 | Define acceptance tests before coding | Every trust rule has a test scenario and fixture plan | Approved Pest test matrix | P0-16 |
| P0-16 | Verify starter baseline in the available environment | Dependencies are installed from lockfiles and baseline checks are recorded | Test/build/route/migration output | Phase 1 approval |
| P0-17 | Approve Phase 1 implementation task breakdown | Exact files, migrations, policies, routes, pages, and tests are assigned | Signed Phase 1 plan | Coding may begin |

## 10. What Codex Must Not Do Yet

Until Phase 0 approval, Codex must not:

- Create or modify EAIC migrations or perform destructive database changes.
- Generate broad domain CRUD, models, controllers, policies, routes, or UI from draft documents.
- Add package dependencies or change configuration without explicit approval.
- Implement an AI provider, AI judge, autonomous recommendation, or consequential automation.
- Infer unresolved state transitions, role scopes, conflict rules, privacy classifications, or decision authority from UI convenience.
- Add large refactors to starter infrastructure.
- Replace Fortify, Spatie Permission, ActivityLogger, notifications, media, API, Inertia, Wayfinder, or testing conventions.
- Treat benchmark scores or draft prose as approval.
- Commit changes or rewrite existing untracked project documents without instruction.

## 11. Recommended Document Update Order

1. `PROJECT-REQUIREMENTS.md`: approve actors, v1 boundary, public access, participation, workflow authority, decision governance, privacy, and AI safety boundary.
2. `DATABASE-SCHEMA.md`: reduce to the approved MVP and specify columns, constraints, state/history, deletes, indexes, and immutability.
3. `PROJECT-ROADMAP.md`: update phases, dependencies, first-slice scope, acceptance gates, and task ownership from the approved product/schema decisions.
4. `MASTER-STARTER-ARCHITECTURE.md`: refresh verified starter facts and document the approved domain extension boundary.
5. `AI-PROJECT-STARTER.md`: update ready-to-code criteria and agent restrictions to point at the approved EAIC contract.
6. `README.md`: add EAIC setup and verification instructions only after implementation begins and the runtime workflow is real.
7. `CODEx-BENCHMARK-REPORT.md`: retain as advisory input and optionally append a post-approval reassessment; do not make it authoritative.
8. `AGENTS.md`: change only when repository-wide implementation rules genuinely change; current rules are sufficient for Phase 1.

No document should be rewritten merely to hide unresolved decisions. Each update should preserve the decision owner, approval date, and evidence.

## 12. Final Recommendation

1. **Is the project ready for coding today?** No for broad EAIC domain coding. The starter is ready for reuse, but domain implementation is P0 blocked.
2. **What prevents coding?** Unapproved lifecycle transitions, submission immutability, assignment/conflict enforcement, rubric/evaluation integrity, decision authority, privacy/access rules, domain-scoped authorization, and the migration-ready v1 schema. The local environment also cannot execute tests until dependencies are installed.
3. **Smallest safe first implementation step:** approve and record the Phase 0 workflow, governance, authorization, privacy, and MVP boundary decisions, then reduce `DATABASE-SCHEMA.md` to the first vertical-slice schema and its Pest acceptance matrix.
4. **What should the project owner decide next?** Approve the v1 actor/participation model, public access, vertical slice, state transition matrices, revision model, judge/conflict rules, rubric/evaluation finalization, human decision authority, privacy/retention, and permission scope.
5. **What should remain untouched?** EAIC migrations, domain CRUD, AI provider code, broad UI/API generation, large starter refactors, package/config changes, and consequential workflow automation until the checklist reaches P0-17.

**Phase 0 exit condition:** the owner has approved the product boundary and trust-critical workflow artifacts; the schema is migration-ready for the reduced MVP; every consequential rule has an acceptance test; the starter baseline is reproducibly verified; and a Phase 1 task plan identifies exact implementation surfaces.
