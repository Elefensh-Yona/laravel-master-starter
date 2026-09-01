# EAIC MVP RBAC + Scope Matrix

**Project:** Ethiopian AI Center (EAIC)  
**Status:** Implementation-ready authorization specification, subject to the owner approvals listed in Section 10.  
**Authority:** `EAIC-PRODUCT-ARCHITECTURE-GOVERNANCE-BLUEPRINT.md` and the reconciled EAIC contract govern EAIC behavior. The Laravel Master Starter governs inherited infrastructure.

## 1. Authorization Foundation

EAIC uses the approved layered model:

```text
User
  ↓
Program Membership
  ↓
EAIC Role / Capability
  ↓
Stage Scope (when applicable)
  ↓
Domain / Action Permission
  ↓
Assignment / Ownership
  ↓
Record-level Policy
  ↓
ALLOW / DENY
```

The layers are cumulative. A broad permission never grants access to an out-of-scope record, and a role never replaces a record-level policy check. Existing starter `web`-guard roles, permissions, middleware, policies, and Super Admin administration remain intact; EAIC adds domain authorization through the same architecture.

### Scope vocabulary

| Scope | Meaning |
|---|---|
| Global | Applies across the installation; reserved for approved system administration and cross-program operations |
| Program | Applies only to a program in which the user has the required membership and capability |
| Stage | Applies only to an allowed lifecycle stage, where the capability has stage restrictions |
| Assignment | Applies only to applications/evaluations explicitly assigned to the user |
| Ownership | Applies only to an account's or application's owned/member records |
| Record policy | Object-level decision using confidentiality, state, conflict, history, and governance rules |

## 2. MVP Actor Model

### 2.1 Super Admin

- **Purpose:** system administration, recovery, and approved cross-system operations.
- **Authority:** broad system administration, but not unlimited authority to rewrite governed business history.
- **Program scope:** global for system administration; EAIC business records remain subject to protected-history and governance rules.
- **Stage scope:** global administrative visibility does not erase stage or governance restrictions on consequential actions.
- **Record scope:** broad access where permitted by policy; all exceptional access is auditable.
- **Assignment restrictions:** no assignment is implied by the Super Admin role for Judge evaluation access.
- **May do:** manage approved system configuration and users; perform approved operational recovery; use formal governance override paths.
- **Must not do:** silently edit submitted versions, frozen rubrics, finalized evaluations, deliberation history, or final decisions; bypass audit, confidentiality, conflict, or human authority.

### 2.2 Program Staff

- **Purpose:** operate assigned programs and their lifecycle.
- **Authority:** configure/publish programs, manage program stages and eligibility, perform human Staff screening, assign/reassign Judges, manage approved conflict determinations, and support structured deliberation.
- **Program scope:** only programs where the user has the required Program Staff membership and capability.
- **Stage scope:** only stages granted by membership/capability; stage restrictions apply when configured.
- **Record scope:** applications, submissions, screening records, assignments, conflicts, evaluations, deliberation material, and outcomes inside authorized programs.
- **Assignment restrictions:** Staff does not gain Judge access merely by operating a program; a Staff member must also hold the approved Judge capability and assignment if they are to evaluate.
- **May do:** perform approved program actions and human screening; manage judge assignment and conflict workflow; open/close approved workflow stages; support deliberation.
- **Must not do:** access unrelated programs; silently modify immutable versions, frozen rubric meaning, finalized evaluations, or formal decisions; use AI as a substitute for human authority.

### 2.3 Decision Maker

- **Purpose:** make and finalize the formal human outcome after structured deliberation.
- **Authority:** separate from Program Staff; records an evidence-informed decision with rationale.
- **Program scope:** only programs where the user has the approved Decision Maker membership/capability.
- **Stage scope:** decision/deliberation scope only, as approved for that program.
- **Record scope:** applications and deliberation material in the authorized program(s); access is policy-controlled.
- **Assignment restrictions:** a decision-specific assignment or membership is required if the approved program design requires it; no decision authority is inferred from Staff or Judge status.
- **May do:** review approved evidence and disclosed evaluations; participate in structured deliberation; record and finalize the human outcome and rationale.
- **Must not do:** decide outside authorized programs/applications; mechanically derive the final outcome from scores; delegate the decision to AI; silently rewrite evaluations or history.

### 2.4 Judge / Evaluator

