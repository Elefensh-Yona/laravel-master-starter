# ETHIOPIAN AI CENTER — CODEX BENCHMARK REPORT

## 1. Model Under Test

OpenAI Codex

## 2. Product Understanding

1. What problem the platform solves.

   This is not a generic contest tool; it is a program-operating system for innovation ecosystems. The platform is meant to manage the entire journey from challenge announcement through application, screening, review, final decision, incubation, mentorship, resource allocation, events, and alumni follow-up. The product is solving operational chaos: fragmented applications, uneven judging, poor evidence traceability, and insufficient auditability across multi-program, multi-stage workflows. It turns an otherwise ad hoc innovation program into something governable, auditable, and scaleable.

   Verified repository fact: the current codebase is a Laravel Master Starter, not a domain implementation. The functionality present today is auth, RBAC, activity logging, media, notifications, settings, import/export, search, and the Vue admin shell. The innovation-program domain is still a proposed downstream design layer, not implemented code.

2. Who the principal actors are.

   - Program staff / administrators: configure programs, publish opportunities, assign judges, manage screenings, run events, coordinate partners, monitor outcomes.
   - Judges: review assigned applications, score against rubrics, declare conflicts, support deliberation, and make recommendation input.
   - Applicants / innovators: individuals or teams applying to programs and later progressing through incubation/mentorship.
   - Mentors: guide selected projects, set goals, hold sessions, and record outcomes.
   - Partners / vendors / stakeholders: provide resources, training, workplace access, sponsorship, or opportunities.
   - Super users / administrators: use the inherited RBAC system and maintain governance, not a parallel auth stack.

3. What the full lifecycle means operationally.

   Operationally, this means a single installation supports multiple independent programs, each with its own application window, stages, eligibility, judges, rubrics, pitch sessions, outcome rules, and post-selection activities. The lifecycle is configurable rather than a single rigid pipeline. The system must support moving an application through public announcement, drafting, submission, staff screening, judge assignment, conflict check, evaluation, deliberation, selection, incubation, mentorship, milestone tracking, resource allocation, showcase participation, and alumni follow-up.

   That requires explicit state management at the application, stage, screening, evaluation, decision, incubation, and mentorship levels. It is not enough to have a generic “status” field.

4. Why this is more than a competition-management system.

   This is more than competition management because the workflow continues after selection. The product owns post-selection support and program operations: incubation, milestones, mentorship, partner contributions, resources, training, build-out, and follow-up. Also, judging is not just ranking; it is an auditable, role-based, conflict-controlled, multi-step decision process with AI decision support that must remain advisory. The platform is effectively an innovation-operation platform, not just a challenge portal.

5. Where AI provides the most valuable assistance.

   AI is most valuable when it reduces manual cognitive overload without bypassing human accountability. The highest-value jobs are:

   - application summarization and triage support
   - evidence extraction from documents and media
   - criterion-to-evidence mapping
   - completeness and missing-evidence detection
   - risk or question generation for judges
   - briefing packs for deliberation
   - applicant-facing guidance and status interpretation
   - staff summaries of program health and pipeline bottlenecks

   The AI should produce structured, source-linked outputs that humans can inspect, approve, modify, or reject.

6. Where AI must explicitly NOT have authority.

   AI must not make or silently influence consequential decisions in the following areas without human review and formal recordkeeping:

   - eligibility decisions
   - rejection/selection/finalist/waitlist decisions
   - judge score modification or final score locking
   - conflict resolution decisions
   - staff decisions on safety, misconduct, or wrongdoing
   - resource allocation to programs or applicants
   - final outcomes in incubation or mentorship

   In this product, AI is an advisory assistant, not a decision-maker.

## 3. Master Starter Reuse Analysis

Verified repository fact: the starter already includes Fortify auth, Spatie Permission RBAC, notifications, activity logs, media, settings registry, global search, import/export engine, API v1, and a Vue/Inertia frontend shell. This must be treated as the reusable foundation, not a project-specific implementation.

Classification:

A. Already provided

- Authentication, password reset, email verification, 2FA, password confirmation flow: confirmed in `config/fortify.php`, `app/Providers/FortifyServiceProvider.php`, `app/Models/User.php`, and `routes/web.php`.
- RBAC and permission middleware: confirmed in `bootstrap/app.php`, `app/Support/SystemRole.php`, and the route authorization patterns in `routes/web.php`.
- Settings registry and admin settings management: confirmed in `app/Support/SettingRegistry.php`, `app/Support/SettingStore.php`, and route-level admin settings permissions.
- Notifications center and database notifications: confirmed in `app/Notifications/SystemMessageNotification.php` and notification endpoints.
- Activity/audit logging: confirmed in `app/Support/ActivityLogger.php`, `AppServiceProvider` listeners, and `activity_logs` patterns.
- Media library and file handling: confirmed in `app/Models/Media.php`, `app/Support/MediaUploader.php`, and admin media routes.
- Search and export engines: confirmed in `app/Http/Controllers/GlobalSearchController.php` and `app/Support/Import/*` plus export controller patterns.
- API v1 baseline and Sanctum approach: confirmed in `routes/api.php` and starter docs.
- Frontend UI system: confirmed in Vue/Inertia/Tailwind pattern and component structure.
- Testing and CI quality gates: confirmed in README and default starter patterns.

