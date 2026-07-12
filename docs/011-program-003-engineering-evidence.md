# Program 003: Engineering Evidence and Case Studies

Status: Committed and deployed to production on July 13, 2026.

## Objective

Program 003 moves the portfolio from résumé-led project summaries toward engineering evidence. It keeps the existing visual system while making architecture decisions, ownership, security boundaries, production practices, and lessons visible.

## Scope

- BuildIQ is the flagship engineering case study.
- MediaHub is a product-engineering case study.
- Hera Backoffice is a legacy-modernization case study.
- Nema30 Backoffice is a production PHP business-system case study.
- Razbudise is an editorial-workflow case study.
- Kalveri describes company direction and shared engineering standards.
- Engineering Principles and Release History are new public sections.

Every project provides the same reviewable structure: Executive Summary, Problem, Role, Responsibilities, Technical Stack, Architecture, Engineering Challenges, Security Considerations, Production Considerations, Lessons Learned, Current Status, and Future Roadmap.

## Evidence model

Existing verified project content remains in `config/portfolio.php`. Program 003 adds only an allowlisted evidence layer in `config/project_evidence.php`: executive summary, architecture notes, production considerations, roadmap, and Mermaid source. `App\Content\PortfolioContent` strips fields outside both public allowlists before rendering.

The case studies distinguish active development from production modernization and support. They describe engineering work without publishing private code, credentials, internal URLs, production topology, confidential client information, or unsupported commercial metrics.

## Architecture diagrams

Each project page renders an inline Mermaid diagram using a strict Mermaid security level. The original `.mmd` source is also available as a download so the evidence remains portable and reviewable. Diagrams are intentionally conceptual: they communicate boundaries and flows without disclosing operational infrastructure.

## New sections

### Engineering Principles

The principles make the decision system behind the projects explicit: understand operations, define boundaries, prefer reversible progress, treat production as product work, use evidence over claims, and keep AI-assisted work accountable.

### Release History

The release history records only approved, verifiable portfolio releases. Program 003 is recorded after its reviewed commit was deployed and verified in production.

## Validation plan

The review gate includes:

- Composer manifest validation and dependency audit
- Laravel feature and content tests
- Laravel Pint formatting check
- production frontend build and npm audit
- route and cache checks
- Git whitespace and secret scans
- Mermaid rendering at desktop, tablet, and mobile widths
- light and dark theme browser screenshots
- accessibility, responsive layout, link, metadata, and Lighthouse checks

## Review boundary

This program does not redesign the site, claim business results, or disclose private implementation details. Commit and deployment were authorized after the local diff, automated gates, and screenshot evidence were reviewed.
