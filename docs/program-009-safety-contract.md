# Program 009 safety and operating contract

Program 009 adds explainable preference learning and safety gates to the private workspace. It does not contain an active discovery scheduler, account creator, submission route, submission command, or browser submission adapter.

## Authority boundaries

Automatic actions are limited to normalizing a supplied discovery record, checking duplicates, evaluating confirmed preference rules, calculating a score, recording explainable evaluations, and importing an allowed record after a successful dry-run review.

Job approval is required before any application preparation or account-related work. Preparation must reverify that the posting is open, confirm remote and geographic eligibility, use a valid application URL, keep documents under the application private-storage directory, record the application questions, and complete the tailored CV, cover letter, answers, salary recommendation, and interview/role research. Candidate facts, employment dates, titles, education, experience boundaries, notice period, salary limits, and work-authorization wording must not be invented or changed without approval.

Final submission approval is separate from job approval. It authorizes only the exact displayed package and hashes. Any answer, document, attachment, URL, salary, notice-period, or work-authorization change invalidates approval and returns the package to final review.

Automation always stops for password creation, email verification, CAPTCHA, MFA, ambiguous consent, demographic questions, identity-document requests, background-check consent, salary drift, work-authorization uncertainty, or material posting changes. Recruiter email and legal declarations also require explicit approval. The first production submission batch is operationally limited to three applications.

## Preference rules

A rejection remains job-only unless the owner selects reusable-rule behavior. A reusable hard exclusion additionally requires its dedicated confirmation checkbox. Confirmed rules can be hard exclusions, strong penalties, soft penalties, or informational observations and can apply to all jobs, a company, role family, source, country, or technology.

Only active, confirmed, unexpired rules are evaluated. The Preferences page lets the owner edit severity and scope, disable a rule, set or change expiry, delete it, and inspect imported jobs affected by it. Changes apply to future evaluations immediately. Every persisted match retains the rule ID, adjustment, decision, and encrypted human-readable explanation. No exclusion is based on opaque semantic similarity.

## Remote-policy evidence

The canonical limited-remote rule excludes explicit annual remote-day limits, required hybrid work, recurring or mandatory office attendance, and office-first arrangements. Fully remote roles, optional office access, and voluntary company events do not match. Missing or ambiguous remote wording becomes Needs Research rather than an exclusion.

## Submission readiness

`SubmissionGuard` is a non-submitting policy service. It requires `approved_for_submission`, current answer and document hashes, an open posting, resolved required/legal questions, a user-confirmed work-authorization answer, complete account verification, and a verified final URL. Runtime stop signals are additive and always block. There is deliberately no code that performs the final submit action.

## Operational workflow

Use the discovery procedure in `docs/program-009-discovery-workflow.md`. Always run the importer with `--dry-run` first, review malformed records, duplicates, source evidence, rule matches, adjustments, and exclusions, then explicitly run the write command. Never place credentials, candidate-private content, or full job content in logs; exclusion audit metadata contains stored rule IDs only.
