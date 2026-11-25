<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public function getSubheading(): ?string
    {
        return 'از تب «محصولات» فهرست همین دسته‌بندی را ببینید و ترتیب آن را با کشیدن ردیف‌ها تغییر دهید.';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'اطلاعات دسته‌بندی';
    }

    public function getContentTabIcon(): string|BackedEnum|Htmlable|null
    {
        return Heroicon::OutlinedPencilSquare;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('بازگشت به فهرست')
                ->icon(Heroicon::OutlinedArrowRight)
                ->color('gray')
                ->url(CategoryResource::getUrl('index')),
            DeleteAction::make()
                ->label('حذف')
                ->icon(Heroicon::OutlinedTrash)
                ->modalHeading('حذف دسته‌بندی')
                ->modalDescription('با حذف این دسته‌بندی، محصولات آن نیز حذف می‌شوند.')
                ->modalSubmitActionLabel('حذف دسته‌بندی')
                ->successNotificationTitle('دسته‌بندی حذف شد.')
                ->using(static fn (Category $record): bool => $record->deleteWithProductImages()),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تغییرات ذخیره شد.')
            ->body('دسته‌بندی «'.$this->record->name.'» با موفقیت به‌روزرسانی شد.');
    }
}
