# Scholarship & Financial Aid Manager (MVP)

A Laravel database-class project for managing scholarship/financial-aid
programs, student applications, reviews, and disbursements, with three
overlapping roles: **student**, **reviewer**, and **coordinator**.

Full design rationale lives in
[docs/superpowers/specs/2026-09-07-scholarship-financial-aid-manager-design.md](docs/superpowers/specs/2026-09-07-scholarship-financial-aid-manager-design.md).

## Tech stack

- Laravel 11
- SQLite (local dev)
- Laravel Breeze — Blade + Tailwind stack
- `spatie/laravel-permission` for roles (a user can hold multiple roles)
- Eloquent ORM, migrations, factories, seeders (no raw SQL)

## Setup

```bash
composer install
cp .env.example .env   # if .env doesn't already exist
php artisan key:generate
touch database/database.sqlite   # if it doesn't already exist
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Visit `http://localhost:8000` and log in with one of the accounts below,
or register a new account (a freshly registered user has no role and
sees a placeholder dashboard until a role is assigned).

### Swapping SQLite for MySQL

Edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scholarship_manager
DB_USERNAME=root
DB_PASSWORD=
```

Remove/comment out the `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`
lines, create the database, then run `php artisan migrate:fresh --seed` again.

## Seeded test accounts

All seeded users share the password: **`password`**

| Email | Role(s) | Notes |
|---|---|---|
| `student1@example.com` | student | has a mix of submitted/under-review applications |
| `coordinator1@example.com` | coordinator | manages one program |
| `reviewer1@example.com` | reviewer | pure reviewer, no coordinator access |
| `staff1@example.com` | reviewer + coordinator | exercises the overlapping-role case; lands on a dashboard picker after login |
| `user@example.com` | *(no role)* | shows the "no role assigned" placeholder dashboard |

14 additional students, 2 additional coordinators, and 2 additional
reviewers are seeded with random data (see `database/seeders/DatabaseSeeder.php`).

## Data model

Matches the assignment's ERD: `users` (Breeze) + `student_profiles`,
`guardian_phones`, `programs`, `applications`, `reviews`, `disbursements`,
plus Spatie's role tables. See the design spec linked above for full
column lists, relationships, and the authorization rules implemented
as Laravel Policies (`app/Policies/*`).

## Known simplifications (MVP scope)

- **Reviewer self-assignment.** There is no formal review-assignment
  table. Any user with the `reviewer` role can see and "claim" any
  `submitted` application from an open pool; claiming is what flips
  the application's status to `under_review` and unlocks the scoring
  form. This is simpler than a real assignment workflow but is
  explicitly acceptable for this MVP.
- **Review visibility to students.** The `reviews` table has no
  release/visibility flag. A student can see review scores/comments
  on their own application only once the coordinator has made a final
  decision (`approved` or `rejected`) — this is computed from
  application status, not stored.
- **Role assignment.** Roles are seeded directly (see `DatabaseSeeder`);
  there is no admin UI for assigning/changing a user's roles in this
  MVP, since it isn't one of the required pages. The
  student/reviewer+coordinator exclusivity rule described in the
  assignment is enforced at the seeder level and would need a form
  validation rule if a role-management UI were added later.
- **Out of scope** (per the assignment): email notifications, file
  uploads/attachments, real payment processing for disbursements,
  multi-language support, and API endpoints (this is a Blade-rendered
  app only).

## Testing

No automated test suite is included for this MVP (a deliberate scope
cut). Verification was done via `php artisan migrate:fresh --seed` and
a manual walkthrough of every role's flow (register → login as each
seeded role → submit an application → claim + review → approve/reject
→ record a disbursement → confirm the student sees the released
review and disbursement history).
