# Handoff: AdZU Thesis Archives — Thesis Inventory System

## Overview
A web app for cataloging thesis **records** (descriptive metadata only — no PDF/file storage). It serves three roles:

- **Viewer** — public, no login. Browses and searches all theses, read-only.
- **Department** — logs in. Creates/edits/deletes ONLY its own thesis records; can capture text from a printed copy via camera (OCR) into the Abstract/Recommendations fields.
- **Admin** — logs in. Manages department accounts and views a system-wide activity log.

Each thesis record has: **title, authors (multiple), year, program/department, abstract, recommendations, adviser (multiple), panelists (multiple), keywords (multiple)**.

## About the Design Files
The files in this bundle are **design references created in HTML/React (via in-browser Babel)** — interactive prototypes that demonstrate the intended look, layout, and behavior. **They are not production code to copy directly.**

The task is to **recreate these designs in your target codebase** using its established environment and patterns (e.g. a real React/Next.js + bundler setup, Vue, etc.). If no frontend environment exists yet, choose the most appropriate framework for the project and implement the designs there. Treat the HTML/JSX here as a precise visual + behavioral spec, not as files to ship.

## Fidelity
**High-fidelity (hifi).** Final colors, typography, spacing, components, and interactions are all specified. Recreate the UI pixel-accurately using your codebase's component library and patterns. The exact design tokens are listed at the bottom of this document and defined in `styles.css`.

---

## Layout Systems

There are two shells. Pick the shell per route based on role.

### 1. Admin shell (logged-in: Department + Admin)
- **Top navbar** (navy `#02327C`), full width, `60px` tall, sticky. Left: square gold logo mark + wordmark "**AdZU** Thesis Archives" (one line, no wrap). Right: "Sign out" link + user chip (round avatar with initials + name/role, name truncates with ellipsis at `200px`). On mobile (`≤860px`) a hamburger menu button appears at the far left.
- **Left sidebar** (dark slate `#1B2227`), `248px` wide. Holds an uppercase section label + menu items. Active item is a **gold pill** (`#FFBB1C` bg, `#333` text). Hover = slightly lighter slate. A footer shows "Signed in as <name>". On mobile the sidebar is a fixed off-canvas drawer (slides in from left, dim backdrop).
- **Content area** (light gray `#ECEFF4`), padded `28px 32px 64px` (mobile `20px 16px 60px`), inner `.content-wrap` capped at `1160px` centered. Cards are white.

### 2. Public shell (Viewer)
- **Top navbar only** (same navy navbar). Right side shows "Browse" link + a gold "Sign in" button. **No sidebar.**
- Content sits directly on the light gray page; the Browse screen opens with a navy hero band.

---

## Screens / Views

### 1. Login  *(full-bleed, no shell)*
- **Purpose:** Department/Admin sign-in.
- **Layout:** Full-viewport navy (`#02327C`) background with a soft cyan radial glow at top-center (`radial-gradient(1100px 500px at 50% -10%, rgba(0,192,239,.22), transparent 60%)`). Centered column, `max-width: 410px`.
- **Components (top → bottom):**
  - Gold logo mark `52×52`, radius `9px`, holding a white book glyph.
  - Wordmark "**AdZU** Thesis Archives" — `27px / 700`, "Thesis Archives" in dimmed navy (`#b9c6e0`, weight 400).
  - Subtitle "Department & Administrator sign-in" — `13.5px`, color `#b9c6e0`.
  - White card (radius `12px`, shadow) containing a form: **Email or username** text input, **Password** input, a right-aligned "Forgot password?" link (navy, nowrap), a full-width gold **Login** button, an "OR" divider, and a full-width white **Sign in with Google** button (with multicolor Google "G" SVG).
  - Below the card: white "← Browse theses as a visitor" link, then a hint line ("any email with 'admin' signs in as Administrator") — *this hint is prototype scaffolding; remove in production.*
- **Behavior:** Submitting with empty fields shows inline error "Enter your email and password to continue." On success, route by role: email containing "admin" → Admin; otherwise → Department. Google button → Department (demo).

