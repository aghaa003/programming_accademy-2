# React Frontend ↔ Laravel Backend Integration Checklist

**Last Updated:** 2026-04-10  
**Status:** Frontend changes READY, Backend changes COMPLETE ✅  
**All Tests Passing:** 58/58 ✅

---

## 1. Current State Summary

### ✅ What's Complete

| Component               | Status          | Notes                                                                                            |
| ----------------------- | --------------- | ------------------------------------------------------------------------------------------------ |
| **Laravel Backend**     | ✅ Ready        | All 25 controllers standardized, 58 tests passing, API response format consistent                |
| **React Frontend**      | ✅ Ready        | Built with TypeScript/Vite, authentication context ready, API client generated from OpenAPI spec |
| **Database**            | ✅ Ready        | MariaDB 10.4.32 (prod), SQLite :memory: (tests), all migrations applied                          |
| **Authentication**      | ✅ Ready        | Sanctum SPA with session cookies, React auth-context expects `/api/auth/me` endpoint             |
| **API Response Format** | ✅ Standardized | Direct array/object responses, `{error: string}` for errors, `{success: boolean}` for auth       |
| **AuthController**      | ✅ Updated      | Accepts `emailAddress` field (React), `identifier` (legacy), returns `{success: true}`           |
| **File Uploads**        | ✅ Ready        | Avatars → `public/uploads/avatars/`, videos → `storage/app/videos/`, MIME validation in place    |

### ⚠️ What Needs to Be Done

| Item                                             | Priority   | Effort  | Notes                                                                               |
| ------------------------------------------------ | ---------- | ------- | ----------------------------------------------------------------------------------- |
| Test React frontend against live Laravel backend | **HIGH**   | 2-3 hrs | Run React dev server, point at Laravel API, test login/register/course listing      |
| Verify ALL endpoints match OpenAPI spec          | **HIGH**   | 1-2 hrs | Check field names, response wrappers, pagination formats against `openapi.yaml`     |
| Test concurrent operations (race conditions)     | **MEDIUM** | 1 hr    | Assignment submissions, challenge scoring, challenge grading                        |
| Test file uploads (avatar, video, logo)          | **MEDIUM** | 1 hr    | End-to-end flow: upload, verify DB, verify file on disk, delete                     |
| CORS configuration                               | **MEDIUM** | 30 min  | Set `VITE_API_URL` in React `.env`, verify cookies sent with `credentials: include` |
| Environment variables                            | **MEDIUM** | 30 min  | Copy `.env.example` → `.env`, set `APP_URL`, `DB_*`, `MAIL_MAILER=log` (dev)        |
| Test error handling (4xx/5xx)                    | **LOW**    | 1 hr    | Invalid login, 404 not found, 409 conflicts, 500 server errors                      |
| Production deployment checklist                  | **LOW**    | 2-3 hrs | Environment hardening, HTTPS, CORS allowlist, database backup strategy              |

---

## 2. Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                 React Frontend (TypeScript)                 │
│        frontend/academy_clean/artifacts/academy/            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ • Vite build tool                                   │    │
│  │ • React Router for navigation                       │    │
│  │ • Auth context (Sanctum SPA)                        │    │
│  │ • API client generated from OpenAPI spec (Orval)   │    │
│  │ • Credentials: 'include' on all requests            │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                           ↕ HTTP/HTTPS
                    (CORS configured)
┌─────────────────────────────────────────────────────────────┐
│              Laravel 12.x Backend (PHP 8.2)                 │
│       c:\xampp\htdocs\programming-academy-laravel           │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ • Sanctum SPA authentication (session + CSRF)       │    │
│  │ • 25 standardized API controllers                   │    │
│  │ • Rate limiting on sensitive endpoints              │    │
│  │ • Response format: direct array/object              │    │
│  │ • Error format: {error: string}                     │    │
│  │ • 58 tests (PHPUnit) — all passing ✅              │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                           ↕ Sanctum
                    (session-based auth)
┌─────────────────────────────────────────────────────────────┐
│         Database Layer (MariaDB prod / SQLite test)          │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ • 17 models, 16 migrations, FK cascades configured  │    │
│  │ • Transactions for multi-step writes                │    │
│  │ • Atomic operations for concurrent requests         │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Pre-Integration Setup