B. Existing infrastructure that should be extended

- User identity model: reuse `users` as the base identity with program-specific participant and judge profiles layered on top.
- Authorization architecture: extend with domain permissions such as `programs.view`, `applications.review`, `judges.assign`, `decisions.finalize`, not with a second authorization system.
- Notification infrastructure: extend the existing notification class and activity logger for domain events.
- Media relationships: extend `media` through polymorphic attachment patterns.
- Search: extend permission-gated modules rather than creating a separate search engine.
- Settings: reuse for configurable program defaults and organization-level runtime settings.

C. Genuinely new project-domain functionality

- Programs, program stages, eligibility rules, application funnel logic.
- Team management and participant profiles.
- Application submissions, question/answer structures, and document associations.
- Rubrics and score models.
- Judge assignment, conflict declarations, and evaluation records.
- Deliberation and formal decision records.
- AI interaction logging and evidence provenance.
- Incubation, mentorship, milestones, resource allocations, partners, and alumni follow-up.

Dangerous or wasteful to rebuild

- A second auth or auth guard layer.
- A parallel RBAC table or custom middleware replacing Spatie Permission.
- A bespoke notification table or second audit log schema.
- A second settings mechanism or environment-based runtime config for editable settings.
- Custom media storage patterns per module when the inherited `media` + morph patterns already exist.
- A homegrown admin shell or component system when the Master Starter already provides a validated foundation.

## 4. Database Critique

This document is conceptually sound but still draft-level. The main issue is not whether the entities are reasonable; it is whether the schema preserves workflow integrity and operational trust. The product requirements are directionally correct, but the schema needs tighter enforcement around rule immutability, evaluation integrity, conflict blocking, and historical accountability.

Material issues and design concerns:

1. Programs and stages

   - The `programs` + `program_stages` model is correct in principle, but the lifecycle representation is too loose unless stage transitions are explicitly constrained by workflow rules and by status transitions. A stage should not be just a generic ordered step; it must be bound to policy and state validation logic.
   - Missing: a proper stage progression table or a workflow-state model if stages are not purely static. Otherwise, “current stage” and stage ordering can drift.
   - Risk: unclear ownership of stage transitions between staff and applicants, especially when submission deadlines or program dates change.

2. Eligibility and screening

   - `program_eligibility_rules` is useful but too generic if there is no rule-type taxonomy with consistent validation and audit semantics. The schema should distinguish “hard gate,” “manual review,” and “advisory” rules.
   - `application_screenings` and `application_screening_results` are directionally good, but they need a clear separation between staff screening and final eligibility. Screening should not be confused with final selection.
   - Missing: screening decision history separate from current status, so a reviewer can update without preserving prior action record.

3. Applicants, teams, and applications

   - The `participant_profiles` and `teams` model is sensible.
   - However, team membership and application ownership need strict rules: one application belongs to one lead applicant; multiple applicants may be on a team, but the lead and team membership must be auditable.
   - `application_questions` and `application_answers` are a design escape hatch, but they need final policy on whether they are a lightweight question store or a full configurable form engine. Without that decision, implementation will drift.
   - `application_submissions` may be too permissive unless versioning is explicit and immutable snapshots are required for legal/audit reasons.
   - Missing: explicit application status history table; status is too likely to be overwritten without lineage.

4. Documents and evidence

   - Using inherited `media` is the correct direction, but `application_media` should be more explicit about document purpose, privacy classification, and whether the evidence was required or optional.
   - Missing: a privacy/access classification for evidence (public, staff-only, judge-only, applicant-visible, confidential). This matters for final decision integrity and applicant data protection.

5. Rubrics and rubric versions

   - The rubric design is sound but must be treated as a versioned contract.
   - The caveat is critical: once evaluations start, rubric versions should become immutable or at least frozen for active applications. Otherwise: inconsistent scoring, unfair comparisons, and retroactive grade changes.
   - Missing: an explicit association from evaluations to rubric version and criterion version, not just rubric id. Without that, historical traceability is weak.

6. Judges and assignments

   - `judge_profiles`, `judge_assignments`, and `conflict_declarations` are necessary and correctly modeled.
   - The biggest problem is assignment cardinality. The schema allows `program_id`, `stage_id`, and `application_id` on the same record, but the actual policy needs to be crisp: assignment may be program-wide, stage-specific, or application-specific; it must not be ambiguous.
   - Missing: a lock or `is_blocked_by_conflict` derivation in evaluator assignment logic.
   - Missing: a uniqueness rule to prevent judges from being assigned to the same application in multiple conflicting ways.