### 2. Public Browse / Search  *(public shell)*
- **Purpose:** Browse and search the whole archive.
- **Layout:**
  - **Hero band** (navy gradient `linear-gradient(180deg,#02327C,#022a68)` + cyan radial glow), padded `46px 24px 40px`. Centered: H1 "Search the thesis archive" (`33px / 700`), a sub line ("Browse N catalogued theses…", `16px`, `#b9c6e0`), then a large white search input (`max-width: 620px`, radius `10px`, leading search icon, `16px` text) — searches title, authors, abstract, keywords (case-insensitive substring).
  - **Filter bar** (white card, flex, `gap: 14px`, wraps): a "Filters" label with funnel icon; a **Year range** control = two `select`s ("From" – "To") separated by an en-dash, each optional; **Program / Department** select; **Keyword** select; a "Clear" ghost button (only when any filter active); and a right-aligned "N results" count.
    - Year filtering: keep records where `(!from || year >= from) && (!to || year <= to)`. If `from > to`, show inline error "From year can't be after To year." and mark both selects invalid.
  - **Results grid:** `repeat(auto-fill, minmax(320px, 1fr))`, `gap: 18px`. Each is a clickable **thesis card** (see Components).
  - **Empty state** (when 0 results): centered card with a dashed-circle search icon, "No theses match your search", helper copy, and a "Clear all filters" button.

### 3. Thesis Detail  *(viewer; also rendered inline inside the admin shell as `DetailInline`)*
- **Purpose:** Read a full record.
- **Layout:** Centered `max-width: 900px`. A "← Back to results" ghost button, then one white card with a `6px` cyan top stripe, padded `30px 34px`.
  - Meta row: cyan year badge, program text, a dot separator, department text.
  - **Title** — `30px / 700`, navy, `line-height 1.2`, `text-wrap: pretty`.
  - "Authors" eyebrow + author chips.
  - **Abstract** section (cyan-underlined `.sec-label` eyebrow + `16px / 1.65` body).
  - **Recommendations** section (same pattern).
  - A definition list: Adviser (chips), Panelists (chips), Keywords (cyan chips), Program (text). Each row is a `180px / 1fr` grid separated by hairline borders.

### 4. Department Dashboard  *(admin shell)*
- **Purpose:** The department manages its OWN theses.
- **Layout:** PageHead "My theses" + sub "<Dept> — records owned by your department" + a gold **"Add Thesis"** button (plus icon).
  - **Stat row:** three stat cards — Total records, Latest year (gold value), Last updated (green value).
  - **Records card:** card head with title "Thesis records" + a `280px` search input ("Search my theses…", filters title+authors). Then a **table**: columns **Title** (navy, clickable → detail), **Year** (tabular), **Authors** (muted), **Last updated** (tabular muted), **Actions** (right-aligned: edit icon-button + danger trash icon-button).
  - Empty state when no records (or no match): book icon, heading, copy, and an "Add Thesis" button when not searching.

### 5. Add / Edit Thesis Form  *(admin shell)*
- **Purpose:** Create or edit a record. Same component for both (titled "Add thesis" / "Edit thesis").
- **Layout:** Centered `max-width: 900px`. PageHead + a white card (`padding: 26px 28px`) with a `22px`-gap column of fields:
  - **Title** — required, textarea (`min-height 64px`).
  - Two-column grid: **Year** (required) + **Program / Department** (required select from program list).
    - **Year is an `<input type="number">` with `min=1900`, `max=<current year>`, `step=1`.** Validation: must be an integer within `[1900, currentYear]`, else error "Year must be between 1900 and <max>." Hint under the field reads "Between 1900 and <max>." (`max` is computed as `new Date().getFullYear()`).
  - **Authors** — repeatable list (see Components), numbered, "Add another".
  - **Abstract** — required textarea (`min-height 130px`) with a **"Scan from printed copy"** button (camera icon) absolutely positioned at the field's top-right.
  - **Recommendations** — optional textarea (`min-height 110px`) with its own **"Scan from printed copy"** button.
  - Hairline divider.
  - **Adviser** — repeatable, numbered. **Panelists** — repeatable, numbered. **Keywords** — repeatable, NOT numbered.
  - Footer row (right-aligned): "Cancel" (secondary) + "Save thesis"/"Save changes" (gold, check icon).
- **Validation:** title, year (with bounds above), program, and abstract are required; errors render inline beneath each field and mark the control `.invalid`. Empty repeatable rows are stripped on save.

