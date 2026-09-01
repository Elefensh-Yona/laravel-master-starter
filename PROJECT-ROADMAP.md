# PROJECT ROADMAP

## Ethiopian AI Center

> **Status:** Draft v0.1 — implementation roadmap baseline.
>
> This roadmap assumes `PROJECT-REQUIREMENTS.md` and `DATABASE-SCHEMA.md` have been
> reviewed by the product owner but does not authorize unrestricted implementation.
> Ox Alpha should validate dependencies, identify gaps, and work in controlled phases.

---

## 1. Roadmap Purpose

This roadmap defines the recommended order for building the real project on top of
Laravel Master Starter.

The project must be developed incrementally.

The implementation priority is:

1. preserve inherited Master Starter functionality;
2. establish the core innovation lifecycle;
3. establish trustworthy judging/evaluation;
4. add AI assistance with human oversight;
5. build incubation, mentorship, resources, and follow-up;
6. add analytics, integrations, and advanced capabilities after the core workflow is stable.

---

## 2. Starting Point

Inherited from Laravel Master Starter:

- Authentication and account security
- RBAC and authorization
- Users and role management
- Settings
- Notifications
- Activity/audit logging
- Media/file management
- Search
- CSV import engine
- Export center
- Sanctum API v1 foundation
- Vue/Inertia/TypeScript/Tailwind UI system
- i18n foundation
- Pest/testing and CI quality gates

The real project should extend these systems rather than replace them.

---

## 3. Phase 0 — Project Initialization & Architecture Validation

### Objective

Prepare the downstream repository and validate the inherited foundation before
adding domain functionality.

### Tasks

- create the dedicated project repository from Laravel Master Starter
- establish project name and branding
- configure `.env`
- choose PostgreSQL as primary development database where appropriate
- retain SQLite compatibility
- verify dependencies
- verify migrations and seeders
- verify authentication
- verify RBAC
- read all project documentation
- validate `PROJECT-REQUIREMENTS.md`
- validate `DATABASE-SCHEMA.md`
- resolve open schema/product questions
- produce an implementation architecture plan

### Deliverables

- configured project repository
- approved requirements baseline
- approved database schema baseline
- architecture decision record for major unresolved decisions
- initial task backlog

### Exit criteria

- Master Starter functionality still passes
- requirements and schema are approved
- no major unresolved architecture contradiction remains

---

## 4. Phase 1 — Program / Challenge Foundation

### Objective

Create the core program entity and configurable lifecycle foundation.

### Build

- programs
- program stages
- program status/lifecycle
- application dates
- program configuration
- public program listing/detail
- program administration
- stage ordering
- program permissions/policies
- audit events

### Reuse

- Settings
- RBAC
- Media
- UI components
- Search
- Notifications
- ActivityLogger

### Tests

- program CRUD
- authorization
- stage ordering
- publication behavior
- date/visibility behavior

### Exit criteria

Staff can create and publish a program with an ordered lifecycle.

---

## 5. Phase 2 — Applicant & Team Foundation

### Objective

Allow individuals and teams to participate in programs.

### Build

- participant profiles
- teams
- team membership
- team lead rules
- applicant-facing profile
- team management
- program eligibility to allow individual/team participation

### Tests

- profile creation/update
- team creation
- member add/remove
- lead constraints
- authorization
- cross-program participation where allowed

### Exit criteria

An individual or team can be represented correctly without duplicating
authentication identities.

---

## 6. Phase 3 — Application & Submission Lifecycle

### Objective

Implement the central application workflow.

### Build

- application creation
- program-specific questions
- draft applications
- submission
- application reference numbers
- submission validation
- application status
- stage movement
- permitted revision/resubmission
- application documents/evidence
- applicant application dashboard

### Reuse

- Forms
- Media
- Notifications
- Search
- Import/export where applicable

### Tests

- draft creation
- submission validation
- authorization
- deadline rules
- team/individual behavior
- revision rules
- document associations

### Exit criteria

A real applicant can complete and submit an application end-to-end.

---

## 7. Phase 4 — Eligibility & Screening

### Objective

Allow staff to evaluate whether applications satisfy program requirements.

### Build

- eligibility rule configuration
- screening workflow
- screening results
- criterion-level screening results
- manual review
- incomplete status
- screening audit trail
- screening notifications

### AI boundary

Initial AI may assist with completeness/rule-gap detection but must not silently
make final eligibility decisions.

### Tests

- eligibility rule behavior
- screening permissions
- manual-review flow
- applicant status transitions
- audit events

### Exit criteria