- **Purpose:** independently evaluate assigned applications and provide evidence-based recommendations.
- **Authority:** review assigned records, declare conflicts, score weighted criteria, record qualitative assessment and justification/evidence, submit/finalize an evaluation, and participate in approved deliberation.
- **Program scope:** requires program membership plus Judge role/capability.
- **Stage scope:** evaluation and approved deliberation stages only.
- **Record scope:** assigned application and exact submitted version; assigned evidence; own evaluation; only the material disclosed at the approved deliberation point.
- **Assignment restrictions:** assignment is required; a broad Judge permission does not grant access to unassigned applications.
- **May do:** declare a conflict; evaluate independently against the frozen rubric; submit a recommendation; access permitted aggregate/disagreement information after controlled disclosure.
- **Must not do:** view unassigned applications; view another Judge's private evaluation before disclosure; manually override the system-calculated total; evaluate while a blocking conflict applies; change the rubric or finalized evaluation.

### 2.5 Applicant / Innovator

- **Purpose:** apply to programs and manage owned participation records.
- **Authority:** create and maintain permitted applications and evidence; submit controlled revisions; receive approved status, feedback, outcome, and notifications.
- **Program scope:** public/permitted program visibility plus programs in which the user has an owned application or approved membership.
- **Stage scope:** applicant-facing actions only for the current permitted stage.
- **Record scope:** own account, primary-owned application, and application-member records permitted by policy.
- **Assignment restrictions:** applicant ownership/member status does not grant Staff, Judge, or Decision Maker access.
- **May do:** discover permitted programs; create/edit drafts; submit approved versions before strict deadline closure; perform permitted revision actions; view approved feedback/outcome communication.
- **Must not do:** view another applicant's application/evidence; view private Judge evaluations, conflicts, internal deliberation, or governance audit data; alter submitted immutable versions outside approved revision flow.

## 3. Proposed MVP Permission Catalog

The authoritative decision record approves the singular `resource.action` naming convention (D-014), but does not approve the complete literal catalog. The following is the **PROPOSED** consistent catalog for owner approval. These names must not be seeded until approved.

