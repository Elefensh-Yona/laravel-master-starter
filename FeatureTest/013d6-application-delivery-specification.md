# EAIC Application HTTP Delivery Specification

## APP-HTTP-001

- **Test ID:** APP-HTTP-001
- **Title:** Authorized user can create an Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Authenticated and verified user with `application.create` permission
- **Authentication:** Authenticated and verified
- **Program context:** Valid published Program exists
- **Preconditions:** User has `application.create` permission
- **Test data:** Program ID, applicant type (INDIVIDUAL/TEAM/ORGANIZATION), optional reference
- **Exact action:** `POST /applications` with valid create request
- **Expected HTTP result:** 302 redirect to application show route
- **Expected authorization result:** ALLOW
- **Expected database/business result:** Application row created with status=draft, initial ApplicationVersion created with status=draft and version_number=1
- **Security reason:** Authenticated user with permission and no program scope restriction can create their own Application
- **Evidence required:** HTTP response and created application record
- **PASS criteria:** Application created and user is redirected
- **FAIL criteria:** 403 Forbidden or application not created

## APP-HTTP-002

- **Test ID:** APP-HTTP-002
- **Title:** Unauthorized user without permission cannot create an Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Authenticated and verified user without `application.create` permission
- **Authentication:** Authenticated and verified
- **Preconditions:** User lacks `application.create` permission
- **Test data:** Program ID, applicant type
- **Exact action:** `POST /applications` with valid create data
- **Expected HTTP result:** 403 Forbidden
- **Expected authorization result:** DENY
- **Expected database/business result:** No Application created
- **Security reason:** Permission middleware blocks create without explicit permission
- **Evidence required:** 403 response
- **PASS criteria:** Request denied with 403
- **FAIL criteria:** Application created or 200 response

## APP-HTTP-003

- **Test ID:** APP-HTTP-003
- **Title:** Application owner can access their own Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists where user is primary_owner_id
- **Test data:** Application ID
- **Exact action:** `GET /applications/{application}`
- **Expected HTTP result:** 200 with application data
- **Expected authorization result:** ALLOW
- **Expected database/business result:** Application record retrieved
- **Security reason:** Owner authorization is checked by ApplicationPolicy::view
- **Evidence required:** 200 response and application summary data
- **PASS criteria:** Application data displayed
- **FAIL criteria:** 403 or missing data

## APP-HTTP-004

- **Test ID:** APP-HTTP-004
- **Title:** Unauthorized user cannot access another user's draft Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Different user without `application.view` permission
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists with different primary_owner_id; Application status is draft
- **Test data:** Application ID of another user's draft
- **Exact action:** `GET /applications/{application}`
- **Expected HTTP result:** 403 Forbidden
- **Expected authorization result:** DENY
- **Expected database/business result:** No data returned
- **Security reason:** Draft Applications visible only to owner unless viewing with `application.view` permission
- **Evidence required:** 403 response
- **PASS criteria:** Access denied with 403
- **FAIL criteria:** Application data displayed or 200 response

## APP-HTTP-005

- **Test ID:** APP-HTTP-005
- **Title:** Application owner can edit their own draft Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists with current draft version
- **Test data:** Updated content object
- **Exact action:** `GET /applications/{application}/edit`, then `PUT /applications/{application}` with new content
- **Expected HTTP result:** 302 redirect to show route on PUT
- **Expected authorization result:** ALLOW
- **Expected database/business result:** ApplicationVersion record updated with new content, version_number unchanged
- **Security reason:** Owner can update their own draft content
- **Evidence required:** Updated version record and redirect response
- **PASS criteria:** Version content updated and user redirected
- **FAIL criteria:** 403 or content not updated

## APP-HTTP-006

- **Test ID:** APP-HTTP-006
- **Title:** Application owner cannot edit another user's draft Application
- **Priority:** High
- **Feature/module:** Application delivery
- **Actor:** Different user
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists with different primary_owner_id
- **Test data:** Updated content object for another's application
- **Exact action:** `PUT /applications/{application}` for another user's application
- **Expected HTTP result:** 403 Forbidden
- **Expected authorization result:** DENY
- **Expected database/business result:** No version content updated
- **Security reason:** Ownership is enforced by policy before version edit is allowed
- **Evidence required:** 403 response
- **PASS criteria:** Access denied with 403
- **FAIL criteria:** Application updated or 200 response

## APP-VERSION-HTTP-001