### 3.1 Laravel Backend Setup (5-10 min)

```bash
# Navigate to project root
cd c:\xampp\htdocs\programming-academy-laravel

# 1. Install dependencies
composer install

# 2. Create .env file from example
copy .env.example .env

# 3. Set required keys in .env
set APP_KEY=                    # Run: php artisan key:generate
set DB_HOST=localhost
set DB_DATABASE=programming_academy
set DB_USERNAME=root
set DB_PASSWORD=                # XAMPP default: leave blank
set APP_URL=http://localhost:8000
set MAIL_MAILER=log             # Dev: use log driver

# 4. Run migrations
php artisan migrate

# 5. Seed database (optional test data)
php artisan db:seed

# 6. Start Laravel dev server
php artisan serve                # Runs on http://localhost:8000
```

### 3.2 React Frontend Setup (5-10 min)

```bash
# Navigate to React project
cd frontend\academy_clean\artifacts\academy

# 1. Install dependencies
npm install
# OR with pnpm (if used in monorepo):
pnpm install

# 2. Create .env file
copy .env.example .env

# 3. Set API URL to point to Laravel backend
set VITE_API_URL=http://localhost:8000

# 4. Start dev server (with HMR)
npm run dev              # Runs on http://localhost:5173

# 5. Open in browser
# http://localhost:5173 will proxy /api requests to http://localhost:8000
```

### 3.3 Database Setup (2-5 min)

```bash
# Option 1: MariaDB (production setup)
# XAMPP includes MariaDB at localhost:3306
# Create database:
mysql -u root < database/seeds/init.sql

# Option 2: SQLite (testing)
# Automatically created by Laravel in database/database.sqlite
```

---

## 4. Integration Testing Checklist

### Phase 1: Authentication (10-15 min)

- [ ] **Register a new user**
    - Navigate to `/sign-up` page
    - Enter email, password, username
    - Verify form validation (min 6 chars password, valid email)
    - Submit → check 201 response in Network tab
    - Verify user in DB: `SELECT * FROM users WHERE email='...'`
    - Verify password hash is NOT plaintext

- [ ] **Login with registered user**
    - Navigate to `/sign-in` page
    - Enter email/password
    - Check Response: `{success: true, id, username, firstName, lastName, email, avatar}`
    - Verify session cookie set in browser DevTools → Storage → Cookies
    - Verify CSRF token in response headers
    - Redirect to dashboard ✓

- [ ] **Test /api/auth/me endpoint**
    - After login, check Network tab for `GET /api/auth/me`
    - Verify response: `{user: {id, firstName, lastName, email, username, avatar, role}}`
    - Verify avatar is a full URL (`http://localhost:8000/uploads/...`)

- [ ] **Test logout**
    - Click logout button
    - Verify `POST /api/auth/logout` called
    - Verify session cookie cleared (empty or expires in past)
    - Verify redirect to `/sign-in`
    - Try accessing protected page → redirects back to login ✓

- [ ] **Test login with wrong credentials**
    - Try invalid email/password combination
    - Verify error response: `{success: false, error: "البريد الإلكتروني أو كلمة المرور غير صحيحة."}`
    - Verify 401/403 status code
    - Verify no session cookie set

### Phase 2: Course Listing & Viewing (5-10 min)

- [ ] **List courses (public endpoint)**
    - Navigate to `/` (dashboard)
    - Check Network tab for `GET /api/courses`
    - Verify response is array of courses: `[{id, title, category, level, ...}, ...]`
    - NOT wrapped in `{success, data}`
    - Verify course cards render properly (no XSS)

- [ ] **View course details**
    - Click on a course
    - Check Network tab for `GET /api/courses/{id}`
    - Verify response: single course object with lessons array
    - Verify lessons list populated under course

- [ ] **View lesson in course**
    - Click on a lesson
    - Check Network tab for video stream/content loading
    - Verify video plays (or placeholder shown)
    - Verify lesson progress tracking works

### Phase 3: User Profile & Preferences (5 min)

