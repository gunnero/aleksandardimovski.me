Legacy PHP systems are often discussed as if age alone makes them candidates for replacement. My experience with Hera Backoffice and Nema30 Backoffice has taught me to start somewhere less dramatic: understand what the system does for the business, identify where change is dangerous, and improve it in controlled steps.

Both systems are presented publicly as sanitized case studies. They are distinct applications with different responsibilities. I do not publish their private users, databases, internal addresses, infrastructure, or client-sensitive rules. The useful public evidence is the engineering method, not confidential implementation detail.

## Understand the business workflow first

Before changing code, I trace the workflow that the code supports. That means identifying inputs, decisions, database changes, generated output, dependencies, and the people or systems that consume the result.

Legacy code can look redundant because the reason for a condition is no longer documented. Removing it before understanding the workflow can turn cleanup into a production regression. A strange branch may represent an old exception that is still contractually or operationally important.

I therefore separate “I do not like this implementation” from “this behavior is wrong.” The first is an engineering preference. The second requires evidence.

For Hera, public discussion stays at the level of maintaining and modernizing an established business-administration platform. For Nema30, the public boundary covers document-oriented and compliance backoffice work. Keeping them distinct matters because combining sanitized examples can accidentally create a fictional system that never existed.

## Map critical paths and failure points

Not every file has equal operational importance. I look for paths that create or update important records, generate documents, change access, communicate with other systems, or affect production configuration.

A useful critical-path map answers practical questions. What happens if this action stops halfway? Can it be retried? Is a database write transactional? Does a generated file need to match stored state? Which errors are visible to an operator, and which disappear into a log?

This does not require publishing the private workflow. A generalized example is enough: if a business action updates a record and produces a document, the release plan should verify both outputs and define what happens when only one succeeds.

The map guides testing and rollback. It also prevents low-risk formatting work from receiving the same release process as a database-affecting change.

## Backups and rollback before edits

A backup is not a ritual command. It is a recovery asset with a known scope. Before risky work, I identify which application files, environment configuration, generated assets, and database state need protection. I record the current deployed revision when Git is available.

Rollback should be designed before deployment. If the new code is incompatible with an irreversible schema change, checking out an older commit may not restore service. Migration design therefore has to consider both forward recovery and the realistic limits of reversal.

For narrower legacy changes, a timestamped application and configuration backup combined with a verified prior commit can provide a clear return path. The specific private locations remain private; the discipline is public.

## Bring the application into Git carefully

Some older systems began with direct file transfers or server-side edits. Moving them into Git creates a reviewable history, but the first import needs care. Environment files, credentials, generated exports, logs, uploads, backups, and customer data do not belong in source control.

I inventory the tree before adding it. Ignore rules should be based on actual files, not a generic template. A secret scan should run before the first push, and history needs review if the repository already existed elsewhere.

Once the deployed commit is identifiable, releases become easier to reason about. A diff can be reviewed, a rollback target can be named, and production drift becomes visible through `git status`.

## Separate cleanup from rewrites

Safe cleanup reduces risk without changing the product contract. Examples include removing dead debug output, tightening file permissions, replacing an unsafe query pattern in a bounded path, documenting configuration, or adding a focused regression test.

A rewrite changes more assumptions at once. It may replace routing, session behavior, database access, templates, and deployment mechanics together. Even if the new code is cleaner, the combined behavioral surface is harder to verify.

That is why I prefer incremental modernization unless there is strong evidence for replacement. The first goal is not to make the repository resemble a new project. It is to make the existing system safer to understand and change.

The [Hera Backoffice case study](/projects/hera-backoffice) and [Nema30 Backoffice case study](/projects/nema30-backoffice) show those boundaries without exposing private code.

## Make changes small and reviewable

A small change has a clear purpose, a limited diff, and a verification plan. It can still be technically meaningful. Adding an authorization check, improving a document-generation boundary, or making database behavior compatible can be safer as separate releases than as one broad modernization commit.

