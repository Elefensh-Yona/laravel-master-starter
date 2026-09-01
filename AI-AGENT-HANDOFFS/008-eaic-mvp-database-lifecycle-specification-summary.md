# AI Agent Handoff 008: EAIC MVP Database and Lifecycle Specification Summary

## 1. Interaction ID

`008`

## 2. Task Requested

Produce the migration-ready MVP database and lifecycle/state specification for EAIC, without implementing migrations, models, policies, services, routes, roles, permissions, database tables, or tests.

## 3. Sources Read

- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `AI-PROJECT-STARTER.md`
- `MASTER-STARTER-ARCHITECTURE.md`
- `PROJECT-REQUIREMENTS.md`
- `DATABASE-SCHEMA.md`
- `PROJECT-ROADMAP.md`
- `CODEX-PHASE-0-IMPLEMENTATION-PLAN.md`
- `AGENTS.md`
- `README.md`
- `TheRoadmap/decisions.md`
- `AI-AGENT-HANDOFFS/001-baseline-environment-summary.md` through `AI-AGENT-HANDOFFS/007-eaic-mvp-rbac-scope-matrix-summary.md`
- Existing starter migrations, models, RBAC source, and database conventions.

## 4. Files Created

- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `AI-AGENT-HANDOFFS/008-eaic-mvp-database-lifecycle-specification-summary.md`

## 5. Files Modified

No existing files were modified in this interaction. The two files above were created.

The pre-existing `.env.example` rename change remains untouched.

## 6. Files Not Modified

- Historical handoffs 001–007.
- EAIC blueprint, contract, RBAC matrix, requirements, schema, roadmap, Phase 0 plan, starter architecture, README, AGENTS instructions, and decision log.
- Application source, models, controllers, policies, services, routes, frontend, tests, factories, seeders, and migrations.
- `.env`, package manifests, lockfiles, and generated artifacts.
- PostgreSQL or SQLite database structures/data.

## 7. Decision-Source Note

The task says Product & Technical Controller decisions are approved through Decision 51. The available repository blueprint contains the explicitly numbered approved decisions 1–35 only. No separate decision record containing decisions 36–51 was found in the workspace.

Accordingly:

- Decisions 1–35 in the blueprint were treated as authoritative.
- No decisions 36–51 were reconstructed or invented.
- Implementation-level values not stated by the available blueprint remain `OWNER DECISION REQUIRED`.
- Mechanical choices such as numeric precision, state labels, key strategy, and delete behavior are labeled `RECOMMENDED` and require acceptance before implementation.

## 8. Entity Inventory

The specification defines the MVP entities requested:

- Program.
- Program Membership.
- Application.
- Application Member.
- Application Version.
- Screening and automated validation results.
- Judge Assignment.
- Conflict of Interest.
- Rubric.
- Rubric Version.
- Rubric Criterion.
- Evaluation.
- Evaluation Criterion Score.
- Deliberation.
- Decision.
- Outcome/Transition.
- Existing Notification.
- Existing Activity/Audit Event foundation.

It identifies reuse of the Master Starter `users`, Spatie authorization tables, `media`, `notifications`, `activity_logs`, and `settings` rather than duplication.

Each entity includes purpose, ownership, lifecycle relevance, relationships, history requirements, and immutability behavior.

## 9. Relationship Summary

The specification defines:

- Program to memberships, eligibility, applications, assignments, rubrics, deliberations, decisions, and outcomes.
- Application to primary owner, members, versions, screenings, assignments, conflicts, evaluations, deliberations, decision, and outcome.
- Rubric to versions to criteria to evaluation scores.
- Evaluations to exact application version, exact rubric version, Judge, application, program, and criterion scores.
- User to program membership, application ownership/membership, Judge assignment, Decision Maker authority, notifications, and audit events.

It recommends retaining direct program/application foreign keys on operational records for policy predicates and integrity.

## 10. Lifecycle Summary

The specification provides transition tables for:

- Program: draft, published, closed, archived.
- Application: draft, submitted, screening, eligible/ineligible, assigned, under evaluation, evaluated, in deliberation, decided, outcomed.
- Application Version: draft/submitted with immutable submitted snapshots and controlled revision.
- Screening: automated validation plus human staff review/completion.
- Judge Assignment: active, declined, reassigned/ended, removed, completed.
- Conflict: further review, declared, cleared, non-blocking, blocked.
- Rubric/Version: draft, active, frozen, retired, new exceptional version.
- Evaluation: draft, submitted, finalized, reopened.
- Deliberation: open, active, closed.
- Decision: draft, finalized, governed reversal/supersession.
- Outcome/Transition: recorded, transitioned, governed successor.

State values are labeled recommended MVP vocabulary where the blueprint does not enumerate literal names. Actors, permissions, scope, prerequisites, blocking conditions, and audit requirements are specified for every transition. Exact business state approval remains listed where needed.

