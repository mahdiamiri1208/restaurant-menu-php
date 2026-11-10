<?php

namespace Tests\Feature;

use App\Caching\PublicMenuCache;
use App\Models\Category;
use App\Models\Settings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_redirects_to_the_public_menu(): void
    {
        $this->get('/')
            ->assertRedirect('/menu');
    }

    public function test_it_renders_seeded_menu_data_and_hides_inactive_categories(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);
        app(PublicMenuCache::class)->invalidate();
        $this->seed(DatabaseSeeder::class);

        self::assertTrue(Settings::query()->findOrFail(1)->samples_seeded);

        Category::query()
            ->whereKey('sample-category-3')
            ->update(['is_active' => false]);

        app(PublicMenuCache::class)->invalidate();

        $this->get('/menu')
            ->assertOk()
            ->assertDontSee('data-category-chip="all"', false)
            ->assertSee('<title>منوی پاریس لانژ</title>', false)
            ->assertSee('کیفیت و تازگی سفارش شما اولویت ماست. از صبوری شما سپاسگزاریم.')
            ->assertSee('پاریس لانژ')
            ->assertSee('Paris Lounge')
            ->assertSee('<meta property="og:site_name" content="Paris Lounge">', false)
            ->assertSee('<meta property="og:title" content="منوی پاریس لانژ">', false)
            ->assertDontSee('نارنج')
            ->assertSee('پیش‌غذا')
            ->assertSee('سالاد سزار')
            ->assertSee('غذای اصلی')
            ->assertSee('چلو کباب لقمه')
            ->assertDontSee('نوشیدنی')
            ->assertDontSee('لیموناد نعنا');

        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        $this->get('/menu')->assertOk();

        self::assertSame(0, $queries);
    }

    public function test_it_renders_the_selected_category_icon_in_the_menu(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);
        app(PublicMenuCache::class)->invalidate();
        $this->seed(DatabaseSeeder::class);

        $category = Category::query()->findOrFail('sample-category-1');
        $category->icon = 'beef';
        $category->save();

        $response = $this->get('/menu')->assertOk();

        self::assertSame(4, substr_count($response->getContent(), 'data-icon="beef"'));
        $response->assertSee('transform="scale(.48)"', false)
            ->assertSee('fill="currentColor" fill-rule="evenodd"', false);
    }

    public function test_product_images_are_connected_to_the_public_image_lightbox(): void
    {
        config(['cache.stores.file' => ['driver' => 'array']]);
        app(PublicMenuCache::class)->invalidate();
        $this->seed(DatabaseSeeder::class);

        $product = \App\Models\Product::query()->findOrFail('sample-product-1-1');
        $product->image_url = '/uploads/sample-product.webp';
        $product->save();

        $this->get('/menu')
            ->assertOk()
            ->assertSee('data-product-image', false)
            ->assertSee('aria-label="نمایش تصویر بزرگ‌تر: سالاد سزار"', false)
            ->assertSee('data-image-lightbox', false)
            ->assertSee('data-lightbox-image', false)
            ->assertSee('data-lightbox-close', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('hidden', false);
    }
}
