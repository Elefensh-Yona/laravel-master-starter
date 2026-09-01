# EAIC Pre-Migration Decision Register

**Project:** Ethiopian AI Center (EAIC)  
**Status:** Final decision register before the first EAIC migration batch.  
**Authority:** EAIC decisions D-008 through D-050, the final schema and acceptance contract, and the Product & Technical Controller technical direction in Task 012.

## 1. Status Legend

- **APPROVED / LOCKED:** Explicitly approved product, governance, or technical direction.
- **RECOMMENDED — SAFE TECHNICAL CHOICE:** Safe implementation convention authorized for the migration contract; it does not redefine product behavior.
- **OWNER DECISION REQUIRED:** Product/controller choice that must be confirmed before a dependent migration or workflow action.
- **DEFERRED — NOT MVP:** Intentionally excluded from the first vertical slice and its schema.

## 2. MVP Permission Catalog

**Decision:** Use the canonical singular `resource.action` MVP catalog below.  
**Status:** APPROVED / LOCKED naming convention; **RECOMMENDED — SAFE TECHNICAL CHOICE** actor capability mapping until the controller approves literal permission assignments in a seeder.  
**Rationale:** D-014 locks the naming convention. The inherited starter roles must not gain EAIC authority automatically (D-015).

| Permission | Intended capability | Scope/policy |
|---|---|---|
| `program.view`, `program.create`, `program.update`, `program.publish` | Program Staff; Super Admin under governance | Public visibility or active Program Membership; state/transition policy |
| `eligibility.view`, `eligibility.validate`, `eligibility.screen` | Program Staff | Active Program Membership, program/stage policy; screening remains human |
| `application.view`, `application.create`, `application.update`, `application.submit`, `application.revise` | Applicant; Staff view by scope | Owner/member/delegation plus program window/state; Staff only target program |
| `assignment.view`, `assignment.create`, `assignment.reassign` | Program Staff; Judge views own | Program scope, exact version, conflict precheck |
| `conflict.declare`, `conflict.view`, `conflict.determine` | Judge declares; Staff/governance determines | Assignment/application scope; confidential record policy |
| `evaluation.view`, `evaluation.create`, `evaluation.update`, `evaluation.submit`, `evaluation.finalize`, `evaluation.reopen` | Judge owns evaluation; governance reopens | Active assignment, exact version, frozen rubric, no blocking conflict, disclosure policy |
| `deliberation.view`, `deliberation.participate`, `deliberation.manage` | Staff, Decision Maker, authorized Judge | Program/stage membership and controlled disclosure |
| `decision.view`, `decision.create`, `decision.finalize`, `decision.reverse` | Decision Maker; governance reverse | Separate Decision Maker authority, deliberation prerequisite, rationale, audit |
| `audit.view` | Super Admin/approved governance | Governance policy only |

**MVP implication:** No deferred-module permissions are added. Every route/action later requires both a permission and a record-level policy.

**OWNER DECISION REQUIRED:** exact persisted role-to-permission grants. Until approved, the catalog is canonical naming, not permission seeder authorization.

## 3. Program Membership and EAIC Capability

**Decision:** Use explicit Program Membership as the primary scope relationship.  
**Status:** APPROVED / LOCKED.  
**Rationale:** D-012, D-013, and D-016 require program-specific authority and membership lifecycle.

- A User has zero or more Program Memberships; a Program has zero or more memberships.
- A user may have multiple capabilities in one Program and different capabilities in different Programs.
- Capability is represented by the domain membership, not inferred from inherited `Manager` or `Staff` roles.
- Membership statuses are `active`, `suspended`, and `ended`.
- Membership has `starts_at`; ending records `ends_at`, ending actor, and reason.
- Ended/suspended membership prevents new EAIC actions but preserves historical authorization/audit facts.

**RECOMMENDED — SAFE TECHNICAL CHOICE:** one membership row per `(program, user, capability)` with an active-row uniqueness constraint; stage scope is optional structured metadata on membership until a stage-scope model is needed.

