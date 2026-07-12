# PDF resume generation

The HTML resume at `/resume` is the source of truth. Generate the final PDF with a Chromium print engine using A4, print backgrounds, and CSS page size:

```bash
playwright-cli open http://127.0.0.1:8000/resume
playwright-cli pdf --filename public/files/aleksandar-dimovski-resume.pdf
```

Verify exactly two pages, selectable text, working links, matching identity/contact content, file size, metadata, and absence of local filesystem paths. Do not hand-edit the PDF independently of the HTML source.

## Deployment preparation

On web01, back up the checkout and vhost, deploy the reviewed branch/commit as the site user, run Composer install/platform checks, build assets with Node 22.23.1 and npm 10.9.8, run migrations and caches, smoke-test routes/assets/PDF/MTA-STS, and retain the prior commit for rollback. The server uses PHP/PHP-FPM 8.4.23 and MariaDB 10.11.14. Composer 2.7.1 should be upgraded separately through APT maintenance; never include `composer self-update` in application deployment.
