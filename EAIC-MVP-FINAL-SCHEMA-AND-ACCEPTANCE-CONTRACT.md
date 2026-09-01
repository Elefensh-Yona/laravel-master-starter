# EAIC MVP Final Schema and Acceptance Contract

**Project:** Ethiopian AI Center (EAIC)  
**Status:** Final pre-migration contract. This document specifies the approved MVP direction and implementation-ready technical contract; it does not create migrations or database objects.  
**Authority:** EAIC decisions D-008 through D-050 in `TheRoadmap/decisions.md`, the EAIC Blueprint, product/governance contract, and RBAC/scope matrix.

**Pre-migration register:** `EAIC-PRE-MIGRATION-DECISION-REGISTER.md` finalizes the Batch 1 boundary and classifies implementation conventions as approved, recommended, deferred, or owner-required.

## 1. Approved Technical Direction

- Permission names use singular `resource.action`.
- The five EAIC actors are Super Admin, Program Staff, Decision Maker, Judge, and Applicant.
- Program scope uses explicit domain Program Membership; inherited starter roles do not automatically grant EAIC authority.
- Applications have an explicit primary owner, approved members, and controlled delegation.
- Individual, Team, and Organization applications are supported.
- Judge assignment is explicit and application-level for the MVP.
- Submitted application versions are immutable; each evaluation references the exact submitted version.
- Lifecycle values are constrained strings enforced by explicit transition logic, not PostgreSQL enums.
- Scores are 0–10, weighted to 100%, deterministically calculated to a normalized 0–100 result, and stored with two calculated decimal places.
- PostgreSQL is primary; timestamps are timezone-aware and every Program stores an IANA timezone.
- JSONB is limited to justified variable metadata; authoritative state remains relational.
- Trust-critical history is preserved using immutable records, archive/supersession, and append-only audit. Blanket soft deletion is prohibited.
- Consequential transitions run transactionally with appropriate locking/optimistic checks.
- In-app notifications are authoritative; notifications are event-driven and email is dispatched after commit.
- Core outcomes are `ACCEPTED`, `REJECTED`, `WAITLISTED`, and `REVISION_REQUIRED`.

## 2. Master Starter Reuse

| Existing starter table | EAIC use | Do not duplicate |
|---|---|---|
| `users` | Every actor, owner, member, evaluator, determiner, participant, and audit actor | Identity/authentication/profile table |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Global capability infrastructure under Spatie | Separate EAIC role/permission engine |
| `notifications` | Authoritative in-app lifecycle notification record | EAIC notification table |
| `activity_logs` | Append-only cross-cutting audit foundation through `ActivityLogger` | Generic EAIC audit table |
| `media` | Evidence/files attached through its existing polymorphic relation | EAIC file-storage table |
| `settings` | Global shared settings only | Program workflow/settings data |

## 3. New MVP Entity and Table Contract

All new keys are **RECOMMENDED — NOT YET APPROVED** Laravel-style bigint identities to match the starter. All timestamps below are `timestamptz` in PostgreSQL and map through Laravel timestamps. Status columns are constrained strings; their allowed values are defined in Section 5.

### 3.1 `programs`

| Item | Contract |
|---|---|
| Purpose | Multi-program lifecycle container |
| Primary key | `id` bigint |
| Required columns | `name` varchar(255), `code` varchar(64), `slug` varchar(120), `status` varchar(32), `timezone` varchar(64), `opens_at` timestamptz, `closes_at` timestamptz, `created_by` FK users, timestamps |
| Nullable columns | `description` text, `published_at` timestamptz, `closed_at` timestamptz, `archived_at` timestamptz, `metadata` jsonb |
| Foreign keys | `created_by → users.id` restrict; no program deletion after dependent history |
| Constraints/indexes | unique `code`; unique `slug`; index `(status, opens_at, closes_at)`; check `opens_at < closes_at` |
| State/history | `status`; publication, deadline, closure, archive changes audited |
| Delete behavior | restrict once dependent membership/application/history exists; archive rather than delete |
| Relationships | one-to-many memberships, eligibility rules, applications, rubrics |

### 3.2 `program_memberships`

| Item | Contract |
|---|---|
| Purpose | Primary program-scope relationship with lifecycle status and EAIC capability |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `user_id` FK, `capability` varchar(64), `status` varchar(32), `starts_at` timestamptz, `granted_by` FK users, timestamps |
| Nullable columns | `stage_scope` jsonb, `ends_at` timestamptz, `ended_by` FK users, `end_reason` text, `metadata` jsonb |
| Constraints/indexes | unique active membership per `(program_id, user_id, capability)`; indexes `(user_id, status)`, `(program_id, status)`, `(program_id, user_id, status)` |
| State/history | `active`, `suspended`, `ended`; ending prevents new actions but preserves historical actions |
| Delete behavior | restrict; end membership instead of delete |
| Relationships | belongs to Program and User; supplies Staff/Judge/Decision Maker program scope |

### 3.3 `program_eligibility_rules`

| Item | Contract |
|---|---|
| Purpose | Program-controlled objective eligibility configuration |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `key` varchar(64), `label` varchar(255), `rule_type` varchar(64), `configuration` jsonb, `position` integer, `is_required` boolean default true, timestamps |
| Nullable columns | `description` text |
| Constraints/indexes | unique `(program_id, key)`; unique `(program_id, position)`; index `program_id` |
| State/history | Changes after publication require controlled program transition/audit |
| Delete behavior | restrict if validation/screening records reference its meaning; otherwise archive/remove only before use |
| Relationships | belongs to Program; evaluated by validation results |

### 3.4 `applications`