**OWNER DECISION REQUIRED:** literal capability values, whether Stage Scope must be relational in Batch 1, and whether a capability change creates a successor membership or an audited change event.

## 4. Application Access Boundary

**Decision:** Authentication is required before application creation and any applicant record mutation.  
**Status:** RECOMMENDED — SAFE TECHNICAL CHOICE.  
**Rationale:** explicit primary ownership, member delegation, immutable versions, audit attribution, and notification delivery require a stable User identity. Public program discovery remains available through hybrid visibility; anonymous drafts would weaken ownership/audit without solving a stated MVP requirement.

- **Applicant:** create/view/edit/submit/revise only primary-owned records or approved member records with active specific delegation.
- **Program Staff:** view and operate applications only in active program scope; Staff cannot become applicant owner by broad permission.
- **Judge:** view/evaluate only explicit application-level assignment, exact submitted version, frozen rubric, and no blocking conflict.
- **Decision Maker:** view disclosed application/evaluation material and decide only in distinct active program authority.
- **Super Admin:** broad administration but no silent protected-history rewrite or confidential disclosure bypass.

**OWNER DECISION REQUIRED:** precise public Program fields. The first migration batch does not require that decision because it does not create a public endpoint.

## 5. Team and Organization Membership

**Decision:** every Application has one primary owner; Team and Organization applications may have approved members.  
**Status:** APPROVED / LOCKED.  
**Rationale:** D-017 through D-020.

- Membership is distinct from ownership.
- Primary owner may grant an approved member a specific delegated permission.
- Delegation is capability-specific, revocable, expiring, audited, and never transfers ownership.
- A member may submit or revise only when the active delegation explicitly grants that action.
- Removing a member ends membership and revokes effective delegation without deleting history.
- Ownership transfer is an explicit audited governance operation, not ordinary member administration.

**DEFERRED — NOT MVP:** invitation delivery workflow, organization verification/KYC, team hierarchy, multiple primary owners, and a formal appeals workflow for ownership disputes.

**OWNER DECISION REQUIRED:** whether initial member approval is owner-only or Staff-approved; the Batch 1 schema only needs Program Membership and does not block on this.

## 6. Application Content and Eligibility

### Application content

**Decision:** use versioned JSONB content for variable application answers; keep authoritative ownership, state, deadlines, version, and relationships relational.  
**Status:** RECOMMENDED — SAFE TECHNICAL CHOICE.  
**Rationale:** the MVP supports multiple program types without prematurely building a generic questionnaire engine. Immutable `application_versions.content` preserves exactly what was submitted.

- Relational: program, owner, applicant type, application/version status, version number, timestamps, submissions, assignments, rubric, evaluations, decisions, outcomes.
- JSONB: variable program-specific answers, structured submission payload, non-query-critical evidence metadata.
- No query-critical ID, permission, state, score, deadline, or authoritative relationship is stored only in JSONB.

**DEFERRED — NOT MVP:** dynamic form builder, question versioning UI, branching/conditional questions, reusable cross-program question bank, and generic rules engine.

### Eligibility and validation

**Decision:** Program Eligibility Rules use a small explicit rule record plus JSONB configuration; Application Validation records an immutable objective result; Screening records the human outcome.  
**Status:** RECOMMENDED — SAFE TECHNICAL CHOICE consistent with D-022/D-023.

- Eligibility rules are keyed, ordered, required/optional, typed, and program-owned.
- Validation result is `passed`, `failed`, or `error`; it never becomes the final human eligibility decision.
- Human Screening is `in_review` then `completed` with outcome `ELIGIBLE` or `INELIGIBLE`, rationale, Staff actor, and exact Application Version.

**DEFERRED — NOT MVP:** generic executable rule language, external data connectors, AI eligibility decisions, and configurable rule evaluation engine.

## 7. Withdrawal, Revision, Reopening, and Appeal

**Decision:** preserve immutable submitted versions; use controlled revisions; do not implement appeals in MVP.  
**Status:** APPROVED / LOCKED for immutable submissions/revisions; DEFERRED — NOT MVP for appeals.

