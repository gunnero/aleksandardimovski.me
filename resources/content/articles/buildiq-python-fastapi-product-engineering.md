BuildIQ is where I started using Python and FastAPI for sustained product work. I did not choose that stack to replace PHP or Laravel, and I do not present it as a decade of Python experience. My longer professional foundation remains PHP, Laravel, SQL databases, and Linux infrastructure. BuildIQ broadened that foundation by making me solve familiar backend problems through a different ecosystem.

The useful lesson was not that one framework wins. It was that technology choices should follow product constraints, and that engineering discipline transfers more reliably than syntax.

> **Key takeaway**
>
> Technology choices should follow product constraints; engineering discipline transfers better than syntax.

## Why BuildIQ uses Python and FastAPI

BuildIQ is a construction management platform in active development. Its authoritative workflows are deterministic: permissions, subscription state, project data, calculations, and release gates cannot depend on probabilistic output. Python is a practical fit for the product domain, while FastAPI provides a clear way to define typed HTTP boundaries. Any future assisted capability remains separate from the implemented public evidence.

That choice did not make the product automatically well designed. A framework can make endpoints concise, but it cannot decide where a business rule belongs, which actor may execute it, or how a failure should be represented. Those decisions still required deliberate product and architecture work.

Coming from Laravel, I was accustomed to a cohesive framework with strong conventions for routing, validation, authorization, queues, configuration, and database access. FastAPI and Starlette felt more compositional. That flexibility was useful, but it also made architectural consistency something I had to establish explicitly rather than inherit from a single framework.

## API boundaries before endpoint volume

It is easy to measure API progress by counting endpoints. I found it more useful to ask whether each boundary had a clear owner and a predictable rule set.

A project action should not merely accept valid JSON. It should establish the current company or tenant context, confirm the authenticated actor, enforce the required role or permission, validate subscription state where relevant, and then execute the business rule. Validation errors, authorization failures, and unavailable product capabilities need different responses because they mean different things to the caller.

This resembles good Laravel work more than it differs from it. Form requests, policies, middleware, service boundaries, and database constraints all exist to prevent controllers from becoming informal collections of rules. In FastAPI, dependency injection and typed request models help, but the same separation still has to be designed.

The [BuildIQ engineering case study](/projects/buildiq) documents the public architecture without exposing private implementation details.

## Authorization must be enforced, not implied

Interface visibility is not authorization. Hiding a button in React can improve the experience, but it cannot be the security boundary. BuildIQ therefore treats role and permission checks as backend responsibilities.

This became especially important as the product surface grew. A user can belong to a company context, have a role, and still lack permission for a particular action. Those concepts need to remain distinct. Otherwise a broad role check gradually becomes an accidental bypass for more precise rules.

The same principle applies to tenant boundaries. Resolving the current company once and passing that context through the request is safer than trusting arbitrary identifiers from a client. Database queries must remain scoped even when an identifier happens to be globally unique. Tests should prove that data from another tenant is unavailable, not simply assume that the interface never requests it.

## Subscription state is a backend rule

Subscription handling is another place where product behavior and security overlap. A disabled interface alone does not enforce a subscription. The backend must decide which states allow which operations and return a consistent response when a capability is unavailable.

I learned to treat subscription state as part of authorization rather than as decoration added after a feature was built. That keeps behavior coherent across the web interface, direct API calls, background work, and future clients.

The difficult part was not writing a conditional. It was defining a small, understandable state model and applying it consistently. Ambiguous states create scattered exceptions. Explicit states create rules that can be tested.

## Production configuration is a release gate

Development defaults are useful until they silently reach production. BuildIQ introduced production configuration gates so that unsafe or incomplete settings fail clearly rather than allowing the application to start in a misleading state.

This includes thinking about secret presence, allowed origins, database configuration, debug behavior, and environment-specific defaults. The exact private topology does not belong in a public article, but the principle does: production readiness should be executable. A checklist is stronger when the application and CI can reject invalid conditions.

My PHP and Laravel operations background influenced this work heavily. Configuration, process ownership, logs, dependency compatibility, and rollback are part of application engineering. A correct endpoint is not production-ready if its environment can start with unsafe assumptions.

## Testing the product rules

