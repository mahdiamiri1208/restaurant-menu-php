<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public function getSubheading(): ?string
    {
        return 'نام و آیکون دسته‌بندی را مشخص کنید؛ پس از ذخیره به فهرست دسته‌بندی‌ها برمی‌گردید.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('انصراف')
                ->icon(Heroicon::OutlinedArrowRight)
                ->color('gray')
                ->url(CategoryResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CategoryResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('دسته‌بندی با موفقیت ایجاد شد.')
            ->body('«'.$this->record->name.'» اکنون در فهرست دسته‌بندی‌ها قرار دارد.');
    }
}
