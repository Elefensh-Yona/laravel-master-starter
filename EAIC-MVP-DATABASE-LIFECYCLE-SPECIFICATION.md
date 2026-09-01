# EAIC MVP Database and Lifecycle Specification

**Project:** Ethiopian AI Center (EAIC)  
**Status:** Migration-ready specification reconciled with EAIC decisions D-008 through D-050. No migrations or database changes are included.  
**Authority:** The approved EAIC Blueprint, reconciled EAIC Contract, and MVP RBAC + Scope Matrix govern domain behavior. The Laravel Master Starter governs inherited tables and infrastructure.

> **Scope note:** This document specifies the smallest approved MVP path. Mechanical recommendations are marked **RECOMMENDED**; unresolved business choices remain **OWNER DECISION REQUIRED**. The repository currently contains only the Master Starter schema, not these EAIC tables.

## 1. MVP Domain Boundary

```text
Program
→ Membership
→ Application + Members
→ Application Versions
→ Eligibility / Automated Validation
→ Staff Screening
→ Judge Assignment
→ Conflict of Interest
→ Rubric Version + Criteria
→ Independent Evaluation + Scores
→ Controlled Disclosure
→ Deliberation
→ Decision
→ Outcome / Transition
→ Existing Notifications + Activity Audit
```

Deferred: incubation, mentorship, milestones, resources/workspaces, events/training/showcase, partners/vendors, alumni/follow-up, broad AI assistants, and autonomous decisions.

## 2. Reused Master Starter Tables

EAIC must reuse, not duplicate:

- `users`: identity, authentication, actor account, and all user foreign keys.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: inherited Spatie infrastructure; EAIC domain authorization is layered on top.
- `media`: evidence/file storage when its polymorphic attachment and access policy satisfy the approved visibility tier.
- `notifications`: authoritative in-app notification record; email remains a delivery channel.
- `activity_logs`: existing audit foundation, supplemented by immutable domain history where required.
- `settings`: shared configuration only; program data is not stored as global settings.

No new notification or generic audit table is required by this specification. A dedicated domain history table is only justified where an entity's prior state/version must be reconstructable and cannot be represented safely by `activity_logs` alone.

## 3. Entity Inventory and Authoritative Relationships

### 3.1 Program

**Purpose:** configurable EAIC program/challenge and lifecycle container.  
**Ownership:** organization/system; operationally managed by authorized Program Staff.  
**Relationships:** has memberships, eligibility rules, applications, assignments, rubrics, deliberations, decisions, outcomes/transitions.  
**History:** publication, deadline, configuration, and governed changes require audit/history.  
**Immutability:** published/closed program fields that affect eligibility, submissions, judging, or interpretation must not be silently changed.

### 3.2 Program Membership

**Purpose:** binds a `User` to a `Program` with an EAIC capability/role and optional stage scope. Explicit program membership is the primary program-scope mechanism and has lifecycle status (D-016).  
**Ownership:** program administration.  
**Relationships:** belongs to `users` and `programs`; may authorize staff, judges, or Decision Makers.  
**History:** membership grant, scope change, suspension, and removal require audit.  
**Immutability:** historical membership actions are append-only in audit; current membership may be ended, not rewritten. Removing membership prevents new actions while preserving historical actions (D-016).

### 3.3 Application

**Purpose:** an Individual, Team, or Organization's participation in one Program.  
**Ownership:** primary applicant owner plus approved members.  
**Relationships:** belongs to `programs`; references primary owner `users`; has members, versions, screening records, assignments, conflicts, evaluations, deliberations, decisions, and outcomes.  
**History:** lifecycle and ownership/member changes require history.  
**Immutability:** submitted business content is held in versions; the application aggregate may retain current status pointers but must not rewrite submitted history.

### 3.4 Application Member

**Purpose:** grants an application member a defined relationship to an application, with only explicitly permitted capabilities. Team and Organization applications support multiple approved members (D-019).  
**Ownership:** primary owner/program policy.  
**Relationships:** belongs to `applications` and `users`.  
**History:** additions, removals, and authority changes require audit.  
**Immutability:** past membership events are historical; current rows may be ended, not retroactively rewritten. Owner delegation is capability-specific, revocable, expiring, and audited; it never grants unrestricted ownership (D-020).

### 3.5 Application Version

**Purpose:** draft or submitted snapshot of application content/evidence. Applicant types are Individual, Team, and Organization (D-017).  
**Ownership:** application primary owner/members according to policy.  
**Relationships:** belongs to `applications`; may reference `media`; is referenced exactly by evaluations.  
**History:** every version and submission/revision event is preserved.  
**Immutability:** submitted versions are immutable forever; judging references the exact submitted version (D-021).

### 3.6 Screening

**Purpose:** automated validation and human Program Staff screening record.  
**Ownership:** authorized Program Staff for the Program.  
**Relationships:** belongs to application and program; records actor; may reference version/evidence.  
**History:** results, reasons, and re-review events are preserved.  
**Immutability:** completed human screening results are not silently edited; correction uses a new/reopened governed record.

### 3.7 Judge Assignment

