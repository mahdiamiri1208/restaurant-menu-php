<?php

namespace App\Filament\Support;

use App\Support\MenuImageProcessor;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Validation\ValidationException;

class MenuImageUpload
{
    public static function make(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(5120)
            ->disk('public_uploads')
            ->directory('')
            ->imagePreviewHeight('80')
            ->preventFilePathTampering(
                true,
                static function (FileUpload $component, string $file): bool {
                    $storedPath = $component->getRecord()?->getAttribute($component->getName());

                    return is_string($storedPath) && basename($storedPath) === basename($file);
                },
            )
            ->saveUploadedFileUsing(
                static fn (FileUpload $component, TemporaryUploadedFile $file): string => app(MenuImageProcessor::class)
                    ->store($file, $component->getName()),
            )
            ->deleteUploadedFileUsing(
                static function (string|TemporaryUploadedFile $file): void {
                    if (is_string($file)) {
                        app(MenuImageProcessor::class)->delete($file);
                    }
                },
            )
            ->dehydrateStateUsing(
                static function (mixed $state, FileUpload $component): ?string {
                    if (is_array($state)) {
                        $state = array_values($state)[0] ?? null;
                    }

                    if (blank($state)) {
                        return null;
                    }

                    if (! is_string($state) || ! preg_match('/\A[a-f0-9-]+\.webp\z/i', basename($state))) {
                        throw ValidationException::withMessages([
                            'data.'.$component->getName() => 'مسیر تصویر معتبر نیست.',
                        ]);
                    }

                    return '/uploads/'.basename($state);
                },
            )
            ->nullable();
    }
}