## 11. Versioning Summary

- Application versions are application-local positive monotonically increasing integers.
- Submitted versions are immutable.
- Controlled revisions create a new version and preserve the prior version.
- The application may maintain a current/active pointer for workflow convenience.
- Evaluations store the exact `application_version_id` and never follow a later current pointer.
- Previous versions remain readable only under authorization.
- Strict timezone-aware deadlines prevent ordinary late submission/revision; governed exceptions are audited.

## 12. Evaluation and Scoring Summary

The specification preserves separate concepts for:

- frozen rubric version;
- criterion and weight;
- raw score;
- calculated contribution;
- calculated normalized total;
- qualitative assessment;
- criterion justification/evidence;
- Judge recommendation;
- evaluation status/finalization;
- reopening/revision history; and
- final human decision.

Approved scoring direction represented:

- raw scores from 0 through 10;
- weighted deterministic scoring;
- normalized 100-point total;
- mean, median, spread/range, and disagreement available for deliberation;
- Judges cannot override calculated totals;
- final outcome is not mechanically derived from scores.

**RECOMMENDED technical calculation:** weights total exactly 100 percentage points; `numeric` values, persisted contributions/totals, recalculation in finalization transaction, and two-decimal display after approved precision. Exact precision/rounding still requires approval because it is not stated in the available decision record.

## 13. Conflict Model Summary

- System detects objective signals.
- Judge declares known/suspected conflict.
- Authorized human determines clear, non-blocking, further-review, or blocking status.
- Blocking status becomes an authorization restriction, denying restricted evaluation/score/finalization/deliberation actions.
- Program Staff performs controlled conflict-aware reassignment.
- Declaration, detection, determination, reason, actor, timestamps, and history are preserved.
- AI cannot resolve conflicts.

Conflict category vocabulary, waiver rules, determination authority, and disclosure fields remain owner decisions.

## 14. RBAC Data Dependencies

The schema must support:

- Program Staff via program membership, capability, stage scope, permission, and policy.
- Judges via program membership, Judge capability, assignment, exact application/version, and no blocking conflict.
- Applicants via primary owner and application members.
- Decision Makers via distinct program capability/membership.
- Evaluation confidentiality via Judge ownership and deliberation disclosure state.
- Protected history via immutable states and audit events.
- Governance override via explicit authority, reason, actor/time, preserved prior state, and audit.

The recommendation is domain membership/application relationships plus inherited Spatie capabilities and record-level policies; no Spatie teams redesign was implemented.

## 15. Immutability and History Rules

The specification requires protection for submitted versions, frozen rubric versions/criteria, finalized evaluations/scores, closed deliberation, finalized decisions, outcomes/transitions, conflicts, assignment history, completed screening, membership changes, notifications, and audit events.

It recommends restrictive deletion and archive/supersession over blanket soft deletes. Hard deletion is limited to disposable abandoned drafts with no consequential dependency.

## 16. PostgreSQL Design Decisions

- PostgreSQL is primary; SQLite remains test compatibility.
- Use timezone-aware timestamps plus an IANA program timezone.
- Use `numeric`, not floating point, for scoring/weights/totals.
- Use `jsonb` only for variable metadata/results/properties, not authoritative relational state.
- Prefer constrained strings plus transition logic over PostgreSQL enum types.
- Use transactions and locking/optimistic checks for submit, versioning, assignment, conflicts, rubric freeze, finalization, deliberation, decisions, and transitions.
- Recalculate score totals inside finalization transaction.
- Make notification/audit emissions idempotent and dispatch email after commit.

## 17. Migration Dependency Order

1. Existing Master Starter baseline.
2. Programs.
3. Program memberships/scope.
4. Eligibility rules.
5. Applications.
6. Application members.
7. Application versions.
8. Media/version association.
9. Validation/screening.
10. Judge identity/profile if approved.
11. Assignments.
12. Conflicts.
13. Rubrics.
14. Rubric versions.
15. Criteria.
16. Evaluations.
17. Criterion scores.
18. Deliberations and required disclosure/participant records.
19. Decisions.
20. Outcomes/transitions.
21. Any approved idempotency/event support.
22. Seed/reference data after schema/RBAC approval.

This order is a recommendation, not authorization to migrate.

## 18. MVP / Deferred Boundary

### MVP

Programs, memberships, eligibility/validation, applications, members, immutable versions, media association, screening, assignments, conflicts, rubrics/versions/criteria, evaluations/scores, deliberation, decisions, outcomes/transitions, existing notifications, and existing activity logs.

### Deferred

Incubation, mentorship, milestones, resources, events/training/showcase, partners/vendors, alumni/follow-up, broad AI assistants, and autonomous decision systems.

## 19. Acceptance-Test Specification

The specification defines 35 database/domain cases covering:

