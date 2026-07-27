# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A custom, framework-less PHP 8.1+ ecommerce system ("fausto"). No Laravel/Symfony — the
`App\Core` namespace *is* the framework (router, DI container, request/response, plugin
system). Business logic lives under `App\Domain\<Feature>`, organized by feature rather than
by architectural layer.

## Commands

```bash
composer install                 # install dependencies
composer dump-autoload           # fix "Class not found" after adding new classes
php -S localhost:8000 -t public  # run dev server (or use Laragon vhost, see below)
php cli/generate-key.php         # generate APP_KEY / JWT_SECRET for .env
php cli/generate-password.php    # generate a bcrypt hash for a password
```

`composer test` (phpunit), `composer stan` (phpstan), `composer migrate`/`composer seed` are
declared in [composer.json](composer.json) but **the supporting files don't exist** — there is
no `tests/` directory, no `phpunit.xml`, and no `cli/migrate.php`/`cli/seed.php`. Don't assume
these scripts work; check before relying on them, and if asked to add tests, you'll need to
scaffold PHPUnit configuration first.

There is no `.git` repository initialized in this directory.

### Database

There is no migration system. Schema lives in plain SQL dumps at the repo root and under
`database/`: [ecommerce.sql](ecommerce.sql) (full schema, ~55 tables), then
[database_improvements.sql](database_improvements.sql), [database/erp_integration.sql](database/erp_integration.sql),
[database/update_for_tailwind.sql](database/update_for_tailwind.sql) applied on top, roughly in
that order. Import manually:

```bash
mysql -u root -e "CREATE DATABASE ecommerce"
mysql -u root ecommerce < ecommerce.sql
mysql -u root ecommerce < database_improvements.sql
```

Seed/test data: [database/seeds/test_data.sql](database/seeds/test_data.sql). Default dev admin
login (seeded, not a real secret): `admin@test.com` / `password123`, `role_id = 1`.

## Architecture

### Three separate front controllers, one shared framework

There is no single entry point. `public/index.php`, `public/manager.php`, and `public/api.php`
each independently construct an `App\Core\Application`, bootstrap it, build their own
`Router`, register their own routes, and call `$app->run()`:

- **`public/index.php`** — customer-facing storefront routes (products, cart, checkout, account).
- **`public/manager.php`** — admin panel (formerly `admin.php` — renamed at some point; the
  root-level dev-journal `.md` files and some `README.md` prose still say `admin.php`, but the
  file on disk is `manager.php`). *Before* touching the router, it does its own inline
  session-login form (HTML embedded directly in the file) and checks
  `$_SESSION['admin_user']` / `role_id = 1` against the `users` table directly via `Connection`
  — this auth check is hand-rolled here, not expressed as router middleware.
- **`public/api.php`** — JSON REST API under `/api/v1`, intended to use `JwtAuth` +
  `RateLimiter` middleware (referenced in routes but not yet implemented — see Gaps below).

`public/.htaccess` has a dedicated rewrite for the admin panel's clean URL
(`^manager(/.*)?$` → `manager.php`), then falls back to routing everything else that isn't an
existing file/dir through `index.php`. So the admin panel is reached at `/manager` (not
`/manager.php`), while `/api.php` is reached by its literal filename.

### Core framework (`app/Core`)

- `Application` — loads `.env` (vlucas/phpdotenv), loads all of `config/*.php` into an array
  keyed by filename, registers core singletons in the `Container`, starts the PHP session.
- `Container` — minimal reflection-based DI (`bind`/`singleton`/`instance`/`make`/`call`).
  Constructor/method dependencies are auto-resolved by type-hint; only typed class params,
  matching named params, or default values are resolvable — no autowiring of scalars without
  a default.
- `Router` — routes registered as `$router->get('/path', 'Namespace\Controller@method')`
  (or a closure). Supports `group(['prefix' => ..., 'middleware' => [...]], fn)`. Dynamic
  segments use `{param}` → resolved as positional method args, *not* named/injected — i.e. a
  controller method signature must accept them in the order they appear in the URI pattern.
  Middleware classes are resolved through the container and must expose `handle(Request): ?Response`.
- `Request` / `Response` — `Response::view()` renders plain PHP templates from `views/` via
  `extract()` + `include` (no templating engine, no autoescaping — use the `sanitize()` helper
  or `htmlspecialchars` explicitly in views).
- `Database\Connection` — a singleton thin wrapper around PDO (no query builder, no ORM).
  Controllers and repositories write raw SQL strings directly; `insert`/`update`/`delete`
  helpers build basic `column = ?` SQL from an associative array. Parameters are bound via `?`
  placeholders — when interpolating *identifiers* (table/column names, `LIMIT`/`OFFSET`
  values built from `(int)` casts), be careful to never interpolate raw user input into SQL text.
