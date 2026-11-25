<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getSubheading(): ?string
    {
        return 'محصولات را ایجاد، ویرایش و برای نمایش در منوی عمومی آماده کنید.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('محصول جدید')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
