<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('حذف دسته‌بندی')
                ->modalDescription('با حذف این دسته‌بندی، محصولات آن نیز حذف می‌شوند.')
                ->modalSubmitActionLabel('حذف دسته‌بندی')
                ->using(static fn (Category $record): bool => $record->deleteWithProductImages()),
        ];
    }
}