| Item | Contract |
|---|---|
| Purpose | Individual, Team, or Organization participation aggregate |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `primary_owner_id` FK users, `applicant_type` varchar(32), `status` varchar(32), `current_version_id` nullable deferred FK application_versions, timestamps |
| Nullable columns | `reference` varchar(64), `submitted_at` timestamptz, `metadata` jsonb |
| Foreign keys | Program/user restrict; `current_version_id` set null until version exists, then restricted |
| Constraints/indexes | unique non-null `(program_id, reference)`; indexes `(program_id, status)`, `(primary_owner_id, status)`; check applicant type in `INDIVIDUAL`, `TEAM`, `ORGANIZATION` |
| State/history | Aggregate state only; business content lives in immutable versions; owner changes audited |
| Delete behavior | no delete after submitted version or consequential dependency; archive/supersede |
| Relationships | belongs to Program and primary owner; has members, delegations, versions, screenings, assignments, conflicts, evaluations, deliberations, decisions, outcomes |

### 3.5 `application_members`

| Item | Contract |
|---|---|
| Purpose | Approved Team/Organization application membership distinct from ownership |
| Primary key | `id` bigint |
| Required columns | `application_id` FK, `user_id` FK, `status` varchar(32), `joined_at` timestamptz, `approved_by` FK users, timestamps |
| Nullable columns | `ended_at` timestamptz, `ended_by` FK users, `end_reason` text |
| Constraints/indexes | unique active `(application_id, user_id)`; index `(user_id, status)` |
| State/history | `active`, `ended`; membership never implies owner-only rights |
| Delete behavior | restrict; end rather than delete after action/history |
| Relationships | belongs to Application/User; has delegation records |

### 3.6 `application_member_delegations`

| Item | Contract |
|---|---|
| Purpose | Controlled primary-owner delegation of specific permitted actions to an approved member |
| Primary key | `id` bigint |
| Required columns | `application_id` FK, `application_member_id` FK, `permission` varchar(96), `granted_by` FK users, `granted_at` timestamptz, timestamps |
| Nullable columns | `expires_at` timestamptz, `revoked_at` timestamptz, `revoked_by` FK users, `revocation_reason` text |
| Constraints/indexes | unique active `(application_member_id, permission)`; index `(application_id, permission)`; check expiry later than grant when non-null |
| State/history | active/revoked/expired is derived from timestamps; grants/revocations audited |
| Delete behavior | restrict; revoked records preserved |
| Relationships | belongs to Application, Application Member, grant/revoke Users |

### 3.7 `application_versions`

| Item | Contract |
|---|---|
| Purpose | Draft or immutable submitted snapshot of application content/evidence |
| Primary key | `id` bigint |
| Required columns | `application_id` FK, `version_number` integer, `status` varchar(32), `content` jsonb, `created_by` FK users, timestamps |
| Nullable columns | `submitted_at` timestamptz, `submitted_by` FK users, `revision_reason` text, `supersedes_version_id` self FK, `metadata` jsonb |
| Constraints/indexes | unique `(application_id, version_number)`; index `(application_id, status)`; check version number > 0 |
| State/history | `draft`, `submitted`; submitted rows immutable; current version pointer changes only in transition transaction |
| Delete behavior | draft may be deleted only before submission and with no dependencies; submitted restrict forever |
| Relationships | belongs to Application, creator/submitting User, optional superseded version; referenced by validation, screening, assignment/evaluation and media |

### 3.8 `application_version_media`

| Item | Contract |
|---|---|
| Purpose | Associates existing starter media with exact application version/evidence scope |
| Primary key | `id` bigint |
| Required columns | `application_version_id` FK, `media_id` FK, `attached_by` FK users, `visibility` varchar(32), timestamps |
| Nullable columns | `label` varchar(255), `metadata` jsonb |
| Constraints/indexes | unique `(application_version_id, media_id)`; index `(media_id, visibility)` |
| State/history | Submitted-version evidence linkage immutable after submission |
| Delete behavior | restrict after version submitted/evaluated; retain starter media separately |
| Relationships | belongs to Application Version, `media`, attaching User |

### 3.9 `application_validations`

| Item | Contract |
|---|---|
| Purpose | Automated objective validation outputs, never a final eligibility decision |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `application_version_id` FK, `status` varchar(32), `executed_at` timestamptz, timestamps |
| Nullable columns | `result` jsonb, `executed_by` FK users nullable, `failure_reason` text |
| Constraints/indexes | unique `(application_version_id, program_id)` for current deterministic validation run; indexes `(application_id, status)`, `(program_id, executed_at)` |
| State/history | `passed`, `failed`, `error`; immutable result snapshot once recorded |
| Delete behavior | restrict after screening dependency |
| Relationships | belongs to Program/Application/Application Version; inputs Program Eligibility Rules |

### 3.10 `screenings`

| Item | Contract |
|---|---|
| Purpose | Human Program Staff screening outcome over exact submitted version and validation context |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `application_version_id` FK, `status` varchar(32), `outcome` varchar(32), `screened_by` FK users, `completed_at` timestamptz, `rationale` text, timestamps |
| Nullable columns | `validation_id` FK, `reopened_at` timestamptz, `reopened_by` FK users, `reopen_reason` text |
| Constraints/indexes | one current completed screening per application version; indexes `(application_id, status)`, `(program_id, outcome)`, `(screened_by, completed_at)` |
| State/history | `in_review`, `completed`; outcome `ELIGIBLE` or `INELIGIBLE`; governed re-review creates successor record |
| Delete behavior | restrict after assignment/evaluation/decision dependency |
| Relationships | belongs to Program/Application/Version/Validation/Staff User |

### 3.11 `judge_assignments`

