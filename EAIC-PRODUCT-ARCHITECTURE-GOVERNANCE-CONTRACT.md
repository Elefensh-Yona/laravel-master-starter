# Ethiopian AI Center (EAIC)
## Product, Architecture, Governance & Evaluation Contract

**Status:** Reconciled against the EAIC Blueprint and authoritative EAIC decisions recorded in `TheRoadmap/decisions.md`.  
**Prepared in:** Phase 1, Task 010  
**Authority rule:** The approved Product & Technical Controller decisions are authoritative for EAIC domain behavior. The inherited Laravel Master Starter remains authoritative for existing technical infrastructure. Source code is authoritative for current implementation facts.

> **Authority note:** The supplied Blueprint contains decisions 1–35. `TheRoadmap/decisions.md` is the authoritative EAIC decision record and contains EAIC decisions D-008 through D-050. D-031 through D-050 are reconciled below; D-051 is not present and was not invented.

## 1. Product Identity

- Official full name: **Ethiopian AI Center**.
- Official short identifier: **EAIC**.
- EAIC is the downstream product/domain project.
- The inherited **Laravel Master Starter** is a separate domain-neutral foundation. Its authentication, RBAC, activity logging, notifications, media, settings, search, import/export, API, Vue/Inertia frontend, and testing conventions are reused rather than replaced.
- The former `AILH` identity is historical terminology only. It must not be used for new EAIC work.

## 2. Actors and Authority

### 2.1 Super Admin / System Administrator

**Status: APPROVED by Blueprint decisions 10–11.** This actor has broad system administration authority, but cannot rewrite trust-critical business history. Governance overrides require a formal, explicit, reasoned, auditable path.

### 2.2 Program Staff / Program Administrator

**Status: APPROVED by Blueprint decisions 4–5, 8–9, 12–14, and 17.** This actor configures and publishes programs, manages stages and eligibility rules, performs human Staff screening, manages judge assignment and conflict workflows, and supports deliberation within authorized program scope.

### 2.3 Decision Maker

**Status: APPROVED as a separate authority by Blueprint decision 1.** The Decision Maker records the final human outcome after evidence-informed deliberation. AI cannot replace this authority.

### 2.4 Judge / Evaluator

**Status: APPROVED actor concept.** A Judge reviews assigned applications and evidence, declares conflicts, evaluates independently against the applicable frozen rubric, provides justification/evidence, submits a recommendation, and participates in controlled deliberation.

### 2.5 Applicant / Innovator

**Status: APPROVED by Blueprint decision 15.** An Applicant may be an Individual, Team, or Organization. Applicants discover eligible programs, create and revise their own applications, submit evidence, monitor permitted status, and receive authorized notifications.

### 2.6 Other actors

Mentors, partners/vendors/stakeholders, and other lifecycle actors are documented for later phases. They are outside the MVP contract. No new role is created by this document.

### Authority principles

- Human authority remains final for eligibility outcomes, shortlist decisions, evaluation outcomes, conflict determinations, final selection, and other consequential decisions.
- A score, recommendation, or AI output is evidence for human decision-making, not the decision itself.
- Every consequential action must be attributable, timestamped, reasoned where applicable, and reconstructable.

## 3. Authorization Architecture

EAIC authorization is layered. A request is allowed only when every required layer succeeds; a failed layer denies access.

| Layer | Responsibility | Example |
|---|---|---|
| User | Establish authenticated identity and account state | The request belongs to a verified user |
| Program membership | Establish participation in a specific program | Staff is assigned to Program A |
| Role/capability | Establish the actor's approved responsibility | User has Judge capability |
| Stage scope | Limit capability to an approved lifecycle stage | User may screen but not decide |
| Domain/action permission | Name the permitted action | `applications.screen` or `evaluations.finalize` |
| Assignment/ownership | Limit records to assigned or owned records | Judge is assigned to Application X; applicant owns Application Y |
| Record-level policy | Evaluate object-specific conditions and confidentiality | A judge is not conflicted and the application belongs to their assignment |
| Allow/deny result | Produce the final authorization decision | Authorized action proceeds; otherwise web/API denial is returned |

The existing starter provides global users, roles, permissions, middleware, and policies. EAIC must add domain scope and record policies without creating a parallel authorization system. Route middleware alone is insufficient for record-level confidentiality.