Staff can process the application funnel from submission to eligible/ineligible/
manual-review outcomes.

---

## 8. Phase 5 — Rubrics & Judge Management

### Objective

Establish professional and auditable judging.

### Build

- rubric management
- evaluation criteria
- weights and score ranges
- rubric versions
- judge profiles
- judge assignments
- conflict-of-interest declarations
- conflict resolution
- assignment restrictions

### Critical design rules

- rubric versions become immutable once active evaluations depend on them
- judges cannot evaluate conflicted applications
- authorization remains permission-based
- assignments are auditable

### Tests

- rubric versioning
- criterion validation
- judge assignment
- conflict declaration
- conflict blocking
- role access

### Exit criteria

Program staff can safely assign judges and publish a stable evaluation framework.

---

## 9. Phase 6 — Independent Judge Evaluation

### Objective

Allow judges to independently review and score assigned applications.

### Build

- judge workspace
- assigned application list
- application evidence review
- criterion-level scoring
- comments
- recommendations
- save draft evaluation
- finalize/lock evaluation
- evaluation history

### UX principle

A judge should have a focused workspace that exposes only the information needed
to perform the assigned evaluation.

### Tests

- judge access restrictions
- score range validation
- evaluation draft/final behavior
- conflict enforcement
- evaluation locking
- audit logging

### Exit criteria

Multiple judges can independently complete evaluations without seeing or altering
each other's work inappropriately.

---

## 10. Phase 7 — AI Judge Copilot v1

### Objective

Introduce AI assistance at controlled, evidence-oriented points.

### Initial capabilities

1. application summary
2. evidence extraction
3. criterion-oriented evidence mapping
4. missing-information detection
5. risk/question suggestions
6. judge briefing generation

### Explicit non-goals

- automatic selection
- automatic rejection
- hidden score changes
- autonomous judge replacement
- opaque winner ranking

### AI governance

Track:

- provider
- model
- task
- prompt/version reference
- input/source references
- output
- reviewer
- review action

### Tests / controls

- authorization
- source-link integrity
- AI result persistence
- human review
- failure handling
- no silent score mutation

### Exit criteria

A judge can invoke AI assistance and inspect evidence-linked output without losing
human control of the evaluation.

---

## 11. Phase 8 — Pitch, Presentation & Deliberation

### Objective

Move from independent scoring to formal group decision-making.

### Build

- pitch events
- session scheduling
- judge/panel participation
- presentation ordering
- attendance
- deliberation sessions
- deliberation items
- discussion summaries
- recommendation
- final decision records

### AI assistance

AI may summarize discussion and prepare structured meeting notes, but humans own
the final decision.

### Tests

- assignment access
- presentation scheduling
- deliberation permissions
- decision finalization
- audit logs

### Exit criteria

The program can move from completed independent evaluations to a documented human
decision.

---

## 12. Phase 9 — Selection & Applicant Communication

### Objective

Formalize outcomes and communicate them.

### Build

- selected/finalist/waitlist/rejected/deferred outcomes
- decision rationale
- decision authority
- finalization
- applicant notifications
- status history

### Tests

- decision permissions
- final decision immutability
- notification delivery
- audit events
- applicant visibility rules

### Exit criteria

Every application has a traceable human decision and appropriate notification.

---

## 13. Phase 10 — Incubation Foundation

### Objective

Support selected innovators after selection.

### Build

- incubation enrollment
- phases/status
- goals
- assigned staff
- progress
- blockers
- outcomes

### Tests

- selected-applicant enrollment
- authorization
- status transitions
- staff visibility

### Exit criteria

A selected application can transition into structured incubation.

---

## 14. Phase 11 — Mentorship

### Objective

Manage mentor relationships and sessions.

### Build

- mentor profiles
- mentor assignment
- mentor availability
- goals
- sessions
- action items
- feedback
- outcomes

### Reuse

- Users
- Notifications
- Activity logs
- Calendar/event structures if later approved

### Tests

- assignment
- access controls
- session management
- audit
- notifications

### Exit criteria

Mentors and selected innovators can manage a structured mentorship relationship.

---

## 15. Phase 12 — Milestones & Progress

### Objective

Track the development of selected innovators.

### Build

- milestones
- due dates
- progress updates
- evidence
- reviewer comments
- completion
- blockers

### Tests

- milestone lifecycle
- permissions
- evidence attachment
- progress calculation where applicable

### Exit criteria

Program staff can measure meaningful progress toward incubation goals.

---

## 16. Phase 13 — Resources & Workspace

### Objective

Manage organizational resources provided to selected participants.

### Build

