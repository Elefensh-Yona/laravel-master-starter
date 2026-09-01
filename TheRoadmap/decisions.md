# Decisions

This file records short architectural and workflow decisions so future implementation stays consistent.

## D-001: Boilerplate Identity Model

Decision:

- the boilerplate is `users`-centric

Reason:

- `users` is the most reusable identity model across Laravel applications
- `staff` is a downstream domain concept, not a universal starter requirement

Implication:

- V1 will build around `users`, `roles`, `permissions`, `notifications`, and `activity logs`

## D-002: Boilerplate Scope

Decision:

- V1 is domain-neutral and excludes asset-management modules

Reason:

- the goal is a reusable Laravel base, not a clone of the previous project

Implication:

- no assets, warranties, maintenance, procurement, audits, or domain-specific reports in V1

## D-003: Folder Structure

Decision:

- keep the Laravel 12 default project structure unless a clear need appears

Reason:

- Laravel conventions reduce friction
- future developers can navigate standard structure faster

Implication:

- no custom top-level architecture folders unless the value is clear and approved

## D-004: Documentation Workflow

Decision:

- implementation progress must update both task tracking and learning notes

Reason:

- the project is both a boilerplate and a Laravel learning path

Implication:

- update `BoilerplateTaskList.md` when work is completed
- append explanations to `laravelbasics.md` after meaningful implementation steps

## D-005: Git Workflow

Decision:

- use one branch per phase and commit at logical checkpoints

Reason:

- it keeps milestones clean and reviewable

Implication:

- each phase starts with a new branch
- each meaningful task batch should be committed and pushed

## D-006: RBAC Is Permission-Driven, Not Cosmetic

Decision:

- roles and permissions must control real access to routes, sidebar items, page actions, CRUD operations, print/export actions, and future module capabilities

Reason:

- role-based apps become unreliable when the sidebar, pages, and actions drift apart
- the same RBAC system should drive both backend authorization and frontend visibility

Implication:

- every new module should define explicit permissions
- every new page or action should be tied to permission checks
- Admin manages role access through the UI rather than hardcoded conditions for normal changes

## D-007: Seeded Role Demo Accounts Are Required

Decision:

- the boilerplate should ship with dedicated demo credentials for each default role

Reason:

- it makes role testing immediate
- it prevents confusion when verifying whether RBAC really works

Implication:

- default seeders should create clearly labeled role-based test accounts
- tests and manual QA can validate access differences quickly



# EAIC Product Decisions

This section records the approved Ethiopian AI Center (EAIC) product, architecture, governance, and workflow decisions.

These decisions apply to the EAIC domain built on top of the Laravel Master Starter.

The EAIC Product Architecture, Governance & Evaluation Blueprint and the EAIC Product & Architecture Governance Contract are authoritative references for these decisions.

## D-008: EAIC Product Identity

Decision:

- the product identity is Ethiopian AI Center (EAIC)

Reason:

- EAIC is the intended product/platform identity for the innovation lifecycle system

Implication:

- EAIC terminology should be used throughout the product-facing domain
- the inherited Laravel Master Starter identity remains historical/infrastructure context unless explicitly changed

## D-009: EAIC Multi-Program Architecture

Decision:

- EAIC is a multi-program innovation lifecycle platform

Reason:

- the platform must support multiple programs rather than a single competition/application workflow

Implication:

- program scope is fundamental to authorization, applications, judging, decisions, and transitions

## D-010: Decision Maker Authority

Decision:

- Decision Maker is a distinct authority from Program Staff

Reason:

- operational program management and final consequential decisions must remain separate

Implication:

- Decision Maker authority must be explicitly represented and authorized
- Program Staff must not automatically receive Decision Maker authority

## D-011: Multiple Roles and Capabilities

Decision:

- a user may hold multiple EAIC roles/capabilities

Reason:

- users may participate in different capacities across the platform

Implication:

- authorization must not assume one permanent role per user

## D-012: Program-Specific Authority

Decision:

- EAIC authority is program-scoped where applicable

Reason:

- a user may have different responsibilities in different programs