7. Evaluations and scores

   - `judge_evaluations` and `evaluation_scores` are necessary, but the schema should state whether the evaluation record is a draft before final submission and whether score rows are updatable after finalization. Without this, the system will see score mutation and data integrity issues.
   - Missing: a separate finalization timestamp and finalization actor.
   - Missing: a way to record “score was changed after finalization” as a historical event rather than silent overwrite.
   - Missing: per-criterion evidence links or rationale fields to support auditability.

8. Deliberation and final decision

   - `deliberation_sessions`, `deliberation_items`, and `selection_decisions` are essential.
   - However, the distinction between “recommendation,” “decision record,” and “selection final outcome” needs to be explicit. The schema currently allows overlapping responsibilities without enough enforcement.
   - Missing: formal actor identity and timestamp on each deliberation recommendation.
   - Missing: a rule that a final selection decision cannot be created without valid deliberation or authorized role.

9. AI and evidence provenance

   - The AI tables (`ai_interactions`, `ai_sources`, `ai_review_actions`) are directionally correct, but they need stronger provenance structure: no raw provider output without source references, review action, and retention policy.
   - Missing: a field for source confidence / direct quote / excerpt hash; otherwise explainability is weak.
   - Missing: a retention and deletion policy for AI output and source references.
   - Missing: explicit `review_required` or `human_override_required` flag for decisive AI results.

10. Incubation, mentorship, and milestones

   - The structures are reasonable and appropriately lightweight for v1.
   - The main issue is that many of these are later phases that should not be treated as early requirements if the system has not yet stabilized around evaluation and decisions.
   - Missing: a distinct link between selected application and incubation enrollment; otherwise program history can be disconnected.

11. Organizational and resource modeling

   - `organizations`, `program_partners`, and `resource_allocations` are necessary but should remain simple in v1.
   - Overlap validation for `resource_allocations` is an important operational question; databases alone cannot fully enforce all semantics, so a service-layer rule is also needed.

12. PostgreSQL / SQLite compatibility

   - The doc is correct to emphasize compatibility, and the repository supports both in principle.
   - The risk is in JSON queries, generated columns, advanced indexes, and soft constraints. Use portable SQL and avoid PostgreSQL-only features unless needed and justified.

   Recommendations for the schema:
   - Keep JSON for flexible config, not for core relation semantics.
   - Do not depend on PostgreSQL-only exclusion constraints unless the team is comfortable with a PostgreSQL-first architecture.
   - Ensure all rate-limited and state-tracking constraints can work in SQLite test runs.

## 5. Workflow Critique

The workflow proposed in the requirements is realistic and well ordered, but several important operational rules are still ambiguous. The primary concern is that a competition-like workflow becomes risky as soon as final outcome, conflict handling, and score finalization are not strictly enforced.

Missing state transitions

- Draft -> submitted -> under review -> shortlisted -> assigned -> evaluated -> deliberated -> selected/rejected/waitlist.
- The current documents do not yet define transitions for withdrawal, resubmission, reopened application, conflict resolution, appeal, and final decision reversal.
- Requirement-derived conclusion: this needs a strict state machine, not free-form status values.

Ambiguous ownership

- Staff, judges, applicants, and mentors all interact with the same application at different stages. The workflow must clearly assign who can create, edit, submit, review, finalize, and decide.
- Without clear ownership, a judge can be both reviewer and delib participant, or an applicant can modify a submitted application after staff screening has started.

Irreversible operations

- Finalizing a score
- formally deciding a selection
- deleting conflict declarations after review
- generating final enrollment into incubation
- locking a rubric for active applications
- closing application windows for a program

   These must be either protected by database-level constraints or explicit domain rules, not by frontend conditions alone.

Race conditions and duplicates

- Duplicate submissions by the same applicant/team must be prevented or governed by a versioning policy.
- A judge may be assigned to the same application both at program and stage level.
- Two staff users could simultaneously finalize a conflicting eligibility decision.
- Two judges may finalize nearly identical evaluations at the same time.

   Backend enforcement is required: unique constraints, transactional updates, and carefully scoped permissions.

Judge isolation problems

- Independent evaluation must be isolated from other judges’ scores, comments, and final recommendations until a release or deliberation phase.
- Conflicts of interest must block assignment before evaluation begins.
- Judges should not see each other’s raw criterion scores unless a deliberate “deliberation view” is intentionally enabled.

Conflict-of-interest loopholes

- The critical risk is not just a declared conflict. It is a person who is assigned to evaluate a project they are indirectly connected to via team members, organization affiliations, mentor relationships, or partner networks.
- This needs a policy of “related parties” and documented review, not just a free-text declaration form.