**APPROVED by Blueprint decisions 2–9:** users may hold multiple EAIC roles and participate in multiple programs. Program scope uses membership + role + permission + policy; stage scope uses hybrid restrictions where applicable; Judge scope uses membership + Judge role + assignment + policy; applicant scope uses primary owner + application members + policy. Exact storage/schema mechanics remain implementation work and must not be inferred as Spatie teams.

## 4. Program Model

### Lifecycle

The MVP program path is: draft/configuration, publication/opening, application window, screening/evaluation operation, closure, and subsequent outcome handling. Program visibility follows a hybrid publication/lifecycle model (Blueprint decision 13).

### Visibility

Programs require a public announcement/discovery experience, while internal configuration and operational records require authenticated authorization. The starter currently has an authenticated-first surface and no public EAIC program routes.

Exact public fields and application-initiation authentication rules remain **OWNER DECISION REQUIRED**; the approved direction is a hybrid publication/lifecycle visibility model (D-013).

### Membership and scope

Program membership is the primary mechanism for program scope and carries program-specific authority and lifecycle status. Removing membership prevents new actions while preserving historical actions (D-016). Judge access is assignment-driven; applicant access is ownership-driven. Exact physical schema remains implementation work.

### Eligibility

Eligibility is program-controlled. The program defines the approved applicant types and objective eligibility rules. Automated checks may validate objective conditions, but consequential eligibility outcomes remain under human Program Staff authority.

The approved applicant types are Individual, Team, and Organization. Exact eligibility rule fields and evaluation mechanics remain **OWNER DECISION REQUIRED**.

## 5. Application Model

### Applicant types and ownership

- **Individual:** one applicant owns the application.
- **Team:** approved members participate; a primary owner remains distinct from membership (D-019).
- **Organization:** an approved applicant type with multiple approved members; exact organization representation mechanics remain implementation detail requiring schema approval (D-019).
- **Application members:** membership determines who may view or edit an application, subject to approved ownership and role policy.

Primary owner plus application members and record-level policy define applicant scope (D-018/D-019). Owners may delegate specific permitted actions to approved members; delegation is capability-specific, revocable, expiring, audited, and never unrestricted ownership (D-020). Exact invitation and submission-on-behalf mechanics remain **OWNER DECISION REQUIRED**.

### Lifecycle

EAIC uses explicit lifecycle state machines. Every transition requires actor authority, permission, current state, prerequisites, program policy, conflict restrictions, and governance rules (D-047). Exact literal state names and transition details remain implementation specification work.

### Revisions and immutable history

Draft changes are permitted until the approved submission lock. A submitted version must be historically reconstructable and must not be silently mutated. A later permitted revision must create a new controlled version with actor/time/reason history and an unambiguous authoritative version.

Submitted versions are immutable and judging references the exact submitted version (D-021). Revisions follow a controlled lifecycle with history. Deadlines are program-configurable, timezone-aware, strictly closing, and may change only through governed exceptions (D-036). Exact withdrawal, appeal, and reopen behavior remains **OWNER DECISION REQUIRED**.

## 6. Eligibility and Screening

1. The system may perform automated objective validation against program-controlled rules.
2. Program Staff performs human screening and records the screening result and rationale.
3. Automated validation does not become a final consequential eligibility decision without the approved human authority.
4. Screening history must preserve actor, timestamp, outcome, reason, and relevant evidence.
5. Screening access is subject to program and record scope.

Exact screening states, result categories, correction/review behavior, and applicant visibility are **OWNER DECISION REQUIRED**.

## 7. Judge Assignment

- Judge assignments are program-scoped and must identify the applicable stage and/or application scope.
- Assignment must be conflict-aware before evaluation access is granted.
- Authorized Program Staff manages assignment and approved reassignment.
- Assignment history must preserve assignment, reassignment, decline, removal, actor, timestamp, and reason.
- A Judge may access only records within the approved assignment scope and must not infer access from a broad global permission.

Judge scope combines program membership, Judge role, assignment, and policy (decision 6). Reassignment is controlled, conflict-aware, and historical (decision 29). Exact assignment precedence, decline behavior, and blocking point remain **OWNER DECISION REQUIRED**.

## 8. Conflict of Interest

EAIC uses a hybrid conflict model:

1. The system detects objective conflict signals where approved data permits.
2. The Judge declares known or suspected conflicts.
3. An authorized human actor makes a controlled determination.
4. A blocking conflict removes or prevents the affected Judge's evaluation and other restricted participation.
5. The assignment is reassigned through an authorized workflow.
6. The declaration, detected signal, determination, actor, reason, timestamps, and changes are audited and historically preserved.

