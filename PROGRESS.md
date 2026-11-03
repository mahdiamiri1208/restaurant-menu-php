# Port Progress

Compatibility verification: Laravel 13 requires PHP `^8.3`; Filament 5 requires PHP `^8.2`. GD reports WebP support enabled.

| Step | Status | Evidence | Missing |
|---|---|---|---|
| 1. Laravel 13 scaffold, PHP 8.3 Composer platform, configuration | PARTIAL | `composer.json`, Laravel 13.0.0 scaffold | Resolve dependencies, configure locale/timezone/database/cache/session, and verify all resolved package requirements. |
| 2. Migrations, models, seeder, shared helper | NOT STARTED | None | Port Prisma schema, exact seed data, and shared discount/format helpers. |
| 3. Public page, CSS/JS, local font | NOT STARTED | None | Port the reference markup, design, interactions, and local font. |
| 4. HTML caching, invalidation, PWA | NOT STARTED | None | Add warm file caching, validators, observers, and versioned offline assets. |
| 5. Filament admin, settings, uploads, Persian locale | NOT STARTED | `composer.json` declares Filament 5 | Implement authenticated resources, settings, secure WebP uploads, and local fonts. |
| 6. QR, requirements command, install SQL | NOT STARTED | None | Add offline QR generation, extension checks, and import-tested SQL. |
| 7. Hosting security and documentation | NOT STARTED | None | Add deployment layout, hardened `.htaccess`, and complete shared-hosting docs. |
| 8. Tests and final checks | NOT STARTED | None | Add feature tests and run required builds, lint, migrations, and deployment checks. |
