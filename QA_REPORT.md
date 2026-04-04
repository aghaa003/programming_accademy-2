# QA Test Report — Programming Academy

**Date:** 2025-07  
**Tester:** QA Agent (automated)  
**Application:** Programming Academy — Laravel SPA  
**Base URL:** http://localhost:8000  
**Auth Credentials Used:** aghaa003@gmail.com / _(admin role, id=1)_

---

## 1. Executive Summary

Full QA testing was performed across all 18 HTML pages and 40+ API endpoints of the Programming Academy application. Testing covered page discovery, unauthenticated access controls, authentication flows, session handling, form validation, and admin CRUD operations.

**Results at a glance:**

| Severity         | Count |
| ---------------- | ----- |
| 🔴 Critical (P1) | 2     |
| 🟠 High (P2)     | 1     |
| 🟡 Medium (P3)   | 4     |
| 🔵 Low (P4)      | 3     |
| ✅ Passed checks | 21    |

---

## 2. Application Overview

| Attribute      | Value                                                                  |
| -------------- | ---------------------------------------------------------------------- |
| Framework      | Laravel 11 (PHP)                                                       |
| Frontend type  | SPA (static HTML + fetch() API calls)                                  |
| Authentication | Laravel Sanctum (SPA session-based)                                    |
| CSRF mechanism | `/sanctum/csrf-cookie` → `XSRF-TOKEN` cookie → `X-XSRF-TOKEN` header   |
| Session driver | File-based, 120-minute lifetime, encrypted                             |
| Rate limiting  | Login: 10/min; Forgot-password: 5/min; Brute-force: 10 failures/15 min |

---

## 3. Pages Discovered

| #   | Page                  | URL                        | Notes                                   |
| --- | --------------------- | -------------------------- | --------------------------------------- |
| 1   | Home                  | `/index.html`              | Main landing page                       |
| 2   | Examples              | `/examples.html`           | Code example gallery                    |
| 3   | Challenges            | `/challenges.html`         | Coding challenges (NaN% bug here)       |
| 4   | Problem Solving       | `/proplemsolving.html`     | ⚠️ Filename typo                        |
| 5   | Roadmap               | `/roadmap.html`            | Learning roadmap                        |
| 6   | Path                  | `/path.html`               | Defaults to basics                      |
| 7   | Path - Basics         | `/path.html?path=basics`   |                                         |
| 8   | Path - Frontend       | `/path.html?path=frontend` |                                         |
| 9   | Path - Backend        | `/path.html?path=backend`  |                                         |
| 10  | Projects              | `/project.html`            |                                         |
| 11  | Login                 | `/login1.html`             | Note: filename is `login1`, not `login` |
| 12  | Register              | `/register.html`           |                                         |
| 13  | Forgot Password       | `/forgot-password.html`    |                                         |
| 14  | Reset Password        | `/reset-password.html`     |                                         |
| 15  | Profile               | `/profile.html`            |                                         |
| 16  | Watch (lesson player) | `/watch.html`              | Requires `?id=` param                   |
| 17  | AI Chat Modal         | `/ai_chat_modal.html`      | Modal component, not standalone         |
| 18  | Example Demo          | `/example_demo.html`       | Requires `?id=` param                   |

All 18 pages return **HTTP 200**.

---

## 4. API Routes Mapped

### Public (no auth required)

```
GET  /api/courses          GET  /api/challenges       GET  /api/examples
GET  /api/reviews          GET  /api/leaderboard       GET  /api/platforms
GET  /api/courses/{id}     GET  /api/lessons           GET  /api/ai/public-conversations
POST /api/login            POST /api/register          POST /api/forgot-password
GET  /api/user/check-availability
```

### Authenticated (auth:sanctum)

```
POST /api/logout           GET  /api/user/status       GET  /api/profile
PUT  /api/profile          GET  /api/user-courses       POST /api/progress
POST /api/challenges/submit                             GET  /api/ai/conversations
POST /api/ai/conversations/{id}/messages
```

### Admin only (auth:sanctum + admin middleware)

```
GET  /api/admin/stats      GET  /api/admin/audit-logs  GET  /api/admin/users
POST/PUT/DELETE /api/admin/courses/{id}
POST/PUT/DELETE /api/admin/lessons/{id}
POST/PUT/DELETE /api/admin/platforms/{id}
POST/PUT/DELETE /api/admin/examples/{id}
POST/PUT/DELETE /api/admin/challenges/{id}
POST/PUT/DELETE /api/admin/assignments/{id}
POST /api/admin/upload     PUT  /api/admin/users/{id}/toggle-admin
PUT  /api/admin/users/{id}/suspend
```

---

## 5. Issues Found

### 🔴 BUG-001 — Missing Enum Validation in AdminCourseRequest

