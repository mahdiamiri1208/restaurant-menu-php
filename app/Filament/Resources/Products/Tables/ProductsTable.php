<?php

namespace App\Filament\Resources\Products\Tables;

use App\Caching\PublicMenuCache;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->afterReordering(static fn () => app(PublicMenuCache::class)->invalidate())
            ->columns([
                ImageColumn::make('image_url')
                    ->label('تصویر')
                    ->disk('public_uploads')
                    ->getStateUsing(static fn (Product $record): ?string => filled($record->image_url) ? 'uploads/'.basename($record->image_url) : null)
                    ->square(),
                TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('قیمت (تومان)')
                    ->numeric(locale: 'fa')
                    ->sortable(),
                TextColumn::make('discount_percent')
                    ->label('تخفیف')
                    ->suffix('%')
                    ->sortable(),
                ToggleColumn::make('is_available')
                    ->label('موجود'),
                ToggleColumn::make('is_spicy')
                    ->label('تند'),
                ToggleColumn::make('is_vegetarian')
                    ->label('گیاهی'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('حذف')
                    ->modalHeading('حذف محصول')
                    ->modalSubmitActionLabel('حذف محصول'),
            ]);
    }
}
