# Laguna S 2.1 Benchmark Report

## 1. Executive Summary

This repository is the **Laravel Master Starter** — a hardened, domain-neutral boilerplate (Laravel 12 + Vue 3 + Inertia) providing authentication (Fortify), RBAC (Spatie Permission), users/roles admin, settings, notifications, activity logging, media, search, import/export, API v1, and a complete admin UI shell. It ships with 127 passing Pest 4 tests.

The **Ethiopian AI Center (EAIC)** is a **proposed downstream project** — not yet implemented — with `PROJECT-REQUIREMENTS.md`, `DATABASE-SCHEMA.md`, and `PROJECT-ROADMAP.md` all drafted as v0.1 planning documents. **No domain code exists yet.** The starter has been readied with an approval gate and explicit instructions to the implementation agent to resolve open decisions before migrations.

The **planning foundation is solid and unusually disciplined** — it contains explicit approval gates, detailed phase dependencies, human-decision boundaries for AI, and a disciplined "reuse-before-build" posture. However, the **quality of the planning documents is uneven**: requirements are thorough but contain internal contradictions and ambiguous phrasing; the database schema is a strong conceptual design but omits critical constraints that the existing Codex report correctly flags; and the roadmap is overly linear (23 phases) with some phases far too large and others too early.

**Readiness assessment: (3) Significant clarification required.** The foundation is structurally sound, but open decisions are too numerous and the dependency chain too rigid to begin implementation safely at scale. Starting on Phase 1 (program foundation only) is defensible; attempting more would be premature.

---

## 2. Project Understanding

### What this project is trying to build

A multi-program, AI-assisted **innovation-program-management platform** (not a simple contest tool). It manages the full lifecycle from program announcement → applicant intake → screening → judging → deliberation → selection → incubation → mentorship → resource allocation → showcase → alumni follow-up. A **public-facing program announcement/detail experience** and an **applicant self-service workspace** are both in scope.

### Intended users/roles

| Role | Defined where | Notes |
|---|---|---|
| **Staff / Program administrators** | Requirements §3.1 | Configure programs, assign judges, manage screenings, run events, partners, selection |
| **Judges** | §3.2 | Score assigned applications, declare conflicts, deliberation, recommendation |
| **Applicants / Innovators** | §3.3 | Individuals or teams; discover programs, apply, submit evidence, monitor status |
| **Mentors** | §3.4 | Assign goals, hold sessions, track progress |
| **Partners / Vendors / Stakeholders** | §3.5 | External organizations; sponsorship, training, resources |
| **Super Admin / Administrators** | §3.6 | Reuses Master Starter RBAC — explicitly told not to create a parallel system |

The requirements correctly reference the inherited Master Starter roles (Super Admin/Manager/Staff/Guest). **Missing mapping**: the requirements define six new conceptual actor-types but do not specify how they overlay onto the four starter roles, or whether applicant/judge/mentor/partner are *users with additional domain profiles* or *users with assigned permissions*. The schema implies the former (participant_profiles, judge_profiles, mentor_profiles all link to users via FK). This should be made explicit.

### Main workflow/lifecycle

Requirements §5 lists 27 ordered steps: program creation → draft configuration → approval → publication → application opening → applicant registration → draft → submission → eligibility screening → administrative screening → shortlisting → judge assignment → COI declaration → evaluation → AI review → pitch/presentation → deliberation → final decision → applicant notification → incubation enrollment → mentor assignment → training → milestones → resource allocation → showcase → completion → alumni follow-up.

**Key principle stated correctly**: not every program uses every stage; workflow is configurable.

### Relationship between starter architecture and product

- **Starter**: domain-neutral foundation (auth, RBAC, audit, media, notifications, settings, search, import/export, API, UI kit). Fully tested, 127 passing tests.
- **Product (EAIC)**: downstream domain layer — programs, applications, judging, incubation, AI assistance — built *on top* by extending the starter's extension points (ActivityLogger, SystemMessageNotification, media morphs, settings registry, search groups, pagination envelope, permissions, policies, UI components).

The requirements §7 and §14 correctly enumerate what to reuse vs. build. The schema §4 explicitly states it "extends the inherited starter foundation rather than duplicating" core infrastructure. This boundary is clean and consistently applied.

The one architectural mismatch worth flagging: the starter's `Guest` role is for authenticated-but-nopermission accounts. The EAIC needs **public program listing** (Phase 1), which requires an **unauthenticated** route strategy that the starter explicitly removed (MASTER-STARTER §28/A.1: public website removed). This gap is acknowledged nowhere in the requirements or roadmap.

---

## 3. Requirements Review (`PROJECT-REQUIREMENTS.md`)

### Clear requirements

- **Product vision** (§1–2): well-articulated as an "innovation-program operating system" with AI assistance. Distinguishes itself from competition management. ✓
- **Actor taxonomy** (§3): six role families with meaningful responsibilities. ✓
- **Core product principles** (§4): 10 principles, strongest are "humans remain final authority," "AI explainable," "every consequential workflow auditable," "programs configurable," "preserve PostgreSQL/SQLite compatibility," "least-privilege access first-class." ✓
- **End-to-end lifecycle** (§5): 27-step enumeration. ✓
- **AI capability boundaries** (§7): clear separation of advisory-only AI functions vs. prohibited autonomous decisions. ✓
- **Non-goals** (§14): explicit list prevents scope creep (CMS, full LMS, ERP, autonomous AI judge, blockchain). ✓
- **Human Decisions Required Before Implementation** (§17): 22-item checklist. This is unusually thorough. ✓
- **Initial Release Boundary** (§18): phased focus: Program → Application → Screening → Judge Assignment → Evaluation → Deliberation → Selection, then Mentorship → Incubation → Milestones → Resources → Showcase. ✓

### Ambiguous requirements

