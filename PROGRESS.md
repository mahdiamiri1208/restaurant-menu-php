# Port Progress

Compatibility verification: Laravel 13 requires PHP `^8.3`; Filament 5 requires PHP `^8.2`. GD reports WebP support enabled.

| Step | Status | Evidence | Missing |
|---|---|---|---|
| 1. Laravel 13 scaffold, PHP 8.3 Composer platform, configuration | DONE | `composer.json`, `composer.lock`, `config/app.php`, `config/database.php`, `config/cache.php`, `config/session.php`, `config/queue.php`, `.env.example` | None. Composer resolved Laravel 13.39.0 and Filament 5 on PHP 8.3.0; local PHP 8.5 requirements passed. |
| 2. Migrations, models, seeder, shared helper | DONE | `database/migrations/2026_11_03_000000_create_menu_tables.php`, `app/Models/`, `app/Support/MenuPrice.php`, `app/Support/MenuFormatter.php`, `database/seeders/DatabaseSeeder.php` | None. Local MariaDB migration and seeding passed; rerunning the seeder keeps 3 categories and 5 products. |
| 3. Public page, CSS/JS, local font | PARTIAL | `resources/views/menu.blade.php`, `resources/css/app.css`, `resources/js/app.js`, `public/fonts/vazirmatn.woff2`, `public/build/` | SSR and bundled assets return HTTP 200 with all five seeded products; a side-by-side 360–430px browser comparison remains unverified because browser tooling is unavailable. |
| 4. HTML caching, invalidation, PWA | NOT STARTED | None | Add warm file caching, validators, observers, and versioned offline assets. |
| 5. Filament admin, settings, uploads, Persian locale | NOT STARTED | `composer.json` declares Filament 5 | Implement authenticated resources, settings, secure WebP uploads, and local fonts. |
| 6. QR, requirements command, install SQL | NOT STARTED | None | Add offline QR generation, extension checks, and import-tested SQL. |
| 7. Hosting security and documentation | NOT STARTED | None | Add deployment layout, hardened `.htaccess`, and complete shared-hosting docs. |
| 8. Tests and final checks | NOT STARTED | None | Add feature tests and run required builds, lint, migrations, and deployment checks. |