| Item | Contract |
|---|---|
| Purpose | Explicit application-level Judge authorization for MVP |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `application_version_id` FK, `judge_id` FK users, `status` varchar(32), `assigned_by` FK users, `assigned_at` timestamptz, timestamps |
| Nullable columns | `ended_at` timestamptz, `ended_by` FK users, `end_reason` text, `reassigned_from_id` self FK |
| Constraints/indexes | partial unique active `(application_version_id, judge_id)`; indexes `(judge_id, status)`, `(application_id, status)`, `(program_id, status)` |
| State/history | `active`, `declined`, `removed`, `completed`; reassignment creates a new row and links history |
| Delete behavior | restrict; end/reassign rather than delete |
| Relationships | belongs to Program/Application/Version/Judge/assigner; has Conflicts and one active Evaluation |

### 3.12 `conflicts`

| Item | Contract |
|---|---|
| Purpose | System signal, Judge declaration, and controlled human determination for a Judge assignment |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `judge_assignment_id` FK, `judge_id` FK users, `source` varchar(32), `status` varchar(32), `created_at` timestamptz |
| Nullable columns | `category` varchar(64), `explanation` text, `detected_at` timestamptz, `declared_at` timestamptz, `determined_by` FK users, `determined_at` timestamptz, `determination_reason` text, `restriction_effective_at` timestamptz, `supersedes_conflict_id` self FK |
| Constraints/indexes | partial unique unresolved/current `(judge_assignment_id)`; indexes `(judge_id, status)`, `(application_id, status)`, `(program_id, status)` |
| State/history | `further_review`, `declared`, `cleared`, `non_blocking`, `blocked`; determination/history append-only through successor records |
| Delete behavior | never delete normal records; supersede through governed action |
| Relationships | belongs to Program/Application/Judge Assignment/Judge/determiner |

### 3.13 `rubrics`

| Item | Contract |
|---|---|
| Purpose | Program-owned rubric identity |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `name` varchar(255), `status` varchar(32), `created_by` FK users, timestamps |
| Nullable columns | `description` text, `metadata` jsonb |
| Constraints/indexes | unique `(program_id, name)`; index `(program_id, status)` |
| State/history | `draft`, `active`, `retired`; version changes audited |
| Delete behavior | restrict once versions/evaluations exist |
| Relationships | belongs to Program; has versions |

### 3.14 `rubric_versions`

| Item | Contract |
|---|---|
| Purpose | Exact versioned rubric evaluated by Judges |
| Primary key | `id` bigint |
| Required columns | `rubric_id` FK, `version_number` integer, `status` varchar(32), `created_by` FK users, timestamps |
| Nullable columns | `activated_at` timestamptz, `frozen_at` timestamptz, `retired_at` timestamptz, `metadata` jsonb |
| Constraints/indexes | unique `(rubric_id, version_number)`; one active version per rubric via partial unique index; check version number > 0 |
| State/history | `draft`, `active`, `frozen`, `retired`; frozen before dependent evaluations |
| Delete behavior | restrict once criteria/evaluations exist |
| Relationships | belongs to Rubric; has Criteria; referenced by Evaluations |

### 3.15 `rubric_criteria`

| Item | Contract |
|---|---|
| Purpose | Ordered weighted criterion within one rubric version |
| Primary key | `id` bigint |
| Required columns | `rubric_version_id` FK, `key` varchar(64), `label` varchar(255), `weight` numeric(5,2), `position` integer, `is_required` boolean default true, timestamps |
| Nullable columns | `description` text, `guidance` text |
| Constraints/indexes | unique `(rubric_version_id, key)`; unique `(rubric_version_id, position)`; check `weight >= 0`; check `position >= 0` |
| State/history | immutable after parent frozen; active/frozen weight total equals `100.00` in transition transaction |
| Delete behavior | restrict once evaluation score exists |
| Relationships | belongs to Rubric Version; has Evaluation Criterion Scores |

### 3.16 `evaluations`

| Item | Contract |
|---|---|
| Purpose | One Judge's independent assessment of one exact submitted version using one frozen rubric version |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `application_version_id` FK, `judge_assignment_id` FK, `judge_id` FK users, `rubric_version_id` FK, `status` varchar(32), `qualitative_assessment` text, `recommendation` varchar(64), `calculated_total` numeric(5,2), timestamps |
| Nullable columns | `submitted_at` timestamptz, `finalized_at` timestamptz, `finalized_by` FK users, `reopened_at` timestamptz, `reopened_by` FK users, `reopen_reason` text, `supersedes_evaluation_id` self FK, `calculation_version` varchar(32) default `v1`, `metadata` jsonb |
| Constraints/indexes | partial unique active `(judge_assignment_id, rubric_version_id)`; indexes `(judge_id, status)`, `(application_version_id, status)`, `(application_id, status)`, `(rubric_version_id, status)`; check total between 0 and 100 |
| State/history | `draft`, `submitted`, `finalized`, `reopened`; finalized is protected; reopening creates successor/revision history |
| Delete behavior | restrict; no normal delete after first score/submission |
| Relationships | belongs to Program/Application/Version/Assignment/Judge/Rubric Version; has criterion scores |

### 3.17 `evaluation_criterion_scores`

| Item | Contract |
|---|---|
| Purpose | Numerical score and separate justification/evidence for each rubric criterion |
| Primary key | `id` bigint |
| Required columns | `evaluation_id` FK, `rubric_criterion_id` FK, `raw_score` numeric(4,2), `calculated_contribution` numeric(5,2), `justification` text, timestamps |
| Nullable columns | `evidence_note` text, `metadata` jsonb |
| Constraints/indexes | unique `(evaluation_id, rubric_criterion_id)`; index `rubric_criterion_id`; check raw score in `[0,10]`; check contribution in `[0,100]` |
| State/history | locked when parent evaluation finalizes; never accept client-calculated contribution |
| Delete behavior | restrict after parent finalizes |
| Relationships | belongs to Evaluation and Rubric Criterion |

