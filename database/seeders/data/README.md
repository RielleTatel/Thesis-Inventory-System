# Thesis seed assets

Seed data + approval-page images for the ADZU Thesis Inventory System handover.
Running `php artisan migrate --seed` should give the school a fully populated,
browsable catalog of 12 Computer Science Department theses (AY 2025-2026).

## Contents
- `thesis-data.json` - the 12 thesis records (title, program, year, authors,
  advisers, panelists, keywords, abstract, recommendations, approval image ref).
  An optional `proofreaders` array may be added per record (missing/empty = none).
- `approval-images/T1.jpg ... T12.jpg` - the scanned panel approval pages.

## How the seeder uses this
1. Resolve the existing `Computer Science` department row (created by the
   existing DepartmentSeeder). Match by name or code - confirm which.
2. For each record in `thesis-data.json`:
   - insert the `theses` row (department_id, title, program, year, abstract,
     recommendations, status = 'published'), then
   - insert its authors / advisers / panelists / keywords into the child tables
     (each with an incrementing `position` starting at 1), then
   - copy `approval-images/{approval_image}` onto the private `local` disk using
     the same path pattern the OCR scanner writes to, and set `approval_page_path`.
3. The image copy must be graceful: if the copy fails, log a warning and
   continue, do not abort the seed.

## IMPORTANT - status
Every record MUST be seeded with `status = 'published'`. The column defaults to
`draft` (department-only), so seeding without setting it leaves the public
viewer empty on a fresh deploy.

## IMPORTANT - real signatures
The committed `approval-images/*.jpg` are the REAL signed approval pages. If this
repo is ever made public, replace them with placeholders (keep the T1..T12
filenames) and load the real images only at school deployment. Or have the
seeder skip the image copy when `APP_ENV=production` is not set as intended.

## Known gaps
- T5's recommendation is condensed and is missing source scan page 182.
- Keywords for T6, T7, T11 are from the papers; the rest are derived - edit freely.
