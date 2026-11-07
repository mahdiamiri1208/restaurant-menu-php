# Restaurant Menu

A Persian, right-to-left restaurant menu and Filament administration panel built with Laravel 13, PHP, MariaDB/MySQL, Blade, and locally bundled Vite assets. The public menu is server-rendered, cached, and supports offline use after its first visit. The application does not require Node.js, a queue worker, Redis, or external runtime services on the production host.

## Requirements

- PHP 8.3, 8.4, or 8.5
- MySQL or MariaDB with `utf8mb4` and `utf8mb4_unicode_ci`
- PHP extensions: `pdo_mysql`, `mbstring`, `gd` with WebP support, `fileinfo`, `intl`, `openssl`, `ctype`, `tokenizer`, and `xml`
- For local asset builds only: Node.js and npm
- For Apache hosting: `mod_rewrite`; `mod_headers`, `mod_expires`, and `mod_deflate` are recommended

The Composer platform is pinned to PHP 8.3.0 so dependency resolution stays compatible with PHP 8.3. Runtime cache and sessions use files, and the queue connection is synchronous.

## Local development (Windows PowerShell)

The local Apache VirtualHost points directly to this checkout's `public` directory. Run this once from PowerShell to configure the XAMPP Apache VirtualHost and PHP OPcache timestamp checks (elevate only if Windows denies access):

```powershell
.\configure-apache.ps1
```

Then, from the project directory, start the local environment:

```powershell
.\dev.ps1
```

The script checks/starts XAMPP Apache and MySQL, clears Laravel's config, route, compiled-view, and file-application caches, verifies the existing MySQL connection, and starts Vite. It does not run migrations, seeders, or a production build. Open [http://localhost](http://localhost). Keep the `.env` database settings and `APP_KEY` private and unchanged.

Blade and PHP changes appear on the next request; Blade edits also trigger a browser refresh. CSS and JavaScript use Vite HMR at `http://127.0.0.1:5173`. Press Ctrl+C in the development window to stop Vite; Apache and MySQL remain running. Do not run `npm run build` for normal development.

The local `.env` should use `APP_ENV=local`, `APP_DEBUG=true`, and `APP_URL=http://localhost`. The public-menu HTML cache and service worker are bypassed/removed in local mode only; production caching and service-worker behavior remain enabled.

## QR code and phpMyAdmin SQL export

Set `PUBLIC_URL` to the permanent HTTPS URL before generating the QR code. The command writes `menu-qr.svg` and a 1200×1200 `menu-qr.png` in the project root:

```powershell
php artisan menu:qr
```

Print at roughly 3–4 cm or larger with strong black-on-white contrast, and test the printed code with several phones.

To prepare an initial phpMyAdmin import, seed the local database using the intended initial admin username and password, then export:

```powershell
php artisan db:seed
php artisan menu:install-sql --force
```

This creates `database/install.sql`. It includes the seeded admin's password hash and is excluded from Git. Keep and transfer it privately. The exported admin login only matches the username and password used to seed that local database. Never import this installation file over an existing production database: it drops and recreates the tables. For a production database update, use a separately prepared and tested migration SQL file.

## Shared-hosting deployment

### 1. Prepare the release locally

1. Set `PUBLIC_URL` and `APP_URL` to the final HTTPS URL.
2. Set `ADMIN_USERNAME` and `ADMIN_PASSWORD` to the initial admin credentials, then run `php artisan db:seed`.
3. Generate the deployment SQL with `php artisan menu:install-sql --force`.
4. Build frontend assets and production dependencies:

   ```powershell
   npm ci
   npm run build
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

5. Generate an application key locally with `php artisan key:generate --show`. Put that key in the production `.env`; do not commit or publicly share it.

### 2. Create and initialize the hosting database

In cPanel or DirectAdmin, create a MySQL/MariaDB database and a database user with privileges on that database. Import the private `database/install.sql` file in phpMyAdmin. The export contains the schema, migration records, sample categories/products/settings, and the initial admin user.

The SQL uses `utf8mb4` and `utf8mb4_unicode_ci`. Do not change the database to a newer MySQL-only collation.

### 3. Upload the application outside the document root

Use a private application directory next to, not inside, the web root. For example:

```text
/home/ACCOUNT/restaurant-menu-app/  (private application code)
/home/ACCOUNT/public_html/          (Apache document root)
```

Upload the application code to `restaurant-menu-app`, including `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`, `vendor`, `artisan`, `composer.json`, and `composer.lock`. Keep the project's `public` directory in the private app directory because Laravel reads the Vite manifest there. Also copy the **contents** of that `public` directory into `public_html`; the web root must contain `index.php`, `.htaccess`, `build`, `fonts`, `css`, `js`, `uploads`, `manifest.json`, and `sw.js` as applicable.

Do not upload `.git`, `node_modules`, or `tests`. Do not place `.env`, `vendor`, `database`, or application source code inside `public_html`. Upload the generated `database/install.sql` only to a private location if it is still needed; remove it from the host after importing.

Edit `public_html/index.php` to point to the private application directory. In all three paths in Laravel's front controller (maintenance file, Composer autoloader, and `bootstrap/app.php`), replace the default `__DIR__.'/../...'` prefix with the actual private app path. For the example layout above, the paths become:

```php
$appRoot = __DIR__.'/../restaurant-menu-app';