- resource catalog
- resource types
- availability
- allocation
- start/end periods
- status
- usage notes where needed

Examples:

- workstation
- GPU
- lab
- meeting room
- connectivity
- software/resource licenses

### Tests

- allocation conflict rules
- authorization
- start/end dates
- resource status

### Exit criteria

The organization can assign and track actual resources safely.

---

## 17. Phase 14 — Training, Events & Showcase

### Objective

Support the broader innovation ecosystem around each program.

### Build

- training sessions
- workshops
- pitch events
- showcase/demo days
- attendance
- instructors/mentors
- participant roles

### Tests

- registration
- attendance
- role-based access
- event visibility

### Exit criteria

Programs can run structured events throughout the lifecycle.

---

## 18. Phase 15 — Partner / Vendor / Stakeholder Management

### Objective

Manage external relationships supporting programs.

### Build

- organizations
- contacts
- organization types
- program partnerships
- contributions
- services/resources
- engagement status

### Possible future extension

Authenticated partner portal access should be treated as a separate feature and
not assumed in the first implementation.

### Exit criteria

Staff can understand and manage who supports each program and how.

---

## 19. Phase 16 — Reporting & Operational Analytics

### Objective

Provide practical program intelligence.

### Initial reports

- application funnel
- screening outcomes
- judge workload
- evaluation completion
- score distributions
- selection outcomes
- mentorship engagement
- milestone completion
- resource utilization

### AI-assisted reporting

AI can help summarize operational trends but reports must remain based on actual
stored data.

### Exit criteria

Staff can answer the key operational questions for a running program.

---

## 20. Phase 17 — Post-Program / Alumni

### Objective

Track longer-term outcomes after program completion.

### Build

- alumni records
- follow-up updates
- ongoing support
- partnership outcomes
- later investment/support where required

### Exit criteria

The organization can maintain a useful longitudinal history after the competition.

---

## 21. Phase 18 — Applicant AI Assistant

### Objective

Provide controlled AI assistance directly to applicants.

### Initial capabilities

- explain program requirements
- explain application questions
- check submission completeness
- provide non-deceptive writing/clarity support
- answer program FAQ content

### Safety

AI must not invent achievements, evidence, credentials, or claims.

### Exit criteria

Applicants can receive helpful guidance without altering authoritative submission
facts without explicit action.

---

## 22. Phase 19 — AI Staff Assistant

### Objective

Assist program staff with operational work.

Potential capabilities:

- application summaries
- screening workload summaries
- judge completion reminders
- program status briefs
- report drafting
- notification drafting
- anomaly/question surfacing

### Exit criteria

Staff receive measurable time savings without losing control of workflow.

---

## 23. Phase 20 — AI Mentor Assistant

### Objective

Assist mentors with participant progress.

Potential capabilities:

- session summaries
- action-item extraction
- milestone reminders
- progress summaries
- blocker summaries

### Exit criteria

Mentors can spend more time mentoring and less time on repetitive documentation.

---

## 24. Phase 21 — Advanced Integrations

Only after the core product is stable consider:

- email providers
- SMS
- calendar systems
- document processing services
- cloud storage
- external partner systems
- identity integrations
- analytics platforms

Each integration requires a separate design decision.

---

## 25. Phase 22 — Production Hardening

Before production:

- security review
- authorization matrix review
- privacy review
- audit review
- AI governance review
- database indexing review
- performance testing
- queue/async strategy
- backup/recovery plan
- logging/monitoring
- error handling
- document/file storage strategy
- deployment configuration
- data retention rules
- operational runbook

---

## 26. Phase 23 — Final Acceptance

The final release should demonstrate:

### Lifecycle

- program creation
- application
- screening
- judging
- deliberation
- decision
- incubation
- mentorship
- progress
- resources
- showcase
- follow-up

### Security

- role-based access
- judge isolation
- conflict enforcement
- protected evaluation data
- protected documents
- auditability

### AI

- evidence-linked outputs
- human review
- traceability
- no autonomous consequential decisions

### Technical quality

- tests passing
- CI green
- PostgreSQL verified
- SQLite verified
- frontend build green
- quality gates green

---

## 27. Development Method

Every phase follows:

```text
Requirements
   ↓
Schema impact
   ↓
Architecture review
   ↓
Task breakdown
   ↓
Implementation
   ↓
Focused tests
   ↓
Full verification
   ↓
Documentation update
   ↓
Next phase
```

Do not implement multiple unrelated domains in one uncontrolled change.

---

## 28. AI-Agent Work Method

Ox Alpha/Codex/other AI agents should:

1. read `AI-PROJECT-STARTER.md`
2. read `AGENTS.md`
3. read `MASTER-STARTER-ARCHITECTURE.md`
4. read `PROJECT-REQUIREMENTS.md`
5. read `DATABASE-SCHEMA.md`
6. read this roadmap
7. inspect actual repository state
8. identify the current phase
9. list tasks for the current phase
10. identify impacted tables/routes/permissions/tests
11. request approval for major architectural changes
12. implement incrementally
13. test each slice
14. update documentation
15. report exact verification

---

## 29. Task Tracking Strategy

The implementation agent should convert each roadmap phase into a concrete
task list.

Each task should contain:

- task ID
- phase
- objective
- dependencies
- files/modules likely affected
- database changes
- permissions
- tests
- acceptance criteria
- status

Suggested statuses:

- Backlog
- Ready
- In Progress
- Blocked
- Review
- Done

The agent should NOT silently mark work complete without verification.

---

## 30. Documentation Update Rules

When a feature changes:

- update relevant requirements
- update `DATABASE-SCHEMA.md` if the schema changes
- update this roadmap if sequencing changes
- update API notes if endpoints change
- update `MASTER-STARTER-ARCHITECTURE.md` only when inherited/core architecture
  changes materially
- preserve a decision record for significant architectural deviations

---

## 31. Phase Dependencies

The dependency chain is:

```text
Phase 0
  ↓
Phase 1
  ↓
Phase 2
  ↓
Phase 3
  ↓
Phase 4
  ↓
Phase 5
  ↓
Phase 6
  ↓
Phase 7
  ↓
Phase 8
  ↓
Phase 9
  ↓
Phase 10
  ↓
Phase 11
  ↓
Phase 12
  ↓
Phase 13
  ↓
Phase 14
  ↓
Phase 15
  ↓
Phase 16
  ↓
Phase 17

AI-facing phases should be introduced after the deterministic workflow they assist
is stable:

Phase 7 → Judge Copilot
Phase 18 → Applicant Assistant
Phase 19 → Staff Assistant
Phase 20 → Mentor Assistant
```

---

## 32. First Implementation Slice

The first actual coding slice should NOT attempt the whole product.

It should be:

### Program Foundation

- programs
- program stages
- program status
- program administration
- basic public program visibility
- permissions
- policies
- tests

Then stop and verify.

After that:

### Applicant / Team Foundation

Then:

### Applications

Then:

### Screening

This produces a stable vertical foundation before the more complicated judging and
AI phases arrive.

---

## 33. Open Product / Architecture Decisions

This roadmap depends on resolving the open decisions listed in:

`PROJECT-REQUIREMENTS.md`

and:

`DATABASE-SCHEMA.md`

The most important early decisions are:

- final product name
- application-question architecture
- submission versioning
- rubric versioning
- judge visibility rules
- conflict-of-interest blocking
- AI evidence/provenance
- AI retention
- applicant organization/startup support
- expected scale
- event/calendar depth
- partner portal requirements

These should be resolved before the phases that depend on them.

---

## 34. Scope Control

If a new requested feature appears during development:

1. identify which phase it belongs to;
2. determine whether it affects the database schema;
3. determine whether it changes existing authorization;
4. determine whether it affects AI governance;
5. assess whether it blocks the current phase;
6. add or reprioritize a task rather than silently changing scope.

Avoid uncontrolled feature growth.

---

## 35. Definition of Done

A phase is complete only when:

- functionality is implemented;
- relevant tests exist and pass;
- authorization is verified;
- database changes are verified;
- UI behavior is verified where applicable;
- existing Master Starter functionality remains intact;
- quality gates pass;
- documentation reflects the result;
- acceptance criteria are satisfied.

---

## 36. Roadmap Approval Gate

This document is a proposed implementation sequence.

Before Ox Alpha begins coding:

1. approve `PROJECT-REQUIREMENTS.md`;
2. approve/refine `DATABASE-SCHEMA.md`;
3. resolve critical open decisions;
4. approve this roadmap;
5. have Ox Alpha generate the detailed task backlog;
6. start Phase 0;
7. implement only the approved current phase.

No agent should jump directly from this roadmap to uncontrolled full-project
implementation.

---

## 37. Immediate Next Step

After approval of the project documents:

### Phase 0 — Initialization & Architecture Validation

Ox Alpha should:

- inspect the new project repository;
- read all project documents;
- verify Master Starter inheritance;
- verify the database schema against Laravel conventions;
- identify gaps/contradictions;
- produce the initial implementation task list;
- identify Phase 1 dependencies;
- wait for approval before large implementation.

