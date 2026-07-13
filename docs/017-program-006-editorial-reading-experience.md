# Program 006: Editorial Reading Experience

## Objective

Improve long-form article reading without changing the established portfolio identity or materially rewriting Program 005 content.

## Layout changes

Article pages use a compact header aligned with a 720px reading column. Desktop typography is approximately 19px with a 1.78 line height; mobile typography is approximately 17px with a 1.75 line height. Heading spacing, lists, links, code, tables, figures, captions, blockquotes, and horizontal rules share reusable editorial rules.

The header reduces the previous vertical padding and title ceiling so article prose appears in the initial laptop viewport. The site header remains sticky.

## Navigation and progress

A two-pixel article-only reading progress bar measures the article body rather than the footer. It is decorative, uses requestAnimationFrame, and is hidden for reduced-motion preferences.

All current articles have sufficiently deep H2 structures, so a desktop table of contents is generated from rendered headings. It is hidden below 1050px. Previous and next links follow publication order, while explicit related slugs provide deterministic related reading without duplicates.

## Callouts

Each current article contains one evidence-based Markdown callout: Key takeaway, Engineering principle, or Production note. No new factual claim is introduced.

## Boundaries

No article prose is materially rewritten. The homepage is unchanged. There are no sharing controls, counters, comments, tracking scripts, newsletters, third-party editorial packages, diagrams, or artificial code samples.

## Review requirements

Review must cover semantics, keyboard focus, contrast, mobile overflow, progress behavior, TOC state, metadata, responsive screenshots, Lighthouse, tests, dependency audits, secret scans, and confidentiality boundaries before commit approval.
