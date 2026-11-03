<?php

namespace App\Support;

use ErrorException;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class MenuImageProcessor
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_WIDTH = 600;
    private const MAX_PIXELS = 10_000_000;

    public function store(TemporaryUploadedFile $file, string $field): string
    {
        $path = $file->getRealPath();
        $size = is_string($path) && is_file($path) ? filesize($path) : false;

        if (! is_string($path) || ! is_int($size) || $size > self::MAX_BYTES) {
            $this->reject($field);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $supportedTypes = [
            'image/jpeg' => IMAGETYPE_JPEG,
            'image/png' => IMAGETYPE_PNG,
            'image/webp' => IMAGETYPE_WEBP,
        ];

        if (! is_string($mime) || ! array_key_exists($mime, $supportedTypes)) {
            $this->reject($field);
        }

        try {
            set_error_handler(static function (int $severity, string $message): never {
                throw new ErrorException($message, 0, $severity);
            });

            $imageInfo = getimagesize($path);
        } catch (ErrorException) {
            $this->reject($field);
        } finally {
            restore_error_handler();
        }

        if (
            ! is_array($imageInfo)
            || ($imageInfo[2] ?? null) !== $supportedTypes[$mime]
            || ! isset($imageInfo[0], $imageInfo[1])
            || $imageInfo[0] < 1
            || $imageInfo[1] < 1
            || ($imageInfo[0] * $imageInfo[1]) > self::MAX_PIXELS
        ) {
            $this->reject($field);
        }

        if (! function_exists('imagewebp') || ! (gd_info()['WebP Support'] ?? false)) {
            throw new RuntimeException('GD WebP support is required to upload menu images.');
        }

        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            $this->reject($field);
        }

        try {
            set_error_handler(static function (int $severity, string $message): never {
                throw new ErrorException($message, 0, $severity);
            });

            $source = imagecreatefromstring($contents);
        } catch (ErrorException) {
            $this->reject($field);
        } finally {
            restore_error_handler();
        }

        if ($source === false) {
            $this->reject($field);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetWidth = min($sourceWidth, self::MAX_WIDTH);
        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            throw new RuntimeException('GD could not allocate the output image.');
        }

        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);

        if (
            $transparent === false
            || ! imagealphablending($target, false)
            || ! imagesavealpha($target, true)
            || ! imagefill($target, 0, 0, $transparent)
        ) {
            throw new RuntimeException('GD could not prepare the output image.');
        }

        if (! imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight)) {
            throw new RuntimeException('GD could not resize the uploaded image.');
        }

        $webRoot = rtrim((string) config('menu.web_root', public_path()), DIRECTORY_SEPARATOR);
        $directory = $webRoot.DIRECTORY_SEPARATOR.'uploads';

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('The public uploads directory could not be created.');
        }

        $filename = bin2hex(random_bytes(16)).'.webp';
        $destination = $directory.DIRECTORY_SEPARATOR.$filename;

        try {
            set_error_handler(static function (int $severity, string $message): never {
                throw new ErrorException($message, 0, $severity);
            });

            $written = imagewebp($target, $destination, 82);
        } catch (ErrorException $exception) {
            if (is_file($destination) && ! unlink($destination)) {
                throw new RuntimeException('A partial menu image could not be removed.', previous: $exception);
            }

            throw new RuntimeException('The uploaded image could not be saved as WebP.', previous: $exception);
        } finally {
            restore_error_handler();
        }

        if (! $written) {
            if (is_file($destination) && ! unlink($destination)) {
                throw new RuntimeException('A partial menu image could not be removed.');
            }

            throw new RuntimeException('The uploaded image could not be saved as WebP.');
        }

        return 'uploads/'.$filename;
    }

    public function delete(string $storedPath): void
    {
        $filename = basename($storedPath);

        if (
            ! in_array($storedPath, [$filename, 'uploads/'.$filename, '/uploads/'.$filename], true)
            || ! preg_match('/\A[a-f0-9-]+\.webp\z/i', $filename)
        ) {
            throw new RuntimeException('Refusing to delete an invalid menu image path.');
        }

        $webRoot = rtrim((string) config('menu.web_root', public_path()), DIRECTORY_SEPARATOR);
        $path = $webRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$filename;

        if (is_file($path) && ! unlink($path)) {
            throw new RuntimeException('The replaced menu image could not be deleted.');
        }
    }

    private function reject(string $field): never
    {
        throw ValidationException::withMessages([
            'data.'.$field => 'تصویر باید JPEG، PNG یا WebP معتبر و حداکثر ۵ مگابایت باشد.',
        ]);
    }
}
