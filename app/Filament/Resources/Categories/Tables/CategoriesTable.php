<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Caching\PublicMenuCache;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('نمایش در منو'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('حذف')
                    ->modalHeading('حذف دسته‌بندی')
                    ->modalDescription('با حذف این دسته‌بندی، محصولات آن نیز حذف می‌شوند.')
                    ->modalSubmitActionLabel('حذف دسته‌بندی')
                    ->using(static fn (Category $record): bool => $record->deleteWithProductImages()),
            ]);
    }
}
