<?php

namespace App\Filament\Support;

use App\Support\MenuImageProcessor;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MenuImageUpload
{
    public static function make(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(5120)
            ->maxFiles(1)
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
                static function (FileUpload $component, TemporaryUploadedFile $file): string {
                    $storedPath = app(MenuImageProcessor::class)->store($file, $component->getName());

                    if (DB::transactionLevel() > 0) {
                        DB::afterRollBack(static function () use ($storedPath): void {
                            app(MenuImageProcessor::class)->delete($storedPath);
                        });
                    }

                    return $storedPath;
                },
            )
            ->afterStateUpdated(
                static function (FileUpload $component): void {
                    $state = $component->getRawState();

                    if (! is_array($state) || count($state) < 2) {
                        return;
                    }

                    $files = array_values($state);
                    $latest = array_pop($files);

                    foreach ($files as $file) {
                        if ($file instanceof TemporaryUploadedFile) {
                            $file->delete();
                        }
                    }

                    $component->state($latest);
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
