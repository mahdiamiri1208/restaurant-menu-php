<?php

namespace Tests\Feature;

use App\Caching\PublicMenuCache;
use App\Models\Category;
use App\Models\Product;
use App\Models\Settings;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class PublicMenuCacheTest extends TestCase
{
    use DatabaseMigrations;

    public function test_saving_a_product_invalidates_the_cached_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        $category = Category::query()->create([
            'name' => 'Cache test',
            'sort_order' => 0,
        ]);
        $cache = app(PublicMenuCache::class);
        $previousVersion = $cache->version('https://restaurant.example');
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        Product::query()->create([
            'name' => 'Cache test product',
            'price' => 1000,
            'category_id' => $category->id,
            'sort_order' => 0,
        ]);

        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    public function test_saving_a_category_invalidates_the_cached_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        $category = Category::query()->create([
            'name' => 'Before',
            'sort_order' => 0,
        ]);
        $cache = app(PublicMenuCache::class);
        $previousVersion = $cache->version('https://restaurant.example');
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $category->update(['name' => 'After']);

        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    public function test_saving_settings_invalidates_the_cached_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        $settings = Settings::query()->create([
            'id' => 1,
            'restaurant_name' => 'Before',
        ]);
        $cache = app(PublicMenuCache::class);
        $previousVersion = $cache->version('https://restaurant.example');
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $settings->update(['restaurant_name' => 'After']);

        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    public function test_brand_migration_updates_legacy_settings_and_invalidates_the_cached_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        Settings::query()->create([
            'id' => 1,
            'restaurant_name' => 'رستوران نارنج',
        ]);
        $cache = app(PublicMenuCache::class);
        $previousVersion = $cache->version('https://restaurant.example');
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $migration = require database_path('migrations/2026_11_09_000001_update_restaurant_brand_name.php');
        $migration->up();

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'restaurant_name' => 'پاریس لانژ',
        ]);
        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    public function test_deleting_a_product_invalidates_the_cached_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        $category = Category::query()->create([
            'name' => 'Cache test',
            'sort_order' => 0,
        ]);
        $product = Product::query()->create([
            'name' => 'Cache test product',
            'price' => 1000,
            'category_id' => $category->id,
            'sort_order' => 0,
        ]);
        $cache = app(PublicMenuCache::class);
        $previousVersion = $cache->version('https://restaurant.example');
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $product->delete();

        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    private function assertCacheVersionChanged(PublicMenuCache $cache, string $previousVersion): void
    {
        self::assertNotSame($previousVersion, $cache->version('https://restaurant.example'));
        self::assertNull($cache->get($previousVersion));
    }

    public function test_cache_entries_are_separated_by_request_origin(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);
        Vite::useHotFile(storage_path('framework/testing-hot'));
        $this->createMenuFixture();

        $firstResponse = $this->get('https://first.example/menu');
        $firstResponse->assertOk()->assertSee('https://first.example/build/assets/');

        $secondResponse = $this->get('https://second.example/menu');
        $secondResponse->assertOk()
            ->assertSee('https://second.example/build/assets/')
            ->assertDontSee('https://first.example/build/assets/');
    }

    public function test_cache_entries_are_separated_by_request_path(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);

        $cache = app(PublicMenuCache::class);

        self::assertNotSame(
            $cache->version('https://restaurant.example', '/menu'),
            $cache->version('https://restaurant.example', '/'),
        );
    }

    public function test_a_changed_vite_manifest_fingerprint_rebuilds_cached_html(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);
        $this->createMenuFixture();

        $temporaryPublicPath = storage_path('framework/testing-public-'.bin2hex(random_bytes(8)));
        $firstBuildPath = $temporaryPublicPath.'/first';
        $secondBuildPath = $temporaryPublicPath.'/second';
        mkdir($firstBuildPath.'/build', 0755, true);
        mkdir($secondBuildPath.'/build', 0755, true);
        copy(public_path('build/manifest.json'), $firstBuildPath.'/build/manifest.json');
        copy(public_path('sw.js'), $firstBuildPath.'/sw.js');
        $updatedManifest = json_decode(file_get_contents($firstBuildPath.'/build/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        $updatedManifest['resources/css/app.css']['file'] = 'assets/app-updated.css';
        file_put_contents($secondBuildPath.'/build/manifest.json', json_encode($updatedManifest, JSON_THROW_ON_ERROR));
        copy(public_path('sw.js'), $secondBuildPath.'/sw.js');
        $originalPublicPath = app()->publicPath();

        try {
            app()->usePublicPath($firstBuildPath);
            $firstResponse = $this->get('https://restaurant.example/menu');
            $firstResponse->assertOk();
            $firstHtml = $firstResponse->getContent();

            preg_match('/<link[^>]+href="([^"]+app-[^"]+\\.css)"/', $firstHtml, $firstAsset);

            app()->usePublicPath($secondBuildPath);
            $secondResponse = $this->get('https://restaurant.example/menu');
            $secondResponse->assertOk()->assertSee('https://restaurant.example/build/assets/app-updated.css');
            preg_match('/<link[^>]+href="([^"]+app-[^"]+\\.css)"/', $secondResponse->getContent(), $secondAsset);

            self::assertNotSame($firstAsset[1] ?? null, $secondAsset[1] ?? null);
            self::assertNotSame($firstHtml, $secondResponse->getContent());
        } finally {
            app()->usePublicPath($originalPublicPath);
            \Illuminate\Support\Facades\File::deleteDirectory($temporaryPublicPath);
        }
    }

    public function test_local_development_bypasses_cached_html(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'local');
        config(['cache.stores.file' => ['driver' => 'array']]);
        $this->createMenuFixture();

        $cache = app(PublicMenuCache::class);
        $version = $cache->version('https://restaurant.example');
        $cache->put($version, '<html>stale menu</html>', '"stale"');

        $this->get('https://restaurant.example/menu')
            ->assertOk()
            ->assertSee('Cache test product')
            ->assertDontSee('stale menu');
    }

    private function createMenuFixture(): void
    {
        $category = Category::query()->create([
            'name' => 'Cache test',
            'sort_order' => 0,
        ]);
        Product::query()->create([
            'name' => 'Cache test product',
            'price' => 1000,
            'category_id' => $category->id,
            'sort_order' => 0,
        ]);
        Settings::query()->create([
            'id' => 1,
            'restaurant_name' => 'Cache test restaurant',
        ]);
    }
}