- Draft withdrawal: primary owner may abandon/delete a draft only if it has no submitted/consequential dependency.
- Submitted withdrawal: primary owner may request withdrawal only before assignment; the request is recorded and requires authorized Staff transition. It never deletes submitted history.
- Applicant revision: creates a new draft successor version only when Program policy permits it; reason is required.
- Staff-requested revision: uses outcome `REVISION_REQUIRED` or controlled pre-decision revision request; creates a new version rather than reopening a submitted one.
- Reopening: applies to finalized evaluations only through governed authority; applications do not reopen a submitted version in place.
- Appeal: **DEFERRED — NOT MVP**.

**OWNER DECISION REQUIRED:** whether a submitted withdrawal after assignment is categorically prohibited or requires a governed exception. This does not block Batch 1 because no Application migration is in Batch 1.

## 8. Conflict of Interest

**Decision:** MVP conflicts are hybrid, human-determined, and blocking when status is `blocked`.  
**Status:** APPROVED / LOCKED for workflow; category values are **RECOMMENDED — SAFE TECHNICAL CHOICE**.

- System may record a signal; it cannot decide the result.
- Judge declares known/suspected conflict.
- Authorized human records determination with reason and timestamp.
- `blocked` removes Judge evaluation and restricted deliberation access.
- Judge cannot clear, waive, or override their own blocking conflict.
- Reassignment creates/ends assignment history and preserves conflict history.
- Conflict information is Program-internal/governance confidential; applicants do not see it.

**RECOMMENDED — SAFE TECHNICAL CHOICE categories:** `FINANCIAL`, `EMPLOYMENT`, `FAMILY_OR_PERSONAL`, `ADVISORY`, `COMPETITIVE`, `PRIOR_COLLABORATION`, `OTHER`.

**DEFERRED — NOT MVP:** conflict waiver. A waiver can undermine the blocking control unless detailed governance safeguards are approved.

**OWNER DECISION REQUIRED:** the specific human determining capability and whether a non-blocking determination needs a second reviewer.

## 9. Evaluation Disclosure

**Decision:** individual Judge evaluations are confidential until controlled disclosure; applicant feedback is distinct from Judge evaluation.  
**Status:** APPROVED / LOCKED.  
**Rationale:** D-026, D-031, D-034, and D-049.

| Viewer | Before controlled disclosure | After controlled disclosure / final outcome |
|---|---|---|
| Applicant | Own application/status only; no Judge identity, scores, comments, evidence, recommendation, conflict, or deliberation notes | Approved applicant feedback/outcome only; private Judge material remains confidential |
| Assigned Judge | Exact assigned version, own evaluation, frozen rubric, own conflict record | Authorized deliberation disclosure only; peer private fields remain restricted unless disclosure policy explicitly includes them |
| Other Judge | No peer application/evaluation access without separate assignment | Controlled deliberation material only if authorized participant |
| Program Staff | Program operational records; no unnecessary peer-evaluation disclosure before deliberation | Controlled disclosed material required for operations/deliberation |
| Decision Maker | Authorized evidence and only disclosed evaluation information | Decision inputs and approved governance history |
| Super Admin | Broad administration but protected-history/confidentiality policy still applies | Does not bypass disclosure policy merely by global role |

**OWNER DECISION REQUIRED:** exact fields and aggregate values disclosed during deliberation and exact applicant feedback template/content.

## 10. Judge Profile

**Decision:** do not create a separate Judge Profile table in the MVP.  
**Status:** RECOMMENDED — SAFE TECHNICAL CHOICE.  
**Rationale:** User + active Program Membership + Judge capability + application-level Judge Assignment satisfies all approved MVP authorization and evaluation requirements. No approved MVP requirement needs persistent judge bio, expertise, availability, or accreditation data.

**DEFERRED — NOT MVP:** expertise taxonomy, availability, qualification records, external profile/CV, conflict auto-matching from profile data, and Judge onboarding workflow.

## 11. Notification Contract

**Decision:** reuse existing starter database notifications. In-app is authoritative, email delivery is after commit, and email failure never removes the in-app record.  
**Status:** APPROVED / LOCKED.

