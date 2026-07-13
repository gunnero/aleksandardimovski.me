A deployment is the point where reviewed code meets a real runtime, real configuration, and real operational constraints. Treating it as a final file-copy step ignores much of the engineering risk.

My approach to Laravel and legacy PHP deployments comes from maintaining applications that need controlled change. The exact servers, accounts, paths, and private domains do not belong in a public article. The sequence and reasoning do.

## Deploy the reviewed commit

I begin by identifying the exact commit that was reviewed and approved. Branch names can move. A commit identifier does not.

Before promotion, I confirm that hosted checks are green, the approved head has not changed, and the target branch has not advanced unexpectedly. After merge, I record the resulting commit because a squash merge produces a different identifier from the feature branch head.

Production should then check out that exact merged commit. This creates a direct answer to a basic operational question: what code is live? If production cannot answer that cleanly, debugging and rollback become guesswork.

The same discipline applies to a legacy PHP repository. Even if its deployment mechanics are simpler, the released diff should be known and the production tree should not contain unexplained edits.

## Confirm runtime compatibility first

Application correctness depends on the environment. I check the PHP CLI and PHP-FPM versions, Composer, Node and npm when frontend assets are built, required PHP extensions, web-server state, and available disk space.

Composer’s platform check is particularly useful because it validates the installed production dependency set against the actual PHP runtime and extensions. It should run from the deployed application directory as the application owner.

A version number alone is not the whole check. CLI and FPM can use different configurations. An extension available to one may be absent from the other. The application’s health endpoint or a real request helps confirm the serving runtime after dependency installation.

## Create a timestamped backup

Before changing production, I create a timestamped backup appropriate to the release. It may include the current checkout or required application files, environment configuration, relevant web-server configuration, and public files that are not part of Git.

The backup should record the previous commit. That makes the recovery target explicit and prevents an operator from selecting an arbitrary earlier release.

Database protection depends on the migration. A code-only release and a destructive schema change do not have the same rollback requirements. The migration plan should identify whether a database backup or forward-fix strategy is needed.

Backup locations and server layouts remain private. What matters publicly is that recovery is prepared before the release, not after a failure.

## Preserve environment configuration

Production `.env` data contains environment-specific settings and secrets. It should not be replaced by repository defaults, printed in deployment output, or copied back into source control.

I verify that it exists, preserve it in the protected backup, and leave it in place while changing tracked application code. After deployment, I check Laravel’s loaded environment through framework commands rather than echoing sensitive values.

The important assertions are safe to report: the application is in production mode, debug is off, the expected URL is configured, and maintenance mode is not accidentally left on.

## Preserve public operational files

Not every file under a web root belongs to the application repository. `.well-known` can contain certificate challenges, security declarations, or mail-related policy files. Replacing the public directory without inventory can break services unrelated to the visible site.

I inspect and back up those files before deployment, preserve them during the release, and verify their public response afterward where applicable.

This is a good example of why application deployment requires systems awareness. A portfolio change should not damage mail policy or certificate renewal behavior simply because both use the same public tree.

## Install production dependencies as the application owner

Composer and Artisan commands should run as the application’s Linux owner rather than root. This prevents root-owned caches and generated files from becoming a later permission problem.

For Laravel, I install from the lock file without development dependencies, prefer distribution archives, optimize the autoloader, and disable interaction. I do not use deployment as an opportunity to upgrade an operating-system-managed Composer installation.

The lock file is part of the reviewed release. A production install should reproduce it, not resolve an unreviewed dependency set.

After installation, `composer check-platform-reqs` confirms that the deployed vendor directory matches the runtime.

## Build frontend assets reproducibly

When the application includes a frontend build, I use `npm ci` so the lock file determines the installed packages. Then I run the documented production build and review warnings rather than treating any zero exit code as complete evidence.

Build output should be generated before removing `node_modules`. Production normally needs the compiled assets, not the entire development dependency tree.

Dependency audits are run during review and CI. They can also provide a useful deployment signal, but the release should not perform uncontrolled package upgrades to chase a warning.

## Run migrations safely

Laravel migrations run with the production force flag only after the target commit, environment, and database connection are confirmed. I review whether migrations are pending and whether they are compatible with rollback.

“Nothing to migrate” is a valid result and should be recorded. If migrations run, their names and result belong in the deployment report without exposing private database details.