Small changes improve diagnosis. If a release introduces unexpected behavior, fewer changed boundaries need investigation. They also support better review because the reviewer can understand why each line changed.

I avoid mixing dependency upgrades, formatting sweeps, feature changes, and security fixes when they can be separated. Mechanical noise hides behavioral risk.

## Preserve operational continuity

Legacy business software often has a long operating history precisely because people depend on it. Modernization has to respect that continuity.

This means planning around active workflows, preserving environment configuration, keeping generated and user-provided files intact, and validating permissions after deployment. It also means avoiding maintenance windows that are longer than the change requires.

Continuity does not mean refusing improvement. It means sequencing improvement so that the organization is not forced to absorb unnecessary technical risk.

## Improve security incrementally

Security work in an older PHP application can include authentication, authorization, session handling, input validation, output escaping, file exposure, database queries, error behavior, and server permissions. Attempting every improvement in one release creates a large regression surface.

I prioritize reachable risks and critical boundaries. A missing server-side permission check deserves focused attention. A publicly accessible private file needs removal or protection. Debug output and detailed errors should not disclose sensitive state in production.

Each change should come with a test or repeatable verification where practical. Security scanners are useful, but they cannot understand every business authorization rule. Manual reasoning remains necessary.

Public documentation should explain the category of improvement without providing attackers with private topology or exposing the client’s operating rules.

## Validate database compatibility

PHP modernization often intersects with database and runtime upgrades. Changes in SQL modes, reserved words, character handling, extensions, or driver behavior can affect code that appeared stable for years.

I verify the actual target runtime and database behavior rather than assuming local compatibility. Queries on critical paths need testing with representative structure and synthetic data. Schema changes should be bounded, backed up, and reviewed for rollback impact.

Compatibility fixes should avoid silently changing business meaning. Making a query execute is not enough if its ordering, filtering, or null behavior changes.

## Deploy with the application’s reality in mind

Deployment starts with the reviewed diff and the target environment. I record the current commit, make the required backup, preserve environment-specific files, and confirm service health before changing anything.

After the release, I validate configuration, runtime services, critical routes, database connectivity, logs, and Git cleanliness. Ownership should match the application user, and only directories that need writes should be writable. Broad `777` permissions are never a repair strategy.

The release and rollback procedure should be documented so that it does not depend on one person remembering a sequence under pressure.

## Why a complete rewrite is rarely the first move

A rewrite can be justified when the existing architecture blocks required change, the runtime is unsupportable, or incremental work costs more than controlled replacement. But that conclusion should follow investigation.

The existing application contains more than code. It contains business decisions, exceptions, data history, integration behavior, and operator expectations. Recreating those correctly is product discovery as much as software development.

Starting with stabilization produces evidence. It reveals which workflows matter, where tests are missing, and which boundaries can be replaced safely. If a rewrite later becomes appropriate, that evidence makes it less speculative.

## What remains private

A public case study should not include private user names, internal addresses, credentials, infrastructure maps, database names, raw screenshots containing client data, or confidential business rules. Synthetic examples must be labeled by context and should illustrate a general principle rather than imitate real records.

Confidentiality is not an obstacle to engineering evidence. I can show that I use Git, regression checks, safe deployment, authorization review, backups, and rollback without exposing the client’s system.

## What I would do differently today

On any legacy system, I would establish a risk register and focused smoke suite earlier. Even a small set of automated checks around authentication, permissions, and the most important workflows creates a better baseline for every later change.

I would also document operational ownership sooner: which files are generated, which jobs run outside requests, where errors are reviewed, and which configuration is authoritative. That knowledge often exists informally until a release exposes the gap.

## Engineering judgment over dramatic replacement

Maintaining old PHP systems has reinforced a simple lesson: modernization is successful when the system becomes safer to operate and easier to change without losing the workflow it exists to support.

Understand the business first. Protect recovery. Put change in Git. Separate cleanup from rewrites. Improve security in reviewable steps. Verify the real runtime and keep private details private. The result may be less dramatic than a rewrite announcement, but it is stronger engineering evidence.