**Priority:** Critical (P1)  
**Location:** `app/Http/Requests/Admin/AdminCourseRequest.php` / `POST /api/admin/courses`

**Description:**  
The `category` and `level` fields accept any arbitrary string. There is no `in:` enum validation rule. Notably, a validation _message_ for `level.in` exists in the `messages()` array (proving the rule was intended), but the constraint itself was never added to `rules()`.

**Reproduction:**

```bash
# Returns HTTP 201 — course created with invalid data
POST /api/admin/courses
{"title":"Test","category":"invalid_category","level":"not_a_level","price":"not_a_number"}
```

**Impact:**

- Courses with nonsensical categories/levels corrupting the data model
- Frontend category filtering and course listings break or show courses incorrectly
- Inconsistent data already present (course id=26 has `level:"أساسي"`, others use `"Beginner"`)

**Fix:**

```php
// In AdminCourseRequest::rules()
'category' => ['required', 'string', 'in:basics,frontend,backend,database,devops'],
'level'    => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,أساسي,مبتدئ-متوسط'],
'price'    => ['nullable', 'numeric', 'min:0'],
```

---

### 🔴 BUG-002 — NaN% Success Rate on Challenges Page

**Priority:** Critical (P1)  
**Location:** `GET /api/challenges` response · `public/challenges.html` JavaScript

**Description:**  
The API returns `total_attempts` as a **string** (e.g., `"0"`, `"5"`) while `total_completions` is returned as a **number** (e.g., `0`, `1`). This is a type inconsistency likely caused by a SQL aggregate `COUNT()` being returned as a string by the PDO driver.

In `challenges.html`, the success rate is calculated as:

```javascript
function calculateSuccessRate(totalAttempts, totalCompletions) {
    if (totalAttempts === 0) return "0%"; // "0" === 0 → FALSE (strict check fails!)
    const rate = Math.round((totalCompletions / totalAttempts) * 100);
    // 0 / "0" → NaN, Math.round(NaN) → NaN → displays "NaN%"
}
```

The call site:

```javascript
calculateSuccessRate(
    challenge.total_attempts || 0,
    challenge.total_completions || 0,
);
// "0" || 0 → "0" (non-empty string is truthy!) ← passes the guard wrong type
```

**Evidence from API:**

```json
{ "total_attempts": "0", "total_completions": 0 } // ← string vs number
```

**Impact:**  
Every challenge with 0 attempts shows **"NaN% نجاح"** instead of "0% نجاح". Visible to all users on the main challenges page.

**Fix (choose one):**  
Option A — Fix the API (cast at the DB level, preferred):

```php
// In ChallengeController, cast to int:
DB::raw('CAST(COUNT(...) AS UNSIGNED) as total_attempts')
// or use model cast: 'total_attempts' => 'integer'
```

Option B — Fix the JavaScript guard:

```javascript
if (parseInt(totalAttempts) === 0) return "0%";
```

---

### 🟠 BUG-003 — Suspicious Test Account with Admin Role

**Priority:** High (P2)  
**Location:** Database `users` table / `GET /api/admin/users`

**Description:**  
User with username `"123"` (id=14, purely numeric username) has the `admin` role. This appears to be a test account accidentally promoted to admin. A second non-obvious admin is also present (`"deniz"`, id=5).

**Evidence:**

```json
{ "id": 14, "username": "123", "roles": [{ "name": "admin" }] }
```

**Impact:**  
If these are shared test accounts or were created during development, they represent an unauthorized admin access vector. In a production environment this is a security issue.

**Fix:**

1. Revoke admin role from user "123" unless intentional
2. Audit all admin-role assignments via `GET /api/admin/users`
3. Implement mandatory logging/approval workflow for admin promotion (`/api/admin/users/{id}/toggle-admin`)

---

### 🟡 BUG-004 — Filename Typo: `proplemsolving.html`

**Priority:** Medium (P3)  
**Location:** `public/proplemsolving.html` + 18 navigation links across all HTML pages

**Description:**  
The problem-solving resources page is named `proplemsolving.html` (missing the "b" in "problem"). All 18+ navigation links across all HTML pages consistently use the same typo. Navigation is not broken because the links and file are consistently misspelled, but the URL is visible in the browser address bar and in shared links.

**Impact:**

- Unprofessional URL visible to users
- SEO: the URL will be indexed incorrectly
- Any external links to the correct URL `problemsolving.html` will 404

**Fix:**  
Rename `proplemsolving.html` → `problemsolving.html` and update all 18+ navigation links. Consider adding a 301 redirect or alias from the old URL.

---

### 🟡 BUG-005 — `user-manager.js` Referenced but Missing

**Priority:** Medium (P3)  
**Location:** `login1.html:438`, `profile.html:826–827`, `register.html:503`