**Purpose:** grants a Judge access to an approved program/application/stage evaluation scope.  
**Ownership:** authorized Program Staff.  
**Relationships:** belongs to program, application, judge `users`, and optionally stage; has conflict checks and evaluation relation.  
**History:** assignment, decline, removal, reassignment, and reason are preserved.  
**Immutability:** past assignment events are append-only; a new assignment/reassignment does not rewrite the old one.

### 3.8 Conflict of Interest

**Purpose:** records detected signal, Judge declaration, human determination, and restriction.  
**Ownership:** affected Judge declares; authorized human actor determines.  
**Relationships:** links application, program, Judge, and relevant assignment.  
**History:** detection, declaration, determination, reason, and restriction history are mandatory.  
**Immutability:** determinations are historical; current status may be superseded only through governed action.

### 3.9 Rubric

**Purpose:** rubric identity/configuration for a program/stage.  
**Ownership:** authorized program governance.  
**Relationships:** belongs to program; has rubric versions.  
**History:** version activation, freeze, retirement, and exceptions are audited.  
**Immutability:** rubric identity/configuration that governs an existing version cannot silently change.

### 3.10 Rubric Version

**Purpose:** immutable criteria/weight set used by evaluations.  
**Ownership:** program governance.  
**Relationships:** belongs to rubric; has criteria; is referenced by evaluations.  
**History:** activation/freeze/retirement preserved.  
**Immutability:** frozen before any evaluation depends on it; criteria and weights cannot be silently changed.

### 3.11 Rubric Criterion

**Purpose:** one weighted scoring criterion in a rubric version.  
**Ownership:** rubric version.  
**Relationships:** belongs to rubric version; has evaluation criterion scores.  
**History:** frozen with the rubric version.  
**Immutability:** criterion label, weight, ordering, and scoring meaning are immutable once frozen.

### 3.12 Evaluation

**Purpose:** one Judge's independent evaluation of one exact application version using one exact rubric version.  
**Ownership:** evaluating Judge; governed by the program.  
**Relationships:** belongs to application, application version, program, Judge, and rubric version; has criterion scores; may be disclosed to authorized deliberation participants.  
**History:** draft/submission/finalization/reopen history is mandatory.  
**Immutability:** finalized evaluation is protected; after deliberation starts, mutation requires governed reopening and preserves the original.

### 3.13 Evaluation Criterion Score

**Purpose:** raw 0–10 score, justification/evidence, and deterministic contribution for one criterion.  
**Ownership:** parent evaluation/Judge.  
**Relationships:** belongs to evaluation and rubric criterion.  
**History:** final values are preserved with the evaluation version.  
**Immutability:** finalized score rows cannot be silently updated.

### 3.14 Deliberation

**Purpose:** controlled, structured human review after independent evaluations and disclosure.  
**Ownership:** authorized Program Staff/Decision Maker governance.  
**Relationships:** belongs to program/application; references disclosed evaluations, participants, and decision.  
**History:** opening, disclosure, participant actions, notes, closure, and decision linkage are preserved.  
**Immutability:** closed deliberation history is append-only; corrections use governed additions. Deliberation does not rewrite individual evaluations (D-031, D-046).

### 3.15 Decision

**Purpose:** formal human outcome record created/finalized by the Decision Maker; it is evidence-informed and not mechanically derived from score.  
**Ownership:** authorized Decision Maker in program scope.  
**Relationships:** belongs to application/program/deliberation; records actor and rationale; has outcome/transition.  
**History:** creation, finalization, reversal/supersession, and governance override are preserved.  
**Immutability:** finalized decisions cannot be silently edited.

### 3.16 Outcome / Transition

**Purpose:** explicit `ACCEPTED`, `REJECTED`, `WAITLISTED`, or `REVISION_REQUIRED` result and controlled movement to a next stage/program or terminal result.  
**Ownership:** decision governance.  
**Relationships:** belongs to application and decision; optionally references next program/stage.  
**History:** transition and reversal are audited.  
**Immutability:** finalized outcome history is append-only; a new governed transition supersedes rather than rewrites it.

### 3.17 Notification

**Purpose:** authoritative in-app communication of approved lifecycle events.  
**Ownership:** recipient/user and system event.  
**Relationships:** existing `notifications` belongs polymorphically to recipient; optional action target is represented in notification data only where safe.  
**History:** existing notification timestamps/read state are retained.  
**Immutability:** sent notification content is not silently rewritten; corrections use a new notification.

### 3.18 Audit Event

**Purpose:** reconstruct consequential actions.  
**Ownership:** system audit foundation with actor, target, action, reason, timestamp, and context.  
**Relationships:** reuse `activity_logs`; subject/causer polymorphism and JSON properties link domain records.  
**History:** append-only.  
**Immutability:** audit events are never updated or deleted through normal domain operations.

## 4. Relationship Model

