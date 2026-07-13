# Article Component Guide

## Page structure

Article pages contain the sticky site header, decorative reading-progress bar, compact article header, optional desktop table of contents, semantic article body, publication footer, previous/next navigation, related reading, and existing site footer. There must be exactly one H1.

## Typography

The header axis is capped at 850px and the body at 720px. Body text targets 18–20px desktop and 17–18px mobile with a 1.7–1.85 line height. H2 anchors include sticky-header offset. Paragraphs, lists, and headings use deliberate vertical rhythm rather than manual spacer elements.

## Callout syntax

Use a Markdown blockquote whose first line is one approved bold label followed by a hard line break:

```markdown
> **Key takeaway**
>
> Evidence-based callout text.
```

Supported labels are Key takeaway, Engineering principle, Common mistake, Security note, and Production note. Use no more than three per article and never introduce a claim that is absent from the surrounding verified content.

## Code blocks

Fenced Markdown code renders in a high-contrast, horizontally scrollable block. Inline code uses the muted surface and border system. No syntax-highlighting dependency, fake terminal chrome, or copy button is included. Add language labels only when the renderer and source provide factual language context.

## Figures

Use semantic `figure` and `figcaption` only for approved visual evidence. Images must have meaningful alternative text, responsive dimensions, and no private data. Program 006 adds styles but no artificial figures.

## Previous and next logic

Navigation follows the published registry order. The newest article has no next link; the oldest has no previous link. Missing directions are omitted cleanly. Links include descriptive accessible labels, title, and category.

## Related reading

`related_slugs` in `config/articles.php` defines deterministic related items. The controller removes missing entries, limits the result to three, and never includes drafts. Do not repeat the current article in its related configuration.

## Table of contents

The detail view generates stable IDs from sanitized H2 text. The TOC appears only when at least five H2 headings exist, is sticky on wide screens, and is hidden below 1050px. IntersectionObserver marks the current section with `aria-current`; all links remain normal keyboard-focusable anchors.

## Mobile and accessibility

At 390px, titles must wrap without clipping, metadata wraps, navigation and related cards stack, callouts remain readable, code and tables scroll internally, and the TOC is absent. The progress bar is decorative and hidden for reduced motion. Preserve the global skip link, visible focus states, semantic landmarks, non-color labels, and correct heading order.
