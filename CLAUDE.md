# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Thesis Inventory System — a web-based catalog of thesis **metadata only** (no full PDFs stored). Three user classes: Administrator (manages department accounts + activity log), Department (CRUD on their own thesis records, with OCR camera capture), Viewer (public, no login, browse + search across all records). See `Thesis_Inventory_System_SRS.pdf` for the full SRS, which is the source of truth for scope.

## Stack

- **Backend:** Laravel 13.8 on PHP 8.3, Eloquent ORM
- **Database:** MySQL, hosted on Railway (the deployment target). The SRS names Supabase/PostgreSQL, but deployment has been switched to MySQL on Railway — MySQL is the source of truth for schema decisions. Note that `.env.example` ships with `DB_CONNECTION=sqlite` from the Laravel skeleton; switch to `mysql` with Railway credentials when configuring `.env`. Keep migrations portable MySQL (avoid Postgres-only column types/functions); for full-text search use MySQL `FULLTEXT` indexes or `LIKE`, not Postgres `tsvector`.
- **Frontend:** Blade templates (server-rendered) + Alpine.js for light interactivity + Tailwind CSS v4 (via `@tailwindcss/vite`)
- **OCR:** Tesseract.js (browser-side) for capturing abstract + recommendations from printed pages
- **RBAC:** `spatie/laravel-permission` (already required)
- **Activity logging:** `spatie/laravel-activitylog` (already required) — required by SRS for account creation and thesis CRUD events, admin-viewable only

## Commands

```bash
composer setup        # install deps, copy .env, key:generate, migrate, npm install, npm run build
composer dev          # concurrent: php artisan serve + queue:listen + pail (logs) + vite
composer test         # config:clear then php artisan test (PHPUnit 12)
php artisan test --filter=SomeTest   # run a single test
./vendor/bin/pint     # format PHP (Laravel Pint)
npm run dev           # vite dev server only
npm run build         # production asset build
```

## Architecture

The codebase is a fresh Laravel 13 skeleton with role-segmented scaffolding already in place — most directories are empty and waiting to be filled:

- **`app/Http/Controllers/Admin/`** and **`app/Http/Controllers/User/`** — empty folders signaling the planned admin-vs-department controller split. The User namespace is for Department-role controllers (thesis CRUD, OCR capture). Admin holds department-account management and activity-log views.
- **`resources/views/admin/{activity-log,departments}/`** — admin dashboard views
- **`resources/views/user/{department,thesis}/`** — department dashboard + thesis CRUD forms
- **`resources/views/public/thesis/`** — public viewer browse/search/detail pages (no auth)
- **`routes/web.php`** — currently only the welcome route; role-grouped routes will be added here using `spatie/laravel-permission` middleware

### Authorization model

Three roles map directly to the SRS user classes: `administrator`, `department`, and implicit `viewer` (unauthenticated). Department users are scoped to their own thesis records — every Thesis Record carries an owning-department FK, and policies/middleware must enforce that a department cannot read or write another department's records through any management endpoint (SRS FR-3.4, FR-3.6, NFR-1.3). The activity log is admin-only viewable (FR-7.4).

### Scope constraints worth remembering

- The system stores **descriptive metadata only**. There is no file-upload field for the thesis PDF (SRS FR-4.4). Do not add one.
- Multi-value fields (authors, advisers, panelists, keywords) render as add/remove input boxes on the frontend and should be stored in a way that preserves order — either JSON columns or related tables.
- OCR output must always be reviewable and editable before save (FR-5.3, NFR-3.3) — never auto-commit OCR text.
- When an admin deletes a department account, the system must prompt to keep or delete that department's records (FR-2.3). Implement this as an explicit choice, not a cascade default.

## Coding standards

Pint enforces formatting and Larastan enforces types — this section is only for decisions tools *can't* make for us. If a rule is about spacing, naming case, or import order, it doesn't belong here.

1. **Thin controllers, because logic must be testable without HTTP.** A controller validates via a Form Request, calls one Action, and returns a view/redirect. If you're writing an `if` about *business* rules in a controller, it's in the wrong place.
2. **Business logic lives in Action classes (`app/Actions`), one job per class.** Named after the job (`DeleteDepartmentAction`, `CaptureThesisOcrAction`). This is what makes logic reusable across web/console/test and keeps controllers honest.
3. **All input validation goes in Form Request classes — never inline, never in Actions.** One place to find "what's a valid thesis record," and it's unit-testable on its own.
4. **Authorization is Policies + spatie permissions only — never `if ($user->role === ...)` in a controller or Blade.** The department-can-only-touch-its-own-records rule (FR-3.4/3.6) lives in one tested policy method, so we can't accidentally leak another department's data through a forgotten endpoint.
5. **Query/search logic lives in one place (a `ThesisRepository` or query scope), not copy-pasted across controllers.** Viewer search, department list, and admin views will all want the same filters — duplicating them is how search silently diverges.
6. **Scope guardrails (from the SRS — these are bugs if violated):**
   - Metadata only. No PDF file-upload field, ever (FR-4.4).
   - OCR output is *always* shown for review/edit before save — never auto-committed (FR-5.3).
   - Deleting a department account prompts keep-or-delete its records — an explicit choice, not a cascade default (FR-2.3).
7. **Multi-value fields (authors, advisers, panelists, keywords) are stored as ordered related tables (a row per value with an order column), not JSON.** Order matters (author order is meaningful), and related rows keep them searchable — the viewer filters by author and adviser, so they can't be locked inside a JSON blob.
8. **UI is Blade components + Tailwind `@theme` tokens — no copy-pasted markup, no inline hex.** Define a color/font once in `resources/css/app.css` (brand colors as `--color-*`, font Source Sans 3); reuse `x-card`, `x-btn`, etc. So a rebrand is one file, not forty.
9. **Role-scoped endpoints get a feature test proving the boundary holds** (e.g. department A gets 403 touching department B's thesis). A scoping rule is just a hope until a test enforces it.

Build incrementally: one feature/screen per change, then stop for review. Match existing patterns before adding new ones. Quality gates before a task is "done": `./vendor/bin/pint` and `./vendor/bin/phpstan analyse` (add Larastan).
