# AI Agent Handoff 011: EAIC Final Schema and Acceptance Contract Summary

## 1. Interaction ID

`011`

## 2. Task Requested

Create the final pre-migration EAIC MVP schema and acceptance contract from decisions D-008 through D-050 and reconciled specifications. This was specification-only work; no implementation, database, package, or environment change was authorized.

## 3. Sources Read

- `TheRoadmap/decisions.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md`
- `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-CONTRACT.md`
- `EAIC-MVP-RBAC-SCOPE-MATRIX.md`
- `EAIC-MVP-DATABASE-LIFECYCLE-SPECIFICATION.md`
- Existing starter migrations for users, activity logs, and media.
- Existing PostgreSQL baseline metadata.
- Required project documentation and handoffs 001–010.

## 4. Approved Decisions Used

D-008 through D-050 were used. Key applied decisions include EAIC identity, multi-program operation, distinct Decision Maker authority, layered program scope, singular permission naming, explicit membership/ownership/delegation, immutable versions, human screening, assignment/conflict control, frozen rubrics, 0–10 weighted scoring to 100 points, qualitative judgment separation, protected finalization, structured deliberation, human score-independent decisions, controlled outcomes, tiered transparency, event-driven notifications, timezone-aware deadlines, append-only audit, governed Super Admin boundaries, AI advisory limits, PostgreSQL-first design, explicit state machines, evaluation confidentiality, and controlled post-decision lifecycle.

## 5. Files Created

- `EAIC-MVP-FINAL-SCHEMA-AND-ACCEPTANCE-CONTRACT.md`
- `AI-AGENT-HANDOFFS/011-eaic-final-schema-acceptance-contract-summary.md`

## 6. Files Modified

No existing files were modified in this interaction.

## 7. Files Not Modified

- Handoffs 001–010.
- Historical decision record, including D-001–D-007 and EAIC D-008–D-050.
- Existing EAIC contracts/specifications.
- Application code, tests, migrations, models, controllers, routes, policies, services, frontend, seeders, package manifests, lockfiles, `.env`, and generated artifacts.
- PostgreSQL and SQLite structures/data.

## 8. Final Entity Inventory

New EAIC MVP tables specified:

- `programs`
- `program_memberships`
- `program_eligibility_rules`
- `applications`
- `application_members`
- `application_member_delegations`
- `application_versions`
- `application_version_media`
- `application_validations`
- `screenings`
- `judge_assignments`
- `conflicts`
- `rubrics`
- `rubric_versions`
- `rubric_criteria`
- `evaluations`
- `evaluation_criterion_scores`
- `deliberations`
- `deliberation_participants`
- `deliberation_disclosures`
- `decisions`
- `outcome_transitions`

Reused Master Starter tables: `users`, Spatie role/permission tables, `media`, `notifications`, `activity_logs`, and `settings`.

## 9. Relationship and Cardinality Summary

The final contract defines exact ownership/cardinality: one Program owns many memberships, applications, eligibility rules, and rubrics; every Application belongs to one Program and exactly one primary owner; Team/Organization applications have approved members and bounded delegations; Applications have one or more versions; assignments/evaluations reference exact application versions; evaluations reference exact frozen rubric versions and one score per criterion; deliberations, decisions, and transitions retain historical rows with at most one active context per specified aggregate.

## 10. State-Machine Summary

Exact constrained values and transition tables are specified for Program, Membership, Application, Version, Validation, Screening, Judge Assignment, Conflict, Rubric, Rubric Version, Evaluation, Deliberation, Decision, and Outcome Transition. All transitions require actor authority, singular permission, scope, prerequisites, conflict/governance checks, audit, and explicit irreversible/supersession behavior. Direct status manipulation is prohibited.

Approved core outcomes: `ACCEPTED`, `REJECTED`, `WAITLISTED`, `REVISION_REQUIRED`.

## 11. Versioning Summary

- Version 1 begins as an application draft.
- Versions are application-local, positive, contiguous, monotonic, and unique.
- Submission freezes content/evidence and preserves submission actor/time.
- Revision creates a successor draft with reason; it never modifies submitted content.
- Owner delegation is capability-specific, active only while unrevoked/unexpired, audited, and non-ownership.
- Judges remain tied to the assignment’s exact submitted application version; later versions never retarget prior evaluations.

## 12. Evaluation and Scoring Summary

The contract keeps numerical score, qualitative human assessment, Judge recommendation, and final human decision separate.

Formula:

$$
contribution_i = score_i \times \frac{weight_i}{100}
$$

$$
score\_out\_of\_100 = \left(\sum_i contribution_i\right) \times 10
$$

Scores are 0–10, weights total 100%, and server calculation creates a normalized 0–100 total. The proposed technical representation is `numeric(4,2)` raw score and `numeric(5,2)` weight/contribution/total, with half-up two-decimal calculation. Client totals are ignored/rejected and finalization recalculates transactionally.

## 13. Conflict Summary

Conflict records support system detection, Judge declaration, controlled human determination, blocking restriction, reassignment, confidentiality, audit, and successor history. A `blocked` conflict denies restricted Judge evaluation/deliberation actions. Category/waiver semantics remain owner decisions; recommended categories are clearly labeled non-approved.

## 14. RBAC and Policy Summary

Every sensitive action requires: authenticated user, active Program Membership, EAIC capability, optional stage scope, singular permission, assignment/ownership/delegation, record policy, conflict state, and governance check. Super Admin retains broad administration but cannot directly rewrite protected history. Decision Maker authority remains separate. Judge peer evaluation disclosure is controlled; Applicant access is limited to owned/member records and approved feedback.