Rubric changes during judging

- This is a high-risk operational issue. A rubric should not be modified in a way that changes scoring semantics for already assigned evaluations.
- Rule: once a rubric version is active for a program stage or evaluation attempt, it becomes immutable for that evaluation cycle.

Score finalization problems

- A score should be final once a judge marks it finalized. Any later change should require a proper audit trail and explicit re-open of the evaluation.
- Backend rule: a finalized evaluation can be reopened only by authorized staff or by the judge under a formal review process.

Decision-record problems

- A final selection decision must identify the application, decision, reason, authority, final date, and actor. It cannot be derived from an ad hoc score number.
- A list of decisions must be append-only, not overwritten.

Notification timing issues

- Applicant notifications should not be sent before a decision has been formally recorded.
- Conflict updates, assignment changes, and rubric freeze events should be communicated to judges and staff in a consistent, auditable sequence.

Enforcement by type

- Database constraint: uniqueness, foreign keys, status/finalization check, active rubric version lock, duplicate active assignment prevention, applicant/team assignment safety.
- Backend/domain rule: stage transitions, finalization, conflict enforcement, rubric immutability, notification timing, eligibility logic, duplicate submission handling, score review/reopen rules.
- Authorization/policy: judge can only see assigned application, staff can view all relevant records, mentors see only their own assigned cohort, AI can read only approved evidence and explicit targets.
- Frontend only: UI hints, pre-filters, and cleaner UX; never the only enforcement for consequential actions.

## 6. Judging Architecture

1. How should judges be assigned?

   Judges should be assigned through a program-stage-application assignment model with an explicit relationship between judge, program, stage, application, and assignment status. This should support both broad program/stage assignments and specific application assignments, but only one active assignment path should be authoritative for a given application-stage-grounded evaluation.

   Best practice: one assignment row with `program_id`, `stage_id`, `application_id`, `judge_user_id`, `assignment_status`, `assigned_at`, and `removed_at`, where `application_id` may be null for stage-level assignment. The system interprets stage-level and application-level assignments carefully and prevents duplicate active assignment logic.

2. Can a judge be assigned at program/stage/application level?

   Yes, but only as a layered assignment model with strict precedence and no silent duplication. The application-level assignment should override or refine stage-level assignment, and the system should permit both for operational convenience while preventing conflicting active assignments for the same evaluation context.

   The important rule is not “which level is allowed,” but “what is the authoritative assignment for this evaluation.” The schema should make that explicit.

3. How should conflicts block evaluation?

   Conflict declarations must operate as policy checks before evaluation is created or before scoring is finalized. A judge with an active conflict on a program/application/team/organization should not be assigned, should not view the application under a judge workspace, and should not participate in deliberation unless a compliant resolution process clears them.

   Implementation should be enforced in both policy and domain logic; not just by UI conditions.

4. Should judges see other judges’ scores?

   Not in the independent evaluation phase. Judges should see only their own scores and comments until a formal deliberation stage is opened. Hidden raw scores are essential to protect independence and fairness. Summary metrics may be visible to staff only, not to judges before the decision phase.

5. When should scores become immutable?

   Scores should become immutable when a judge marks the evaluation as final. Prior to that, draft edits are normal. Once finalized, a score row should be append-only in practice, with explicit reopened-finalization as an exception handled by authorized staff. This is essential for audit integrity.

6. How should rubric versioning work?

   Rubrics should be versioned per program and, if relevant, per stage. Each evaluation must be linked to the exact rubric version used. Once a rubric becomes active for a program stage, it should be frozen for all active or pending evaluations in that cycle. A new rubric version may be created for future rounds, but not retroactively alter past judgments.

7. How should score outliers be surfaced?

   Score outliers should be surfaced by the backend as analytic data, not by ad hoc manual review. The system should highlight unusually high or low scores relative to peer judges and within criterion distributions, but it should not automatically override a judge’s decision. It should raise review flags for staff or deliberation moderators.

8. What belongs in independent evaluation vs deliberation?

   Independent evaluation should be the judge’s private scoring phase: evidence review, criterion-level scoring, comments, recommendation, and finalization. Deliberation should be the collective discussion phase: comparing evidence, reconciling different assessments, discussing outliers, deciding recommendations, and producing human final decisions.

   A key separation: independent evaluation is not the same as group judgment, and both phases must be recorded separately.

9. How should final decisions be represented?

   Final decisions should be represented as a formal record separate from evaluation scores. It needs application, program, stage, decision type, reason, decider or authority, decision date, notification state, and possibly final outcome version. The decision should not be inferred from the sum of scores alone; the product requirement explicitly rejects score arithmetic as the sole selection basis.

Missing tables or relationships

