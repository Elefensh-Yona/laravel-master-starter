# PROJECT REQUIREMENTS

## AI Innovation Lifecycle Platform
### Official product name: **Ethiopian AI Center (EAIC)**

> **Status:** Draft v0.1 — product/design baseline for human review.
>
> This document defines the intended product scope for the first real project built
> from **Laravel Master Starter**. It is not an official government specification
> and does not claim affiliation with any Ethiopian institution unless a future
> partnership explicitly establishes that relationship.

---

## 1. Product Vision

Build a professional platform that manages the **full innovation-program lifecycle**
from public program announcement and citizen/application intake through screening,
judging, deliberation, selection, incubation, mentorship, resource allocation,
showcase events, and post-program follow-up.

The platform should also provide **AI-assisted decision support** for applicants,
staff, judges, mentors, and program administrators.

The central idea is not to replace judges with AI.

> **AI assists humans; humans remain accountable for consequential decisions.**

The product should make the lifecycle more organized, transparent, auditable,
scalable, and easier to operate across multiple programs.

---

## 2. Product Positioning

This is broader than a competition management system.

It should be treated as an:

> **AI-assisted innovation program management and judging platform.**

A single installation should be capable of supporting many programs/challenges,
each with its own application period, stages, eligibility rules, rubric, judges,
mentoring/incubation process, events, resources, partner relationships, and outcomes.

The platform must remain **program-configurable** rather than hard-coded around one competition.

---

## 3. Target Users / Actors

### 3.1 Platform / Organization Staff

Responsible for program configuration, publication, screening, judge assignment,
conflict management, events, partners, resources, incubation, mentoring,
communications, and program monitoring.

### 3.2 Judges

Review assigned applications, review evidence, score against published criteria,
record comments, declare conflicts, participate in deliberation, and submit recommendations.

### 3.3 Applicants / Innovators

May be individuals or teams. They can discover programs, apply, form or join teams,
submit proposals/evidence, monitor status, receive notifications, participate in pitches,
complete milestones, and participate in incubation/mentorship/support.

### 3.4 Mentors

Accept assignments, set goals, hold sessions, record outcomes, track progress,
and provide feedback.

### 3.5 Partners / Vendors / Stakeholders

External organizations may sponsor/support programs, provide technology/resources,
training, mentorship, workspace, or opportunities.

### 3.6 Administrators / Super Administrators

Use the inherited Master Starter RBAC architecture. Do not create a parallel authorization system.

---

## 4. Core Product Principles

1. Human authority remains final for selection, rejection, and other consequential decisions.
2. AI outputs must be explainable enough to review and, where practical, tied to source evidence.
3. Every consequential workflow is auditable.
4. Programs are configurable.
5. Individual and team applications are supported.
6. The same person can participate in different roles across programs, subject to authorization.
7. Master Starter infrastructure is reused rather than rebuilt.
8. Domain logic remains in the downstream project rather than generic starter infrastructure.
9. PostgreSQL and SQLite compatibility must be preserved.
10. Privacy, security, and least-privilege access are first-class requirements.

---

## 5. End-to-End Lifecycle

1. Program creation
2. Draft configuration
3. Internal review/approval
4. Public publication
5. Application opening
6. Applicant registration
7. Draft application
8. Submission
9. Eligibility screening
10. Administrative/technical screening
11. Shortlisting
12. Judge assignment
13. Conflict-of-interest declaration
14. Independent judge evaluation
15. AI-assisted analysis/review
16. Pitch/presentation
17. Deliberation
18. Final decision
19. Applicant notification
20. Incubation enrollment
21. Mentor assignment
22. Training/workshops
23. Milestones/progress tracking
24. Resource/workspace allocation
25. Final showcase/demo
26. Program completion
27. Post-program follow-up / alumni tracking

Not every program must use every stage. The workflow must be configurable.

---

## 6. Module Requirements

### 6.1 Program / Challenge Management

A program supports name, code/slug, description, objective, public information,
application window, status, capacity/limits where applicable, eligibility definition,
stages, evaluation criteria, settings, instructions, and metadata.

A future organization may run many programs.

### 6.2 Program Stages / Workflow

Programs may define ordered configurable stages with name, code, description,
sequence, active state, optional dates, configuration, and responsible role/team.

Avoid baking the lifecycle into one rigid enum.

### 6.3 Public Program / Announcement Experience

Published programs should expose objective, eligibility, dates, requirements,
criteria summary where appropriate, instructions, FAQs/instructions, and application access.

Do not turn v1 into a general-purpose CMS.

### 6.4 Applicant & Participant Management

Support individual participants, team-based participants, and organization/startup
representation where required. Reuse the inherited `users` identity model.