```text
User
 ├── Program Membership ──> Program
 ├── owns/member-of ──────> Application
 ├── Judge Assignment ────> Application + Program (+ Stage)
 ├── Conflict Declaration -> Application + Judge Assignment
 ├── Evaluation ──────────> Application Version + Rubric Version
 ├── Decision Maker ───────> Program Membership/Capability
 └── Activity Log / Notification

Program
 ├── memberships
 ├── applications
 ├── eligibility rules
 ├── rubrics → rubric versions → criteria
 ├── judge assignments
 ├── deliberations
 ├── decisions
 └── outcomes/transitions

Application
 ├── members
 ├── versions
 ├── screenings
 ├── assignments
 ├── conflicts
 ├── evaluations
 ├── deliberations
 ├── decision
 └── outcome/transition

Evaluation
 └── criterion scores → rubric version criteria
```

**RECOMMENDED:** keep program/application foreign keys on operational records even where they are derivable. They provide direct policy predicates, reduce ambiguous joins, and support integrity checks. Enforce consistency between redundant keys transactionally/application-side and with database constraints where practical.

## 5. Lifecycle State Machines

The state labels below are the **RECOMMENDED MVP vocabulary** needed to implement the approved directions. They are not additional product decisions. The controller must approve literal names before migrations if they differ from the approved decision record.

### 5.1 Program

| Current | Requested transition | Result | Authorized actor | Permission | Scope/prerequisites/blockers | Audit |
|---|---|---|---|---|---|---|
| `draft` | configure | `draft` | Program Staff | `program.update` | Program membership + capability; no published dependent records | Yes for consequential changes |
| `draft` | publish/open | `published` | Program Staff/approved admin | `program.publish` | Required configuration, rubric/eligibility readiness, valid timezone/deadline; governance policy | Yes |
| `published` | close | `closed` | Program Staff/approved admin | `program.update` | Deadline/approved closure rule; no unapproved active workflow bypass | Yes |
| `closed` | archive | `archived` | Program Staff/approved admin | `program.update` | No prohibited pending operation; retains all history | Yes |

No actor may assign arbitrary status strings or skip transition validation.

### 5.2 Application

| Current | Requested transition | Result | Authorized actor | Permission | Scope/prerequisites/blockers | Audit |
|---|---|---|---|---|---|---|
| none | create | `draft` | Applicant | `application.create` | Program visible/open; applicant type permitted; ownership established | Yes |
| `draft` | submit | `submitted` | Primary owner/approved member | `application.submit` | Valid current version; strict timezone-aware deadline not passed; no blocking validation; transaction lock | Yes |
| `submitted` | screen | `screening` | Program Staff | `eligibility.screen` | Submitted exact version exists; program scope | Yes |
| `screening` | eligible | `eligible` | Program Staff | `eligibility.screen` | Human screening completed; objective validation available | Yes |
| `screening` | ineligible | `ineligible` | Program Staff | `eligibility.screen` | Human rationale required | Yes |
| `eligible` | assign | `assigned` | Program Staff | `assignment.create` | Judge assignment and conflict precheck pass | Yes |
| `assigned` | evaluate | `under_evaluation` | Assigned Judge/Staff workflow | `evaluation.create` | Assignment, exact version, frozen rubric, no blocking conflict | Yes |
| `under_evaluation` | evaluation complete | `evaluated` | Judge workflow | `evaluation.finalize` | Required assigned evaluations finalized | Yes |
| `evaluated` | disclose/deliberate | `in_deliberation` | Program Staff/Decision Maker | `deliberation.manage` | Disclosure prerequisites satisfied | Yes |
| `in_deliberation` | decide | `decided` | Decision Maker | `decision.finalize` | Human rationale and authorized deliberation | Yes |
| `decided` | outcome | `outcomed` | Decision Maker/approved workflow | `decision.finalize` | Explicit outcome and controlled transition | Yes |

`ACCEPTED`, `REJECTED`, `WAITLISTED`, and `REVISION_REQUIRED` are approved outcome values (D-050). `withdrawn`, `reopened`, and any additional state values remain **OWNER DECISION REQUIRED**.

### 5.3 Application Version

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | create | `draft` | Applicant/approved member | `application.create`/`application.revise` | Application ownership/member policy | Yes |
| `draft` | submit | `submitted` | Primary owner/approved submitter | `application.submit` | Valid content, open window, strict deadline | Yes |
| `submitted` | revise when allowed | prior remains `submitted`; new version `draft` | Applicant/approved submitter | `application.revise` | Approved revision window/state; prior version immutable | Yes |
| `submitted` | lock for judging | `submitted` remains immutable | System workflow | none | Exact version captured by assignment/evaluation | Yes |

Version numbers are positive integers unique per application and monotonically increasing. The current/active version pointer may move only through a controlled revision/submission operation. Evaluations store the exact `application_version_id`; they never follow the current pointer.

### 5.4 Screening

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | automated validation | `validated`/`validation_failed` result | System/authorized Staff | `eligibility.validate` | Objective rules available; no final human outcome | Yes |
| none | start human screen | `in_review` | Program Staff | `eligibility.screen` | Program scope + submitted version | Yes |
| `in_review` | complete eligible | `completed` with eligible result | Program Staff | `eligibility.screen` | Rationale required | Yes |
| `in_review` | complete ineligible | `completed` with ineligible result | Program Staff | `eligibility.screen` | Rationale required; AI cannot finalize | Yes |
| `completed` | governed review | new screening record/review | Authorized Staff | `eligibility.screen` | Reason and approval | Yes |