### 6. OCR Capture & Review  *(modal over the form)*
- **Purpose:** Photograph/upload a printed page, extract text, **review/correct it before** it fills the Abstract or Recommendations field.
- **Behavior / stages:**
  1. **Capture** — info banner + two big dashed-border cards: "Take a photo" (camera icon) and "Upload an image" (upload icon). Footer: "Cancel".
  2. **Processing** — a spinner ("Extracting text…") for ~1.2s (simulated; in production this is the real OCR call).
  3. **Review** — a thumbnail placeholder of the captured page beside a `.sec-label` "Extracted text — review & correct" + helper copy, then an **editable textarea** pre-filled with the extracted text. Footer: "← Retake" (back to capture) + gold "Use this text ✓".
- **Critical wiring:** "Use this text" must write the (edited) textarea value into the correct target field (`abstract` or `recommendations`) of the form's live state, then close the modal and show a toast "Text added to Abstract/Recommendations." In the prototype this is done by passing a `scanApply = {field, text, nonce}` object into the form, which a `useEffect` merges into the controlled field — do the equivalent in your state model. Nothing is committed to the field until the user clicks "Use this text".
- Title is dynamic: "Scan to Abstract" / "Scan to Recommendations".

### 7. Admin — Department Accounts  *(admin shell)*
- **Purpose:** Admin manages department logins.
- **Layout:** PageHead "Department accounts" + gold **"Create department account"** button. A records card with head ("N accounts" + search box). **Table** columns: **Department**, **Username / email**, **Status** (green dotted "Active" badge / gray dotted "Inactive" badge), **Records** (count, tabular), **Created** (date), **Actions** (right-aligned: "Edit" secondary button, an activate/deactivate icon-button [lock when active, check when inactive], danger trash icon-button).

### 8. Delete Department Account — Confirmation  *(modal)*
- **Purpose:** Decide what happens to a department's records when deleting its account.
- **Layout:** Modal titled "Delete department account" (trash icon). Body: a sentence naming the department + username; a **warning banner** ("This department owns N thesis records. Choose what happens to them."); then two explainer blocks — "**Keep the records**" (records stay but become unowned) and "**Delete the records too**" (destructive, in the coral/danger box). Footer buttons: "Cancel" (secondary), "Keep the records" (navy), and "Delete the records too" (danger/red, trash icon).

### 9. Admin — Activity Log  *(admin shell, its own screen)*
- **Purpose:** System audit trail.
- **Layout:** PageHead "Activity log" + sub. A filter card: a flex-1 search box ("Search actor or affected record…"), an **Action type** select (All / created / edited / deleted / deactivated), and a **Date** select (All / month options). Then a **table**: columns **Actor** (name + role sub-line), **Action** (color-coded badge by action + the entity type "thesis"/"account"), **Affected record**, **Timestamp** (tabular). Action badge tones: created→green, edited→cyan, deleted→red, deactivated→gold. Empty state when filters match nothing.

---

## Components (shared design system)

> Recreate these as real components in your stack. All borders/dividers use `#dfe4ec` unless noted; all shadows are the soft set in Design Tokens.

