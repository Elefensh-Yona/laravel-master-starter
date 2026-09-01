# Manual Test 08: Local QA RBAC Fixture

**Status:** NOT RUN  
**Scope:** Future local browser QA after governed fixture restoration. No production credentials or UI exposure is permitted.

## Preconditions

- Local PostgreSQL fixture restoration has completed.
- Laravel application and browser environment are running locally.
- All five documented fixture accounts have a non-null `email_verified_at` value.

## 08.01: Super Admin login

1. Sign in locally as the documented Super Admin account.
2. Open an administrative page.

**Expected:** The account passes the verified-user gate and retains Super Admin access.  
**Result:** NOT RUN

## 08.02: Program Staff login and program scope

1. Sign in locally as Program Staff.
2. Navigate to Program A administration.
3. Attempt a Program B staff operation using a direct URL when available.

**Expected:** Program A actions are available only when the permission and active Program A scope both allow them; Program B staff operations are denied.  
**Result:** NOT RUN

## 08.03: Applicant login and boundary

1. Sign in locally as Applicant.
2. Open applications owned by the account, when fixture application data exists.
3. Attempt a Program Staff or Eligibility URL.

**Expected:** Applicant access is limited to ownership/delegation behavior and does not expose Staff/Judge authority.  
**Result:** NOT RUN

## 08.04: Judge login and boundary

1. Sign in locally as Judge.
2. Attempt direct access to program administration and eligibility screening URLs.

**Expected:** The identity is email verified but receives no global Program Staff authority or unassigned application access.  
**Result:** NOT RUN

## 08.05: Decision Maker login and boundary

1. Sign in locally as Decision Maker.
2. Attempt direct access to Program Staff and Judge-only routes.

**Expected:** The identity is email verified but has no broad global permission from the QA fixture.  
**Result:** NOT RUN

## 08.06: Pre-verified fixture behavior

1. Sign in separately with all five documented fixture accounts.
2. Observe post-login navigation.

**Expected:** No account is blocked by Fortify email verification; authorization boundaries still apply after login.  
**Result:** NOT RUN

## 08.07: Permission and navigation visibility

1. Compare Program Staff, Applicant, Judge, and Decision Maker navigation/actions.
2. Attempt direct URLs for hidden actions.

**Expected:** UI visibility reflects grants where implemented, while direct requests remain enforced by backend routes and policies.  
**Result:** NOT RUN

## 08.08: Application and Eligibility authorization boundaries

1. As Program Staff, attempt allowed Program A eligibility access once the UI exists.
2. As Applicant, Judge, and Decision Maker, attempt the same direct URLs.

**Expected:** Only an actor with the required permission and Program Staff scope is allowed. The absence of current Eligibility/Screening Vue pages is a known delivery blocker and does not constitute a pass.  
**Result:** NOT RUN

## Evidence Rules

- Record the account, URL/action, actual HTTP outcome, and timestamp for each future run.
- Do not mark a scenario PASS until browser evidence exists.
- Do not change fixture roles, permissions, scope, or passwords while conducting manual QA.