### 5.5 Judge Assignment

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | assign | `active` | Program Staff | `assignment.create` | Target eligible; Judge membership/capability; conflict precheck | Yes |
| `active` | Judge declines | `declined` | Assigned Judge | assignment action approved by controller | Reason; no evaluation access | Yes |
| `active` | conflict/reassignment | `reassigned`/ended + new `active` record | Program Staff | `assignment.reassign` | Conflict-aware; new Judge passes precheck | Yes |
| `active` | remove | `removed` | Program Staff | `assignment.reassign` | Reason; preserve history | Yes |
| `active` | complete | `completed` | Workflow/authorized Staff | `assignment.view` or approved completion action | Evaluation/assignment conditions satisfied | Yes |

Exact assignment scope/precedence remains **OWNER DECISION REQUIRED**. **RECOMMENDED — NOT YET APPROVED:** use application-level assignment for the MVP because it is the least ambiguous enforcement target.

### 5.6 Conflict

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | system signal | `further_review` | System | `conflict.view`/workflow | Signal only; no automatic resolution | Yes |
| none | Judge declaration | `declared` | Judge | `conflict.declare` | Relevant assignment/application | Yes |
| `declared`/`further_review` | determine no conflict | `cleared` | Authorized human determiner | `conflict.determine` | Evidence/reason required | Yes |
| `declared`/`further_review` | determine blocking | `blocked` | Authorized human determiner | `conflict.determine` | Reason required; Judge restricted | Yes |
| `blocked` | reassign | assignment ended/new assignment | Program Staff | `assignment.reassign` | New Judge precheck | Yes |
| `declared`/`further_review` | determine non-blocking | `non_blocking` | Authorized human determiner | `conflict.determine` | Reason required; policy permits continued access | Yes |

A blocking conflict denies restricted evaluation, score, and deliberation actions until the restriction is governed away or the assignment is removed. AI cannot determine conflict status.

### 5.7 Rubric and Rubric Version

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | create | Rubric `draft`, version `draft` | Program Staff/governance | `program.update` or approved rubric action | Program scope | Yes |
| `draft` | activate | version `active` | Authorized program governance | approved rubric action | Criteria complete; weights valid | Yes |
| `active` | freeze | version `frozen` | Authorized governance workflow | approved rubric action | Before evaluations depend on it | Yes |
| `frozen` | retire | `retired` | Authorized governance | approved rubric action | Preserve all dependent evaluations | Yes |
| frozen/retired | exceptional change | new version | Governance override | explicit override | Never rewrite dependent version | Yes |

The exact literal rubric permission names are controlled by the RBAC approval; no frozen version may be updated in place.

### 5.8 Evaluation

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | create | `draft` | Assigned Judge | `evaluation.create` | Membership, Judge capability, assignment, exact submitted version, frozen rubric, no blocking conflict | Yes |
| `draft` | submit | `submitted` | Assigned Judge | `evaluation.submit` | All required scores/notes/recommendation present | Yes |
| `submitted` | finalize | `finalized` | Assigned Judge/approved authority | `evaluation.finalize` | Mathematical total recalculated and persisted; no blocking conflict | Yes |
| `finalized` | governed reopen | `reopened` | Approved governance authority | `evaluation.reopen` | Reason, actor, audit; stronger restrictions after deliberation | Yes |
| `reopened` | resubmit/finalize | new protected revision | Assigned Judge/approved authority | approved evaluation actions | Original remains historical | Yes |

### 5.9 Deliberation

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | open | `open` | Program Staff/Decision Maker | `deliberation.manage` | Required evaluations finalized; disclosure policy satisfied | Yes |
| `open` | structured discussion | `active` | Authorized participants | `deliberation.participate` | Participant scope and confidential disclosure | Yes |
| `active` | close | `closed` | Program Staff/Decision Maker | `deliberation.manage` | Required deliberation conditions; decision may follow | Yes |

Original evaluations remain unchanged historical records throughout deliberation.

### 5.10 Decision

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | create | `draft` | Decision Maker | `decision.create` | Authorized program/application; deliberation prerequisites | Yes |
| `draft` | finalize | `finalized` | Decision Maker | `decision.finalize` | Explicit outcome and rationale; human authority; no automatic score decision | Yes |
| `finalized` | governed reverse/supersede | new decision | Governance authority | `decision.reverse` | Reason, authority, preserved prior decision, audit | Yes |

### 5.11 Outcome / Transition

| Current | Requested transition | Result | Authorized actor | Permission | Blockers | Audit |
|---|---|---|---|---|---|---|
| none | record outcome | `recorded` | Decision Maker/approved workflow | `decision.finalize` | Final human decision and explicit outcome | Yes |
| `recorded` | move next stage/program | `transitioned` | Approved workflow authority | approved decision action | Target exists; policy permits | Yes |
| `recorded` | governed change | new transition | Governance authority | `decision.reverse` | Preserve prior outcome and reason | Yes |

## 6. Application Versioning Rules