- **Button** — variants: `primary` (gold `#FFBB1C` bg / `#333` text / weight 700), `secondary` (white / `#333` / `#cdd4df` border), `navy` (`#02327C` / white), `danger` (`#C0392B` / white), `ghost` (transparent / muted, navy on hover). Padding `10px 18px`, radius `8px`, `font 14.5px/600`, `gap 8px` for leading/trailing icon. `sm` size = `7px 12px / 13px / radius 5px`. Active state nudges `translateY(1px)`. Also **icon-button**: `34×34`, white, `1px` border, radius `5px`; `.danger` variant turns red on hover.
- **Card** — white, `1px #dfe4ec` border, radius `12px`, soft shadow. `.card-head` = `17px 22px` with bottom border; `.card-pad` = `22px 24px`.
- **Chip** (pill) — `5px 12px`, radius `999px`, `13px/600`, `1px` border. `person` variant = `#eef1f6` bg / `#41495a` text. `key` (keyword) variant = cyan tint `#e3f7fd` bg / `#0a6b85` text / `#bdeaf6` border. Chip rows are `flex-wrap` with `gap 8px`.
- **Badge** — `4px 10px`, radius `999px`, `12px/700`, optional leading 7px dot. Tones: `green` (`#e0f1ea`/`#147A52`), `gray` (`#eceef2`/`#6b727c`), `cyan` (`#e3f7fd`/`#0a6b85`), `gold` (`#fff3d4`/`#9a6b00`), `red` (`#fbe9e7`/`#C0392B`).
- **Table** (`.tbl`) — full width, collapsed borders, `14px`. Header cells: `11.5px/700`, uppercase, tracked `.06em`, muted `#8a929c`, `#f6f8fb` bg, bottom border. Body cells `14px 16px`, hairline row borders, row hover `#f4f8ff`. Title cells navy + underline on hover. Numeric cells tabular.
- **Input / Textarea / Select** — bg **input tint `#E8F0FD`**, `1px #cfdcf3` border, radius `8px`, `10px 13px`, `14.5px`. Focus: navy border + `3px rgba(2,50,124,.12)` ring + white bg. `.invalid`: red border + red ring. Select uses a custom inline-SVG chevron. Field label `13px/600`; required marker is a red `*`; hint `12.5px` muted; error `12.5px/600` red.
- **Repeatable row** (`.rep-row`) — `#f6f8fb` row, `1px` border, radius `8px`. Contains: a grip/drag handle (6-dot icon, `cursor: grab`), an optional circular number badge, a white input, and a danger "×" remove icon-button (disabled when only one row remains). An "Add another" secondary-sm button sits below.
- **Modal** — dim overlay `rgba(12,20,38,.55)` (fade-in `.18s`), centered white panel (radius `12px`, large shadow, slide+scale-in `.22s`) with `.modal-head` (icon + title + × close), scrollable `.modal-body` (`22px`), and `.modal-foot` (`#f6f8fb`, right-aligned actions). Closes on overlay click, the ×, or Escape.
- **Banner** — cyan info (`#e3f7fd` bg, `#bdeaf6` border, `4px` cyan left border, `#0a5f76` text) and a `warn` variant (coral/danger tints).
- **Toast** — fixed bottom-center, slate pill, white text, green check icon, auto-dismiss ~2.4s, slide-up-in.
- **Empty state** — centered: `76px` dashed-circle icon holder, `19px/600` heading, muted helper paragraph (`max-width 360px`), optional action button.
- **PageHead** — H1 `27px/700` with a `46×3px` cyan underline accent (`::after`), optional muted sub line, optional right-aligned actions.
- **Section label** (`.sec-label`) — `12px/700` uppercase navy eyebrow with a `2px` cyan bottom border.

## Iconography
The prototype uses small inline-SVG line icons (`24×24`, `currentColor`, `stroke-width 2`, round caps/joins): search, book, grid, plus, edit (pencil), trash, users, activity, camera, back (arrow-left), logout, lock, menu, grip (6 dots), x, upload, check, calendar, tag, filter (funnel), chart. The Google "G" is a 4-color brand SVG. **In production, substitute your icon library (e.g. Lucide — names map almost 1:1) at the same 2px-stroke line-art weight.** No icon font is used. No emoji.

---

## Interactions & Behavior
- **Navigation:** Clicking a thesis (card or table title) → Thesis Detail. "Add Thesis" / sidebar "Add thesis" → blank form. Row edit → form pre-filled. Sidebar items switch admin screens; the active item is the gold pill.
- **Role/auth routing:** Viewer = public shell; Department/Admin = admin shell with role-specific sidebar menu (Department: My theses, Add thesis · Admin: Department accounts, Activity log). Sign out → back to public Browse.
- **Search & filters:** all client-side, case-insensitive substring; year range as described; results count updates live; "Clear" resets filters.
- **Forms:** inline validation on submit; `.invalid` styling; repeatable add/remove/(drag-reorder handle shown); empty rows stripped on save; success toast.
- **OCR:** capture → processing (~1.2s simulated) → editable review → "Use this text" writes into the target field; "Retake" returns to capture; cancel/Escape closes without writing.
- **Destructive actions:** thesis delete and account delete both confirm via modal; account delete offers keep-vs-delete-records.
- **Animations (CSS only):** overlay fade `.18s`, modal slide+scale `.22s`, toast slide-up `.22s`, button press `translateY(1px)`, spinner `.8s linear` loop. Respect `prefers-reduced-motion`.
- **Responsive:** `≤860px` collapses the sidebar into an off-canvas drawer (hamburger toggles it, backdrop closes it), stacks two-column grids to one, and hides the navbar user name text. Results grid reflows via `auto-fill minmax(320px,1fr)`.

