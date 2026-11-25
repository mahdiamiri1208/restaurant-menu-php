<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getSubheading(): ?string
    {
        return 'تغییرات پس از ذخیره، فوراً در منوی عمومی اعمال می‌شوند.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('بازگشت به فهرست')
                ->icon(Heroicon::OutlinedArrowRight)
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
            DeleteAction::make()
                ->label('حذف')
                ->icon(Heroicon::OutlinedTrash)
                ->modalHeading('حذف محصول')
                ->modalDescription('این محصول و تصویر آن برای همیشه حذف می‌شوند. این عملیات قابل بازگشت نیست.')
                ->modalSubmitActionLabel('حذف محصول')
                ->successNotificationTitle('محصول حذف شد.'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تغییرات ذخیره شد.')
            ->body('«'.$this->record->name.'» با موفقیت به‌روزرسانی شد.');
    }
}