- `Plugin\PluginManager` / `PluginInterface` / `EventDispatcher` — plugins are discovered from
  `plugins/<Category>/<PluginName>/plugin.json` manifests, but which plugins are *active* is
  tracked in the `plugins` DB table and re-loaded (queried + `class_exists` + instantiated)
  on every request bootstrap via `loadActivePlugins()`. A plugin's `registerHooks()` wires
  into `EventDispatcher`; domain code calls the global `event('name', $payload)` helper
  (see "Eventos Disponibles" list in [README.md](README.md) for the hook names already in use,
  e.g. `product.after_create`, `order.after_payment`, `cart.item_added`).
- `helpers.php` is loaded as a Composer `files` autoload entry (global functions, no namespace):
  `env()`, `config()`, `view()`, `redirect()`, `json()`, `csrf_token()`/`csrf_field()`,
  `session()`, `flash()`/`get_flash()`, `auth()`, `is_logged_in()`, `has_permission()` (currently
  a stub returning `true`), `money_format()`, `str_slug()`, `sanitize()`, `dd()`, `app()`, `event()`.
- `Security\AuthMiddleware` / `Security\CsrfProtection` — the two middleware classes that
  actually exist (checked via `Request`/`Response`, not the `admin_user` session key used by
  `manager.php`). `AuthMiddleware` checks `$_SESSION['user']` and redirects to `/account/login`;
  `CsrfProtection` checks `_token`/`X-CSRF-TOKEN` against `$_SESSION['csrf_token']` for
  POST/PUT/DELETE/PATCH. Neither is currently wired into a route group in `index.php`/`api.php`
  — don't assume a route is protected just because these classes exist.

### Domain layer (`app/Domain/<Feature>/...`)

Organized by feature, not by Controller/Model/Repository layers — e.g. `Domain/Product/` has
both `ProductController` and `ProductRepository`, while most other features (`Cart`, `Home`,
`Pages`, `User`) only have a controller that talks to `Connection` directly with inline SQL.
`Domain/Admin/*Controller` classes are the admin-panel equivalents of the public ones. Don't
assume a repository exists for a given feature — check before introducing one, and match
whatever pattern (repository vs. direct `Connection` calls) the surrounding feature already uses.

### ERP integration (`app/Services/ERP`, `app/Contracts`)

`ERPManager` is a facade selecting an adapter implementing `App\Contracts\ERPAdapterInterface`
based on `ERP_TYPE` env var. Only `App\Services\ERP\Adapters\CustomAdapter` exists; `sap` and
`odoo` are referenced in `ERPManager::getAdapterClass()` but have no implementation — adding
them means creating the adapter class, not just changing config. ERP sync activity is logged
to the `erp_sync_logs` table.

### Plugins (`plugins/<Category>/<PluginName>/`)

Each plugin directory needs a `plugin.json` manifest (`name`, `display_name`, `version`,
`namespace`, `main_class`) plus a main class implementing `PluginInterface`. See
`plugins/PaymentGateways/Stripe/` for the only existing example. Installing a plugin
(`PluginManager::install()`) inserts a row into the `plugins` table; activating
(`activate()`) flips `is_active` and calls the instance's `activate()`.

## Gaps / things referenced but not implemented

These show up in routes or composer scripts but don't exist yet — don't assume they work,
and treat hitting them as a sign you need to build the missing piece rather than debug a typo:

- The entire `App\Api\V1\*` namespace (`AuthController`, `ProductController`, `OrderController`,
  `InventoryController`, `CustomerController`, `CategoryController`, `ErpController`,
  `WebhookController`) referenced in `public/api.php` — no `app/Api/` directory exists yet.
- `JwtAuth` and `RateLimiter` middleware referenced in `public/api.php`'s route group.
- `App\Domain\Order\CheckoutController` referenced in `public/index.php`.
- `cli/migrate.php`, `cli/seed.php` (composer scripts point to these).
- `phpunit.xml` / `tests/` (phpunit/phpstan are dev dependencies but unconfigured).

## Root-level markdown files are a dev journal, not documentation

`QUICKSTART.md`, `ACCESS_GUIDE.md`, `ADMIN_ACCESS.md`, `ADMIN_READY.md`, `AUTH_ENABLED.md`,
`DATABASE_READY.md`, `FIX_APPLIED.md` are dated progress snapshots written while building this
project, kept at the repo root. They **contradict each other** as the project evolved (e.g.
`ADMIN_ACCESS.md` says the admin panel has no auth; `AUTH_ENABLED.md` says auth was added
afterward, which matches current `public/manager.php` — note these docs still call it
`admin.php`, the file's old name). Treat them as historical context at best
— always verify claims against the actual current code rather than trusting these files.
[README.md](README.md) is the closest thing to authoritative docs (architecture diagram, API
endpoint list, plugin event names, security checklist) but is written as a polished
project pitch, not as an accurate snapshot of what's implemented (e.g. it documents an `app/Api/`
layer and ERP REST endpoints that don't exist in code yet).
