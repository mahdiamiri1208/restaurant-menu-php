<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Caching\PublicMenuCache;
use App\Models\Category;
use App\Support\MenuImageProcessor;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
                    ->using(static function (Category $record): bool {
                        $imagePaths = $record->products()
                            ->whereNotNull('image_url')
                            ->pluck('image_url')
                            ->all();

                        if (! $record->delete()) {
                            return false;
                        }

                        DB::afterCommit(static function () use ($imagePaths): void {
                            foreach ($imagePaths as $imagePath) {
                                app(MenuImageProcessor::class)->delete($imagePath);
                            }
                        });

                        return true;
                    }),
            ]);
    }
}
