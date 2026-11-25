<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Caching\PublicMenuCache;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use App\Support\MenuFormatter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ProductsRelationManager extends RelationManager
{
    protected static ?string $title = 'محصولات';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedShoppingBag;

    protected static ?string $badgeColor = 'primary';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema, showCategory: false);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return MenuFormatter::toman($ownerRecord->products()->count());
    }

    protected function getTableHeading(): string
    {
        return 'محصولات '.$this->getOwnerRecord()->name;
    }

    public function table(Table $table): Table
    {
        $createAction = CreateAction::make()
            ->label('افزودن محصول')
            ->icon(Heroicon::OutlinedPlus)
            ->mutateFormDataUsing(fn (array $data): array => [
                ...$data,
                'category_id' => $this->getOwnerRecord()->getKey(),
            ]);

        return $table
            ->defaultSort('sort_order')
            ->description('برای شروع، «تغییر ترتیب» را بزنید؛ سپس ردیف‌ها را بکشید و رها کنید.')
            ->reorderRecordsTriggerAction(
                static fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? 'پایان مرتب‌سازی' : 'تغییر ترتیب')
                    ->icon($isReordering ? Heroicon::OutlinedCheck : Heroicon::OutlinedArrowsUpDown)
                    ->button()
                    ->color('gray'),
            )
            ->searchPlaceholder('جستجو در محصولات این دسته‌بندی...')
            ->paginated(false)
            ->stackedOnMobile()
            ->reorderable(
                'sort_order',
                static fn (Component $livewire): bool => blank($livewire->tableSearch),
            )
            ->afterReordering(static fn () => app(PublicMenuCache::class)->invalidate())
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
            ->filters([])
            ->headerActions([
                $createAction,
            ])
            ->emptyStateHeading('محصولی در این دسته‌بندی ثبت نشده است')
            ->emptyStateDescription('اولین محصول را اضافه کنید تا در این بخش منو نمایش داده شود.')
            ->emptyStateActions([
                clone $createAction,
            ])
            ->recordActions([
                EditAction::make()
                    ->label('ویرایش')
                    ->icon(Heroicon::OutlinedPencilSquare),
                DeleteAction::make()
                    ->label('حذف')
                    ->icon(Heroicon::OutlinedTrash)
                    ->modalHeading('حذف محصول')
                    ->modalDescription('این محصول و تصویر آن برای همیشه حذف می‌شوند.')
                    ->modalSubmitActionLabel('حذف محصول')
                    ->successNotificationTitle('محصول حذف شد.'),
            ]);
    }
}