Legacy PHP applications may not use a migration framework. The same principle still applies: schema changes need ordered scripts, backups, compatibility checks, and a record of execution.

## Clear stale state and rebuild caches

Laravel’s cached configuration, routes, views, and events can preserve assumptions from the previous release. I clear stale optimized state and rebuild the caches after dependencies and migrations are ready.

Cache commands can reveal problems that normal file installation misses. A route closure can prevent route caching. Invalid configuration can fail configuration caching. Blade compilation can expose template errors before a user reaches the page.

The release is not complete until these commands run successfully under the production environment.

## Normalize ownership and permissions

Application files should belong to the application owner. Only directories that require runtime writes—typically Laravel storage and bootstrap cache areas—should be writable by the relevant owner or group.

I never use `chmod 777`. It hides the ownership problem by allowing every local user or process to write. That is not a stable fix and expands the impact of another compromised process.

Permission normalization should preserve executable files defined by the repository. A broad command that removes an executable bit can make an otherwise clean checkout appear modified. Final `git status` is useful here because it detects mode drift as well as content drift.

## Validate the web server before reload

Apache or Nginx configuration should be tested before a reload. If the configuration test fails, the service should not be reloaded with invalid state.

I keep application deployments scoped to the relevant virtual host and avoid unrelated server, DNS, mail, or database changes. Shared-service modifications require a separate reason and approval because they broaden both risk and rollback.

After reload, I confirm the web server and PHP-FPM service are active. For Laravel, PHP-FPM health matters more than the CLI command succeeding because FPM serves the application.

## Smoke-test the public surface

My smoke plan lists explicit routes rather than checking only the homepage. For this portfolio, that includes core pages, project case studies, articles, the resume, downloadable PDF, sitemap, robots file, health endpoint, assets, and the custom not-found response.

The expected status matters. An unknown route should return 404, not a styled page with status 200. A PDF should return `application/pdf`, be non-empty, and retain its intended page structure. A health endpoint should exercise enough application bootstrapping to detect configuration failures.

I also verify TLS normally, without bypassing certificate validation, and check rendered metadata for development addresses.

## Inspect logs after real requests

Service status does not prove that requests are error-free. After smoke testing, I inspect recent web-server, PHP-FPM, and application logs.

The time boundary matters. Old errors may be useful history but should not be reported as release failures. New errors caused by the smoke test need investigation even when the page returned 200.

Operator mistakes during verification should be recorded honestly and distinguished from application request failures. Hiding or deleting a diagnostic error makes the report less trustworthy.

## Confirm a clean production checkout

At the end, production should resolve to the intended commit and `git status` should be clean. Generated files should be ignored or stored outside tracked paths. Unexpected modifications may indicate permission changes, server-side edits, or a build writing into source-controlled files.

I also confirm that temporary build dependencies were removed when the procedure requires it and that preserved operational files still exist.

The [Engineering Principles](/engineering-principles) page places this release discipline alongside architecture, confidentiality, and evidence. The [resume](/resume) summarizes production ownership as part of the broader engineering role.

## Keep rollback executable

Rollback is not a sentence added to a report. It is a sequence tied to the previous commit and backup. If a critical verification fails, I restore the prior release and preserved configuration, reinstall or rebuild what that release requires, rebuild caches, and verify it with the same smoke plan.

I do not leave production halfway between releases while investigating. A stable previous version is better than an uncertain new version.

Not every warning requires rollback. A known non-blocking build warning can be documented. A broken route, unsafe configuration, failed migration, invalid TLS result, or new production error on a critical path does.

## What I would do differently today

I would automate more of the repeatable evidence while retaining explicit stop points. A deployment script can record commits, run platform checks, build assets, rebuild caches, and execute route probes consistently. It should still refuse to continue when the checkout is dirty or a critical gate fails.

I would also keep a machine-readable route manifest for each release so that smoke coverage evolves with the application instead of relying on an operator to remember new pages.

## Deployment is engineering work

Safe deployment connects source review, dependency management, runtime configuration, database change, server operation, security, observability, and recovery. That is why I treat it as part of engineering rather than an afterthought assigned after the code is “done.”

The goal is not a long command list. It is a release whose identity is known, whose assumptions are checked, whose public behavior is verified, and whose previous state can be restored. That discipline applies equally to a modern Laravel application and a carefully maintained legacy PHP system.
