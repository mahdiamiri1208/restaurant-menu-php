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

        $this->get('/')
            ->assertOk()
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

        $this->get('/')->assertOk();

        self::assertSame(0, $queries);
    }
}