## State Management
State the prototype tracks (model equivalently, ideally server-backed):
- `role` (`viewer` | `department` | `admin`) and current `screen`/route.
- `selected` thesis (detail view), `editing` thesis (form; `null` = new).
- `scanField` (which field the OCR modal targets) and `scanApply` (`{field, text, nonce}` applied into the form on "Use this text").
- Modal flags: delete-thesis target, delete-account target, create-account open.
- `toast` message (auto-clears).
- Collections: `theses` (with `owner`/`department`), `accounts`, `activity` log. CRUD on theses updates the list + `updated` date; create/delete account updates `accounts`; both should append to the activity log in a real backend.
- Department screens are scoped to the logged-in department's own records.

## Data Fetching (production guidance)
- Public browse/detail: read-only fetch of all published records.
- Department: scoped CRUD on its own records (enforce ownership server-side, not just in the UI).
- Admin: account CRUD + read of the activity log; activity entries should be written server-side on every mutating action (actor, action, entity type, affected record, timestamp).
- OCR: replace the simulated delay with a real OCR endpoint (image upload → extracted text). The review-before-apply step is a hard requirement.

---

## Design Tokens
(Source of truth: `styles.css`. Font: **Source Sans 3** — weights 400/500/600/700, plus italic 400.)

**Brand colors**
- Navy (navbar): `#02327C` · deep `#022a68`
- Slate (sidebar): `#1B2227` · alt `#232c33` · hover `#2b353d`
- Gold (primary/active): `#FFBB1C` · dark `#e7a800` · text-on-gold `#333333`
- Cyan (accents/underlines/banners): `#00C0EF` · soft `#e3f7fd`
- Green (success/status): `#147A52` · soft `#e0f1ea`
- Danger (destructive): `#C0392B` · soft `#fbe9e7` · dark `#a52f23`

**Surfaces & text**
- Page bg `#ECEFF4` · surface white `#FFFFFF` · surface-alt `#f6f8fb` · **input tint `#E8F0FD`**
- Text `#333333` · secondary `#5a626b` · tertiary `#8a929c`
- On-navy `#fff` / dim `#b9c6e0` · on-slate `#d4dadf` / dim `#8b949c`
- Lines `#dfe4ec` · stronger `#cdd4df`

**Radius:** sm `5px` · md `8px` · lg `12px` · pill `999px`
**Shadows (soft, layered):**
- sm `0 1px 2px rgba(16,32,64,.06), 0 1px 3px rgba(16,32,64,.08)`
- md `0 2px 6px rgba(16,32,64,.08), 0 4px 14px rgba(16,32,64,.06)`
- lg `0 10px 30px rgba(16,32,64,.16), 0 4px 10px rgba(16,32,64,.10)`

**Type scale (px):** display/H1 `33` · page title `27` · detail title `30` · card/section `16–18` · body `14–16` · label `13` · meta/eyebrow `11.5–12` (uppercase, tracked `.05–.09em`). Line-heights: body `1.5–1.65`, tight headings `1.2`.

**Layout constants:** navbar height `60px` · sidebar width `248px` · content max-width `1160px` · mobile breakpoint `860px` · results card min `320px`.

## Assets
- **No external image assets.** The logo mark is a CSS/SVG lockup (gold rounded square + white book glyph) — replace with the official AdZU logo in production. Icons are inline SVG (see Iconography). The Google "G" is an inline multicolor SVG. The OCR "captured page" is a placeholder tile (no real image).
- **Font:** Source Sans 3 loaded from Google Fonts in the prototype; bundle/self-host in production as your project prefers.

## Files (in this bundle)
- `Thesis Inventory.html` — entry point; loads fonts, `styles.css`, and the scripts below.
- `styles.css` — all design tokens + component styles (the authoritative token source).
- `data.js` — sample theses, accounts, activity log, program list, OCR sample text.
- `components.jsx` — shared components + icon set (Icon, Btn, Chip, Badge, Field, Navbar, Sidebar, Modal, Toast, PageHead, Brand).
- `screens-public.jsx` — Login, Browse/Search, Thesis Detail, ThesisCard.
- `screens-admin.jsx` — Department Dashboard, Department Accounts, Activity Log, Delete-account modal.
- `screens-form.jsx` — Add/Edit form, repeatable-list editor, OCR modal, create-account modal, delete-thesis modal.
- `app.jsx` — root: routing, roles, shell selection, modal orchestration, prototype navigator.

> To run the prototype locally: serve the folder over a static server and open `Thesis Inventory.html` (the JSX is transpiled in-browser via Babel — fine for reference, not for production). The "Screens" pill (bottom-right) is a prototype navigator for jumping between roles/screens; it is **not** part of the product — omit it when implementing.