The reviewed BuildIQ baseline contains 127 backend tests and 45 frontend tests. Those numbers are evidence of the current repository state, not claims about completeness. Test counts matter less than what the tests protect.

Backend tests cover behavior such as access boundaries, configuration requirements, and product rules. Frontend tests cover user-facing states and interactions. Together they make cross-stack changes more reviewable. A backend response change that breaks a React assumption should be visible before release.

I also learned that tests need intentional isolation. Dependency overrides, database state, authentication helpers, and tenant context can leak between tests if cleanup is weak. A passing suite that depends on execution order is not a dependable release gate.

Vitest fit naturally with the React and TypeScript side. Fast feedback encouraged smaller checks close to the component behavior, while the production build remained a separate gate for bundling and type-related integration problems.

## Dependency remediation without feature drift

Dependency work is easy to mix with unrelated product changes. For BuildIQ, I treated remediation as a bounded engineering program: identify advisories, separate production dependencies from test and build tooling, understand compatible upgrade pairs, update lock files, and rerun the full validation surface.

The repository uses `pip-audit`, `npm audit`, and Gitleaks alongside CI. These tools do not replace review. They provide repeatable evidence that known dependency advisories and obvious secret patterns have been checked.

FastAPI and Starlette are a good example of why compatibility matters. Updating one package in isolation can create a technically installed but behaviorally mismatched stack. The safe target is a reviewed combination backed by tests, not merely the newest version number.

The remediation work finished with zero reported findings from the dependency audits at that reviewed point. That is a time-bound result, not a permanent security claim. New advisories can appear after any release, so the checks need to remain in CI and maintenance practice.

## React, TypeScript, Vite, and bundle evidence

BuildIQ’s frontend uses React, TypeScript, Vite, and Vitest. As protected pages accumulated, the primary bundle grew to approximately 520 kB. Rather than hiding the warning, I introduced route-level lazy loading for larger pages.

That reduced the primary bundle to 367.94 kB, with 112.83 kB gzip in the reviewed build, and removed the chunk-size warning. This is a modest but useful example of evidence-led frontend work: identify a concrete build problem, change the loading boundary, and verify the output without changing product behavior.

Code splitting also reinforced an architectural point. Routes are useful product boundaries. Loading a large protected area only when it is needed improves the initial path and keeps page ownership clearer. It is not a substitute for performance measurement in real usage, but it is a verifiable build improvement.

## What worked well

Typed request models and explicit API schemas made boundary discussions concrete. FastAPI’s dependency model worked well for shared request context when the dependencies remained focused. PostgreSQL provided the relational constraints expected by business workflows. React and TypeScript made interface states explicit, and Vite kept the development and production build loop direct.

More importantly, documentation and tests moved with the implementation. Architecture notes, security gates, dependency audits, and release checks were treated as part of the product rather than cleanup for later.

The [Engineering Principles](/engineering-principles) page describes the broader approach behind those choices.

## What was harder than expected

The hardest part was maintaining consistency across a flexible stack. It is possible to express the same concern through middleware, dependencies, service functions, routers, or database helpers. Without a clear rule, similar features drift into different patterns.

Test infrastructure also required attention. Async behavior, dependency overrides, and client-library changes can make apparently small upgrades affect many tests. Frontend chunking required looking beyond source-file size to the actual production output.

None of these difficulties were arguments against Python. They were reminders that framework ergonomics do not remove system design work.

## What I would do differently today

I would define domain and authorization boundaries even earlier, before expanding route volume. I would establish a stricter convention for request context and service ownership, and add bundle inspection before the first size warning appeared.

I would also schedule dependency review as routine maintenance instead of allowing remediation to become a separate concentrated program. Smaller, regular upgrades reduce compatibility distance and make failures easier to attribute.

## A broader backend foundation

BuildIQ has made me more comfortable working across Python, FastAPI, Starlette, PostgreSQL, React, TypeScript, Vite, and Vitest. It has not replaced my PHP and Laravel foundation. It has tested whether the principles I rely on—explicit rules, server-side enforcement, reviewable changes, security gates, and production evidence—hold across another stack.

They do. The syntax and framework conventions change, but the engineering questions remain: who may do what, where does the rule live, how is failure represented, what proves the change, and how can it be released safely? That judgment is the part worth carrying between technologies.
