<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