- **Test ID:** APP-VERSION-HTTP-001
- **Title:** Initial draft Version exists and can be edited
- **Priority:** High
- **Feature/module:** Application versioning
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application created with initial draft version
- **Test data:** None (version pre-exists)
- **Exact action:** Retrieve current version from application
- **Expected HTTP result:** 200
- **Expected authorization result:** ALLOW
- **Expected database/business result:** ApplicationVersion row exists with version_number=1, status=draft
- **Security reason:** Initial version is created atomically with Application
- **Evidence required:** Version record with correct number and status
- **PASS criteria:** Initial version is draft with version_number=1
- **FAIL criteria:** Missing version or incorrect state

## APP-VERSION-HTTP-002

- **Test ID:** APP-VERSION-HTTP-002
- **Title:** Editable draft Version can be modified by authorized owner
- **Priority:** High
- **Feature/module:** Application versioning
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Draft Application with draft ApplicationVersion
- **Test data:** New content object
- **Exact action:** `PUT /applications/{application}` with new content
- **Expected HTTP result:** 302 redirect on success
- **Expected authorization result:** ALLOW
- **Expected database/business result:** ApplicationVersion.content updated, version_number unchanged, status remains draft
- **Security reason:** Owners may edit draft versions they created
- **Evidence required:** Updated version content and redirect response
- **PASS criteria:** Version content modified without changing version_number
- **FAIL criteria:** Version not updated or version_number changed

## APP-VERSION-HTTP-003

- **Test ID:** APP-VERSION-HTTP-003
- **Title:** Submitted Version becomes immutable
- **Priority:** Critical
- **Feature/module:** Application versioning
- **Actor:** Any actor, including owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application with submitted version (status=submitted)
- **Test data:** New content object for update
- **Exact action:** `PUT /applications/{application}` with update attempt on submitted version
- **Expected HTTP result:** 400 or 422 error, or 302 with error message
- **Expected authorization result:** DENY or application logic rejects
- **Expected database/business result:** ApplicationVersion row unchanged; no mutation of submitted content
- **Security reason:** Submitted versions must be immutable for auditability and Judge reference integrity
- **Evidence required:** Rejection response and unchanged version content
- **PASS criteria:** Submitted version is not modified
- **FAIL criteria:** Submitted version content changed

## APP-VERSION-HTTP-004

- **Test ID:** APP-VERSION-HTTP-004
- **Title:** Submitted Version can be viewed by authorized actors
- **Priority:** High
- **Feature/module:** Application versioning
- **Actor:** Application owner or Program staff with `application.view`
- **Authentication:** Authenticated and verified
- **Preconditions:** Application with submitted version
- **Test data:** Application ID
- **Exact action:** `GET /applications/{application}` retrieve submitted version
- **Expected HTTP result:** 200 with version data
- **Expected authorization result:** ALLOW
- **Expected database/business result:** Submitted version data returned
- **Security reason:** Historical submitted versions remain visible for audit and future workflow
- **Evidence required:** Version data in response including submitted_at timestamp
- **PASS criteria:** Submitted version is readable
- **FAIL criteria:** Version not returned or 403 error

## APP-VERSION-HTTP-005

- **Test ID:** APP-VERSION-HTTP-005
- **Title:** Revision action creates a new Version with next version_number
- **Priority:** High
- **Feature/module:** Application versioning
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application with submitted version (version_number=1, status=submitted)
- **Test data:** None (revision copies previous content)
- **Exact action:** `POST /applications/{application}/revise`
- **Expected HTTP result:** 302 redirect to show route
- **Expected authorization result:** ALLOW
- **Expected database/business result:** New ApplicationVersion created with version_number=2, status=draft, content copied from version 1, supersedes_version_id=1
- **Security reason:** Revisions preserve submitted history and create a new editable draft for next submission cycle
- **Evidence required:** New version record with correct version_number and relationships
- **PASS criteria:** New revision version created with incremented version_number
- **FAIL criteria:** Version_number not incremented or draft not created

## APP-VERSION-HTTP-006

- **Test ID:** APP-VERSION-HTTP-006
- **Title:** Previous submitted Version remains unchanged and accessible
- **Priority:** Critical
- **Feature/module:** Application versioning
- **Actor:** Application owner or authorized Program staff
- **Authentication:** Authenticated and verified
- **Preconditions:** Multiple versions exist; revision has been created from submitted version
- **Test data:** Application with versions 1 (submitted) and 2 (draft)
- **Exact action:** Retrieve version history and inspect version 1 data
- **Expected HTTP result:** 200 with historical version data
- **Expected authorization result:** ALLOW
- **Expected database/business result:** Version 1 content and submitted_at remain unchanged; version 2 references version 1 as supersedes_version_id
- **Security reason:** Historical versions are immutable and preserved for audit, Judge reference, and accountability
- **Evidence required:** Version 1 record unchanged with correct metadata
- **PASS criteria:** Previous submitted version is preserved unchanged
- **FAIL criteria:** Version 1 content modified or deleted