### 3.18 `deliberations`

| Item | Contract |
|---|---|
| Purpose | Structured human-led deliberation after controlled disclosure |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `status` varchar(32), `opened_by` FK users, `opened_at` timestamptz, timestamps |
| Nullable columns | `closed_by` FK users, `closed_at` timestamptz, `summary` text, `metadata` jsonb |
| Constraints/indexes | partial unique active `(application_id)`; indexes `(program_id, status)`, `(application_id, status)` |
| State/history | `open`, `active`, `closed`; closed deliberation append-only |
| Delete behavior | restrict after participants/decision |
| Relationships | belongs to Program/Application; has participants/disclosures; may be referenced by Decision |

### 3.19 `deliberation_participants`

| Item | Contract |
|---|---|
| Purpose | Records authorized human participants and controlled disclosure access |
| Primary key | `id` bigint |
| Required columns | `deliberation_id` FK, `user_id` FK, `role` varchar(32), `joined_at` timestamptz, timestamps |
| Nullable columns | `left_at` timestamptz, `left_reason` text |
| Constraints/indexes | unique active `(deliberation_id, user_id)`; index `(user_id, deliberation_id)` |
| State/history | participant entry/exit auditable; does not alter original evaluations |
| Delete behavior | restrict; end participation instead of delete |
| Relationships | belongs to Deliberation/User |

### 3.20 `deliberation_disclosures`

| Item | Contract |
|---|---|
| Purpose | Auditable controlled disclosure event for evaluation aggregates/individual fields |
| Primary key | `id` bigint |
| Required columns | `deliberation_id` FK, `disclosed_by` FK users, `disclosed_at` timestamptz, `scope` varchar(32), timestamps |
| Nullable columns | `metadata` jsonb |
| Constraints/indexes | unique `(deliberation_id, scope)`; index `disclosed_at` |
| State/history | immutable disclosure record; scope must not exceed authorized policy |
| Delete behavior | restrict |
| Relationships | belongs to Deliberation/discloser User; governs evaluation visibility policy |

### 3.21 `decisions`

| Item | Contract |
|---|---|
| Purpose | Formal human Decision Maker record separate from scores and recommendations |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `deliberation_id` FK, `decision_maker_id` FK users, `status` varchar(32), `outcome` varchar(32), `rationale` text, timestamps |
| Nullable columns | `finalized_at` timestamptz, `supersedes_decision_id` self FK, `superseded_at` timestamptz, `metadata` jsonb |
| Constraints/indexes | partial unique active/finalized `(application_id)`; indexes `(program_id, outcome)`, `(decision_maker_id, status)`; check outcome in Section 5 |
| State/history | `draft`, `finalized`, `superseded`; supersession creates new row, preserving prior decision |
| Delete behavior | restrict after finalization/outcome |
| Relationships | belongs to Program/Application/Deliberation/Decision Maker; has Outcome Transition |

### 3.22 `outcome_transitions`

| Item | Contract |
|---|---|
| Purpose | Separate controlled post-decision outcome and configured next-stage/program movement |
| Primary key | `id` bigint |
| Required columns | `program_id` FK, `application_id` FK, `decision_id` FK, `outcome` varchar(32), `status` varchar(32), `recorded_by` FK users, `recorded_at` timestamptz, timestamps |
| Nullable columns | `next_program_id` FK programs, `next_stage_key` varchar(64), `transitioned_at` timestamptz, `supersedes_transition_id` self FK, `metadata` jsonb |
| Constraints/indexes | partial unique active `(decision_id)`; indexes `(application_id, status)`, `(program_id, outcome)`; check outcome in Section 5 |
| State/history | `recorded`, `transitioned`, `superseded`; transition target required only for `transitioned` |
| Delete behavior | restrict; applications are never deleted when a decision is made |
| Relationships | belongs to Program/Application/Decision/recorder; optional next Program/self predecessor |

## 4. Relationship and Cardinality Contract

| Relationship | Cardinality | Rule |
|---|---|---|
| User ↔ Program Membership | User 0..N; Program 0..N | Active capability is explicit membership, not inferred from starter role |
| Program ↔ Application | Program 0..N; Application exactly 1 | An application belongs to one program |
| Application ↔ Primary Owner | Application exactly 1; User 0..N | Owner is explicit and distinct from member |
| Application ↔ Members | Application 0..N; User 0..N | Individual may have no additional member; Team/Organization supports approved members |
| Application Member ↔ Delegation | Member 0..N; Delegation exactly 1 member | Delegation is specific permission, revocable/expiring/audited |
| Application ↔ Versions | Application 1..N; Version exactly 1 application | Submitted versions immutable; version numbers monotonic |
| Application ↔ Screening | Application 0..N; Screening exactly 1 application/version | Current screening identified by status/successor, history retained |
| Application ↔ Judge Assignment | Application 0..N; Assignment exactly 1 application/version/Judge | MVP assignment is explicit application-level |
| Assignment ↔ Conflict | Assignment 0..N; Conflict exactly 1 assignment | One current unresolved conflict allowed; history superseded not overwritten |
| Program ↔ Rubric | Program 0..N; Rubric exactly 1 program | Rubric identity is program-owned |
| Rubric ↔ Rubric Version | Rubric 1..N; Version exactly 1 rubric | One active version; frozen used by evaluation |
| Rubric Version ↔ Criterion | Version 1..N; Criterion exactly 1 version | Ordered criteria; frozen criteria immutable |
| Evaluation ↔ Application Version | Evaluation exactly 1; Version 0..N | Exact submitted version only; never follows current pointer |
| Evaluation ↔ Rubric Version | Evaluation exactly 1; Version 0..N | Exact frozen rubric version only |
| Evaluation ↔ Judge | Evaluation exactly 1 Judge | Must match its active Judge Assignment |
| Evaluation ↔ Criterion Scores | Evaluation 1..N; score exactly 1 evaluation/criterion | Exactly one score per required criterion on finalization |
| Application ↔ Deliberation | Application 0..N historical; max 1 active | Deliberation does not rewrite evaluations |
| Deliberation ↔ Participants | Deliberation 1..N when active | Authorized participants only |
| Application ↔ Decision | Application 0..N historical; max 1 active/finalized | Decision Maker only; supersession preserves history |
| Decision ↔ Outcome Transition | Decision 0..N historical; max 1 active | Outcome separate from decision |
| Audit/history ↔ domain rows | Existing `activity_logs` 0..N per subject | Audit subject/actor tracks all consequential transitions |

