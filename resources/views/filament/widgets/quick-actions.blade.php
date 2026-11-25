@php
    use App\Filament\Resources\Categories\CategoryResource;
    use App\Filament\Resources\Products\ProductResource;
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        heading="دسترسی سریع"
        description="کارهای روزمره منو را از همین‌جا شروع کنید."
        :icon="Heroicon::OutlinedSparkles"
        icon-color="primary"
    >
        <div class="admin-quick-actions">
            <a href="{{ ProductResource::getUrl('create') }}" class="admin-quick-action">
                <span class="admin-quick-action-icon">
                    <x-filament::icon :icon="Heroicon::OutlinedPlus" />
                </span>
                <span class="admin-quick-action-copy">
                    <strong>افزودن محصول</strong>
                    <small>یک آیتم تازه به منو اضافه کنید</small>
                </span>
                <x-filament::icon :icon="Heroicon::OutlinedArrowLeft" class="admin-quick-action-arrow h-4 w-4" />
            </a>

            <a href="{{ CategoryResource::getUrl('create') }}" class="admin-quick-action">
                <span class="admin-quick-action-icon">
                    <x-filament::icon :icon="Heroicon::OutlinedSquaresPlus" />
                </span>
                <span class="admin-quick-action-copy">
                    <strong>افزودن دسته‌بندی</strong>
                    <small>ساختار منو را مرتب‌تر کنید</small>
                </span>
                <x-filament::icon :icon="Heroicon::OutlinedArrowLeft" class="admin-quick-action-arrow h-4 w-4" />
            </a>

            <a href="{{ \App\Filament\Pages\ManageSettings::getUrl() }}" class="admin-quick-action">
                <span class="admin-quick-action-icon">
                    <x-filament::icon :icon="Heroicon::OutlinedCog6Tooth" />
                </span>
                <span class="admin-quick-action-copy">
                    <strong>تنظیمات رستوران</strong>
                    <small>نام و لوگوی منو را ویرایش کنید</small>
                </span>
                <x-filament::icon :icon="Heroicon::OutlinedArrowLeft" class="admin-quick-action-arrow h-4 w-4" />
            </a>

            <a href="{{ route('menu') }}" target="_blank" rel="noopener" class="admin-quick-action">
                <span class="admin-quick-action-icon">
                    <x-filament::icon :icon="Heroicon::OutlinedArrowTopRightOnSquare" />
                </span>
                <span class="admin-quick-action-copy">
                    <strong>مشاهده منوی عمومی</strong>
                    <small>نتیجه را همان‌طور که مهمان می‌بیند ببینید</small>
                </span>
                <x-filament::icon :icon="Heroicon::OutlinedArrowLeft" class="admin-quick-action-arrow h-4 w-4" />
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
