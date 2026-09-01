# AI Agent Handoff 012: EAIC Pre-Migration Decision Register Summary

## 1. Interaction ID

`012`

## 2. Task Requested

Finalize or deliberately defer migration-blocking technical and product decisions, create the final pre-migration decision record, clarify the final schema contract only where required, define the intentionally small first migration batch, and stop before any implementation/database work.

## 3. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- Existing Master Starter migrations and `config/database.php`.
- Required project documentation and Handoffs 001–011.

## 4. Decision Areas Reviewed

- MVP permission catalog/capability mapping.
- Program membership/capability scope.
- Application authentication/access boundary.
- Team/Organization membership and delegation.
- Application content and eligibility model.
- Withdrawal/revision/reopening/appeal boundary.
- Conflict, disclosure, Judge profile, notifications, and outcomes/transitions.
- PostgreSQL physical conventions.
- History/deletion and transaction/concurrency invariants.
- First migration batch boundary.

## 5. Decisions Finalized

- Permission names use the approved singular `resource.action` convention; a canonical MVP catalog is recorded.
- Explicit Program Membership is the primary scope relation; multiple capabilities per user/program and differing capabilities across programs are supported.
- Primary owner, approved member, and bounded delegation rules are finalized for application access.
- Authentication before application creation is a safe technical choice for attributable ownership/audit; public program discovery remains distinct.
- Variable application content is versioned JSONB while authoritative relationships/state remain relational.
- Eligibility uses a small keyed Program Eligibility Rule record, objective validation result, and human Staff Screening; no generic rules engine.
- Submitted versions are immutable; controlled revision produces successors; appeals are deferred.
- Conflicts are hybrid and human-determined; `blocked` restricts Judge actions; waiver is deferred.
- Evaluation confidentiality is locked by tier; applicant feedback remains distinct from Judge-private records.
- No separate Judge Profile table is needed for MVP.
- Existing in-app notifications are authoritative; email is after commit and failure does not remove in-app record.
- Decision, Outcome, and Transition are separate; outcomes are `ACCEPTED`, `REJECTED`, `WAITLISTED`, and `REVISION_REQUIRED`.
- PostgreSQL-first, timezone-aware, numeric, constrained-string, JSONB-boundary, history, and transaction directions are documented.
- Batch 1 is locked to `programs`, `program_memberships`, `program_eligibility_rules`, and `rubrics`.

## 6. Decisions Explicitly Deferred

- Appeals workflow.
- Conflict waiver.
- Dynamic form-builder/question-bank/branching questions.
- Generic executable eligibility rules engine and external data connectors.
- Judge expertise/availability/qualification profile and onboarding.
- Invitation delivery workflow, organization verification, team hierarchy, multiple primary owners, ownership-dispute process.
- Incubation, mentorship, milestones, resources, events/training/showcase, partners/vendors, alumni/follow-up, broad AI assistants, and autonomous decision systems.

## 7. OWNER DECISION REQUIRED Items Remaining

- Literal role-to-permission grants in the existing Spatie seeder.
- Capability vocabulary and whether Stage Scope must be relational in Batch 1.
- Exact public Program fields and application-initiation behavior beyond mandatory authentication for mutation.
- Member approval actor, team lead/invitation/submission-on-behalf mechanics.
- Exact withdrawal-after-assignment policy.
- Conflict determination capability, categories/indirect-affiliation rule, second-review requirement, and field disclosure.
- Exact evaluation disclosure fields/aggregates and applicant feedback content.
- Notification email enablement, cadence, recipient delegation behavior, and wording.
- Configured Program/stage transition targets and `REVISION_REQUIRED` placement by Program policy.
- Exact application content/question schema and eligibility rule types.

## 8. SAFE TECHNICAL RECOMMENDATIONS

- Laravel-style bigint primary keys.
- Partial unique indexes for active/current relationships.
- `timestamptz` plus Program IANA timezone.
- `numeric(4,2)` raw score; `numeric(5,2)` weights/contribution/total; half-up rounding to two calculated decimals.
- Constrained strings and explicit transition logic rather than PostgreSQL enums.
- Domain relationship tables rather than Spatie teams for scope.
- JSONB only for variable content/metadata/result data.
- Restrictive history foreign keys, archive/supersession, and draft-only deletion instead of blanket soft deletion.
- Row locks or optimistic checks plus idempotency identity on retryable consequential commands.

## 9. Schema Contract Changes

`EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md` was clarified to reference this register and to state the approved first migration batch: `programs`, `program_memberships`, `program_eligibility_rules`, and `rubrics`. The migration graph now explicitly defers rubric versions/criteria, applications, and consequential workflow tables to later dependent batches.

## 10. RBAC Implications

