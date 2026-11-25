<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Support\MenuFormatter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->description('برای تغییر ترتیب محصولات داخل هر دسته‌بندی، از بخش «محصولات» در ویرایش دسته‌بندی استفاده کنید.')
            ->searchPlaceholder('جستجو در نام محصول...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->stackedOnMobile()
            ->recordUrl(static fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ImageColumn::make('image_url')
                    ->label('تصویر')
                    ->disk('public_uploads')
                    ->getStateUsing(static fn (Product $record): ?string => filled($record->image_url) ? 'uploads/'.basename($record->image_url) : null)
                    ->defaultImageUrl(asset('images/product-placeholder.svg'))
                    ->square()
                    ->imageSize(56)
                    ->width('4.5rem')
                    ->grow(false)
                    ->alignCenter()
                    ->extraCellAttributes(['class' => 'admin-product-image-cell'])
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(static fn ($state): string => MenuFormatter::toman((int) $state).' تومان')
                    ->description(static fn (Product $record): ?string => $record->discount_percent > 0
                        ? MenuFormatter::toman($record->discount_percent).'٪ تخفیف'
                        : null)
                    ->alignEnd()
                    ->sortable(),
                ToggleColumn::make('is_available')
                    ->label('موجود'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('همه دسته‌بندی‌ها'),
                TernaryFilter::make('is_available')
                    ->label('وضعیت موجودی')
                    ->placeholder('همه محصولات')
                    ->trueLabel('موجود')
                    ->falseLabel('ناموجود'),
            ])
            ->emptyStateHeading('هنوز محصولی اضافه نشده است')
            ->emptyStateDescription('اولین محصول را اضافه کنید تا در منوی عمومی نمایش داده شود.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('افزودن محصول')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(ProductResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('ویرایش')
                    ->icon(Heroicon::OutlinedPencilSquare),
                DeleteAction::make()
                    ->label('حذف')
                    ->icon(Heroicon::OutlinedTrash)
                    ->modalHeading('حذف محصول')
                    ->modalDescription('این محصول و تصویر آن برای همیشه حذف می‌شوند. این عملیات قابل بازگشت نیست.')
                    ->modalSubmitActionLabel('حذف محصول')
                    ->successNotificationTitle('محصول حذف شد.'),
            ]);
    }
}
