# AGENTS.md — Solvia.Nova OS

Laravel 12 (PHP `^8.2`, local 8.3) + Blade + Tailwind v4 (Vite) + Alpine. Classic MVC controllers, no Livewire components in `app/` despite `livewire/livewire` in `composer.json`. Auth is custom session-based (`AuthController`), not Breeze/Jetstream.

## Commands (verified in `composer.json` / `package.json`)

- First setup: `composer setup` (install → copy `.env` → key → migrate → `npm install` → `npm run build`)
- Daily dev (4 procs: serve, queue:listen, pail, vite): `composer dev`
- Tests: `composer test` (runs `config:clear` then `artisan test`); single test: `php artisan test --filter=TestName`
- Format (Pint, default config, no `pint.json`): `./vendor/bin/pint`
- Frontend only: `npm run dev` / `npm run build` (entries `resources/css/app.css`, `resources/js/app.js`)
- Engines: `php artisan os:run-engines [--only=reminders|automation|health]` (scheduled daily 07:00 reminders, hourly health+automation — see `routes/console.php`)

## Database gotchas

- `DB_CONNECTION` default is `sqlite`, but committed `.env` points to **MySQL** (`DB_HOST=127.0.0.1 DB_DATABASE=novaos`). `.env` is gitignored — never assume which backend is active; check `DB_CONNECTION` first. `phpunit.xml` forces `sqlite :memory:`.
- Source of truth is migrations (`database/migrations/2026_09_12_*` domain-grouped + Laravel defaults). `novaos.sql` dump and `database/database.sqlite` are artifacts — do not edit, prefer `php artisan migrate:fresh --seed`.
- Seed order matters (`DatabaseSeeder`): Users → Company → Clients → FinancialAccounts → Projects → Resources → Finance → Inventory → Purchasing → Automation → Announcements → KB. Seeded logins: `rian@solvia.id`, `dimas@solvia.id`, `nadia@solvia.id`, `bagas@solvia.id`, `sinta@solvia.id`, `firda@solvia.id`, `hendra@solvia.id` — all password `password`.

## Architecture rules agents miss

- Entry points: `routes/web.php` (everything except `/login`, `/register` behind `auth` middleware), `routes/console.php` (`os:run-engines`), `bootstrap/app.php` (routing/health only, no custom middleware).
- Roles: `super_admin` (only via seeder, `register` allows just `content_creator|designer|frontend_developer|backend_developer|iot_engineer|viewer`) — see `AuthController@register`. Permission checks go through `User::hasPermission()` / `isSuperAdmin()` (`app/Models/User.php`); `DashboardController@index` splits `super-admin` vs `operational` views.
- Finance invariant: **never write `FinancialAccount.balance` directly** — always use `FinancialLedgerService::recordTransaction()` / `transferFunds()` / `recordCapitalInjection|Withdrawal()` / `recordInvoicePayment()` / `payPayable()`, which recompute via `syncAccountBalance()` (`app/Services/FinancialLedgerService.php`). Transfers are `TRANSFER` type (net cash 0), capital moves are `CAPITAL_IN/OUT` (not revenue/expense).
- Cross-cutting: state-changing controller actions should call `AuditLogger::log()` (`app/Services/AuditLogger.php`); it uses `Auth::id()` + `Request::ip()`.
- Services in `app/Services/` (`FinancialLedgerService`, `ProjectHealthService`, `ReminderService`, `AutomationService`, `ScheduleService`, `WorkloadService`, `GlobalSearchService`) hold business logic — put new logic there, keep controllers thin.
- Views mirror route prefixes 1:1 (`resources/views/{finance,resources,company,projects,…}`); dashboards are `dashboard/super-admin.blade.php` vs `dashboard/operational.blade.php`.

## Docker (production stack)

- `docker compose up --build -d` → `app` on `8000:80`, `worker` (`queue:work`, recycled hourly via `--max-time=3600`), `scheduler` (`schedule:work` for `os:run-engines`), MySQL 8 on `127.0.0.1:3306` only (not internet-exposed).
- Image (`Dockerfile`, php `8.3-apache`) is prod-defaulted: `APP_ENV=production`, `LOG_CHANNEL=stderr` (exists in `config/logging.php`), `QUEUE_CONNECTION=database`, OPcache with `validate_timestamps=0` (`docker/php.prod.ini`) — so code changes need a rebuild, never a bind mount. Secrets are never baked (`.dockerignore` excludes `.env`).
- Required server `.env` (fail fast via `:?` guards, entrypoint also refuses to boot without `APP_KEY`): `APP_KEY` (generate once locally: `php artisan key:generate --show`), `APP_URL`, `DB_USERNAME` (must be NON-root, e.g. `novaos` — `MYSQL_USER` cannot be `root`), `DB_PASSWORD`, `DB_ROOT_PASSWORD`. `APP_DEBUG` is forced `false`. A `$` in secrets must be escaped as `$$`.
- Deploy: first boot `docker compose exec app php artisan migrate --force --seed`; updates `migrate --force` manually or via `RUN_MIGRATIONS=true`. Entrypoint runs `php artisan optimize` at boot (config cached at runtime, never baked — cached config freezes env). App healthcheck hits `/up`.
- Ops: backup via `docker compose exec db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" novaos' > backup.sql` (replace db name as needed). No TLS in stack — terminate HTTPS in front (reverse proxy) and set `APP_URL=https://…`.
- Docker daemon is not available in every environment — `php artisan config:clear`, `php -l`, and `composer test` remain the offline verification path.

## Conventions

- `.editorconfig`: 4 spaces, LF, trim trailing whitespace (Markdown exempt). No CI workflows / pre-commit hooks in repo — run `pint` + `composer test` manually before finishing.
- `scratch/*.php` are throwaway manual probes, not autoloaded — do not import from them or ship them.
- Currency/number formatting in finance views is `Rp` with `number_format(..., 0, ',', '.')`; keep it when adding finance UI.