| Event | Recipient | In-app record | Email |
|---|---|---|---|
| Application submitted | Primary owner and authorized delegates | Yes, after commit | Optional after commit |
| Screening completed | Primary owner; Staff as needed | Yes, redacted to applicant tier | Optional after commit |
| Revision required | Primary owner and authorized delegates | Yes | Optional after commit |
| Judge assigned/reassigned | Assigned Judge | Yes | Optional after commit |
| Conflict declared/determined/blocked | Affected Judge and authorized Staff | Yes, confidential | Optional after commit |
| Evaluation deadline reminder | Assigned Judge | Yes | Optional after commit |
| Evaluation finalized | Evaluating Judge and authorized Staff | Yes, no peer score leakage | Optional after commit |
| Deliberation opened/disclosure | Authorized participants | Yes, confidential | Optional after commit |
| Decision finalized/outcome recorded | Primary owner and authorized delegates; authorized Staff/Decision Maker | Yes, applicant data is tiered | Optional after commit |
| Program/stage transition | Primary owner and authorized members where applicable | Yes | Optional after commit |

**RECOMMENDED — SAFE TECHNICAL CHOICE:** use one domain event/idempotency identity per consequential transition so retries cannot duplicate authoritative notifications.

**OWNER DECISION REQUIRED:** email enablement by event, reminder cadence, exact recipient delegation behavior, and applicant-facing wording.

## 12. Outcome and Transition

**Decision:** Final Decision, Outcome, and Transition are separate records/concerns.  
**Status:** APPROVED / LOCKED.

- Decision Maker finalizes human Decision with rationale and one core outcome: `ACCEPTED`, `REJECTED`, `WAITLISTED`, or `REVISION_REQUIRED`.
- Outcome Transition records the resulting controlled movement; it may include an approved target Program/stage only when applicable.
- Application is never deleted due to a decision.
- Later transition/supersession preserves prior Decision and Outcome history.

**DEFERRED — NOT MVP:** incubation enrollment, mentorship assignment, milestones, resources, events, partners, alumni, and all post-decision domain modules.

**OWNER DECISION REQUIRED:** allowed target Program/stage pairs and whether `REVISION_REQUIRED` occurs before or after a formal Decision in every Program policy.

## 13. PostgreSQL Physical Conventions

| Choice | Status | Contract |
|---|---|---|
| Primary key | RECOMMENDED — SAFE TECHNICAL CHOICE | Laravel-compatible bigint identity keys, matching starter migrations |
| Timestamps | APPROVED / LOCKED | PostgreSQL `timestamptz` semantics for all lifecycle/audit timestamps |
| Timezone | APPROVED / LOCKED | Program stores IANA timezone; comparisons are performed in UTC |
| Raw score | APPROVED / LOCKED | 0–10 decimal score with two decimal places |
| Weight/contribution/total | APPROVED / LOCKED | Decimal/numeric values with two decimal calculated values; weights total 100.00 |
| Rounding | RECOMMENDED — SAFE TECHNICAL CHOICE | Half-up at final calculation, persisted to two decimal places |
| Status storage | APPROVED / LOCKED | Constrained strings + explicit transition logic; no PostgreSQL enum types |
| JSONB | APPROVED / LOCKED | Only variable metadata/content/result payloads; relational state remains relational |
| Foreign keys | RECOMMENDED — SAFE TECHNICAL CHOICE | Restrict trust-critical history; limited draft-only cascade/delete; null actor references only where audit history must survive user deletion |
| Indexes | RECOMMENDED — SAFE TECHNICAL CHOICE | Every FK plus active/current uniqueness and primary authorization lookup composites |
| Active uniqueness | RECOMMENDED — SAFE TECHNICAL CHOICE | PostgreSQL partial unique indexes for active/current rows; SQLite tests may assert equivalent behavior via application constraints |

## 14. History and Deletion

