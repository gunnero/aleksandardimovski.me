# GitHub Governance Baseline

## Purpose

This document records the repository-governance files introduced for `gunnero/aleksandardimovski.me` and the GitHub settings that remain subject to manual approval. It does not apply branch protection, rulesets, security features, merge settings, or Actions permissions.

## Files added

- `.github/CODEOWNERS` assigns repository ownership to `@gunnero`.
- `SECURITY.md` defines the private vulnerability-reporting process.
- `.github/dependabot.yml` schedules conservative weekly Composer and npm updates.
- `.github/pull_request_template.md` provides a focused release-review checklist.
- `.github/ISSUE_TEMPLATE/bug_report.yml` collects reproducible website defects.
- `.github/ISSUE_TEMPLATE/content_correction.yml` collects evidence-backed content corrections.

## Intended branch-protection checks

The sole intended required status check is `quality`, matching the existing GitHub Actions job identifier.

Before making it required, confirm that `quality` reports successfully on a current pull request. If the job identifier changes, update the proposed rule before activation to avoid blocking every merge.

## Intended main-branch ruleset

The proposed ruleset targets only `main` and should:

- require a pull request before merge;
- require the `quality` status check;
- require the branch to be up to date before merge;
- require all review conversations to be resolved;
- block force pushes;
- block branch deletion; and
- apply to administrators where practical, without routine bypass.

For a solo-maintainer repository, begin with zero required approving reviews. Requiring approval from `CODEOWNERS` without another eligible reviewer could prevent a self-authored pull request from merging. Increase the review requirement only after another trusted reviewer is available.

## Merge strategy

Focused portfolio programs should use squash merge. The intended repository posture is:

- squash merge enabled;
- merge commits disabled;
- rebase merge disabled; and
- linear history required by the `main` ruleset.

Automatic merging and automatic branch deletion should remain disabled until separately reviewed.

## Actions permission posture

The default `GITHUB_TOKEN` permission should remain read-only. Workflows should receive write permissions only at the narrowest job or workflow scope when an explicitly reviewed use case requires them. GitHub Actions should not be allowed to create or approve pull requests by default.

The existing workflow uses these action families:

- `actions/checkout`;
- `actions/setup-node`; and
- `shivammathur/setup-php`.

A future Actions allowlist may be limited to these families after a pull-request CI run confirms the inventory. This change must be applied manually and is outside this program.

## Dependabot posture

Dependabot is configured for weekly Composer and npm checks against `main`. Compatible minor and patch development-dependency updates are grouped to reduce noise, and each ecosystem is limited to three open pull requests. Major and production dependency updates remain separate for deliberate review.

The configuration does not enable automatic merging. Dependency graph, Dependabot alerts, and Dependabot security updates still require separate manual review and activation in GitHub.

## Security reporting process

Suspected vulnerabilities must be reported privately to `aleksandar.dimovski@me.com`, not through public issues or pull requests. Reports should contain enough redacted detail to reproduce and assess the issue without disclosing credentials, personal data, server topology, environment data, or sensitive production information. Each linked repository remains governed by its own security policy.

## Manual GitHub UI steps still required

These steps are intentionally not performed by this program:

1. Open **Settings → Rules → Rulesets** and create a branch ruleset targeting `main`.
2. Require pull requests, the exact `quality` check, current branches, and resolved conversations.
3. Restrict force pushes and deletion, and apply the rules to administrators where practical.
4. Require linear history and use zero mandatory approvals until another eligible reviewer is available.
5. Test the ruleset in **Evaluate** mode if available, then activate it only after a full pull-request test.
6. Open **Settings → General → Pull Requests**, retain squash merge, and disable merge commits and rebase merging.
7. Open **Settings → Actions → General**, retain read-only workflow permissions, keep workflow PR approval disabled, and consider limiting allowed actions to the audited families.
8. Open **Settings → Code security and analysis** and separately review enabling Dependency graph, Dependabot alerts, Dependabot security updates, private vulnerability reporting, and staged CodeQL default setup.
9. Confirm secret scanning and push protection remain enabled.

## Kalveri pilot settings to apply first

The approved governance rollout should be piloted on `gunnero/kalveri` before applying this repository's settings:

- target only `main`;
- require pull requests;
- require `site`, `repository`, and `secrets` with the branch up-to-date requirement;
- require conversation resolution;
- block force pushes and deletion;
- apply to administrators where practical, without routine bypass;
- start with zero required approvals unless a second eligible reviewer is available;
- keep merge commits enabled because distinct SEO history may matter;
- do not require linear history;
- disable rebase merging;
- retain squash merging only if isolated maintenance is explicitly allowed;
- keep Actions default permissions read-only and workflow PR approval disabled; and
- retain secret scanning and push protection while staging other security features separately.

## Rollback and removal

If these repository files cause an unexpected integration problem:

1. Revert the governance commit through a reviewed pull request; do not rewrite history.
2. Remove only the affected file or configuration section after recording why it failed.
3. Re-run the full `quality` workflow and verify issue and pull-request forms render correctly.
4. If a future ruleset causes lockout, set only that new ruleset to **Evaluate** or **Disabled** while correcting it; do not weaken unrelated protections.
5. Restore captured merge and Actions settings if a separately approved manual setting change caused the failure.

Removing these files does not automatically undo GitHub settings. Any future settings rollback must be performed and documented separately through the GitHub web interface.