### 6.5 Team Management

Domain team concept (not Spatie Teams). Support team profile, members, membership role,
lead designation, status, and membership history where needed.

### 6.6 Application Management

Applications belong to a program and support reference, lead applicant, team,
title, problem, solution, AI component, target users, innovation, technical approach,
impact, scalability, model/business information where relevant, status, stage,
submission, withdrawal, and declarations.

Do not turn every future question into a permanent column. Use a configurable question
mechanism where appropriate.

### 6.7 Application Submission / Revision

Support draft, submitted, revised/resubmitted where permitted, and locked/final states.
Important submission history remains auditable.

### 6.8 Documents & Evidence

Support proposals, pitch decks, CVs, technical files, screenshots, prototypes,
and other evidence. Reuse Master Starter `media`.

### 6.9 Eligibility & Screening

Programs may define geographic, participant, document, completeness, deadline,
technical, and program-specific rules. Screening outcomes include Eligible,
Ineligible, Needs Manual Review, and Incomplete. AI may assist, but humans control outcomes.

### 6.10 Evaluation Rubrics

Each program can define criteria, descriptions, weights, score ranges, guidance,
order, and stage association. Examples are illustrative, not hard-coded.

### 6.11 Judge Management

Reuse users for identity. Support judge profile, expertise, specialization,
organization, availability, and assignment at program/stage/application level.

### 6.12 Conflict of Interest

First-class support for declarations, affected parties, type, review, resolution,
reason, timestamps, and blocking/flagging of conflicted evaluations.

### 6.13 Judge Evaluation

Judges independently score applications against criteria. Support stage/rubric,
criterion-level scores, comments, evidence references, recommendation, status,
and finalization.

### 6.14 AI Judge Copilot

Advisory only. Potential functions include summaries, criterion-oriented analysis,
evidence extraction, missing information, risk/questions, rubric comparison,
and deliberation preparation.

AI must not silently change scores, reject/select applicants, override judges, or override staff decisions.

### 6.15 AI Governance / Explainability

Track AI task type, provider/model, prompt/version where appropriate, sources,
output, metadata, requester, and human review/disposition. Possible review actions:
Accepted, Modified, Rejected, Not Used.

### 6.16 Pitch / Presentation / Showcase

Support pitch sessions, interviews, demos, showcase days, and award events with
schedule, participant/application, order, location/online details, judges/panels,
attendance, and outcomes.

### 6.17 Deliberation

Support formal deliberation sessions, participants, discussed applications,
discussion points, recommendations, rationale, and final decision records.

### 6.18 Selection / Outcome

Support outcomes such as Selected, Rejected, Finalist, Waitlisted, Deferred.
Record result, reason, deciding authority, date, stage, and notification state.
Selection is not derived from score arithmetic alone.

### 6.19 Incubation

Support enrollment, program, dates, status/phase, assigned staff, mentors, objectives,
milestones, blockers, progress, and outcomes.

### 6.20 Mentorship

Support mentor profiles, assignments, goals, sessions, outcomes, action items,
progress, feedback, and status. Do not recreate the removed Notes module.

### 6.21 Training / Workshops

Support training, workshops, seminars, and technical sessions with session,
instructor/mentor, participants, attendance, and completion where needed. Do not build a full LMS in v1.

### 6.22 Milestones / Progress

Support milestone, due date, completion, status, progress, evidence, responsible
participant/team, reviewer, and review comments without becoming generic project management.

### 6.23 Resources / Workspaces

Support resources such as workstations, labs, GPUs, meeting rooms, connectivity,
and software/resources with catalog, type, capacity, allocation, assignee, dates, and status.

### 6.24 Partners / Vendors / Stakeholders

Support external organizations such as government, universities, companies,
investors, vendors, NGOs, partners, and communities; track contacts, relationships,
contributions, services/resources, status, and dates.

### 6.25 Communications

Reuse the Master Starter notification system. Domain events may trigger submission,
screening, judge assignment, conflict, pitch, decision, mentor, milestone, and event notifications.

### 6.26 Post-Program Follow-Up

Support lightweight alumni, follow-up updates, ongoing mentoring, partnerships,
continued support, and outcome tracking where required.

---

## 7. AI Capabilities by Actor

### Applicant Assistant

Explain requirements, check completeness, explain questions, help improve clarity,
and answer program FAQs without fabricating evidence.

### Judge Copilot

Summarize, extract evidence, map evidence to criteria, surface missing information,
prepare comparisons, and summarize deliberation.

### Staff Assistant

Support application summaries, workload views, screening support, operational reports,
and notification drafting.

### Mentor Assistant

