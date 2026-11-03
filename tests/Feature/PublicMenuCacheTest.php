<?php

namespace Tests\Feature;

use App\Caching\PublicMenuCache;
use App\Models\Category;
use App\Models\Product;
use App\Models\Settings;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
        $previousVersion = $cache->version();
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
        $previousVersion = $cache->version();
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
        $previousVersion = $cache->version();
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $settings->update(['restaurant_name' => 'After']);

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
        $previousVersion = $cache->version();
        $cache->put($previousVersion, '<html>old</html>', '"old"');

        $product->delete();

        $this->assertCacheVersionChanged($cache, $previousVersion);
    }

    private function assertCacheVersionChanged(PublicMenuCache $cache, string $previousVersion): void
    {
        self::assertNotSame($previousVersion, $cache->version());
        self::assertNull($cache->get($previousVersion));
    }
}
