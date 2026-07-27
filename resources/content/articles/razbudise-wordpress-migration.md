Content migration is often described as an export-and-import task. That description hides the difficult parts: source identity, incomplete metadata, incompatible markup, media references, duplicate prevention, validation, retries, and the decision about when migrated material may become public.

Razbudise is moving legacy publishing content toward a Laravel editorial platform and a Next.js delivery direction. The migration work is deliberately bounded. Content is delivered as draft material for review; the tooling does not treat a successful transfer as permission to publish.

> **Production note**
>
> A migration is safe when every transferred item can be traced, reviewed, retried, and withheld from publication.

## Inventory the source before transforming it

The first step is not writing an importer. It is understanding the source content model. WordPress content can include posts, pages, categories, tags, authors, media, excerpts, status values, dates, slugs, embedded blocks, shortcodes, and plugin-specific metadata.

Not all of that belongs in the destination. Some fields represent editorial meaning; others are implementation history. Carrying everything forward creates a new system shaped by old plugin choices. Ignoring too much loses provenance and makes review harder.

I classify source fields into three groups:

- content and metadata required by the destination;
- provenance needed for traceability and safe retries;
- legacy detail that should remain outside the new domain.

That classification becomes part of the migration contract. It prevents transformation rules from being invented differently inside each command or endpoint.

## Preserve identity without preserving old architecture

A migrated item needs a stable way to identify its source. The destination should know whether it has already received that source item and which record resulted from the transfer. Without that link, retries can create duplicates or update the wrong article.

Source identity does not require exposing a private legacy URL publicly. It can use a bounded source identifier and migration record appropriate to the application. The important property is determinism: the same source item should resolve to the same migration decision.

This identity also helps rollback. If a batch is later found to contain a transformation error, the affected destination records can be identified without searching article text or guessing from timestamps.

## Normalize before mapping

Legacy content rarely arrives in one clean shape. HTML may contain absolute links, inline styles, obsolete embeds, shortcode remnants, inconsistent headings, or markup produced by different editor generations.

I separate normalization from destination mapping. Normalization turns source variants into a predictable intermediate representation. Mapping then translates that representation into the destination fields and structures.

This separation makes failures easier to understand. If an image reference is malformed, that is a source-normalization issue. If a normalized category has no destination equivalent, that is a mapping decision. Combining both inside one large import loop makes testing and operational diagnosis much harder.

The intermediate form does not need to become a permanent public schema. It needs to be explicit enough for commands and tests to agree about what transformation occurred.

## Treat markup as untrusted input

Published source content may still contain markup that is unsafe or incompatible in a new rendering environment. Migration should not assume that historical storage implies suitability for the destination.

Sanitization needs a deliberate allowlist aligned with the public renderer. Scriptable attributes, unsupported embeds, and unsafe URL schemes should not pass through merely because they existed in the source. At the same time, aggressive stripping can damage legitimate structure, so the output needs review fixtures and representative tests.

Links require similar care. Internal legacy links may need mapping to new routes, while external links should retain valid destinations without carrying tracking or broken environment-specific addresses. Media references need ownership and availability checks before the new article relies on them.

The public portfolio explains these controls without exposing real unpublished content or private endpoint details.

## Keep migration and publication separate

The strongest safety decision in the Razbudise migration path is draft-only delivery. A transferred item can be inspected in the destination before any publication decision is made.

This creates room to verify:

- title, slug, excerpt, and author attribution;
- heading hierarchy and body formatting;
- internal and external links;
- media references and alternative text;
- categories, tags, and dates;
- metadata used for previews and search;
- the absence of unsupported or unsafe markup.

These checks include editorial judgment that an HTTP success response cannot provide. Automation can make review efficient, but it should not claim authority over content readiness.

## Design retries before the first failure

Migration commands will encounter partial failure. A network request may time out after the destination accepted it. A media file may be unavailable while the article body is valid. A batch may stop halfway through.

Retry behavior should be part of the design. The tool needs to distinguish “not attempted,” “accepted,” “rejected,” and “outcome uncertain.” Re-running should use source identity and destination evidence rather than blindly creating another item.

Idempotency is especially important across API boundaries. When practical, the destination can accept a stable operation key or source reference. When that is not available, the migration layer needs its own durable record of attempts and results.

Errors should be actionable but sanitized. Operators need the source reference, operation stage, and failure category. They do not need secrets, authorization headers, private topology, or full unpublished bodies printed into logs.

## Use small batches and visible checkpoints

A large one-shot migration maximizes uncertainty. Smaller batches make transformation defects easier to detect and reduce the scope of rollback.

I prefer a staged flow: select a bounded source set, normalize it, validate the proposed representation, deliver drafts, inspect the destination, and record acceptance before expanding the batch. The exact batch size depends on content variety and review capacity, not an arbitrary performance target.

Checkpoints should be durable enough to resume safely. A terminal window is not a migration ledger. If the process stops, the next run needs to establish what actually happened from stored evidence.

## Validate content, not only requests

An importer can receive `200 OK` while producing poor content. Technical validation should therefore cover both transport and representation.

Unit tests can exercise normalization and mapping rules with sanitized fixtures. Integration tests can verify authentication, validation, duplicate handling, and draft status at the API boundary. Acceptance checks can render representative migrated content and inspect structure, links, and metadata.

Counts are useful for reconciliation, but they are not proof of quality. The number read from the source, proposed for transfer, accepted by the destination, rejected, and awaiting review should reconcile without becoming a public claim about publication volume.

The reviewed evidence should remain time-bound. A passing migration test today does not guarantee that a future WordPress plugin or content pattern will match the same assumptions.

## Plan rollback at each boundary

Rollback is easier when migration writes are attributable. The process should identify which destination drafts came from a specific run and whether any related media or metadata was created.

Because delivery is draft-only, rollback does not require undoing a public editorial event. A failed batch can be removed, corrected, or superseded while the legacy source remains available as the reference point.

Configuration and credentials require separate protection. They should not be embedded in commands, committed to source control, or printed in reports. Public documentation uses generic deployment principles rather than real hostnames, paths, usernames, or infrastructure.

## Cutover is a product decision

Migration completion does not automatically define cutover. The product needs an explicit decision about when the new delivery surface becomes authoritative, how redirects are handled, what remains read-only, and which checks must pass.

That decision should include editorial review, metadata and sitemap verification, link checks, TLS and availability checks, logging, and a rollback path. It should also establish what happens to content changed in the legacy system during the transition window.

For Razbudise, active development means these decisions remain bounded to verified implementation and documented acceptance rules. I do not describe a completed public migration where the evidence supports a migration foundation.

## What I would do differently today

I would build the migration inventory and representative fixture set earlier. Real content variation matters more than a large quantity of similar samples. A small, deliberately diverse fixture set would expose mapping questions before command behavior became established.

I would also define a migration manifest from the start. It would record the source identity, normalized checksum, destination reference, attempt state, and review status without copying private content into operational logs.

Finally, I would make rendered-content comparison a standard acceptance step earlier in the process. Structured data can match while heading rhythm, embeds, or media presentation still fail editorial expectations.

## The engineering lesson

Safe migration is not measured by how quickly content moves. It is measured by how confidently each result can be explained.

The Razbudise approach keeps source identity, transformation, delivery, review, and publication as separate concerns. Draft-only delivery protects the public boundary. Stable provenance and retry behavior protect operations. Sanitized validation protects content and readers.

That discipline is more important than the specific source and destination frameworks. WordPress, Laravel, and Next.js define parts of the technical context; the engineering maturity comes from making every irreversible-looking step reviewable and reversible.
