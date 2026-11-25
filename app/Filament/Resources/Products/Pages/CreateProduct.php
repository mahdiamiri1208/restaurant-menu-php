<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getSubheading(): ?string
    {
        return 'اطلاعات محصول را کامل کنید؛ پس از ذخیره به فهرست محصولات برمی‌گردید.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('انصراف')
                ->icon(Heroicon::OutlinedArrowRight)
                ->color('gray')
                ->url(ProductResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ProductResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('محصول با موفقیت ایجاد شد.')
            ->body('«'.$this->record->name.'» اکنون در فهرست محصولات قرار دارد.');
    }
}