1. Every application has version `1` as its first draft/submission sequence.
2. Version numbers are positive, contiguous application-local integers; database uniqueness is `(application_id, version_number)`.
3. A draft may be edited only by an authorized owner/member before the permitted lock.
4. Submission changes the draft version to immutable `submitted`.
5. A permitted revision creates the next version; it never edits the prior submitted version.
6. The application stores a controlled current/active version reference for workflow convenience.
7. A Judge evaluation stores the exact submitted version ID and exact rubric version ID.
8. A later revision does not move an existing evaluation to the new version. It requires an explicitly governed evaluation/reassignment policy.
9. Previous versions remain readable only to authorized users; immutable does not mean public.
10. After strict timezone-aware deadline closure, submission/revision is denied except for a governed exception with actor, reason, timestamp, and audit.
11. A submitted version cannot be deleted through ordinary application operations.

## 7. Evaluation Data Model and Scoring Mathematics

### Required conceptual fields

| Concept | Storage rule |
|---|---|
| Rubric version | Evaluation references immutable `rubric_version_id` |
| Criterion | Score references exact frozen `rubric_criterion_id` |
| Weight | Store a non-negative numeric weight on the criterion; total must equal 100 |
| Raw score | Store numeric score in `[0, 10]` inclusive |
| Calculated contribution | Persist system-calculated normalized contribution for reproducibility |
| Calculated total | Persist system-calculated normalized 100-point total |
| Qualitative assessment | Separate text/structured field, never combined with numeric score |
| Criterion justification/evidence | Required per criterion; separate from score and qualitative assessment |
| Judge recommendation | Separate controlled value/text, never substituted for total |
| Status/finalization | Status plus finalized actor/time |
| Reopening | Separate governed event/history with reason/actor/time |
| Revision history | Append-only evaluation revision/finalization/reopen records |

### Approved scoring model

- Raw criterion score: numeric `0` through `10`, inclusive.
- Weight representation: **RECOMMENDED** `numeric(7,4)` percentage points, non-negative, with active rubric-version sum exactly `100.0000`.
- Normalized criterion contribution:

$$
contribution_i = \frac{score_i}{10} \times weight_i
$$

- Total normalized result:

$$
total = \sum_{i=1}^{n} contribution_i
$$

- Stored score/contribution/total precision: **RECOMMENDED** `numeric(12,4)` for raw/contribution and total; display rounded to two decimal places using half-up rounding.
- The system recalculates all contributions and total from the immutable rubric criteria and raw scores on draft save, submission, finalization, and approved reopen.
- Persist calculated values for query/report performance, but treat raw scores plus exact rubric-version criteria/weights as the source needed to reproduce them.
- At finalization, recalculate inside the same transaction and reject client-supplied calculated totals.
- Judges may not manually override total or contribution values.
- An evaluation's historical calculation remains reproducible because its rubric version, criterion weights, raw scores, calculation precision/version, and finalization event are preserved.

The exact score scale and rounding/precision values are not stated in the available decision record beyond the approved 0–10 plus normalized 100-point model. The numeric precision above is **RECOMMENDED** technical policy and requires controller acceptance before migration.

### Evaluation integrity

- One active evaluation per `(application_version_id, judge_user_id, rubric_version_id)`; **RECOMMENDED** database uniqueness may use the immutable evaluation identity plus controlled revision model rather than allowing duplicate active rows.
- Every required rubric criterion has exactly one score per evaluation.
- Finalized evaluation includes every criterion score, justification/evidence, qualitative assessment, and recommendation required by the approved rubric.
- Final decision is separate from score, assessment, and recommendation.

## 8. Conflict Data Model

### Required data

- Program, application, Judge user, and relevant assignment references.
- Category/type: **OWNER DECISION REQUIRED**; the blueprint approves detection/declaration/determination but does not enumerate categories.
- Source: system signal, Judge declaration, or other approved source.
- Explanation/declaration text.
- Determination status: `further_review`, `declared`, `cleared`, `non_blocking`, or `blocked` as **RECOMMENDED** vocabulary.
- Determining actor, determination reason, determined timestamp.
- Declaration timestamp and detection timestamp where applicable.
- Restriction effective timestamp and resolution/supersession reference.

### Behavior

- System detection creates a reviewable signal and never autonomously clears or blocks a conflict.
- Judge declaration is auditable and immediately subject to the approved restriction policy.
- Controlled human determination sets the effective restriction.
- `blocked` denies evaluation creation/update/submission/finalization and restricted deliberation participation.
- A blocked assignment is removed/reassigned through controlled, conflict-aware history.
- Conflict records are not deleted to hide a prior declaration or determination.

## 9. RBAC and Scope Data Dependencies

The later schema must support the following predicates without relying on UI visibility:

| Authorization need | Required data relationship |
|---|---|
| Program Staff scope | `program_memberships(user_id, program_id, capability/role, stage scope, active period)` |
| Judge scope | Program membership + Judge capability + `judge_assignments(judge_id, program_id, application_id/stage)` |
| Applicant ownership | `applications(primary_owner_id, program_id)` |
| Application membership | `application_members(application_id, user_id, member role, active period)` |
| Decision Maker authority | Program membership/capability specifically authorizing decision actions |
| Conflict restriction | Current/effective conflict determination linked to Judge/application/assignment |
| Evaluation confidentiality | Evaluation owner Judge + disclosure state on deliberation/workflow |
| Protected history | Immutable versions/finalized states plus activity/audit events |
| Governance override | Authorized governance capability + explicit override event/reason/actor/time |

**RECOMMENDED:** represent program memberships and application memberships as domain relationships, not Spatie teams. Keep Spatie roles/permissions as capability gates and use record policies for scope. A user can have multiple memberships/roles across programs as approved.

## 10. Immutability and History

| Record | Required protection |
|---|---|
| Submitted application version | Immutable; no normal update/delete; judging links exact version |
| Rubric version/criteria | Freeze before dependent evaluation; new version for change |
| Finalized evaluation/scores | Locked; governed reopen only; preserve original/revision history |
| Deliberation | Closed record append-only; original evaluations remain historical |
| Finalized decision | Immutable normal path; reversal/supersession preserves prior decision |
| Outcome/transition | Append-only event/history; later change creates governed successor |
| Conflict determination | Preserve declarations/determinations; no hiding via delete |
| Assignment history | Preserve assignment/reassignment/decline/removal |
| Screening result | Preserve completed result; correction uses governed new/review record |
| Program membership | End membership and audit; preserve grants/scope changes |
| Notifications | Sent content not silently rewritten; existing read state remains |
| Activity audit | Append-only; no normal update/delete |

**RECOMMENDED deletion policy:** hard deletion only for abandoned draft records where no submitted, screening, assignment, evaluation, deliberation, decision, notification, or audit dependency exists. Otherwise use state transition/archive or governed supersession. Do not add soft deletes to every table automatically.

## 11. Constraints, Indexes, and Delete Behavior

### Primary keys

**RECOMMENDED:** use Laravel-compatible big integer identity primary keys for domain rows, matching the starter. Use UUID/ULID only where external exposure or event identity requires it and approve that choice before implementation.

### Foreign keys

All required ownership and scope references are non-nullable unless the lifecycle explicitly permits an absent actor/target:

- `program_memberships.program_id → programs.id`, `user_id → users.id`.
- `applications.program_id → programs.id`, `primary_owner_id → users.id`.
- `application_members.application_id → applications.id`, `user_id → users.id`.
- `application_versions.application_id → applications.id`.
- Screening, assignment, conflict, evaluation, deliberation, decision, and outcome rows reference their parent application/program and actor users as required.
- Evaluations require `application_version_id` and `rubric_version_id`.
- Scores require `evaluation_id` and `rubric_criterion_id`.

**Delete recommendations:** restrict deletion of programs, applications, submitted versions, rubrics, evaluations, deliberations, decisions, outcomes, and audit subjects once dependent history exists. Cascade only from a disposable draft parent to disposable draft children before submission. User deletion should follow the starter's existing policy and be restricted/nullified according to whether history needs the actor reference; never cascade away consequential history.

### Uniqueness

- Program: unique approved public/program identifier; exact slug/key format **OWNER DECISION REQUIRED**.
- Program membership: unique active `(program_id, user_id, capability/role)` or a controlled membership identity; no duplicate active membership.
- Application: unique program-local application reference if exposed; exact format **OWNER DECISION REQUIRED**.
- Application member: unique active `(application_id, user_id)`.
- Application version: unique `(application_id, version_number)`.
- Judge assignment: prevent duplicate active assignment for the same `(application_id, judge_id, stage scope)`; exact stage granularity **OWNER DECISION REQUIRED**.
- Conflict: prevent duplicate current conflict record for `(assignment/application, judge)` while preserving historical determinations.
- Rubric version: unique `(rubric_id, version_number)`.
- Rubric criterion: unique `(rubric_version_id, stable_key/order)`.
- Evaluation: prevent duplicate active Judge evaluation for exact application version/rubric version; preserve revisions separately.
- Evaluation score: unique `(evaluation_id, rubric_criterion_id)`.
- Deliberation: prevent more than one active deliberation per application/program stage; exact stage rule **OWNER DECISION REQUIRED**.
- Decision: at most one active/finalized decision per application/outcome context; superseded decisions remain historical.
- Outcome/transition: unique active transition identity; never duplicate a committed transition idempotently.
- Notifications/audit: use existing starter uniqueness/identity conventions; domain event IDs should be idempotent when emitted.

### Indexes

At minimum index:

- membership `(user_id, program_id, active/status)` and `(program_id, active/status)`;
- applications `(program_id, status)`, `(primary_owner_id, status)`, and deadline/window lookup through program;
- application members `(user_id, application_id, active)`;
- versions `(application_id, version_number)` and `(application_id, status)`;
- screenings `(program_id, status)` and `(application_id, created_at)`;
- assignments `(judge_id, program_id, status)`, `(application_id, status)`, and stage scope;
- conflicts `(judge_id, application_id, status)`;
- rubric versions `(rubric_id, version_number, status)`;
- evaluations `(judge_id, application_version_id, status)`, `(application_id, status)`, and disclosure queries;
- scores `(evaluation_id, criterion_id)`;
- deliberations `(application_id, status)`;
- decisions/outcomes `(application_id, status)` and `(program_id, status)`;
- audit subject/actor/event/time using existing activity-log conventions.