**Description:**  
Three HTML pages reference a `<script src="user-manager.js">` that does not exist anywhere in the project. The developers acknowledged this with a comment: `"user-manager.js is optional / not present in workspace; commented out to avoid 404"`. All three references are currently commented out.

**Impact:**

- Dead commented code indicating incomplete feature
- If the comments are removed (e.g., by a future developer), users will see JavaScript 404 errors and potentially broken functionality

**Fix:**  
Either implement the `user-manager.js` functionality described by the references, or permanently remove the commented-out lines from all three files.

---

### 🟡 BUG-006 — `watch.html` and `example_demo.html` Show Blank/Empty Without Required Parameters

**Priority:** Medium (P3)  
**Location:** `public/watch.html`, `public/example_demo.html`

**Description:**

- `watch.html` requires a `?id=` query parameter specifying the lesson ID. Without it, the page renders a blank content area with no error message.
- `example_demo.html` requires a `?id=` query parameter specifying the example ID. Without it, the page renders empty with no user feedback.

**Impact:**  
Poor user experience — users who navigate to these pages directly (e.g., via bookmarks missing the query param, or a broken link) see a blank page with no explanation.

**Fix:**  
Add a check for missing/invalid `id` parameter and display a helpful error message with a link back to the listing page (`/watch.html` → `/path.html`, `/example_demo.html` → `/examples.html`).

---

### 🟡 BUG-007 — `total_attempts` Has Wrong API Type (String Instead of Integer)

**Priority:** Medium (P3)  
**Location:** `GET /api/challenges` and potentially other endpoints using SQL aggregate functions

**Description:**  
The `total_attempts` field is returned as a JSON string (`"0"`, `"5"`) instead of an integer (`0`, `5`). This is the direct root cause of BUG-002 (NaN% display). Several SQL `COUNT()` or `SUM()` aggregates in the Laravel controllers may have the same issue.

**Fix:**  
Add integer casts in the relevant Eloquent model or controller:

```php
// In Challenge model:
protected $casts = ['total_attempts' => 'integer'];
// Or in controller:
'total_attempts' => (int) $row->total_attempts,
```

---

### 🔵 BUG-008 — Challenges Missing `test_cases` and `starter_code`

**Priority:** Low (P4)  
**Location:** Database `challenges` table

**Description:**  
Most (20+) challenges have `test_cases: null` and `starter_code: null`. If the challenge submission/evaluation system requires these fields to run code tests, submissions will silently fail or return empty results.

**Impact:**  
Challenge submission may not work for most challenges. The practice coding functionality would be non-functional.

**Fix:**  
Populate `test_cases` and `starter_code` for all challenges, or add a graceful handling path when these fields are null.

---

### 🔵 BUG-009 — Inconsistent Course Level Values in Database

**Priority:** Low (P4)  
**Location:** Database `courses` table

**Description:**  
Existing courses use inconsistent level strings:

- `"Beginner"` (English — courses id=8, 6)
- `"مبتدئ-متوسط"` (Arabic — course id=23)
- `"أساسي"` (Arabic — course id=26)

This is partly caused by BUG-001 (no enum validation). UI level filters will show all three variants as separate options (or fail to match).

**Fix:**

1. Fix BUG-001 first (add enum validation to prevent future inconsistency)
2. Migrate existing courses to use a consistent level value set

---

### 🔵 BUG-010 — Assignment Creation Allows Arbitrarily Large `assignment_order`

**Priority:** Low (P4)  
**Location:** `app/Http/Controllers/Admin/AdminAssignmentController.php::store()`

**Description:**  
The `assignment_order` field has no upper bound validation. The code uses `max(1, ...)` to enforce a minimum of 1, but no maximum. An integer overflow or very large value could cause ordering issues.

**Fix:**  
Add `$order = min(9999, max(1, (int) $request->input('assignment_order', 1)));` or use FormRequest with `integer|min:1|max:9999`.

---

## 6. Test Results Summary

### Authentication & Session Tests ✅

| Test                                       | Expected                      | Result  |
| ------------------------------------------ | ----------------------------- | ------- |
| Login with empty body                      | 422 + per-field Arabic errors | ✅ Pass |
| Login with wrong credentials               | 401 generic (no enumeration)  | ✅ Pass |
| Login with correct credentials             | 200 + session established     | ✅ Pass |
| Logout                                     | 200 + session invalidated     | ✅ Pass |
| Reuse session after logout                 | 401 Unauthenticated           | ✅ Pass |
| Admin role verified via `/api/user/status` | `roles:["admin"]`             | ✅ Pass |
| CSRF protection on mutations               | 419 without X-XSRF-TOKEN      | ✅ Pass |
| Session regenerated on login               | Old CSRF token refused        | ✅ Pass |

### API Access Control Tests ✅