- foreign-key and uniqueness integrity;
- submitted-version immutability;
- exact evaluation-to-version linkage;
- frozen rubric and criterion integrity;
- 0–10 validation and deterministic weighted calculation;
- duplicate assignment and conflict blocking;
- program, applicant, and Judge scope;
- evaluation privacy and finalization/reopen protection;
- deliberation prerequisites;
- decision authority, rationale, uniqueness, and reversal;
- outcome validity;
- post-commit notification behavior;
- append-only audit;
- deadline concurrency and idempotency;
- PostgreSQL verification.

These tests are specifications only and were not implemented or run.

## 20. Owner Decision Required Items

1. Exact permission names and role/capability matrix.
2. Storage/cardinality for memberships, stage scope, assignments, application members, and Decision Maker authority.
3. Public fields/authentication and applicant delegation rules.
4. Exact lifecycle state names and complete transition matrix.
5. Assignment granularity/precedence.
6. Conflict categories, indirect affiliation, waiver, authority, disclosure.
7. Score precision, rounding, and calculation-version policy.
8. Field-level applicant/Judge/Decision Maker disclosure.
9. Notification catalog, recipients, timing, email/retry behavior.
10. Program-specific outcomes and transition targets.
11. EAIC namespace/module structure.
12. Confirmation of PostgreSQL `public` schema/database `development`.
13. Separate Judge profile requirement.
14. Deliberation/disclosure/validation/history table representation.
15. Acceptance of technical recommendations: numeric precision, strings vs enums, key strategy, delete/archive, and idempotency.
16. The missing decisions 36–51 record, if those decisions add rules beyond the available blueprint.

## 21. Verification Performed

- Confirmed the required source documents and handoffs were read/reviewed.
- Confirmed the available blueprint contains numbered decisions 1–35 and no available decision record 36–51 was found.
- Checked the new specification for all 15 requested deliverable areas.
- Confirmed no EAIC implementation artifacts were created in `app/Models`, `app/Http/Controllers`, `database/migrations`, `routes`, or `resources/js`.
- Confirmed migration count remains 14.
- Confirmed no existing file was modified by this interaction.
- Confirmed no `.env`, package, lockfile, database, or historical handoff changes occurred.
- Ran `git diff --check`: passed.

## 22. Test Results

No executable application or database tests were run. This interaction was specification-only, and focused content/scope verification was the appropriate check. No claim is made that the application suite passed in Interaction 008.

## 23. Database Changes

None.

- No migrations created or modified.
- No EAIC tables created.
- No Master Starter tables changed.
- No schema or data changed.
- No destructive database operation performed.

## 24. Git Status

- Branch: `main`.
- Upstream: `main...upstream/main`.
- Latest relevant commit: `dc03e5e (HEAD -> main, upstream/main, upstream/HEAD) Add AI project starter onboarding guide`.
- Pre-existing tracked modification: `.env.example` from Interaction 004.
- Existing untracked planning/contract/blueprint documents remain present.
- Historical handoffs 001–007 remain present and unchanged.
- New untracked specification: `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`.
- New untracked handoff: `AI-AGENT-HANDOFFS/008-eaic-mvp-database-lifecycle-specification-summary.md`.
- No commit was created.

## 25. Known Risks

- Decisions 36–51 were referenced but no source record was available; implementation must not assume they add nothing.
- Several technical values are recommendations rather than approved product decisions.
- Exact state names and schema cardinality/constraints may change after controller review.
- PostgreSQL baseline exists, but EAIC-specific PostgreSQL behavior cannot be verified until migrations are implemented.
- Acceptance tests are not implemented and no executable tests were run in this documentation-only interaction.

## 26. Recommended Next Task

Stop for Product & Technical Controller review of Handoff 008 and the specification.

First supply/register the approved decisions 36–51 if they exist, then approve the remaining schema/lifecycle details and acceptance-test contract. The next implementation task should create only the approved MVP migrations/models after that review, beginning with the first migration dependency batch and PostgreSQL verification. Do not begin implementation from this handoff alone.

## 27. Verified Facts vs Assumptions

**Verified:** the specification and handoff were created; the available blueprint contains decisions 1–35; no source implementation/database/package/environment files changed; migration count remains 14; existing handoffs remain unchanged; the requested entity, relationship, lifecycle, versioning, evaluation, conflict, RBAC, history, PostgreSQL, ordering, MVP/deferred, and acceptance-test sections are present; and focused documentation verification passed.

**Recommendations:** state vocabulary, numeric precision/rounding, key strategy, delete/archive policy, direct redundant foreign keys, index set, and idempotency representation.

**Assumptions avoided:** decisions 36–51 were not invented; exact permissions, role mappings, database columns, foreign keys, indexes, migration files, namespace, notification implementation, and unapproved business states/outcomes were not treated as final without owner approval; no migration or domain code was created.
