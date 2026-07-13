# Program 008 — private job search and application workspace

Program 008 is an authenticated, single-owner workspace for job discovery, review, application preparation, exact-content approval, and submission history. It is deliberately absent from public navigation, sitemap, structured data, social metadata, RSS, and public APIs.

## Safety boundary

Approval of a job creates an application in `preparing_application`; it never authorizes submission. Submission requires a separate final approval after every answer, document, sensitive question, account requirement, and uncertainty is shown. Approval stores hashes of the exact answers and private documents. A changed hash invalidates authorization and requires review again.

No automated submission action is included in this program. Any future submitter must require `approved_for_submission`, matching current hashes, no unresolved required questions, a verified-open job, a valid URL, and completed or in-flow email verification.

Passwords, tokens, identity documents, banking or tax identifiers, one-time codes, full email bodies, and private browser state must never be stored. Application files belong only under `storage/app/private/job-applications/`; the local filesystem disk is not web-servable.

## Setup without creating a production user

Back up the database and `storage/app/private/job-applications/` before migration. Run migrations, create the sole workspace owner interactively, then initialize only verified portfolio facts:

```shell
php artisan migrate
php artisan workspace:user-create
php artisan jobs:initialize-profile --user=owner@example.com
```

Absent authorization, notice, availability, salary, visa, reference, and desired-start facts are marked `user_confirmation_required`.

## Secure discovery import

```shell
php artisan jobs:import-discovered discoveries.json --user=owner@example.com --dry-run
php artisan jobs:import-discovered discoveries.json --user=owner@example.com
```

The command accepts a JSON list (or a top-level `jobs` list), rejects unknown fields and malformed values, normalizes tracking parameters from HTTP(S) URLs, detects per-user duplicates, requires an existing user, emits a minimal report, and audits successful records without logging private notes or candidate data.

CSV import and export are deferred scope. No spreadsheet formulas are accepted or produced.

## Security and authorization model

Registration and public write APIs do not exist. Every workspace route requires authentication, verified email, CSRF protection for mutations, and the single `is_workspace_owner` account; other authenticated users receive HTTP 403. Login is limited to five attempts per minute and regenerates the session. Production defaults enable encrypted sessions and secure cookies; deployment must additionally confirm HTTPS, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, and an appropriate SameSite policy.

Sensitive profile facts, answers, notes, account email metadata, and email summaries use Laravel encrypted casts. Agent activity stores only bounded event metadata, never answers, candidate facts, tokens, passwords, or email bodies. The interactive user command requires a 16-character mixed-case password with a number and symbol, refuses common defaults, and never logs the secret.

Private downloads require owner authorization and a generated UUID filename. Upload handling accepts only server-detected PDF, DOCX, or plain-text MIME types up to 10 MB. Paths are generated below an application-specific directory, never accepted from user input, and application deletion removes its private directory. Backups must include these files but must never copy them beneath `public/`.

## Data and workflow model

`CandidateProfile` is one-to-one with the owner. `JobOpportunity` holds source evidence and scoring. `JobApplication` holds preparation, approval, submission confirmation, and follow-up state. Questions, account verification tasks, minimal email events, and redacted agent activities are separate child records.

Job and application transitions are allowlisted in `StateTransitions`; terminal states cannot be reopened arbitrarily. Job approval only creates `preparing_application`. Final approval requires a verified-open, unexpired posting, valid URL, and confirmed required questions. Beginning submission additionally requires current answer and actual stored-document hashes. This release intentionally exposes no submit route or command, so approval cannot itself perform an external action.

Legal declarations, authorization, visa status, salary outside the approved range, notice/start dates, references, demographics, criminal history, disability/veteran questions, background checks, relocation, travel, and identity requests always require explicit user confirmation. Voluntary demographics default conceptually to “Prefer not to answer,” but remain unsubmitted until final approval.

## Codex agent contract

Codex may import validated discovery records, score fit, prepare private drafts, map form fields, and request missing input. It must not invent verified facts, change employment or education boundaries, store credentials, access email automatically, or submit without a separate current final approval. Gmail remains future OAuth-only scope with minimal permissions and reply approval.

## Operations, rollback, deletion, and recovery

Deployment is an explicit operator task: back up database and private documents, deploy code, run `php artisan migrate --force`, create the owner interactively, initialize the profile, set private-directory ownership to the application user, configure secure sessions, then verify anonymous 302/authorized 200/unauthorized 403 behavior. No public-site redesign is required.

Rollback code first, then use `php artisan migrate:rollback --step=2` only if no Program 008 data must be retained. Otherwise restore the pre-migration database and matching private-document backup together. For deletion, remove the application through the model so its private directory is removed; deleting the owner cascades workspace records. Recovery must restore database and private files from the same backup point, verify ownership and hashes, rotate the application key only through a planned encrypted-data migration, and retest authorization before reopening access.
