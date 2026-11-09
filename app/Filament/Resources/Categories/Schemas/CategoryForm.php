<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\MenuCategoryIcon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام دسته‌بندی')
                    ->required()
                    ->minLength(2)
                    ->maxLength(80)
                    ->trim()
                    ->autofocus(),
                ViewField::make('icon')
                    ->label('آیکون دسته‌بندی')
                    ->view('filament.forms.components.category-icon-picker')
                    ->default('utensils')
                    ->required()
                    ->in(MenuCategoryIcon::names())
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(10000)
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('نمایش در منو')
                    ->default(true),
            ]);
    }
}