if (file_exists($maintenance = $appRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appRoot.'/vendor/autoload.php';
$app = require_once $appRoot.'/bootstrap/app.php';
```

Keep the remainder of Laravel's `public/index.php` unchanged. The `.htaccess` in `public_html` must be the project's hardened `public/.htaccess`.

### 4. Configure the production environment

Create `.env` in the private application directory, never in `public_html`. Start from `.env.example` and set at least:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
PUBLIC_URL=https://your-domain.example
APP_KEY=base64:the-key-generated-locally

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

ADMIN_USERNAME=the-seeded-admin-username
ADMIN_PASSWORD=the-initial-admin-password-used-for-the-SQL-export
MENU_PUBLIC_ROOT=/home/ACCOUNT/public_html

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Use the actual absolute filesystem path for `MENU_PUBLIC_ROOT`. It directs image uploads and image deletion to the live web root's `uploads` directory rather than the private copy of `public`. Keep `storage` and `bootstrap/cache` writable by PHP; set directories to `755` and files to `644` where supported. Create `public_html/uploads` if it is absent and make it writable by PHP. Avoid `777`.

Choose PHP 8.3 or newer in the hosting control panel and confirm all required extensions are enabled. The `.htaccess` file redirects HTTP to HTTPS while allowing ACME challenge requests, applies security headers, blocks private files and executable uploads, and configures compression and static asset caching. Enable SSL before accepting customer traffic.

### 5. Verify the deployment

- Open `/` and confirm the Persian menu and product images load.
- Open `/admin`; a logged-out visitor should see the login page. Sign in with the seeded username/password.
- Confirm the site has a valid HTTPS certificate and the service worker and manifest load from the same origin.
- Use the browser Network panel on both the public page and `/admin` to verify every request stays on your domain.
- Change a product in the admin and reload the public menu to confirm the cached page updates.

The host does not need Node.js. `public/build` and all fonts, icons, and admin assets are compiled or bundled locally before upload. No queue worker, Redis, `storage:link`, or scheduler is required.

## Backups and restores

- Export the production database from phpMyAdmin before updates.
- Download the complete `public_html/uploads` directory separately; it contains the processed WebP product and logo images.
- To restore, import the database backup into the intended database and upload the matching `uploads` files. Keep the production `.env` and `APP_KEY` private and backed up separately.
- `database/install.sql` is for first-time installation only; it drops tables and is not a production backup.

## Updating the site

1. Back up the database and `public_html/uploads`.
2. Build the new frontend assets and run `composer install --no-dev --optimize-autoloader` locally.
3. Upload the updated application files and the new contents of `public` while preserving the production `.env`, uploaded images, and writable `storage` data.
4. Clear the application cache after uploading the new build. If SSH is available, run `php artisan cache:clear` from the private application directory. Otherwise, in the hosting file manager, delete the files inside `storage/framework/cache/data` without deleting the directory itself.
5. If the release changes the database schema, prepare and test a version-specific SQL migration locally, then import that migration in phpMyAdmin after taking the backup. Do not import `database/install.sql` over live data.
6. Verify `/`, `/admin`, image uploads, and the public menu cache after deployment.

The public menu cache is also separated by request scheme/host/port and automatically rebuilds when the Vite manifest fingerprint or service-worker cache version changes. Clearing the Laravel cache after deployment remains a safe precaution, especially when using file caches copied or retained between releases.

## Troubleshooting

- **HTTP 500:** Check `storage/logs/laravel.log`, verify `.env` syntax and `APP_KEY`, confirm PHP 8.3+ and all required extensions, and ensure the web server can write to `storage`, `bootstrap/cache`, and `public_html/uploads`.
- **Laravel files download or routes return 404:** Confirm `public_html` is the document root, the copied `index.php` paths point to the private app directory, and Apache `mod_rewrite` is enabled.
- **403 or `.htaccess` error:** Check that the host allows `.htaccess` overrides and supports Apache 2.4 authorization rules. If the host uses a different web server, ask the provider to configure equivalent HTTPS redirects, rewrite rules, security headers, and sensitive-file protections.
- **Images do not appear after upload:** Verify `MENU_PUBLIC_ROOT` is the absolute path to the live document root and that its `uploads` directory is writable by PHP.
- **Assets look stale:** Ensure the latest contents of `public/build` were uploaded. The service worker script and manifest are configured not to retain stale versions.
- **Login fails after SQL import:** Use the admin username/password that were present when `database/install.sql` was generated. Changing `ADMIN_PASSWORD` in `.env` alone does not change the imported database password hash.
- **SQL import fails:** Confirm the hosting database is empty, the user has database privileges, and phpMyAdmin selected the correct schema. The generated SQL is intended for MySQL/MariaDB.

## Assumptions

- The production host provides Apache-compatible `.htaccess` support, PHP 8.3+, and MySQL/MariaDB. A host using Nginx or another web server must configure equivalent rules in its server configuration.
- The hosting account allows PHP to write to the configured document root's `uploads` directory.
- Browser-level visual comparison on 360–430px screens and offline testing must be performed on the eventual production-equivalent host and devices.