AI may assist with signals only if its inputs, provenance, review, and limits are approved. AI cannot resolve a conflict.

Conflict handling is approved as detection + declaration + controlled determination (decision 19). Blocking conflicts become authorization restrictions (Blueprint Trust & governance). Exact categories, waiver policy, disclosure, and determination authority remain **OWNER DECISION REQUIRED**.

## 9. Rubric

- A rubric has a lifecycle and version identity.
- Evaluation uses the rubric version approved for the relevant program/stage.
- Once evaluations depend on a rubric version, that version and its criteria are protected from silent mutation.
- A governed exceptional change must create a new version or an explicit audited exception; it must not rewrite historical evaluation meaning.
- Evaluations retain the rubric version used for calculation and justification.

Rubrics have a controlled lifecycle, versioning, and freeze (decision 20). A rubric version is frozen before evaluations depend on it. Exact scoring scale, weights, precision, and governed exception mechanics remain **OWNER DECISION REQUIRED**.

## 10. Evaluation Architecture

Each independent Judge evaluation consists of:

- weighted scores for the approved criteria;
- deterministic mathematical calculation by the system;
- Judge justification and supporting evidence;
- qualitative human assessment;
- Judge recommendation;
- aggregate statistics only at the approved disclosure point; and
- disagreement visibility for authorized deliberation participants.

The system calculates the mathematical total. Judges do **not** manually override the calculated total. Scores, comments, evidence, rubric version, evaluator, and timestamps are preserved.

There is no automatic final decision from score arithmetic. Scores inform human deliberation and the Decision Maker's evidence-informed outcome.

The approved evaluation model includes weighted deterministic scoring, criterion justification/evidence, separate qualitative assessment, separate Judge recommendation, private independent evaluations until controlled disclosure, and mean, median, spread, and disagreement visibility for deliberation (decision 30 and Blueprint evaluation model). Exact scale, precision, disclosure point, and presentation remain **OWNER DECISION REQUIRED**.

## 11. Evaluation Finalization

- **Draft:** Judge may edit within the permitted workflow.
- **Submitted:** Judge has submitted the evaluation for finalization processing according to the approved rule.
- **Finalized:** scores, justification, evidence, rubric version, actor, and time are protected from silent mutation.
- **Reopened:** only through a controlled, authorized operation with reason, actor, timestamp, history, and audit event.
- Once deliberation begins, evaluation protection becomes stronger; original evaluations remain historical records even if a controlled correction is approved.

Finalization is locked; reopening is controlled and historical (decision 21). Exact submitted/finalized transition and reopen authority remain **OWNER DECISION REQUIRED**.

## 12. Deliberation

- Deliberation is a controlled human process entered only when approved prerequisites are met.
- Disclosure is limited to authorized participants and the approved stage of the process.
- Judges may participate within their approved assignment/program scope.
- Authorized participants may review disagreement and aggregate information without rewriting original independent evaluations.
- Original evaluations remain historical records.
- The Decision Maker holds the final human authority for the outcome.

Deliberation is structured, human-led, and evidence-informed; controlled disclosure occurs at the appropriate point and original evaluations remain historical records without being rewritten (D-031). The exact participant set, private-note rules, quorum/closure conditions, and applicant field visibility remain **OWNER DECISION REQUIRED**.

## 13. Final Decision

The Decision Maker records an explicit human outcome informed by the application, screening, evidence, rubric-based evaluations, disagreement analysis, and deliberation.

A decision record includes at least:

- authorized decision actor;
- explicit outcome;
- rationale;
- relevant evidence/context;
- timestamp;
- finalization state; and
- audit/history linkage.

The decision is not generated automatically from scores and cannot be delegated to AI. Reversal or change requires a governance-controlled, reasoned, audited operation that preserves the prior decision.

The approved MVP outcomes are `ACCEPTED`, `REJECTED`, `WAITLISTED`, and `REVISION_REQUIRED` (D-050). Final decisions are human Decision Maker records with rationale, are not mechanically derived from scores, and governed reversal/supersession preserves history (D-032). Exact finalization mechanics, transition targets, and applicant-facing rationale fields remain **OWNER DECISION REQUIRED**.

## 14. Post-Decision

A finalized outcome records one of the approved outcome values and may trigger an explicit configured next-stage/program transition. Applications are not deleted after decision; prior application, screening, assignment, conflict, rubric, evaluation, deliberation, and decision history remains intact (D-033, D-050). The applicant receives an authorized notification after the finalized outcome.

