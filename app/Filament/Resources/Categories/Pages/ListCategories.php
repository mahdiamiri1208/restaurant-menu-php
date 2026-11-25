<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getSubheading(): ?string
    {
        return 'برای دیدن و تغییر ترتیب محصولات هر دسته‌بندی، روی «محصولات» همان ردیف بزنید.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('دسته‌بندی جدید')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
