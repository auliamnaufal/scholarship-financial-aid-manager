# Scholarship & Financial Aid Manager MVP — Design Spec

Date: 2026-09-07
Status: Approved by user, ready for implementation planning

## Purpose

Database class assignment: a working MVP web app on top of an
already-designed relational schema (ERD). Priority is correctness of
the data model and role-based flows over UI polish.

## Tech stack

- Laravel 11 (latest stable)
- SQLite for local dev (`database/database.sqlite`); README documents
  the `.env` `DB_CONNECTION`/`DB_DATABASE` swap to MySQL
- Laravel Breeze, Blade + Tailwind stack (no React/Vue)
- `spatie/laravel-permission` for roles (many-to-many `model_has_roles`,
  not separate user tables)
- Eloquent ORM, migrations, factories, seeders only — no raw SQL

## Roles

Every authenticated user is a `User`. Roles: `student`, `reviewer`,
`coordinator`, assigned via Spatie's roles table so a user can hold
multiple simultaneously (e.g. `reviewer` + `coordinator`, common for
staff).

**Constraint:** a user must not hold `student` at the same time as
`reviewer` or `coordinator`. Enforced via a form request validation
rule on the role-assignment action (not a DB constraint).

**Post-login routing:** a user with more than one role (or whose role
set doesn't map to exactly one dashboard) lands on a small role picker
page listing the dashboards they can access; single-role users go
straight to their dashboard.

## Data model

Matches the given ERD exactly — no added/renamed columns. All FKs use
`constrained()->cascadeOnDelete()` unless noted.

- **users** (Breeze default, unmodified)
- **student_profiles**: `user_id` FK→users (unique), `gpa` decimal(3,2),
  `year_enrolled` year
- **guardian_phones**: `id`, `user_id` FK→users, `phone_number` string
- **programs**: `id`, `name`, `type` enum(`need_based`,`merit_based`),
  `funding_source`, `budget` decimal(12,2), `application_deadline`
  date, `max_family_income` decimal(12,2) nullable, `min_gpa`
  decimal(3,2) nullable, `coordinator_id` FK→users, `description` text
  nullable
- **applications**: `id`, `student_id` FK→users, `program_id`
  FK→programs, `semester` string, `submission_date` date, `status`
  enum(`submitted`,`under_review`,`approved`,`rejected`), unique
  (`student_id`,`program_id`,`semester`)
- **reviews**: `id`, `reviewer_id` FK→users, `application_id`
  FK→applications, `score` integer, `comments` text nullable, unique
  (`reviewer_id`,`application_id`)
- **disbursements**: `id`, `application_id` FK→applications, `seq_no`
  integer, `amount` decimal(12,2), `disbursement_date` date,
  `semester` string, unique (`application_id`,`seq_no`)

### Eloquent relationships (every model)

- `User`: `hasOne(StudentProfile)`, `hasMany(GuardianPhone)`,
  `hasMany(Program, 'coordinator_id')`, `hasMany(Application,
  'student_id')`, `hasMany(Review, 'reviewer_id')`,
  `belongsToMany(Role)` (via Spatie trait)
- `StudentProfile`: `belongsTo(User)`
- `GuardianPhone`: `belongsTo(User)`
- `Program`: `belongsTo(User, 'coordinator_id')`, `hasMany(Application)`
- `Application`: `belongsTo(User, 'student_id')`, `belongsTo(Program)`,
  `hasMany(Review)`, `hasMany(Disbursement)`
- `Review`: `belongsTo(User, 'reviewer_id')`, `belongsTo(Application)`
- `Disbursement`: `belongsTo(Application)`

Type/enum columns implemented as PHP native backed enums
(`ProgramType`, `ApplicationStatus`) cast via `$casts`, not raw
strings, so Blade and controllers get autocompletion and validation
for free.

## Status workflow (design decision)

The schema has no explicit "who moves submitted → under_review"
field, so this is defined behaviorally:

1. Student submits → `status = submitted`.
2. Any user with the `reviewer` role can claim/self-assign an
   application from the open pool (a "Claim" button on a listing of
   `submitted` applications). Claiming is its own POST action
   (`reviewer.applications.claim`) that flips
   `application.status` from `submitted` to `under_review` and then
   redirects the reviewer to the scoring form for that application
   (no separate assignment table — see "known simplification" below).
   Submitting the review itself (`reviewer.reviews.store`) is a
   separate, later request. Only reviewers can trigger the claim
   transition, and only from `submitted`.
3. Coordinator can approve/reject an `under_review` application only
   once at least one `Review` row exists for it, and only for
   programs where `coordinator_id` is their own user id.
4. Disbursements can only be recorded against `approved` applications
   under the coordinator's own programs.

**Known simplification (documented in README):** there is no formal
review-assignment table. Any reviewer can claim any `under_review`-
eligible (i.e. currently `submitted`) application. Once claimed, the
application is `under_review` and other reviewers may still add
their own review (the unique constraint is per reviewer, not
exclusive-lock), matching "reviewer dashboard shows under_review
apps this reviewer hasn't reviewed yet."

## Review comment visibility (design decision)

Reviews have no `released`/visibility column in the schema. Visibility
to the student is computed, not stored: a student can see review
`comments` (and `score`) for their own application only when
`application.status` is `approved` or `rejected` (i.e., the
coordinator has made a final decision). While `submitted` or
`under_review`, the student sees status only, no review content. No
migration changes needed — implemented as a helper/accessor, e.g.
`Application::reviewsVisibleToStudent(): bool`.

## Authorization (Policies)

One Laravel Policy per model, registered in `AuthServiceProvider`,
used via `$this->authorize()` in controllers — no ad-hoc `if` checks:

- `ApplicationPolicy`: `view`/`update` → owner student AND
  `status === submitted` (for update); coordinators can `view` if the
  application's program belongs to them; reviewers can `view` if
  `under_review` or already reviewed by them. `create` → student role
  only. `approve`/`reject` → coordinator owns the program AND ≥1
  review exists AND status is `under_review`.
- `ReviewPolicy`: `create` → reviewer role, application is
  `under_review`, and reviewer has no existing review for it (also DB-
  enforced by the unique constraint, policy just gives a clean error).
- `ProgramPolicy`: `update`/`viewApplications` → `coordinator_id ===
  auth()->id()`.
- `DisbursementPolicy`: `create` → coordinator owns the application's
  program AND application status is `approved`.

## Routes & controllers

Grouped by role prefix with `role:` middleware (Spatie), resource
controllers + route model binding:

- `student.dashboard`, `student.programs.index` (browse open
  programs), `student.applications.{index,create,store,show}`
- `reviewer.dashboard`, `reviewer.applications.{index,claim,show}`,
  `reviewer.reviews.store`
- `coordinator.dashboard`, `coordinator.programs.{index,create,store,
  edit,update}`, `coordinator.applications.{index,show,approve,reject}`,
  `coordinator.disbursements.store`

Program create/edit Blade view toggles the `max_family_income` vs
`min_gpa` fields via a small Alpine/vanilla-JS show/hide keyed on the
`type` select (Breeze ships Alpine already).

## Validation

Form Request classes per write action, e.g.:

- `StoreApplicationRequest`: deadline not passed (compare
  `program.application_deadline` to today), no duplicate
  (student_id, program_id, semester) — surfaced as a friendly
  validation error, not relying solely on the DB unique constraint
  throwing.
- `StoreReviewRequest`: score numeric range (e.g. 0–100), comments
  optional.
- `StoreDisbursementRequest`: seq_no uniqueness per application
  surfaced as validation error before hitting the DB constraint.
- Role-assignment request: student vs reviewer/coordinator mutual
  exclusivity rule.

All validation errors render inline via standard Blade
`@error`/`$errors` — Breeze's default form layout already supports
this pattern.

## Seeding

Single `DatabaseSeeder`, using model factories:

1. Create 3 roles: `student`, `reviewer`, `coordinator`.
2. ~15 student users + `StudentProfile` + 0–2 `GuardianPhone` each.
3. 3 coordinator users, 4 reviewer users, with 2 of the reviewers also
   given the `coordinator` role (overlap case).
4. 5 programs, mixed `need_based`/`merit_based`, distributed across
   the 3 coordinators.
5. ~20 applications across students/programs, varying `status`
   values (some `submitted`, some `under_review`, some `approved`,
   some `rejected`), respecting the unique (student, program,
   semester) constraint.
6. Reviews for applications in `under_review`/`approved`/`rejected`
   status (not for plain `submitted` ones, consistent with the
   workflow above).
7. Disbursements only for `approved` applications, 1–2 rows each with
   sequential `seq_no`.

All seeded users get a fixed known password (e.g. `password`), listed
per-account in the README (emails + role).

## Out of scope (unchanged from request)

Email notifications, file uploads/attachments, real payment
processing, multi-language support, API endpoints.

## Testing / verification approach

No automated test suite for this MVP (user-approved scope cut). Verify
via:

- `php artisan migrate:fresh --seed` completes with no errors.
- Manual walkthrough of each Definition-of-Done flow using the dev
  server: register, log in as a seeded student/reviewer/coordinator,
  submit → claim/review → approve/reject → disburse.

## Definition of done

(As given in the original request — restated for traceability)

- `php artisan migrate:fresh --seed` runs clean.
- Register a new user; log in as seeded student, reviewer, coordinator
  to see each role's dashboard (credentials in README).
- Student submits an application, it shows up for the right
  coordinator.
- Reviewer submits a review, student sees it once released (per the
  visibility rule above).
- Coordinator approves an application and records a disbursement.
- Form validation errors display inline.
- README covers setup, seeded accounts, and the reviewer
  self-assignment simplification.
