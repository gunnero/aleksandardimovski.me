# Portfolio public-topology sanitization

## Scope reviewed

The review covered the root README and changelog, all tracked Markdown and policy documents, application content configuration, article source, Blade templates, tests, workflow files, public metadata, and downloadable resume content. Dependencies and generated build assets were excluded from policy scanning because they are not authored public evidence.

## Categories found

The legacy public documentation contained an environment-specific server label, exact production runtime inventory, operational backup and web-server detail, deployment instructions tied too closely to real infrastructure, and release-review text that preserved those specifics. No sensitive value is reproduced here.

The content review also found outdated BuildIQ wording that could imply an assisted capability is already implemented. Current public evidence supports deterministic workflows, so that wording was corrected.

## Remediation approach

- Replaced the environment-named deployment runbook with public deployment principles.
- Removed exact production versions, module inventories, server references, and operationally specific procedure text from historical review documents.
- Retained useful engineering evidence about exact reviewed commits, locked dependencies, reproducible builds, safe migrations, cache rebuilding, narrow permissions, configuration validation, health checks, log review, backups, and rollback.
- Corrected internal links and release-history wording to the sanitized document.
- Added owned-source regression coverage for server labels, home-directory paths, IP addresses, remote-shell commands, web-server layout, and private backup-location patterns.
- Kept detailed operational records private and outside the public repository.

## Public material retained

The repository continues to explain release discipline at a generic level because this is relevant engineering evidence and does not identify infrastructure. Localhost examples remain allowed for local development and automated review. Public HTTPS project, repository, and canonical URLs remain allowed because they are intended public destinations.

## Program 007 preservation

The pre-existing uncommitted repository-link, QR, resume, documentation, and test work was reviewed in place and preserved. Sanitization adds no product feature and does not modify production.
