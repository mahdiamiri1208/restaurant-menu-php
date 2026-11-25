<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Caching\PublicMenuCache;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Support\MenuFormatter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Livewire\Component;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->description('برای تغییر ترتیب، ردیف‌ها را بکشید. در حالت جستجو، مرتب‌سازی دستی غیرفعال است.')
            ->searchPlaceholder('جستجو در نام دسته‌بندی...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->stackedOnMobile()
            ->recordUrl(static fn (Category $record): string => CategoryResource::getUrl('edit', ['record' => $record]))
            ->reorderable(
                'sort_order',
                static fn (Component $livewire): bool => blank($livewire->tableSearch)
                    && collect($livewire->tableFilters ?? [])
                        ->flatten()
                        ->filter(static fn (mixed $value): bool => filled($value))
                        ->isEmpty(),
            )
            ->afterReordering(static fn () => app(PublicMenuCache::class)->invalidate())
            ->columns([
                TextColumn::make('name')
                    ->label('نام دسته‌بندی')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('محصولات')
                    ->counts('products')
                    ->formatStateUsing(static fn ($state): string => MenuFormatter::toman((int) $state).' محصول')
                    ->badge()
                    ->color('gray')
                    ->url(static fn (Category $record): string => CategoryResource::getUrl('edit', [
                        'record' => $record,
                        'relation' => 'products',
                    ])),
                TextColumn::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('نمایش در منو'),
            ])
            ->filters([])
            ->emptyStateHeading('هنوز دسته‌بندی‌ای ساخته نشده است')
            ->emptyStateDescription('با ساخت اولین دسته‌بندی، ساختار منو را شروع کنید.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('افزودن دسته‌بندی')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(CategoryResource::getUrl('create')),
            ])
            ->recordActions([
                Action::make('products')
                    ->label('محصولات')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->url(static fn (Category $record): string => CategoryResource::getUrl('edit', [
                        'record' => $record,
                        'relation' => 'products',
                    ])),
                EditAction::make()
                    ->label('ویرایش')
                    ->icon(Heroicon::OutlinedPencilSquare),
                DeleteAction::make()
                    ->label('حذف')
                    ->icon(Heroicon::OutlinedTrash)
                    ->modalHeading('حذف دسته‌بندی')
                    ->modalDescription('با حذف این دسته‌بندی، محصولات آن نیز حذف می‌شوند.')
                    ->modalSubmitActionLabel('حذف دسته‌بندی')
                    ->successNotificationTitle('دسته‌بندی حذف شد.')
                    ->using(static fn (Category $record): bool => $record->deleteWithProductImages()),
            ]);
    }
}
