# AleksandarDimovski.me

Production-oriented personal portfolio for Aleksandar Dimovski, positioned as a Senior PHP / Laravel Engineer and Backend & Product Engineer. The identity keeps long-term PHP, Laravel, SQL, and Linux experience primary while documenting current Python, FastAPI, React, TypeScript, and PostgreSQL product work through BuildIQ.

## Stack

Laravel 13, PHP 8.3+, Blade components, Tailwind CSS 4, minimal vanilla JavaScript, Vite, and PHPUnit. Program 001 has no application database, authentication, admin panel, or contact form.

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
npm install
npm run build
composer run dev
```

Set `APP_URL` and `PORTFOLIO_EMAIL` for the environment. Never commit `.env` or secrets.

## Testing and quality

```bash
php artisan test
vendor/bin/pint --test
npm run build
composer validate --strict
composer audit
npm audit
```

## Content editing

Project records live in `config/portfolio.php`. Article records live in `config/articles.php`, with long-form Markdown in `resources/content/articles`. Rendering passes through `App\Content\PortfolioContent`, which allowlists public fields. Draft articles are excluded from public routes and the sitemap. Add only information approved for public disclosure and follow `docs/015-editorial-style-guide.md` and `docs/016-article-fact-boundaries.md`.

Article layout, callouts, navigation, related-reading selection, table-of-contents behavior, and accessibility requirements are documented in `docs/018-article-component-guide.md`.

Verified resume facts live in `config/resume.php`; `docs/008-resume-source-of-truth.md` defines what is verified, derived, omitted, and confidential. The HTML resume is the source for the final PDF.

Verified public repository links and locally generated resume QR evidence are governed by `docs/021-public-repository-link-policy.md` and `docs/022-resume-qr-code-policy.md`.

Technology-experience boundaries are defined in `docs/013-technology-experience-boundaries.md` so current product work is not presented as long-term commercial employment.

## Production build and deployment

Production releases install locked dependencies, build frontend assets reproducibly, rebuild framework caches, verify web-server configuration and application health, and retain a rollback path. See `docs/004-public-deployment-principles.md`. Detailed operational records remain private and outside this repository.

## Privacy and confidentiality

Private work must remain sanitized. Never add credentials, private source, production URLs, client or customer data, confidential business rules, or unsupported outcomes. See `docs/005-confidentiality-and-portfolio-policy.md`.