- A distinct `evaluation_finalizations` or `score_audit_events` table for immutable score change history.
- A `rubric_versions` table if the versioning is more sophisticated than a `version` field on `program_rubrics`.
- A `judge_assignment_history` table for role and status change traceability.
- `conflict_resolution` or `conflict_review_actions` if review decisions need explicit lifecycle tracking.
- A `decision_audit` table if final decisions require strong append-only logging.

## 7. AI Architecture

The AI design is promising but must be treated as enterprise decision support, not autonomous decision-making. It is fundamentally a trust and governance problem, not merely a prompt engineering problem.

Evaluate the proposed components:

- Application summarization: good V1 capability; it reduces staff and judge read time. Must be source-linked and reveal what was omitted.
- Evidence extraction: good V1 capability if document references and page snippets are retained.
- Criterion mapping: good V1 capability if tied to rubric and threshold definitions.
- Missing evidence: very valuable; risks are low if output is purely advisory and tied to visible evidence gaps.
- Risk/question generation: good V1; useful for staff and judges, but should be clearly marked as suggestions.
- Judge copilot: likely the highest-value use case, but should be scoped to review only and should never mutate scores without human approval.
- Deliberation summary: useful, but should be a summary of evidence and arguments, not a hidden recommendation engine.
- Applicant assistant: useful for status guidance and drafting improvements, but it requires careful privacy and sensitive-data boundaries.
- Staff assistant: high value for pipeline management, compliance checks, and summary generation; must stay restricted to operational data and not bypass policy.
- Mentor assistant: useful later; should not automatically drive milestones or appraisals without explicit mentor oversight.

Proposed model evaluation

- Provider abstraction: required. The system should support a provider adapter layer and not hard-code one API.
- Model tracking: required. Every AI interaction should track provider, model, version, temperature, and metadata where relevant.
- Prompt/version tracking: required. Prompt versions and instruction templates should be auditable and reviewable.
- Source references: required. Output without source references is weak and unsafe.
- Evidence provenance: required. "This came from application section 3.2 and media file M123" must be explicit.
- Human review: required for any output that affects consequential decisions.
- Accepted/modified/rejected output: required. The system should store review actions and the resulting final output.
- Privacy: critical. AI tools must respect applicant data minimization and separate public/private data boundaries.
- Retention: must be explicit. AI outputs should have retention policies aligned with project requirements and legal constraints.
- Hallucination: a major risk. System must restrict outputs to source-grounded references and refuse unsupported claims.
- Prompt injection: a serious risk if applicants or partners can submit documents containing instructions that the AI might execute. Inputs need sanitization and source boundaries.
- Sensitive data: the system must avoid sending all raw data to external AI providers without policy review, especially private documents, personal identifiers, and confidential program data.
- Failure/retry behavior: required. Failures should be non-destructive and traceable.
- Cost management: required. AI work should be queued, rate-limited, and cost-tagged per task type.

Recommended maturity split

- V1: application summarization, evidence extraction, criterion mapping, missing evidence, AI review logging, human review, source references, provider abstraction, restricted output scope.
- V2: judge copilot, staff assistant, deliberation summary, applicant assistant with explicit policy control, model benchmarking, prompt review workflow, approved output redaction and retention controls.
- Later: autonomous recommendations, broad cross-program analytics, advanced semantic retrieval, agentic workflows, broad mentorship automation, and open-ended generative assistance without human approval gates.

## 8. Security & Authorization

The inherited Master Starter uses Spatie Permission with a single `web` guard and system roles. This architecture is correct and should be retained. The product must not add a second authorization system. The expected pattern is to extend the existing permission catalog with project-specific domain permissions and then authorize through policies and route-level middleware.

Required project permissions

- `programs.view`, `programs.create`, `programs.update`, `programs.publish`, `programs.archive`
- `applications.view`, `applications.create`, `applications.update`, `applications.submit`, `applications.withdraw`
- `screenings.view`, `screenings.review`, `screenings.finalize`
- `judges.view`, `judges.assign`, `judges.conflict.manage`
- `evaluations.view`, `evaluations.create`, `evaluations.update`, `evaluations.finalize`
- `deliberations.view`, `deliberations.manage`, `decisions.view`, `decisions.finalize`
- `incubation.view`, `incubation.manage`, `mentorship.view`, `mentorship.manage`
- `partners.view`, `partners.manage`
- `resources.view`, `resources.manage`
- `ai.view`, `ai.review`, `ai.use`

Access boundaries

