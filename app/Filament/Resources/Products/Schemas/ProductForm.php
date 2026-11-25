<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\MenuImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema, bool $showCategory = true): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات محصول')
                    ->description('نام، دسته‌بندی و توضیحی که مهمان در منو می‌بیند.')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام محصول')
                            ->required()
                            ->minLength(2)
                            ->maxLength(100)
                            ->trim()
                            ->autofocus()
                            ->columnSpan($showCategory ? 1 : 'full'),
                        ...($showCategory ? [
                            Select::make('category_id')
                                ->label('دسته‌بندی')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->exists('categories', 'id')
                                ->helperText('محصول در بخش همین دسته‌بندی در منوی عمومی نمایش داده می‌شود.'),
                        ] : []),
                        Textarea::make('description')
                            ->label('توضیحات')
                            ->maxLength(500)
                            ->rows(5)
                            ->helperText('حداکثر ۵۰۰ کاراکتر؛ در منو فقط دو خط اول نمایش داده می‌شود.')
                            ->columnSpanFull(),
                    ]),
                Section::make('قیمت و ترتیب نمایش')
                    ->description('قیمت پایه، تخفیف و جایگاه محصول در فهرست را مشخص کنید.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label('قیمت (تومان)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(1000000000)
                            ->required()
                            ->suffix('تومان')
                            ->helperText('مبلغ قبل از اعمال تخفیف.'),
                        TextInput::make('discount_percent')
                            ->label('تخفیف')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable()
                            ->suffix('%')
                            ->helperText('۰ یا خالی یعنی بدون تخفیف.'),
                        TextInput::make('sort_order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->default(0)
                            ->required()
                            ->helperText('عدد کمتر، جایگاه بالاتر در فهرست.'),
                    ]),
                Section::make('تصویر محصول')
                    ->description('یک تصویر مربع و باکیفیت، کارت محصول را حرفه‌ای‌تر نشان می‌دهد.')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        MenuImageUpload::make('image_url', 'تصویر محصول')
                            ->helperText('فرمت JPG، PNG یا WebP؛ حداکثر ۵ مگابایت.'),
                    ]),
                Section::make('وضعیت نمایش')
                    ->description('محصول ناموجود از منوی عمومی حذف می‌شود و در پنل باقی می‌ماند.')
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_available')
                            ->label('موجود در منو')
                            ->default(true)
                            ->helperText('با خاموش کردن، محصول از منو حذف می‌شود و با روشن کردن دوباره نمایش داده می‌شود.'),
                    ]),
            ]);
    }
}
