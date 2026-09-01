# AI Agent Handoff 007: EAIC MVP RBAC + Scope Matrix Summary

## 1. Interaction ID

`007`

## 2. Task Requested

Define the implementation-ready MVP authorization model for Ethiopian AI Center (EAIC) as specification only. Cover actors, exact proposed permission catalog, role-permission matrix, scope matrix, authorization order, critical security/governance rules, and authorization acceptance-test specifications. Do not implement code, roles, permissions, migrations, models, policies, routes, services, UI, or database changes.

## 3. Sources Read

- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `AI-PROJECT-STARTER.md`
- `MASTER-STARTER-ARCHITECTURE.md`
- `PROJECT-REQUIREMENTS.md`
- `DATABASE-SCHEMA.md`
- `PROJECT-ROADMAP.md`
- `CODEX-PHASE-0-IMPLEMENTATION-PLAN.md`
- `AGENTS.md`
- `README.md`
- `TheRoadmap/decisions.md`
- `AI-AGENT-HANDOFFS/001-baseline-environment-summary.md`
- `AI-AGENT-HANDOFFS/002-postgresql-rbac-test-contract-summary.md`
- `AI-AGENT-HANDOFFS/003-postgresql-rbac-contract-summary.md`
- `AI-AGENT-HANDOFFS/004-project-name-rename-summary.md`
- `AI-AGENT-HANDOFFS/005-eaic-authoritative-contract-summary.md`
- `AI-AGENT-HANDOFFS/006-eaic-blueprint-reconciliation-summary.md`
- Existing starter RBAC source: `app/Support/SystemRole.php`, `database/seeders/RolePermissionSeeder.php`, `config/permission.php`, `bootstrap/app.php`, policies, and route middleware.

## 4. Files Created

- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `AI-AGENT-HANDOFFS/007-eaic-mvp-rbac-scope-matrix-summary.md`

## 5. Files Modified

No existing files were modified in this interaction. The two files listed above were created.

The pre-existing `.env.example` modification from the approved rename interaction remains unchanged.

## 6. Files Not Changed

- Historical handoffs 001–006.
- EAIC blueprint and reconciled contract.
- Requirements, schema, roadmap, Phase 0 plan, starter architecture, README, AGENTS instructions, and decision log.
- All application source, models, controllers, policies, routes, services, frontend files, tests, migrations, and seeders.
- `.env`, package manifests, and lockfiles.
- PostgreSQL database and local SQLite database.

## 7. MVP Actor Model

The matrix defines five MVP actors:

- **Super Admin:** broad system administration; protected from silently rewriting governed history; no implicit Judge assignment.
- **Program Staff:** operates only programs covered by membership, capability, permission, stage, and record policy; performs human screening and operational workflow actions.
- **Decision Maker:** separate human authority; decides only within approved program/application authority and deliberation prerequisites; must provide rationale.
- **Judge:** requires program membership, Judge capability, stage scope, explicit assignment, exact submitted version, and no blocking conflict; evaluates independently and respects private-score controls.
- **Applicant:** owns or is an approved member of an application; manages permitted drafts/revisions and sees only applicant-tier information.

No additional role was created. Mentors and partners remain deferred lifecycle actors.

## 8. Permission Catalog Summary

The document contains a proposed 34-permission MVP catalog using singular resource/action names, including:

- Program: `program.view`, `program.create`, `program.update`, `program.publish`.
- Eligibility: `eligibility.view`, `eligibility.validate`, `eligibility.screen`.
- Application: `application.view`, `application.create`, `application.update`, `application.submit`, `application.revise`.
- Assignment: `assignment.view`, `assignment.create`, `assignment.reassign`.
- Conflict: `conflict.declare`, `conflict.view`, `conflict.determine`.
- Evaluation: `evaluation.view`, `evaluation.create`, `evaluation.update`, `evaluation.submit`, `evaluation.finalize`, `evaluation.reopen`.
- Deliberation: `deliberation.view`, `deliberation.participate`, `deliberation.manage`.
- Decision: `decision.view`, `decision.create`, `decision.finalize`, `decision.reverse`.
- Governance: `audit.view`.