## 5. Exact MVP State Machine Contract

### Allowed values

| Entity | Values |
|---|---|
| Program | `draft`, `published`, `closed`, `archived` |
| Program Membership | `active`, `suspended`, `ended` |
| Application | `draft`, `submitted`, `screening`, `eligible`, `ineligible`, `assigned`, `under_evaluation`, `evaluated`, `in_deliberation`, `decided`, `outcomed`, `archived` |
| Application Version | `draft`, `submitted` |
| Validation | `passed`, `failed`, `error` |
| Screening | `in_review`, `completed` |
| Judge Assignment | `active`, `declined`, `removed`, `completed` |
| Conflict | `further_review`, `declared`, `cleared`, `non_blocking`, `blocked`, `superseded` |
| Rubric | `draft`, `active`, `retired` |
| Rubric Version | `draft`, `active`, `frozen`, `retired` |
| Evaluation | `draft`, `submitted`, `finalized`, `reopened`, `superseded` |
| Deliberation | `open`, `active`, `closed` |
| Decision | `draft`, `finalized`, `superseded` |
| Outcome Transition | `recorded`, `transitioned`, `superseded` |
| Core outcome | `ACCEPTED`, `REJECTED`, `WAITLISTED`, `REVISION_REQUIRED` |

### Program

| Current | Action → result | Actor/permission | Prerequisites and blockers | Irreversible / override | Audit |
|---|---|---|---|---|---|
| `draft` | configure → `draft` | Program Staff, `program.update` | Active program membership/capability; no forbidden dependency change | No; protected changes require override | Consequential changes |
| `draft` | publish → `published` | Program Staff, `program.publish` | Eligibility configuration, valid timezone/deadline, authorized transition | No direct reversal; governed correction only | Yes |
| `published` | close → `closed` | Program Staff, `program.update` | Closing policy/deadline | No ordinary reopen | Yes |
| `closed` | archive → `archived` | Program Staff, `program.update` | History retained | Yes, absent governed exceptional path | Yes |

### Application and version

| Current | Action → result | Actor/permission | Prerequisites and blockers | Irreversible / override | Audit |
|---|---|---|---|---|---|
| none | create → Application/Version `draft` | Applicant, `application.create` | Program published/open; owner established | No | Yes |
| `draft` version | submit → version `submitted`; application `submitted` | Owner or valid delegate, `application.submit` | Valid content, active delegation if non-owner, strict deadline, transaction lock | Submitted immutable; exception requires governance | Yes |
| `submitted` application | start screening → `screening` | Program Staff, `eligibility.screen` | Exact submitted version exists | No | Yes |
| `screening` | complete eligible/ineligible → `eligible`/`ineligible` | Program Staff, `eligibility.screen` | Human rationale; validation considered; no AI decision | Governed re-review uses new screening | Yes |
| `eligible` | assign → `assigned` | Program Staff, `assignment.create` | Active Judge membership/capability; exact version; conflict precheck | No | Yes |
| `assigned` | create evaluation → `under_evaluation` | Assigned Judge, `evaluation.create` | Frozen rubric; no blocking conflict | No | Yes |
| `under_evaluation` | all required evaluations finalized → `evaluated` | Workflow transition | Required active assignments complete | No | Yes |
| `evaluated` | open deliberation → `in_deliberation` | Staff/Decision Maker, `deliberation.manage` | Controlled disclosure prerequisite | No | Yes |
| `in_deliberation` | finalize decision → `decided` | Decision Maker, `decision.finalize` | Rationale/outcome; human authority | Decision history protected | Yes |
| `decided` | record transition → `outcomed` | Decision Maker, `decision.finalize` | Outcome record exists | Supersession only | Yes |

A permitted revision creates the next `draft` version while the previous submitted version remains submitted and immutable. It does not silently alter assignment/evaluation links.

### Screening, assignment, conflict, rubric, evaluation, deliberation, decision, and outcome

| Aggregate | Transition | Authority | Mandatory conditions | Irreversible / override | Audit |
|---|---|---|---|---|---|
| Screening | none → `in_review` → `completed` | Program Staff, `eligibility.screen` | Exact version; rationale on completion | Correction creates governed successor | Yes |
| Assignment | none → `active` | Program Staff, `assignment.create` | Judge membership/capability, application exact version, precheck | No | Yes |
| Assignment | `active` → `declined`/`removed`/`completed` | Judge for decline; Staff for remove; workflow for complete | Reason for decline/remove; preserve row | New row for reassignment | Yes |
| Conflict | signal/declaration → `further_review`/`declared` | System/Judge | Signal/declaration only; no AI determination | No | Yes |
| Conflict | review → `cleared`/`non_blocking`/`blocked` | Authorized human, `conflict.determine` | Reason + authority | Successor only for reversal | Yes |
| Rubric | `draft` → `active` → `frozen` → `retired` | Authorized program governance | Criteria complete; active/frozen total weight 100; freeze before evaluation | Frozen never edited; new version/governance path | Yes |
| Evaluation | none → `draft` → `submitted` → `finalized` | Assigned Judge, evaluation permissions | Exact assignment/version/frozen rubric; scores/notes/assessment/recommendation complete | Finalized protected | Yes |
| Evaluation | `finalized` → `reopened` → successor finalization | Authorized governance, `evaluation.reopen` | Explicit reason/authority; stronger review after deliberation begins | Original preserved | Yes |
| Deliberation | none → `open` → `active` → `closed` | Staff/Decision Maker; participants | Required final evaluations and controlled disclosure | Closed append-only | Yes |
| Decision | none → `draft` → `finalized` | Decision Maker | Authorized program scope, deliberation prerequisite, rationale, outcome | Supersede only through governance | Yes |
| Outcome | none → `recorded` → `transitioned` | Decision Maker/authorized transition actor | Final decision and approved target where transition applies | Supersede only | Yes |