- Applicants: can view only their own application, own team, own documents, own statuses, and public program information. No access to other applicants’ files or judge deliberations.
- Team members: same as applicants for team-owned applications; role-specific rights must be assigned carefully, especially team lead actions.
- Judges: can view only assigned applications and only within their permitted program/stage context; they cannot see other judges’ raw scores or notes until deliberation is intentionally opened.
- Staff: can manage program config, judge assignment, screening, final selection, and notifications within scope; they must not bypass review controls.
- Mentors: restricted to assigned incubations or mentorship relationships and relevant goals/milestones.
- Partners: visibility should be program-scoped and resource/account-specific, not open access to applicant data or internal evaluations.
- Administrators: system roles use the inherited Master Starter RBAC; these are role-driven and should not bypass domain-specific checks incorrectly.
- AI outputs: must be guarded by access policy and should not be visible to unauthorized users; review status should distinguish accepted/modified/rejected.
- Evaluations: judge-only before finalization; staff/decision-makers only after formal release.
- Conflicts of interest: conflict records should be visible to authorized staff and review actors; not broadly public.
- Private application documents: staff and judges only on a need-to-know basis, with explicit policy and file-access authorization.
- Final decisions: visible to authorized roles and applicants, but not necessarily to all judges or to all public parties.

Important security point: do not invent a second authorization system. The correct model is to add permissions to the same Spatie Permission model and use policies for the domain entities, with route `permission:` middleware in `routes/web.php` and API checks in the versioned API layer.

## 9. MVP Recommendation

MVP should prove the central value: a configurable program with auditable application intake, secure staff review, judge scoring, and human final selection. Avoid front-loading mentoring, partnerships, complex resources, and mirrored enterprise features.

MUST HAVE

- Program creation and publishing
- Stage configuration and ordering
- Applicant/team profile and team membership
- Draft and submitted application flow
- Application document/media attachments
- Eligibility screening and manual review outcomes
- Judge assignment and conflict declaration
- Rubric versioning and criterion scoring
- Independent evaluation with finalization
- Human deliberation and final decision record
- Applicant notification of outcome
- Basic audit logs and AI interaction logging with source references

Why: these are the core path that proves the platform works and that it is not merely a competition landing page. They provide the product’s value and the trust model.

SHOULD HAVE

- Role-based staff dashboards
- Program search and global listing
- Standardized applicant-facing status pages
- Basic event support for pitch and showcase planning
- Simple incubation enrollment for winners
- Mentor assignment and milestone tracking for selected teams only
- AI summary and evidence extraction for judge workflow

Why: these improve usability and reduce operational overhead, but they are not required to validate the central value proposition.

LATER

- Complex partner ecosystems and full resource scheduling
- Full public CMS or fundraising features
- Full mentorship/LMS features
- Multi-track or recursive program portfolio analytics
- Advanced AI agent workflows and autonomous operations
- Longitudinal alumni tracking beyond the minimal record
- Advanced integrations or external data connectors

Why: these are important eventually but distract from the core trust and workflow integrity required to operate a serious program.

## 10. First Implementation Slice

The correct first slice is: Program + application + stage + eligibility + judge assignment + conflict + evaluation + decision + audit. Everything else should wait.

Entities

- `programs`
- `program_stages`
- `team_profiles` or `teams`
- `team_members`
- `applications`
- `application_submissions`
- `application_media`
- `program_eligibility_rules`
- `application_screenings`
- `program_rubrics`
- `evaluation_criteria`
- `judge_profiles`
- `judge_assignments`
- `conflict_declarations`
- `judge_evaluations`
- `evaluation_scores`
- `selection_decisions`
- `ai_interactions`
- `ai_sources`
- `ai_review_actions`

Migrations

- Add the domain tables in small, stage-based migration batches: program foundation, team/application foundation, screening and rubric foundation, judge/conflict foundation, evaluation/decision foundation, AI foundation.
- Use explicit foreign keys and avoid destructive cascades for historical records.
- Ensure `created_by`, `updated_by`, and `deleted_by` semantics are considered, but not on every table blindly.

Models

- `Program`
- `ProgramStage`
- `Team`
- `TeamMember`
- `Application`
- `ApplicationSubmission`
- `ApplicationScreening`
- `ProgramRubric`
- `EvaluationCriterion`
- `JudgeProfile`
- `JudgeAssignment`
- `ConflictDeclaration`
- `JudgeEvaluation`
- `EvaluationScore`
- `SelectionDecision`
- `AiInteraction`
- `AiSource`
- `AiReviewAction`

Permissions

- `programs.*`
- `applications.*`
- `screenings.*`
- `judges.*`
- `evaluations.*`
- `decisions.*`
- `ai.review`

Policies

- `ProgramPolicy`
- `ApplicationPolicy`
- `JudgeAssignmentPolicy`
- `JudgeEvaluationPolicy`
- `SelectionDecisionPolicy`
- `AiInteractionPolicy`

Routes

- Program admin CRUD and publish routes
- Applicant/team routes
- Application create/view/edit/submit/withdraw routes
- Staff screening routes
- Judge evaluation routes
- Deliberation and decision routes
- AI review routes

Requests

