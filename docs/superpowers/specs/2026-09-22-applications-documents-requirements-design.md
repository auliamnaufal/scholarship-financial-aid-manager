# Applications, Documents & Requirements — Design Spec

Supersedes the "no added/renamed columns" constraint in
[the MVP spec](2026-09-07-scholarship-financial-aid-manager-design.md).
The ERD is now allowed to grow; `docs/diagrams/scholarship-financial-aid-pdm.drawio`
is updated alongside it.

## Purpose

Ten features asked for on 2026-09-22:

1. A student can cancel an application.
2. A coordinator can delete a scholarship.
3. A reviewer can edit their assessment.
4. A student can edit their own biodata.
5. The application form collects biodata and file uploads (CV,
   recommendation letter, personal statement).
6. A student submits an essay and a transcript (KHS).
7. Disbursements show how much money is left to hand out.
8. A coordinator has full CRUD over students and scholarships.
9. A coordinator decides the requirements for each scholarship.
10. A student enters a bank account number.

## Decisions taken with the user

| Question | Decision |
| --- | --- |
| May the ERD change? | Yes; update the PDM and this spec with it |
| How are requirements modelled? | Lookup table of requirement types + a per-programme join table |
| Deleting programmes and students | Soft delete, so applications, reviews and disbursements survive |
| Essay vs personal statement | The same thing: one text answer, not a file |
| Cancelling an application | Allowed while `submitted` or `under_review`, never after a decision |
| Editing a review | Allowed while `under_review`, locked once the coordinator decides |
| Bank account | Held on the student's biodata, one per student |
| "Money left to hand out" | Both per programme and per approved application |
| Biodata fields | Academic identity, contact, family economics, bank account |
| Uploads | PDF/DOC/DOCX, 5 MB maximum |

### One extension beyond what was asked

The essay stays a single text answer, but **whether it is required is set
per programme**, exactly like the CV and the recommendation letter. The
user's own point — that not every scholarship needs a recommendation
letter — applies to essays too. A `requires_essay` boolean on `programs`
would reintroduce the column-per-requirement problem the requirement
table exists to avoid, so `requirement_types` carries a `kind` of
`file` or `text` and the essay is simply a `text` requirement.

## Data model

### New tables

- **requirement_types**: `id`, `name`, `slug` unique, `kind`
  enum(`file`,`text`), `description` text nullable.
  Seeded with: CV (file), Recommendation letter (file), Transcript/KHS
  (file), Essay (text), Achievement certificate (file).
- **program_requirements**: `id`, `program_id` FK→programs,
  `requirement_type_id` FK→requirement_types, `is_required` boolean
  default true, `instructions` text nullable, unique
  (`program_id`,`requirement_type_id`).
- **application_documents**: `id`, `application_id` FK→applications,
  `requirement_type_id` FK→requirement_types, `file_path` string
  nullable, `original_name` string nullable, `mime_type` string
  nullable, `size_bytes` integer nullable, `body` text nullable, unique
  (`application_id`,`requirement_type_id`).
  A `file` requirement fills the file columns; a `text` requirement
  fills `body`. Exactly one of the two, enforced in the model.

### Changed tables

- **student_profiles** gains: `nim` string unique, `faculty` string,
  `study_program` string, `phone` string nullable, `address` text
  nullable, `family_income` decimal(12,2) nullable,
  `parent_occupation` string nullable, `dependents_count` integer
  nullable, `bank_name` string nullable, `bank_account_number` string
  nullable, `bank_account_holder` string nullable.

  `family_income` closes a real hole: `programs.max_family_income`
  already exists but nothing on the student side could be compared
  against it, so need-based eligibility was uncheckable.

- **applications** gains: `awarded_amount` decimal(12,2) nullable — the
  sum promised when the coordinator approves — and `cancelled_at`
  timestamp nullable.

- **programs** and **users** gain `deleted_at` (soft deletes).

- **ApplicationStatus** gains a `cancelled` case.

### Relationships added

- `Program`: `hasMany(ProgramRequirement)`,
  `belongsToMany(RequirementType, 'program_requirements')`
- `RequirementType`: `hasMany(ProgramRequirement)`,
  `hasMany(ApplicationDocument)`
- `ProgramRequirement`: `belongsTo(Program)`, `belongsTo(RequirementType)`
- `Application`: `hasMany(ApplicationDocument)`
- `ApplicationDocument`: `belongsTo(Application)`,
  `belongsTo(RequirementType)`

## Behaviour

### Applying

The apply form is built from `program_requirements`: a file input per
`file` requirement, a textarea per `text` one, with optional ones marked
as such. Submission is rejected unless every required item is present,
and unless the student's profile satisfies `min_gpa` /
`max_family_income` where the programme sets them.

Uploads go to the `local` disk under `applications/{application}/`, are
validated as `mimes:pdf,doc,docx` and `max:5120`, and are served through
a controller guarded by `ApplicationPolicy::view` — never from a public
URL, since transcripts and recommendation letters are personal data.

### Cancelling

`ApplicationPolicy::cancel` allows it when the application belongs to
the student and the status is `submitted` or `under_review`. Cancelling
sets `status = cancelled` and stamps `cancelled_at`. Reviews already
written stay as history. A cancelled application does not reappear in
the reviewer pool and cannot be decided on.

### Reviewing

`ReviewPolicy::update` allows a reviewer to edit their own review while
the application is `under_review`. Once the coordinator approves or
rejects, the review is read-only — the decision rests on those numbers.
No change-history table; the user chose the simpler option.

The reviewer's application page lists every requirement with what the
student supplied, so a missing optional document is visible at a glance.

### Deciding and disbursing

Approving sets `awarded_amount`. Two derived figures are shown:

- per programme: `budget − Σ disbursements` across its applications
- per application: `awarded_amount − Σ disbursements`

Both are computed with `withSum`, not stored, so they cannot drift.
Recording a disbursement is rejected if it would push either figure
below zero.

### Coordinator CRUD

- Scholarships: index (new), create, edit, soft delete. Deleting hides
  the programme from the public listing and from new applications;
  existing applications keep working.
- Students: index, create, edit (new), soft delete (new). A soft-deleted
  user cannot log in, and this needs no extra work: `EloquentUserProvider`
  builds its lookups with `newQuery()`, which applies the soft-delete
  global scope, so an archived account fails both `retrieveByCredentials`
  and `retrieveById` — an open session ends on the next request.
- Requirements: managed from the programme form, as a checklist of
  requirement types with a required/optional toggle and free-text
  instructions.

## Open points for the user

- Deleting a student who has an approved application with money already
  disbursed: currently allowed (soft delete keeps the records). Say if
  it should be blocked instead.
- `awarded_amount` is entered by the coordinator at approval time. An
  alternative is a fixed per-awardee amount on the programme.
- Requirement types are seeded and global. Coordinators pick from the
  list but cannot invent new types; that was the option not chosen.