Implication:

- program membership and scope must participate in authorization decisions

## D-013: Layered Authorization

Decision:

- EAIC authorization uses layered authorization

Decision order must consider:

- authenticated user
- program membership
- role/capability
- stage scope where applicable
- domain/action permission
- assignment or ownership
- record-level policy
- conflict-of-interest restrictions
- governance restrictions

Reason:

- role membership alone cannot safely determine access to trust-sensitive EAIC records

Implication:

- permission does not automatically equal access

## D-014: Permission Naming

Decision:

- EAIC permissions use a singular `resource.action` naming convention

Examples:

- `program.view`
- `program.create`
- `application.view`
- `application.submit`
- `evaluation.finalize`

Reason:

- consistent permission naming makes authorization understandable and maintainable

Implication:

- roles/capabilities remain separate from permissions
- permissions do not bypass scope or policy

## D-015: Inherited Starter Roles

Decision:

- inherited Master Starter roles do not automatically grant EAIC domain authority

Reason:

- boilerplate roles and EAIC business authority are separate concerns

Implication:

- EAIC capabilities must be explicitly mapped
- existing `Manager` or `Staff` roles must not silently become EAIC authorities

## D-016: Program Membership

Decision:

- explicit program membership is the primary mechanism for program scope

Reason:

- the same user may have different capabilities in different programs

Implication:

- membership carries program-specific authority
- membership has lifecycle status
- removing membership prevents new actions while preserving historical actions

## D-017: Application Types

Decision:

- EAIC supports Individual, Team, and Organization applications

Reason:

- the platform must support different applicant structures

Implication:

- application ownership and membership must support all three forms

## D-018: Application Ownership

Decision:

- every application has explicit primary ownership

Reason:

- consequential applicant actions require clear authority

Implication:

- ownership is distinct from membership
- owner-only actions must be protected
- ownership changes are explicit and auditable

## D-019: Application Membership

Decision:

- Team and Organization applications support multiple approved members

Reason:

- collaborative applications require controlled participation

Implication:

- membership does not automatically equal ownership
- members receive only authorized capabilities

## D-020: Controlled Delegation

Decision:

- application owners may delegate specific permitted actions to approved members

Reason:

- collaboration should not require transferring ownership

Implication:

- delegation is capability-specific
- delegation can be revoked
- delegation may expire
- delegation is audited
- delegation never grants unrestricted ownership authority

## D-021: Application Versioning

Decision:

- submitted application versions are immutable

Reason:

- Judges must evaluate a known version and historical evidence must remain reproducible

Implication:

- revisions create new versions
- previous submitted versions remain preserved
- every Judge evaluation references the exact application version evaluated

## D-022: Program-Controlled Eligibility

Decision:

- eligibility is controlled by program rules

Reason:

- different EAIC programs may have different eligibility requirements

Implication:

- eligibility cannot be hardcoded as one universal rule

## D-023: Screening

Decision:

- screening combines automated objective validation with human Program Staff review

Reason:

- objective checks can be automated while consequential screening remains human-controlled

Implication:

- screening results and decisions are auditable

## D-024: Judge Assignment

Decision:

- Judges must be explicitly assigned to applications before performing restricted evaluation actions

Reason:

- evaluation access must be scoped to legitimate assignments

Implication:

- unassigned Judges cannot evaluate applications
- reassignment is controlled and auditable

## D-025: Conflict of Interest

Decision:

- EAIC uses a hybrid conflict-of-interest model

Reason:

- objective signals can be detected by the system, but human determination is required

Implication:

- system detection
- Judge declaration
- authorized human determination
- blocking conflict becomes an authorization restriction
- conflict history is preserved
- AI cannot resolve conflicts

## D-026: Independent Judging

Decision:

- Judges evaluate independently before controlled disclosure

Reason:

- independent assessment reduces inappropriate influence between Judges

Implication:

- Judges initially see their own assigned evaluation context
- another Judge's private evaluation is protected

## D-027: Rubric Governance

Decision:

- rubrics are versioned and frozen before dependent evaluations

Reason:

