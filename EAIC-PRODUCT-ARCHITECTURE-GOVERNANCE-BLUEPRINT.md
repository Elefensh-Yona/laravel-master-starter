Ethiopian AI Center (EAIC)
Product Architecture, Governance & Evaluation Blueprint
Approved foundation before implementation
EAIC Blueprint • Page 1
Executive overview
• EAIC is designed as a multi-program innovation platform, not a simple application-ranking CRUD system.
• The platform supports a controlled lifecycle from program publication and eligibility through application, screening, independent judging, deliberation, human decision,
and post-decision transition.
• Authorization is deliberately layered: membership, role/capability, stage, permission, assignment/ownership, and record-level policy.
• Consequential decisions remain human-controlled; AI is advisory only.
Current project state
• Project identity is now Ethiopian AI Center (EAIC).
• Laravel is configured for PostgreSQL database `development`; the Master Starter baseline is already present.
• At the rename handoff, no EAIC domain tables existed and no domain implementation had started.
• Handoff 004 reports 127 tests passed, 2 skipped, 768 assertions; TypeScript, formatting and production build also passed.
• Historical handoffs 001–003 were intentionally preserved.
• Implementation should follow: requirements fi architecture fi database/domain specification fi workflow/state machine fi RBAC/actor mapping fi AI governance fi
roadmap/approval gates fi implementation.
EAIC Blueprint • Page 2
Authorization architecture
• User
• Program Membership
• EAIC Role / Capability
• Stage Scope (when applicable)
• Domain / Action Permission
• Assignment or Ownership
• Record-level Policy
• ALLOW / DENY
End-to-end workflow
• Program published / opened
• Eligibility checked
• Application created and submitted
• Automated validation
• Human Staff screening
• Judge assignment + conflict controls
• Independent Judge evaluations
• Evaluation finalization
• Controlled disclosure
• Structured deliberation
• Decision Maker records final human decision
• Outcome + controlled transition
• Applicant notification / feedback
EAIC Blueprint • Page 3
• Audit trail preserved
Evaluation model
• Rubric version is frozen before evaluations depend on it.
• Each Judge independently scores weighted criteria.
• The system calculates weighted totals; Judges do not manually override the math.
• Each criterion includes a human justification/evidence note.
• Qualitative human assessment is recorded separately from the numerical score.
• Judge recommendation is separate from the mathematical score.
• Individual evaluations remain private until the approved disclosure point.
• Mean, median, range/spread and disagreement are surfaced for deliberation.
• The final Decision Maker outcome is not mechanically derived from the score.
EAIC Blueprint • Page 4
Trust & governance
• Super Admin power does not equal unlimited authority to rewrite governed business history.
• Blocking conflicts become authorization restrictions, not warnings.
• Frozen rubrics cannot be silently changed.
• Finalized evaluations cannot be silently edited.
• Final decisions are formal human records.
• Governance overrides require explicit reason, actor, timestamp, action and audit history.
• Historical handoffs remain historical records and are not rewritten.
• AI must remain advisory at consequential decision points.
EAIC Blueprint • Page 5
AI boundary
• AI may organize evidence, summarize patterns, identify disagreements and assist with workflow.
• AI must not autonomously determine final eligibility, shortlist decisions, final Judge scores, conflict resolution, final selection, resource allocation, or final
incubation/mentorship outcomes.
• Future AI governance should track provenance, source references, model/provider, prompt/version, review state, audit history, permissions, privacy, retention and
prompt-injection/data-leakage protections.
First MVP vertical slice
• Program
• Application (Individual / Team / Organization)
• Program-controlled eligibility
• Controlled application revisions
• Automated validation + Staff screening
• Program-scoped Judge assignment
• Conflict-of-interest controls
• Frozen/versioned rubric
• Independent weighted evaluation
• Qualitative human assessment
• Evaluation finalization/reopening controls
• Controlled disclosure + structured deliberation
• Human Decision Maker + rationale
• Outcome/transition
• Applicant notification
• Audit/governance events
• Focused acceptance tests + PostgreSQL verification
EAIC Blueprint • Page 6
Implementation gate
• Do not jump directly into the full migration set.
• First consolidate the approved decisions into one authoritative EAIC Product + Architecture + Governance Contract.
• Then produce the migration-ready MVP schema and acceptance-test specification.
• Implement the smallest complete vertical slice end-to-end.
• Every Codex interaction must create an incremental AI-AGENT-HANDOFFS/-descriptive-summary.md artifact.
• Test-loop rule: diagnose fi one meaningful corrective attempt fi rerun once fi record fi continue to the next safe task. Never enter an endless retry loop.
• After each interaction, the Product & Technical Controller reviews the handoff and determines the next prompt.
EAIC Blueprint • Page 7
Important uncertainties
• The inherited repository/package identity remains `elefensh-yona/laravel-master-starter`; it was intentionally not renamed.
• Future EAIC PHP namespace/module structure must be selected during implementation rather than assumed.
• No manual browser acceptance test was performed during the rename interaction.
• PostgreSQL-specific behavior beyond the starter baseline must be covered as EAIC workflows are implemented.
• Because `development` already contains the Master Starter baseline, future migrations must account for existing applied migrations.
EAIC Blueprint • Page 8
EAIC Blueprint • Page 9
Approved decisions 1–35
# Area Approved direction
1 Decision Maker Separate authority from Program Staff.
2 Multiple roles A user may hold multiple EAIC roles.
3 Multiple programs A user may participate in multiple programs.
4 Program scope Membership + role + permission + policy.
5 Stage scope Hybrid stage restrictions where applicable.
6 Judge scope Membership + Judge role + assignment + policy.
7 Applicant scope Primary owner + application members + policy.
8 Starter roles Keep Master Starter roles as infrastructure; add EAIC domain authorization.
9 Authorization Full layered authorization model.
10 Super Admin Broad system administration, but protected trust-critical boundaries.
11 Governance override Formal, explicit, reasoned and auditable override path.
12 Permissions Domain/action permissions + policy enforcement.
13 Program visibility Hybrid publication/lifecycle model.
14 Eligibility Program-controlled eligibility.
15 Applicant types Individual + Team + Organization.
16 Application revisions Controlled revision lifecycle with history.
17 Screening Automated validation + human Staff screening.
18 Judge visibility Independent judging first; controlled disclosure later.
19 Conflict of interest Detection + declaration + controlled determination.
20 Rubrics Controlled lifecycle + freeze + versioning.
21 Evaluation finalization Locked finalization + controlled reopening + history.
22 Deliberation Structured human deliberation.
23 Final decision Evidence-informed human Decision Maker with rationale.
24 Post-decision Outcome + controlled transition.
25 Transparency Tiered transparency + applicant-facing feedback.
26 Notifications Event-driven in-app authority + email delivery.
27 Deadlines Program-configurable, timezone-aware, strict closing with governed exceptions.
28 Application versions Immutable submitted versions; judging references exact version.
29 Judge reassignment Controlled, conflict-aware reassignment with history.
#
30
31
32
33
Area
Evaluation mathematics
Deliberation rules
Post-decision lifecycle
Audit/governance
Approved direction
Weighted deterministic scoring + mean/median/spread + disagreement visibility.
Structured evidence-based deliberation; Decision Maker final authority.
Controlled movement into next stage/program.
Comprehensive append-only audit trail for consequential actions.
34
35
AI boundaries
MVP scope
AI advisory only for consequential decisions; human accountability.
Smallest complete vertical slice with end-to-end acceptance tests.
EAIC Blueprint • Page 10
Source basis
• AI-Innovation-Lifecycle-Hub-New-Tab-Handover.md — project vision, trust/governance principles, documentation flow and AI boundaries.
• AI Agent Handoff 004: Project Name Rename Summary — current EAIC identity and verified starter baseline.
• CODEX-PHASE-0-IMPLEMENTATION-PLAN.md — phase sequencing, acceptance gates and MVP implementation plan.
• Product & Technical Controller decisions 1–35 — approved architecture and governance decisions recorded in this project handover.
EAIC Blueprint • Page 11
