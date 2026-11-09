<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Settings;
use App\Support\MenuPrice;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    public function __invoke(): View
    {
        $artColors = ['orange', 'teal', 'red'];
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('products')
            ->with(['products' => static fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $menuCategories = $categories->values()->map(
            static fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
            ],
        );

        $menuProducts = $categories->values()->flatMap(
            static function (Category $category) use ($artColors): array {
                return $category->products->map(
                    static fn (Product $product): array => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description ?? '',
                        'price' => $product->price,
                        'final_price' => MenuPrice::final($product->price, $product->discount_percent),
                        'discount_percent' => $product->discount_percent,
                        'category_id' => $product->category_id,
                        'category_name' => $category->name,
                        'category_icon' => $category->icon,
                        'color' => $artColors[$product->sort_order % count($artColors)],
                        'spicy' => $product->is_spicy,
                        'vegetarian' => $product->is_vegetarian,
                        'available' => $product->is_available,
                        'image_url' => $product->image_url,
                    ],
                )->all();
            },
        )->values();

        $settings = Settings::query()->find(1);

        return view('menu', [
            'categories' => $menuCategories,
            'products' => $menuProducts,
            'restaurantName' => $settings?->restaurant_name ?? 'پاریس لانژ',
            'logoUrl' => $settings?->logo_url,
        ]);
    }
}