No RBAC code was changed. Batch 1 stores program-scoped membership/capability data but does not seed EAIC permissions or map literal role grants. Future policies will combine active Program Membership, capability, singular permission, stage scope when applicable, and record policy.

## 11. Lifecycle Implications

Batch 1 creates only configuration foundations. Application lifecycle, immutable versions, screening, assignment, conflict, evaluation, deliberation, decisions, outcomes, and notifications remain later dependent batches. This prevents a partially implemented consequential workflow.

## 12. PostgreSQL Conventions

PostgreSQL remains primary. Use timezone-aware lifecycle timestamps, Program IANA timezone, numeric scoring, constrained string states, limited JSONB, restrictive history FKs, indexes for policy predicates, and transactional protection. Exact Laravel migration syntax is not included because this task is documentation-only.

## 13. History and Deletion Rules

Trust-critical records are preserved. Submitted versions/evidence, frozen rubrics, finalized evaluations, closed deliberations, decisions/outcomes, conflicts/assignments, membership/delegation changes, notifications, and activity events are not silently erased. Draft-only deletion and archive/supersession are the safe narrow exceptions.

## 14. Transaction and Concurrency Rules

Submission/versioning, membership/delegation changes, Judge assignment, conflict determination, rubric freeze, evaluation finalization, deliberation entry, decisions/outcomes, and notification/audit emission require atomic invariants. Email dispatch occurs only after authoritative in-app notification and transaction commit.

## 15. First Migration Batch Definition

**Batch 1:**

1. `programs`
2. `program_memberships`
3. `program_eligibility_rules`
4. `rubrics`

It reuses starter `users`, Spatie tables, settings, activity logs, media, and notifications. It introduces neither application/judging workflow nor EAIC role/permission seeding. Its acceptance gate is focused PostgreSQL and SQLite migration/constraint coverage.

## 16. Files Created

- `EAIC-PRE-MIGRATION-DECISION-REGISTER.md`
- `AI-AGENT-HANDOFFS/012-eaic-pre-migration-decision-register-summary.md`

## 17. Files Modified

- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`

## 18. Files Not Modified

- Handoffs 001–011.
- `TheRoadmap/decisions.md` and all historical decisions.
- Existing EAIC blueprint/contract/RBAC/database specifications except the targeted final-schema clarification.
- Application code, migrations, models, controllers, routes, policies, services, UI, tests, factories, seeders, packages, lockfiles, `.env`, and databases.

## 19. Verification Performed

- Reviewed decisions D-008–D-050 and all current EAIC specification inputs.
- Confirmed the four-table first migration boundary depends only on existing starter tables.
- Confirmed no migration, source, database, package, or `.env` file changed.
- Confirmed migration count remains 14.
- Confirmed Handoff sequence supports the new `012` record.
- Ran focused `git diff --check` and contract-section checks.

## 20. Test/Check Results

No executable application tests were run because no executable code changed. Focused documentation/scope checks were performed. No test failure occurred.

## 21. Database Changes

None. PostgreSQL/SQLite were not altered; no migrations, schema/data commands, role/permission seeding, or destructive operations were performed.

## 22. Git Status

- Branch: `main`, tracking `upstream/main`.
- Pre-existing tracked modifications: `.env.example` and `TheRoadmap/decisions.md`.
- Existing EAIC documentation/handoffs remain untracked in the current worktree.
- New untracked files are the pre-migration register and Handoff 012.
- No commit was created.

## 23. Known Risks

- Batch 1 stores capability values before literal Spatie permission grants are approved; migration implementation must not infer grants.
- Application public fields, content schema, and workflow details remain deliberately absent from Batch 1.
- Partial unique index portability must be validated in the Laravel migration and SQLite test path.
- The final contract is implementation-ready for Batch 1 only; later batches retain dependent owner decisions.

## 24. Recommended Next Task

Stop for Product & Technical Controller review. Once accepted, authorize a narrowly scoped Batch 1 migration task only: create migrations, models/factories, and targeted tests for Programs, Program Memberships, Program Eligibility Rules, and Rubrics, then run PostgreSQL and SQLite verification and produce Handoff 013. Do not add application/judging/evaluation workflow in that task.

## 25. Verified Facts vs Assumptions

**Verified:** D-008–D-050 are the decision authority; Batch 1 depends only on existing starter tables; no implementation/database/package/environment changes were made; migration count remains 14; the decision register, final-schema clarification, and Handoff 012 were created.

**RECOMMENDED — SAFE TECHNICAL CHOICE:** physical key/precision/index/status/JSONB/delete/idempotency conventions stated in the register.

**Assumptions avoided:** no literal role grants, public endpoint fields, owner/member approval workflow, assignment/conflict disclosure details, application question schema, or later workflow tables were created or silently decided.