- [ ] **View profile page**
    - Navigate to `/profile`
    - Check Network: `GET /api/profile` (or `/api/auth/me`)
    - Verify all user fields display: name, email, phone, avatar, etc.

- [ ] **Update profile**
    - Edit name/email/preferences
    - Verify Network: `POST /api/profile` with updated data
    - Verify response returns updated user object
    - Refresh page → changes persist ✓

- [ ] **Upload avatar**
    - Click upload avatar button
    - Select image file
    - Verify Network: `POST /api/upload-avatar` (multipart/form-data)
    - Verify response: `{avatar: "http://localhost:8000/uploads/avatars/..."}`
    - Verify avatar image displays on profile
    - Verify file exists on disk: `public/uploads/avatars/`

### Phase 4: Challenges & Assignments (10-15 min)

- [ ] **List challenges**
    - Navigate to `/challenges`
    - Check Network: `GET /api/challenges?limit=20&offset=0`
    - Verify response: `[{id, title, description, category, difficulty, ...}]` (array, not wrapped)
    - Verify challenges render with difficulty badges

- [ ] **Submit challenge solution**
    - Click "Submit" on a challenge
    - Enter code solution
    - Submit
    - Verify Network: `POST /api/challenges/{id}/submit`
    - Response should be: `{status: 'correct'|'incorrect', score: 0-100}`
    - Verify score recorded in DB

- [ ] **List assignments**
    - Navigate to assignments section
    - Check Network: `GET /api/assignments`
    - Verify response format matches challenges (direct array)

- [ ] **Submit assignment**
    - Click submit on an assignment
    - Submit solution
    - Verify response: score recorded, grade displayed

### Phase 5: AI Features (5-10 min)

- [ ] **AI Chat**
    - Navigate to AI chat modal
    - Send message: "What is JavaScript?"
    - Verify Network: `POST /api/ai/general`
    - Response should return AI reply (from Ollama)
    - Verify conversation saves to DB

- [ ] **AI Code Verification**
    - On a challenge, click "AI Verify"
    - Paste code
    - Submit
    - Verify Network: `POST /api/ai/challenges/verify`
    - Response: `{verdict: 'yes'|'no', explanation: "..."}`

### Phase 6: Error Handling (3-5 min)

- [ ] **404 Not Found**
    - Navigate to `/api/courses/99999`
    - Verify response: `{error: "Course not found"}` with 404 status
    - NOT `{success: false, message: ...}`

- [ ] **409 Conflict (duplicate review)**
    - Submit two reviews for same course
    - Second should return: `{error: "..."}` with 409 status

- [ ] **429 Rate Limit**
    - Spam login endpoint (> 10 requests in 1 minute)
    - Verify response: `{error: "Too Many Attempts..."}` with 429 status

- [ ] **Validation Error (400)**
    - Submit empty login form
    - Verify response: `{error: "validation error message"}` with 400 status

---

## 5. API Endpoint Verification

### Backend Standardization Status ✅

All 25 controllers verified standardized:

| Category         | Endpoints                                                                        | Status          |
| ---------------- | -------------------------------------------------------------------------------- | --------------- |
| **Auth**         | login, register, logout, me, forgot-password, reset-password, check-availability | ✅ Standardized |
| **Courses**      | list, show, progress                                                             | ✅ Standardized |
| **Lessons**      | list, show, like, comments                                                       | ✅ Standardized |
| **Challenges**   | list, show, submit, leaderboard, stats                                           | ✅ Standardized |
| **Assignments**  | list, show, submit, courses-with-assignments                                     | ✅ Standardized |
| **Reviews**      | list, create, show, course reviews                                               | ✅ Standardized |
| **Community**    | posts, comments, like                                                            | ✅ Standardized |
| **Repositories** | list, show, like                                                                 | ✅ Standardized |
| **Profile**      | show, update, upload-avatar, preferences                                         | ✅ Standardized |
| **AI**           | general, challenges, projects                                                    | ✅ Standardized |
| **Admin**        | stats, courses, users, challenges, assignments, platforms, audit-logs            | ✅ Standardized |

### Response Format Verification