| Test                                     | Expected | Result                  |
| ---------------------------------------- | -------- | ----------------------- |
| Unauthenticated → protected endpoint     | 401 JSON | ✅ Pass                 |
| Non-admin → admin endpoint               | 403 JSON | ✅ Pass (code verified) |
| Public endpoints accessible without auth | 200 OK   | ✅ Pass                 |
| Admin session → all 8 admin endpoints    | 200 OK   | ✅ Pass                 |

### Form Validation Tests ✅ / ❌

| Form / Endpoint                                   | Test                    | Result                |
| ------------------------------------------------- | ----------------------- | --------------------- |
| `POST /api/register` — empty body                 | 422 + per-field errors  | ✅ Pass               |
| `POST /api/register` — invalid email              | 422 email.email error   | ✅ Pass               |
| `POST /api/register` — username < 3 chars         | 422 username.min error  | ✅ Pass               |
| `POST /api/register` — password < 8 chars         | 422 password.min error  | ✅ Pass               |
| `POST /api/admin/courses` — invalid category      | Should 422, got **201** | ❌ **FAIL** (BUG-001) |
| `POST /api/admin/courses` — invalid level         | Should 422, got **201** | ❌ **FAIL** (BUG-001) |
| `POST /api/admin/challenges` — invalid category   | 422 category.in error   | ✅ Pass               |
| `POST /api/admin/challenges` — invalid difficulty | 422 difficulty.in error | ✅ Pass               |
| `POST /api/admin/assignments` — no course_id      | 422 course not found    | ✅ Pass               |
| `POST /api/admin/assignments` — no question       | 422 question required   | ✅ Pass               |

### Security Checks

| Check                                 | Result                                     |
| ------------------------------------- | ------------------------------------------ |
| SQL injection (Eloquent ORM used)     | ✅ No raw string interpolation found       |
| CSRF protection active                | ✅ Pass                                    |
| Passwords hashed (bcrypt, max:72)     | ✅ Pass                                    |
| No password in API responses          | ✅ Pass                                    |
| Session regenerated on login          | ✅ Pass                                    |
| Rate limiting on login endpoint       | ✅ Pass (10/min)                           |
| Brute force lockout after 10 failures | ✅ Pass (code verified)                    |
| Remember-me cookie cleared on logout  | ✅ Pass                                    |
| Admin accounts audited                | ⚠️ Suspicious test account with admin role |

---

## 7. Priority Fix List

| Priority | ID      | Finding                                                | Estimated Effort |
| -------- | ------- | ------------------------------------------------------ | ---------------- |
| 🔴 P1    | BUG-001 | Add `in:` enum validation to AdminCourseRequest        | 30 min           |
| 🔴 P1    | BUG-002 | Fix `total_attempts` type → cast to int in API         | 30 min           |
| 🟠 P2    | BUG-003 | Audit and revoke admin from test accounts              | 1 hr             |
| 🟡 P3    | BUG-007 | Audit all aggregate fields for string→int casting      | 1 hr             |
| 🟡 P3    | BUG-004 | Rename file + update 18+ navigation links              | 1 hr             |
| 🟡 P3    | BUG-006 | Add empty-state UI to watch.html and example_demo.html | 2 hr             |
| 🟡 P3    | BUG-005 | Remove commented user-manager.js references            | 30 min           |
| 🔵 P4    | BUG-009 | Normalize course level values in DB                    | 1 hr             |
| 🔵 P4    | BUG-008 | Populate test_cases/starter_code for challenges        | Large            |
| 🔵 P4    | BUG-010 | Add max bound to assignment_order validation           | 15 min           |

---

## 8. Appendix: Test Environment Detail

| Item           | Value                                          |
| -------------- | ---------------------------------------------- |
| Server         | PHP built-in server (`php -S 127.0.0.1:8000`)  |
| PHP            | XAMPP PHP at `C:\xampp\php\php.exe`            |
| Project root   | `C:\xampp\htdocs\programming-academy-laravel\` |
| Session driver | File (`.env: SESSION_DRIVER=file`)             |
| DB             | MySQL via XAMPP                                |
| Auth domain    | `localhost:8000` (SANCTUM_STATEFUL_DOMAINS)    |

**Test admin account:**

```json
{
    "id": 1,
    "username": "ahmadaghaa003",
    "email": "aghaa003@gmail.com",
    "roles": ["admin"]
}
```

**All users in system at time of test:**
| ID | Username | Roles |
|---|---|---|
| 1 | ahmadaghaa003 | admin |
| 2 | (redacted) | admin |
| 5 | deniz | admin |
| 14 | 123 | admin ⚠️ |
| ... | ... | student / none |

---

_Report generated by automated QA testing agent. All API tests performed via curl with session-based Sanctum authentication._