- historical evaluations must remain reproducible

Implication:

- frozen rubric versions cannot be silently changed
- exceptional changes require governed handling

## D-028: Evaluation Mathematics

Decision:

- EAIC uses deterministic weighted scoring

Approved direction:

- criterion scores use a 0–10 scale
- criteria have weights
- weights total 100%
- the system calculates the weighted result
- the normalized result is expressed on a 100-point scale
- Judges cannot manually override the calculated total

Reason:

- mathematical results must be reproducible and auditable

Implication:

- raw scores and calculated results remain separate from human judgment

## D-029: Human Qualitative Judgment

Decision:

- qualitative human judgment is a first-class evaluation component

Reason:

- numerical scoring cannot capture every professional judgment relevant to an innovation assessment

Implication:

- criterion justification/evidence is preserved
- qualitative assessment is stored separately from numerical scores
- Judge recommendation is separate from mathematical score

## D-030: Evaluation Finalization

Decision:

- finalized evaluations are protected records

Reason:

- finalized assessments must not be silently changed

Implication:

- reopening requires controlled authority
- reopening and subsequent changes are auditable
- protection becomes stronger after deliberation begins

## D-031: Deliberation

Decision:

- deliberation is structured, human-led, and evidence-informed

Reason:

- Judges and authorized decision participants need a controlled process for considering evaluations and disagreements

Implication:

- controlled disclosure occurs at the appropriate point
- original evaluations remain historical records
- deliberation does not rewrite individual evaluations

## D-032: Final Human Decision

Decision:

- the Decision Maker makes the final consequential decision

Reason:

- EAIC must retain clear human accountability

Implication:

- final decisions are evidence-informed
- the Decision Maker records a rationale
- final outcome is not mechanically determined by score
- governed reversal/supersession preserves history

## D-033: Outcome and Transition

Decision:

- final decisions produce controlled outcomes and, where applicable, controlled transitions

Reason:

- successful applications may move into another EAIC stage or program

Implication:

- outcomes and transitions are separate from the final decision
- transitions must be configured and authorized
- applications are not deleted when a decision is made

## D-034: Applicant Transparency

Decision:

- EAIC uses tiered transparency

Reason:

- applicants should understand their own status and outcome without exposing confidential internal evaluation information

Implication:

- applicant-visible information is separated from program-internal, Judge-confidential, and governance information
- applicant-facing feedback is distinct from confidential Judge evaluation

## D-035: Event-Driven Notifications

Decision:

- EAIC uses event-driven notifications

Reason:

- important lifecycle changes require reliable communication

Implication:

- in-app notification is the authoritative notification record
- email is a delivery channel
- notification delivery must respect authorization and confidentiality
- delivery failure does not remove the authoritative in-app record

## D-036: Program-Configurable Deadlines

Decision:

- program deadlines are configurable and timezone-aware

Reason:

- different programs may operate under different schedules and jurisdictions

Implication:

- ordinary late submission/revision is prevented after the deadline
- governed exceptions must be explicit and auditable

## D-037: Audit and Governance

Decision:

- consequential EAIC actions require a comprehensive append-only audit trail

Reason:

- EAIC decisions must be reconstructable and defensible

Implication:

- actor
- timestamp
- action
- reason where required
- relevant state/history
- governance override information

must be preserved for trust-critical events

## D-038: Super Admin Governance Boundary

Decision:

- Super Admin has broad system administration authority but does not receive an unrestricted ability to rewrite governed business history

Reason:

- administrative power must not undermine trust-critical records

Implication:

- protected history remains protected
- exceptional governance intervention requires an explicit governed mechanism
- overrides are auditable

## D-039: AI Advisory Boundary

Decision:

- AI is advisory only for consequential EAIC decisions

AI may:

- summarize evidence
- identify patterns
- highlight disagreement
- assist workflow
- organize information

AI must not autonomously determine:

- final eligibility
- shortlist decisions
- final Judge scores
- conflict resolution
- final selection
- resource allocation
- final incubation/mentorship outcomes

Reason:

- consequential authority must remain human-controlled

## D-040: MVP Boundary