Incubation, mentorship, milestones, resources, events, partners, and alumni are deferred lifecycle modules and are not part of this MVP contract.

## 15. Applicant Transparency and Information Tiers

| Tier | Visibility |
|---|---|
| Applicant-visible | Public program information, own application/draft/submission status, approved communications, and approved outcome information |
| Program-internal | Authorized staff operational data, screening details, assignment state, and internal workflow records |
| Judge-confidential | Judge's own draft/final evaluation, private justification, conflict declaration details, and other restricted judge material |
| Governance/audit | Protected event history, overrides, reasons, actors, timestamps, and reconstructive records for authorized governance users |

These tiers are the approved conceptual model. Exact field-level disclosure is **OWNER DECISION REQUIRED**, especially for scores, judge identity, rationale, conflict details, internal notes, and evidence/media.

## 16. Notifications

- In-app notification is the authoritative communication record.
- Email is a delivery channel, not the authoritative state record.
- Notification creation and viewing must obey authorization and confidentiality rules.
- Important events include publication, application submission, screening outcome, assignment, conflict action, evaluation finalization where appropriate, deliberation/decision outcome, and approved post-decision transitions.
- Notifications are emitted after the relevant state mutation is committed and must not expose restricted information.

In-app notification is authoritative and email is a delivery channel (D-035). Delivery failure does not remove the authoritative in-app record. Exact event catalog, recipient rules, timing, email enablement, failure/retry behavior, and notification field visibility remain **OWNER DECISION REQUIRED**.

## 17. Audit and Governance

Every consequential event must be reconstructable through the inherited `ActivityLogger`/activity-log foundation plus immutable domain history where required. An audit record must identify:

- actor;
- action/event;
- target/record;
- timestamp;
- reason or rationale where applicable;
- relevant before/after or version context;
- governance override status; and
- related notification or workflow transition where applicable.

Required examples include submission, screening result, assignment/reassignment, conflict declaration/determination, rubric activation/freeze, evaluation submission/finalization/reopen, deliberation closure, decision finalization, reversal, and notification-triggering outcome.

Activity logging must not be treated as permission to rewrite protected history. Governance overrides preserve the original record and explain the exception.

## 18. AI Boundaries and Governance

AI is advisory only at consequential decision points. EAIC must not allow AI to autonomously determine:

- final eligibility;
- shortlist decisions;
- final Judge scores;
- conflict resolution;
- final selection;
- resource allocation; or
- final incubation/mentorship outcomes.

AI may summarize evidence, identify patterns, highlight disagreement, assist workflow, and organize information, but remains advisory only for consequential decisions (D-039). Future AI features require provider/data boundaries, privacy controls, provenance/source links, model/provider and prompt/version recording, permissions, retention, prompt-injection/data-leakage protections, human review state, and auditable governance actions. AI output must not silently mutate domain records or replace human authority.

AI is deferred from the deterministic MVP unless a separately approved, non-consequential capability is added without weakening these controls.

## 19. MVP Boundary

### MVP

```text
Program
→ Application
→ Eligibility
→ Submission
→ Automated validation
→ Staff screening
→ Judge assignment
→ Conflict check
→ Frozen rubric
→ Independent evaluation
→ Finalization
→ Controlled disclosure
→ Deliberation
→ Decision Maker
→ Outcome
→ Applicant notification
→ Audit
```

The MVP must support one complete, auditable path through the approved actor scopes and information tiers. It must include positive and negative authorization tests, immutable-history tests, conflict blocking, mathematical score calculation, human decision authority, notification confidentiality, and audit reconstruction.

### Deferred later lifecycle modules

- Incubation operations.
- Mentorship management.
- Milestones and progress tracking.
- Resources and workspaces.
- Events, training, and showcase depth.
- Partners/vendors/stakeholders.
- Alumni and post-program follow-up.
- Advanced analytics/reporting.
- Broad applicant/staff/mentor AI assistants.
- Autonomous or agentic decision systems.

## 20. Open Questions and Uncertainties

The following remain genuinely unresolved after reconciliation with the complete available EAIC decision record D-008 through D-050:

1. **OWNER DECISION REQUIRED:** exact MVP permission strings and role-to-permission matrix; D-014 approves the naming convention, not the complete catalog or assignment.
2. **OWNER DECISION REQUIRED:** exact public fields and application-initiation authentication boundary.
3. **OWNER DECISION REQUIRED:** exact team-lead, invitation, delegation, and submission-on-behalf mechanics; D-020 decides delegation must be specific, revocable, expiring, audited, and non-ownership.
4. **OWNER DECISION REQUIRED:** exact lifecycle state names and complete transition preconditions; D-047 requires explicit state machines, but does not enumerate literal states. Withdrawal, reopening, and appeal rules remain open where not specified.
5. **OWNER DECISION REQUIRED:** exact judge assignment granularity, precedence, decline behavior, and conflict blocking point.
6. **OWNER DECISION REQUIRED:** conflict categories, indirect-affiliation semantics, waiver policy, determination authority, and disclosure.
7. **OWNER DECISION REQUIRED:** exact rubric scoring precision, rounding, criterion weight representation, and exceptional-change mechanics; D-028 decides 0–10, 100% weights, weighted calculation, and normalized 100-point result.
8. **OWNER DECISION REQUIRED:** exact applicant-facing score, Judge identity, rationale, evidence, and internal-note disclosure.
9. **OWNER DECISION REQUIRED:** notification event catalog, recipients, timing, email enablement, and failure/retry rules.
10. **OWNER DECISION REQUIRED:** PostgreSQL schema details and migration-ready table/constraint decisions. Current verified baseline uses database `development` and PostgreSQL `public`; no EAIC domain tables exist.
11. **OWNER DECISION REQUIRED:** exact EAIC namespace/module structure. No domain namespace currently exists in source.
12. **OWNER DECISION REQUIRED:** exact program-specific transition targets and any additional outcome metadata; D-050 approves the four MVP outcome values.

Items already explicitly decided and therefore not reopened here:

- EAIC is the current project identity.
- The Master Starter is reused rather than replaced.
- Human authority remains final for consequential outcomes.
- AI is advisory only and cannot perform the prohibited autonomous decisions listed above.
- The MVP follows the vertical slice in Section 19, including Individual/Team/Organization applications, controlled revisions, timezone-aware deadlines, weighted deterministic scoring, controlled disclosure, and focused PostgreSQL verification.
- The authoritative decision record confirms the permission naming convention, explicit program membership, ownership/member/delegation boundaries, immutable versions, and the 0–10 weighted normalized scoring model.
- Decision Maker is a separate authority from Program Staff; users may hold multiple EAIC roles and participate in multiple programs; scope is layered through membership, role/capability, permission, stage, assignment/ownership, and policy.
- Application versions submitted for judging are immutable and judging references the exact version; judge reassignment is controlled, conflict-aware, and historical.
- Deliberation is structured, human-led, and evidence-informed; the Decision Maker makes the final human decision with rationale; outcomes and transitions are separate, controlled, and preserve application history.
- Notifications are event-driven with authoritative in-app records and email as a delivery channel; delivery failure does not remove the in-app record.
- PostgreSQL is the primary EAIC database. Timezone-aware timestamps, numeric scoring types, justified JSONB, and relational state are required design directions.
- EAIC uses explicit state machines; scores are evidence rather than automatic decisions; individual Judge evaluations remain confidential until controlled disclosure.
- Later lifecycle modules remain deferred.

## 21. Implementation Dependency Map

```text
Approved EAIC Contract
        ↓
MVP Database Schema
        ↓
Acceptance Test Specification
        ↓
Migrations / Models
        ↓
Policies / Workflow Services
        ↓
UI / API
        ↓
End-to-End Acceptance
```

No step in this map was implemented by this interaction. The available decision record and Blueprint are reconciled through D-050; the remaining implementation-level approvals must be completed before implementation begins.

## 22. Implementation Guardrails

- Do not create EAIC migrations, models, controllers, routes, policies, roles, permissions, workflow code, frontend pages, or domain seed data from this contract until the controller approves the unresolved items.
- Do not modify Master Starter migrations, database objects, `.env`, packages, or historical handoffs as part of contract work.
- Do not rename the PostgreSQL `development` database or `public` schema.
- Do not use score arithmetic or AI output as an automatic final decision.
- Do not expose confidential evaluations, conflicts, evidence, notes, or audit data outside approved scope.
- Do not rewrite historical handoffs or historical domain records.

## 23. Contract Approval Gate

This contract becomes the implementation authority only when the Product & Technical Controller has:

1. approved the role/capability and scope implementation matrix and exact permission assignments;
2. approved the MVP state-transition matrix and remaining open questions;
3. approved the submission, rubric, evaluation, decision, privacy, notification, and audit implementation details;
4. approved the migration-ready MVP schema; and
5. approved the acceptance-test specification.

Until then, this document is a controlled contract draft, not permission to implement unresolved behavior.