### Check constraints

Use database checks where portable and meaningful:

- raw score `>= 0 AND <= 10`;
- weight `>= 0` and active rubric total validated transactionally as exactly 100;
- version number `> 0`;
- deadline ordering (`opens_at < closes_at` in the program timezone representation);
- finalized timestamps/actors required when finalized;
- submitted version requires submitted timestamp/actor;
- decision finalization requires rationale/outcome;
- no negative sequence/order values.

Do not rely on database enum types for frequently evolving workflow values; use constrained strings plus application transition logic and targeted checks. Exact state values require approval.

## 12. PostgreSQL Design and Transactions

- PostgreSQL is the primary EAIC database; SQLite remains the automated-test compatibility database.
- Use timezone-aware timestamps (`timestamptz` semantics) for deadlines, transitions, audit, declarations, finalization, and notifications. Store the program's IANA timezone identifier separately so deadline interpretation is reproducible.
- **RECOMMENDED:** use `numeric`, not floating point, for weights, scores, contributions, and totals.
- Prefer `jsonb` only for genuinely variable structured evidence/metadata, validation results, audit properties, and notification payloads. Do not place authoritative relational state only in JSONB.
- Use ordinary constrained strings rather than PostgreSQL enum types for lifecycle statuses unless a later approved policy accepts enum migration overhead.
- Use database transactions for submit, revision creation, assignment/reassignment, conflict determination, rubric freeze, evaluation finalization/reopen, deliberation close, decision finalization, and outcome transition.
- Lock the application/version or use optimistic version checks during submission/revision to prevent duplicate version numbers and deadline races.
- Lock or atomically claim evaluation finalization to prevent duplicate finalization/reopen actions.
- Validate rubric weight totals and calculate totals inside the finalization transaction.
- Use idempotency keys/domain event identity for notification and audit emission where retries are possible.
- Queue email delivery only after the authoritative in-app notification and domain transaction commit; do not make email delivery the source of truth.
- Verify every EAIC migration and critical query against PostgreSQL; retain SQLite tests for supported application behavior and avoid PostgreSQL-only types that break the test architecture.

## 13. Migration Dependency Order

1. Existing Master Starter `users`, Spatie authorization, `media`, `notifications`, and `activity_logs` baseline (already present in PostgreSQL).
2. `programs`.
3. Program memberships/capability-scope records.
4. Program eligibility rules/configuration.
5. `applications` with primary owner and program reference.
6. `application_members`.
7. `application_versions`.
8. Application media/version associations, reusing `media`.
9. Automated validation/screening records.
10. Judge profiles only if a separate profile is required; otherwise use `users` plus approved capability.
11. Judge assignments.
12. Conflict declarations/determinations.
13. Rubrics.
14. Rubric versions.
15. Rubric criteria.
16. Evaluations.
17. Evaluation criterion scores.
18. Deliberations and participants/ disclosure records if required by approved model.
19. Decisions.
20. Outcomes/transitions.
21. Any domain event/idempotency support required for notifications/audit, without duplicating starter notifications/activity logs.
22. Seed/reference data only after schema and RBAC approval.

The order is dependency-oriented and does not authorize any migration by itself.

## 14. MVP vs Deferred Entities

### MVP database entities

- Programs.
- Program memberships and scope data.
- Program eligibility rules/validation results.
- Applications.
- Application members.
- Application versions.
- Version/media association using existing `media`.
- Screening/validation records.
- Judge assignments.
- Conflict records.
- Rubric, rubric versions, and criteria.
- Evaluations and criterion scores.
- Deliberations and only the participants/disclosure records required by the approved workflow.
- Decisions.
- Outcomes/transitions.
- Existing notifications and activity logs.

### Deferred entities

Do not create tables for incubation, mentorship, milestones, resources, events/training/showcase, partners/vendors/stakeholders, alumni/follow-up, broad AI assistants, or autonomous decision systems in the MVP migration batch.

## 15. Schema Acceptance-Test Specification

Specification only; no tests were implemented.