```
✅ List endpoints:
   Response: [item1, item2, ...] (direct array)
   NOT: {success: true, data: [...]}

✅ Create endpoints (POST):
   Response: {object} with 201 status
   OR: {success: true, id, ...}

✅ Error responses:
   Format: {error: "message"}
   Status: 400 (validation), 401 (auth), 403 (forbidden), 404 (not found), 409 (conflict), 429 (rate limit), 500 (server)

✅ Like endpoints:
   Format: {liked: boolean, count|likes|likesCount: number}

✅ Special: /api/auth/me
   Format: {user: {id, email, firstName, lastName, username, avatar, role}}
```

---

## 6. Environment Variables

### Laravel (.env)

```ini
# App Config
APP_NAME="Programming Academy"
APP_ENV=development             # Or "production" for prod
APP_DEBUG=false                 # NEVER true in production
APP_KEY=base64:...              # Run: php artisan key:generate
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=programming_academy
DB_USERNAME=root
DB_PASSWORD=                    # XAMPP default: empty

# Session & Security
SESSION_DRIVER=cookie           # SPA: use cookie
SESSION_SECURE_COOKIES=false    # true only on HTTPS production
SESSION_HTTP_ONLY=true          # Always true
SESSION_SAME_SITE=lax           # SPA: lax (or strict if no cross-site requests)

# CORS (allow React frontend)
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000

# Queue
QUEUE_CONNECTION=sync           # Dev: sync (no worker needed)

# Mail (dev: log driver; production: SMTP)
MAIL_MAILER=log                 # Dev: log to storage/logs/
MAIL_FROM_ADDRESS=noreply@academy.local

# AI (Ollama local)
OLLAMA_HOST=http://localhost:11434
OLLAMA_MODEL=qwen3-coder:480b-cloud
OLLAMA_VISION_MODEL=qwen3-vl:235b-cloud
```

### React (.env)

```ini
# API Configuration
VITE_API_URL=http://localhost:8000

# Build
VITE_BUILD_SOURCEMAP=false
```

---

## 7. File Upload Testing

### Avatar Upload Flow

```bash
# 1. Frontend: Form submission
POST /api/upload-avatar
Content-Type: multipart/form-data
[file binary]

# 2. Backend verification:
# - Validates MIME type (image/jpeg, image/png, image/gif, image/webp)
# - Maps MIME to extension
# - Saves to public/uploads/avatars/
# - Returns: {avatar: "http://localhost:8000/uploads/avatars/..."}

# 3. DB update: users.avatar_path = 'uploads/avatars/...'

# 4. Verify file exists:
ls public/uploads/avatars/
```

### Video Upload Flow (Admin)

```bash
# 1. Frontend: Form submission to create lesson
POST /api/admin/courses/{courseId}/lessons
Content-Type: multipart/form-data
Fields: title, video (file), description

# 2. Backend verification:
# - Validates video MIME (video/mp4, video/webm, etc.)
# - Saves to storage/app/videos/
# - Creates lesson record with video_path
# - Returns: {id, title, video_path, ...}

# 3. Verify file exists:
ls storage/app/videos/
```

---

## 8. Concurrent Operation Testing

### Challenge Scoring (Race Condition Test)

```bash
# Test: Two users submit solution to same challenge simultaneously

# 1. User A submits challenge
POST /api/challenges/1/submit (User A)
# Atomically: INSERT OR UPDATE user_challenges
# Uses: DB::raw('GREATEST(COALESCE(best_score,0), ?)')

# 2. User B submits challenge (same time)
POST /api/challenges/1/submit (User B)

# Expected: Both scores recorded independently
# Verify in DB:
SELECT * FROM user_challenges WHERE challenge_id=1;
# Should have 2 rows, one per user
```

### Assignment Grading (Admin)

```bash
# Test: Admin manually grades while auto-grade happens

# 1. User submits assignment
POST /api/assignments/1/submit

# 2. Admin grades simultaneously
POST /api/admin/assignments/1/grade (Admin)

# Expected: Last write wins (atomic updateOrInsert)
# Verify: Only one grade record per user+assignment
```

---

## 9. Testing Commands

### Laravel Backend Tests

```bash
cd c:\xampp\htdocs\programming-academy-laravel

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AiConversationTest.php

# Run with coverage
php artisan test --coverage

# Watch mode (re-run on file change)
php artisan test --watch
```