## APP-SUBMIT-HTTP-001

- **Test ID:** APP-SUBMIT-HTTP-001
- **Title:** Authorized owner can submit a draft Version
- **Priority:** Critical
- **Feature/module:** Application submission
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application with draft version
- **Test data:** `confirmed: true`
- **Exact action:** `POST /applications/{application}/submit` with confirmation
- **Expected HTTP result:** 302 redirect to show route
- **Expected authorization result:** ALLOW
- **Expected database/business result:** ApplicationVersion.status changed from draft to submitted; ApplicationVersion.submitted_at set to current time; Application.submitted_at set; Application.status changed to submitted
- **Security reason:** Only the owner can submit their application, and submission is consequential and audited
- **Evidence required:** Updated version and application records with submitted timestamps
- **PASS criteria:** Version and application status changed to submitted atomically
- **FAIL criteria:** Status not changed or only partially updated

## APP-SUBMIT-HTTP-002

- **Test ID:** APP-SUBMIT-HTTP-002
- **Title:** Unauthorized user cannot submit another user's Version
- **Priority:** Critical
- **Feature/module:** Application submission
- **Actor:** Different user
- **Authentication:** Authenticated and verified
- **Preconditions:** Application owned by different user with draft version
- **Test data:** Application ID of another user
- **Exact action:** `POST /applications/{application}/submit`
- **Expected HTTP result:** 403 Forbidden
- **Expected authorization result:** DENY
- **Expected database/business result:** Version status unchanged; Application status unchanged
- **Security reason:** Only owners can submit their own applications
- **Evidence required:** 403 response and unchanged status
- **PASS criteria:** Submission blocked with 403
- **FAIL criteria:** Application submitted by unauthorized actor

## APP-SUBMIT-HTTP-003

- **Test ID:** APP-SUBMIT-HTTP-003
- **Title:** Already submitted Version cannot be submitted again
- **Priority:** High
- **Feature/module:** Application submission
- **Actor:** Application primary owner
- **Authentication:** Authenticated and verified
- **Preconditions:** Application with submitted version (already submitted once)
- **Test data:** `confirmed: true`
- **Exact action:** `POST /applications/{application}/submit` on already-submitted version
- **Expected HTTP result:** 400 or 422 error, or 302 with error message
- **Expected authorization result:** ALLOW (owner), but application logic rejects
- **Expected database/business result:** Version status unchanged
- **Security reason:** A version can only be submitted once; further changes require revision
- **Evidence required:** Rejection response and unchanged submitted_at
- **PASS criteria:** Duplicate submission rejected
- **FAIL criteria:** Version submitted twice or submitted_at changed

## APP-SCOPE-HTTP-001

- **Test ID:** APP-SCOPE-HTTP-001
- **Title:** Application outside user's permitted Program scope cannot be modified
- **Priority:** Critical
- **Feature/module:** Application authorization scoping
- **Actor:** User without Program membership or `application.update` permission
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists in Program A; user has no scope in Program A
- **Test data:** Application ID in Program A
- **Exact action:** `PUT /applications/{application}` or `POST /applications/{application}/submit`
- **Expected HTTP result:** 403 Forbidden
- **Expected authorization result:** DENY
- **Expected database/business result:** No modification occurs
- **Security reason:** Application ownership is independent; but Program scope and authorization boundaries must be enforced
- **Evidence required:** 403 response
- **PASS criteria:** Cross-program access blocked
- **FAIL criteria:** Application modified or access granted

## APP-SCOPE-HTTP-002

- **Test ID:** APP-SCOPE-HTTP-002
- **Title:** Direct URL access cannot bypass Application ownership policy
- **Priority:** Critical
- **Feature/module:** Application authorization
- **Actor:** User accessing another user's application via direct URL
- **Authentication:** Authenticated and verified
- **Preconditions:** Application exists with different owner
- **Test data:** Application ID in URL for another user's application
- **Exact action:** Direct navigation to `/applications/{application}` or `/applications/{application}/edit`
- **Expected HTTP result:** 403 Forbidden (on edit); 200 with limited view (on show if submitted)
- **Expected authorization result:** DENY (edit); conditionally allow (view submitted only)
- **Expected database/business result:** No unauthorized access or modification
- **Security reason:** Policy is the only authorization boundary; URL route does not bypass policy
- **Evidence required:** Policy enforcement response
- **PASS criteria:** Policy boundaries enforced regardless of direct URL access
- **FAIL criteria:** Unauthorized access or modification allowed