| ID | Test | Expected result |
|---|---|---|
| DB-01 | Insert application with nonexistent program | Foreign-key violation; transaction rolls back |
| DB-02 | Insert application member twice for same active user/application | Uniqueness violation or controlled idempotent response; no duplicate active membership |
| DB-03 | Insert application version with duplicate application/version number | Rejected; prior version remains unchanged |
| DB-04 | Update submitted application version | Rejected by policy and persistence guard; submitted snapshot unchanged |
| DB-05 | Delete submitted version with evaluation dependency | Restricted/rejected; evaluation reference remains valid |
| DB-06 | Create evaluation referencing another application version than the assigned exact version | Rejected by policy/integrity validation |
| DB-07 | Create evaluation referencing unfrozen/non-approved rubric version | Rejected |
| DB-08 | Add duplicate criterion score to evaluation | Rejected by uniqueness constraint |
| DB-09 | Save score outside 0–10 | Rejected by validation/check constraint |
| DB-10 | Activate rubric whose weights do not total 100 | Rejected transactionally |
| DB-11 | Finalize evaluation with client-supplied altered total | Rejected/ignored; server recalculates deterministic total |
| DB-12 | Recalculate identical evaluation twice from same raw values/version | Same persisted total/contributions within approved precision |
| DB-13 | Change rubric criterion after evaluation depends on frozen version | Rejected; new version required |
| DB-14 | Create duplicate active Judge assignment | Rejected or controlled idempotent result; no duplicate access grant |
| DB-15 | Evaluate with blocking conflict | Denied by policy; no evaluation mutation |
| DB-16 | Reassign after blocking conflict | Old assignment history preserved; new assignment passes conflict precheck |
| DB-17 | Applicant accesses another applicant's application | Denied by record policy |
| DB-18 | Staff accesses application in unrelated program | Denied by membership/policy |
| DB-19 | Judge accesses unassigned application | Denied |
| DB-20 | Judge accesses peer private evaluation before disclosure | Denied |
| DB-21 | Finalize evaluation twice concurrently | One valid finalization; duplicate attempt denied/idempotently handled; one audit event |
| DB-22 | Reopen finalized evaluation without authority/reason | Denied; no state/history mutation |
| DB-23 | Open deliberation before required evaluations are finalized | Denied |
| DB-24 | Create second active deliberation for same application/context | Rejected by uniqueness/policy |
| DB-25 | Decision Maker creates decision outside membership/authority | Denied |
| DB-26 | Finalize decision without rationale/outcome | Rejected |
| DB-27 | Create second active/finalized decision without governed supersession | Rejected |
| DB-28 | Reverse decision without authority/reason | Denied; original decision remains |
| DB-29 | Record outcome transition to invalid/unapproved target | Rejected; no partial transition |
| DB-30 | Emit notification before decision transaction commits | Not allowed; authoritative notification follows commit |
| DB-31 | Delete or update activity audit event through normal domain operation | Denied; append-only history preserved |
| DB-32 | Governance override changes protected record | Requires explicit authority, reason, actor, timestamp, preserved prior state, and audit |
| DB-33 | Submit concurrently at/after strict deadline | Exactly one valid result according to locked timezone-aware deadline; late result denied |
| DB-34 | Run the same assignment/finalization/decision command twice with same idempotency identity | No duplicate committed domain event or notification |
| DB-35 | Apply MVP schema to PostgreSQL and run critical constraints | Migration/query verification passes without SQLite-only assumptions |

## 16. Owner Decision Required

The authoritative EAIC decision record determines the authorization/lifecycle direction but does not provide all literal migration values. Controller approval is still required for:

1. Exact domain permission names and role-to-permission matrix; D-014 approves the singular `resource.action` convention, while D-015 keeps inherited roles separate from EAIC authority.
2. Exact database representation/cardinality for membership lifecycle status, capabilities, stage scope, assignments, application members, owner delegation, and Decision Maker authority.
3. Exact public fields/authentication boundary and application member/owner action rules; D-018–D-020 decide explicit ownership, approved members, and bounded delegation.
4. Exact lifecycle state names and complete transition matrix, including withdrawal, reopening, and appeal. D-047 requires explicit state machines; D-050 approves the four MVP outcome values.
5. Exact judge assignment granularity and precedence; this specification recommends application-level assignment for MVP.
6. Exact conflict categories, indirect affiliation, waiver, determination authority, and disclosure fields.
7. Exact rubric scoring scale details beyond 0–10, weight precision, rounding, and calculation-version policy.
8. Exact applicant/Judge/Decision Maker field-level disclosure and controlled disclosure point.
9. Exact notification event catalog, recipients, timing, email/retry behavior, and notification data visibility. D-035 decides event-driven notifications, authoritative in-app records, email delivery, and in-app retention after delivery failure.
10. Exact configured transition targets and any additional outcome metadata. D-050 approves the four MVP outcome values.
11. Exact EAIC namespace/module structure.
12. Confirmation of the recommended PostgreSQL `public` schema and database `development` use already established by the project baseline.
13. Confirmation whether a separate Judge profile entity is needed beyond `users` plus approved capability.
14. Confirmation of any schema choice for deliberation participants, disclosure events, validation results, and domain history beyond `activity_logs`.
15. Approval of technical recommendations in this document: numeric precision, state strings versus enums, key strategy, hard-delete/archive rules, and idempotency representation.

No item above should be silently finalized by a later implementation task.

## 17. Implementation Boundary

This document defines database/lifecycle requirements only. It does not create:

- migrations;
- models or factories;
- controllers, policies, services, routes, or UI;
- roles or permissions;
- seed data;
- database tables or structure;
- AI providers or workflow code.

## 18. Recommended Next Task

After controller review, create the separate acceptance-test specification and resolve/record the remaining schema decisions. Then implement only the approved MVP migration batch in PostgreSQL first, with SQLite-compatible tests, before adding models or workflow services.