No direct status update is permitted outside these transition operations. Governance overrides require authorized actor, reason, timestamp, action, preserved prior state, and audit event.

## 6. Application Version Contract

1. Creation creates application version number `1` in `draft`.
2. Version numbers are positive, contiguous, monotonically increasing per application and unique on `(application_id, version_number)`.
3. The primary owner may edit drafts; a member may act only through a current, capability-specific delegation.
4. Submission freezes `content`, exact evidence links, version number, creator/submission actor, and submission timestamp.
5. A submitted version can never be updated or deleted by normal operations.
6. A revision creates a new draft version with `supersedes_version_id` referencing the prior submitted version and a required revision reason.
7. The application's `current_version_id` changes only inside the revision/submission transaction.
8. Judges evaluate the assignment's exact `application_version_id`. Later versions never retarget existing assignments/evaluations.
9. Previous submitted versions are readable only through record-level policy; immutability does not imply disclosure.
10. Ordinary submission/revision fails after `closes_at` interpreted using program timezone; a governed exception requires reason and audit.

## 7. Rubric, Evaluation, and Scoring Contract

### Separation of concepts

```text
Numerical Score ≠ Qualitative Human Judgment ≠ Judge Recommendation ≠ Final Human Decision
```

- `evaluation_criterion_scores.raw_score` and calculated contribution are numerical.
- `evaluation_criterion_scores.justification`/`evidence_note` and `evaluations.qualitative_assessment` are human judgment.
- `evaluations.recommendation` is Judge recommendation.
- `decisions.outcome` and `decisions.rationale` are the Decision Maker's formal human decision, not mechanically derived from score.

### Exact formula

For criterion $i$, with $s_i \in [0, 10]$ and percentage weight $w_i$:

$$
contribution_i = s_i \times \frac{w_i}{100}
$$

$$
weighted\_total = \sum_i contribution_i
$$

$$
score\_out\_of\_100 = weighted\_total \times 10
$$

Rules:

- `raw_score` is `numeric(4,2)`, range 0.00–10.00.
- `weight` is `numeric(5,2)` percentage points; all active/frozen criterion weights must total exactly 100.00.
- `calculated_contribution` and `calculated_total` are `numeric(5,2)` and rounded at calculation/finalization to two decimal places using half-up rounding.
- The server recalculates contributions and total on draft save, submission, finalization, and controlled reopening. Client supplied total/contribution values are ignored/rejected.
- Finalization runs inside one transaction after verifying all required criteria, frozen rubric, exact submitted application version, valid assignment, no blocking conflict, and deterministic formula.
- Persist calculated values for queries/reporting and preserve raw scores, frozen criterion weights, formula version, rubric version, and exact application version for reproducibility.

## 8. Conflict Contract

### Approved model

- System detection may create an objective signal.
- Judge declaration records known/suspected conflict.
- Authorized human determination sets effective status.
- `blocked` is an authorization restriction, not a warning.
- A blocked Judge cannot create, update, submit, or finalize evaluation, or participate in restricted deliberation.
- Reassignment creates/ends assignment history; it never rewrites prior assignment/conflict history.
- AI cannot determine conflicts.

### Category policy

**OWNER DECISION REQUIRED:** conflict categories and indirect-affiliation/waiver semantics are not enumerated in D-008–D-050.

**RECOMMENDED — NOT YET APPROVED:** categories `FINANCIAL`, `EMPLOYMENT`, `FAMILY_OR_PERSONAL`, `ADVISORY`, `COMPETITIVE`, `PRIOR_COLLABORATION`, and `OTHER`, each with a free-text explanation and evidence where available.

## 9. RBAC and Policy Contract

For all sensitive operations, policy evaluates the following in order:

1. authenticated/eligible user;
2. active Program Membership for program-bound action;
3. EAIC role/capability, separate from inherited starter role;
4. stage restriction when configured;
5. singular `resource.action` permission;
6. exact assignment, primary ownership, membership, or delegation;
7. record-level visibility/state policy;
8. conflict restriction for Judge actions;
9. governance/protected-history restriction.

| Sensitive action | Actor | Scope/policy requirement |
|---|---|---|
| Program configuration/publication | Program Staff | active program membership + capability + `program.update`/`program.publish` + transition policy |
| Application draft/edit/submit/revise | Primary owner or delegated member | owner or unexpired delegation + current program window/state + application policy |
| Screening | Program Staff | active membership/capability + program/application policy; human rationale |
| Judge evaluation | Judge | active membership + Judge capability + explicit active assignment + exact submitted version + frozen rubric + no blocked conflict |
| Peer evaluation visibility | Judge/participant | controlled disclosure row and deliberation participation; otherwise deny |
| Conflict determination | Authorized Staff/governance actor | program scope + `conflict.determine` + reason/audit |
| Decision finalization | Decision Maker | separate active authority + program/application policy + deliberation prerequisite + rationale/outcome |
| Protected record change | Governance actor | ordinary action denied; governed override with preserved history and audit |
| Applicant feedback/notification | Applicant | own primary/member application + applicant visibility tier |

