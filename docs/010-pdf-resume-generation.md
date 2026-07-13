# PDF resume generation

The HTML resume at `/resume` is the source of truth. Generate the final PDF with a Chromium print engine using A4, print backgrounds, and CSS page size:

```bash
playwright-cli open http://127.0.0.1:8000/resume
playwright-cli pdf --filename public/files/aleksandar-dimovski-resume.pdf
```

Verify exactly two pages, selectable text, working links, matching identity/contact content, file size, metadata, and absence of local filesystem paths. Do not hand-edit the PDF independently of the HTML source.

## Deployment preparation

Prepare a rollback backup, deploy the exact reviewed commit, install locked dependencies, build assets reproducibly, run safe migrations, rebuild caches, and smoke-test the public routes, assets, and PDF. Validate service configuration and health before completion. Exact infrastructure, paths, runtime inventories, and operational commands remain in private release records.
