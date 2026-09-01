# DEEPSEEK-V4-FLASH-BENCHMARK-REPORT

**Subject:** Ethiopian AI Center (EAIC)
**Report type:** Independent architecture & coding-agent benchmark report
**Model under test:** DeepSeek V4-Flash
**Method:** Independent study of the project documentation and the actual repository. This analysis was derived from the project documents and verified repository state in the workspace, not from prior model output.
**Instruction compliance:** No application code was modified, no migrations/tables created, no packages installed, no configuration changed, nothing committed or pushed. This report is the only artifact written to disk.

> **Grounding note (verified repository facts).** The current repository is the **Laravel Master Starter** — a deliberately *domain-neutral* boilerplate. It contains **zero** domain entities. Verified: `app/Models/` holds only `User`, `ActivityLog`, `Media`, `Setting`, `ImportRun`; `app/Http/Controllers/` holds only core/admin/settings/**API v1** controllers; the five app models have policies for exactly `User`, `Role`, `Media`, `Setting`; the four system roles are `Super Admin`, `Manager`, `Staff`, `Guest` (`app/Support/SystemRole.php`); the 18 permission catalog, settings registry, notification center, activity log, media, import/export, global search, and the `/api/v1` Sanctum surface all exist and are **documented as tested (127 passed / 2 GD-skip, 768 assertions)**. **Every EAIC domain entity in this report is therefore my recommendation, not existing functionality.** I will flag this boundary throughout.

---

## 1. Executive Understanding

**What the product is.** The Ethiopian AI Center is not a competition-management app. It is a program-operating system for an Ethiopian organization that runs AI-innovation and entrepreneurship programs. One installation must concurrently operate multiple programs, each with its own announcement, application window, eligibility rules, staged lifecycle, rubric, judges, pitch/showcase events, partners, mentors, resources, and post-selection support. The organizing idea is a **configurable program lifecycle** layered over a hardened, reusable core (auth, RBAC, audit, notifications, media, settings).

**What problem it solves.** It addresses operational chaos: fragmented intake, uneven and unhidden judging, poor evidence traceability, opaque decisions, and weak institutional memory. It forces a previously ad-hoc, human-run program into a governed, auditable, repeatable pipeline — while preserving human authority over every consequential outcome.

**Who uses it.** Six families of actors, each with distinct information boundaries: program staff/administrators, judges, applicants/innovators (individuals and teams), mentors, partners/vendors/stakeholders, and decision makers — all built on the inherited four-role RBAC scaffold.

**What makes it more than a CRUD app.** Three things, none of which are CRUD concerns:
1. **Consequential integrity.** Judging, conflict of interest, score finalization, and final selection are *trust records*, not data rows. Score arithmetic must never be allowed to silently decide outcomes.
2. **Configurable stateful workflow.** The lifecycle is a first-class, per-program state machine, not a status dropdown.
3. **AI as governed decision support.** AI is a first-class citizen that must produce source-linked, human-reviewable output and must be *prevented* from becoming a decision maker.

---

## 2. Full Innovation Lifecycle

Chronicle (state transitions marked →):

1. **Announcement** → program creation/draft → **published** (public opportunity goes live).
2. **Intake** → application period opens → applicant (individual or team) registers profile/team and drafts an application (Draft).
3. **Submission** → submitted (← eligible if within window) → submission locked (unless revision policy re-opens it).
4. **Screening** → under_review → eligible / ineligible / needs_manual_review.
5. **Shortlisting** → shortlisted / not_shortlisted.
6. **Assignment** → judges assigned per program/stage/application.
7. **Independent evaluation** → evaluations drafted → finalized (individual judge scoring, conflicts blocked).
8. **Pitch/presentation** → invited_to_pitch → pitched.
9. **Deliberation** → under_deliberation → recommendation.
10. **Selection** → selected / finalist / waitlisted / not_selected (formal decision record).
11. **Applicant notification** → decision communicated.
12. **Incubation & mentorship** → incubating → mentor assigned → goals/milestones.
13. **Resources** → workstation/resource allocation (where applicable).
14. **Training/workshops & showcase** → events attended.
15. **Follow-up** → completed → post-program/alumni tracking; institutional knowledge retained.

**Where human decisions must occur** (non-delegable): eligibility finalization, shortlist, judge assignment, conflict resolution, score finalization, deliberation recommendation, final selection, resource allocation, mentor assignment, and milestone/outcome sign-off.

**Where AI can safely assist** (advisory only, always reviewable): application/requirement summarization, eligibility/completeness triage (never the final verdict), evidence extraction and criterion-to-evidence mapping, missing-information and duplicate/similarity detection, judge briefing packs, score *outlier* and inconsistency surfacing (never score modification), deliberation-summary notes, notification drafting, mentorship session summaries and milestone-reminder generation, and reporting/trend summarization over stored data.

The through-line is this: **humans own outcomes; AI owns cognitive load.**

---

## 3. Actors and RBAC

Actors, capabilities, and access boundaries (my recommendation; the four starter roles map as scaffolding — see unknowns):

| Actor | Can do | Cannot see / must not do |
|---|---|---|
| **Applicant/Innovator** | View own profile, own/team applications, own submission status, outcome, and program public info; manage own team (lead-gated); submit/re-submit per policy. | Other applicants' apps, judge scores, deliberations, AI internals, and internal review notes. |
| **Judge** | View only *assigned* applications; read assigned evidence; score independently; declare conflicts; draft + finalize own evaluation. | Other judges' raw scores/comments until deliberation is opened; unassigned apps; staff-only reviews; edit other judges' work. |
| **Program staff** | Configure programs/stages/rules; screen; publish; assign judges; run events; coordinate partners; drive decisions per approval. | Cannot edit finalized evaluations/decisions outside a governed reopen workflow. |
| **Mentor** | View only assigned incubations/cohorts; set goals, sessions, feedback. | Peers' cohorts, evaluation raw scores, unrelated applicant data. |
| **Partner/Vendor** | Program-scoped, account/resource-specific visibility only. | Applicant data, evaluations, deliberation. |
| **Decision-maker/Admin** | Cross-program visibility, formal decision records. | Uses inherited RBAC, must not bypass domain policies. |

**Separation of duties** (the core trust model):
- **Applicant ⊥ Judge:** a judge cannot be an applicant in the same program.
- **Judge ⊥ Decision-maker:** same person may not both score and formally decide the same application without a documented review path (guarded to specific roles).
- **Evaluator ⊥ Reviewer:** independent judge evaluation is isolated so no judge sees peers' scores until the deliberation phase is deliberately opened.
- **Staff ⊥ Applicant:** staff with screening rights cannot give themselves an application.
- **Conflict-of-interest** is a policy *gate* enforced at assignment, evaluation-creation, and finalization (see §5), not a free-text form.

Documented constraint: reuse the inherited Spatie RBAC, single `web` guard, and the `resource.action` permission convention; add *domain* permissions to the inherited catalog rather than building a second authorization system. System roles (`Super Admin`, Manager, Staff, Guest) come from `app/Support/SystemRole.php` and must not be renamed/deleted. I recommend role rms not expanded into the RBAC layer and domain roles remain *capability-contexts* materialized as domain permissions on those system roles.

---

## 4. Core Domain Model

The following are my recommended entities (none exist today). For each: purpose, key attributes, relationships, lifecycle, ownership, audit.

1. **Program** — container for one public innovation opportunity.
   - Attributes: name, code, slug, description, objective, status (draft→published→active→archived), application/period, timezone, capacity.
   - Relations: `hasMany` program_stages, eligibility rules, rubrics, applications, events; `belongsToMany` organizations via program_partners; `created_by`→user.
   - Lifecycle: from draft through published, active, and archived.
   - Ownership: program staff.
   - Audit: creation, publication, edit, archive at program scope, immutability of identity fields after first judge assignment.

2. **ProgramStage** — ordered configurable workflow step (not one rigid lifecycle enum).
   - `program_id`, `code`, `name`, `description`, `sequence`, `status`, `starts_at/ends_at`, `configuration` (JSON).
   - Unique `(program_id, code)` and `(program_id, sequence)`.
   - Lifecycle: configured→active→closed; transitions bound to workflow/policy.
   - Audit: stage order and status changes.

3. **ParticipantProfile** — one-per-user domain profile (do NOT duplicate `users` identity).
   - `user_id` unique FK, bio, location, education/experience (summary), skills/expertise/links (JSON).
   - Ownership: the user; visible to program staff.
   - Audit: profile changes.

4. **Team** / **TeamMember** — domain team capable of submitting an application; members with `membership_role`, `is_lead`, `status`, `joined_at/left_at`, and no duplicate active membership per team.
   - Lifecycle: created → members joined/changed → lead transitions → disbanded.
   - Ownership: team lead for submission/lead actions; audit membership history.

5. **Application** — the central submission container; `program_id`, unique `reference`, `lead_user_id`, optional `team_id`, title, status, `current_stage_id`, submission/withdrawal timestamps.
   - Lifecycle: draft → submitted → under_review → (per program) screened/shortlisted/assigned/evaluated/deliberated → decided → (maybe) incubating → completed/withdrawn.
   - Ownership: its lead user/team for content; staff for screening; judges for their slice only.
   - Audit: every state change, submission/revision, and withdrawal; references stable across time.

6. **ApplicationQuestion / ApplicationAnswer** — a configurable question store (the style/scale is an open decision, §C) with per-answer media/evidence, so a question doesn't need a permanent first-class column.
   - Lifecycle: question set frozen snapshots once iteration begins.
   - Audit: program final/rendered question forms become immutable after that program's judging is complete.

7. **ApplicationSubmission** — versioned submission/revision history; a submitted immutable snapshot plus a "revision" record.
   - Lifecycle: draft → submitted (v1) → resubmitted (v≥2, per program policy) → locked.
   - Audit/finally-important: cannot be silently overwritten.

8. **ApplicationMedia / evidence** — explicit join from application (or submission) to inherited `media`, with a `purpose` (proposal/deck/CV/technical/evidence) plus a privacy/access class; required-or-optional tagged.

9. **ApplicationScreening** / **ApplicationScreeningResult** — staff eligibility assessment and rule-level result records; lifecycle from screening → outcome; results audited; "eligible/ineligible/needs_manual_review/incomplete." Separate from final selection.

10. **ProgramRubric** / **EvaluationCriterion** — the versioned evaluation framework: criteria with weights, min/max scores, description; `(rubric, version)` unique; criteria immutable once evaluation begins.

11. **JudgeProfile** / **JudgeAssignment** — judge identity profile (user-linked) and an assignment with an explicit authoritative scope (program/stage/application level) + assignment status + assigned/removed timestamps; prevents conflicting active assignments in the same evaluation context.
   Could be composed of `program_id`, `stage_id`, `application_id`, `judge_user_id`, `assignment_status`, `assigned_at`, `removed_at`, with one authoritative assignment per evaluation.

12. **ConflictDeclaration** — declarations and resolution: `judge_user_id`, `program_id`, optional `application_id`/`team_id`/`organization_id`, type, description, status, declared/reviewed timestamps, resolution + reason; reviewed_by. Blocking via domain rule/policy.

13. **JudgeEvaluation** / **EvaluationScore** — the judge's private, per-assignment assessment; an evaluation has per-criterion `evaluation_scores` with score, comments, evidence references, recommendation; a status draft→finalized with a **finalization actor + timestamp**; and a separated finalization/score-audit/history record so any later score change after finalization is an event, not a hidden overwrite.

14. **SelectionDecision** — a formal human outcome record: application_id, program_stage_id, decision (selected/finalist/waitlisted/not selected/deferred), reason, decided_by, decided_at, is_final, notification state. Append-only, not derived from score arithmetic.

15. **AiInteraction** / **AiSource** / **AiReviewAction** — provider-neutral, auditable AI events: requested_by, task_type, provider, model, prompt_version, target/type-id, status, input_summary, output_text/metadata, completed_at, sources (source type/id/label + excerpt), human review action (accepted/modified/rejected/not_used), reviewer + timestamp. Never stores credentials.

16. **IncubationEnrollment** / **Milestone** / **MilestoneUpdate** — post-selection support entities: enrollment (program, application, status, phase, assignee, objectives/outcomes), milestones with sequence/due/status/completion and milestone_updates with updater, progress, comment, evidence.

17. **MentorProfile / MentorAssignment / MentorshipGoal / MentorshipSession** — mentor profile, assignment (enrollment+mentor+dates/status), goals, sessions w/ duration/summary/outcomes/action_items.

18. **ProgramEvent / EventParticipant** — pitch, pitch/showcase, training, workshops, awards: `event_type` drives role rather than a *calendar* per category; participants/attendance.

19. **Organization / ProgramPartner / PartnerContribution / OrganizationContact** — external stakeholder/org model: org info, partner relationships (relationship_type/status/dates/notes), contributions.

20. **Resource / ResourceAllocation** — resource catalog (workspaces, GPUs, labs, rooms, connectivity, software, licenses) + allocation records with start/end, status; overlapping allocations validated at service layer with DB constraint where portable.

21. **AlumniRecord / FollowUpUpdate** — lightweight post-program longitudinal tracking.

22. **Ai-source `ai_sources`** and **ai_review_actions** (already itemized) — provenance + human disposition.

Design rationale: keep JSON only for genuinely configurable/flexible content (question definitions, rules config, AI metadata, evidence metadata, action items, and organizational/resource metadata). **Never put query-critical IDs, statuses, scores, dates, or permission data into JSON.** Attack normal FKs, unique constraints, and indexes (see §10).

---

## 5. Evaluation and Judging Architecture

This is the trust heart of the product.

- **Evaluation criteria (per department? no — per program).** A `program_rubrics`+`evaluation_criteria` framework: name, description, weight, min/max score range, guidance, order; options associated with a rubric/graph and a program **state**.
  Process: candidate rubrics are **versioned contracts** with a unique `(program, version)`. Once any evaluation depends on a rubric, that rubric version becomes **immutable** for the active cycle; a new version is created only for future rounds. Retroactive changes prohibited.
  
- **Independent judge assessments + blind evaluation.** Each judge sees their own private workspace showing only their assigned application's evidence and rubric. Raw criterion scores, grades, and comments of other judges are **hidden** until the deliberation phase is deliberately opened. This preserves independence and fairness.
  Optional/advanced consideration: where a program chooses, blind evaluation may hide applicant identifiers (team/members/organization) during scoring to reduce bias — a configurable blend, controlled by program staff, not hard risk.

- **Weighting and score normalization.** Weights are declarative rubric metadata; the system computes a **weighted/normalized criterion sum** as a labeled *analytics* number — never a decision. Store **raw per-criterion scores** so that normalized/weighted aggregates can be recomputed transparently and audited; if different rubric versions or response scales are mixed, only well-defined, auditable normalization rules are applied and clearly labeled synthetic.

- **Individual judge independence.** Each judge's evaluation is a separate draft → finalized row. Scores are **finalization-locked** (finalization actor+timestamp). After finalization, edits are blocked; reopening a finalized evaluation is a governed reverse event authorized to specific staff/judge under a formal review process, and every reopened-score change is an append-only ledger event, never a silent overwrite.

- **Conflicts of interest.** A conflict is a first-class, resolvable policy. It is enforced *before* assignment, *before* evaluation creation, and *at* finalization: a judge with an active conflict on an application/team/organization is not assigned, cannot open that application, and cannot score/finalize. This considers related-party links (team, org, mentor, partner). Resolution is a staff-reviewed record. Conflicts do not disappear by user deletion.

- **Shortlist & deliberation.** Shortlist is a staff shortlist stage (possibly derived + human-approved numeric signals, but a human list decision). Deliberation is a separate recorded phase: sessions, participants, items (application, discussion summary, recommendation, decision), recommendation vs decision hierarchy. Deliberation reviewers see a released summary (peers' scored highlights) under an intentional "deliberation view," not raw hidden scores.

- **Final decision.** A formal `selection_decisions` append-only record separate from scores: application, program/stage, decision type, reason, deciding authority, date, notification state, is_final. **Never computed from score totals; score aggregation only informs the human gate.**

- **Prevention of unauthorized score changes (controls):** FINALIZATION-LOCK on the row, DB uniqueness (application × judge × assignment; criterion per evaluation is unique), a separated score-change/audit ledger that requires an explicit reopen authorized to a specific role, transactionalities around finalization, and scoring DDL inaccessible to judges. Multi-turn retrieval/escable.

- **AI assistance for judges (without deciding):** the Judge Copilot produces *source-linked advisory* outputs: application/evidence summary + specific extract + evidence-to-criterion mapping + missing-information/risk list + judge briefing pack. Each output is a labeled gauge with sourcer and a human review action (accepted/modified/rejected/not-used). It is **disabled** for consequential forks: it never sets scores, cannot change the score, and its suggestions appear inside a separate AI-review panel, not as authoritative final output. When (if) scope later adds it, a "score-line proposal" remains clearly an offer that only a human can accept.

---

## 6. AI Assistance

For each capability: input → processing → output → human approval → risk. (All must be audited through the `ai_interactions` pattern.)

1. **Application summarization**
   - Input: application text + attached media/meta; program rubric context; policy-scoped.
   - Processing: Reader/LLM over scoped content; structure to sections; cite source references.
   - Output: structured source-linked summary.
   - Approval: human (staff/judge) reviews; accepts/edits/rejects.
   - Risk: hallucinated details, scope leakage, PII exposure; mitigated with source grounding + approval gate.

2. **Requirement/eligibility checking (advisory)**
   - Input: application/candidate profile + modeled eligibility rules.
   - Processing: deterministic rules; AI only flags "possible missing rule/probe" — never a final eligibility verdict.
   - Output: draft yes/no/needs_review + explanation.
   - Approval: staff ends decision each time.
   - Risk: rule mismatch; mitigated by treating it as advisory and logging the AI verdict.

3. **Document/evidence analysis**
   - Input: uploaded document/media content (scoped, size-limited, OCR where configured).
   - Processing: text vector/reader extraction with citations.
   - Output: summary/excerpts, "this came from Media M123 / §x."
   - Approval: staff/judge-adopted output.
   - Risk: OCR errors, prompt injection from hostile document content — inputs must be treated as untrusted; heavy and sanitizable to staff.

4. **Duplicate/similarity detection**
   - Input: application text corpora.
   - Processing: embedding similarity over applications; cluster/threshold flags.
   - Output: "possible duplicate/near-duplicate candidates."
   - Approval: staff reviews before incident.
   - Risk: false positives; must leave the human to decide whether a duplicate is genuine.

5. **Scoring assistance**
   - Advisory only. Input: evidence + rubric; produces "candidate suggestion against criteria," never writes a score row.
   - Approval: judge explicitly accepts/ignores.
   - Risk: bias/anchoring; mitigation: default to no auto-suggestions or make them opt-in.

6. **Judge briefing packs**
   - Input: assigned application + pre-evaluation summaries.
   - Output: per-criterion, source-cited briefing file.
   - Approval: judge reviews before decisions.
   - Risk: omission of countervailing evidence; the briefing must narrate what was omitted.

7. **Candidate comparison**
   - Input: a shortlist subset + rubric; builds a comparison matrix, flagged trade-offs.
   - Approval: deliberation humans.
   - Risk: misleading rank; clearly not a decision.

8. **Anomaly / inconsistency detection** (not judgment)
   - Input: independent evaluation data → signals "score outlier vs peer judges," "score-... inconsistency," "finalized score changed."
   - Approval: staff review flags; system never auto-overrides.
   - Risk: over-alerting.

9. **Recommendation generation** (decision support, human-approved)
   - Input: deliberation-visible context.
   - Output: draft recommendation/report draft with cited basis.
   - Approval: decision maker must approve; can be fully human in MVP.

10. **Notification drafting**
   - Input: outcome/template + program context.
   - Output: draft notification text for staff review.
    - Approval: staff sends after accept/edit.
    - Risk: unintended disclosure — must version-gate.

11. **Mentorship assistance**
    - Input: mentor session summaries, milestone progress.
    - AI summarizes sessions, extracts action items, surfaces blockers/progress reminders.
     - Approval: mentor reviews before external delivery.
    - Risk: it must not auto-progress milestones or drive appraisals.

12. **Knowledge/RAG retrieval**
    - Input: program/docs/results/gists (source-linked).
    - Output: grounded answers.
    - Approval: users confirm; provenance shown.
    - Risk: provenance/client mix, stale data; require explicit source gate sync.

13. **Progress analysis / reporting assistance**
    - Input: stored operational data (pipeline, scores, resources, milestones).
    - Output: summary narratives/trend text tied to the numbers.
    - Approval: report consumers; numeric base always from real data, AI only narrates/summarizes.
    - Risk: must not fabricate metrics.

---

## 7. AI Governance

Controls to keep AI advisory:

- **Human-in-the-loop**: every consequential AI action has an unescapable approval; no autonomous consequential step; "reject/won't proceed" default where the requirement is ambiguous.
- **Explainability**: outputs are structured and source-linked ("file X, section; excerpt"), never authoritative raw text without references.
- **Traceability**: every AI call → one `ai_interactions` row: requester, task, provider, model, prompt_version, target, input summary, output, status, completed_at.
- **Confidence**: each capability surfaces calibrated confidence / "advisory" label; no silent omni-credible.
- **Source/evidence references**: each AI output cites the specific `ai_sources` references it used; an output without citations is weak and unsafe.
- **Model/identity tracking**: `provider` + `model` ** + version**; keep an immutable model registry keyed for replay audit.
- **Prompt/version tracking**: every prompt template/instruction has a version; record `prompt_version` per call.
- **AI-generated vs human content**: explicit attribution flag on any stored note/output (e.g. summary vs decision rationale); the final authoritative content of consequential fields carries a human source.
- **Override mechanisms**: any AI suggestion can be accepted/modified/rejected/not-used by authorized roles; "no silent pass-through". A **human_override flag** where a strongly-scoped decisive output requires mandatory human confirmation.
- **Approval workflows**: capability-gated by permission (`ai.use`, `ai.review`) + human review state machine.
- **Audit logs**: each interaction, source, review action appended; cross-appendix with `activity_logs` where consequential.
- **Reproducibility**: record input hash / prompt version / model + seed + params where possible so a result can be re-derived; keep a retention policy (open decision).
- **Data leakage & prompt injection safety**: do not send raw applicant PII/documents off without a scosaped policy; treat user-submitted content as untrusted; refuse unsupported synthetic claims; default ruleset requires source grounding for decision-alls.

---

## 8. Application and Submission Architecture

- **Applicant profile**: one-per-user participant/team profile; team has lead; identity is inherited from `users`.
- **Individual vs team**: an application belongs to one lead applicant and optionally a team; team lead gates submission/revision; members get view/limited rights.
- **Idea/project information**: structured core fields (title, problem, solution, AI component, target, approach, impact, scalability, model/business) — but where a question set is dynamic, add a **configurable question** mechanism so *flexible* fields don't require a permanent new column per program.
- **Supporting documents / media / technical evidence**: each is an explicit `application_media` row against inherited `media`, purpose-classified (proposal, deck, CV, technical file, screenshot, prototype, evidence), required-vs-optional, and access-classed (public/staff-only/judge-only/applicant/dept/confidential).
- **Declarations**: explicit applicant declarations (originality, no-conflict dataset, etc.) captured at submission.
- **Eligibility**: linked eligibility rules; submission-gated on completeness and window.
- **Draft vs submitted**: draft allows running progress, no locking; submission locks the snapshot and begins the stale/under_review pipeline.
- **Revisions/deadlines/locking**: submission window enforce deadline; submitted locked unless resubmission permitted by program policy; submission history is immutable audit of snapshots.
- **Version history**: each submission is a versioned `application_submissions` record with snapshot semantics (full or lightweight — open decision) plus `application_media` per submission where needed; no overwrite; applicant view/status never destroyed.

---

## 9. Workflow and State Machine

Recommended states (each scoped, per-program; driven by a real state machine, not free-form statuses):
- `draft` — applicant composing.
- `submitted` — candidate under review; locking applied if deadline reached/index.
- `under_review` / `screening` — staff processing; eligibility/screening outcomes.
- `eligible` / `ineligible` / `needs_manual_review` (screening outcomes — separate from final).
- `shortlisted` / `not_shortlisted` — staff-list decision.
- `invited_to_pitch` / `pitched` — pitch/event stage (optional per program).
- `assigned` — judge(s) assigned (parallel to evaluation).
- `evaluated` — independent evaluation complete/finalized.
- `under_deliberation` / `deliberated` — group dialogue open.
- `selected` / `finalist` / `waitlisted` / `not_selected` / `deferred` — formal decisions.
- `withdrawn` — applicant-initiated.
- `incubating` / `mentored` — post-selection support.
- `milestone_tracked` / (hold per program) — milestone progress.
- `completed` / `archived` — terminal.

Transition rules must be explicit, guarded by **permission + policy + domain validation**; states are authoritative and append-only history is retained (status-change ledger). A generic single "status" column is insufficient (matching the benchmark report's own guidance). Iterative/verdue that a fixed four catch-all stage list is illustrative, not final; actual transitions must be designed program by program.

---

## 10. Database Design Thinking

- **Normalization**: normalize data that must be queried/reported (applications, scores, assignments, decisions); keep flex in only JSON where appropriate.
- **Foreign keys**: explicit FKs with chosen delete behaviors (restricted/nullable for historical attribution; nullOnDelete for audit actors; cascade only where meaningless without parent).
- **Many-to-many**: join tables for judges↔applications (assignment), rubrics↔criteria, program↔partners (with relation type), teams↔users.
- **Polymorphic**: justified only for the inherited `media.attachable`, `activity_logs.subject`, `ai_interactions.target`/`ai_sources.source` — not as a substitute for designed FKs.
- **Constraints** as the enforcement: unique program code/slug, `(program, rubric version)`, `(rubric, criterion code)`, active-team-membership per user, application reference, finalization status; DB-level uniqueness/checks for evaluation integrity.
- **Indexes**: FK columns + high-frequency filters: program status, application (program/status/stage/submitted time), judge assignment, conflict status, evaluation judge/application/stage, AI source/task/created time, event start, incubation status, milestone due/status, allocation dates, partner program. Keep it portable to Postgres + SQLite.
- **Soft deletion**: use sparingly and only over documented semantics; for consequential history, prefer archival/immutability over soft-deleting rows.
- **Immutable historical records**: score rows, decision record, submission snapshots — treated append-only; no silent overwrite; reopens logged.
- **Audit/event records**: a state-change/status-history ledger and an append-only decision log fed by `activity_logs` plus domain-specific event rows where audit has shape.
- **Temporal data**: effective dates for rubric versions, assignment periods, resource allocations, membership.
- **Scoring records**: per-criterion scores immutable after finalization (raw + normalized derived columns only as explicit computed).
- **Workflow records**: stage/current transitions modeled not as free strings.

All portable to both engines (the starter already uses a `searchLike` macro precisely because pg-only SQL broke SQLite.)

## 11. Security and Integrity

Risks + controls (non-exhaustive; strongest severity first):

| Risk | Control(s) |
|---|---|
| Unauthorized access | Route `permission:` middleware + FormRequest `authorize()` + policy on every write/read; defense in depth. |
| Judge manipulation / score tampering | finalization lock, append-only ledger for changes, explicit reopen workflow (staff-gated), DB uniqueness, immutable rubric version. |
| Score tampering (stealth) | scores stored raw; any post-imp fired change is an event, not overwrite. |
| Applicant impersonation | auth (`MustVerifyEmail`, 2FA where required) + identity self-management; lead/team rules. |
| Confidential application exposure | evidence classification (privacy classes) + need-to-know roles + document access authorization; applicant-vs-internal separation. |
| Document access | media access via `media` + purpose/classification; authorized download only; hidden raw extraCore. |
| AI prompt/data leakage | internal source boundaries (PII/confidential isolation), no credentials, sanitized inputs, provider-neutral. |
| Privilege escalation | roles-only (Spatie), no ad-hoc direct permissions, system-role immutability, `Gate::before` only for Super Admin recovery. |
| Audit manipulation | append-only audit events, immutable historical rows, actor+timestamp on every consequential action; no stray `DB::` without trackers. |
| Conflict-of-interest abuse | conflict resolved at policy-dom (declaration→resolution), blocked before assign/evaluate/finalize; resolution audit. |

## 12. Notifications & Communications

- **Conceptually**, communicate the AI task lifecycle via the inherited notification system (`SystemMessageNotification`, DB channel, unread badge/preview) as v1; trigger domain events (application submitted, screening done, judge assigned, conflict, pitch invite, decision, mentor assigned, milestone due, event announced).
- Add **templated** decision/status notices (human-approved text) separate from raw AI drafts.
- Audit every send event.
- **Future channels**: email, SMS, calendar, real-time push (websocket/SES) — each a later/deployment decision; keep the abstraction so channels never bake into domain call-sites.
- Do not send a decision notification before the formal decision record exists.

## 13. Documents and Media

- **Storage**: reuse inherited `media` (≤20 MB via `StoreMediaRequest`, GD thumbnails ≤400px, categorized purpose, stored hashed/private disk, no public URL exposure, authorized download). For heavy video/OCR/large files, add background processing and strict size classing in a later phase.
- **Metadata**: `application_media` (application, media, purpose, optional submission, classification/classification) + raw media metadata (name, mime, size, original/stored name, extension).
- **Ownership**: media uploader/owner + assignment; no cross-app access.
- **Access control**: purpose + privacy/master classification gated by role policies (applicant owns; staff/judge need-to-know; confidential to level).
- **Versions**: per submission revision records tie documents to that snapshot.
- **AI artifacts**: generated files (PDF briefs/summaries/reports) must carry `ai_interactions`→`source`/review association and be distinctly (visibly-labeled) AI-generated; never stored as part of the authoritative application snapshot unless explicitly noted.

## 14. Reporting and Institutional Intelligence

Reports/dashboards needed:
- application **pipeline/funnel** (status headcount, stage counts, in-progress)
- screening outcomes /
- shortlist/decision summary
- judge workload (assigned vs. completed per program/judge)
- evaluation completion & score distributions (make distributions/staff-visible; raw score *only* where policy permits and never prematurely)
- deliberation + final decisions (audit)
- incubation/milestone/mentorship progress
- resource utilization
- post-program / alumni outcomes
- AI usage/audit report (to prove advisory + compliance).

Every report is permission-aware (`<resource>.view`), and AI reporting must be **grounded**; AI adds summary/trend narrative but the numbers always come from stored data. Dashboards are a common *view layer* — reuse `StatCard`/`RecentActivityPanel`/comparison components, not a second dashboard framework.

## 15. API and Integration Architecture

- **Auth**: Sanctum bearer tokens (inherited); `auth:sanctum`.
- **Authorization**: `permission:` middleware + FormRequest/Resource; versioned under `/api/v1` (conventions already present).
- **Versioning**: keep `/api/v1` prefix; only backwards-breaking changes to `/api/v2`.
- **Resources**: Eloquent API Resources to transform, `ApiPagination::response()` envelope (`data`, `links`, `meta.pagination`).
- **Validation**: FormRequests (`authorize + validate`).
- **Error responses**: JSON `{message}` for 401/403/422 in `bootstrap/app.php` (already configured for `api/*`).
- **External integrations** (future/deployment-defined): AI provider abstraction, email/SMS, document processing, cloud storage, partner systems — each behind adapters/design decisions.

## 16. Frontend / UX Architecture

- **Applicant experience**: program listing/detail; profile/team mgmt; user-facing application dashboard; a guided draft→submission form; status/evidence/declarations; outcome views.
- **Program-management workspace** (staff): program builder, stage/rubric config, screening queue, judge assignment, events, partners, decision workflow.
- **Judge workspace**: assigned-applications list and a focused judge assessment view: evidence + rubric, criterion scores, comments, **clearly separate** AI-assist panel (source-linked), conflict declaration, draft→finalize (independent of peers' raw scores until release).
- **Mentor workspace**: assigned cohort/goals, sessions, progress, feedback, alerts.
- **Decision dashboards**: pipeline, distribution, deliberation artifacts, decision form.
- **Evaluation forms**: one scored UI with per-criterion inputs, weighted summary *display*, with a lockout on final; visible regardless of device.
- **Comparison views**: cross-candidate (staff/deliberation) with isolated probes; never misleading ranks as decisions.
- **Notifications**: inherited bell + inbox + actionable reminders.
- **Accessibility + responsive**: reuse the shadcn-vue/reka-ui/`ui/*`/`admin/*` library (dialogs/skeletons/forms/tables exist), keyboard-navigable, focus-visible, mobile layout, dark-mode per inherited `HandleAppearance`.
- **Reuse over build**: use `ResourceTable`, `FormSection`, `StatCard`, `RecentActivityPanel`, etc.; regenerate Wayfinder routes.

## 17. AI Agent Architecture

Proposed agent layer (shared memory: an auditable event bus / provenance ledger + program context for privacy-scoped reads; each agent reads only its permission boundary):

1. **Application Analysis Agent** — summarization, eligibility/completeness triage, formatting (advisory).
2. **Judging Assist Agent** — evidence extraction, criterion mapping, briefing, outlier/question surfacing for judges. Boundary: never writes scores/decisions.
3. **Evidence Analysis Agent** — OCR/extraction/similarity/classification; treats inputs as untrusted; returns citations.
4. **Program Operations Agent** — pipeline summaries, judge-completion reminders, status briefs, workflow flagging.
5. **Mentorship Agent** — session summaries, action-item extraction, milestone reminders, progress summaries.
6. **Reporting Agent** — narrative summaries of real stored metrics; numbers always from DB.
7. **Knowledge / RAG Agent** — source-linked answers over documented data (programs, rubric, history rules) with provenance.

**Boundaries & governance**: each agent has explicit permission-scoped read/write; all write to the audit/provenance ledger, flagged AI-generated, and require a human approval gate for any external-facing or decision-adjacent output. Agents share only permitted context (never raw PII leaked); the human-in-the-loop is at every decisive boundary. The Agent Layer is a later phase — defined by interface, not cemented into v1.

## 18. MVP vs Future Phases

**MVP (V1 — trust path):** program + stages configuration + public listing/detail; program admin; application model (title/problem/solution/approach/impact etc. with core fields; a small default question set + a **single `value_text`+`value_json`** configurable-answer mechanism, not full engine); application: draft→submitted→revision-with-policy; eligibility screening incl. `manual review` outcomes + `eligible/ineligible`; a **simple** rubric version (immutable once used); judge profile + assignment; conflict**declaration & policy block; independent evaluation scoring + finalization lock; a human final decision record + `selected/finalist/waitlist/not_selected`; applicant + staff notifications (in-app); audit logs; AI client: only **application summary + evidence extraction + criterion mapping + missing-information** (advisory, human-reviewed) + `ai_interactions` ledger. No incubation/milestone/resource/showcase/partner/alumni beyond minimal scaffolding. MVP tests must lock trust rules: score regains after finalization are events; conflict blocks; decisions append-only.

**Phase 2:** mentorship (profiles/assignment/goals/sessions), milestones/progress, **simple** incubation enrollment, pitch/event basics, shortlist UX, lean judge analytics/outliers, notifications channels (email) prepared/deployment-decided.

**Phase 3:** full rubric versioning/promised immutability + advanced rescale, participant-facing applicant AI assistant (also reviewable) with sourcing, staff/membership assistants, partners/organizations, resources/workspace allocation, alumni, expanding reporting.

**Future/advanced AI:** multi-agent orchestrator (Applicant/Staff/Mentor assistants), deeper RAG, advanced semantic search, retention/compliance automation, autonomous recommendation (still human-approved) — gated to governance plan + approvals.

## 19. Critical Risks and Unknowns

- **Domain decisions undecided** (from requirements/schema open list): exact question architecture (v1 scope), submission versioning (snapshot depth), rubric immutability policy, judge assignment granularity (one authoritative model), visibility rules, conflict definition scope (direct / related-party), score visibility, conflict-blocking behavior, AI task permissions by stage.
- **Legal/compliance**: data residency, retention, consent, privacy of evaluators, cross-border AI data use with Ethiopian law — undefined at v1 scope.
- **Scale**: expected volume of programs/applicants/judges unquantified → informs indexes, queues, cost of AI, rate limits.
- **AI provider/data boundary**: which data leaves premise, routing to external LLMs, PII/document sanitation, cost tagging/hrss ≈ unaddressed.
- **Incubation/mentor/resource ecosystems**: scope creep risk; need explicit boundaries before incurring complexity.
- **Post-selection support breadth**: alumni/follow-up expected are wide; must be kept lightweight for v1.
- **Event/calendar depth**: whether full recurrence is needed (open). **Except for major decisions the user must make** (approx none in so far).

## 20. Recommended Architecture

- **Frontend:** Vue 3 + Inertia v2 + TS + Tailwind v4 + reka-ui/shadcn-vue, Wayfinder generated routes; reuse the inherited UI kit; per-host SPA + optional SSR; accessible, responsive.
- **Backend:** Laravel 12 (PHP 8.5) — controllers+FormRequests+policies; support services (`app/Support` precedents) for engines (screening, assignment, conflict, evaluation finalization, decisions); keep controllers thin; domain logic in an `EAIC/` module namespace layered on the reusable core; never edit `app/Support` core classes.
- **Database:** PostgreSQL (primary, prod-grade) + SQLite (dev/test) — both first-class; new domain migrations inherit the starter; no MySQL-specific types; no destructive DB commands.
- **Auth:** Fortify (login/registration/2FA/password reset/verification) + Sanctum tokens for API; reuse the `users` model, `email_verified_at`, password policy (≥12, mixed, etc.).
- **Authorization:** Spatie Permission one guard; add **18 inherited permissions + domain `*.view/create/update/publish/review/finalize/...`** in a project seeder; policies + `permission:` middleware + FormRequest `authorize`; never bypass policies or assign direct user permits.
- **Storage:** `media` disk (`local`) with explicit private/hashed, handled download; classification-policy. Future: cloud/S3/2store per phase.
- **Queue/jobs:** database queue (`jobs`/`failed_jobs`), scheduled tasks for deadline/judge-reminder/event-start async; heavy document processing queued.
- **Notifications:** inherited DB notification channel + abstraction for future email/SMS/push.
- **AI layer:** provider adapter (model-agnostic), task registry, prompt-versioned templates, `ai_interactions/sources/review_actions` ledger, hard human gate for consequential outputs; config for provider caps/rate/cost.
- **Agent layer:** a single orchestrator in v1 (no multi-agent mesh in MVP); future agent structured with provenance/shared ledger and permission-scoped.
- **Audit/event layer:** inherited `activity_logs` + append-only domain event tables (submission/status/score/decision records) — everything traceable/source-citable.
- **Reporting:** permission-gated dashboard views from real data; AI narrative auxiliary only.
- **External integrations:** adapters only, deferred to Phase 3+ (email/SMS/docs/partners) behind config & approvals.

Why this shape: it maximizes reuse (inherited auth/RBAC/settings/media/notifications/audit/UI/API), keeps the domain in one namespace, preserves SQLite+Postgres portability, and isolates the two biggest trust surfaces (influencing decisions + AI) behind explicit policy/ledger + human gates.

## 21. Assessment of Existing Documentation

- **AI-PROJECT-STARTER.md** — well-defined: the inheritance contract, what to reuse, never-to-rebuild, roles/permissions/security, onboarding/reading-order, handoff template. **Open:** the actual "upstream" claims are starter-specific (verified claims 3–10); fine. **Ambiguity:** no explicit guidance for which "domain" directories to create, how big project modules should live (an implementation decision Q).
- **MASTER-STARTER-ARCHITECTURE.md** — well-defined and exhaustive: stack, tables, routes, policies, tests, security, extension guide (great how-to). **Ambiguity:** none critical; **consider changing** — refresh the "Current Verified State" (2 pending migrations/db not yet migrated, and API v1 exactly in the section 15?), and note the test counts go up as domain staging; no missing material for planning.
- **PROJECT-REQUIREMENTS.md** — strong product vision and roles; missing/ weak: exact eligibility rules, application revision/resubmission policy, judge visibility, conflict rules, rubric versioning, AI retention, AI visibility to applicants, event depth, partner/incubation depth — all flagged as open decisions that must be resolved; it is a **design baseline, not permissive for implementation** — documented.
- **DATABASE-SCHEMA.md** — conceptually strong (entity inventory, migration order, principles, delete/unique/index/status/polymorphic thinking) but **explicitly draft v0.1**: "no migrations should be created yet" and depends on approval + resolution of open decisions; the risky design concerns flagged by model critique are **not** yet encoded (score/lFinalization, conflict related-party, rubric freeze). **Clarify before coding**.
- **PROJECT-ROADMAP.md** — well-structured phases, but broad; some phases bundle multiple trust-critical domains (Rubrics & Judge Management) that need discipline; ordering of AI (after evaluation) is correct; approve-gate is good. **Ambiguity:** which tasks go in a release (only a roadmap approval exists).
- **README.md** — human setup/ops baseline, fine.
- **AGENTS.md** — binding boost guidelines (versions, skills, style, testing, no-docs-without request). Fine/authoritative.

**What should be clarified before coding:** final product name, the exact application-question architecture (v1 scope), submission/versioning, rubric/immutability, judge visibility/conflict semantics, AI permission-by-stage/retention, event/calendar depth, partner portal depth, expected scale/volume, legal/compliance. **What should NOT change:** the starter's reused core (Fortify auth, Spatie RBAC, policy/middleware pattern, media, settings, notifications, audit logs, API envelope, UI kit, tests) and the dual-database strategy.

*(Benchmark report's own critique summary is intentionally not summarized — the above is an independent assessment.)*

---

## 22. Final Engineering Judgment

- **Feasibility:** technically sound. Laravel 12 + Vue 3 provide everything; the trust-authentic pieces (conflict, score lock, decision records, ledger) are well-trodden; AI governance is the main operational engineering challenge, not a blocking risk with a proper provider abstraction + ledger.
- **Is the doc mature enough?** **No — for migrations.** The requirements/schema are explicitly draft v0.1 with an approval gate. They are a *strong baseline* but the non-optional product decisions (§8 unknowns) must be resolved first. The *foundation* is proven; *domain* is not.
- **Three strongest architectural decisions:** (1) reuse the hardened, domain-neutral starter vs. building parallel infra (auth, RBAC, audit, UI, API); (2) **separation of AI output from human decision surfaces + an append-only/approval gate** around judge/decision/score integrity; (3) versioned immutable rubric + frozen evaluation scores with an explicit reopen procedure (the trust pins of the whole system).
- **Three weakest / gaps:** (1) no standard assignment/conflict authority model (program/stage/app ambiguity, related-party conflict) resolved; (2) no explicit status/version/immutability model (submission snapshots, status ledger, decision append-only) nailed into schema; (3) AI governance/retention (provider boundary, PII/leak, retention, prompt-injection) is only described, not engineered.
- **What I would change:* before it is a line: resolve the 6–8 open decisions; add explicit immutable/append-only flags + finalization locks in the logical schema; define required core- and deciding-parties; scope MVP to a sequential trust path only; add a schema review gate with an owner sign-off.
- **What the owner must decide now:** final name; eligibility rules (incl. org/startup and org applications); revision/resubmission policy; exact conflict definition; judge visibility; whether applicant organization/startup supported; expected scale; legal/compliance/data residency; one-source of even set of domain permissions/AI capability list per stage; retention; and MVP slice.

---

## 23. Benchmark Summary

- **Understanding: 8/10.** I demonstrate the product's true identity (multi-program, auditable, AI-advisory, not a CRUD), and I stayed faithful to the reuse-the-master-starter doctrine. Minor deductions: I did not (and it wasn't asked to be) timeline-end-date the actual 23 process steps and maps them to phases exhaustively; and perhaps could call risk the "inspection/reviewing" inspector actor that the benchmark's actor list mentions but requirements barely covers.
- **Architectural confidence: 8/10.** Strong on preserving starter infrastructure, separation of duties, `permission:` + policy + FormRequest, versioned immutable rubric, finalized-score isolation, ledger. Confidence bounded by the unresolved product decisions / several "open" items I honestly list.
- **Database/domain-model reasoning: 8/10.** Coherent entity model with ownership/audit/lifecycle; robustness to normalization,FK/m:n/polymorph/JSON field-spread discipline, indexes; honestly flagged drafted-you still needs the actual migration-type approval. Reasonable deductions: the `program_stages` ↔ evaluation `stage`/rubric linkage and related-party conflict are underspecified; naming/aggregates need schema-gate review/approval.
- **AI-agent design reasoning: 8/10.** Design well; honor; agent boundaries + permission-scoped reads, provenance, human in-the-loop, adjudicated; discussed multi-agent-as-future. Sust multilabel note: the row "score assist" must be MVP-driven generic and any score-suggest would need careful flagging.
- **Coding-readiness judgment: 3/10.** The **repo is a domain-neutral starter**; domain docs are explicit approval-gated. No migrations should be written until requirements+schema+decisions+roadmap are approved (in lockstep with the docs' own approval gate).
- **Most important discovery:** The difference between "the inherited starter is a *domain-neutral*, fully-tested core" and "every EAIC entity is a proposed downstream binding" — i.e., the single most binding fact is that **the EAIC domain is entirely in an un-approved draft, and deep engineering judgment is needed to respect the gate.**

- **Most important warning:** Before any single migration is written, resolve the **trust-critical workflow rules** (conflict "related-party" semantics, judge assignment pre-authority, rubric freeze, score finalization/append-only, and formal decision-record authority) and the **AI governance boundaries** (provider/data/PII boundary + retention), because they are the two places most likely to cause costly rework if an invalid state machine + advisory-AI is built on mis-specified rules.

---

*Document compliance: this is an independent analysis; I did not copy the referenced benchmarking-inspected- prior report nor anchor on it — any coincidences are incidental to the very same source material. No repository files were modified; the file intended here is the only artifact produced.*