Summarize sessions, extract actions, remind milestones, and summarize participant progress.

### Applicant-facing AI safety

AI must never fabricate applicant claims or alter submissions without explicit human review.

---

## 8. Reporting Requirements

Eventually support program operations, applicant analytics, evaluation analytics,
incubation analytics, resource utilization, and AI usage analytics, with permission-aware output.

---

## 9. Security / Privacy / Governance Requirements

Reuse Master Starter authentication/authorization. Protect applicant documents,
judge evaluations, conflicts, AI inputs/outputs, and internal review data.
Separate applicant-visible information from internal review information.
Record important administrative actions.

---

## 10. Audit Requirements

Reuse `activity_logs` for application submission, screening outcomes, judge assignment,
conflict declaration/resolution, evaluation finalization, deliberation decisions,
selection, mentor assignment, resource allocation, milestone changes, and AI dispositions.

Use dotted event names such as `applications.submitted`, `evaluations.finalized`,
and `selections.decided`.

---

## 11. Notifications

Reuse the Master Starter notification system for application status, judge assignment,
review reminders, pitch scheduling, decisions, mentor assignment, milestones, and events.
Additional delivery channels are a later deployment decision.

---

## 12. API Requirements

Use the inherited `/api/v1` conventions: FormRequest, controller, resource,
`ApiPagination`, permission middleware, and Sanctum.

Potential consumers include applicant portals, mobile clients, partner integrations,
and internal dashboards. Specific endpoints will be designed after schema approval.

---

## 13. Import / Export Requirements

Reuse the Master Starter import/export foundation. Potential domain imports include
applicants, participants, judges, programs, and partners; potential exports include
applications, evaluations, outcomes, progress, and resource allocations.

---

## 14. Non-Goals for Initial Version

Do not automatically build:

- public CMS
- generic social network
- full LMS
- ERP/accounting
- full CRM
- generic project-management suite
- autonomous AI judge
- automatic final winner selection
- generic enterprise workflow engine
- blockchain
- marketplace

---

## 15. Product Success Criteria

A first mature release should demonstrate:

1. Program creation/publication.
2. Individual and team applications.
3. Eligibility/screening workflow.
4. Safe judge assignment and conflict handling.
5. Configurable rubric scoring.
6. AI-assisted judging without replacing humans.
7. Auditable deliberation and decisions.
8. Incubation and mentoring.
9. Milestones and resource allocation.
10. Notifications and operational reporting.
11. PostgreSQL/SQLite compatibility.
12. Preservation of inherited security/RBAC/authentication.
13. Automated tests for major lifecycle behavior.

---

## 16. Constraints

Prioritize correctness, explainability, security, auditability, configurable workflow,
maintainability, AI governance, responsive UX, and performance on realistic datasets.
Do not optimize for maximum features before the core lifecycle works.

---

## 17. Human Decisions Required Before Implementation

1. Final product name.
2. Exact applicant eligibility rules.
3. Whether organization/startup applications are allowed.
4. Anonymous/public application policy.
5. Application revision/resubmission policy.
6. Exact screening model.
7. Judge score visibility rules.
8. Conflict-of-interest rules.
9. Rubric versioning policy.
10. AI visibility to applicants.
11. Allowed AI tasks by stage.
12. AI retention policy.
13. Event/calendar depth.
14. Partner relationship depth.
15. Incubation milestone model.
16. Resource/workstation rules.
17. Communications channels.
18. Alumni depth.
19. Reporting priorities.
20. Deployment/hosting requirements.
21. Expected application/judge volume.
22. Legal/compliance requirements.

---

## 18. Initial Release Boundary

First implementation should concentrate on:

**Program → Application → Screening → Judge Assignment → Evaluation →
Deliberation → Selection**

Then add:

**Mentorship → Incubation → Milestones → Resources → Showcase**

Initial AI focus:

1. completeness assistance
2. application summarization
3. evidence extraction
4. criterion-oriented judge support
5. deliberation summarization

More autonomous AI behavior comes later, if ever approved.

---

## 19. Documentation Contract

Maintain:

- `AI-PROJECT-STARTER.md` — inherited platform orientation
- `MASTER-STARTER-ARCHITECTURE.md` — inherited technical reference
- `PROJECT-REQUIREMENTS.md` — this project's requirements
- `DATABASE-SCHEMA.md` — this project's domain schema
- `PROJECT-ROADMAP.md` — later phased implementation plan

---

## 20. Approval Gate

This document is a product/design baseline, not implementation permission.

Before migrations/models for major domains:

1. review requirements
2. review schema
3. resolve open decisions
4. approve roadmap
5. implement in controlled phases
