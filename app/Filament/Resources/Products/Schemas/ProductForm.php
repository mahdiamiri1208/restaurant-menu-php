<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\MenuImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام محصول')
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->trim()
                    ->autofocus(),
                Textarea::make('description')
                    ->label('توضیحات')
                    ->maxLength(500)
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('قیمت (تومان)')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1000000000)
                    ->required()
                    ->suffix('تومان'),
                TextInput::make('discount_percent')
                    ->label('تخفیف (درصد)')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(100)
                    ->nullable(),
                MenuImageUpload::make('image_url', 'تصویر محصول'),
                Select::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->exists('categories', 'id'),
                TextInput::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(10000)
                    ->default(0)
                    ->required(),
                Toggle::make('is_available')
                    ->label('موجود')
                    ->default(true),
                Toggle::make('is_spicy')
                    ->label('تند'),
                Toggle::make('is_vegetarian')
                    ->label('گیاهی'),
            ]);
    }
}
