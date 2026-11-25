<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Support\MenuFormatter;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MenuStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'وضعیت منو';

    protected ?string $description = 'نگاه سریع به محصولات و دسته‌بندی‌هایی که در منوی عمومی نمایش داده می‌شوند.';

    protected function getStats(): array
    {
        $products = Product::query()->count();
        $availableProducts = Product::query()->where('is_available', true)->count();
        $categories = Category::query()->count();
        $activeCategories = Category::query()->where('is_active', true)->count();
        $discountedProducts = Product::query()->where('discount_percent', '>', 0)->count();
        $unavailableProducts = $products - $availableProducts;

        return [
            Stat::make('محصولات منو', MenuFormatter::toman($products))
                ->description(MenuFormatter::toman($availableProducts).' محصول آماده نمایش')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedShoppingBag)
                ->color('success')
                ->url(ProductResource::getUrl('index')),
            Stat::make('دسته‌بندی‌ها', MenuFormatter::toman($categories))
                ->description(MenuFormatter::toman($activeCategories).' دسته‌بندی فعال')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->icon(Heroicon::OutlinedSquares2x2)
                ->color('info')
                ->url(CategoryResource::getUrl('index')),
            Stat::make('تخفیف‌های فعال', MenuFormatter::toman($discountedProducts))
                ->description($discountedProducts > 0 ? 'محصول با قیمت ویژه در منو' : 'در حال حاضر تخفیفی فعال نیست')
                ->descriptionIcon(Heroicon::OutlinedTag)
                ->icon(Heroicon::OutlinedTag)
                ->color($discountedProducts > 0 ? 'warning' : 'gray')
                ->url(ProductResource::getUrl('index')),
            Stat::make('نیازمند بررسی', MenuFormatter::toman($unavailableProducts))
                ->description($unavailableProducts > 0 ? 'محصول ناموجود از منو حذف شده' : 'همه محصولات در منو هستند')
                ->descriptionIcon($unavailableProducts > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckBadge)
                ->icon($unavailableProducts > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckBadge)
                ->color($unavailableProducts > 0 ? 'danger' : 'success')
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