Decision:

- MVP focuses on the smallest complete EAIC lifecycle

MVP:

Program
→ Application
→ Eligibility
→ Submission
→ Validation
→ Screening
→ Judge Assignment
→ Conflict Check
→ Frozen Rubric
→ Independent Evaluation
→ Finalization
→ Controlled Disclosure
→ Deliberation
→ Decision Maker
→ Final Decision
→ Outcome
→ Applicant Notification
→ Audit

Deferred:

- incubation
- mentorship
- milestones
- resources
- events/training/showcase
- partners/vendors
- alumni/follow-up
- broad AI assistants
- autonomous decision systems

## D-041: Incremental Implementation

Decision:

- EAIC implementation must proceed in small, controlled, reviewable tasks

Reason:

- the product architecture is complex and consequential

Implication:

- Codex receives one narrowly scoped task at a time
- each task has a clear stop condition
- each interaction produces an incremental Markdown handoff
- Product & Technical Controller reviews each handoff before the next task

## D-042: Test-Loop Safety

Decision:

- AI agents must not become trapped in endless test/fix/retest loops

Required behavior:

- diagnose the failure
- make one meaningful corrective attempt
- rerun the affected check once
- record the result
- if still failing, stop that failure loop and continue to the next safe task

Implication:

- persistent failures must be documented honestly
- a test failure must never prevent creation of the required handoff

## D-043: Agent Handoff Documentation

Decision:

- every Codex/AI-agent interaction must produce an incremental, descriptive Markdown handoff

Required location:

`AI-AGENT-HANDOFFS/`

Required contents include:

- interaction ID
- task requested
- sources read
- files created/modified
- verification
- tests
- database changes
- Git status
- known risks
- recommended next task
- verified facts vs assumptions

Reason:

- the Product & Technical Controller must be able to reconstruct and review every implementation step

## D-044: Implementation Approval Gate

Decision:

- no implementation task may proceed until its prerequisite specification has been reviewed and approved

Reason:

- Codex must not invent missing product rules during implementation

Implication:

- product decisions
- authorization
- lifecycle/state model
- database schema
- acceptance tests

are approved progressively before implementation of the corresponding layer

## D-045: PostgreSQL

Decision:

- PostgreSQL is the primary EAIC database

Implication:

- EAIC domain design must be PostgreSQL-first
- SQLite compatibility may remain for starter/test compatibility where practical
- timezone-aware timestamps are required
- numeric types are preferred for scoring calculations
- JSONB is used only where variable metadata is justified
- relational state remains relational

## D-046: Database History and Immutability

Decision:

- trust-critical historical records must be preserved rather than silently overwritten or deleted

Implication:

- submitted application versions
- finalized evaluations
- frozen rubric versions
- deliberation records
- final decisions
- outcomes/transitions
- conflicts
- assignments
- consequential membership changes
- audit events

must have appropriate historical protection

## D-047: Controlled State Transitions

Decision:

- EAIC uses explicit lifecycle state machines

Reason:

- actors must not be able to arbitrarily manipulate workflow state

Implication:

A transition requires appropriate:

- actor authority
- permission
- current state
- prerequisites
- program policy
- conflict restrictions
- governance rules

## D-048: Decision Independence from Score

Decision:

- numerical score is evidence, not an automatic final decision

Reason:

- human professional judgment is part of EAIC's decision model

Implication:

- high score does not automatically mean acceptance
- low score does not automatically mean rejection
- the Decision Maker records the final human decision and rationale

## D-049: Protected Evaluation Confidentiality

Decision:

- individual Judge evaluations remain confidential until the approved disclosure point

Implication:

- Judges cannot access other Judges' private evaluations prematurely
- applicants do not automatically receive private Judge information
- deliberation disclosure is controlled

## D-050: Controlled Post-Decision Lifecycle

Decision:

- an application remains historically intact after the final decision

Implication:

- ACCEPTED, REJECTED, WAITLISTED, or REVISION_REQUIRED outcomes are recorded
- configured next-stage/program transitions are explicit
- applicant notification follows the finalized outcome