### React Frontend Tests

```bash
cd frontend\academy_clean\artifacts\academy

# Run unit tests
npm test

# Run E2E tests (if configured)
npm run test:e2e

# Build for production
npm run build

# Serve built version
npm run preview
```

---

## 10. Common Issues & Troubleshooting

| Issue                                 | Symptom                                              | Solution                                                                                            |
| ------------------------------------- | ---------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| **CORS Error**                        | Frontend can't reach backend API                     | Check `CORS_ALLOWED_ORIGINS` in Laravel `.env`, verify `credentials: 'include'` in React fetch      |
| **Session not persisting**            | Login works but next request shows 401               | Check `SESSION_DRIVER=cookie`, verify `Set-Cookie` header in login response                         |
| **Avatar not displaying**             | Avatar URL returns 404                               | Verify file exists in `public/uploads/avatars/`, check Laravel `APP_URL` in env                     |
| **Video not playing**                 | Video stream returns 404                             | Verify file in `storage/app/videos/`, check `VIDEO_STREAM_URL` config, verify Laravel can read file |
| **Rate limit too strict**             | Legitimate requests blocked                          | Adjust `throttle` values in `routes/api.php` (default: 10/min for login)                            |
| **AI responses not working**          | `POST /api/ai/*` returns 502                         | Verify Ollama running: `curl http://localhost:11434/api/tags`, check model name matches `.env`      |
| **Tests failing**                     | `php artisan test` shows red ✗                       | Check SQLite is writable, verify migrations ran: `php artisan migrate --env=testing`                |
| **Password reset emails not sending** | `POST /forgot-password` returns success but no email | Verify `MAIL_MAILER=log` in dev (emails logged to `storage/logs/laravel.log`)                       |

---

## 11. Production Deployment Checklist

### Pre-Deployment (1-2 hrs)

- [ ] **Environment Hardening**

    ```bash
    # Set in .env
    APP_ENV=production
    APP_DEBUG=false              # CRITICAL: never true
    SESSION_SECURE_COOKIES=true  # Enable HTTPS-only cookies
    ```

- [ ] **Database Backup**

    ```bash
    # Backup MariaDB before production
    mysqldump -u root programming_academy > backup_$(date +%Y%m%d).sql
    ```

- [ ] **CORS Configuration**

    ```ini
    # Set to production domains ONLY
    CORS_ALLOWED_ORIGINS=https://academy.example.com,https://www.academy.example.com
    ```

- [ ] **Asset Compilation**

    ```bash
    # Frontend
    cd frontend/academy_clean/artifacts/academy
    npm run build
    # Output: dist/ folder with minified assets
    ```

