# web01 deployment

This is preparation documentation only. Do not change DNS or production automatically.

## Requirements

The verified web01 runtime is:

- PHP CLI 8.4.23
- PHP-FPM 8.4.23
- Composer 2.7.1
- Node.js 22.23.1
- npm 10.9.8
- All PHP extensions required by the locked Composer dependencies
- Additional modules including MySQL, Redis, Intl, GD, Imagick, ZIP, OPcache, and Sodium

These versions satisfy the application requirement of PHP 8.3 or newer and Node 20 or newer for asset builds. The web server document root must point to the repository `public/` directory. The Linux site user needs write access only to `storage/` and `bootstrap/cache/`.

Composer 2.7.1 emits deprecation notices under PHP 8.4. Update Composer to the latest stable release before deployment, using the server's approved Composer maintenance procedure. This documentation does not authorize or perform that update.

## Release procedure

1. Confirm the intended host, user, checkout, branch, and clean Git state.
2. Fetch and check out the reviewed release.
3. As the Linux site user, change into the deployed application directory and run `composer check-platform-reqs --no-dev`. Do not run the check from a home directory or unrelated checkout.
4. Run `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`.
5. Run `npm ci && npm run build` or deploy reviewed build artifacts.
6. Configure `.env` with production `APP_URL`, `PORTFOLIO_EMAIL`, `APP_ENV=production`, and `APP_DEBUG=false`.
7. Run `php artisan optimize` and ensure storage permissions are narrow.
8. Verify `/up`, every public route, assets, sitemap, robots, 404, TLS, and response logs.
9. Switch DNS only through a separately approved change window. Keep the previous release available for rollback.

No database migration is required for Program 001.