- `StoreProgramRequest`
- `UpdateProgramRequest`
- `StoreApplicationRequest`
- `SubmitApplicationRequest`
- `StoreJudgeAssignmentRequest`
- `StoreConflictRequest`
- `StoreEvaluationRequest`
- `FinalizeEvaluationRequest`
- `StoreDecisionRequest`

Controllers / services

- `ProgramController`
- `ApplicationController`
- `ScreeningController`
- `JudgeAssignmentController`
- `JudgeEvaluationController`
- `DecisionController`
- `AiReviewController`
- `ProgramWorkflowService`
- `AssignmentService`
- `ConflictService`
- `EvaluationFinalizationService`

Frontend pages / components

- Program index and detail pages
- Application dashboard and submission form
- Staff screening dashboard
- Judge assigned application workspace
- Conflict declaration modal
- Rubric management interface
- Decision summary page
- AI review panel with source references

Tests

- Program creation and publish behavior
- Applicant submission and time-lock rules
- Screenings and manual review workflow
- Judge assignment and conflict prevention
- Score finalization and immutability checks
- Final decision creation and notifications
- AI review logging with source provenance and human review actions

Acceptance criteria

- Staff can publish a program with stages and rules.
- Applicants can create and submit applications within program rules.
- A judge can score only assigned applications and cannot see other judges’ scores before deliberation.
- A conflict blocks assignment or evaluation for the relevant application.
- An evaluation can be finalized only once and then becomes immutable unless reopened by authorized roles.
- A final decision is recorded as a separate formal event and not computed from score totals alone.
- AI outputs are stored with source references and human review status.

Why this is the correct first slice

- It proves the platform’s real value: the core program lifecycle from application to selection.
- It validates the risky trust assumptions: conflicts, score integrity, final decisions, and source-linked AI.
- It avoids premature complexity in mentorship, resource management, and advanced partner modules.

## 11. Roadmap Critique

The roadmap is directionally good but too broad in several places and not strict enough on the dependency order for trust-critical phases.

Correct dependencies

- Program foundation before applications
- applications before screenings and judging
- judging before deliberation and final decisions
- AI assistance after the evaluation pipeline is stable and reviewable
- incubation/mentorship after final decisions exist and are auditable

Incorrect or weak ordering

- AI guidance should not be introduced before the evaluation workflow is stable. It is a major risk to add AI before data integrity patterns are proven.
- The roadmap currently treats judging and AI as parallelizable too early; they are not. AI should be introduced into a mature judging workflow, not integrated before the evaluation rules and data model are settled.
- Incubation and mentorship should come after selection decisions are fully formalized and auditable.

Phases that can run in parallel

- Some staff-side program administration and public program pages can be developed in parallel with the first application and team model.
- Notification infrastructure and activity logging are cross-cutting and should run in parallel with earlier domain features.
- Media handling and settings are shared infrastructure and should not be delayed.

Phases too broad

- “Applicant & Team Foundation” is reasonable, but team membership and application workflow are tightly coupled and may be better kept as part of the same phase rather than split.
- “Rubrics & Judge Management” includes multiple trust-critical domains and likely needs a dedicated implementation discipline.
- “AI Judge Copilot v1” is still too broad if it includes summarization, evidence extraction, risk detection, and reviewer workflows at once.

Phases too early

- Advanced AI features before the scoring and decision model is stable.
- Partner/resource systems before the core program and decision workflow is stable.
- Alumni and follow-up may be deferred until the core pipeline and decision records are proven.

Missing milestones

- A formal schema review and approval gate before migrations.
- A conflict/offense and access-control policy milestone before judge assignment.
- A rubric freeze/finalization milestone before scoring begins.
- A final decision audit milestone before applicant notifications.
- An AI governance and retention milestone before AI receives any applicant data.

Areas that should be delayed

- detailed mentorship management
- advanced resource management and allocations
- partner ecosystems and contributions
- showcase and alumni programs
- analytics and reporting beyond minimal staff dashboards

## 12. Engineering Risks

Risk | Likelihood | Impact | Mitigation

- AI reliability and hallucination | High | High | Require source grounding, human review, output rejection workflow, and strict task boundaries.
- Fairness in judging | Medium | High | Freeze rubrics, isolate independent scores, surface outliers, require explainable evaluation records, and review assignment conflicts.
- Privacy and sensitive data handling | Medium | High | Use least-privilege access, classify evidence, sanitize prompts, and avoid broad provider data sharing.
- Applicant data protection and retention | Medium | High | Explicit data retention policy, role-based access, and encrypted storage for sensitive documents.
- Evaluation integrity | High | High | Finalization locking, append-only score audit trail, policy-enforced conflict blocking, and decision records separate from scores.
- Schema complexity | High | Medium | Start with minimal tables and explicit dependencies; avoid over-generalizing too early.
- Workflow complexity | High | High | Formal state machine and strict transition rules before building UIs.
- Performance on large document sets | Medium | Medium | Pagination, background processing, storage optimization, and evidence indexing.
- Large file / document ingestion | Medium | High | Limit file sizes, classify document types, and use media storage policies and background processing for heavy workloads.
- AI cost | High | Medium | Queue work, rate-limit, track model cost per task type, and avoid unrestricted usage.
- Provider lock-in | Medium | Medium | Provider abstraction, model tracking, and clear adapter interfaces.
- PostgreSQL/SQLite compatibility | Medium | Medium | Keep SQL portable and avoid PostgreSQL-only constructs unless necessary.
- Auditability of decisions and AI | High | High | Append-only records, review actions, and actor timestamps at every consequential step.

