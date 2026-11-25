<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Support\MenuCategoryIcon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات دسته‌بندی')
                    ->description('نام و ترتیب دسته‌بندی، ساختار منوی عمومی را می‌سازد.')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
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
                            ->required()
                            ->helperText('عدد کمتر، جایگاه بالاتر در نوار دسته‌بندی.'),
                        Toggle::make('is_active')
                            ->label('نمایش در منو')
                            ->default(true)
                            ->helperText('دسته‌بندی غیرفعال در منوی عمومی دیده نمی‌شود.'),
                    ]),
                Section::make('آیکون دسته‌بندی')
                    ->description('آیکون در نوار دسته‌بندی و عنوان بخش‌های منو نمایش داده می‌شود.')
                    ->icon(Heroicon::OutlinedSwatch)
                    ->columnSpanFull()
                    ->schema([
                        ViewField::make('icon')
                            ->label('آیکون دسته‌بندی')
                            ->view('filament.forms.components.category-icon-picker')
                            ->default('utensils')
                            ->required()
                            ->in(MenuCategoryIcon::names())
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