| Entity | Decision |
|---|---|
| Submitted Application Version and evidence link | Immutable; never normal update/delete; superseding version only |
| Draft Application Version | Mutable by owner/delegate; deletable only before dependency |
| Program/application memberships and delegations | End/revoke/expire, preserve record/history |
| Rubric Version/Criterion | Mutable in draft; immutable after freeze; successor version for change |
| Evaluation/Score | Mutable in draft; finalized protected; governed reopen/successor only |
| Deliberation | Mutable while open/active; closed append-only; never rewrites evaluations |
| Decision/Outcome | Draft then finalized/recorded; supersession only; application retained |
| Conflict/Assignment | Preserve signal/declaration/determination/assignment history; end or supersede, never erase |
| Notification | Content immutable after send; read state mutable |
| Activity audit | Append-only; no normal update/delete |

**Status:** APPROVED / LOCKED for preservation, no blanket soft deletion, archive/supersession, and protected history.  
**RECOMMENDED — SAFE TECHNICAL CHOICE:** use restrictive FK delete behavior and draft-only hard deletion as stated above.

## 15. Transaction and Concurrency Contract

| Operation | Required invariant |
|---|---|
| Application submission/version creation | One next version number; deadline checked in Program timezone/UTC instant; exact snapshot, current pointer, and audit commit atomically |
| Membership/delegation change | No action may authorize after membership/delegation ends/revokes/expires; history preserved atomically |
| Judge assignment/reassignment | No duplicate active Judge/version assignment; membership/capability/conflict precheck and history change commit atomically |
| Conflict determination | Effective blocking status becomes visible atomically before restricted Judge action can proceed |
| Rubric activation/freeze | Criteria complete, weights exactly 100.00, and version becomes immutable before evaluation dependency |
| Evaluation finalization | Exact assignment/version/frozen rubric/no blocked conflict rechecked; scores/contributions/total recalculated and finalization/audit commit once |
| Deliberation transition | Required final evaluations/disclosure predicates are true before entry; original evaluations remain unchanged |
| Final Decision/Outcome | One active decision/outcome context; Decision rationale/outcome, transition, in-app notification, and audit commit atomically |
| Email delivery | Queued/dispatched only after authoritative in-app notification and transaction commit |

**Status:** APPROVED / LOCKED for transactional protection and appropriate locking/optimistic checks.  
**RECOMMENDED — SAFE TECHNICAL CHOICE:** use row locking for one-row transition aggregates and partial unique indexes/idempotency identities to protect retry paths.

## 16. First Migration Batch

**Decision:** Batch 1 is intentionally limited to:

1. `programs`
2. `program_memberships`
3. `program_eligibility_rules`
4. `rubrics`

**Status:** APPROVED / LOCKED as the safest first batch.  
**Rationale:** It establishes the multi-program boundary, program-scoped authority, program-specific eligibility configuration, and rubric identity without prematurely creating application, judging, evaluation, deliberation, or outcome workflows.

### Batch 1 constraints

- Reuse existing `users`, Spatie tables, `settings`, `activity_logs`, `media`, and notifications; create no duplicates.
- `programs` stores name/code/slug/status/timezone/open/close dates and lifecycle audit references.
- `program_memberships` stores explicit program scope, capability, lifecycle status, effective dates, and ending history.
- `program_eligibility_rules` stores only small explicit keyed/type/configuration rules; no generic rules engine.
- `rubrics` stores only Program-owned rubric identity/status. `rubric_versions` and criteria are Batch 2 because the first batch must not freeze/evaluate content before application/evaluation prerequisites exist.
- No new domain roles/permissions are seeded in Batch 1 unless a separately approved task authorizes the literal role-to-permission grants.

**Batch 1 acceptance gate:** focused migrations run against PostgreSQL `development` and SQLite test configuration; FK/unique/check behavior is covered by targeted tests; no application/evaluation workflow is added.

## 17. Remaining Migration Gate

Batch 1 may proceed after the controller accepts this register. Later batches require their dependent owner decisions to be resolved or explicitly deferred. In particular, Batch 2 onward must not invent application public access, member approval/invitation, literal permission grants, assignment precedence, conflict determination authority, disclosure field policy, or notification wording/cadence.
