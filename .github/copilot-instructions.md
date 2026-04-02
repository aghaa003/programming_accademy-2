# GitHub Copilot Instructions — Programming Academy

## Project Context File

This project has a living context file at `PROJECT_CONTEXT.md` in the root.

**At the START of every session, read `PROJECT_CONTEXT.md` before scanning any files.** This avoids re-scanning the whole codebase.

**After every implementation session, you MUST update `PROJECT_CONTEXT.md` to reflect:**
- Any new files created (add to Key Files table if significant)
- Any security/pending items completed (mark as ~~strikethrough~~ + ✅ Done)
- Any new pending items discovered (add to the Security — Pending table)
- Any architectural decisions made (add to Known Decisions / Notes)
- Any new migrations added (update the Migrations summary)
- Any new models or traits added (update Models with Traits table)
- The "Last updated" date at the top

**Do NOT wait to be asked.** Updating the context file is part of every implementation task.

---

## Fixes Log

All previously applied bug fixes are tracked in the Copilot repo memory file at:
`/memories/repo/fixes-applied.md`

**At the START of every session, read `fixes-applied.md` via the memory tool before diagnosing bugs.** It lists every bug that is ALREADY FIXED so you do not re-diagnose or re-fix them.

**After every bug fix session, update `fixes-applied.md` to reflect:**
- Any new bugs found and fixed (add a new numbered entry with: File, Bug, Why It's Wrong, Fix, Verified Against)
- Any files that are now confirmed clean (add to the "Controllers confirmed clean" list)
- The "Last updated" date at the top

If an entry in `fixes-applied.md` references a file or class that no longer exists (e.g. deleted middleware, renamed controller), mark it with `~~strikethrough~~ — FILE REMOVED` so future sessions don't look for it.

---

## Project Summary

- **Laravel 12.x** API backend, MariaDB (prod) / SQLite (tests), Sanctum SPA auth
- **Frontend**: currently vanilla HTML/JS in `public/` — planned React migration
- **Queue**: `sync` in dev, `database` in production
- **Mail**: `log` driver in dev, SMTP in production
- **Tests**: PHPUnit, SQLite `:memory:`, always run `php artisan test` after changes
- **Context file**: `PROJECT_CONTEXT.md` — read this at the start of any session before scanning files

---

## Standing Rules

- Always run `php artisan test` after any code change and confirm tests pass before finishing
- Never store secrets in code — use `.env` only
- When adding migrations, check existing migration timestamps and name new ones accordingly
- The frontend is vanilla HTML — do not suggest Blade unless specifically asked
- `QUEUE_CONNECTION=sync` is intentional for dev (no worker needed)
- `MAIL_MAILER=log` is intentional for dev
- Never set `APP_DEBUG=true` for production guidance