These names are **PROPOSED**, not approved or seeded. The blueprint approves domain/action permissions with policy enforcement but does not specify literal strings.

## 9. Role-Permission Matrix Summary

The matrix provides ALLOW, DENY, or CONDITIONAL for every proposed permission across Super Admin, Program Staff, Decision Maker, Judge, and Applicant. Conditional rules are explicit:

- Super Admin requires governance override and protected-history policy for exceptional actions.
- Program Staff requires target-program membership, capability/permission, stage scope, and record policy.
- Decision Maker requires approved authority, target program/application scope, and deliberation prerequisites.
- Judge requires program membership, Judge capability, assignment, stage, exact version, no blocking conflict, and confidentiality policy.
- Applicant requires public/ownership/member policy, permitted state, and deadline policy.

The matrix denies ordinary Super Admin `decision.create`/`decision.finalize`, preserving separate Decision Maker authority. Governance changes use explicit override paths rather than normal mutation permissions.

## 10. Scope Model Summary

The authorization order and scopes implement the approved layered model:

- Program Staff: membership and capability restrict program/stage/application operations; no unrelated-program access.
- Judge: membership + Judge capability + stage + assignment + exact submitted version + no blocking conflict + controlled disclosure.
- Applicant: primary owner/application member + record policy; membership does not automatically grant owner-only actions.
- Decision Maker: separate authority scoped to authorized programs/applications and deliberation prerequisites.
- Super Admin: broad system administration but protected trust-critical history and explicit governance overrides.

No database mechanism was selected for these relations. Storage/cardinality remains an owner decision.

## 11. Authorization Order

The recommended evaluation sequence is:

1. Authenticated user/account state.
2. EAIC role/capability.
3. Program membership.
4. Stage scope.
5. Domain/action permission.
6. Assignment or ownership.
7. Record-level policy.
8. Conflict-of-interest state.
9. Governance restrictions.
10. ALLOW/DENY result.

The sequence narrows from identity/capability to relationship, confidentiality, integrity, and governance gates. Conflict and governance are final denial gates for otherwise apparently permitted actions.

## 12. Critical Security Rules

The specification explicitly defines:

- Unassigned Judge application access: **DENY**.
- Another Judge's private evaluation before controlled disclosure: **DENY**.
- Blocking Judge conflict: **DENY** evaluation/restricted participation.
- Cross-applicant access: **DENY**.
- Application member owner-only action: **DENY** absent approved delegation.
- Staff access outside program scope: **DENY**.
- Decision Maker outside authority: **DENY**.
- Direct Super Admin protected-history mutation: **DENY**.
- Governance exception: ordinary action **DENY**; explicit reasoned audited override may **ALLOW**.
- Finalized evaluation after deliberation: **CONDITIONAL** viewing by approved governance/staff/Decision Maker scopes; no silent mutation.

## 13. Acceptance-Test Specification Summary

The specification includes 17 positive and 22 negative/security cases. Positive coverage includes authorized program operation, publication, applicant draft/submission, automated validation, human screening, assignment, conflict declaration, frozen rubric evaluation, finalization, controlled disclosure, human decision, applicant notification, and audited override.

Negative coverage includes cross-program and cross-applicant isolation, unassigned Judge access, private-score disclosure, blocking conflicts, wrong version evaluation, manual score override, frozen-rubric mutation, immutable-submission mutation, unauthorized decisions, audit/confidentiality violations, unauthorized web/API access, direct URL/API bypass, unauthorized reopen, AI consequential decisions, and confidential notification leakage.

These tests are specifications only and were not implemented or run.

## 14. OWNER DECISION REQUIRED Items

