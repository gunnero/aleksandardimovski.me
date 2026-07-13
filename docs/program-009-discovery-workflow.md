# Program 009 discovery workflow

This workflow discovers candidates; it never approves, prepares, creates accounts, or submits applications. The complete authority and stop-condition contract is in `docs/program-009-safety-contract.md`.

1. Find 10–20 current Senior PHP/Laravel/backend/product-engineering opportunities on employer-controlled pages. Require remote Europe, EMEA, or global eligibility and evidence that North Macedonia or international contracting is supported.
2. Reject expired, hybrid, office-required, limited-remote-day, junior, WordPress-only, scraped-only, geographically ineligible, and duplicate listings before creating the validated JSON file.
3. Record only source-supported facts. Use `needs_research` when geography, compensation, or contracting eligibility is unclear. Do not infer candidate facts, legal answers, or work authorization.
4. Run `php artisan jobs:import-discovered <validated-file> --user=aleksandar.dimovski@me.com --dry-run`.
5. Review malformed records, URL/external-ID duplicates, possible company/role duplicates, matched rules, base scores, adjustments, final scores, and exclusions. Import only when malformed records are zero, sources are verified, duplicates are resolved, and no intended import is hard-excluded.
6. Run the same command without `--dry-run`. Confirm created, excluded, duplicate, warning, and invalid totals. Exclusions retain rule IDs in the private audit trail without storing job or candidate content in logs.

Preparation remains a separate owner-approved workflow. Only opportunities already in `approved_for_preparation` may receive a private package. Submission requires current exact-content approval and the `SubmissionGuard`; CAPTCHA, MFA, account/password/email verification, legal or demographic questions, identity/background-check requests, salary drift, or material posting changes stop automation and require user action. The initial production submission batch is capped operationally at three and is outside this development workflow.
