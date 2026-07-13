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

Project and article records live in `config/portfolio.php`. Rendering passes through `App\Content\PortfolioContent`, which allowlists public fields. Draft articles are excluded from public routes. Add only information approved for public disclosure.

Verified resume facts live in `config/resume.php`; `docs/008-resume-source-of-truth.md` defines what is verified, derived, omitted, and confidential. The HTML resume is the source for the final PDF.

Technology-experience boundaries are defined in `docs/013-technology-experience-boundaries.md` so current product work is not presented as long-term commercial employment.

## Production build and deployment

Run `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, then cache framework configuration and views. The document root must be `/public`. See `docs/004-web01-deployment.md`. This repository does not automate DNS or production changes.

## Privacy and confidentiality

Private work must remain sanitized. Never add credentials, private source, production URLs, client or customer data, confidential business rules, or unsupported outcomes. See `docs/005-confidentiality-and-portfolio-policy.md`.