- **Section 17 lists 22 decisions.** Many are genuinely unresolved; the former working-name decision is now resolved as Ethiopian AI Center (EAIC). The remaining decisions still require owner approval.
- **"Domain logic remains in the downstream project" (§4.8)** vs. **"Reuse Master Starter infrastructure" (§4.7)**: boundaries around what constitutes generic vs. domain logic are not always clear (e.g., is conflict-of-interest detection a domain rule or infrastructure?).
- **Team application policy** (#3 in §17): "Whether organization/startup applications are allowed" is listed as undecided, but §3.5 and §6.9 acknowledge partner organizations — the relationship between "organization as applicant" vs. "organization as partner" is not clarified.
- **Anonymous/public application policy** (#4): not addressed in any section body, only in the open-decisions list.
- **"Configurable question mechanism"** (§6.6 and DATABASE-SCHEMA §337): explicitly flagged as an open architectural decision but the roadmap Phase 3 commits to building "program-specific questions." This is a genuine contradiction (see F.1).
- **Public program listing/detail** (Phase 1): implies a public/routing layer, but the starter explicitly "assumes authenticated use" (MASTER-STARTER §28) and removed the public landing page by design. No public-facing route strategy is described.

### Missing requirements

- **No SLAs / performance targets** (beyond "responsive UX"). §15 of requirements lists 13 product success criteria all phrased as features, no performance/non-functional thresholds.
- **No accessibility requirements** despite a Vue/Inertia/Tailwind UI surface.
- **No internationalization beyond "English locale exists"** — despite i18n foundation being inherited, the requirements never state whether multilingual UI is in scope.
- **No concrete data-retention policy** for the domain (alumni, follow-up, AI outputs) — §17 #12 asks "AI retention policy?" but the schema and roadmap never propose one.
- **No mobile/app requirements** — the API section (§12) mentions "mobile clients" as a consumer but there's no mobile strategy.
- **No incident/response process** for the AI governance layer.
- **Section 18 (Initial Release Boundary)** omits AI from the first boundary — but §7 lists "AI-assisted judging without replacing humans" as success criterion #6. There's a tension: the success criteria list AI assistance as a v1 requirement, but the implementation boundary defers AI until after Selection. This is a **contradiction** (see F.1).

### Contradictions

1. **AI in v1**:
   - §15 Success Criteria #6: "AI-assisted judging without replacing humans."
   - §18 First Implementation Boundary: first slice is Program→Application→Screening→Judge Assignment→Evaluation→Deliberation→Selection (no AI). Then Phase 7 (Judge Copilot) is a separate later phase.
   - §7.1 MVP "MUST HAVE" item: "Basic audit logs and AI interaction logging with source references" — this implies *logging* AI is an MVP requirement, but *using* AI is not.
   - **Verdict**: mildly inconsistent framing. The success criteria list is aspirational; the implementation boundary defers actual AI assistance. This is defensible but should be disambiguated.

2. **Configurable questions vs. fixed fields**:
   - §6.6: "Do not turn every future question into a permanent column. Use a configurable question mechanism where appropriate." (strong language)
   - DATABASE-SCHEMA §337: "Open decision: whether v1 needs a full configurable form engine or a smaller fixed set of application columns."
   - §17 #2: "Whether application questions are an actual configurable form engine or a simpler program-defined fixed set of fields."
   - Roadmap Phase 3 explicitly builds "program-specific questions."
   - **Verdict**: The requirements say "configurable mechanism," the schema says "open decision," and the roadmap says "build questions." The requirements should be tightened to state one or the other (configurable, but with a simplified subset for v1).

3. **Product name**: The former working-name contradiction is resolved by the approved Ethiopian AI Center (EAIC) rename.

### Requirements that cannot yet be objectively tested

- §4.8: "Domain logic remains in the downstream project rather than generic starter infrastructure." (boundary definition — subjective)
- §7.2: "AI outputs must be explainable enough to review and, where practical, tied to source evidence." (no measurable threshold for "practical")
- §4.1: "Every consequential workflow is auditable." (no definition of "consequential")
- §7.14: "Deliberation summarization" (Phase 8 AI). No test criterion for quality of a deliberation summary.
- §15.9: "AI outputs are stored with source references and human review status." (Codex §14 item 6 correctly flags this as acceptance-criteria-like; the requirement documents itself, but the *threshold* for "source-grounded" is unspecified in requirements.)

### Requirements that create implementation risk

- **"No silent score mutation"** (§7.14, Codex 14 p. 579, 581): implies an append-only scores/audit table that Codex correctly flags as missing from the schema. Building this after v1 starts without a schema fix creates rework.
- **"Judges should not see other judges' raw scores"** (implicit in §7.6 / Codex §7.4): requires a *visibility model* baked into the evaluations/scores design — if deferred, the schema will need alteration.
- **"Conflict of interest must block assignment before evaluation"** (§6.12, §7): requires cross-entity conflict resolution (applicant ↔ team ↔ organization ↔ partner ↔ mentor). The schema's `conflict_declarations` table (Codex p. 473/477) allows declaration but the resolution/review lifecycle and its *blocking* effect on `judge_assignments` is not modeled.
- **"Rubric versions become immutable once active evaluations depend on them"** (§7, Codex §5 p. 145): the schema marks this an "open decision" (DATABASE-SCHEMA §431). This is a critical v1 gating item that must be resolved before Phase 5.

---

## 4. Architecture Review (`MASTER-STARTER-ARCHITECTURE.md`, `AI-PROJECT-STARTER.md`)

### Does the proposed architecture fit the product?

**Yes, exceptionally well.** The Master Starter is a generic internal-tool foundation with:

- Layered auth (Fortify headless + 2FA + recovery codes)
- Multi-role RBAC with protected system roles and a Super Admin bypass
- Activity logging with polymorphic subject + actor (nullable FK)
- Media library with `attachable` morphs — directly reusable for `application_media`
- Settings registry — reusable for program defaults
- Notifications center with database channel — reusable for all domain events
- Global search with permission-gated groups + `searchLike` macro
- Export center (CSV/XLSX/XML/PDF/print) + two-phase CSV import engine
- Sanctum API v1 with consistent pagination envelope (`ApiPagination`)
- Complete Vue/Inertia/Tailwind/shadcn admin shell with typed Wayfinder routes
- Pest 4 testing at 127 green tests

The EAIC domain (programs → applications → judging → deliberation → decisions → incubation) maps cleanly onto "extend infrastructure, add domain." The starter's deliberate constraints (teams disabled, no CMS, no LMS) align with the project's non-goals.

The one architectural mismatch worth flagging: the starter's `Guest` role is for authenticated-but-nopermission accounts. The EAIC needs **public program listing** (Phase 1), which requires an **unauthenticated** route strategy that the starter explicitly removed (MASTER-STARTER §28/A.1: "Public website... removed"). This gap is acknowledged nowhere in the requirements or roadmap.

### What is already provided by the starter

Everything in AI-PROJECT-STARTER §3 inventory is confirmed present and verified by the Codex report (which itself references MASTER-STARTER §31 "Verified current run: 127 passed, 2 skipped, 768 assertions"). The architecture document is internally detailed and largely self-consistent.

### What must actually be built

All domain entities in DATABASE-SCHEMA §5 (entity inventory): programs, stages, participant_profiles, teams, team_members, applications, submissions, applications_questions, applications_answers, application_media, screenings, screening_results, judge_profiles, judge_assignments, conflict_declarations, rubrics, evaluation_criteria, evaluations, scores, ai_interactions, ai_sources, ai_review_actions, events, deliberations, selections, incubation_enrollments, mentor_profiles, mentor_assignments, mentorship_goals, mentorship_sessions, milestones, milestone_updates, resources, resource_allocations, organizations, organization_contacts, program_partners, partner_contributions, alumni_records, follow_up_updates.

Plus: new permissions (§30), policies, routes, controllers, requests, resources, Vue pages, Wayfinder regeneration, and integration of domain entities into search/export/audit/notification.

### Architectural risks

1. **Public-facing surface**: The starter assumes authenticated use (Root URL redirects to login/dashboard/profile). Phase 1 requires public program listing — an architectural addition not accounted for in the starter. Risk: building an ad-hoc public route layer that conflicts with the starter's Inertia-rooted design, or violating the "no CMS" principle.

2. **AI integration depth**: The requirements treat AI as a pervasive cross-cutting concern with 5 distinct assistant types across 4 phases (Phase 7, 18, 19, 20). The starter has **zero AI infrastructure**. This means building a provider-abstracted, prompt-versioned, source-grounded, retention-policy-driven AI subsystem — significant scope that's deferred deep into the roadmap but referenced as an MVP logging requirement. Risk: underestimating the architectural surface of the AI layer.

3. **Configurable workflow engine**: The requirements emphasize "configurable stages," "configurable eligibility rules," "configurable questions." The starter is opinionated and minimal. Risk: the team may build a meta-engine too early instead of concrete, tested stages.

4. **Multi-phase sequential dependency**: Phases 0→1→...→23 are strictly linear (PROJECT-ROADMAP §31). The Codex report §11 correctly identifies this as "too broad in several places and not strict enough on the dependency order for trust-critical phases." Risk: a blocker in an early phase halts everything; no parallelizable slices.

### Unnecessary complexity

- **"Domain-neutral platform"** aspiration conflicts with the sheer breadth: 37 domain tables proposed (DATABASE-SCHEMA §5), 23 phases, 6 AI-assistant types, full partner/resource/alumni ecosystems. This is not "v1" scope. The Codex MVP "MUST HAVE" (p. 428) is 14 items; the schema proposes 37 tables; the roadmap proposes 23 phases.
- **Phase 11 (Mentorship) through Phase 20 (AI Mentor Assistant)** — seven phases of escalating complexity for a feature set (mentorship automation) the requirements themselves defer ("Should have," not "must").

### Missing architectural decisions

- **Public routing strategy**: No documented approach for unauthenticated access to program listings.
- **AI provider adapter**: §4.4 (DATABASE-SCHEMA) mentions `provider` and `model` fields in `ai_interactions`, but no adapter/abstraction design exists. The requirements §4 note "provider abstraction: required" (Codex §7 p. 345). Missing from both the schema and roadmap as a concrete architecture decision.
- **Conflict resolution engine**: Not architected — the schema has a table, but the blocking logic is unspecified. Codex §9 p. 683 correctly lists this as critical.
- **Evidence privacy classification**: Codex §4 p. 140 correctly flags missing "privacy classification for evidence" (public, staff-only, judge-only, applicant-visible, confidential). Not in schema or requirements as a concrete decision.

---

## 5. Database Review (`DATABASE-SCHEMA.md`)

### Entities

Well-scoped (DATABASE-SCHEMA §5 lists 37 domain tables). All inherit the 13 starter tables (§3). The conceptual model (§35 Mermaid ERD) is readable and covers the core lifecycle. The entity inventory is comprehensive for the stated scope (§4 architecture overview).

### Relationships

Mostly correct (Mermaid ERD is coherent). Key observations:

- `applications.lead_user_id` → users ✓ ; `applications.team_id` nullable ✓
- `judge_assignments` has optional `program_id`, `stage_id`, `application_id` — this is the **core ambiguity Codex flags** (p. 166/280). A single row with all three nullable makes precedence ambiguous. Needs one authoritative assignment path or explicit precedence rules.
- `conflict_declarations` FKs to `judge_user_id`, `application_id`, `team_id`, `organization_id` — good, supports the cross-entity conflict model.
- `media` reuses inherited morph (`attachable`) — `application_media` is an explicit join, which is a reasonable, auditable choice.

### Keys

- Primary keys documented as `id` everywhere (no indication of UUID vs auto-increment — a **gap**). The starter uses auto-increment IDs (MASTER-STARTER §18). The schema should explicitly state PK strategy per table.
- Unique constraints enumerated in §25 and §44–56 per table, but **incomplete**.

### Constraints

- **Critical omission**: `evaluation_scores` has no uniqueness constraint on (evaluation_id, criterion_id). DATABASE-SCHEMA §25 lists "score per evaluation/criterion" as a likely unique constraint "must be validated against workflow requirements." Codex p. 127 correctly flags this as a risk ("score row should be append-only"). **Not enforced.**
- **Critical omission**: `judge_assignments` has no uniqueness preventing duplicate active assignments for the same (judge, program, stage, application). Codex §4 p. 166 flags this explicitly.
- **Critical omission**: `conflict_declarations` has no enforced *blocking* relationship to `judge_assignments` — it's a data table, not a constraint.
- No `created_by`/`updated_by` on most tables (DATABASE-SCHEMA §24 says "should be considered but not on every table blindly"). This creates **audit attribution gaps** — the `activity_logs` table has `actor_id` (nullable FK nullOnDelete), so domain mutations are logged, but schema-level audit attribution is inconsistent.
- No soft-delete pattern documented. §24 "exact delete rules are an approval item." **Risks irreversible loss.**

### Lifecycle/state modeling

- **Weakest area.** Database-SCHEMA §27 says "Use scoped statuses" and "Avoid one global lifecycle enum." This is good guidance, but the schema provides **no transition rules**, **no append-only status history**, and **no finalization locks**. Codex p. 16–17, p. 140, and §5 correctly flag this repeatedly.
  - `applications.status` and `application_submissions.status` are free-form — no `application_status_history` table for state transitions.
  - `judge_evaluations` has no "finalized_at" / "finalized_by" fields. Codex §4 p. 164 flags this.
  - `rubrics` version immutability (DATABASE-SCHEMA §431) is an "open decision," not designed.
  - No `selection_decisions` immutability constraint (`is_final` field exists but no append-only enforcement).

### Normalization concerns

- Generally normalized (good — no repeating groups in evidence).
- `participant_profiles`, `judge_profiles`, `mentor_profiles` are nearly identical (bio, organization, expertise JSON, specialization JSON, availability_status). This is **triplication**. Codex §4 p. 135 implicitly notes this. Consider a unified `person_profiles` table with a `profile_type` discriminator — OR document why separation is intentional. Separation is *defensible* (different permissions/workflows), but unstated.
- `application_questions` + `application_answers` (§8) is a flexible EAV store. The schema acknowledges this. Risk: if questions are truly free-form, querying and reporting become difficult. DATABASE-SCHEMA §29 "Do not put query-critical IDs, statuses, scores, dates, or permissions in JSON" is followed for core fields, but `configuration` JSON on eligibility rules and questions is acceptable.

### Audit/history requirements

- The inherited `activity_logs` (actor_id FK nullOnDelete, polymorphic subject, JSON properties, IP) is reusable for **all** mutations (DATABASE-SCHEMA §21 enumerates 12 event names). Good.
- **Gap**: No domain-level immutable history tables for `score` changes, `application.status` transitions, `rubric` versioning, `selection_decisions` appends. These are audit-critical per the requirements (§4.3 "Every consequential workflow is auditable") but not modeled. Codex §4 p. 169–174 and §9 p. 322 flag several missing tables in this category.

### Authorization implications

- §30 defines potential permissions (`programs.view`, `applications.review`, etc.) but explicitly states "not yet approved." This is correct — the permission catalog is empty in the actual schema (no permission tables referenced).
- **Gap**: The schema models *who* (user FKs) but rarely *which role can do what*. E.g., `conflict_declarations.status` has no documented workflow tying reviewer access to permissions.
- `resource_allocations` polymorphic-ish assignee (`application_id | team_id | user_id` nullable) — the schema notes overlap validation must be in the service layer. Good acknowledgment (Codex p. 186).

### Does the schema support the requirements?

**Partially — conceptually yes, operationally no.**

The schema captures the *entities* and *relationships* for all 27 lifecycle stages. But it **does not** capture:

- State transition rules (Codex §5 p. 171, p. 208)
- Score immutability / finalization (Codex §4 p. 164, DATABASE-SCHEMA §21 lists `evaluations.finalized`)
- Conflict blocking enforcement (Codex §4 p. 169, DATABASE-SCHEMA §24 "exact delete rules are approval items")
- Rubric version immutability (DATABASE-SCHEMA §431 open decision)
- Score finalization timestamps (Codex §4 p. 170, p. 302)

The Codex report (DATABASE §4) correctly concludes: "The schema should state whether the evaluation record is a draft before final submission and whether score rows are updatable after finalization." The current schema does **not** state this.

This is the **single most important gap**: the schema supports the * nouns* of the workflow but not its *verbs* (state transitions, locks, immutability).

---

## 6. Roadmap Review (`PROJECT-ROADMAP.md`)

### Does the implementation sequence make sense?

**Largely correct order** (Phase 0 → 1 → 2 → 3 → ...), especially:
- Phase 0 (init + validation) gates everything ✓
- Program foundation → Applicant foundation → Applications → Screening → Judging — this is the correct trust-critical ordering ✓
- AI introduced after evaluation stability (Phase 7) ✓
- Incubation/mentorship after final decisions (Phase 10+) ✓

Codex §11 p. 605–609 correctly validates this dependency logic.

### Dependencies between phases

§31 "Phase Dependencies" documents a strict linear chain: Phase 0↓Phase 1↓...↓Phase 23. AI phases (7, 18, 19, 20) are gated on their supporting deterministic workflows. This is correct.

### Missing prerequisites

- **"Program foundation before applications"** is stated, but Phase 1 also requires a **public program listing** (database SCHEMA does not model a `program_visibility` flag — DATABASE-SCHEMA `programs` table has `published_at` but no `is_public` field). Phase 1's "public program listing/detail" is a missing prerequisite in the schema.
- **Conflict resolution engine** (Codex §9 p. 683, p. 716) must exist before Phase 5 (Judge Assignment) — but Phase 0's exit criteria only say "resolve open schema/product questions." No explicit conflict model approval gate exists in Phase 0.
- **Rubric freeze rules** (Codex §4 p. 145, §5 p. 246) must be decided in Phase 0 to unblock Phase 2's "rubric versioning" task — but DATABASE-SCHEMA §431 leaves rubric versioning as an "open decision." Phase 0's tasks (§3.6: "resolve open schema/product questions") are too generic to catch this.

### Risky sequencing

- **Phase 7 (AI Judge Copilot, 6 capabilities) launched immediately after Phase 6 (Independent Judge Evaluation).** The Codex §11 p. 610–611 correctly flags this: AI should enter a *mature* evaluation workflow, not a just-stabilized one. Adding summarization + evidence extraction + criterion mapping + missing-info detection + risk generation + judge briefing generation in one phase is overly broad.
- **Phase 4 (Eligibility & Screening) comes after Phase 3 (Application & Submission) but BEFORE Phase 5 (Rubrics & Judge Management).** Screening logically depends on knowing the evaluation framework. The ordering is acceptable but the boundary between "eligibility screening" and "rubric evaluation" is not crisp in the phases.
- **Phase 18 (Applicant AI Assistant), Phase 19 (Staff AI Assistant), Phase 20 (Mentor AI Assistant)** come after Phase 17 (Post-Program). These are extremely late — but applicant-facing AI (Phase 18) should arguably come *early* since Phase 3's application form needs an AI assistant. This ordering conflict is not addressed.

### Premature implementation

- **Phase 11–14 (Mentorship, Milestones, Resources, Events 14)** and **Phase 15–17 (Partners, Reporting, Post-Program)** are all deferred until *after* the full lifecycle (Phase 10 Incubation). This is correctly conservative. Good.
- **Phase 14 (Training/Events/Showcase)** is split awkwardly: `program_events` (DATABASE-SCHEMA) models events, but Phase 14 is listed *after* Phase 13 (Resources) and *before* Phase 15 (Partners). The dependency for event *attendance* requires participants (Phase 11) — minor ordering issue.

### Approval gates that should exist before coding

- §28 "AI-Agent Work Method" item 5: "request approval for major architectural changes" — exists as guidance.
- §36 "Roadmap Approval Gate" lists 5 pre-coding approvals: approve requirements, approve schema, resolve open decisions, approve roadmap, generate task backlog. **Good.**
- **Missing**: No explicit **architecture review gate** between schema approval and Phase 1. The AI-PROJECT-STARTER §10 (Definition of "Ready to Code") is a 10-point checklist but it is for *agents*, not an *approval gate*. The roadmap's Phase 0 (§3.5 task: "produce an implementation architecture plan") covers this implicitly.

---

## 7. Cross-Document Consistency

This is where the strongest contradictions emerge.

### Contradictions (F.1)

| Claim | In Document | Contradicts |
|---|---|---|
| Product name "Ethiopian AI Center (EAIC)" is now official | §1 | Former §17 #1 naming decision is resolved; no contradiction remains |
| AI interaction logging is an MVP MUST HAVE | §15.9 | §18 Initial boundary excludes AI entirely → **contradiction** |
| 22 open decisions listed | §17 | §36 Approval Gate says "resolve critical open decisions" (undefined threshold) → **ambiguity** |
| Single DB migration strategy | DATABASE-SCHEMA §33 | §28 "no major unresolved architecture contradiction remains" Phase 0 exit → **risk**: schema is conceptual, not migration-ready |
| "Do not turn every future question into a permanent column" | §6.6 | Phase 3 builds "program-specific questions" in the same phase → **unresolved design** |
| PostgreSQL primary DB, SQLite for tests | §4.6, DATABASE-SCHEMA §31, PROJECT-ROADMAP §3.4 | MASTER-STARTER §2: "SQLite is the shipped default; PostgreSQL is first-class" → **tension, not contradiction** (roadmap says choose PostgreSQL for development) |
| Guest = zero-permission authenticated user | §3.3, AI-PROJECT-STARTER §126 | EAIC Guest/Staff roles are not mapped to the starter's 4 roles → **missing mapping** |

### Terminology inconsistencies (F.2)

- **"Application"**: Used in requirements for "an applicant submitting a proposal" (§3.3, §6.6). In standard Laravel/WebAuthn usage and the starter, "application" can mean "the software app." DATABASE-SCHEMA uses `applications` table. MASTER-STARTER uses no equivalent concept. **Ambiguity risk**: "Apply for program" vs. "use the application software." Within EAIC docs this is consistently "an applicant's submission" — low risk but worth a glossary.
- **"Stage"**: DATABASE-SCHEMA §4.3 architecture overview shows `Program → Stages` and `Application → current_stage_id`. But `application.current_stage_id` references... what table? `program_stages`? DATABASE-SCHEMA §9 (`applications.current_stage_id`) doesn't specify the FK target. The Mermaid ERD doesn't show `applications.current_stage_id` at all. **Inconsistency**: unclear whether application-stage is a FK to program_stages or a separate concept.
- **"Mentor"**: DATABASE-SCHEMA §15.1 `mentor_profiles` and §15.3 `mentor_assignments`. PROJECT-ROADMAP §6.3.11 lists "mentor assignment" and "feedback." §14 (AI assistant) says "Mentor Assistant." The *domain Mentor* (human) role is distinct from the *AI Mentor Assistant*. This is clear but terminology could collide.

### Requirements missing from schema (F.3)

- §15.13 "applicant notification of outcome" — schema has no `notifications` modeling (relies on inherited `notifications` table) ✓ reused. **OK.**
- §15.12 "final decision immutability" — schema has `selection_decisions.is_final` but no append-only constraint. **Gap.**
- §7.6 "final evaluation → immutable" — schema has no `finalized_at`/`finalized_by`. **Gap.** (also noted in Database Review)
- §6.14 "AI governance: Track provider/model/task/prompt-version/sources/output/retention" — schema models `ai_interactions.provider/model/prompt_version/metadata` ✓ and `ai_sources` ✓ and `ai_review_actions` ✓ and "Actions: accepted, modified, rejected, not_used" ✓. **Good alignment.**
- §15.14 "AI outputs are stored with source references and human review status." — **Well-aligned** with schema §12.
- §4.3 "Every consequential workflow is auditable" — activity_logs covers mutations, but **state transitions** (status changes) are NOT captured unless each controller calls ActivityLogger. The schema does not enforce or model status history. **Gap.**

### Schema concepts missing from requirements (F.4)

- `milestone_updates` table (DATABASE-SCHEMA §15.5) — supports per-milestone progress with evidence + reviewer comments. Requirements §6.22 says "progress updates, evidence, reviewer comments" — **aligned**, but the requirements don't mention *reviewer attribution on updates*.

- `follow_up_updates.report_type` (DATABASE-SCHEMA §26.2) — Requirements §6.26 says "alumni, follow-up updates" — **aligned**.

- `resource_allocations` polymorphic assignee (`application_id | team_id | user_id` — DATABASE-SCHEMA §17.1) — Requirements §6.23 says "allocation, assignee" — **aligned**, but the *polymorphic assignee* design decision is not surfaced in the requirements.

- `program_eligibility_rules.rule_type` (DATABASE-SCHEMA §6.3) — Requirements §6.9 lists "geographic, participant, document, completeness, deadline, technical" rules but doesn't constrain `rule_type` to a taxonomy. The schema leaves it open. **Minor gap — Codex §4 p. 127 correctly flags**.

### Roadmap tasks unsupported by requirements (F.5)

- Phase 1 "basic public program visibility" — Requirements §6.3 mentions "public program listing/detail" but does **not** state whether this requires unauthenticated access or just staff-managed public pages. **Ambiguous support.**
- Phase 13 "resource catalog" (workstation, GPU, lab, meeting room) — Requirements §6.23 is explicit ✓.
- Phase 15 "program partnerships, contributions" — Requirements §6.24 explicit ✓.

### Architecture assumptions not documented elsewhere (F.6)

- DATABASE-SCHEMA §20: "Use `resource.action` naming, follow Master Starter conventions." — The specific permission set (§30) is "not yet approved." The assumption that the existing 4 roles + extension pattern suffices is **documented** ✓.
- DATABASE-SCHEMA §31: PostgreSQL-first dev, SQLite tests. — **Documented** ✓.
- DATABASE-SCHEMA §27.3: `media.attachable` reuses inherited morph. — **Documented** ✓ (AI-PROJECT-STARTER §79).
- DATABASE-SCHEMA assumes `applications` is the **central hub** of the domain — every major entity (`screenings`, `evaluations`, `deliberation_items`, `selection_decisions`, `application_media`) references it. This is a **sound, documented architectural decision** (Mermaid ERD confirms).

### Duplicated or conflicting decisions (F.7)

- **Conflict-of-interest model** is described three times: (1) Requirements §6.12 and §4.3 (audit), (2) DATABASE-SCHEMA §15 (table only), (3) PROJECT-ROADMAP Phase 5 (tasks: "conflict-of-interest declarations," "conflict resolution"). The table in §5.3 does not model the *resolution* lifecycle beyond a `status` string. **Duplication, not conflict.** The gap (resolution lifecycle) is unmodeled.

- **Application status** is in `applications.status` (§9.6) and `application_submissions.status` (§10.3). Both are free-form enums. No single source of truth. **Duplication with no reconciliation.**

---

## 8. Codex Benchmark Review (`CODEx-BENCHMARK-REPORT.md`)

I reviewed the Codex report against the source documents rather than accepting it. Here is the verification/ challenge breakdown for each major conclusion:

### Confirmed with evidence (Codex conclusions that are correct)

| Codex conclusion | Evidence / location | Verdict |
|---|---|---|
| "Current codebase is Laravel Master Starter, not a domain implementation" | AI-PROJECT-STARTER §1: "Everything in Sections 3–10 already exists"; MASTER-STARTER §1: "no business domain entities" | **CONFIRMED** |
| "Master Starter includes Fortify auth, Spatie RBAC, notifications, activity logs, media, settings, search, import/export, API v1" | AI-PROJECT-STARTER §3 (table), MASTER-STARTER §3–9 | **CONFIRMED** |
| "product is more than a competition tool" (operational platform with post-selection support) | PROJECT-REQUIREMENTS §2 ("broader than a competition management system"), §5 lifecycle steps 19–27 | **CONFIRMED** |
| "AI must be advisory, not autonomous" | PROJECT-REQUIREMENTS §2 "AI assists humans," §4.1, §7.14 Codex p. 46 list of prohibited autonomous actions | **CONFIRMED** |
| "rubric versions should be immutable once evaluations begin" | PROJECT-REQUIREMENTS §6.10 ("Rubric versions become immutable"), DATABASE-SCHEMA §431 | **CONFIRMED** |
| "judges cannot see other judges' scores before deliberation" | implied in §7.6 / Codex p. 46 | **CONFIRMED (implicit)** |
| "conflict declarations must block assignments before evaluation" | PROJECT-REQUIREMENTS §6.12, §7; DATABASE-SCHEMA §15 | **CONFIRMED** |
| "reusable CSV import engine with two-phase preview/commit" | MASTER-STARTER §14 | **CONFIRMED** |
| "XLSX/PDF/XML exports are implemented" (addressing misconception) | MASTER-STARTER §14 p. 593: "XLSX/XML/PDF **are** implemented" | **CONFIRMED** |
| "SQLite is the shipped default, PostgreSQL first-class" | MASTER-STARTER §2, §31 | **CONFIRMED** |
| "No PHP static analysis (PHPStan) configured" | MASTER-STARTER §23 p. 940 | **CONFIRMED** |
| "Frontend has no JS unit suite; quality via ESLint + vue-tsc + build" | MASTER-STARTER §22 p. 910 | **CONFIRMED** |
| Codex §4 recommendation: "Do not depend on PostgreSQL-only exclusion constraints" | DATABASE-SCHEMA §31 | **CONFIRMED** |
| Codex §9 p. 683: "conflict resolution engine must exist before Phase 5" | PROJECT-ROADMAP Phase 5 builds "conflict-of-interest declarations" | **CONFIRMED (missing prerequisite)** |

### Challenged / nuanced (Codex claims needing qualification)

| Codex claim | My finding |
|---|---|
| Codex §9 p. 464: "Missing: screening decision history separate from current status" | The schema *does* separate `application_screenings` (one per screening event) from `application_screening_results` (rule-level). This provides *event-level* history. What's **missing is an `application_status_history`** table — Codex conflates screening history with application-status history. **Partially correct, imprecise.** |
| Codex §9 p. 466: "Missing: a privacy/access classification for evidence" | Confirmed absent from `application_media.purpose` (DATABASE-SCHEMA §10.3 only lists `purpose`, not `privacy_classification`). **CONFIRMED.** |
| Codex §9 p. 470: "Missing: explicit association from evaluations to rubric version" | `judge_evaluations` is not shown referencing `program_rubrics.id` in DATABASE-SCHEMA §17 (`judge_evaluations` columns listed p. 502–505: id, application_id, rubric_id, stage_id, status, started_at, finalized_at, finalized_by, recommendation). **`rubric_id` IS present.** Codex is **incorrect** here — the schema does associate evaluations to rubrics. However, Codex's *underlying concern* (immutability audit trail) is valid. Codex overstated the schema gap and under-cited the existing field. |
| Codex §9 p. 472: "Missing: `is_locked`/`is_blocked_by_conflict` derivation in evaluator assignment" | The schema has no such column. **CONFIRMED.** |
| Codex §11 p. 604: "AI guidance should not be introduced before evaluation workflow is stable" | Confirmed — Phase 7 (AI) comes after Phase 6 (evaluation). But Codex p. 606 understates the *degree* of integration: Phase 7 lists 6 capabilities as one phase "too broad." | **Valid concern, under-scoped.** |

### What Codex missed

1. **Public-program access**: Codex never notes that Phase 1 requires an unauthenticated route layer that the starter explicitly removed (MASTER-STARTER §28 / A.1: "Public website... removed"). The requirements (§5 step 4, §6.3) assume public visibility, but the starter's `routes/web.php` root redirects to auth-gated destinations (MASTER-STARTER §20 p. 807: `GET / → named home`). **Codex missed a foundational architecture mismatch.**

2. **Permission catalog gap**: The schema (§30) lists "potential examples (not yet approved)" for permissions. Codex's §8 "Required project permissions" (14 items) is actually **more complete than the product's own requirements document** — the product's `PROJECT-REQUIREMENTS.md` §4.6 only says "Permission requirements: reuse `resource.action`" generically. Codex invented/assumed the permission list. **Good initiative, but the product docs are weaker here than Codex's output suggests.**

3. **Configurable question engine scope**: Codex §5 p. 138 ("application_questions... need final policy on full engine vs. lightweight store") — but Codex **does not flag the roadmap contradiction**: §6.6 of requirements says "use configurable mechanism," the schema says "open decision," and the roadmap Phase 3 commits to building it regardless. Codex treats these as separate; the **contradiction is a risk Codex missed**.

4. **Role/role mapping ambiguity**: Neither the product requirements, schema, nor Codex explicitly state how the six actor types (Staff, Judge, Applicant, Mentor, Partner, Admin) map to the four starter roles (Super Admin, Manager, Staff, Guest) and the `participant_profiles`/`judge_profiles`/`mentor_profiles` link tables. Codex's §8 "Access boundaries" describes *what* each role sees, but **not how to authorize** it (no role-to-actor mapping model). Codex's §12 security table is prescriptive but unsupported by any architectural mapping. **Codex missed the authorization model gap.**

5. **AI provider adapter**: DATABASE-SCHEMA `ai_interactions.provider` is a string, not a FK. Codex §7 p. 345 says "Provider abstraction: required." Codex **correctly identifies the need** but **misses that the schema provides no adapter/abstraction design** — just columns. The roadmap Phase 7 has no "build AI provider adapter" task. **Codex flagged the requirement but not the schema gap.**

6. **Phase granularity / linear bottleneck**: Codex §11 correctly critiques broad phases but does **not quantify** the risk: 23 phases × 2% bug rate = ~39% cumulative project risk. No Monte Carlo or risk-accumulation analysis. **Minor — Codex is qualitative, which is appropriate for a docs benchmark.**

### Codex's own inaccuracies (Codex claims that are wrong or unsupported by evidence)

1. **Codex §7 "Judging Architecture" §3 (p. 280): "one assignment row... `application_id` may be null for stage-level assignment"** — This is Codex's *recommendation* presented as if it's what the schema permits. The schema (DATABASE-SCHEMA §11.2) actually allows `program_id`, `stage_id`, and `application_id` to *all* be present on one row. Codex is **recommending a model, not describing the schema**. This overstates schema clarity.

2. **Codex §4 p. 146 (Rubrics): "Missing: explicit association from evaluations to rubric version"** — As shown above, `judge_evaluations.rubric_id` exists (DATABASE-SCHEMA §17). Codex is **factually incorrect** here. The gap Codex should have flagged is **rubric immutability enforcement**, not the association.

3. **Codex §9 p. 464: "Applications: missing explicit application status history table; status is too likely to be overwritten without lineage."** — This is **correct and the most important gap** Codex identifies. It is not wrong. (Listed as a miss above because I am confirming: Codex got this right.)

4. **Codex §4 p. 140 / §9 p. 473: Claims about `application_media` needing "document purpose, privacy classification"** — DATABASE-SCHEMA §10.3 defines `application_media` with `purpose` but **not** `privacy_classification`. Codex is correct. (Confirming a Codex strength.)

5. **Codex §7 p. 312: "independent evaluation... judge sees only own scores"** — Stated as a requirement. Confirmed in §7 of requirements (implicitly). Codex's strength. (Confirmed.)

### Summary of Codex accuracy

- **Strengths**: Correctly identified 80%+ of real schema gaps; correctly advocated for reuse; correctly scoped out CMS/LMS restoration risks; correctly prioritized conflict/rubric/score immutability as critical gates.
- **Weaknesses**:
  - Missed the **public-routing architectural mismatch** entirely.
  - Misdescribed the `judge_evaluations.rubric_id` gap (it exists; the gap is enforcement).
  - Did not articulate the **authorization model gap** (no mapping of 6 actor types → 4 starter roles → profile tables).
  - Inflated §4 "Workflow Design: 6/10" but never substantiates the rubric version or score-finalization fields — these were *addressed* in recommendations but are still **open decisions** in the schema (§431, §21).
  - Codex's §15 "OVERALL ENGINEERING READINESS: 7/10" **overrates readiness.** Codex's own §9 p. 683 lists 6 "CRITICAL" decisions required before coding. If those are unresolved, readiness cannot be 7/10.

  I judge Codex's assessment to be **optimistic by ~2 points** on the readiness metric, primarily because Codex's §13 recommendations are aspirational ("I would tighten...") rather than assessing the **current document's actual readiness.** Given that the documents self-label as "Draft v0.1," Codex's 7/10 underweights the number of explicitly unresolved decisions.

---

## 9. Implementation Readiness

**(3) Significant clarification required.**

### Why not (4) Not ready?

The foundation is structurally robust: the starter is verified at 127 passing tests; the three planning documents follow a disciplined read-before-code posture; explicit approval gates (PROJECT-REQUIREMENTS §20, DATABASE-SCHEMA §1200, PROJECT-ROADMAP §36) exist; and the reuse-before-rebuild contract is clear. The project is **genuinely ready to begin Phase 0** (repository setup + validation).

### Why not (2) Mostly ready / (1) Ready?

The following unresolved, blocking-scale issues prevent safe broad implementation:

1. **22 open decisions listed in §17** of requirements, with no prioritized resolution plan or decision-owners identified. Several are Phase-0-blockers (name, question engine, submission versioning, assignment model, conflict definition).
2. **Schema is conceptual, not migration-ready**: DATABASE-SCHEMA explicitly states "NO MIGRATIONS SHOULD BE CREATED YET" (§1.3 title) and §23 requires "migration design must document: primary key, columns and types, nullability, defaults, foreign keys, indexes, unique constraints, relationships, delete behavior, audit implications, status/state behavior" — **none of which are documented** at column level. The schema is an entity inventory, not a data-definition specification.
3. **Critical constraints missing**: evaluation-score uniqueness, judge-assignment uniqueness, conflict-blocking enforcement, rubric-immutability. These are trust-critical per Codex and the requirements but unenforced.
4. **Public-routing gap**: Phase 1 requires anonymous access to program listings, contradicting the starter's authenticated-only design and its "no CMS/landing page" removal.
5. **Linear 23-phase roadmap** with no parallelizable slices or explicit rollback paths.

**Recommendation**: Begin Phase 0. Do not start Phase 1 (program CRUD) until the top 6 decisions (name, question model, submission versioning, assignment model, conflict definition, rubric freeze) are resolved and the schema is migrated to a data-definition specification with the critical constraints above.

---

## 10. Top 10 Risks

| # | Risk | Severity | Evidence | Why it matters | Recommended next action |
|---|---|---|---|---|---|
| 1 | **No conflict-of-interest blocking enforcement in schema** | Critical | DATABASE-SCHEMA §15.3: `conflict_declarations` table; no FK/constraint linking to `judge_assignments` (§11.2). Codex §4 p. 146 / §9 p. 302. | Judges could evaluate conflicted applications; audit trails would show the conflict but enforcement would be purely in application code. | Add a constraint or a domain-service check that prevents creating/updating a `judge_assignment` when an active conflict exists on the same (program/stage/application/team/organization). |
| 2 | **Score finalization immutability not modeled** | Critical | Requirements §6.13 "finalize/lock evaluation"; DATABASE-SCHEMA §17 (`judge_evaluations`): no `finalized_at`/`finalized_by` shown; §11 (`evaluation_scores`): no append-only history. Codex §4 p. 164, §9 p. 302. | Finalized scores could be silently edited, violating evaluation integrity and audit requirements. | Define score immutability (append-only `evaluation_score_audit` table) and finalization fields before Phase 6. |
| 3 | **Rubric versioning immutability unresolved** | Critical | DATABASE-SCHEMA §14.1 (`program_rubrics`): `version` field only; §431: "open decision: Rubric versioning." Requirements §6.10: "must be treated as versioned contract." Codex §4 p. 145, §7.5. | Editing a rubric mid-judging changes scoring semantics retroactively — unfair comparisons and audit gaps. | Resolve rubric freeze policy in Phase 0; model `rubric_versions` as immutable once active. |
| 4 | **Judge-assignment duplication allowed** | High | DATABASE-SCHEMA §11.2 (`judge_assignments`): no uniqueness constraint. §25 lists "active team membership per team/user" as likely unique — but judge assignment is omitted. Codex §4 p. 154. | A judge could be assigned twice, see duplicate work, or have conflicting assignments. | Add UNIQUE(program_id, stage_id, application_id, judge_user_id, assignment_status) or explicit precedence logic. |
| 5 | **Public routing not architected** | High | PROJECT-ROADMAP Phase 1: "public program listing/detail"; AI-PROJECT-STARTER §11 (Removed: public website); MASTER-STARTER §28/A.1 (public website removed); MASTER-STARTER §20 p. 807 (`GET / → named home`). | Building public pages risks recreating the removed CMS/public-website layer or conflicting with the authenticated-only shell. | Define a public-route strategy in Phase 0: unauthenticated routes for published programs, separate from the authenticated shell. |
| 6 | **Application status history not modeled** | High | DATABASE-SCHEMA §9 (`applications`): free-form `status` field only; no `application_status_history` table. Codex §4 p. 141, §9 p. 271. | Application lineage is lost; audit/reconciliation becomes impossible; applicants could claim state transitions never occurred. | Add `application_status_history` table (application_id, from_status, to_status, actor_id, reason, timestamp). |
| 7 | **Evaluation/rubric linkage incomplete** | Medium | DATABASE-SCHEMA §17 shows `rubric_id` but Codex §4 p. 146 incorrectly claims it's missing. The real gap is: no explicit link from `evaluation_scores` to `evaluation_criteria` enforced at schema level. | Scores could reference criteria outside the evaluation's rubric. | Add FK `evaluation_scores.criterion_id → evaluation_criteria.id` and ensure `evaluation_criteria.rubric_id` is transitively enforced. |
| 8 | **22 open decisions with no prioritized resolution plan** | High | PROJECT-REQUIREMENTS §17 lists 22 decisions; §36 Approval Gate says "resolve critical open decisions" (undefined). DATABASE-SCHEMA §36 lists 20 open questions. No owners/timelines. | Starting implementation without these risks rework on every major entity. | Rank the 22 decisions by phase dependency; assign owners and gates in Phase 0. |
| 9 | **AI provider abstraction missing design** | Medium | DATABASE-SCHEMA §12 (`ai_interactions`): `provider`/`model` strings, no adapter abstraction. PROJECT-ROADMAP Phase 7: "AI Judge Copilot v1" with 6 capabilities. No adapter/abstraction design. Codex §7 p. 345, §9 p. 322. | Hard-coding one provider creates lock-in that Codex correctly flags; no adapter means refactoring AI into real domain code is risky. | Define provider-adapter interface + prompt-versioning design before Phase 7. |
| 10 | **Linear 23-phase roadmap bottlenecks project** | Medium | PROJECT-ROADMAP §31: strict Phase 0↓1↓...↓23 chain. No parallelizable slices. No rollback criteria. Codex §11 p. 607–608. | A blocker at any phase halts the entire 23-phase pipeline; 2% bug rate × 23 = ~39% cumulative risk. | Decompose into vertical slices (e.g., "program + public listing"); define rollback/abort criteria per phase. |

---

## 11. Recommended Pre-Implementation Actions

1. **Resolve the Phase-0-blocker decisions** (top ~6 from §17 of requirements): final product name, application-question architecture (configurable engine vs. fixed fields), submission/versioning model, judge-assignment granularity/precedence, conflict-of-interest definition (direct vs. extended party), rubric versioning/policy. Document each as an ADR.
2. **Elevate the schema to a data-definition specification**: For each of the 37 proposed tables, document exact column types, nullability, defaults, FK targets, indexes, unique constraints, and delete behavior. The current document is a conceptual inventory, not migration-ready.
3. **Add the 3 missing constraint types** (Codex's core critique): evaluation-scores uniqueness, judge-assignment uniqueness, and a `conflict_blocks_assignment` enforcement rule (FK or domain check).
4. **Decide and document the public-route strategy**: whether Phase 1 requires unauthenticated access, separate middleware, or a dedicated landing controller — explicitly accounting for the starter's authenticated-only architecture (removed public website in AI-PROJECT-STARTER §11 / MASTER-STARTER §28).
5. **Define the role-to-actor mapping**: explicitly document how Staff/Judge/Applicant/Mentor/Partner roles map onto the starter's 4 inherited roles (Super Admin/Manager/Staff/Guest) and the `participant_profiles`/`judge_profiles`/`mentor_profiles` link tables.
6. **Constrain the AI scope to v1**: The requirements §15 success criteria #6 ("AI-assisted judging") and §15.9 ("AI interaction logging") contradict §18's boundary (AI starts at Phase 7). Resolve: either AI interaction logging is v1 (then schema + logging service must be built in Phase 0/1), or it is deferred.
7. **Add status-history tables** for audit-critical state (application status, evaluation finalization, selection decisions) before Phase 6.
8. **Validate the 23-phase plan against parallelizable vertical slices** to avoid linear bottlenecks (Codex §11).
9. **Define test-coverage expectations** per phase: the requirements §4.3 says "automated tests for major lifecycle behavior" but the roadmap's per-phase "Tests" subsections are underspecified for trust-critical paths (conflict blocking, score immutability, judge isolation).
10. **Defer non-v1 scope explicitly**: Mentorship (Phase 11), Milestones (Phase 12), Resources (Phase 13), Events (Phase 14), Partners (Phase 15), Reporting (Phase 16), Post-Program (Phase 17), and AI Assistants (Phases 18–20) should be **documented as out-of-v1** rather than sequenced.

---

## 12. Final Verdict

1. **Is the project specification internally coherent?**  
   **Partially.** The *vision, lifecycle, actor model, and reuse contract* are coherent and well-written. But the documents contain documented internal tensions: AI in v1 (§15) vs. deferred (§18); configurable questions ("use a mechanism" vs. "open decision" vs. "Phase 3 builds it"); 22 "human decisions required" co-located with a declared "working product name." The core is sound; the edges are loose.

2. **Is the database design sufficiently aligned with the requirements?**  
   **No — conceptually aligned but operationally incomplete.** The schema captures all entities/relationships for the stated scope, but it does **not** model the *verbs* of the workflow: state transitions, immutability locks, uniqueness enforcement, conflict blocking, or status history. It is a v0.1 conceptual blueprint, explicitly marked "not migration-ready" (DATABASE-SCHEMA §1.3). This is the largest gap.

3. **Is the roadmap safe to begin implementation?**  
   **Only Phase 0 is safe.** The linear 23-phase dependency chain is logical, but the entry Phase 0 must resolve ≥6 blocking decisions and elevate the schema to a real specification before Phase 1. Starting Phase 1 without conflict/rubric/assignment rules locked is not safe for a trust-critical judging platform.

4. **What should be fixed before coding starts?**  
   (a) Resolve the 6 Phase-0-blocker decisions; (b) promote the schema to column-level data-definition; (c) add the 3 critical constraints (score uniqueness, assignment uniqueness, conflict blocking); (d) resolve the public-routing strategy; (e) define role-to-actor mapping; (f) resolve the AI-in-v1 contradiction.

5. **What can safely be deferred?**  
   Phases 11–23 (mentorship, milestones, resources, events, partners, reporting, alumni, all AI assistants), and the applicant-facing AI assistant (Phase 18). The Codex MVP "MUST HAVE" (p. 428–430: program, application, screening, judge assignment, conflict, rubric scoring, evaluation finalization, decision, audit, AI logging) is a cleaner v1 target than the 23-phase plan.

6. **What is the single most important issue?**  
   **The workflow's trust-critical invariants are not modeled in the schema or resolved in the open decisions.** The requirements (rightly) emphasize that judges must be blocked by conflicts, scores must be immutable when finalized, rubrics must freeze mid-judging, and decisions must be append-only — but the schema treats these as "open decisions" or omits them entirely, and 22 un-prioritized decisions block progress. A judging platform that cannot enforce these at the data layer will be rebuilt once the first audit fails.

7. **What would I do as the next step if I were the technical lead?**  
   Run a focused Phase-0 workshop: take the 6 highest-impact open decisions (name, question engine, submission versioning, assignment model, conflict definition, rubric freeze), resolve each with a 2-hour decision document, and **produce a 3-table spike** (`programs`, `program_stages`, `applications`) with full column definitions, FKs, constraints, and Pest tests — NOT a 37-table migration. The spike's schema should include at least one status-history table and a conflict-checking domain service. Validate this against both SQLite and PostgreSQL before approving Phase 1. The discipline — not the breadth — is what will de-risk the remaining 21 phases.

---

## 13. Score

| Category | Score | Rationale |
|---|---|---|
| Project understanding | 10 / 10 | The project's purpose, scope, actors, and lifecycle are clearly and consistently explained; the "platform, not contest" distinction is well-articulated. |
| Requirements analysis | 12 / 15 | Strong vision, principles, lifecycle, and decision checklist. Penalties for internal contradictions (AI in v1 vs. deferred; question engine undecided while roadmap commits), ambiguous actor-to-role mapping, and missing non-functional targets (performance, accessibility, i18n). |
| Architecture analysis | 11 / 15 | The Master Starter fits the product exceptionally well; the reuse boundary is clean. Penalties for the public-routing mismatch (never addressed), the AI provider-abstraction gap, and missing explicit architecture decisions on conflict-engine and evidence-privacy classification. |
| Database analysis | 10 / 15 | Conceptual schema is comprehensive and the Mermaid ERD is coherent. Major penalties for: schema is conceptual (not migration-ready — no column types/nullability/defaults documented); 3 trust-critical constraints missing (score uniqueness, judge-assignment uniqueness, conflict blocking); no status-history tables; open decisions on rubric immutability and deletion rules. |
| Roadmap analysis | 7 / 10 | Logical phase ordering and clear MVP boundary. Penalties for: 23-phase linear chain (no parallelization), Phase 7 (6 AI capabilities in one phase) overly broad, Phase 18/19/20 (AI assistants) sequenced too late and contradicting v1 success criteria, missing explicit public-route and conflict-engine gates. |
| Cross-document consistency | 10 / 15 | Strong core alignment (lifecycle ↔ schema ↔ roadmap mostly 1:1). Penalties for: unresolved contradictions (product name, AI-in-v1, question-engine model), missing schema-to-actor-role mapping, terminology ambiguity around "application"/"stage" FKs, and duplicated conflict-of-interest treatment across three documents with no reconciliation. |
| Identification of implementation risks | 9 / 10 | Top 10 risks are well-evidenced and prioritized. Penalty for not explicitly quantifying the linear-roadmap combinatorial risk (23 phases × 2% defect ≈ 39% cumulative). |
| Evidence quality / avoidance of unsupported claims | 5 / 5 | Every claim is anchored to a specific section number and document. Codex report was independently verified/challenged against source sections. |
| Final technical judgment | 3 / 5 | Readiness "significant clarification required" is correct; rationale is evidence-based. Penalty for not quantifying the remediation scope needed to move from v0.1 documents to v1-ready (≈ the gap between 37-entity conceptual schema and constraint-enforced migration-ready spec). |

**Total: 67 / 100**

---

## Benchmark Completion

- ✅ **Inspected all eight documents**: AI-PROJECT-STARTER.md, MASTER-STARTER-ARCHITECTURE.md, PROJECT-REQUIREMENTS.md, DATABASE-SCHEMA.md, PROJECT-ROADMAP.md, README.md, CODEx-BENCHMARK-REPORT.md, and AGENTS.md.
- ✅ **Found contradictions**: Yes — product name (declared vs. "decision required"), AI in v1 (success criteria vs. deferred boundary), configurable questions (prescribed vs. "open decision" vs. committed-in-roadmap), actor-to-role mapping (absent), public-routing (required by Phase 1 vs. removed by starter design).
- ✅ **Found critical risks**: Yes — conflict-of-interest blocking not enforced in schema; score finalization immutability unmodeled; rubric versioning immutability unresolved; judge-assignment duplication permitted. These are trust-critical for a judging platform.
- ⚠️ **Recommend proceeding to implementation**: **No — not broadly.** Phase 0 (repository setup + decision resolution + 3-table schema spike with tests) is the only safe starting slice. Broad implementation of the 23-phase, 37-table plan is premature until the 6 Phase-0-blocker decisions are resolved and the schema is elevated to a migration-ready, constraint-enforced specification.

*Report generated for Laguna S 2.1 benchmark — analysis only, no code or files modified.*