- [ ] **SSL/HTTPS Setup**
    - Install SSL certificate (Let's Encrypt recommended)
    - Enable HTTPS in web server config
    - Redirect HTTP → HTTPS

### Deployment (30 min)

- [ ] Copy Laravel code to production server
- [ ] Copy React `dist/` folder to web server document root
- [ ] Run Laravel migrations: `php artisan migrate --force`
- [ ] Run Laravel cache:
    ```bash
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    ```
- [ ] Start queue worker: `php artisan queue:work` (if using `database` queue)
- [ ] Verify endpoints work with `curl`:
    ```bash
    curl -i https://academy.example.com/api/courses
    ```

### Post-Deployment (Verify)

- [ ] [ ] Test login flow end-to-end
- [ ] [ ] Test file uploads (avatar, video)
- [ ] [ ] Test AI features
- [ ] [ ] Monitor logs: `tail -f storage/logs/laravel.log`
- [ ] [ ] Check database size & run ANALYZE
- [ ] [ ] Load test with Apache Bench: `ab -n 1000 -c 10 https://academy.example.com/`

---

## 12. Project File Structure

```
c:\xampp\htdocs\programming-academy-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/          (25 controllers — ALL standardized ✅)
│   │   ├── Middleware/           (AdminMiddleware, SecurityHeaders)
│   │   └── Requests/             (Form validation requests)
│   ├── Models/                   (17 models, all with proper $fillable/$hidden)
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/               (16 migrations, all with FK CASCADE)
│   ├── factories/
│   └── seeders/
├── public/
│   ├── index.php                 (Laravel entry point)
│   ├── uploads/                  (avatars, logos)
│   └── [JavaScript/CSS files]
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
├── routes/
│   ├── api.php                   (ALL 25+ controllers registered, rate limiting)
│   └── web.php
├── storage/
│   ├── app/videos/               (video uploads)
│   └── logs/
├── tests/
│   ├── Feature/                  (14 feature tests — all passing ✅)
│   └── Unit/
│
└── frontend/academy_clean/
    ├── artifacts/academy/
    │   ├── src/
    │   │   ├── contexts/auth-context.tsx  (Sanctum SPA auth)
    │   │   ├── pages/                     (SignInPage, ProfilePage, etc.)
    │   │   ├── components/                (React UI components)
    │   │   └── lib/                       (API client, utilities)
    │   ├── index.html
    │   ├── vite.config.ts
    │   └── package.json
    │
    └── lib/api-spec/
        └── openapi.yaml                  (API specification — SOURCE OF TRUTH)
```

---

## 13. API Response Format Reference

### Standard Patterns

#### List Endpoint (Array)

```json
GET /api/courses
→ [
    {id: 1, title: "...", category: "..."},
    {id: 2, title: "...", category: "..."}
  ]
```

#### Create Endpoint (Object + 201)

```json
POST /api/courses
→ 201 Created
  {id: 3, title: "...", category: "..."}
```

#### Auth Success (Object + success flag)

```json
POST /api/auth/login
→ 200 OK
  {success: true, id: 1, username: "...", email: "..."}
```

#### Fetch Current User (Nested object)

```json
GET /api/auth/me
→ 200 OK
  {user: {id: 1, username: "...", email: "...", role: "user"}}
```

#### Like Endpoint (Like + count)

```json
POST /api/lessons/1/like
→ 200 OK
  {liked: true, count: 42}
```

#### Error (error field + status code)

```json
GET /api/courses/999
→ 404 Not Found
  {error: "Course not found"}
```

#### Validation Error (error field + 400)

```json
POST /api/auth/login (empty password)
→ 400 Bad Request
  {error: "The password field is required."}
```

---

## 14. Next Steps (Prioritized)

### Immediate (Today)

1. ✅ **Backend changes COMPLETE** — AuthController updated, all tests passing
2. **Start React dev server** — `npm run dev` from `frontend/academy_clean/artifacts/academy/`
3. **Test Phase 1** — Authentication (register, login, logout, me endpoint)
4. **Document any issues** — Note broken endpoints, field mismatches, etc.

### Short-term (This week)

5. **Test Phase 2-4** — Courses, profiles, challenges, assignments
6. **Test Phase 5** — AI features (requires Ollama running)
7. **Fix any frontend/backend mismatches** — Adjust field names, response formats
8. **Test file uploads** — Avatar, video, logo uploads
9. **Test error scenarios** — 404, 409, rate limits, validation errors

### Medium-term (Next week)

10. **E2E testing** — Automated tests across both frontend and backend
11. **Performance testing** — Load tests, concurrent operations
12. **Security hardening** — HTTPS, CORS allowlist, rate limit tuning
13. **Deployment setup** — Production server, database, SSL, monitoring

### Long-term (Production)

14. **Staging environment** — Full replica for pre-production testing
15. **Monitoring & logging** — Error tracking, user analytics
16. **Backup strategy** — Database backups, disaster recovery plan
17. **Documentation** — API docs, deployment guide, runbooks

---

## 15. Contact & Support

### For Issues:

- **Backend errors**: Check `storage/logs/laravel.log`
- **Frontend errors**: Check browser DevTools console & Network tab
- **Database errors**: Check MySQL error log or SQLite file permissions
- **AI errors**: Verify Ollama running: `curl http://localhost:11434/api/tags`

### Quick Reference:

- **Laravel Docs**: https://laravel.com/docs/12.x
- **Sanctum SPA Docs**: https://laravel.com/docs/12.x/sanctum
- **React Docs**: https://react.dev
- **Vite Docs**: https://vitejs.dev

---

**Status:** Ready for Phase 1 testing. All backend changes complete, all 58 tests passing ✅