## 10. Immutability and Delete Contract

| Entity | Mutable until | Protected after | Deletion policy | History required |
|---|---|---|---|---|
| Application version | Draft | Submission | Draft only before dependency; submitted never normal delete | version/submission/revision/audit |
| Submitted evidence link | Draft version | Submission | Restrict after submitted/evaluated | attachment/audit |
| Rubric | Draft/configurable | dependent version active/frozen | Restrict after version/evaluation | version/audit |
| Rubric version/criteria | Draft | Frozen | Never mutate frozen; successor version | freeze/version/audit |
| Evaluation/scores | Draft | Finalization | Restrict after score/submission | submission/finalization/reopen/supersession |
| Deliberation | Open/active | Close | Restrict after decision | participants/disclosure/closure |
| Decision | Draft | Finalization | Restrict; supersede only | rationale/finalize/supersede |
| Outcome/transition | Recorded | Transition | Restrict; supersede only | target/transition/supersede |
| Conflict | Before determination | Determination | Never normal delete | signal/declaration/determination |
| Judge assignment | Active | End/completion | End/reassign, do not delete | assign/decline/remove/reassign |
| Program/application membership | Active | End | End/revoke, do not delete after history | grants/scope/delegation/end |
| Activity event | Never normal mutation | Creation | Never normal delete | append-only |
| Notification | Send time | Send | no content rewrite; read state may update | send/read |

## 11. Index, Constraint, and Concurrency Contract

### Exact constraints

- Program unique `code` and `slug`.
- Active program membership unique per program/user/capability.
- Active application member unique per application/user.
- Active delegation unique per application member/permission.
- Application versions unique per application/version number.
- Active Judge assignment unique per application version/Judge.
- Current unresolved conflict unique per assignment.
- Rubric version unique per rubric/version number; exactly one active version per rubric.
- Criterion key and position unique per rubric version.
- Active evaluation unique per Judge assignment/rubric version.
- Criterion score unique per evaluation/criterion.
- At most one active deliberation per application.
- At most one active/finalized decision per application.
- At most one active outcome transition per decision.
- Numeric checks: raw scores $[0,10]$, weights non-negative, totals $[0,100]$.
- Temporal checks: version number > 0, criterion position >= 0, deadline opens before closes, expiry after grant, required finalization/submission actor/time present.

### Concurrency rules

- Submission/revision locks the application row or uses optimistic version state, checks deadline, allocates next version number, writes immutable snapshot, moves current pointer, then emits audit/notification after commit.
- Assignment/reassignment locks application/version assignment context and rejects a second active assignment for the same Judge/version.
- Evaluation finalization locks evaluation and score rows, recalculates server total, rechecks conflict/assignment/rubric, and writes one finalization event.
- Decision/outcome finalization locks current decision/outcome context and prevents duplicate active decision/transition.
- Notification and audit event creation uses domain event/idempotency identity on retry-capable commands so one committed transition produces one authoritative notification/audit event.

## 12. PostgreSQL Contract

### Decided

- PostgreSQL is primary.
- Use timezone-aware timestamps.
- Numeric types are preferred for scoring calculations.
- JSONB is only for justified variable metadata.
- Authoritative state remains relational.

### RECOMMENDED — NOT YET APPROVED

- Use bigint keys matching existing Laravel starter migrations; no UUID/ULID is required for the MVP.
- Use PostgreSQL partial unique indexes for active/current relationship constraints.
- Use `timestamptz`, store IANA timezone in `programs.timezone`, and compare deadlines in UTC while retaining configured timezone.
- Use `numeric(4,2)` raw score, `numeric(5,2)` weight/contribution/total, and half-up two-decimal calculated values.
- Use constrained strings plus application transition guards rather than database enums.
- Use JSONB for `content`, variable validation result/metadata, and non-query-critical evidence properties only.
- Use restrictive foreign keys for trust-critical history and limited draft-only deletion rather than broad cascade/soft deletion.

## 13. Migration Dependency Graph

```text
Existing starter: users, Spatie tables, media, notifications, activity_logs
        ↓
programs
        ↓
program_memberships + program_eligibility_rules + rubrics
        ↓
rubric_versions
        ↓
rubric_criteria
        ↓
applications
        ↓
application_members + application_versions
        ↓
application_member_delegations + application_version_media
        ↓
application_validations + screenings
        ↓
judge_assignments
        ↓
conflicts
        ↓
evaluations
        ↓
evaluation_criterion_scores
        ↓
deliberations
        ↓
deliberation_participants + deliberation_disclosures
        ↓
decisions
        ↓
outcome_transitions
```

Safe migration grouping:

1. Programs, memberships, eligibility rules, rubrics.
2. Rubric versions and criteria.
3. Applications, members, versions, delegation, evidence association.
4. Validation, screening, assignment, conflicts.
5. Evaluations and criterion scores.
6. Deliberation, decision, outcome.

**Approved first migration batch:** only `programs`, `program_memberships`, `program_eligibility_rules`, and `rubrics`. `rubric_versions`/criteria, applications, and all consequential workflow tables are deliberately deferred to dependent batches.

`applications.current_version_id` requires a deferred/additional foreign key after `application_versions` exists. `outcome_transitions.next_program_id` may be created with the table because `programs` already exists. Do not duplicate notification/audit migrations.

## 14. Database Acceptance-Test Contract