| Permission name | Domain | Action | Description | Intended actors | Scope requirement | Trust-critical |
|---|---|---|---|---|---|---|
| `program.view` | Program | View | View a program in an allowed visibility state | Super Admin, Program Staff, Applicant, Decision Maker, Judge | Global for approved admin; program membership or public visibility otherwise | No |
| `program.create` | Program | Create | Create a program draft | Super Admin, Program Staff | Global or program administration capability | No |
| `program.update` | Program | Update | Update an unpublished/configurable program | Super Admin, Program Staff | Program membership + stage/state policy | Yes |
| `program.publish` | Program | Publish/open | Publish/open a configured program | Super Admin, Program Staff | Program membership + publish capability + transition policy | Yes |
| `eligibility.view` | Eligibility | View | View program-controlled eligibility rules/results permitted to actor | Super Admin, Program Staff, Applicant, Judge, Decision Maker | Program/record policy and transparency tier | Yes |
| `eligibility.validate` | Eligibility | Validate | Run objective automated validation | Super Admin, Program Staff, system process | Program scope + rule/state policy; no final human decision | Yes |
| `eligibility.screen` | Eligibility | Screen | Record human Staff screening outcome | Super Admin, Program Staff | Program/stage scope + record policy | Yes |
| `application.view` | Application | View | View an application within approved visibility | Super Admin, Program Staff, Judge, Decision Maker, Applicant | Program membership, assignment, ownership, or explicit public policy | Yes |
| `application.create` | Application | Create | Create an owned application draft | Super Admin, Applicant | Applicant ownership/member policy and program window | Yes |
| `application.update` | Application | Update | Edit an eligible draft or permitted revision | Super Admin, Program Staff where operationally approved, Applicant | Ownership/membership + state/deadline policy | Yes |
| `application.submit` | Application | Submit | Submit a controlled immutable application version | Super Admin only through approved recovery, Applicant | Ownership/member authority + program window + strict timezone-aware deadline policy | Yes |
| `application.revise` | Application | Revise | Create a permitted controlled revision with history | Super Admin through governance path, Applicant | Ownership/member authority + approved revision state/policy | Yes |
| `assignment.view` | Judge assignment | View | View assignment records allowed to actor | Super Admin, Program Staff, Judge, Decision Maker | Program/stage/assignment policy and confidentiality tier | Yes |
| `assignment.create` | Judge assignment | Assign | Assign a Judge to an application/stage/program scope | Super Admin, Program Staff | Program membership + assignment capability + conflict precheck | Yes |
| `assignment.reassign` | Judge assignment | Reassign | Reassign a Judge through controlled conflict-aware flow | Super Admin, Program Staff | Program membership + governance/state policy + history | Yes |
| `conflict.declare` | Conflict | Declare | Declare or update the actor's conflict disclosure | Judge | Judge identity + assigned/eligible record + conflict workflow state | Yes |
| `conflict.view` | Conflict | View | View conflict information allowed by tier | Super Admin, Program Staff, affected Judge, Decision Maker as approved | Program/record policy; confidential fields restricted | Yes |
| `conflict.determine` | Conflict | Determine | Record controlled human conflict determination | Super Admin, Program Staff or approved governance capability | Program scope + determination authority + audit policy | Yes |
| `evaluation.view` | Evaluation | View | View an evaluation at the allowed disclosure point | Super Admin, Program Staff, Judge, Decision Maker | Assignment/ownership + disclosure/state/confidentiality policy | Yes |
| `evaluation.create` | Evaluation | Create | Create an evaluation draft for an assigned application/version | Judge | Judge membership + assignment + no blocking conflict + exact version policy | Yes |
| `evaluation.update` | Evaluation | Update | Edit an evaluation draft before finalization | Judge | Own draft + evaluation state + no blocking conflict | Yes |
| `evaluation.submit` | Evaluation | Submit | Submit an evaluation for finalization | Judge | Own draft + exact rubric/version + state policy | Yes |
| `evaluation.finalize` | Evaluation | Finalize | Lock the evaluation and preserve its history | Judge, or approved authorized actor if specified | Own evaluation/approved authority + rubric/version + conflict/state policy | Yes |
| `evaluation.reopen` | Evaluation | Reopen | Reopen a finalized evaluation through governed exception | Super Admin, Program Staff, or approved governance capability | Explicit authority + reason + history + stronger deliberation restrictions | Yes |
| `deliberation.view` | Deliberation | View | View controlled disclosed deliberation material | Super Admin, Program Staff, Decision Maker, Judge | Program/stage membership + disclosure policy | Yes |
| `deliberation.participate` | Deliberation | Participate | Participate in structured human deliberation | Decision Maker, Program Staff, Judge | Authorized participant + program/stage/assignment policy | Yes |
| `deliberation.manage` | Deliberation | Manage | Open, structure, and close deliberation | Program Staff, Decision Maker where approved | Program/stage capability + entry/closure policy | Yes |
| `decision.view` | Decision | View | View an outcome/decision at the permitted tier | Super Admin, Program Staff, Decision Maker, Applicant | Program/record policy and transparency tier | Yes |
| `decision.create` | Decision | Create | Record a formal human decision draft | Decision Maker | Decision authority + authorized application/program + deliberation prerequisites | Yes |
| `decision.finalize` | Decision | Finalize | Finalize the human outcome and rationale | Decision Maker | Decision authority + required rationale + governance/state policy | Yes |
| `decision.reverse` | Decision | Reverse | Change/supersede a finalized outcome through governance | Super Admin or approved governance authority | Explicit reason, authority, preserved prior record, audit/history | Yes |
| `audit.view` | Governance/audit | View | View protected consequential history | Super Admin, approved governance users | Global/program governance policy; never applicant/Judge by default | Yes |

### Catalog conventions

- Singular domain resource names are used consistently: `program`, `application`, `evaluation`, `decision`.
- Permission checks are necessary but never sufficient for record access.
- `evaluation.finalize`, `decision.finalize`, and governance actions are trust-critical and require explicit policy checks and audit events.
- `application.submit` and `application.revise` are distinct from ordinary update because they change historical authority.
- Deferred modules have no permissions in this MVP catalog.

## 4. Role-to-Permission Matrix

This matrix applies the proposed permission names above. **ALLOW** means the actor may be granted the action subject to the scope/policy columns. **CONDITIONAL** means the action is never global by default and requires the stated policy. **DENY** means the actor cannot perform the action in the MVP.