## 13. Decisions Required Before Coding

CRITICAL

- What is the authoritative state model for applications, screenings, evaluations, and final decisions? The workflow must be explicit and not approximate.
- How will rubric versioning be frozen once judging starts?
- What is the exact conflict definition: only direct relationships, or also team/org/mentor/partner affiliation?
- What is the assignment model at the program, stage, and application levels: one authoritative assignment or multiple overlapping assignments with precedence rules?
- How should evaluation scores be finalized and reopened within a controlled domain workflow?
- What is the acceptable evidence and AI-data boundary for applicant documents and sensitive information?
- What constitutes a “decision record” and who can create it?

IMPORTANT

- Whether final decision logic uses strict score thresholds or a human deliberation override model.
- Whether application questions are an actual configurable form engine or a simpler program-defined fixed set of fields.
- Whether team members can submit applications on behalf of a team without lead approval.
- Whether applicant privacy should be higher than standard staff-internal access patterns.
- What alerting and notification workflow should be triggered for assignment, conflict, and outcome changes.
- Whether AI output should be stored in full or summarized with source-linked excerpts.

LATER

- Full partner ecosystem and resource allocations.
- Advanced reporting and analytics.
- Alumni tracking and long-term follow-up.
- Full training/LMS features.
- Broad multi-program portfolio analytics.

## 14. Final Engineering Judgment

1. What parts of the project are especially well designed?

   The strongest parts are the inherited foundation and the explicit principle that the product is not a generic competition app but a multi-program lifecycle platform. The Master Starter architecture is strong for auth, RBAC, settings, notifications, auditing, media, and admin patterns. The product documents also correctly emphasize that AI is assistive and that final decisions remain human-controlled.

2. What parts concern you most?

   The biggest concerns are the trust-critical workflow rules: conflict management, rubric immutability, score finalization, decision-record authority, and the absence of a formal state machine for application progression. The second concern is AI governance under real applicant data and documentation.

3. What would you change before coding?

   I would tighten the exact workflow state model and finalize the core domain decision rules before writing code. I would not start with broad modules; I would lock down assignment semantics, rubric freezing, conflict definitions, and the decision record model. I would also require AI output provenance and review actions as a mandatory system requirement, not a later extension.

4. What would you explicitly leave alone?

   I would leave the inherited Master Starter alone: auth, permission model, media, settings, notifications, activity logs, search, and the established Vue/Inertia patterns are appropriate and should be reused.

5. What is most likely to cause rework later?

   Poorly defined state transitions and loosely enforced judging semantics are the most likely sources of expensive rework. The second likely source is unreviewed AI architecture without provenance and retention rules.

6. What should we validate with a prototype?

   The critical prototype should validate: application submission and versioning, judge assignment and conflict restriction, independent scoring with no cross-judge visibility, rubric freeze behavior, and final selection decision logging. The AI prototype should validate source-grounded summarization and human review flow, not business end-to-end automation.

7. What should never be prototyped poorly because it affects data integrity or security?

   Judge scoring immutability, conflict detection and blocking, final decision records, access boundaries for private documents, and any AI workflow touching applicant documents without source provenance and human review.

8. What is the first thing you would implement?

   The first implementation should be a minimal but full trust path: program creation, application submission, screening, judge assignment, conflict blocking, rubric-scored evaluation, and final decision record. That is the minimum slice that proves product value and data integrity.

9. What should absolutely NOT be implemented yet?

   Broad incubator/mentor operations, advanced resource allocation, full partner management, extensive alumni tracking, advanced reporting, and open-ended AI workflow automation. Those belong later once the core trust path is proven.

## 15. Scores

PRODUCT UNDERSTANDING: 9/10
DOMAIN MODELING: 7/10
DATABASE DESIGN: 7/10
WORKFLOW DESIGN: 6/10
JUDGING ARCHITECTURE: 7/10
AI ARCHITECTURE: 7/10
SECURITY: 8/10
IMPLEMENTATION PLANNING: 7/10

OVERALL ENGINEERING READINESS: 7/10

This is a promising domain with a good foundation and a realistic product shape, but the trust-critical rules around judging, conflicts, immutable score finalization, and AI provenance need tightening before implementation begins.