| ID | Scenario | Setup | Action | Expected result |
|---|---|---|---|---|
| SC-01 | FK integrity | No Program with target ID | Create Application | Foreign-key rejection; no row |
| SC-02 | Membership uniqueness | Active Program Membership | Insert same program/user/capability | Duplicate active membership rejected |
| SC-03 | Owner/member isolation | Application has owner/member | Member invokes owner-only action without delegation | Denied; no mutation |
| SC-04 | Delegation | Active member and specific valid delegation | Member executes delegated action | Allowed only until expiry/revocation; audit created |
| SC-05 | Version numbering | Existing version 1 | Create another version 1 | Unique rejection |
| SC-06 | Submission immutability | Submitted version | Update content/evidence link | Denied; snapshot unchanged |
| SC-07 | Exact evaluation version | Assignment references submitted v1; v2 exists | Judge creates evaluation for v2 | Denied; v1 remains reference |
| SC-08 | Deadline concurrency | Program closes now; draft ready | Submit competing on-time/late requests | Exactly one valid result according to locked UTC deadline |
| SC-09 | Validation/human screening | Validation fails objective rule | Staff completes screening | Staff may record auditable human result; no AI final decision |
| SC-10 | Assignment uniqueness | Active Judge assignment | Create same Judge/version assignment | Duplicate active assignment rejected/idempotent |
| SC-11 | Conflict blocking | Active assignment has `blocked` conflict | Judge creates/submits/finalizes evaluation | Denied; no evaluation mutation |
| SC-12 | Rubric weight total | Draft rubric with weights not 100 | Activate/freeze | Transaction rejected |
| SC-13 | Frozen rubric | Frozen version used by evaluation | Modify criterion/weight | Denied; successor version required |
| SC-14 | Score range | Evaluation draft | Persist -0.01 or 10.01 raw score | Check/validation rejection |
| SC-15 | Deterministic calculation | Scores/weights totaling 100 | Finalize evaluation | Server persists formula result to two decimals; client total ignored |
| SC-16 | Score uniqueness | Evaluation already has criterion score | Add second score same criterion | Unique rejection |
| SC-17 | Evaluation confidentiality | Judge A and Judge B; no disclosure | Judge B fetches A evaluation | Denied |
| SC-18 | Finalization protection | Finalized evaluation | Normal update | Denied; record unchanged |
| SC-19 | Controlled reopening | Finalized evaluation | Reopen without authorized actor/reason | Denied; no history mutation |
| SC-20 | Deliberation prerequisite | Required evaluations unfinished | Open deliberation | Denied |
| SC-21 | Deliberation history | Closed deliberation | Alter original evaluation | Denied; evaluation remains historical |
| SC-22 | Decision authority | Authorized and unauthorized actors | Finalize decision | Only Decision Maker in scope succeeds with rationale/outcome |
| SC-23 | Decision independence | High and low evaluation totals | Create decision | No automatic accept/reject; human rationale/outcome required |
| SC-24 | Decision uniqueness | Finalized active decision | Create another active decision | Rejected unless governed supersession |
| SC-25 | Outcome validity | Finalized decision | Record invalid outcome | Check/validation rejection |
| SC-26 | Post-decision history | Final decision/outcome | Delete application | Restricted; application retained |
| SC-27 | Notification after commit | Decision transaction deliberately rolls back | Trigger notification | No authoritative notification emitted |
| SC-28 | Email delivery resilience | In-app notification committed; email fails | Process delivery | In-app notification remains authoritative |
| SC-29 | Audit append-only | Consequential transition | Attempt normal audit update/delete | Denied; transition actor/time/reason preserved |
| SC-30 | Governance override | Protected evaluation/decision | Exceptional change | Requires authority, reason, preserved prior state, and audit |
| SC-31 | PostgreSQL schema | PostgreSQL `development` baseline | Apply MVP migrations in future task | All constraints/indexes/types migrate successfully |
| SC-32 | SQLite compatibility | SQLite test database | Run supported domain tests | Tests pass without PostgreSQL-only migration/query assumptions |

## OWNER DECISION REQUIRED

1. Exact literal permission catalog and role-to-permission assignments under the approved singular convention.
2. Capability values and exact `program_memberships`/stage-scope cardinality.
3. Exact public Program fields and whether application initiation requires authentication.
4. Exact team lead/invitation and submission-on-behalf details beyond approved bounded delegation.
5. Exact lifecycle transition prerequisites for withdrawal, appeal, revision reopening, and any states beyond the values in Section 5.
6. Conflict categories, indirect-affiliation rule, waiver policy, determining authority, and field-level disclosure.
7. Exact application content/question schema and eligibility rule types.
8. Exact field-level evaluation/disclosure policy for applicants, Judges, Staff, and Decision Makers.
9. Exact notification event catalog, recipients, timing, email policy, and retry mechanics.
10. Exact configured next-stage/program transition targets and additional outcome metadata.
11. Whether a separate Judge profile is needed beyond User + Membership + capability.

## RECOMMENDED — NOT YET APPROVED

1. Bigint primary keys matching the Laravel starter.
2. Exact table/column names and relationships in Section 3.
3. Partial unique indexes for active/current rows.
4. `numeric(4,2)` score and `numeric(5,2)` weight/contribution/total, with half-up two-decimal calculation.
5. IANA timezone string plus `timestamptz` timestamps.
6. JSONB columns identified as variable metadata only.
7. Constrained strings instead of PostgreSQL enums.
8. Draft-only hard deletion and restrictive history foreign keys; no blanket soft deletion.
9. Application-level assignment as the MVP scope implementation.
10. Domain event/idempotency identity for retried transition notification/audit emission.

## 15. Implementation Gate

A later implementation task may create migrations only after the Product & Technical Controller accepts this contract and either resolves or explicitly defers each `OWNER DECISION REQUIRED` item that affects the first migration batch. That task must implement incrementally, write targeted Pest coverage, validate SQLite and PostgreSQL, record a new handoff, and stop for review before proceeding to models/workflows.