| Permission | Super Admin | Program Staff | Decision Maker | Judge | Applicant |
|---|---|---|---|---|---|
| `program.view` | ALLOW | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL |
| `program.create` | ALLOW | CONDITIONAL | DENY | DENY | DENY |
| `program.update` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `program.publish` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `eligibility.view` | ALLOW | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL |
| `eligibility.validate` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `eligibility.screen` | CONDITIONAL | ALLOW | DENY | DENY | DENY |
| `application.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL |
| `application.create` | CONDITIONAL | DENY | DENY | DENY | CONDITIONAL |
| `application.update` | CONDITIONAL | CONDITIONAL | DENY | DENY | CONDITIONAL |
| `application.submit` | CONDITIONAL | DENY | DENY | DENY | CONDITIONAL |
| `application.revise` | CONDITIONAL | DENY | DENY | DENY | CONDITIONAL |
| `assignment.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY |
| `assignment.create` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `assignment.reassign` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `conflict.declare` | CONDITIONAL | DENY | DENY | CONDITIONAL | DENY |
| `conflict.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY |
| `conflict.determine` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `evaluation.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY |
| `evaluation.create` | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL | DENY |
| `evaluation.update` | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL | DENY |
| `evaluation.submit` | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL | DENY |
| `evaluation.finalize` | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL | DENY |
| `evaluation.reopen` | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY |
| `deliberation.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY |
| `deliberation.participate` | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY |
| `deliberation.manage` | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY | DENY |
| `decision.view` | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL |
| `decision.create` | DENY | DENY | CONDITIONAL | DENY | DENY |
| `decision.finalize` | DENY | DENY | CONDITIONAL | DENY | DENY |
| `decision.reverse` | CONDITIONAL | DENY | DENY | DENY | DENY |
| `audit.view` | CONDITIONAL | DENY | DENY | DENY | DENY |

### Conditional rules

- **Super Admin:** broad administration does not itself authorize silent protected-history changes or automatic decisions. Conditional operations require the approved governance override path with reason, actor, timestamp, action, and audit history.
- **Program Staff:** requires membership in the target program, the relevant capability/permission, applicable stage scope, and record policy. Staff may screen and operate workflow but is not automatically a Decision Maker or Judge.
- **Decision Maker:** requires Decision Maker authority for the target program/application and the approved deliberation prerequisites. Decision actions remain human and reasoned.
- **Judge:** requires target-program membership, Judge capability, explicit assignment, applicable stage, no blocking conflict, exact submitted version, and evaluation/disclosure state policy. A Judge sees own private evaluation before disclosure and approved disclosed material afterward.
- **Applicant:** requires public visibility or ownership/application-member policy, permitted application state, and strict program deadline policy. Owner-only actions cannot be performed by an ordinary application member. An owner may delegate specific permitted actions to an approved member; delegation is revocable, expiring, audited, and never unrestricted ownership.

## 5. Scope Matrix

### Program Staff

- Staff cannot access all programs by role alone.
- Staff can access a program only with the required program membership, capability, permission, stage scope, and record policy.
- Staff can access applications inside an authorized program, subject to application state, confidentiality, and record policy.
- Staff cannot access unrelated programs or their applications through direct URLs, API parameters, or a broad global permission.
- Staff can manage assigned program records; whether a staff member may manage every record inside that program is controlled by the approved membership/capability and policy implementation.

### Judge

A Judge must satisfy all of the following before an application/evaluation action is allowed:

1. Authenticated and approved user account.
2. Membership in the target program.
3. Judge role/capability.
4. Applicable evaluation stage scope.
5. Explicit assignment to the target application or approved assignment scope.
6. Ownership of the evaluation for own-evaluation actions.
7. No blocking conflict; a blocking conflict is an authorization restriction, not a warning.
8. Access to the exact immutable submitted version being judged.
9. Controlled-disclosure state for any evaluation not owned by the Judge.
10. Record-level policy permits the requested field/action.

Independent evaluations remain private before the approved disclosure point. After controlled disclosure, only the approved aggregate statistics, disagreement information, and evaluation fields become visible; original evaluations remain historical records.

### Applicant

- Account access begins with the authenticated user identity and approved applicant capability.
- Application ownership is held by the primary owner; approved application members receive only the member permissions granted by policy.
- Organization and team boundaries are represented through application ownership/membership policy; an application member cannot perform a primary-owner-only action without explicit approved authority.
- Application owners may delegate specific permitted actions to approved members. Delegation can be revoked, may expire, is audited, and never grants unrestricted ownership authority.
- Submitted versions are immutable. Revisions create controlled history and do not mutate the version already referenced by judging.
- Applicant-visible feedback and outcomes follow the tiered transparency policy. Internal screening, conflicts, private evaluations, deliberation, governance history, and confidential evidence are not exposed unless explicitly approved.

### Decision Maker

- Decision Maker authority is separate from Program Staff.
- A Decision Maker can decide only for programs/applications covered by the approved Decision Maker membership/capability and record policy.
- Access to evaluations is controlled disclosure access for deliberation, not unrestricted Judge-private access.
- A Decision Maker must satisfy the approved deliberation entry/prerequisite policy before creating/finalizing a decision.
- Finalization requires a formal human rationale and produces protected audit/history. Scores inform but do not mechanically determine the outcome.

### Super Admin

- Super Admin retains broad system administration.
- The existing global bypass must not be interpreted as unlimited authority to rewrite governed business history.
- Protected operations include silent mutation of immutable submitted versions, frozen rubric meaning, finalized evaluations, original deliberation/evaluation records, and final decisions.
- Any exceptional change requires a formal governance override with explicit authority, reason, actor, timestamp, action, preserved prior state, and audit history.
- Super Admin does not receive Judge assignment implicitly and cannot use administration to expose Judge-confidential material outside the approved disclosure policy.

## 6. Authorization Decision Order

The recommended server-side order is:

1. **Authenticated user and account state:** identify the user and require the account state required by the route/workflow.
2. **EAIC role/capability:** establish the actor responsibility relevant to the action.
3. **Program membership:** establish membership in the target program when the action is program-bound.
4. **Stage scope:** verify that the actor may act in the current lifecycle stage, where applicable.
5. **Domain/action permission:** verify the approved action permission.
6. **Assignment or ownership:** verify Judge assignment, Applicant primary ownership, or approved application membership.
7. **Record-level policy:** evaluate target record state, confidentiality tier, exact version, and actor relationship.
8. **Conflict-of-interest state:** for Judge actions, deny when a blocking conflict exists; for assignment/reassignment, require the approved conflict precheck.
9. **Governance restrictions:** deny silent protected-history changes; require the explicit override path for exceptions.
10. **ALLOW/DENY:** allow only when all required checks pass; return the framework-appropriate web/API denial otherwise.

This order establishes identity and broad capability before querying sensitive records, then applies the narrower relationship and integrity checks before the final result. Conflict and governance are deliberately final gates because they can deny an otherwise permitted action.

## 7. Critical Security and Governance Rules

| Case | Result | Authorization reason |
|---|---|---|
| Judge accesses an unassigned application | **DENY** | Judge scope requires explicit assignment in addition to membership, role, permission, and policy |
| Judge accesses another Judge's private evaluation before controlled disclosure | **DENY** | Independent evaluation confidentiality remains in force until the approved disclosure point |
| Judge has a blocking conflict | **DENY** | Blocking conflict is an authorization restriction and removes evaluation/restricted participation access |
| Applicant accesses another applicant's application | **DENY** | Applicant scope is primary ownership/application membership plus record policy |
| Application member performs an owner-only action | **DENY** | Membership does not equal primary ownership or owner-only authority |
| Program Staff accesses another program outside scope | **DENY** | Program membership and policy limit Staff to authorized programs |
| Decision Maker decides an application outside authority | **DENY** | Decision authority is program/application scoped and requires approved deliberation prerequisites |
| Super Admin changes protected governed history directly | **DENY** | Broad administration does not authorize silent rewrite of protected history |
| A governance override is required | **DENY** to the ordinary action; **ALLOW** only through the explicit override path | Override must include approved authority, reason, actor, timestamp, action, preserved history, and audit event |
| Finalized evaluation is accessed after deliberation begins | **CONDITIONAL** | Authorized governance/staff/Decision Maker access may view approved protected history; Judges/applicants remain limited by disclosure and confidentiality policy; mutation is denied without governed reopen |

## 8. RBAC Acceptance-Test Specification

These are specification cases only. They are not implemented by this task.

### Positive cases

| ID | Test | Expected result |
|---|---|---|
| RBAC-P01 | Authorized Program Staff opens an assigned program | ALLOW; program membership, capability, permission, stage, and policy pass |
| RBAC-P02 | Authorized Program Staff creates/configures a program | ALLOW within approved administrative scope |
| RBAC-P03 | Authorized Program Staff publishes a configured program | ALLOW only through the approved transition and audit event |
| RBAC-P04 | Applicant views a public/permitted program | ALLOW according to hybrid visibility policy |
| RBAC-P05 | Applicant creates an owned draft | ALLOW within program window and ownership policy |
| RBAC-P06 | Applicant submits before the timezone-aware deadline | ALLOW; immutable submitted version and audit history are created |
| RBAC-P07 | Staff runs objective validation | ALLOW; result is not treated as final human eligibility decision |
| RBAC-P08 | Program Staff records human screening | ALLOW within program/stage/record scope; audit required |
| RBAC-P09 | Staff assigns an eligible Judge after conflict precheck | ALLOW; assignment history is recorded |
| RBAC-P10 | Assigned Judge views the exact submitted version | ALLOW; assignment and no-blocking-conflict policy pass |
| RBAC-P11 | Assigned Judge declares a conflict | ALLOW; declaration is protected/audited |
| RBAC-P12 | Cleared assigned Judge creates and updates own evaluation draft | ALLOW against frozen rubric and exact submitted version |
| RBAC-P13 | Judge finalizes own evaluation | ALLOW; calculated total is system-generated and evaluation becomes protected |
| RBAC-P14 | Authorized participant views disclosed aggregate statistics/disagreement | ALLOW only after controlled disclosure |
| RBAC-P15 | Authorized Decision Maker records and finalizes a human decision | ALLOW with evidence, rationale, prerequisites, and audit history |
| RBAC-P16 | Applicant views approved outcome notification/feedback | ALLOW only for applicant-visible fields |
| RBAC-P17 | Authorized governance actor performs an approved override | ALLOW only through explicit reasoned, audited override path |

### Negative/security cases

| ID | Test | Expected result |
|---|---|---|
| RBAC-N01 | Staff without target-program membership views its application | DENY |
| RBAC-N02 | Staff uses a valid permission against an unrelated program | DENY by record policy |
| RBAC-N03 | Judge views an unassigned application | DENY |
| RBAC-N04 | Judge views another Judge's private evaluation before disclosure | DENY |
| RBAC-N05 | Conflicted Judge opens evaluation action | DENY |
| RBAC-N06 | Judge evaluates a superseded/non-authoritative version | DENY |
| RBAC-N07 | Judge manually submits a replacement mathematical total | DENY; total is system-calculated |
| RBAC-N08 | Judge edits the frozen rubric | DENY |
| RBAC-N09 | Applicant views another applicant's application/evidence | DENY |
| RBAC-N10 | Application member performs a primary-owner-only submission/revision action | DENY unless approved owner delegation exists |
| RBAC-N11 | Applicant edits an immutable submitted version directly | DENY |
| RBAC-N12 | Decision Maker decides outside assigned/membership scope | DENY |
| RBAC-N13 | Judge or Applicant views internal deliberation/governance audit data | DENY |
| RBAC-N14 | Super Admin directly mutates finalized evaluation/history | DENY; require override path |
| RBAC-N15 | Ordinary user invokes a governance override without authority/reason | DENY |
| RBAC-N16 | Any actor attempts to finalize a decision without human Decision Maker authority | DENY |
| RBAC-N17 | AI process attempts final eligibility, shortlist, score, conflict, selection, resource, or incubation decision | DENY by architecture; no AI authority permission exists |
| RBAC-N18 | Unauthorized web request targets a protected EAIC record | DENY with approved web response |
| RBAC-N19 | Unauthorized API request targets a protected EAIC record | DENY with approved JSON response |
| RBAC-N20 | Judge accesses another Judge's evaluation through direct URL/API manipulation after no disclosure | DENY |
| RBAC-N21 | Finalized evaluation is reopened without approved authority, reason, and audit | DENY |
| RBAC-N22 | Notification exposes private evaluation, conflict, or internal note to Applicant | DENY delivery/content; notification must not be emitted or must be redacted before delivery |

## 9. Decided Now

The following are decided by the approved Blueprint and are ready to guide implementation:

- Decision Maker is separate from Program Staff.
- Users may hold multiple EAIC roles and participate in multiple programs.
- Program scope combines membership, role/capability, permission, and policy.
- Stage scope uses hybrid restrictions where applicable.
- Judge scope requires membership, Judge role/capability, assignment, and policy.
- Applicant scope uses primary owner, application members, and policy.
- Master Starter roles remain infrastructure; EAIC uses domain/action permissions with policy enforcement.
- Super Admin has broad administration but protected trust-critical boundaries.
- Governance overrides are formal, explicit, reasoned, and auditable.
- Program visibility uses a hybrid publication/lifecycle model.
- Applicant types are Individual, Team, and Organization.
- Eligibility is program-controlled and combines automated validation with human Staff screening.
- Independent judging precedes controlled disclosure.
- Blocking conflicts are authorization restrictions.
- Rubrics are versioned and frozen before evaluations depend on them.
- Evaluation mathematics is weighted and deterministic; Judges cannot manually override the calculated total.
- Finalized evaluations are locked, reopening is controlled, and history is preserved.
- Deliberation is structured, human-led, evidence-informed, and does not rewrite individual evaluations.
- Final decisions are evidence-informed human Decision Maker records with rationale; scores do not automatically decide outcomes.
- Post-decision movement is controlled, separate from the decision, and preserves the application history.
- Transparency is tiered; applicant-facing feedback is distinct from confidential Judge evaluations.
- Notifications are event-driven; in-app is authoritative, email is a delivery channel, and delivery failure does not remove the in-app record.
- Deadlines are program-configurable, timezone-aware, strictly closing, with governed exceptions.
- Submitted versions are immutable and judging references the exact version.
- Judge reassignment is controlled, conflict-aware, and historical.
- Audit/governance history is comprehensive and append-only for consequential actions.
- AI is advisory only at consequential decision points.
- PostgreSQL is primary; timezone-aware timestamps, numeric scoring types, justified JSONB, and relational state are required design directions.
- Incremental implementation, one corrective retry, per-interaction handoffs, and prerequisite approval gates are mandatory process controls.

## 10. OWNER DECISION REQUIRED

The following are deliberately not finalized by this matrix because the Blueprint establishes direction but not literal implementation values:

1. Approve the exact permission strings and role-to-permission/capability matrix; D-014 decides the singular `resource.action` convention, not assignments.
2. Approve the storage mechanism and cardinality for program memberships, lifecycle status, stage restrictions, Judge assignments, application members, owner delegation, and Decision Maker authority.
3. Approve the exact public program fields and whether application initiation requires authentication.
4. Approve team lead, member invitation, organization representation, and owner-only action mechanics; D-020 already decides delegation must be specific, revocable, expiring, audited, and non-ownership.
5. Approve exact lifecycle state names and transition preconditions; D-047 requires explicit state machines but does not enumerate literal values.
6. Approve exact conflict categories, indirect-affiliation handling, determination authority, waiver policy, and disclosure fields.
7. Approve rubric scoring precision, rounding, and criterion weight values; D-028 decides 0–10 scores, 100% weights, weighted calculation, and normalized 100-point result.
8. Approve exact Judge/applicant/Decision Maker field-level disclosure, including score and Judge identity visibility.
9. Approve notification event catalog, recipient/timing rules, email behavior, and retry/failure semantics; D-035 decides event-driven notification, in-app authority, email delivery, and in-app retention after delivery failure.
10. Approve migration-ready database columns, keys, constraints, indexes, delete behavior, and history representation.
11. Approve exact EAIC PHP namespace/module structure.
12. Approve exact configured transition targets and any additional outcome metadata; D-050 approves `ACCEPTED`, `REJECTED`, `WAITLISTED`, and `REVISION_REQUIRED` outcomes.

## 11. Implementation Boundary

### This task defines

- actor authority boundaries;
- permission naming proposal;
- role/permission decision matrix;
- program, stage, assignment, ownership, and record policy scope;
- authorization order;
- security/governance behavior; and
- authorization-focused acceptance-test specifications.

### This task does not define or implement

- database tables, columns, foreign keys, indexes, migrations, or seeders;
- Laravel models, policies, controllers, routes, services, or workflow code;
- frontend pages or API endpoints;
- exact lifecycle state names or scoring scale;
- notification implementation;
- AI provider implementation;
- new roles or permissions in the database.

## 12. Recommended Dependency

```text
Approved EAIC Contract + Approved RBAC/Scope Matrix
                         ↓
Migration-ready MVP Database Schema
                         ↓
Authorization and Workflow Acceptance Tests
                         ↓
Migrations / Models
                         ↓
Policies / Workflow Services
                         ↓
UI / API
                         ↓
End-to-End Acceptance
```

The next implementation task should begin only after the owner approves the exact permission catalog, role/capability mapping, scope storage model, and remaining field-level authorization decisions.