1. Approve exact permission strings; the catalog is proposed.
2. Approve exact role-to-permission/capability mapping, including treatment of inherited `Manager` and `Staff`.
3. Approve storage/cardinality for memberships, capabilities, stage scope, assignments, application members, and Decision Maker authority.
4. Approve public fields and authentication boundary for program/application initiation.
5. Approve team lead, invitation, delegation, organization representation, and owner-only action rules.
6. Approve exact lifecycle state names and transition preconditions.
7. Approve conflict categories, indirect affiliation, waiver, determination authority, and disclosure.
8. Approve rubric scoring scale, precision, rounding, and weights.
9. Approve field-level visibility for applicants, Judges, Decision Makers, and staff.
10. Approve notification events, recipients, timing, email, and retry/failure semantics.
11. Approve migration-ready schema and history representation.
12. Approve EAIC namespace/module structure.
13. Approve exact program-specific outcome values and transition targets.

These are not silently decided by this task. Database table design, column names, foreign keys, indexes, migration structure, scoring scale, notification implementation, and namespace structure were explicitly excluded.

## 15. Verification Performed

- Confirmed required source documents and all prior handoffs were read/reviewed.
- Confirmed the approved blueprint’s layered authorization decisions and current starter RBAC mechanics.
- Checked the new matrix contains actor model, permission catalog, role matrix, scope rules, authorization order, critical rules, acceptance-test specification, implementation boundary, and owner-decision section.
- Confirmed no EAIC implementation files exist under `app/Models`, `app/Http/Controllers`, `database/migrations`, `routes`, or `resources/js`.
- Confirmed migration count remains 14.
- Confirmed no application source, database, package, environment, or historical handoff changes occurred.
- Ran `git diff --check`: passed.

## 16. Test Results

No executable application tests were run. This was documentation/specification work only. The acceptance tests in the matrix are future specifications, not executed tests.

## 17. Database Changes

None.

- No migrations created or modified.
- No models or seeders created.
- No roles or permissions inserted.
- No PostgreSQL or SQLite database structure/data changed.
- No destructive command performed.

## 18. Git Status

- Branch: `main`.
- Upstream relation: `main...upstream/main`.
- Latest relevant commit: `dc03e5e (HEAD -> main, upstream/main, upstream/HEAD) Add AI project starter onboarding guide`.
- Pre-existing tracked change: `.env.example` from Interaction 004.
- Existing untracked planning and blueprint/contract files remain present.
- Existing handoffs 001–006 remain present and unchanged.
- New untracked files: `EAIC-MVP-RBAC-SCOPE-MATRIX.md` and `AI-AGENT-HANDOFFS/007-eaic-mvp-rbac-scope-matrix-summary.md`.
- No commit was created.

## 19. Known Risks

- Literal EAIC permission names and role mapping remain unapproved; implementation must not seed the proposed catalog without controller approval.
- Scope storage/cardinality is unspecified and may affect schema and policy design.
- Exact lifecycle state names, conflict categories, disclosure fields, scoring scale, and notification behavior remain open.
- The existing starter Super Admin global bypass must be reconciled carefully with EAIC protected-history rules during policy implementation.
- No executable tests were run in this documentation-only interaction.

## 20. Recommended Next Task

Stop for Product & Technical Controller review of Handoff 007.

The next safe task should be controller approval or revision of the exact permission catalog, role/capability matrix, scope storage model, and remaining field-level authorization rules. After those are approved, prepare the migration-ready MVP database schema and acceptance-test specification. Do not create migrations or implement RBAC from this handoff alone.

## 21. Verified Facts vs Assumptions

**Verified:** current starter RBAC is global, web-guard based, permission-middleware driven, policy-backed, teams-disabled, and includes a Super Admin global gate bypass; the EAIC blueprint mandates layered authorization and protected trust-critical boundaries; the specification file and handoff were created; no implementation or database changes occurred; and focused documentation/scope checks passed.

**Proposed:** exact permission strings, role matrix entries, conditional scope predicates, and future acceptance cases.

**Assumptions avoided:** no database schema, scope storage mechanism, namespace, lifecycle state names, scoring scale, notification implementation, or unapproved role/permission was created or seeded.