## 15. Immutability and Delete Summary

Submitted versions/evidence links, frozen rubric versions/criteria, finalized evaluations, closed deliberations, finalized decisions, outcomes, conflict determinations, assignments, membership changes, notifications, and audit events have explicit protection. The contract recommends draft-only deletion where safe and archive/supersession/restrict behavior for consequential history. Blanket soft deletion is prohibited.

## 16. Constraint and Index Summary

The contract specifies primary keys, foreign keys, unique/partial unique constraints, indexes, checks, and temporal constraints for memberships, members, delegations, versions, assignments, conflicts, rubrics, criteria, evaluations, criterion scores, deliberations, decisions, and outcomes. It includes concurrency-sensitive uniqueness and transaction rules for submission/revision, assignment, finalization, decision/outcome, and retried notification/audit events.

## 17. PostgreSQL Summary

PostgreSQL-first guidance specifies `timestamptz`, IANA program timezone, numeric scoring, limited JSONB, relational state, restrictive history FKs, partial unique indexes, transactions, locks/optimistic checks, and after-commit email delivery. Key size/column precision/status strings/index mechanics are labeled `RECOMMENDED — NOT YET APPROVED` where they are technical implementation choices rather than explicit product decisions.

## 18. Migration Dependency Order

1. Existing starter foundation.
2. Programs, memberships, eligibility rules, rubrics.
3. Rubric versions and criteria.
4. Applications, members, versions, delegation, evidence association.
5. Validation, screening, assignments, conflicts.
6. Evaluations and criterion scores.
7. Deliberation, decision, and outcomes.

The contract identifies the deferred `applications.current_version_id` FK and avoids duplicate notification/audit migrations.

## 19. Acceptance-Test Summary

32 implementation-ready specification cases cover FK integrity, scope, delegation, immutable versions/evidence, exact evaluation version, deadline races, human screening, assignment/conflict blocking, rubric/score constraints, deterministic calculation, confidentiality, finalization/reopen, deliberation, Decision Maker authority, outcome validity, after-commit notifications, email resilience, append-only audit, governance overrides, PostgreSQL migration, and SQLite compatibility.

No tests were implemented or run in this documentation-only interaction.

## 20. OWNER DECISION REQUIRED

- Literal permission catalog and actor-to-permission assignment.
- Capability values and membership/stage-scope cardinality.
- Public fields and application authentication boundary.
- Team lead/invitation/submission-on-behalf details.
- Exact withdrawal/appeal/reopen transition rules beyond the approved state contract.
- Conflict categories, indirect-affiliation, waiver, determination authority, and disclosure.
- Application content/question schema and eligibility rule types.
- Field-level evaluation/disclosure policy.
- Notification catalog, recipients, timing, email and retry policy.
- Exact configured transition targets/additional outcome metadata.
- Separate Judge profile need.

## 21. RECOMMENDED — NOT YET APPROVED

- Bigint keys matching the starter.
- Exact new table and column names in the contract.
- Application-level assignment implementation.
- Partial unique indexes for active/current records.
- Numeric precision/half-up rounding values.
- IANA timezone storage + `timestamptz`.
- JSONB metadata boundaries.
- Constrained strings rather than enums.
- Restrictive deletion/archive/supersession design.
- Domain event/idempotency identity for retry paths.

## 22. Verification Performed

- Read and applied authoritative decisions D-008–D-050 and reconciled EAIC specifications.
- Inspected current starter migrations and PostgreSQL baseline read-only.
- Confirmed PostgreSQL `development` contains only 20 starter tables and 14 applied starter migrations.
- Confirmed no EAIC migration/code artifact was created.
- Confirmed migration count remains 14.
- Confirmed no package, `.env`, or database changes occurred.
- Checked Git status and diff scope.

## 23. Test/Check Results

No executable application tests were run because no executable code changed. Focused repository, PostgreSQL metadata, migration-count, and documentation consistency checks completed. No check failure was encountered.

## 24. Database Changes

None. The PostgreSQL baseline was inspected read-only. No migration, schema, data, role, permission, or destructive operation was performed.

## 25. Git Status

- Branch: `main`, tracking `upstream/main`.
- Existing tracked modifications remain `.env.example` (approved rename) and `TheRoadmap/decisions.md` (controller-added EAIC decisions).
- EAIC contract/specification and handoff files are untracked in the current worktree.
- No commit was created.

## 26. Known Risks

- Remaining owner decisions directly affect migration detail and must be resolved or explicitly deferred before migration implementation.
- The contract is pre-migration documentation; table/constraint behavior is not executable evidence yet.
- The original PDF blueprint is still not present locally; decisions.md is the authoritative decision record used.

## 27. Recommended Next Task

Stop for Product & Technical Controller review. The next task should explicitly resolve/defer the remaining migration-blocking owner decisions and authorize a narrowly scoped first migration batch with targeted Pest tests. Do not begin migration implementation from this handoff alone.

## 28. Verified Facts vs Assumptions

**Verified:** final schema/acceptance contract and Handoff 011 were created; D-008–D-050 are the applied authority; existing PostgreSQL baseline is reachable with 20 starter tables and 14 applied migrations; no EAIC implementation artifacts, package changes, environment changes, or database changes occurred.

**RECOMMENDED — NOT YET APPROVED:** exact physical keys/table/column names, partial indexes, numeric representation/rounding, enum strategy, assignment implementation, deletion mechanics, and idempotency implementation.

**Assumptions avoided:** no migration/model/controller/policy/route/service/role/permission was created; no unresolved product policy was silently decided; no historical record was rewritten.
