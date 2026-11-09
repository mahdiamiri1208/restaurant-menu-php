<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use App\Support\MenuCategoryIcon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryIconPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_choose_from_the_single_select_category_icon_picker(): void
    {
        config(['menu.admin_username' => 'admin']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/categories/create')
            ->assertOk()
            ->assertSee('Paris Lounge | پاریس لانژ');

        $iconNames = MenuCategoryIcon::names();

        self::assertCount(18, $iconNames);
        $response->assertSee('category-icon-picker', false)
            ->assertDontSee('x-model="search"', false)
            ->assertDontSee('category-icon-search', false)
            ->assertDontSee('category-icon-preview', false)
            ->assertSee('$wire.entangle(\'data.icon\').live', false)
            ->assertSee(':class="{ \'is-selected\': selected ===', false)
            ->assertSee('class="category-icon-option is-selected"', false)
            ->assertSee('aria-checked="true"', false)
            ->assertSee('.category-icon-option.is-selected::after', false)
            ->assertSee('color-mix(in srgb, var(--fi-color-primary-500) 9%', false)
            ->assertSee('.dark .category-icon-option.is-selected', false)
            ->assertSee('color-mix(in srgb, var(--fi-color-primary-500) 14%', false)
            ->assertSee('.category-icon-art', false)
            ->assertSee('color: inherit;', false)
            ->assertSee('.dark .category-icon-option.is-selected .category-icon-art', false);

        foreach ($iconNames as $icon) {
            $response->assertSee('data-icon="'.$icon.'"', false)
                ->assertSee(MenuCategoryIcon::labels()[$icon]);
        }
    }

    public function test_hookah_icon_renders_as_a_customizable_inline_svg_component(): void
    {
        $this->blade('<x-icons.hookah size="32" color="#123456" stroke-width="1.5" class="text-current" aria-label="Hookah" />')
            ->assertSee('<svg', false)
            ->assertSee('data-icon="hookah"', false)
            ->assertSee('width="32"', false)
            ->assertSee('height="32"', false)
            ->assertSee('stroke="#123456"', false)
            ->assertSee('stroke-width="1.5"', false)
            ->assertSee('class="text-current"', false)
            ->assertSee('aria-label="Hookah"', false)
            ->assertSee('viewBox="0 0 24 24"', false)
            ->assertSee('stroke-linecap="round"', false)
            ->assertSee('stroke-linejoin="round"', false)
            ->assertSee('<path', false)
            ->assertDontSee('<img', false);

        $this->blade('<x-icons.hookah />')
            ->assertSee('stroke="currentColor"', false)
            ->assertSee('stroke-width="1.2"', false);
    }

    public function test_selected_icon_is_saved_with_the_new_category(): void
    {
        config(['menu.admin_username' => 'admin']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'دسر',
                'icon' => 'cake-slice',
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        self::assertDatabaseHas('categories', [
            'name' => 'دسر',
            'icon' => 'cake-slice',
        ]);
    }

    public function test_a_livewire_icon_selection_updates_the_selected_option(): void
    {
        config(['menu.admin_username' => 'admin']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateCategory::class)
            ->assertSet('data.icon', 'utensils')
            ->set('data.icon', 'pizza')
            ->assertSet('data.icon', 'pizza')
            ->assertSee('class="category-icon-option is-selected"', false);
    }

    public function test_edit_form_restores_the_category_icon_selection(): void
    {
        config(['menu.admin_username' => 'admin']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
        ]);
        $category = Category::query()->create([
            'name' => 'نوشیدنی',
            'icon' => 'coffee',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->assertSet('data.icon', 'coffee')
            ->assertSee('class="category-icon-option is-selected"', false)
            ->assertSee('data-icon="coffee"', false);
    }
}
