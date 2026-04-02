# Repository Guidelines

## Project Structure & Module Organization
This repository is a Laravel 12 application. Core domain code lives in `app/`, with models in `app/Models` and service logic in `app/Services`. HTTP routes are defined in `routes/web.php` and `routes/auth.php`. Blade views, frontend JavaScript, and CSS live under `resources/views`, `resources/js`, and `resources/css`. Database migrations, factories, and seeders are in `database/`. Feature and unit tests are split between `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
Use Composer for the PHP workflow and npm for assets:

- `composer setup` installs dependencies, creates `.env` if needed, generates the app key, runs migrations, installs npm packages, and builds assets.
- `composer dev` starts the local stack: Laravel server, queue listener, log tailing, and Vite dev server.
- `npm run dev` runs Vite only; use it when backend services are already running.
- `npm run build` creates production assets in `public/build`.
- `composer test` clears config and runs the PHPUnit suite through `php artisan test`.
- `php artisan test --filter InventoryTest` runs a focused test class.
- `vendor/bin/pint` formats PHP to the project standard.

## Coding Style & Naming Conventions
Follow `.editorconfig`: UTF-8, LF line endings, spaces for indentation, and 4-space indents for PHP. Keep PSR-4 names aligned with paths, for example `App\\Models\\Product` in `app/Models/Product.php`. Use singular PascalCase for classes, camelCase for methods, and snake_case for database columns and migration fields. Keep Blade view names lowercase and descriptive, such as `resources/views/reports.blade.php`.

## Testing Guidelines
Write request, route, and database behavior tests in `tests/Feature`; reserve `tests/Unit` for isolated logic. Name files `*Test.php` and prefer explicit method names like `test_user_can_add_item_to_inventory`. Use `RefreshDatabase` when a test mutates schema-backed state. Add or update tests for new routes, inventory flows, transactions, and reporting changes.

## Commit & Pull Request Guidelines
Recent history uses short, imperative subjects with a prefix, usually `feat:` or `Fix:`. Keep commits focused and describe the behavior change, for example `feat: add invoice download route`. Pull requests should include a concise summary, affected areas, test coverage notes, and screenshots for Blade/UI changes. Link related issues and call out any migration, queue, or env changes reviewers must apply.

## CI/CD Deployment
- GitHub Actions workflow: `.github/workflows/deploy.yml`.
- Trigger: push to `main` or manual `workflow_dispatch`.
- Target server: DigitalOcean VPS `159.223.245.73` over SSH as `root` on port `22`.
- Live app path on VPS: `/var/www/shopsell`.
- Public URL context: Apache serves the app at `https://phoneworkslancaster.com/admin` via an alias to `/var/www/shopsell/public`.
- Server-side deploy entrypoint: `/usr/local/bin/shopsell-deploy`, which runs `/var/www/shopsell/scripts/deploy.sh`.
- SSH restriction: the GitHub Actions deploy key in `/root/.ssh/github_actions_shopsell_deploy` is forced in `authorized_keys` to run only `/usr/local/bin/shopsell-deploy`.
- Workflow connection settings are hardcoded in `.github/workflows/deploy.yml`: host `159.223.245.73`, port `22`, user `root`.
- Deploy script behavior: `git fetch`, `git reset --hard origin/main`, `composer install --no-dev`, `npm ci`, `npm run build`, `php artisan migrate --force`, clear/rebuild Laravel caches, `php artisan queue:restart`, then fix ownership for `storage`, `bootstrap/cache`, and `database`.
- Required GitHub Actions secret: `DEPLOY_SSH_PRIVATE_KEY`.
- `DEPLOY_SSH_PRIVATE_KEY` must contain the full private key from `/root/.ssh/github_actions_shopsell_deploy`, not the `.pub` key.
- Operational constraint: deployment is in-place and destructive to manual server edits because it hard-resets the VPS checkout to `origin/main`.
